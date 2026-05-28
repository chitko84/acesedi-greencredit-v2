<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Define super admins (hardcoded user IDs)
$superAdmins = [47];

// Check if current user is super admin
$is_superadmin = in_array($_SESSION['user_id'], $superAdmins, true);

// Pagination configuration
$per_page_param = $_GET['per_page'] ?? '10';
$allowed_per_page = ['10', '25', '50', '100', 'all'];
$per_page_param = in_array($per_page_param, $allowed_per_page, true) ? $per_page_param : '10';
$results_per_page = (int)($per_page_param === 'all' ? 10 : $per_page_param);
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$sort_by = $_GET['sort_by'] ?? 'created_at';
$sort_order = strtoupper($_GET['sort_order'] ?? 'DESC');
$sort_order = $sort_order === 'ASC' ? 'ASC' : 'DESC';
$submission_sort_columns = [
    'id' => 's.id',
    'user_id' => 's.user_id',
    'submitter_name' => 'u.name',
    'category' => 's.category',
    'action' => 's.action',
    'points' => 's.points',
    'status' => "FIELD(s.status, 'pending', 'approved', 'rejected')",
    'created_at' => 's.created_at',
    'verified_date' => 's.verified_date',
    'team_number' => 's.team_number',
    'team_members' => 's.team_members',
    'three_zero_cluster' => 's.three_zero_cluster',
    'admin_remarks' => 's.admin_remarks',
    'superadmin_remarks' => 's.superadmin_remarks'
];
$sort_by = array_key_exists($sort_by, $submission_sort_columns) ? $sort_by : 'created_at';
$order_by = $submission_sort_columns[$sort_by] . " $sort_order, s.id DESC";
$submission_sort_labels = [
    'id' => 'Submission ID',
    'user_id' => 'Club ID',
    'submitter_name' => 'Submitted By',
    'category' => 'Category',
    'action' => 'Action',
    'points' => 'Points',
    'status' => 'Status',
    'created_at' => 'Submitted Date',
    'verified_date' => 'Verification Date',
    'team_number' => 'Team Number',
    'team_members' => 'Team Members',
    'three_zero_cluster' => '3ZERO Cluster',
    'admin_remarks' => 'Admin Remarks',
    'superadmin_remarks' => 'Superadmin Remarks'
];
$active_submission_sort_label = ($submission_sort_labels[$sort_by] ?? 'Submitted Date') . ' (' . ($sort_order === 'ASC' ? 'Ascending' : 'Descending') . ')';
$submission_sort_options = [
    ['group' => 'Review Priority', 'items' => [
        ['created_at', 'DESC', 'Newest Submissions', 'Latest items first', 'fa-clock'],
        ['created_at', 'ASC', 'Oldest Submissions', 'Review oldest first', 'fa-clock-rotate-left'],
        ['status', 'ASC', 'Status Priority', 'Pending, approved, rejected', 'fa-list-check'],
        ['verified_date', 'DESC', 'Recently Verified', 'Latest verification first', 'fa-circle-check'],
    ]],
    ['group' => 'Submission Details', 'items' => [
        ['id', 'DESC', 'Submission ID', 'Largest first', 'fa-hashtag'],
        ['id', 'ASC', 'Submission ID', 'Smallest first', 'fa-arrow-up-1-9'],
        ['category', 'ASC', 'Category', 'A to Z', 'fa-layer-group'],
        ['action', 'ASC', 'Action', 'A to Z', 'fa-leaf'],
        ['points', 'DESC', 'Points', 'Highest first', 'fa-star'],
        ['points', 'ASC', 'Points', 'Lowest first', 'fa-star-half-stroke'],
    ]],
    ['group' => 'People & Teams', 'items' => [
        ['submitter_name', 'ASC', 'Submitted By', 'A to Z', 'fa-user'],
        ['user_id', 'ASC', 'Club ID', 'Smallest first', 'fa-id-card'],
        ['team_number', 'ASC', 'Team Number', 'A to Z', 'fa-people-group'],
        ['team_members', 'ASC', 'Team Members', 'A to Z', 'fa-users'],
    ]],
    ['group' => 'Admin Checks', 'items' => [
        ['three_zero_cluster', 'ASC', '3ZERO Cluster', 'A to Z', 'fa-seedling'],
        ['admin_remarks', 'DESC', 'Admin Remarks', 'Filled remarks first', 'fa-comment-dots'],
        ['superadmin_remarks', 'DESC', 'Superadmin Remarks', 'Filled remarks first', 'fa-comments'],
    ]],
];

