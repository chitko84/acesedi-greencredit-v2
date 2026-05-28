<?php
session_start();
include '../includes/db.php';

// Redirect if admin is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $submission_id = $_POST['submission_id'];
    $points = $_POST['points'];

    // Validate that points is a valid number
    if (is_numeric($points) && $points >= 0) {
        // Update the points for the submission in the database
        $update_query = "UPDATE submissions SET points = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $points, $submission_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Points assigned successfully!";
        } else {
            $_SESSION['error'] = "Error updating points: " . $stmt->error;
        }
    } else {
        $_SESSION['error'] = "Invalid points value!";
    }

    // Redirect back to manage submissions page
    header('Location: submissions.php');
    exit();
}
?>
