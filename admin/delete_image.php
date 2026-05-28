<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = intval($_GET['id']);
$news_id = intval($_GET['news_id']);

$image = $conn->query("SELECT image FROM news_images WHERE id = $id")->fetch_assoc();
if ($image) {
    $file_path = "../uploads/news/" . $image['image'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    $conn->query("DELETE FROM news_images WHERE id = $id");
}

header("Location: edit_news.php?id=$news_id");
exit();