// Get total number of submissions for pagination
$count_query = "SELECT COUNT(*) AS total FROM submissions";
$count_stmt = $conn->prepare($count_query);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$results_per_page = $per_page_param === 'all' ? max(1, (int)$total_rows) : $results_per_page;
$offset = $per_page_param === 'all' ? 0 : (($page - 1) * $results_per_page);
$total_pages = $per_page_param === 'all' ? 1 : (int)ceil($total_rows / $results_per_page);

// Ensure page doesn't exceed total pages
if ($page > $total_pages && $total_pages > 0) {
    header("Location: ?page=" . $total_pages . "&per_page=" . urlencode($per_page_param) . "&sort_by=" . urlencode($sort_by) . "&sort_order=" . urlencode($sort_order));
    exit();
}

// Fetch paginated submissions with submitter name
$query = "
    SELECT s.*,
           u.name AS submitter_name
    FROM submissions s
    LEFT JOIN users u ON u.id = s.user_id
    ORDER BY $order_by
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $results_per_page, $offset);
$stmt->execute();
$submissions_result = $stmt->get_result();

function build_submission_page_link($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}
$submission_return_query = http_build_query([
    'page' => $page,
    'per_page' => $per_page_param,
    'sort_by' => $sort_by,
    'sort_order' => $sort_order
]);

// Handle bulk actions (only for super admins)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_superadmin) {
    if (isset($_POST['bulk_action']) && isset($_POST['selected_submissions'])) {
        $selected_ids = $_POST['selected_submissions'];
        // sanitize ids
        $selected_ids = array_map('intval', $selected_ids);
        $selected_ids = array_filter($selected_ids, function($v){ return $v > 0; });
        $action = $_POST['bulk_action'];
        $bulk_remarks = isset($_POST['bulk_remarks']) ? trim($_POST['bulk_remarks']) : '';

        if (!empty($selected_ids)) {
            $in = implode(',', $selected_ids); // safe because ints

            if ($action === 'approve' || $action === 'reject') {
                $status = ($action === 'approve') ? 'approved' : 'rejected';
                
                // For reject action, set points to 0
                if ($action === 'reject') {
                    $update_query = "UPDATE submissions SET status = ?, verified_date = NOW(), superadmin_remarks = ?, points = 0 WHERE id IN ($in)";
                } else {
                    // For approve action, restore points based on category
                    $update_query = "UPDATE submissions SET status = ?, verified_date = NOW(), superadmin_remarks = ?, 
                                    points = CASE 
                                        WHEN category = 'Low Impact' THEN 25 
                                        WHEN category = 'Medium Impact' THEN 50 
                                        WHEN category = 'High Impact' THEN 75 
                                        ELSE points 
                                    END 
                                    WHERE id IN ($in)";
                }
                
                // include superadmin_remarks if provided
                if ($bulk_remarks !== '') {
                    $upd_stmt = $conn->prepare($update_query);
                    $upd_stmt->bind_param("ss", $status, $bulk_remarks);
                } else {
                    // If no remarks provided, use empty string
                    $empty_remarks = '';
                    $upd_stmt = $conn->prepare($update_query);
                    $upd_stmt->bind_param("ss", $status, $empty_remarks);
                }
            } elseif ($action === 'delete') {
                $update_query = "DELETE FROM submissions WHERE id IN ($in)";
                $upd_stmt = $conn->prepare($update_query);
                // no bind needed
            } else {
                $upd_stmt = null;
            }

            if ($upd_stmt) {
                if ($upd_stmt->execute()) {
                    // Send email notifications for bulk actions
                    if ($action === 'approve' || $action === 'reject' || $action === 'delete') {
                        foreach ($selected_ids as $submission_id) {
                            sendSubmissionNotification($submission_id, $action, $bulk_remarks);
                        }
                    }
                    
                    $_SESSION['success'] = "Bulk action completed successfully!";
                    header("Location: submissions.php?" . $submission_return_query);
                    exit();
                } else {
                    $_SESSION['error'] = "Error performing bulk action: " . $conn->error;
                }
            } else {
                $_SESSION['error'] = "Invalid bulk action.";
            }
        } else {
            $_SESSION['error'] = "No submissions selected for bulk action.";
        }
    }

    // Handle individual submission actions
    if (isset($_POST['action_type']) && isset($_POST['submission_id'])) {
        $submission_id = (int)$_POST['submission_id'];
        $action_type = $_POST['action_type'];
        $remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';

        if ($action_type === 'approve') {
            // For approve action, restore points based on category
            $update_query = "UPDATE submissions SET status = 'approved', verified_date = NOW(), superadmin_remarks = ?, 
                            points = CASE 
                                WHEN category = 'Low Impact' THEN 25 
                                WHEN category = 'Medium Impact' THEN 50 
                                WHEN category = 'High Impact' THEN 75 
                                ELSE points 
                            END 
                            WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $remarks, $submission_id);
        } elseif ($action_type === 'reject') {
            // For reject action, set points to 0
            $update_query = "UPDATE submissions SET status = 'rejected', verified_date = NOW(), superadmin_remarks = ?, points = 0 WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $remarks, $submission_id);
        } elseif ($action_type === 'delete') {
            $update_query = "DELETE FROM submissions WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("i", $submission_id);
        } else {
            $stmt = null;
        }

        if ($stmt) {
            if ($stmt->execute()) {
                // Send email notification for individual action
                sendSubmissionNotification($submission_id, $action_type, $remarks);
                
                $_SESSION['success'] = "Submission {$action_type} successfully!";
                header("Location: submissions.php?" . $submission_return_query);
                exit();
            } else {
                $_SESSION['error'] = "Error updating submission: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Invalid action.";
        }
    }
}

