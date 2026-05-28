<?php

function notifySubmissionAction($conn, $submission_id, $action, $remarks) {
    // Fetch submission + submitter + team
    $query = "SELECT s.*, u.email AS submitter_email, u.name AS submitter_name, u.team_id 
              FROM submissions s 
              LEFT JOIN users u ON u.id = s.user_id
              WHERE s.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $submission = $result->fetch_assoc();
    $stmt->close();

    if (!$submission) return;

    // Get all admin emails
    $admins = [];
    $admin_query = "SELECT email FROM users WHERE role = 'admin'";
    $admins_result = $conn->query($admin_query);
    while ($row = $admins_result->fetch_assoc()) {
        $admins[] = $row['email'];
    }

    // Get teammates (if submitter has a team_id)
    $teammates = [];
    if (!empty($submission['team_id'])) {
        $team_stmt = $conn->prepare("SELECT email FROM users WHERE team_id = ? AND id != ?");
        $team_stmt->bind_param("ii", $submission['team_id'], $submission['user_id']);
        $team_stmt->execute();
        $team_result = $team_stmt->get_result();
        while ($row = $team_result->fetch_assoc()) {
            $teammates[] = $row['email'];
        }
        $team_stmt->close();
    }

    // Prepare email content
    $subject = "GreenCredit - Submission #{$submission_id} " . ucfirst($action);
    $message = "
        <html>
        <head><title>Submission Notification</title></head>
        <body>
            <h2>Submission #{$submission_id} has been " . htmlspecialchars($action) . ".</h2>
            <p><strong>Category:</strong> " . htmlspecialchars($submission['category']) . "</p>
            <p><strong>Action:</strong> " . htmlspecialchars($submission['action']) . "</p>
            <p><strong>Points:</strong> " . htmlspecialchars($submission['points']) . "</p>
            <p><strong>Status:</strong> " . htmlspecialchars($submission['status']) . "</p>
            " . ($remarks ? "<p><strong>Remarks:</strong> " . nl2br(htmlspecialchars($remarks)) . "</p>" : "") . "
            <p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($submission['description'])) . "</p>
        </body>
        </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: GreenCredit <no-reply@ace-sedi.aiu.edu.my>\r\n";

    // Send to all admins
    foreach ($admins as $email) {
        mail($email, $subject, $message, $headers);
    }

    // Send to submitter (if exists)
    if (!empty($submission['submitter_email'])) {
        mail($submission['submitter_email'], $subject, $message, $headers);
    }

    // Send to teammates (if any)
    foreach ($teammates as $email) {
        mail($email, $subject, $message, $headers);
    }
}
?>