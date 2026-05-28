<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Delete user's trend data
$query = "DELETE FROM sustainability_scores WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$_SESSION['message'] = "Sustainability trend cleared successfully.";
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
?>
