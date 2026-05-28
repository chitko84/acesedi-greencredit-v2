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
    // Get submission ID and reward value from the form
    $submission_id = $_POST['submission_id'];
    $reward = $_POST['reward'];

    // Validate the inputs (optional but recommended)
    if (empty($reward)) {
        // Display an error if the reward field is empty
        $_SESSION['error'] = 'Reward cannot be empty.';
        header("Location: submissions.php");
        exit();
    }

    // Prepare the SQL query to update the submission with the assigned reward
    $query = "UPDATE submissions SET reward = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $reward, $submission_id);

    // Execute the query and check if the update was successful
    if ($stmt->execute()) {
        // If successful, redirect back to the manage submissions page
        $_SESSION['success'] = 'Reward successfully assigned!';
    } else {
        // If there was an error, show an error message
        $_SESSION['error'] = 'Failed to assign reward. Please try again.';
    }

    // Close the statement and redirect
    $stmt->close();
    header("Location: submissions.php");
    exit();
}
?>
