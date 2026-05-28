<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the submission ID from the request
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid submission ID.']);
    exit();
}

$submission_id = $_POST['id'];

// Check if the submission exists and belongs to the logged-in user or their team
$query = "SELECT * FROM submissions WHERE id = ? AND (user_id = ? OR JSON_CONTAINS(team_members, '\"". $conn->real_escape_string($_SESSION['user_name']) ."\"'))";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $submission_id, $user_id);
$stmt->execute();
$submission_result = $stmt->get_result();

if ($submission_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Submission not found or you do not have permission to delete.']);
    exit();
}

// Delete the submission
$delete_query = "DELETE FROM submissions WHERE id = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("i", $submission_id);

if ($delete_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Submission deleted successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete submission.']);
}
?>
