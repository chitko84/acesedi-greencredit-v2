<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if submission ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: all_submissions.php');
    exit();
}

$submission_id = (int)$_GET['id'];

// Fetch submission data
$query = "
    SELECT s.*, u.name AS submitter_name
    FROM submissions s
    LEFT JOIN users u ON u.id = s.user_id
    WHERE s.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: all_submissions.php');
    exit();
}

$submission = $result->fetch_assoc();

// Format dates
$submitted_date = date('d M Y, H:i', strtotime($submission['created_at']));
$verified_date = ($submission['verified_date'] && $submission['verified_date'] !== '0000-00-00 00:00:00')
    ? date('d M Y, H:i', strtotime($submission['verified_date']))
    : 'Not verified yet';

// Team members
$team_members = json_decode($submission['team_members'], true);
if (!is_array($team_members)) {
    $team_members = [];
}

// Description
$description = $submission['description'] ?? 'No description provided';

// Club ID - handle low impact category
$club_id_display = (strtolower($submission['category']) === 'low impact') 
    ? '0' 
    : ($submission['club_id'] ?? '-');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GreenCredit - Description View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/styles.css" />
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon" />
    <style>
        .description-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .description-content {
            white-space: pre-wrap;
            line-height: 1.6;
            font-size: 1.1rem;
        }
        .submission-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Submission Description</h2>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <div class="description-container">
                <!-- Submission Information -->
                <div class="submission-info">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <span class="info-label">Submission ID:</span> #<?= $submission['id'] ?>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Club ID:</span> <?= htmlspecialchars($club_id_display) ?>
                        </div>
                        <div class="col-md-3 mb-2">
                            <span class="info-label">Category:</span> <?= htmlspecialchars($submission['category']) ?>
                        </div>
                        <div class="col-md-3 mb-2">
                            <span class="info-label">Action:</span> <?= htmlspecialchars($submission['action']) ?>
                        </div>
                        <div class="col-md-3 mb-2">
                            <span class="info-label">Points:</span> <?= strtolower(trim($submission['status'] ?? '')) === 'approved' ? (int)$submission['points'] : 0 ?>
                        </div>
                        <div class="col-md-3 mb-2">
                            <span class="info-label">Status:</span> 
                            <span class="badge 
                                <?php 
                                if ($submission['status'] == 'pending') echo 'bg-warning';
                                elseif ($submission['status'] == 'approved') echo 'bg-success';
                                else echo 'bg-danger';
                                ?>">
                                <?= ucfirst($submission['status']) ?>
                            </span>
                        </div>
                        <div class="col-md-3 mb-2">
                            <span class="info-label">Submitted By:</span> <?= htmlspecialchars($submission['submitter_name'] ?? 'Unknown') ?>
                        </div>
                        <div class="col-md-3 mb-2">
                            <span class="info-label">Submitted Date:</span> <?= $submitted_date ?>
                        </div>
                        <div class="col-md-3 mb-2">
                            <span class="info-label">Verified Date:</span> <?= $verified_date ?>
                        </div>
                    </div>
                </div>
                
                <!-- Description Content -->
                <h4 class="mb-3">Description</h4>
                <div class="description-content">
                    <?= nl2br(htmlspecialchars($description)) ?>
                </div>
                
                <?php if (!empty($team_members)): ?>
                <div class="mt-4">
                    <h5>Team Members</h5>
                    <ul>
                        <?php foreach ($team_members as $member): ?>
                            <li><?= htmlspecialchars($member) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
