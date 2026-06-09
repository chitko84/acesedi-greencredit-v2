<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    header('Location: manageuser.php');
    exit();
}

 $query = "SELECT * FROM submissions WHERE user_id = ? AND reward IS NOT NULL AND LOWER(TRIM(status)) = 'approved' ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$rewards_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Rewards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <h2 class="text-center mb-4">Rewards for User ID: <?php echo htmlspecialchars($user_id); ?></h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Reward</th>
                <th>Points</th>
                <th>Submission Date</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($reward = $rewards_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($reward['reward']); ?></td>
                    <td><?php echo strtolower(trim($reward['status'] ?? '')) === 'approved' ? (int)$reward['points'] : 0; ?></td>
                    <td><?php echo $reward['created_at']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
