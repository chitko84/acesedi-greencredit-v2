<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $submission_id = $_POST['submission_id'];
    $verification_date = $_POST['verification_date'];

    $query = "UPDATE submissions SET verified_date = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $verification_date, $submission_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Verification date updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update verification date!";
    }

    header('Location: submissions.php');
    exit();
}
?>