// Function to send email notifications for submission actions
function sendSubmissionNotification($submission_id, $action, $remarks) {
    global $conn;
    
    // Get submission details
    $submission_query = $conn->prepare("
        SELECT s.*, u.name AS submitter_name, u.email AS submitter_email 
        FROM submissions s 
        LEFT JOIN users u ON u.id = s.user_id 
        WHERE s.id = ?
    ");
    $submission_query->bind_param("i", $submission_id);
    $submission_query->execute();
    $submission_result = $submission_query->get_result();
    
    if ($submission_result->num_rows === 0) {
        return false;
    }
    
    $submission = $submission_result->fetch_assoc();
    
    // Get team members - FIXED: team_members contains names, not IDs
    $team_members = [];
    $team_member_emails = [];
    
    if (!empty($submission['team_members'])) {
        $team_member_names = json_decode($submission['team_members']);
        if (is_array($team_member_names) && !empty($team_member_names)) {
            // Since we have names, we need to find their emails by querying the users table
            $placeholders = implode(',', array_fill(0, count($team_member_names), '?'));
            $types = str_repeat('s', count($team_member_names));
            
            $team_query = $conn->prepare("SELECT id, name, email FROM users WHERE name IN ($placeholders)");
            $team_query->bind_param($types, ...$team_member_names);
            $team_query->execute();
            $team_result = $team_query->get_result();
            
            while ($member = $team_result->fetch_assoc()) {
                $team_members[] = $member['name'];
                $team_member_emails[] = $member['email'];
            }
        }
    }
    
    // Action labels for email
    $action_labels = [
        'approve' => 'Approved',
        'reject' => 'Rejected',
        'delete' => 'Deleted'
    ];
    
    $action_label = $action_labels[$action] ?? ucfirst($action);
    
    // Email subject and content
    $subject = "Your Submission Has Been $action_label - GreenCredit";
    
    $message = "
        <html>
        <head>
            <title>Submission $action_label</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2E8B57; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { margin-top: 20px; text-align: center; font-size: 0.8em; color: #666; }
                .status { font-weight: bold; padding: 5px 10px; border-radius: 4px; }
                .approved { background-color: #d4edda; color: #155724; }
                .rejected { background-color: #f8d7da; color: #721c24; }
                .deleted { background-color: #f8f9fa; color: #6c757d; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Submission $action_label</h2>
                </div>
                <div class='content'>
                    <p>Dear {$submission['submitter_name']},</p>
                    <p>Your submission has been <span class='status $action'>$action_label</span> by the GreenCredit administration team.</p>
                    
                    <p><strong>Submission Details:</strong></p>
                    <ul>
                        <li><strong>ID:</strong> #{$submission['id']}</li>
                        <li><strong>Category:</strong> {$submission['category']}</li>
                        <li><strong>Action:</strong> {$submission['action']}</li>
                        <li><strong>Points:</strong> {$submission['points']}</li>
                        <li><strong>Status:</strong> <span class='status $action'>$action_label</span></li>
                    </ul>
    ";
    
    if (!empty($remarks)) {
        $message .= "<p><strong>Remarks from Administrator:</strong><br>" . nl2br(htmlspecialchars($remarks)) . "</p>";
    }
    
    if (!empty($team_members)) {
        $message .= "<p><strong>Team Members:</strong> " . implode(", ", $team_members) . "</p>";
    }
    
    $message .= "
                    <p>If you have any questions about this decision, please contact the GreenCredit administration.</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from GreenCredit</p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    // Email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: GreenCredit <acesediaiuedu@ace-sedi.aiu.edu.my>" . "\r\n";
    
    // Send email to submitter
    mail($submission['submitter_email'], $subject, $message, $headers);
    
    // Send email to team members
    foreach ($team_member_emails as $email) {
        if ($email !== $submission['submitter_email']) {
            mail($email, $subject, $message, $headers);
        }
    }
    
    // Send notification to admins
    $admin_emails = [
        'chitko.ko@student.aiu.edu.my',
        'another.admin@example.com',
    ];
    
    $admin_subject = "Submission #{$submission['id']} Has Been $action_label";
    $admin_message = "
        <html>
        <head>
            <title>Submission $action_label</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2E8B57; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { margin-top: 20px; text-align: center; font-size: 0.8em; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Submission $action_label by Super Admin</h2>
                </div>
                <div class='content'>
                    <p>A submission has been <strong>$action_label</strong> by a super administrator.</p>
                    
                    <p><strong>Submission Details:</strong></p>
                    <ul>
                        <li><strong>ID:</strong> #{$submission['id']}</li>
                        <li><strong>Submitter:</strong> {$submission['submitter_name']} ({$submission['submitter_email']})</li>
                        <li><strong>Category:</strong> {$submission['category']}</li>
                        <li><strong>Action:</strong> {$submission['action']}</li>
                        <li><strong>Points:</strong> {$submission['points']}</li>
                        <li><strong>Status:</strong> $action_label</li>
                    </ul>
    ";
    
    if (!empty($remarks)) {
        $admin_message .= "<p><strong>Remarks from Super Admin:</strong><br>" . nl2br(htmlspecialchars($remarks)) . "</p>";
    }
    
    if (!empty($team_members)) {
        $admin_message .= "<p><strong>Team Members:</strong> " . implode(", ", $team_members) . "</p>";
    }
    
    $admin_message .= "
                </div>
                <div class='footer'>
                    <p>This is an automated message from GreenCredit</p>
                </div>
            </div>
        </body>
        </html>
    ";
    
    foreach ($admin_emails as $admin_email) {
        mail($admin_email, $admin_subject, $admin_message, $headers);
    }
    
    return true;
}

// Handle remarks from non-super admins (these go to admin_remarks)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_superadmin && isset($_POST['add_remarks'])) {
    $submission_id = (int)$_POST['submission_id'];
    $remarks = trim($_POST['remarks']);

    if (!empty($remarks)) {
        $update_query = "UPDATE submissions SET admin_remarks = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $remarks, $submission_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Remarks added successfully!";
            header("Location: submissions.php?" . $submission_return_query);
            exit();
        } else {
            $_SESSION['error'] = "Error adding remarks: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Remarks cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GreenCredit - Manage Submissions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/submissions.css" />
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        .table-responsive { margin: 0 auto; max-width: 100%; overflow-x: auto; }
        table.table { margin-left: auto; margin-right: auto; width: 100%; max-width: 1400px; margin: 0 auto; }
        .sort-panel {
            border: 1px solid rgba(46,139,87,.14);
            background: linear-gradient(180deg, #ffffff 0%, #f7fbf8 100%);
            border-radius: 16px;
            padding: 1rem;
            margin: 1rem 0 1.25rem;
            box-shadow: 0 10px 24px rgba(16,24,40,.06);
        }
        .sort-panel-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            flex-wrap: wrap;
            margin-bottom: .85rem;
        }
        .sort-chip-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: .65rem;
        }
        .sort-chip {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .75rem .85rem;
            border: 1px solid #dde8df;
            border-radius: 12px;
            color: #263238;
            text-decoration: none;
            background: #fff;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .sort-chip:hover {
            transform: translateY(-2px);
            border-color: #2E8B57;
            box-shadow: 0 12px 24px rgba(46,139,87,.13);
            color: #1f6d43;
        }
        .sort-chip.active {
            border-color: #2E8B57;
            background: #eaf6ef;
            color: #145c35;
            font-weight: 700;
        }
        .sort-chip i {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #edf7f1;
            color: #2E8B57;
            flex: 0 0 auto;
        }
        .sort-chip small {
            display: block;
            color: #6c757d;
            font-weight: 500;
            margin-top: .1rem;
        }
        .evidence-btn { white-space: nowrap; }
        .file-icon { margin-right: 5px; }
        .pagination { justify-content: center; margin-top: 20px; }
        .page-info { text-align: center; margin: 10px 0; color: #6c757d; }
        .action-buttons { white-space: nowrap; }
        .bulk-actions { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        @media (max-width: 991.98px) {
            .container { padding: 30px 15px; }
            .row { display: flex; flex-direction: column; align-items: center; }
            .col-md-4, .col-md-8 { width: 100%; margin-bottom: 20px; }
            .table td, .table th { font-size: 0.9rem; padding: 0.5rem; }
        }
        @media (max-width: 768px) {
            .container { padding: 20px 10px; }
            h2.text-center { font-size: 1.5rem; }
            .table td, .table th { padding: 0.75rem 0.5rem; }
            .pagination .page-link { padding: 0.25rem 0.5rem; font-size: 0.875rem; }
            .sort-panel-title { align-items: flex-start; }
            .sort-chip-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 576px) {
            .container { padding: 15px 5px; }
            h2.text-center { font-size: 1.25rem; }
            .table td, .table th { padding: 0.5rem; }
            .pagination .page-item { margin: 0 2px; }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container my-5" style="box-shadow: 0 0 20px rgba(0,0,0,0.2); border-radius: 15px; padding: 40px 20px; background: #fff; width: 100%; max-width: 1400px; margin: 0 auto;">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0">Manage Submissions</h2>
            <div class="d-flex gap-2 flex-wrap">
                <a href="export_csv.php?table=submissions" class="btn btn-outline-success">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
                </a>
                <form method="GET" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sort_by) ?>">
                    <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sort_order) ?>">
                    <label for="per_page" class="mb-0 fw-semibold">Rows</label>
                    <select id="per_page" name="per_page" class="form-select" onchange="this.form.submit()">
                        <?php foreach (['10','25','50','100','all'] as $option): ?>
                            <option value="<?= $option ?>" <?= $per_page_param === $option ? 'selected' : '' ?>><?= $option === 'all' ? 'All rows' : $option . ' rows' ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <?php if ($is_superadmin): ?>
                <span class="badge bg-danger">Super Admin Mode</span>
            <?php else: ?>
                <span class="badge bg-info">Admin Mode</span>
            <?php endif; ?>
        </div>
        <div class="col-md-6 text-end">
            <div class="d-inline-flex align-items-center gap-2">
                <span class="badge bg-primary fs-6">Total Records: <?= number_format($total_rows) ?></span>
                <span class="badge bg-success fs-6">Sorted: <?= htmlspecialchars($active_submission_sort_label) ?></span>
            </div>
        </div>
    </div>

    <section class="sort-panel">
        <div class="sort-panel-title">
            <div>
                <h5 class="mb-1"><i class="fas fa-arrow-up-wide-short me-2 text-success"></i>Sort Submissions</h5>
                <div class="text-muted small">Sorts the full submissions list before pagination.</div>
            </div>
            <a class="btn btn-sm btn-outline-secondary" href="submissions.php?per_page=<?= urlencode($per_page_param) ?>">
                <i class="fas fa-rotate-left me-1"></i> Reset
            </a>
        </div>
        <?php foreach ($submission_sort_options as $sort_group): ?>
            <div class="mb-3">
                <div class="text-uppercase small fw-bold text-muted mb-2"><?= htmlspecialchars($sort_group['group']) ?></div>
                <div class="sort-chip-grid">
                    <?php foreach ($sort_group['items'] as $option):
                        [$option_by, $option_order, $option_title, $option_hint, $option_icon] = $option;
                        $is_active = ($sort_by === $option_by && $sort_order === $option_order);
                        $url = '?sort_by=' . urlencode($option_by) . '&sort_order=' . urlencode($option_order) . '&per_page=' . urlencode($per_page_param);
                    ?>
                        <a class="sort-chip <?= $is_active ? 'active' : '' ?>" href="<?= $url ?>">
                            <i class="fas <?= htmlspecialchars($option_icon) ?>"></i>
                            <span>
                                <?= htmlspecialchars($option_title) ?>
                                <small><?= htmlspecialchars($option_hint) ?></small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
        unset($_SESSION['success']);
    }
    ?>

    <!-- Bulk Actions (Super Admin Only) -->
    <?php if ($is_superadmin): ?>
    <div class="bulk-actions mb-4">
        <h5>Bulk Actions</h5>
        <form method="POST" id="bulkActionForm">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select class="form-select" name="bulk_action" id="bulkActionSelect" required>
                        <option value="">Select Action</option>
                        <option value="approve">Approve Selected</option>
                        <option value="reject">Reject Selected</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="bulk_remarks" class="form-control" placeholder="Optional shared remark for this bulk action">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Apply to Selected</button>
                </div>
                <div class="col-md-2 form-check text-end">
                    <input type="checkbox" class="form-check-input" id="selectAll">
                    <label class="form-check-label" for="selectAll">Select All</label>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <?php if ($is_superadmin): ?>
                    <th width="40">
                        <input type="checkbox" id="selectAllHeader">
                    </th>
                    <?php endif; ?>
                    <th>Submission ID</th>
                    <th>Club ID</th>
                    <th>Category</th>
                    <th>Action</th>
                    <th>Points</th>
                    <th>Status</th>
                    <th>Submitted Date</th>
                    <th>Verification Date</th>
                    <th>Team Number</th>
                    <th>Team Members</th>
                    <th>Submitted By</th>
                    <th>3ZERO Cluster</th>
                    <th>Evidence</th>
                    <th>Description</th>
                    <th>Admin Remarks</th>
                    <th>Superadmin Remarks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($total_rows > 0): ?>
                <?php while ($submission = $submissions_result->fetch_assoc()): 
                    // Team members
                    $team_members = json_decode($submission['team_members'], true);
                    if (!is_array($team_members)) { $team_members = []; }

                    // Verified date
                    $verified_date = $submission['verified_date'] ?? null;
                    $verified_date_formatted = ($verified_date && $verified_date !== '0000-00-00 00:00:00')
                        ? date('d M Y, H:i', strtotime($verified_date))
                        : '-';

                    // Evidence
                    $evidence = json_decode($submission['proof_image'], true);
                    $evidence = is_array($evidence) ? $evidence : (!empty($submission['proof_image']) ? [$submission['proof_image']] : []);
                    $modalId = "evidenceModalAdmin" . $submission['id'];

                    // Description trimmed/expandable
                    $description_raw = $submission['description'] ?? '-';
                    $description = htmlspecialchars($description_raw);
                    $max_length = 50;
                    $has_long_desc = strlen($description) > $max_length;
                    $short_description = $has_long_desc ? substr($description, 0, $max_length) . '...' : $description;
                    
                    // Remarks
                    $admin_remarks = $submission['admin_remarks'] ?? '';
                    $superadmin_remarks = $submission['superadmin_remarks'] ?? '';
                ?>
                <tr>
                    <?php if ($is_superadmin): ?>
                    <td>
                        <!-- IMPORTANT: form="bulkActionForm" ensures checkbox is included in bulk form submission -->
                        <input type="checkbox" class="submission-checkbox" name="selected_submissions[]" value="<?= (int)$submission['id'] ?>" form="bulkActionForm">
                    </td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($submission['id']); ?></td>
                    <td>
                        <?php 
                        if (strtolower($submission['category']) === 'low impact') {
                            echo '0';
                        } else {
                            echo htmlspecialchars($submission['club_id'] ?? '-');
                        }
                        ?>
                    </td>
                    <td><?= htmlspecialchars($submission['category']); ?></td>
                    <td><?= htmlspecialchars($submission['action']); ?></td>
                    <td><?= htmlspecialchars($submission['points']); ?></td>
                    <td>
                        <span class="badge 
                            <?php 
                            if ($submission['status'] == 'pending') echo 'bg-warning';
                            elseif ($submission['status'] == 'approved') echo 'bg-success';
                            else echo 'bg-danger';
                            ?>">
                            <?= ucfirst(htmlspecialchars($submission['status'])); ?>
                        </span>
                    </td>
                    <td><?= date('d M Y, H:i', strtotime($submission['created_at'])); ?></td>
                    <td><?= $verified_date_formatted; ?></td>
                    <td><?= htmlspecialchars($submission['team_number'] ?? '-'); ?></td>
                    <td>
                        <?php 
                        if (!empty($team_members)) {
                            $bold_names = array_map(function($name){ return "<strong>" . htmlspecialchars($name) . "</strong>"; }, $team_members);
                            echo implode(", ", $bold_names);
                        } else {
                            echo "-";
                        }
                        ?>
                    </td>
                    <td><?= htmlspecialchars($submission['submitter_name'] ?? 'Unknown'); ?></td>
                    <td><?= htmlspecialchars($submission['three_zero_cluster'] ?? '-'); ?></td>
                    <td>
                        <?php if (!empty($evidence)) { ?>
                            <button class="btn btn-sm btn-primary evidence-btn" data-bs-toggle="modal" data-bs-target="#<?= $modalId ?>">
                                View Evidence
                            </button>

                            <!-- Evidence Modal -->
                            <div class="modal fade" id="<?= $modalId ?>" tabindex="-1" aria-labelledby="<?= $modalId ?>Label" aria-hidden="true">
                              <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="<?= $modalId ?>Label">Evidence for Submission #<?= htmlspecialchars($submission['id']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <?php foreach ($evidence as $file):
                                        $file_ext = pathinfo($file, PATHINFO_EXTENSION);
                                        $file_path = "../user/uploads/" . htmlspecialchars($file);
                                    ?>
                                        <div class="mb-4">
                                            <?php if (in_array(strtolower($file_ext), ['jpg','jpeg','png','gif'])): ?>
                                                <img src="<?= $file_path ?>" alt="Evidence Image" class="img-fluid mb-2" style="max-height:300px;">
                                                <div class="text-center mt-2">
                                                    <a href="<?= $file_path ?>" download class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                </div>
                                            <?php elseif (strtolower($file_ext) === 'pdf'): ?>
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-file-pdf file-icon text-danger" style="font-size: 2rem;"></i>
                                                    <a href="<?= $file_path ?>" target="_blank" class="ms-2">View PDF</a>
                                                    <a href="<?= $file_path ?>" download class="btn btn-sm btn-outline-primary ms-3">
                                                        <i class="fas fa-download"></i> Download
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <a href="<?= $file_path ?>" download class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-download"></i> Download File
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                        <?php } else {
                            echo "No evidence submitted";
                        } ?>
                    </td>
                    <td>
                        <?php
                        $description_raw = $submission['description'] ?? '-';
                        $description = htmlspecialchars($description_raw);
                        $max_length = 50;
                        $has_long_desc = strlen($description) > $max_length;
                        $short_description = $has_long_desc ? substr($description, 0, $max_length) . '...' : $description;
                        ?>
                        
                        <?php if ($has_long_desc): ?>
                            <span class='short-description'><?= $short_description; ?></span>
                            <a href='description_view.php?id=<?= $submission['id'] ?>' class='read-more' target="_blank">
                                Read More
                            </a>
                        <?php else: ?>
                            <?= $description; ?>
                        <?php endif; ?>
                    </td>
                    <td><?= nl2br(htmlspecialchars($admin_remarks)); ?></td>
                    <td><?= nl2br(htmlspecialchars($superadmin_remarks)); ?></td>
                    <td class="action-buttons">
                        <?php if ($is_superadmin): ?>
                            <!-- Super Admin Actions -->
                            <div class="btn-group-vertical" role="group">
                                <button type="button" class="btn btn-sm btn-success mb-1" 
                                    onclick="showActionModal('approve', <?= $submission['id'] ?>, '<?= htmlspecialchars($superadmin_remarks) ?>')">
                                    Approve
                                </button>
                                <button type="button" class="btn btn-sm btn-danger mb-1" 
                                    onclick="showActionModal('reject', <?= $submission['id'] ?>, '<?= htmlspecialchars($superadmin_remarks) ?>')">
                                    Reject
                                </button>
                                <button type="button" class="btn btn-sm btn-warning mb-1" 
                                    onclick="showActionModal('delete', <?= $submission['id'] ?>, '')">
                                    Delete
                                </button>
                            </div>
                        <?php else: ?>
                            <!-- Admin Remarks Only -->
                            <button type="button" class="btn btn-sm btn-info" 
                                onclick="showRemarksModal(<?= $submission['id'] ?>, '<?= htmlspecialchars($admin_remarks) ?>')">
                                Add Remarks
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="<?= $is_superadmin ? 18 : 17 ?>" class="text-center">No submissions found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination Controls -->
    <?php if ($total_pages > 1): ?>
    <div class="page-info">
        Showing <?= (($page - 1) * $results_per_page) + 1 ?> to 
        <?= min($page * $results_per_page, $total_rows) ?> of <?= number_format($total_rows) ?> pages
    </div>
    
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <!-- Previous Page Link -->
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= build_submission_page_link($page - 1) ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            
            <!-- Page Number Links -->
            <?php
            // Show limited page numbers with ellipsis
            $max_visible_pages = 5;
            $start_page = max(1, $page - floor($max_visible_pages / 2));
            $end_page = min($total_pages, $start_page + $max_visible_pages - 1);
            
            // Adjust if we're near the end
            if ($end_page - $start_page < $max_visible_pages - 1) {
                $start_page = max(1, $end_page - $max_visible_pages + 1);
            }
            
            // First page and ellipsis if needed
            if ($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="' . build_submission_page_link(1) . '">1</a></li>';
                if ($start_page > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            // Page numbers
            for ($i = $start_page; $i <= $end_page; $i++) {
                $active = $i == $page ? 'active' : '';
                echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . build_submission_page_link($i) . '">' . $i . '</a></li>';
            }
            
            // Last page and ellipsis if needed
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="' . build_submission_page_link($total_pages) . '">' . $total_pages . '</a></li>';
            }
            ?>
            
            <!-- Next Page Link -->
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= build_submission_page_link($page + 1) ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Action Modal for Super Admins -->
<div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="actionForm">
                <div class="modal-body">
                    <input type="hidden" name="action_type" id="actionType">
                    <input type="hidden" name="submission_id" id="submissionId">
                    <p id="actionMessage"></p>
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks (Optional)</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="confirmActionBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remarks Modal for Admins -->
<div class="modal fade" id="remarksModal" tabindex="-1" aria-labelledby="remarksModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="remarksModalLabel">Add Remarks</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="submission_id" id="remarksSubmissionId">
                <input type="hidden" name="add_remarks" value="1">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="adminRemarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="adminRemarks" name="remarks" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Remarks</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
    // Read More / Read Less
    document.querySelectorAll('.read-more').forEach(function(link) {
        link.addEventListener('click', function() {
            const fullDesc  = this.previousElementSibling;
            const shortDesc = fullDesc.previousElementSibling;
            if (fullDesc.style.display === 'none' || fullDesc.style.display === '') {
                fullDesc.style.display = 'inline';
                shortDesc.style.display = 'none';
                this.textContent = 'Read Less';
            } else {
                fullDesc.style.display = 'none';
                shortDesc.style.display = 'inline';
                this.textContent = 'Read More';
            }
        });
    });

    // Super Admin Action Modal
    function showActionModal(actionType, submissionId, currentRemarks) {
        const modalEl = document.getElementById('actionModal');
        const modal = new bootstrap.Modal(modalEl);
        const actionLabels = {
            'approve': 'Approve',
            'reject': 'Reject',
            'delete': 'Delete'
        };

        const safeRemarks = (currentRemarks || '').replace(/&lt;/g, "<").replace(/&gt;/g, ">");

        document.getElementById('actionType').value = actionType;
        document.getElementById('submissionId').value = submissionId;
        document.getElementById('remarks').value = safeRemarks;
        document.getElementById('actionMessage').textContent =
            `Are you sure you want to ${actionType} submission #${submissionId}?`;
        document.getElementById('confirmActionBtn').textContent = `Confirm ${actionLabels[actionType]}`;
        document.getElementById('confirmActionBtn').className =
            actionType === 'approve' ? 'btn btn-success' :
            actionType === 'reject' ? 'btn btn-danger' : 'btn btn-warning';

        modal.show();
    }

    // Admin Remarks Modal
    function showRemarksModal(submissionId, currentRemarks) {
        const modal = new bootstrap.Modal(document.getElementById('remarksModal'));
        const safeRemarks = (currentRemarks || '').replace(/&lt;/g, "<").replace(/&gt;/g, ">");
        document.getElementById('remarksSubmissionId').value = submissionId;
        document.getElementById('adminRemarks').value = safeRemarks;
        modal.show();
    }

    // Bulk Actions confirmation
    function getBulkActionMessage() {
        const selectedCount = document.querySelectorAll('.submission-checkbox:checked').length;
        const action = document.getElementById('bulkActionSelect').value;

        if (selectedCount === 0) {
            alert('Please select at least one submission.');
            return null;
        }

        const actionLabels = {
            'approve': 'approve',
            'reject': 'reject',
            'delete': 'delete'
        };

        return `Are you sure you want to ${actionLabels[action]} ${selectedCount} submission(s)?`;
    }

    document.getElementById('bulkActionForm')?.addEventListener('submit', function(event) {
        if (this.dataset.confirmed === 'true') return;
        event.preventDefault();
        const message = getBulkActionMessage();
        if (!message) return;
        const submitForm = () => {
            this.dataset.confirmed = 'true';
            this.submit();
        };
        if (window.showConfirmModal) {
            window.showConfirmModal(message, submitForm);
        } else if (confirm(message)) {
            submitForm();
        }
    });

    // Select All functionality (two checkboxes in header and bulk area)
    document.getElementById('selectAllHeader')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.submission-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        document.getElementById('selectAll').checked = this.checked;
    });

    document.getElementById('selectAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.submission-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        document.getElementById('selectAllHeader').checked = this.checked;
    });

    // Update header checkbox when individual checkboxes change
    document.querySelectorAll('.submission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = document.querySelectorAll('.submission-checkbox:checked').length ===
                              document.querySelectorAll('.submission-checkbox').length;
            document.getElementById('selectAllHeader').checked = allChecked;
            document.getElementById('selectAll').checked = allChecked;
        });
    });
</script>
</body>
</html>
