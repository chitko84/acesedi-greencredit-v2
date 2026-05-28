<?php
session_start();
include '../includes/db.php';

// Redirect if admin is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if submission_id is provided
if (isset($_GET['submission_id'])) {
    $submission_id = $_GET['submission_id'];

    // First, retrieve the proof image to delete it from the file system (if it exists)
    $query = "SELECT proof_image FROM submissions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($proof_image);
    $stmt->fetch();

    // If there is a proof image, delete it from the uploads folder
    if ($proof_image && file_exists("../uploads/$proof_image")) {
        unlink("../uploads/$proof_image"); // Delete the file
    }

    // Delete the submission from the database
    $delete_query = "DELETE FROM submissions WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $submission_id);

    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "Submission deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting submission: " . $delete_stmt->error;
    }

    // Redirect back to the manage submissions page
    header('Location: submissions.php');
    exit();
} else {
    // If no submission_id is provided, redirect back with an error message
    $_SESSION['error'] = "No submission ID provided!";
    header('Location: submissions.php');
    exit();
}
?>
