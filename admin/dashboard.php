<?php
session_start();
include '../includes/db.php';

// Check if the admin is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if the logged-in user is an admin
$logged_in_user_id = $_SESSION['user_id'];
$check_admin_query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($check_admin_query);
$stmt->bind_param("i", $logged_in_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if ($user_data['role'] !== 'admin') {
    $_SESSION['error'] = "Access denied. Admin privileges required.";
    header('Location: ../index.php');
    exit();
}

// Function to get submission statistics by category including team members
function getSubmissionStats($conn, $category) {
    $stats = [];
    
    // First get all users with their category values
    $users_query = "SELECT id, $category FROM users";
    $users_result = $conn->query($users_query);
    
    $user_categories = [];
    while ($user = $users_result->fetch_assoc()) {
        $user_categories[$user['id']] = $user[$category] ?? 'Unknown';
    }
    
    // Now get all submissions with their team members
    $submissions_query = "
        SELECT s.id, s.user_id, s.status, s.team_members
        FROM submissions s
    ";
    $submissions_result = $conn->query($submissions_query);
    
    if ($submissions_result && $submissions_result->num_rows > 0) {
        while ($sub = $submissions_result->fetch_assoc()) {
            $status = $sub['status'];
            $team_members = json_decode($sub['team_members'], true) ?? [];
            $submitter_id = $sub['user_id'];
            
            // Remove submitter from team members to avoid double counting (kept logic placeholder)
            $team_members = array_filter($team_members, function($member) use ($submitter_id, $user_categories) {
                return true;
            });
            
            // Process the submitter
            $submitter_category = $user_categories[$submitter_id] ?? 'Unknown';
            
            if (!isset($stats[$submitter_category])) {
                $stats[$submitter_category] = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
            }
            
            $stats[$submitter_category]['total']++;
            if ($status === 'pending') $stats[$submitter_category]['pending']++;
            if ($status === 'approved') $stats[$submitter_category]['approved']++;
            if ($status === 'rejected') $stats[$submitter_category]['rejected']++;
            
            // Process team members if any (excluding the submitter)
            if (!empty($team_members)) {
                // Get user IDs for all team members in a single query
                $placeholders = implode(',', array_fill(0, count($team_members), '?'));
                $names = array_values($team_members);
                
                $member_query = "SELECT id, name FROM users WHERE name IN ($placeholders)";
                $member_stmt = $conn->prepare($member_query);
                
                // Dynamic binding for variable number of parameters
                $types = str_repeat('s', count($names));
                $member_stmt->bind_param($types, ...$names);
                $member_stmt->execute();
                $member_result = $member_stmt->get_result();
                
                $member_ids = [];
                while ($member_row = $member_result->fetch_assoc()) {
                    $member_ids[$member_row['name']] = $member_row['id'];
                }
                $member_stmt->close();
                
                // Count for each team member (excluding submitter)
                foreach ($team_members as $member) {
                    if (isset($member_ids[$member])) {
                        $member_id = $member_ids[$member];
                        
                        // Skip if this is the submitter
                        if ($member_id == $submitter_id) continue;
                        
                        $member_category = $user_categories[$member_id] ?? 'Unknown';
                        
                        if (!isset($stats[$member_category])) {
                            $stats[$member_category] = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
                        }
                        
                        $stats[$member_category]['total']++;
                        if ($status === 'pending') $stats[$member_category]['pending']++;
                        if ($status === 'approved') $stats[$member_category]['approved']++;
                        if ($status === 'rejected') $stats[$member_category]['rejected']++;
                    }
                }
            }
        }
    }
    
    return $stats;
}

// Get statistics for all categories
$department_stats = getSubmissionStats($conn, 'department');
$program_stats = getSubmissionStats($conn, 'program_of_study');
$intake_stats = getSubmissionStats($conn, 'intake');
$graduation_stats = getSubmissionStats($conn, 'expected_graduation_year');
$gender_stats = getSubmissionStats($conn, 'gender');
$country_stats = getSubmissionStats($conn, 'country');

// Get total submissions count (including team members but avoiding double counting)
$total_submissions = 0;
$pending_submissions = 0;
$approved_submissions = 0;
$rejected_submissions = 0;

foreach ($department_stats as $stats) {
    $total_submissions += $stats['total'];
    $pending_submissions += $stats['pending'];
    $approved_submissions += $stats['approved'];
    $rejected_submissions += $stats['rejected'];
}

// Get total users and admins
$total_users_query = "SELECT COUNT(*) as total FROM users";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total'];

$total_admins_query = "SELECT COUNT(*) as total FROM users WHERE role = 'admin'";
$total_admins_result = $conn->query($total_admins_query);
$total_admins = $total_admins_result->fetch_assoc()['total'];

// Get 3ZERO cluster statistics for individual users (no double counting)
$user_cluster_stats = [];

// First get all users
$users_query = "SELECT id, name FROM users";
$users_result = $conn->query($users_query);

if ($users_result && $users_result->num_rows > 0) {
    while ($user = $users_result->fetch_assoc()) {
        $user_id = $user['id'];
        $user_cluster_stats[$user_id] = [
            'name' => $user['name'],
            'Zero Poverty' => 0,
            'Zero Unemployment' => 0,
            'Zero Net Carbon Emission' => 0,
            'total' => 0
        ];
    }
}

// Get all submissions with their three_zero_cluster data
$cluster_query = "SELECT id, user_id, three_zero_cluster, status, team_members FROM submissions";
$cluster_result = $conn->query($cluster_query);

if ($cluster_result && $cluster_result->num_rows > 0) {
    while ($row = $cluster_result->fetch_assoc()) {
        $submission_id = $row['id'];
        $submitter_id = $row['user_id'];
        $clusters = json_decode($row['three_zero_cluster'], true) ?? [];
        $status = $row['status'];
        $team_members = json_decode($row['team_members'], true) ?? [];
        
        // Get user IDs for all team members
        $team_member_ids = [];
        if (!empty($team_members)) {
            $placeholders = implode(',', array_fill(0, count($team_members), '?'));
            $types = str_repeat('s', count($team_members));
            
            $member_query = "SELECT id FROM users WHERE name IN ($placeholders)";
            $member_stmt = $conn->prepare($member_query);
            $member_stmt->bind_param($types, ...$team_members);
            $member_stmt->execute();
            $member_result = $member_stmt->get_result();
            
            while ($member_row = $member_result->fetch_assoc()) {
                $team_member_ids[] = $member_row['id'];
            }
            $member_stmt->close();
        }
        
        // Add submitter to team member IDs (they're already included in the submission)
        $all_member_ids = array_unique(array_merge([$submitter_id], $team_member_ids));
        
        // Count clusters for each user in this submission
        foreach ($all_member_ids as $user_id) {
            if (isset($user_cluster_stats[$user_id])) {
                foreach ($clusters as $cluster) {
                    if (isset($user_cluster_stats[$user_id][$cluster])) {
                        $user_cluster_stats[$user_id][$cluster]++;
                        $user_cluster_stats[$user_id]['total']++;
                    }
                }
            }
        }
    }
}

// Aggregate cluster stats from individual user data
$cluster_stats = [
    'Zero Poverty' => 0,
    'Zero Unemployment' => 0,
    'Zero Net Carbon Emission' => 0,
    'total' => 0
];

foreach ($user_cluster_stats as $user_id => $stats) {
    $cluster_stats['Zero Poverty'] += $stats['Zero Poverty'];
    $cluster_stats['Zero Unemployment'] += $stats['Zero Unemployment'];
    $cluster_stats['Zero Net Carbon Emission'] += $stats['Zero Net Carbon Emission'];
    $cluster_stats['total'] += $stats['total'];
}

// Get top users by cluster
$top_poverty_users = [];
$top_unemployment_users = [];
$top_carbon_users = [];
$top_total_users = [];

foreach ($user_cluster_stats as $user_id => $stats) {
    if ($stats['Zero Poverty'] > 0) {
        $top_poverty_users[] = [
            'name' => $stats['name'],
            'count' => $stats['Zero Poverty']
        ];
    }
    
    if ($stats['Zero Unemployment'] > 0) {
        $top_unemployment_users[] = [
            'name' => $stats['name'],
            'count' => $stats['Zero Unemployment']
        ];
    }
    
    if ($stats['Zero Net Carbon Emission'] > 0) {
        $top_carbon_users[] = [
            'name' => $stats['name'],
            'count' => $stats['Zero Net Carbon Emission']
        ];
    }
    
    if ($stats['total'] > 0) {
        $top_total_users[] = [
            'name' => $stats['name'],
            'count' => $stats['total']
        ];
    }
}

// Sort top users
usort($top_poverty_users, function($a, $b) { return $b['count'] - $a['count']; });
usort($top_unemployment_users, function($a, $b) { return $b['count'] - $a['count']; });
usort($top_carbon_users, function($a, $b) { return $b['count'] - $a['count']; });
usort($top_total_users, function($a, $b) { return $b['count'] - $a['count']; });

// Get only top 5 for each category
$top_poverty_users = array_slice($top_poverty_users, 0, 5);
$top_unemployment_users = array_slice($top_unemployment_users, 0, 5);
$top_carbon_users = array_slice($top_carbon_users, 0, 5);
$top_total_users = array_slice($top_total_users, 0, 5);

// ---------- CHART DATA PREP HELPERS ----------

// Helper to safely JSON-encode
function safe_json($data) {
    return json_encode($data, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
}

// Bar chart: Department labels & totals
$dept_labels = array_keys($department_stats);
$dept_totals = array_map(function($v){ return $v['total'] ?? 0; }, $department_stats);

// Pie/Doughnut: Overall status breakdown
$status_labels = ['Pending', 'Approved', 'Rejected'];
$status_counts = [(int)$pending_submissions, (int)$approved_submissions, (int)$rejected_submissions];

// Line chart: Submissions per month (last 12 months) — assumes `created_at` exists
$monthly_query = "
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS c
    FROM submissions
    GROUP BY ym
    ORDER BY ym ASC
";
$monthly_result = $conn->query($monthly_query);

$raw_months = [];
if ($monthly_result && $monthly_result->num_rows > 0) {
    while ($row = $monthly_result->fetch_assoc()) {
        $raw_months[$row['ym']] = (int)$row['c'];
    }
}

// Build a dense last-12-months series (YYYY-MM) including months with 0
$start = new DateTime(date('Y-m-01', strtotime('-11 months')));
$labels_ym = [];
$values_ym = [];
for ($i=0; $i<12; $i++) {
    $key = $start->format('Y-m');
    $labels_ym[] = $start->format('M Y');
    $values_ym[] = $raw_months[$key] ?? 0;
    $start->modify('+1 month');
}

// 3ZERO cluster doughnut
$cluster_labels = ['Zero Poverty', 'Zero Unemployment', 'Zero Net Carbon Emission'];
$cluster_values = [
    (int)$cluster_stats['Zero Poverty'],
    (int)$cluster_stats['Zero Unemployment'],
    (int)$cluster_stats['Zero Net Carbon Emission']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GreenCredit - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2E8B57;
            --secondary-color: #3CB371;
            --accent-color: #20c997;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        body {
            background-color: #f5f7f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 10px 10px;
            box-shadow: var(--card-shadow);
        }
        
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 12px;
            border: none;
            margin-bottom: 20px;
            height: 100%;
            box-shadow: var(--card-shadow);
            background: white;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.15);
        }
        
        .dashboard-section { 
            margin-bottom: 30px; 
        }
        
        .stats-container { 
            max-height: 300px; 
            overflow-y: auto; 
        }
        
        .info-badge { 
            font-size: 0.7rem; 
            margin-left: 5px; 
        }
        
        .stats-table th {
            position: sticky; 
            top: 0; 
            background: white; 
            z-index: 10;
        }
        
        .summary-card { 
            height: 100%; 
        }
        
        .card-header { 
            font-weight: 600;
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .cluster-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
        }
        
        .cluster-icon { 
            font-size: 2rem; 
            margin-bottom: 10px; 
        }
        
        .top-users-list { 
            list-style-type: none; 
            padding: 0; 
            margin: 0; 
        }
        
        .top-users-list li { 
            padding: 6px 0; 
            border-bottom: 1px solid rgba(255,255,255,0.1); 
            font-size: 0.85rem;
        }
        
        .top-users-list li:last-child { 
            border-bottom: none; 
        }
        
        .user-rank {
            display: inline-block; 
            width: 22px; 
            height: 22px;
            background: rgba(255,255,255,0.2); 
            border-radius: 50%;
            text-align: center; 
            line-height: 22px; 
            margin-right: 8px; 
            font-weight: bold;
            font-size: 0.75rem;
        }
        
        .top-1 .user-rank { 
            background: gold; 
            color: #333; 
        }
        
        .top-2 .user-rank { 
            background: silver; 
            color: #333; 
        }
        
        .top-3 .user-rank { 
            background: #cd7f32; 
            color: #333; 
        }
        
        /* Chart containers */
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        
        /* Summary numbers styling */
        .summary-number {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        /* Tabs for category statistics */
        .category-tabs .nav-link {
            border-radius: 8px 8px 0 0;
            margin-right: 5px;
            font-weight: 500;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chart-container {
                height: 220px;
            }
            
            .summary-number {
                font-size: 1.5rem;
            }
            
            .cluster-icon {
                font-size: 1.7rem;
            }
        }
        
        @media (max-width: 576px) {
            .chart-container {
                height: 200px;
            }
            
            .card-body {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h2>
                <p class="mb-0 opacity-75">Comprehensive overview of system metrics and statistics</p>
            </div>
            <div class="col-md-4 text-md-end">
                <span class="badge bg-light text-dark p-2">
                    <i class="fas fa-users me-1"></i> <?= number_format($total_users) ?> Users
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid my-4">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card text-white bg-primary summary-card">
                <div class="card-body text-center p-3">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h6 class="card-title mb-1">Total Users</h6>
                    <div class="summary-number"><?= number_format($total_users) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card text-white bg-success summary-card">
                <div class="card-body text-center p-3">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <i class="fas fa-user-shield fa-2x"></i>
                    </div>
                    <h6 class="card-title mb-1">Total Admins</h6>
                    <div class="summary-number"><?= number_format($total_admins) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card text-white bg-info summary-card">
                <div class="card-body text-center p-3">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                    <h6 class="card-title mb-1">Total Submissions</h6>
                    <div class="summary-number"><?= number_format($total_submissions) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card text-white bg-warning summary-card">
                <div class="card-body text-center p-3">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <i class="fas fa-spinner fa-2x"></i>
                    </div>
                    <h6 class="card-title mb-1">Pending</h6>
                    <div class="summary-number"><?= number_format($pending_submissions) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card text-white bg-success summary-card">
                <div class="card-body text-center p-3">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <h6 class="card-title mb-1">Approved</h6>
                    <div class="summary-number"><?= number_format($approved_submissions) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card text-white bg-danger summary-card">
                <div class="card-body text-center p-3">
                    <div class="d-flex justify-content-center align-items-center mb-2">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                    <h6 class="card-title mb-1">Rejected</h6>
                    <div class="summary-number"><?= number_format($rejected_submissions) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Bar: Submissions by Department -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card stat-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Submissions by Department</strong>
                    <span class="badge bg-info">Total</span>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="deptBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doughnut: Status Breakdown -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card stat-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Status Breakdown</strong>
                    <span class="badge bg-info">Distribution</span>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Line chart and cluster doughnut -->
    <div class="row mb-4">
        <!-- Line: Submissions Over Time -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card stat-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Submissions Over Time (Last 12 Months)</strong>
                    <span class="badge bg-info">Trend</span>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="timeLineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3ZERO Cluster Mix Doughnut -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card stat-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>3ZERO Cluster Mix</strong>
                    <span class="badge bg-info">Distribution</span>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="clusterDoughnut"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3ZERO Cluster Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card stat-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-seedling me-2"></i>3ZERO Cluster Statistics</h5>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stat-card cluster-card h-100">
                                <div class="card-body text-center p-3">
                                    <div class="cluster-icon">
                                        <i class="fas fa-hand-holding-heart"></i>
                                    </div>
                                    <h5 class="card-title">Zero Poverty</h5>
                                    <h3 class="card-text"><?= number_format($cluster_stats['Zero Poverty']) ?></h3>
                                    
                                    <h6 class="mt-3 mb-2">Top Contributors</h6>
                                    <ul class="top-users-list">
                                        <?php if (!empty($top_poverty_users)): ?>
                                            <?php foreach ($top_poverty_users as $index => $user): ?>
                                                <li class="<?= $index < 3 ? 'top-' . ($index + 1) : '' ?>">
                                                    <span class="user-rank"><?= $index + 1 ?></span>
                                                    <?= htmlspecialchars($user['name']) ?> (<?= $user['count'] ?>)
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li>No contributors yet</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stat-card cluster-card h-100">
                                <div class="card-body text-center p-3">
                                    <div class="cluster-icon">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <h5 class="card-title">Zero Unemployment</h5>
                                    <h3 class="card-text"><?= number_format($cluster_stats['Zero Unemployment']) ?></h3>
                                    
                                    <h6 class="mt-3 mb-2">Top Contributors</h6>
                                    <ul class="top-users-list">
                                        <?php if (!empty($top_unemployment_users)): ?>
                                            <?php foreach ($top_unemployment_users as $index => $user): ?>
                                                <li class="<?= $index < 3 ? 'top-' . ($index + 1) : '' ?>">
                                                    <span class="user-rank"><?= $index + 1 ?></span>
                                                    <?= htmlspecialchars($user['name']) ?> (<?= $user['count'] ?>)
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li>No contributors yet</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stat-card cluster-card h-100">
                                <div class="card-body text-center p-3">
                                    <div class="cluster-icon">
                                        <i class="fas fa-leaf"></i>
                                    </div>
                                    <h5 class="card-title">Zero Net Carbon Emission</h5>
                                    <h3 class="card-text"><?= number_format($cluster_stats['Zero Net Carbon Emission']) ?></h3>
                                    
                                    <h6 class="mt-3 mb-2">Top Contributors</h6>
                                    <ul class="top-users-list">
                                        <?php if (!empty($top_carbon_users)): ?>
                                            <?php foreach ($top_carbon_users as $index => $user): ?>
                                                <li class="<?= $index < 3 ? 'top-' . ($index + 1) : '' ?>">
                                                    <span class="user-rank"><?= $index + 1 ?></span>
                                                    <?= htmlspecialchars($user['name']) ?> (<?= $user['count'] ?>)
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li>No contributors yet</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card stat-card cluster-card h-100">
                                <div class="card-body text-center p-3">
                                    <div class="cluster-icon">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                    <h5 class="card-title">Total 3ZERO Actions</h5>
                                    <h3 class="card-text"><?= number_format($cluster_stats['total']) ?></h3>
                                    
                                    <h6 class="mt-3 mb-2">Top Contributors</h6>
                                    <ul class="top-users-list">
                                        <?php if (!empty($top_total_users)): ?>
                                            <?php foreach ($top_total_users as $index => $user): ?>
                                                <li class="<?= $index < 3 ? 'top-' . ($index + 1) : '' ?>">
                                                    <span class="user-rank"><?= $index + 1 ?></span>
                                                    <?= htmlspecialchars($user['name']) ?> (<?= $user['count'] ?>)
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li>No contributors yet</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics by Category -->
    <div class="row dashboard-section">
        <div class="col-12">
            <div class="card stat-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistics by Different Categories</h5>
                    <p class="mb-0 text-muted small"></p>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-tabs category-tabs px-3 pt-3" id="categoryTabs" role="tablist">
                        <?php
                        $categories = [
                            'Department' => $department_stats,
                            'Program' => $program_stats,
                            'Intake' => $intake_stats,
                            'Gender' => $gender_stats,
                            'Country' => $country_stats,
                            'Graduation Year' => $graduation_stats
                        ];
                        
                        $first = true;
                        foreach ($categories as $category => $stats):
                        ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $first ? 'active' : '' ?>" id="<?= strtolower(str_replace(' ', '-', $category)) ?>-tab" data-bs-toggle="tab" 
                                data-bs-target="#<?= strtolower(str_replace(' ', '-', $category)) ?>" type="button" role="tab">
                                <?= $category ?>
                            </button>
                        </li>
                        <?php 
                        $first = false;
                        endforeach; 
                        ?>
                    </ul>
                    
                    <div class="tab-content p-3" id="categoryTabsContent">
                        <?php
                        $first = true;
                        foreach ($categories as $category => $stats):
                        ?>
                        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="<?= strtolower(str_replace(' ', '-', $category)) ?>" role="tabpanel">
                            <div class="table-responsive stats-container">
                                <table class="table table-sm table-hover mb-0 stats-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th><?= $category ?></th>
                                            <th>Total</th>
                                            <th>Pending</th>
                                            <th>Approved</th>
                                            <th>Rejected</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $category_total = 0;
                                        $category_pending = 0;
                                        $category_approved = 0;
                                        $category_rejected = 0;
                                        
                                        foreach ($stats as $value => $counts): 
                                            $category_total += $counts['total'];
                                            $category_pending += $counts['pending'];
                                            $category_approved += $counts['approved'];
                                            $category_rejected += $counts['rejected'];
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($value) ?></td>
                                            <td><?= $counts['total'] ?></td>
                                            <td><?= $counts['pending'] ?></td>
                                            <td><?= $counts['approved'] ?></td>
                                            <td><?= $counts['rejected'] ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <th>Total</th>
                                            <th><?= $category_total ?></th>
                                            <th><?= $category_pending ?></th>
                                            <th><?= $category_approved ?></th>
                                            <th><?= $category_rejected ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <?php 
                        $first = false;
                        endforeach; 
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart initializations -->
<script>
(() => {
  // PHP ➜ JS data
  const deptLabels = <?php echo safe_json($dept_labels); ?>;
  const deptTotals = <?php echo safe_json($dept_totals); ?>;

  const statusLabels = <?php echo safe_json($status_labels); ?>;
  const statusCounts = <?php echo safe_json($status_counts); ?>;

  const timeLabels = <?php echo safe_json($labels_ym); ?>;
  const timeValues = <?php echo safe_json($values_ym); ?>;

  const clusterLabels = <?php echo safe_json($cluster_labels); ?>;
  const clusterValues = <?php echo safe_json($cluster_values); ?>;

  // Colors for charts
  const chartColors = {
    primary: '#2E8B57',
    secondary: '#3CB371',
    accent: '#20c997',
    warning: '#ffc107',
    danger: '#dc3545',
    info: '#0dcaf0',
    light: '#f8f9fa',
    dark: '#212529'
  };

  // Bar: Submissions by Department
  const deptCtx = document.getElementById('deptBarChart');
  if (deptCtx) {
    new Chart(deptCtx, {
      type: 'bar',
      data: {
        labels: deptLabels,
        datasets: [{
          label: 'Total Submissions',
          data: deptTotals,
          backgroundColor: chartColors.primary,
          borderColor: chartColors.secondary,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) => {
                return `Submissions: ${context.parsed.y}`;
              }
            }
          }
        },
        scales: {
          x: { 
            ticks: { 
              autoSkip: true, 
              maxRotation: 45, 
              minRotation: 0,
              font: { size: 10 }
            } 
          },
          y: { 
            beginAtZero: true, 
            title: { display: true, text: 'Count' },
            ticks: { stepSize: 1 }
          }
        }
      }
    });
  }

  // Doughnut : Status Breakdown
  const statusCtx = document.getElementById('statusPieChart');
  if (statusCtx) {
    new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: statusLabels,
        datasets: [{
          data: statusCounts,
          backgroundColor: [
            chartColors.warning,
            chartColors.success,
            chartColors.danger
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: (context) => {
                const total = context.dataset.data.reduce((a,b)=>a+b,0);
                const val = context.parsed;
                const pct = total ? ((val/total)*100).toFixed(1) : 0;
                return `${context.label}: ${val} (${pct}%)`;
              }
            }
          }
        }
      }
    });
  }

  // Line: Submissions Over Time (last 12 months)
  const timeCtx = document.getElementById('timeLineChart');
  if (timeCtx) {
    new Chart(timeCtx, {
      type: 'line',
      data: {
        labels: timeLabels,
        datasets: [{
          label: 'Submissions',
          data: timeValues,
          tension: 0.3,
          fill: false,
          backgroundColor: chartColors.accent,
          borderColor: chartColors.primary,
          pointBackgroundColor: chartColors.secondary,
          pointBorderColor: '#fff',
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) => {
                return `Submissions: ${context.parsed.y}`;
              }
            }
          }
        },
        scales: {
          y: { 
            beginAtZero: true, 
            title: { display: true, text: 'Count' },
            ticks: { stepSize: 1 }
          },
          x: {
            ticks: {
              maxRotation: 45,
              minRotation: 0
            }
          }
        }
      }
    });
  }

  // 3ZERO Cluster Mix Doughnut
  const clusterCtx = document.getElementById('clusterDoughnut');
  if (clusterCtx) {
    new Chart(clusterCtx, {
      type: 'doughnut',
      data: {
        labels: clusterLabels,
        datasets: [{
          data: clusterValues,
          backgroundColor: [
            chartColors.primary,
            chartColors.info,
            chartColors.accent
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: (context) => {
                const total = context.dataset.data.reduce((a,b)=>a+b,0);
                const val = context.parsed;
                const pct = total ? ((val/total)*100).toFixed(1) : 0;
                return `${context.label}: ${val} (${pct}%)`;
              }
            }
          }
        }
      }
    });
  }
})();
</script>
</body>
</html>