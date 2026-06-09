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
        'sql' => "SELECT u.id, u.name, u.date_of_birth, u.phone_number, u.email, u.role, (COALESCE(sp.points, 0) + COALESCE(tp.points, 0)) AS eco_points, u.profile_pic, u.program_of_study, u.intake, u.country, u.gender, u.department, u.expected_graduation_year, u.created_at, u.reset_token, u.token_expiry FROM users u LEFT JOIN (SELECT user_id, SUM(points) AS points FROM submissions WHERE LOWER(TRIM(status)) = 'approved' GROUP BY user_id) sp ON sp.user_id = u.id LEFT JOIN (SELECT tm.id AS user_id, SUM(s.points) AS points FROM users tm JOIN submissions s ON JSON_CONTAINS(s.team_members, JSON_QUOTE(tm.name)) AND s.user_id <> tm.id WHERE LOWER(TRIM(s.status)) = 'approved' GROUP BY tm.id) tp ON tp.user_id = u.id ORDER BY u.id ASC",
    ],
    'submissions' => [
        'filename' => 'greencredit_submissions.csv',
        'sql' => "SELECT s.id, s.user_id, s.category, s.action, CASE WHEN LOWER(TRIM(s.status)) = 'approved' THEN s.points ELSE 0 END AS points, s.proof_image, s.status, s.created_at, s.reward, s.description, s.admin_remarks, s.verified_date, s.team_number, s.team_members, s.three_zero_cluster, s.club_id, s.superadmin_remarks, u.name AS submitter_name, u.email AS submitter_email FROM submissions s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC",
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
        'sql' => "SELECT id, user_id, category, action, CASE WHEN LOWER(TRIM(status)) = 'approved' THEN points ELSE 0 END AS points, proof_image, status, created_at, reward, description, admin_remarks, verified_date, team_number, team_members, three_zero_cluster, club_id, superadmin_remarks FROM submissions ORDER BY created_at DESC",
    ],
    'leaderboard' => [
        'filename' => 'greencredit_leaderboard_source.csv',
        'sql' => "SELECT s.*, u.name AS submitter_name, u.email AS submitter_email FROM submissions s LEFT JOIN users u ON s.user_id = u.id WHERE LOWER(TRIM(s.status)) = 'approved' ORDER BY s.created_at DESC",
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
