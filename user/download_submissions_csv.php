<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's name (for team member filtering)
$user_query = "SELECT name FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$user_name = $user['name'] ?? 'user';
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=\"{$user_name}_submissions.csv\"");

$output = fopen('php://output', 'w');
fputcsv($output, ['Category', 'Action', 'Points', 'Status', 'Verified Date', 'Team Members']);

$submission_query = "
    SELECT * FROM submissions
    WHERE user_id = ? 
    OR JSON_CONTAINS(team_members, '\"". $conn->real_escape_string($user_name) ."\"')
    ORDER BY created_at DESC
";
$submission_stmt = $conn->prepare($submission_query);
$submission_stmt->bind_param("i", $user_id);
$submission_stmt->execute();
$result = $submission_stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $team_members = json_decode($row['team_members'], true);
    if (!is_array($team_members)) $team_members = [];
    $team_string = implode(", ", $team_members);

    $verified_date = ($row['verified_date'] && $row['verified_date'] !== '0000-00-00 00:00:00')
        ? date('d M Y, H:i', strtotime($row['verified_date']))
        : '-';

    fputcsv($output, [
        $row['category'],
        $row['action'],
        $row['points'],
        ucfirst($row['status']),
        $verified_date,
        $team_string
    ]);
}

fclose($output);
exit();
?>
