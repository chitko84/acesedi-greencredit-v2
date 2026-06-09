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
    if (strtolower(trim($sub['status'] ?? '')) === 'approved') {
        $total_points += (int)$sub['points'];
    }
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
    $submission_points[] = strtolower(trim($submission['status'] ?? '')) === 'approved' ? (int)$submission['points'] : 0;
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
$__allSubs = $conn->query("SELECT user_id, points, team_members FROM submissions WHERE LOWER(TRIM(status)) = 'approved'");
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

    .chatbot-widget {
        position: fixed;
        right: 22px;
        bottom: 22px;
        z-index: 1080;
        font-family: inherit;
    }

    .chatbot-toggle {
        width: 64px;
        height: 64px;
        border: 0;
        border-radius: 50%;
        background: linear-gradient(135deg, #2e7d32, #113f19);
        color: #ffffff;
        box-shadow: 0 14px 32px rgba(17, 63, 25, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.55rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .chatbot-toggle:hover,
    .chatbot-toggle:focus {
        transform: translateY(-2px);
        box-shadow: 0 18px 38px rgba(17, 63, 25, 0.42);
        outline: none;
    }

    .chatbot-panel {
        position: absolute;
        right: 0;
        bottom: 78px;
        width: min(380px, calc(100vw - 28px));
        max-height: min(620px, calc(100vh - 112px));
        background: #ffffff;
        border: 1px solid rgba(46, 125, 50, 0.18);
        border-radius: 16px;
        box-shadow: 0 22px 55px rgba(15, 42, 20, 0.28);
        overflow: hidden;
        display: none;
    }

    .chatbot-panel.is-open {
        display: flex;
        flex-direction: column;
    }

    .chatbot-header {
        background: linear-gradient(135deg, #2e7d32, #143f1b);
        color: #ffffff;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .chatbot-title-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .chatbot-title-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.18);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .chatbot-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.2;
    }

    .chatbot-subtitle {
        margin: 2px 0 0;
        font-size: 0.78rem;
        opacity: 0.86;
        line-height: 1.2;
    }

    .chatbot-close {
        border: 0;
        background: rgba(255, 255, 255, 0.14);
        color: #ffffff;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
    }

    .chatbot-close:hover,
    .chatbot-close:focus {
        background: rgba(255, 255, 255, 0.24);
        outline: none;
    }

    .chatbot-messages {
        display: flex;
        flex-direction: column;
        gap: 10px;
        height: 300px;
        overflow-y: auto;
        padding: 14px;
        background: #f6faf7;
    }

    .chat-message {
        max-width: 86%;
        border-radius: 14px;
        padding: 9px 12px;
        line-height: 1.42;
        font-size: 0.9rem;
        word-wrap: break-word;
    }

    .chat-message.bot {
        align-self: flex-start;
        background: #ffffff;
        border: 1px solid #dce9df;
        color: #223128;
    }

    .chat-message.user {
        align-self: flex-end;
        background: #2e7d32;
        color: #ffffff;
    }

    .chatbot-suggestions {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        padding: 12px 14px 10px;
        border-top: 1px solid #e4ebe5;
        background: #ffffff;
    }

    .chatbot-suggestion {
        border: 1px solid #b7d8bd;
        background: #ffffff;
        color: #2e7d32;
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 0.8rem;
        font-weight: 700;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .chatbot-suggestion:hover,
    .chatbot-suggestion:focus {
        background: #e8f5e9;
        border-color: #2e7d32;
        outline: none;
    }

    .chatbot-form {
        display: flex;
        gap: 8px;
        padding: 12px 14px 14px;
        background: #ffffff;
    }

    .chatbot-form input {
        border-radius: 999px;
        border: 1px solid #ccd8cf;
        min-height: 42px;
        font-size: 0.9rem;
        padding-left: 14px;
    }

    .chatbot-form button {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #2e7d32;
        border-color: #2e7d32;
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
    }

    .chatbot-form button:hover,
    .chatbot-form button:focus {
        background: #256b2a;
        border-color: #256b2a;
        outline: none;
    }

    @media (max-width: 576px) {
        .chatbot-widget {
            right: 14px;
            bottom: 14px;
        }

        .chatbot-toggle {
            width: 58px;
            height: 58px;
            font-size: 1.35rem;
        }

        .chatbot-panel {
            right: 0;
            bottom: 70px;
            width: calc(100vw - 28px);
            max-height: calc(100vh - 96px);
            border-radius: 14px;
        }

        .chatbot-messages {
            height: min(300px, calc(100vh - 290px));
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
                            $display_points = strtolower(trim($submission['status'] ?? '')) === 'approved' ? (int)$submission['points'] : 0;
                            
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
                                <td><?= htmlspecialchars($display_points); ?></td>
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

<div class="chatbot-widget" aria-label="GreenCredit Help Assistant">
    <div class="chatbot-panel" id="chatbotPanel" aria-hidden="true">
        <div class="chatbot-header">
            <div class="chatbot-title-wrap">
                <span class="chatbot-title-icon"><i class="fas fa-leaf"></i></span>
                <div>
                    <h4 class="chatbot-title">GreenCredit Help</h4>
                    <p class="chatbot-subtitle">Local help assistant</p>
                </div>
            </div>
            <button type="button" class="chatbot-close" id="chatbotClose" aria-label="Minimize chat">
                <i class="fas fa-minus"></i>
            </button>
        </div>

        <div class="chatbot-messages" id="chatbotMessages" aria-live="polite">
            <div class="chat-message bot">
                Hi! Ask me about submissions, points, 3ZERO clusters, proof files, status, rewards, profile, calculator, contact, news, or guidelines.
            </div>
        </div>

        <div class="chatbot-suggestions" aria-label="Suggested questions">
            <button type="button" class="chatbot-suggestion" data-question="How do I submit an eco-friendly action?">Submit action</button>
            <button type="button" class="chatbot-suggestion" data-question="How many impact categories are available?">Categories</button>
            <button type="button" class="chatbot-suggestion" data-question="When is Club ID required?">Club ID</button>
            <button type="button" class="chatbot-suggestion" data-question="What proof files are allowed?">Proof files</button>
            <button type="button" class="chatbot-suggestion" data-question="Where can I check submission status?">Status</button>
            <button type="button" class="chatbot-suggestion" data-question="Where can I view rewards?">Rewards</button>
            <button type="button" class="chatbot-suggestion" data-question="Where can I view points guidelines?">Guidelines</button>
            <button type="button" class="chatbot-suggestion" data-question="How do I contact admin?">Contact admin</button>
        </div>

        <form class="chatbot-form" id="chatbotForm" autocomplete="off">
            <input type="text" class="form-control" id="chatbotInput" placeholder="Ask a quick question..." aria-label="Type a help question">
            <button type="submit" class="btn btn-primary" aria-label="Send message">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>

    <button type="button" class="chatbot-toggle" id="chatbotToggle" aria-label="Open GreenCredit help chat" aria-expanded="false">
        <i class="fas fa-comments"></i>
    </button>
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

    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotPanel = document.getElementById('chatbotPanel');
    const chatbotClose = document.getElementById('chatbotClose');
    const chatbotForm = document.getElementById('chatbotForm');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotMessages = document.getElementById('chatbotMessages');
    const chatbotSuggestions = document.querySelectorAll('.chatbot-suggestion');

    const chatbotAnswers = [
        { question: 'How do I submit an eco-friendly action?', keywords: ['how submit', 'submit action', 'submit activity', 'eco friendly action', 'where submit action'], answer: 'Open Submit Item, choose a category, add details, upload proof, and submit.' },
        { question: 'Where is the submission page?', keywords: ['submission page', 'submit page', 'where submit', 'submit item'], answer: 'Use Submit Item in Your Actions on this dashboard.' },
        { question: 'What information is required for submission?', keywords: ['required submission', 'submission information', 'what need submit', 'details required'], answer: 'You need category, action, 3ZERO cluster, description, proof files, and team/Club ID if required.' },
        { question: 'Can I edit a submission?', keywords: ['edit submission', 'change submission', 'modify submission'], answer: 'Editing is limited. Use Submission History to manage pending submissions where available.' },
        { question: 'Can I delete a submission?', keywords: ['delete submission', 'remove submission', 'cancel submission'], answer: 'Yes, only pending submissions can be deleted from Submission History.' },
        { question: 'Can I delete approved submissions?', keywords: ['delete approved', 'remove approved'], answer: 'No. Approved submissions cannot be deleted by users.' },
        { question: 'Can I delete rejected submissions?', keywords: ['delete rejected', 'remove rejected'], answer: 'No. Rejected submissions cannot be deleted by users.' },
        { question: 'Can I submit multiple activities?', keywords: ['multiple activities', 'many submissions', 'submit again'], answer: 'Yes. You can submit multiple activities if each has valid details and proof.' },
        { question: 'What happens after I submit?', keywords: ['after submit', 'after submission', 'what next'], answer: 'Your submission becomes pending until an admin reviews it.' },
        { question: 'How long does verification take?', keywords: ['verification time', 'how long verify', 'review time'], answer: 'Verification time depends on admin review. Check Submission History for updates.' },
        { question: 'How many impact categories are available?', keywords: ['how many categories', 'impact categories', 'categories available'], answer: 'There are 3 impact categories: Low Impact, Medium Impact, and High Impact.' },
        { question: 'What is Low Impact?', keywords: ['low impact', 'explain low', 'low category'], answer: 'Low Impact actions are simple eco-friendly activities. They give 25 points.' },
        { question: 'What is Medium Impact?', keywords: ['medium impact', 'explain medium', 'medium category'], answer: 'Medium Impact actions have higher contribution. They give 50 points and require Club ID and team members.' },
        { question: 'What is High Impact?', keywords: ['high impact', 'explain high', 'high category'], answer: 'High Impact actions are major activities. They give 75 points and require Club ID and team members.' },
        { question: 'How many points does Low Impact give?', keywords: ['low points', 'low impact points', '25 points'], answer: 'Low Impact gives 25 points.' },
        { question: 'How many points does Medium Impact give?', keywords: ['medium points', 'medium impact points', '50 points'], answer: 'Medium Impact gives 50 points.' },
        { question: 'How many points does High Impact give?', keywords: ['high points', 'high impact points', '75 points'], answer: 'High Impact gives 75 points.' },
        { question: 'Which category should I choose?', keywords: ['choose category', 'which impact', 'best category'], answer: 'Choose the category that matches your activity size, contribution, and requirements.' },
        { question: 'What are examples of Low Impact activities?', keywords: ['low examples', 'low activity examples'], answer: 'Low Impact can include simple individual eco-friendly actions with valid proof.' },
        { question: 'What are examples of Medium Impact activities?', keywords: ['medium examples', 'medium activity examples'], answer: 'Medium Impact can include club or team activities with stronger contribution and valid proof.' },
        { question: 'What are examples of High Impact activities?', keywords: ['high examples', 'high activity examples'], answer: 'High Impact can include major club projects, campaigns, or large sustainability contributions.' },
        { question: 'What are the 3ZERO clusters?', keywords: ['3zero clusters', '3 zero clusters', 'cluster list'], answer: 'The 3ZERO clusters are Zero Poverty, Zero Unemployment, and Zero Net Carbon Emission.' },
        { question: 'What is Zero Poverty?', keywords: ['zero poverty', 'poverty cluster'], answer: 'Zero Poverty focuses on actions that help reduce poverty and support community wellbeing.' },
        { question: 'What is Zero Unemployment?', keywords: ['zero unemployment', 'unemployment cluster'], answer: 'Zero Unemployment focuses on skills, work opportunities, entrepreneurship, and employability.' },
        { question: 'What is Zero Net Carbon Emission?', keywords: ['zero net carbon', 'carbon emission', 'net carbon'], answer: 'Zero Net Carbon Emission focuses on reducing carbon impact and protecting the environment.' },
        { question: 'Which cluster should I select?', keywords: ['choose cluster', 'which cluster', 'select cluster'], answer: 'Select the one 3ZERO cluster that best matches your activity goal.' },
        { question: 'Can I select more than one cluster?', keywords: ['more than one cluster', 'multiple clusters', 'two clusters'], answer: 'No. Select only one 3ZERO cluster for each submission.' },
        { question: 'Why are clusters important?', keywords: ['why clusters', 'cluster important', 'purpose cluster'], answer: 'Clusters help classify your action under the correct 3ZERO goal.' },
        { question: 'When is Club ID required?', keywords: ['club id required', 'when club id', 'clubid required'], answer: 'Club ID is required for Medium Impact and High Impact submissions.' },
        { question: 'Is Club ID required for Low Impact?', keywords: ['club id low', 'low club id'], answer: 'No. Club ID is not required for Low Impact.' },
        { question: 'Is Club ID required for Medium Impact?', keywords: ['club id medium', 'medium club id'], answer: 'Yes. Club ID is required for Medium Impact.' },
        { question: 'Is Club ID required for High Impact?', keywords: ['club id high', 'high club id'], answer: 'Yes. Club ID is required for High Impact.' },
        { question: 'When are team members required?', keywords: ['team required', 'members required', 'when team'], answer: 'Team members are required for Medium Impact and High Impact submissions.' },
        { question: 'How many team members can I add?', keywords: ['how many team', 'team size', 'add members'], answer: 'For Medium/High Impact, total team size must be 3 to 5 including you.' },
        { question: 'Can I submit without team members?', keywords: ['without team', 'no team members', 'submit alone'], answer: 'Yes for Low Impact. Medium and High Impact require team members.' },
        { question: 'What proof files are allowed?', keywords: ['proof files', 'allowed files', 'upload proof'], answer: 'Allowed proof: PNG, JPG, JPEG, GIF images, or PDF.' },
        { question: 'How many images can I upload?', keywords: ['how many images', 'image count', 'upload images'], answer: 'Upload 2 to 5 images if you use image proof.' },
        { question: 'Can I upload PDF files?', keywords: ['upload pdf', 'pdf proof', 'pdf file'], answer: 'Yes. If using PDF proof, upload exactly 1 PDF file.' },
        { question: 'What is the maximum file size?', keywords: ['max file size', 'maximum size', '15 mb'], answer: 'Each proof file must be 15 MB or less.' },
        { question: 'Can I upload both images and PDF?', keywords: ['mix pdf images', 'images and pdf', 'both pdf image'], answer: 'No. Upload either images or one PDF, not both together.' },
        { question: 'Why was my file rejected?', keywords: ['file rejected', 'upload rejected', 'proof rejected'], answer: 'Files may be rejected for wrong type, too many/few files, mixed PDF/images, or size over 15 MB.' },
        { question: 'What makes valid proof?', keywords: ['valid proof', 'good proof', 'proof valid'], answer: 'Valid proof is clear, relevant, and shows the activity happened.' },
        { question: 'What does Pending mean?', keywords: ['pending mean', 'status pending'], answer: 'Pending means your submission is waiting for admin review.' },
        { question: 'What does Approved mean?', keywords: ['approved mean', 'status approved'], answer: 'Approved means admin accepted your submission and it counts for points.' },
        { question: 'What does Rejected mean?', keywords: ['rejected mean', 'status rejected'], answer: 'Rejected means admin did not accept the submission. Check remarks for the reason.' },
        { question: 'Where can I check submission status?', keywords: ['check status', 'submission status', 'where status'], answer: 'Open View Submission History to check each submission status.' },
        { question: 'How do I know if my submission is approved?', keywords: ['know approved', 'approved submission', 'is approved'], answer: 'In Submission History, approved submissions show the Approved status and verification date.' },
        { question: 'What is a verification date?', keywords: ['verification date', 'verified date'], answer: 'The verification date is when admin reviewed and updated your submission status.' },
        { question: 'Can I resubmit after rejection?', keywords: ['resubmit rejected', 'submit after rejection'], answer: 'Yes. Create a new submission with improved details and valid proof.' },
        { question: 'How are points calculated?', keywords: ['calculate points', 'points calculated', 'eco points'], answer: 'Points are based on impact category: Low 25, Medium 50, High 75.' },
        { question: 'Where can I view my points?', keywords: ['view points', 'my points', 'points earned'], answer: 'Your points appear in the profile summary on this dashboard.' },
        { question: 'How do I view rewards?', keywords: ['view rewards', 'my rewards', 'where rewards'], answer: 'Open Your Rewards to see rewards assigned to your submissions.' },
        { question: 'How are rewards assigned?', keywords: ['rewards assigned', 'assign rewards', 'reward assigned'], answer: 'Admins assign rewards after reviewing eligible submissions.' },
        { question: 'Where can I see my ranking?', keywords: ['see ranking', 'my ranking', 'my rank'], answer: 'Your rank appears on the dashboard. You can also open View Leaderboard.' },
        { question: 'How does the leaderboard work?', keywords: ['leaderboard work', 'ranking work', 'leaderboard'], answer: 'The leaderboard ranks users by approved submission points and team participation.' },
        { question: 'How do I update my profile?', keywords: ['update profile', 'edit profile'], answer: 'Open Profile from the profile menu and update your details.' },
        { question: 'How do I change my profile picture?', keywords: ['change picture', 'profile picture', 'upload profile'], answer: 'Open Profile, choose a new profile picture, crop it, and save.' },
        { question: 'How do I crop my profile picture?', keywords: ['crop profile', 'crop picture'], answer: 'After selecting a profile picture, use the crop popup before saving.' },
        { question: 'How do I change my password?', keywords: ['change password', 'new password'], answer: 'Open Profile and use the password change section.' },
        { question: 'How do I reset my password?', keywords: ['reset password', 'forgot password'], answer: 'Use Forgot Password on the login page to request a reset link.' },
        { question: 'What is the sustainability calculator?', keywords: ['sustainability calculator', 'calculator'], answer: 'It estimates your sustainability score from water usage, reusable items, and walking days.' },
        { question: 'How does the sustainability score work?', keywords: ['sustainability score', 'score work'], answer: 'The score combines water, reusable item, and walking inputs into a score out of 100.' },
        { question: 'How can I track my sustainability score?', keywords: ['track score', 'score history'], answer: 'Use the Sustainability Calculator. Your previous scores appear in the score chart.' },
        { question: 'What do the sustainability charts show?', keywords: ['sustainability charts', 'calculator chart'], answer: 'They show your sustainability score trend over time.' },
        { question: 'How do I contact admin?', keywords: ['contact admin', 'ask admin', 'admin help'], answer: 'Use Contact to send a message to the admin team.' },
        { question: 'Where can I view admin replies?', keywords: ['admin replies', 'admin responses', 'view replies'], answer: 'Open Admin Responses from the tools menu to view admin replies.' },
        { question: 'How do I send a message to admin?', keywords: ['send message admin', 'message admin'], answer: 'Open Contact, write your message, and submit the form.' },
        { question: 'How will I know if admin replied?', keywords: ['admin replied', 'know reply'], answer: 'Check Admin Responses. You may also receive an email response.' },
        { question: 'What does the dashboard show?', keywords: ['dashboard show', 'dashboard info'], answer: 'The dashboard shows your profile, points, rank, recent submissions, charts, and CSV download.' },
        { question: 'What is my rank?', keywords: ['what rank', 'rank meaning'], answer: 'Your rank shows your position compared with other users based on points.' },
        { question: 'How do I download my submissions as CSV?', keywords: ['download csv', 'submissions csv', 'export csv'], answer: 'Click Download Submissions as CSV in the Submission Insights section.' },
        { question: 'What are recent submissions?', keywords: ['recent submissions', 'latest submissions'], answer: 'Recent submissions are your latest 5 submitted activities shown on the dashboard.' },
        { question: 'What does the submission trend chart show?', keywords: ['submission trend', 'trend chart'], answer: 'It shows how many submissions you made over time.' },
        { question: 'Where can I view news and events?', keywords: ['news events', 'view news', 'events'], answer: 'Open the News and Events page from the main site navigation.' },
        { question: 'Where can I view points guidelines?', keywords: ['points guidelines', 'view guidelines', 'guidelines'], answer: 'Open the Guidelines page from the main site navigation, or go to ../guidelines.php.' },
        { question: 'How do I understand the submission rules?', keywords: ['submission rules', 'understand rules'], answer: 'Read the Guidelines page and check the proof, Club ID, team, and category rules.' },
        { question: 'What activities earn the most points?', keywords: ['most points', 'highest points', 'earn most'], answer: 'High Impact activities earn the most points: 75 points.' },
        { question: 'How do I use GreenCredit?', keywords: ['use greencredit', 'how use system'], answer: 'Submit eco-friendly actions, upload proof, wait for review, earn points, and track progress.' },
        { question: 'What is GreenCredit?', keywords: ['what greencredit', 'green credit'], answer: 'GreenCredit is a platform for tracking eco-friendly actions, points, rewards, and sustainability progress.' },
        { question: 'What is the purpose of this platform?', keywords: ['purpose platform', 'why greencredit'], answer: 'Its purpose is to encourage sustainable actions and recognize users through points and rewards.' }
    ];

    const chatbotStopWords = new Set(['how', 'can', 'what', 'where', 'when', 'does', 'are', 'the', 'and', 'for', 'with', 'why', 'is', 'my', 'your', 'you', 'do', 'did', 'this', 'that', 'from']);

    function normalizeChatText(text) {
        return text
            .toLowerCase()
            .replace(/3\s*zero/g, '3zero')
            .replace(/club\s*id/g, 'clubid')
            .replace(/eco\s*friendly/g, 'ecofriendly')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function getChatTokens(text) {
        return normalizeChatText(text)
            .split(' ')
            .filter(token => token.length > 1 && !chatbotStopWords.has(token));
    }

    function getEditDistance(a, b) {
        if (a === b) return 0;
        if (!a.length) return b.length;
        if (!b.length) return a.length;

        const matrix = Array.from({ length: b.length + 1 }, (_, i) => [i]);
        for (let j = 0; j <= a.length; j++) matrix[0][j] = j;

        for (let i = 1; i <= b.length; i++) {
            for (let j = 1; j <= a.length; j++) {
                const cost = b.charAt(i - 1) === a.charAt(j - 1) ? 0 : 1;
                matrix[i][j] = Math.min(
                    matrix[i - 1][j] + 1,
                    matrix[i][j - 1] + 1,
                    matrix[i - 1][j - 1] + cost
                );
            }
        }

        return matrix[b.length][a.length];
    }

    function isFuzzyTokenMatch(inputToken, answerToken) {
        if (inputToken === answerToken) return true;
        if (inputToken.length < 4 || answerToken.length < 4) return false;
        const limit = Math.max(inputToken.length, answerToken.length) > 7 ? 2 : 1;
        return getEditDistance(inputToken, answerToken) <= limit;
    }

    function scoreChatbotAnswer(entry, normalizedQuestion, questionTokens) {
        const entryPhrases = [entry.question, ...entry.keywords].map(normalizeChatText);
        const entryText = normalizeChatText(entryPhrases.join(' '));
        const entryTokens = getChatTokens(entryText);
        let score = 0;

        entryPhrases.forEach(phrase => {
            if (phrase && normalizedQuestion.includes(phrase)) score += 12;
            if (phrase.length > 4 && phrase.includes(normalizedQuestion)) score += 8;
        });

        questionTokens.forEach(token => {
            if (entryTokens.includes(token)) {
                score += 3;
                return;
            }

            if (entryTokens.some(entryToken => isFuzzyTokenMatch(token, entryToken))) {
                score += 1.5;
            }
        });

        return score;
    }

    function addChatMessage(text, sender) {
        const message = document.createElement('div');
        message.className = 'chat-message ' + sender;
        message.textContent = text;
        chatbotMessages.appendChild(message);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function getChatbotAnswer(question) {
        const normalizedQuestion = normalizeChatText(question);
        const questionTokens = getChatTokens(question);

        if (!normalizedQuestion) {
            return 'Type a question about submissions, points, rewards, profile, calculator, contact, or guidelines.';
        }

        let bestMatch = null;
        let bestScore = 0;

        chatbotAnswers.forEach(entry => {
            const score = scoreChatbotAnswer(entry, normalizedQuestion, questionTokens);
            if (score > bestScore) {
                bestScore = score;
                bestMatch = entry;
            }
        });

        if (bestMatch && bestScore >= 3) {
            return bestMatch.answer;
        }

        return 'I can help with submissions, impact categories, 3ZERO clusters, Club ID, proof files, status, points, rewards, profile, calculator, contact, news, and guidelines.';
    }

    function askChatbot(question) {
        const cleanQuestion = question.trim();
        if (!cleanQuestion) return;

        addChatMessage(cleanQuestion, 'user');
        addChatMessage(getChatbotAnswer(cleanQuestion), 'bot');
        chatbotInput.value = '';
        chatbotInput.focus();
    }

    function openChatbot() {
        chatbotPanel.classList.add('is-open');
        chatbotPanel.setAttribute('aria-hidden', 'false');
        chatbotToggle.setAttribute('aria-expanded', 'true');
        setTimeout(() => chatbotInput.focus(), 50);
    }

    function closeChatbot() {
        chatbotPanel.classList.remove('is-open');
        chatbotPanel.setAttribute('aria-hidden', 'true');
        chatbotToggle.setAttribute('aria-expanded', 'false');
    }

    if (chatbotToggle && chatbotPanel && chatbotClose && chatbotForm && chatbotInput && chatbotMessages) {
        chatbotToggle.addEventListener('click', function() {
            if (chatbotPanel.classList.contains('is-open')) {
                closeChatbot();
            } else {
                openChatbot();
            }
        });

        chatbotClose.addEventListener('click', closeChatbot);

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
