<?php
// Start session and include database connection
session_start();
include '../includes/db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php'); // Redirect to login page if not logged in
    exit();
}

// Get logged-in user's email from the session
$user_email = $_SESSION['user_email']; // Assuming you store the user's email in the session

// Fetch contact messages along with responses from the database based on user's email
$query = "SELECT * FROM contact_messages WHERE email = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$messages_result = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Your Responses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/images/gc_logo.jpg" type="image/x-icon">
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
        
        body.dark-mode td p {
            color: #e0f7e9;
        }

        body.dark-mode td strong {
            color: #b2fab4;
        }

    </style>
</head>
<body>

<!-- User Responses Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">Your Contact Messages and Responses</h2>

    <!-- Response Table -->
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Contact Message</th>
                <th>Admin Response</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($message = $messages_result->fetch_assoc()) { ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($message['name']); ?></strong><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($message['email']); ?><br>
                        <strong>Message:</strong><br>
                        <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                    </td>
                    <td>
                        <?php if (!empty($message['response'])) { ?>
                            <strong>Response:</strong><br>
                            <p><?php echo nl2br(htmlspecialchars($message['response'])); ?></p>
                        <?php } else { ?>
                            <p>No response yet</p>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
        <div class="text-center">
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
