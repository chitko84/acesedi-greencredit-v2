<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = intval($_GET['id']);

// Delete images from folder
$images = $conn->query("SELECT image FROM news_images WHERE news_id = $id");
while ($img = $images->fetch_assoc()) {
    $file_path = "../uploads/news/" . $img['image'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Delete from DB (images table will auto-delete if ON DELETE CASCADE is set)
$conn->query("DELETE FROM news_events WHERE id = $id");

header("Location: admin_news.php");
exit();
