<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
$role_stmt->bind_param("i", $_SESSION['user_id']);
$role_stmt->execute();
$role = $role_stmt->get_result()->fetch_assoc()['role'] ?? '';
if ($role !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

$exports = [
    'users' => [
        'filename' => 'greencredit_users.csv',
        'sql' => "SELECT id, name, date_of_birth, phone_number, email, role, eco_points, profile_pic, program_of_study, intake, country, gender, department, expected_graduation_year, created_at, reset_token, token_expiry FROM users ORDER BY id ASC",
    ],
    'submissions' => [
        'filename' => 'greencredit_submissions.csv',
        'sql' => "SELECT s.*, u.name AS submitter_name, u.email AS submitter_email FROM submissions s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC",
    ],
    'messages' => [
        'filename' => 'greencredit_contact_messages.csv',
        'sql' => "SELECT * FROM contact_messages ORDER BY created_at DESC",
    ],
    'news' => [
        'filename' => 'greencredit_news_events.csv',
        'sql' => "SELECT * FROM news_events ORDER BY created_at DESC",
    ],
    'rewards' => [
        'filename' => 'greencredit_rewards.csv',
        'sql' => "SELECT * FROM submissions ORDER BY created_at DESC",
    ],
    'leaderboard' => [
        'filename' => 'greencredit_leaderboard_source.csv',
        'sql' => "SELECT s.*, u.name AS submitter_name, u.email AS submitter_email FROM submissions s LEFT JOIN users u ON s.user_id = u.id WHERE s.status = 'approved' ORDER BY s.created_at DESC",
    ],
];

$type = $_GET['table'] ?? '';
if (!isset($exports[$type])) {
    http_response_code(400);
    exit('Invalid export type');
}

$result = $conn->query($exports[$type]['sql']);
if (!$result) {
    http_response_code(500);
    exit('Export query failed');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $exports[$type]['filename'] . '"');

$out = fopen('php://output', 'w');
$fields = $result->fetch_fields();
fputcsv($out, array_map(fn($field) => $field->name, $fields));
while ($row = $result->fetch_assoc()) {
    fputcsv($out, $row);
}
fclose($out);
exit();
