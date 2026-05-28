<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sustainability_trend.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Sustainability Score']);

$query = "SELECT DATE(created_at) as date, sustainability_score 
          FROM sustainability_scores 
          WHERE user_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [$row['date'], $row['sustainability_score']]);
}
fclose($output);
exit();
?>
