<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
} else {
    $_SESSION['error'] = "User data not found!";
    header('Location: ../login.php');
    exit();
}

$user_name = $user_data['name'];

// Fetch submissions where user is submitter OR team member (team_members JSON contains user's name)
$submission_query = "
    SELECT * FROM submissions
    WHERE user_id = ? 
    OR JSON_CONTAINS(team_members, '\"". $conn->real_escape_string($user_name) ."\"')
    ORDER BY created_at DESC
";

$submission_stmt = $conn->prepare($submission_query);
if ($submission_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$submission_stmt->bind_param("i", $user_id);
$submission_stmt->execute();
$submission_result = $submission_stmt->get_result();

// Calculate total points (submitter + team member)
$total_points = 0;
foreach ($submission_result as $sub) {
    $total_points += $sub['points'];
}
// Reset pointer for later fetch_assoc() usage
$submission_result->data_seek(0);
// Data for charts
$category_counts = [];
$submission_points = [];
$submission_labels = [];

foreach ($submission_result as $submission) {
    // Chart 1: Count submissions per category
    $category = $submission['category'];
    if (!isset($category_counts[$category])) {
        $category_counts[$category] = 0;
    }
    $category_counts[$category]++;

    // Chart 2: Points per submission (label as "Category - Date")
    $submission_points[] = (int)$submission['points'];
    $label = $submission['category'] . ' (' . date('d M', strtotime($submission['created_at'])) . ')';
    $submission_labels[] = $label;
}
$submission_result->data_seek(0);

// ==== Compute user rank across all users ====
// Build users map
$__users = [];
$__res = $conn->query("SELECT id, name FROM users");
while ($__r = $__res->fetch_assoc()) {
    $__users[(int)$__r['id']] = ['name' => $__r['name'], 'points' => 0];
}

// Submitted By Logic
$idToName = [];
foreach ($__users as $id => $u) {
    $idToName[$id] = $u['name'];
}

// Name -> ID map for teammate matching by name
$__nameToId = [];
foreach ($__users as $__id => $__u) {
    $__nameToId[$__u['name']] = $__id;
}

// Aggregate points for submitters + teammates
$__allSubs = $conn->query("SELECT user_id, points, team_members FROM submissions");
if ($__allSubs) {
    while ($__row = $__allSubs->fetch_assoc()) {
        $__submitter = (int)$__row['user_id'];
        $__pts       = (int)$__row['points'];

        if (isset($__users[$__submitter])) {
            $__users[$__submitter]['points'] += $__pts;
        }

        $__team = json_decode($__row['team_members'], true);
        if (is_array($__team)) {
            foreach ($__team as $__memberName) {
                if (isset($__nameToId[$__memberName])) {
                    $__mid = (int)$__nameToId[$__memberName];
                    if ($__mid !== $__submitter) {
                        $__users[$__mid]['points'] += $__pts;
                    }
                }
            }
        }
    }
}

// Sort by points desc and find this user's rank (only users with > 0 points)
$__sorted = $__users;
uasort($__sorted, fn($a, $b) => $b['points'] <=> $a['points']);

$user_rank = 0;
$__pos = 0;
foreach ($__sorted as $__uid => $__urow) {
    if ((int)$__urow['points'] <= 0) continue;
    $__pos++;
    if ((int)$__uid === (int)$user_id) {
        $user_rank = $__pos;
        break;
    }
}
// If $user_rank stays 0, user has no points yet (no rank)

// Chart 3: Submission Trend Over Time
$submission_trend = [];

foreach ($submission_result as $submission) {
    $date = date('Y-m-d', strtotime($submission['created_at']));
    if (!isset($submission_trend[$date])) {
        $submission_trend[$date] = 0;
    }
    $submission_trend[$date]++;
}
ksort($submission_trend);
$submission_result->data_seek(0);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>GreenCredit - User Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="assets/css/styles.css" />
<link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
<style>
    .table tbody tr:nth-child(1) td:first-child::before {
            content: '';
            margin-right: 8px;
        }

    .table tbody tr:nth-child(2) td:first-child::before {
        content: '';
        margin-right: 8px;
    }

    .table tbody tr:nth-child(3) td:first-child::before {
        content: '';
        margin-right: 8px;
    }
    
    .table-responsive {
        margin: 0 auto;
        max-width: 100%;
        overflow-x: auto;
    }
    table.table {
        margin-left: auto;
        margin-right: auto;
        width: 100%;
        max-width: 1000px; 
    }
    #preloader {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #fff;
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    body.loaded #preloader {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    
    .evidence-btn {
        white-space: nowrap;
    }
    
    .file-icon {
        margin-right: 5px;
    }
     .table tbody tr:nth-child(1) td:first-child::before {
            content: '';
            margin-right: 8px;
    }

    .table tbody tr:nth-child(2) td:first-child::before {
            content: '';
            margin-right: 8px;
    }

    .table tbody tr:nth-child(3) td:first-child::before {
            content: '';
            margin-right: 8px;
    }

    table {
            margin-bottom: 20px;
    }
    th, td {
            text-align: center;
    }
    @media (max-width: 991.98px) {
        /* For larger tablets and up */
        .container {
            padding: 30px 15px;
        }

        /* Column adjustments for profile and actions */
        .row {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .col-md-4, .col-md-8 {
            width: 100%;
            margin-bottom: 20px;
        }

        /* Submission table adjustments */
        .table td,
        .table th {
            font-size: 0.9rem;
            padding: 0.5rem;
        }
    }

    @media (max-width: 768px) {
        /* For tablets and small screens */
        .container {
            padding: 20px 10px;
        }

        /* Adjust profile image */
        .rounded-circle {
            width: 100px;
            height: 100px;
        }

        h2.text-center {
            font-size: 1.5rem;
        }

        /* Adjust the actions list layout */
        .list-group-item {
            padding: 0.75rem 1rem;
        }

        /* Submissions table adjustments */
        .table td,
        .table th {
            padding: 0.75rem 0.5rem;
        }
    }

    @media (max-width: 576px) {
        /* For mobile screens */
        .container {
            padding: 15px 5px;
        }

        /* Adjust profile image size */
        .rounded-circle {
            width: 80px;
            height: 80px;
        }

        h2.text-center {
            font-size: 1.25rem;
        }

        /* Table adjustments */
        .table td,
        .table th {
            padding: 0.5rem;
        }

        /* Modal adjustments */
        .modal-body img {
            max-height: 50vh;
        }

        .list-group-item {
            padding: 0.75rem 1rem;
        }
    }
    .rounded-circle {
    border: none !important;
    }

    .dashboard-chatbot {
        background: #ffffff;
        border: 1px solid rgba(46, 125, 50, 0.14);
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.14);
        padding: 28px;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }

    .chatbot-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 18px;
    }

    .chatbot-title {
        margin: 0;
        color: #2e7d32;
        font-weight: 700;
    }

    .chatbot-subtitle {
        margin: 6px 0 0;
        color: #667085;
        font-size: 0.95rem;
    }

    .chatbot-badge {
        background: #e8f5e9;
        color: #2e7d32;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 0.82rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .chatbot-window {
        background: #f8faf9;
        border: 1px solid #e4ebe5;
        border-radius: 12px;
        padding: 16px;
    }

    .chatbot-messages {
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-height: 210px;
        max-height: 320px;
        overflow-y: auto;
        padding-right: 4px;
        margin-bottom: 14px;
    }

    .chat-message {
        max-width: 82%;
        border-radius: 14px;
        padding: 10px 13px;
        line-height: 1.45;
        font-size: 0.95rem;
    }

    .chat-message.bot {
        align-self: flex-start;
        background: #ffffff;
        border: 1px solid #dbe7dd;
        color: #25352b;
    }

    .chat-message.user {
        align-self: flex-end;
        background: #2e7d32;
        color: #ffffff;
    }

    .chatbot-suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }

    .chatbot-suggestion {
        border: 1px solid #b7d8bd;
        background: #ffffff;
        color: #2e7d32;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 0.88rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .chatbot-suggestion:hover,
    .chatbot-suggestion:focus {
        background: #e8f5e9;
        border-color: #2e7d32;
    }

    .chatbot-form {
        display: flex;
        gap: 10px;
    }

    .chatbot-form input {
        border-radius: 10px;
        border: 1px solid #ccd8cf;
        min-height: 44px;
    }

    .chatbot-form button {
        border-radius: 10px;
        min-width: 96px;
        background: #2e7d32;
        border-color: #2e7d32;
        font-weight: 700;
    }

    .chatbot-form button:hover {
        background: #256b2a;
        border-color: #256b2a;
    }

    @media (max-width: 576px) {
        .dashboard-chatbot {
            padding: 20px 14px;
        }

        .chatbot-header,
        .chatbot-form {
            flex-direction: column;
        }

        .chatbot-badge {
            align-self: flex-start;
        }

        .chat-message {
            max-width: 94%;
        }

        .chatbot-form button {
            width: 100%;
        }
    }

</style>
</head>
<body>
<!-- Preloader -->
<div id="preloader">
  <div class="spinner-border text-success" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>

<?php include 'includes/header.php'; ?>

<div class="container my-5" style="box-shadow: 0 0 20px rgba(0,0,0,0.2); border-radius: 15px; padding: 40px 20px; background: #fff; width: 100%; max-width: 1200px; margin: 0 auto;">
    <h2 class="text-center mb-4">Welcome to Your Dashboard, <?= htmlspecialchars($user_data['name']); ?>!</h2>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="../uploads/<?= (empty($user_data['profile_pic']) || $user_data['profile_pic'] == 'default-profile.jpg') ? 'default-profile.jpg' : htmlspecialchars($user_data['profile_pic']); ?>" 
            alt="Profile Picture" class="rounded-circle mb-3" width="120" height="120">
            <h4>Name: <?= htmlspecialchars($user_data['name']); ?></h4>
            <p><strong>Points Earned: </strong><?= htmlspecialchars($total_points); ?></p>
            <p><strong>Rank: </strong><?= $user_rank ? '#'.htmlspecialchars($user_rank) : '-' ?></p>
            <p><strong>User ID: </strong><?= htmlspecialchars($user_id); ?></p>
        </div>

        <div class="col-md-8">
            <h4>Your Actions</h4>
            <div class="list-group">
                <a href="submit_item.php" class="list-group-item list-group-item-action">Submit Item</a>
                <a href="leaderboard.php" class="list-group-item list-group-item-action">View Leaderboard</a>
                <a href="history.php" class="list-group-item list-group-item-action">View Submission History</a>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <h4>Your 5 Recent Submissions</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Submission ID</th>
                            <th>Club ID</th>
                            <th>Category</th>
                            <th>Action</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Submitted Date</th>
                            <th>Verification Date by Admin</th>
                            <th>Team Number</th>
                            <th>Team Members</th>
                            <th>Submitted By</th>
                            <th>3ZERO Cluster</th>
                            <th>Evidence</th>
                            <th>Description</th>
                            <th>Admin Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 0;
                        while ($submission = $submission_result->fetch_assoc()) { 
                            if ($count >= 5) break;
                            $count++;
                            
                            // Decode team members JSON array (names)
                            $team_members = json_decode($submission['team_members'], true);
                            if (!is_array($team_members)) {
                                $team_members = [];
                            }

                            // Format verified date
                            $verified_date = $submission['verified_date'] ?? null;
                            if ($verified_date && $verified_date !== '0000-00-00 00:00:00') {
                                $verified_date_formatted = date('d M Y, H:i', strtotime($verified_date));
                            } else {
                                $verified_date_formatted = '-';
                            }

                            // Handle evidence (proof_image) - can be images or PDF
                            $evidence = json_decode($submission['proof_image'], true);
                            $modalId = "evidenceModal" . $submission['id'];
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($submission['id']); ?></td>
                                <td>
                                    <?php 
                                    if ($submission['category'] == 'low impact') {
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
                                <td><?= $verified_date_formatted ?></td>
                                <td><?= htmlspecialchars($submission['team_number']); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($team_members)) {
                                        $bold_names = array_map(function($name) {
                                            return "<strong>" . htmlspecialchars($name) . "</strong>";
                                        }, $team_members);
                                        echo implode(", ", $bold_names);
                                    } else {
                                        echo "-";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                      $submitterName = $idToName[(int)$submission['user_id']] ?? 'Unknown';
                                      echo htmlspecialchars($submitterName);
                                    ?>
                                 </td>
                                <td><?= htmlspecialchars($submission['three_zero_cluster'] ?? '-'); ?></td>
                                <td>
                                    <?php if (!empty($evidence) && is_array($evidence)) { ?>
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
                                                <?php foreach ($evidence as $file) { 
                                                    $file_ext = pathinfo($file, PATHINFO_EXTENSION);
                                                    $file_path = "uploads/" . htmlspecialchars($file);
                                                ?>
                                                    <div class="mb-4">
                                                        <?php if (in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                                            <img src="<?= $file_path ?>" alt="Evidence Image" class="img-fluid mb-2" style="max-height:300px;">
                                                        <?php elseif (strtolower($file_ext) == 'pdf'): ?>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="fas fa-file-pdf file-icon text-danger" style="font-size: 2rem;"></i>
                                                                <a href="<?= $file_path ?>" target="_blank" class="ms-2">View PDF</a>
                                                            </div>
                                                        <?php else: ?>
                                                            <a href="<?= $file_path ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Download File</a>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php } ?>
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
                                <td>
                                    <?php
                                    $admin_remarks = htmlspecialchars($submission['superadmin_remarks'] ?? '');
                                    $max_length = 20;
                                    
                                    if (strlen($admin_remarks) > $max_length) {
                                        $short_remarks = substr($admin_remarks, 0, $max_length) . '...';
                                        echo "<span class='short-remarks'>$short_remarks</span>";
                                        echo "<span class='full-remarks' style='display:none;'>$admin_remarks</span>";
                                        echo "<a href='javascript:void(0)' class='read-more'>Read More</a>";
                                    } else {
                                        echo $admin_remarks;
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="container my-5 dashboard-chatbot">
    <div class="chatbot-header">
        <div>
            <h4 class="chatbot-title"><i class="fas fa-seedling me-2"></i>GreenCredit Help Assistant</h4>
            <p class="chatbot-subtitle">Ask quick questions about submissions, points, proof rules, rewards, and dashboard features.</p>
        </div>
        <span class="chatbot-badge">Local Help Bot</span>
    </div>

    <div class="chatbot-window" aria-label="GreenCredit chatbot">
        <div class="chatbot-messages" id="chatbotMessages" aria-live="polite">
            <div class="chat-message bot">
                Hi! I can help with eco points, impact categories, proof uploads, 3ZERO clusters, status, rewards, profile updates, the sustainability calculator, contact admin, and guidelines.
            </div>
        </div>

        <div class="chatbot-suggestions" aria-label="Suggested questions">
            <button type="button" class="chatbot-suggestion" data-question="How to submit an eco-friendly action?">Submit action</button>
            <button type="button" class="chatbot-suggestion" data-question="Explain impact categories">Impact categories</button>
            <button type="button" class="chatbot-suggestion" data-question="When is Club ID required?">Club ID</button>
            <button type="button" class="chatbot-suggestion" data-question="What proof files are allowed?">Proof rules</button>
            <button type="button" class="chatbot-suggestion" data-question="How to check submission status?">Status</button>
            <button type="button" class="chatbot-suggestion" data-question="How to view the guidelines?">Guidelines</button>
        </div>

        <form class="chatbot-form" id="chatbotForm" autocomplete="off">
            <input type="text" class="form-control" id="chatbotInput" placeholder="Type a help question..." aria-label="Type a help question">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane me-1"></i> Send
            </button>
        </form>
    </div>
</div>

<div class="container my-5" style="box-shadow: 0 0 20px rgba(0,0,0,0.2); border-radius: 15px; padding: 40px 20px; background: #fff; width: 100%; max-width: 1200px; margin: 0 auto;">
    <h4 class="text-center mb-4">Your Submission Insights</h4>
    <div class="row">
        <div class="col-md-6">
            <h5 class="text-center">Submission Categories</h5>
            <canvas id="categoryChart"></canvas>
        </div>
        <div class="col-md-6">
            <h5 class="text-center">Points Per Submission</h5>
            <canvas id="pointsChart"></canvas>
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-12">
            <h5 class="text-center">Your Submission Trend Over Time</h5>
            <canvas id="submissionTrendChart"></canvas>
        </div>
    </div>
    
    <div class="text-center my-4">
        <a href="download_submissions_csv.php" class="btn btn-outline-primary">
            <i class="bi bi-download"></i> Download Submissions as CSV
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
    const categoryLabels = <?= json_encode(array_keys($category_counts)) ?>;
    const categoryData = <?= json_encode(array_values($category_counts)) ?>;

    const submissionLabels = <?= json_encode($submission_labels) ?>;
    const submissionPoints = <?= json_encode($submission_points) ?>;

    // Chart 1: Category Distribution Pie Chart
    new Chart(document.getElementById('categoryChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryData,
                backgroundColor: ['#4caf50', '#2196f3', '#ff9800', '#e91e63', '#9c27b0'],
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Chart 2: Points Per Submission Bar Chart
    new Chart(document.getElementById('pointsChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: submissionLabels,
            datasets: [{
                label: 'Points',
                data: submissionPoints,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    ticks: {
                        maxRotation: 90,
                        minRotation: 45
                    }
                }
            }
        }
    });
    
    const trendDates = <?= json_encode(array_keys($submission_trend)) ?>;
    const trendCounts = <?= json_encode(array_values($submission_trend)) ?>;

    new Chart(document.getElementById('submissionTrendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: trendDates,
            datasets: [{
                label: 'Number of Submissions',
                data: trendCounts,
                fill: false,
                borderColor: 'rgba(255, 99, 132, 1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Submissions'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
    
    window.addEventListener('load', function () {
        setTimeout(function () {
            document.body.classList.add('loaded');
        }, 1000);
    });
    
    // Add event listeners to all "Read More" links
    document.querySelectorAll('.read-more').forEach(function(link) {
        link.addEventListener('click', function() {
            var shortDesc = this.previousElementSibling.previousElementSibling;
            var fullDesc = this.previousElementSibling;
            
            // Toggle visibility of short and full descriptions
            if (fullDesc.style.display === 'none') {
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

    const chatbotForm = document.getElementById('chatbotForm');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotMessages = document.getElementById('chatbotMessages');
    const chatbotSuggestions = document.querySelectorAll('.chatbot-suggestion');

    const chatbotAnswers = [
        {
            keywords: ['submit', 'eco-friendly', 'eco friendly', 'action', 'submission'],
            answer: 'Go to Submit Item from Your Actions. Choose an impact category, select one 3ZERO cluster, add details, upload valid proof, then submit.'
        },
        {
            keywords: ['how many', 'impact categories', 'category', 'categories'],
            answer: 'There are 3 impact categories: Low Impact, Medium Impact, and High Impact.'
        },
        {
            keywords: ['low impact', '25'],
            answer: 'Low Impact actions are worth 25 points. They are usually individual or smaller eco-friendly activities.'
        },
        {
            keywords: ['medium impact', '50'],
            answer: 'Medium Impact actions are worth 50 points. Club ID is required, and the team must have 3 to 5 members including you.'
        },
        {
            keywords: ['high impact', '75'],
            answer: 'High Impact actions are worth 75 points. Club ID is required, and the team must have 3 to 5 members including you.'
        },
        {
            keywords: ['club id', 'clubid'],
            answer: 'Club ID is required for Medium Impact and High Impact submissions. It is not required for Low Impact.'
        },
        {
            keywords: ['team', 'member', 'members'],
            answer: 'Team members are required for Medium and High Impact actions. Select 2 to 4 additional members, so the total team size is 3 to 5 including you.'
        },
        {
            keywords: ['proof', 'upload', 'file', 'files', 'image', 'pdf'],
            answer: 'Allowed proof: 2 to 5 images in PNG, JPG, JPEG, or GIF format, or exactly 1 PDF. Do not mix PDF with images. Max size is 15 MB per file.'
        },
        {
            keywords: ['status', 'pending', 'approved', 'rejected', 'check'],
            answer: 'Check your submission status in View Submission History. Status can be pending, approved, or rejected.'
        },
        {
            keywords: ['delete', 'remove'],
            answer: 'You can delete a submission only while it is pending. Go to View Submission History and use the Delete button for that pending submission.'
        },
        {
            keywords: ['leaderboard', 'rank', 'ranking'],
            answer: 'Open View Leaderboard to see top users ranked by approved submission points and team participation.'
        },
        {
            keywords: ['reward', 'rewards'],
            answer: 'Open Your Rewards to view rewards assigned by admins for your submissions.'
        },
        {
            keywords: ['profile', 'update profile', 'picture', 'password'],
            answer: 'Use the Profile menu to update your personal details, profile picture, or password.'
        },
        {
            keywords: ['sustainability', 'calculator', 'score'],
            answer: 'Open Sustainability Calculator from the tools menu. Enter your water usage, reusable items, and walking days to calculate and track your score.'
        },
        {
            keywords: ['contact', 'admin', 'message', 'response'],
            answer: 'Use Contact to send a message to admin. You can view replies in Admin Responses from the tools menu.'
        },
        {
            keywords: ['3zero', '3 zero', 'cluster', 'zero poverty', 'zero unemployment', 'zero net carbon'],
            answer: 'Choose one 3ZERO cluster for each submission: Zero Poverty, Zero Unemployment, or Zero Net Carbon Emission.'
        },
        {
            keywords: ['guideline', 'guidelines', 'view guidelines'],
            answer: 'To view the guidelines, open the Guidelines page from the main website navigation, or go to ../guidelines.php from this user area.'
        },
        {
            keywords: ['eco point', 'eco points', 'points'],
            answer: 'Eco points come from submitted actions. Low Impact gives 25 points, Medium Impact gives 50 points, and High Impact gives 75 points after admin review.'
        }
    ];

    function addChatMessage(text, sender) {
        const message = document.createElement('div');
        message.className = 'chat-message ' + sender;
        message.textContent = text;
        chatbotMessages.appendChild(message);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function getChatbotAnswer(question) {
        const normalizedQuestion = question.toLowerCase();
        const match = chatbotAnswers.find(item =>
            item.keywords.some(keyword => normalizedQuestion.includes(keyword))
        );

        if (match) {
            return match.answer;
        }

        return 'I can help with submissions, eco points, impact categories, proof rules, Club ID, team members, status, leaderboard, rewards, profile updates, calculator, contact admin, and guidelines.';
    }

    function askChatbot(question) {
        const cleanQuestion = question.trim();
        if (!cleanQuestion) return;

        addChatMessage(cleanQuestion, 'user');
        addChatMessage(getChatbotAnswer(cleanQuestion), 'bot');
        chatbotInput.value = '';
        chatbotInput.focus();
    }

    if (chatbotForm && chatbotInput && chatbotMessages) {
        chatbotForm.addEventListener('submit', function(event) {
            event.preventDefault();
            askChatbot(chatbotInput.value);
        });

        chatbotSuggestions.forEach(function(button) {
            button.addEventListener('click', function() {
                askChatbot(this.dataset.question || this.textContent);
            });
        });
    }
</script>
</body>
</html>
