<?php
// Start the session and include the database connection
session_start();
include '../includes/db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Redirect to login page if not logged in
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id']; // Assuming you store the user's ID in the session

// Fetch rewards based on user_id from the submissions table
$query = "SELECT * FROM submissions WHERE user_id = ? AND reward IS NOT NULL AND LOWER(TRIM(status)) = 'approved' ORDER BY created_at DESC"; // Only fetch approved submissions with a reward
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
    <title>GreenCredit - Your Rewards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" href="assets/images/gc_logo.jpg" type="image/x-icon">
</head>
<body>

<!-- Navbar for User -->
<?php include 'includes/header.php'; ?>

<!-- User Rewards Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">Your Rewards</h2>

    <!-- Rewards Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Category</th>
                <th>Action</th>
                <th>Points</th>
                <th>Status</th>
                <th>Reward</th>
                <th>Description</th>
                <th>Submitted On</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($reward = $rewards_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($reward['category']); ?></td>
                    <td><?php echo htmlspecialchars($reward['action']); ?></td>
                    <td><?php echo strtolower(trim($reward['status'] ?? '')) === 'approved' ? (int)$reward['points'] : 0; ?></td>
                    <td>
                        <span class="badge 
                            <?php 
                            if ($reward['status'] == 'pending') echo 'bg-warning';
                            elseif ($reward['status'] == 'approved') echo 'bg-success';
                            else echo 'bg-danger';
                            ?>">
                            <?php echo ucfirst($reward['status']); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($reward['reward']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($reward['description'])); ?></td>
                    <td><?php echo $reward['created_at']; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
