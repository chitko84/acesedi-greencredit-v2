<?php
session_start();
include '../includes/db.php';

// Redirect if admin is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $submission_id = $_POST['submission_id'];
    $status = $_POST['status'];

    $valid_statuses = ['pending', 'approved', 'rejected'];
    if (in_array($status, $valid_statuses)) {

        $get_user_id_stmt = $conn->prepare("SELECT user_id FROM submissions WHERE id = ?");
        $get_user_id_stmt->bind_param("i", $submission_id);
        $get_user_id_stmt->execute();
        $user_result = $get_user_id_stmt->get_result();
        $user_row = $user_result->fetch_assoc();
        $user_id = $user_row['user_id'];

        $user_stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_info = $user_stmt->get_result()->fetch_assoc();

        $update_stmt = $conn->prepare("UPDATE submissions SET status = ? WHERE id = ?");
        $update_stmt->bind_param("si", $status, $submission_id);

        if ($update_stmt->execute()) {
            $to = $user_info['email'];
            $subject = 'Your Submission Status Updated';
            $message = "
                <html>
                <head><title>Submission Status</title></head>
                <body>
                    <p>Dear {$user_info['name']},</p>
                    <p>Your submission (ID: {$submission_id}) has been updated. Your points and verfication status have been updated too. Please take a look in the system.</p>
                    <p>Thank you for contributing to GreenCredit!</p>
                </body>
                </html>
            ";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: GreenCredit <acesediaiuedu@ace-sedi.aiu.edu.my>\r\n";
            mail($to, $subject, $message, $headers);
            
            $to_admin = "chitko.ko@student.aiu.edu.my";
            $admin_subject = 'User Submission Status Updated by Admin';
            $admin_message = "
                <html>
                <head><title>User Submission Status Updated</title></head>
                <body>
                    <p>Dear Admin,</p>
                    <p>User with submission (ID: {$submission_id}) has been updated in the system by one of the admins.</p>
                    <footer>This is an automated message from GreenCredit.</footer>
                </body>
                </html>
            ";
            $admin_headers = "MIME-Version: 1.0\r\n";
            $admin_headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $admin_headers .= "From: GreenCredit <acesediaiuedu@ace-sedi.aiu.edu.my>\r\n";
            mail($to_admin, $admin_subject, $admin_message, $admin_headers);

            $_SESSION['success'] = "Submission status updated and email sent!";
        } else {
            $_SESSION['error'] = "Error updating submission status.";
        }
    } else {
        $_SESSION['error'] = "Invalid status selected.";
    }

    header('Location: submissions.php');
    exit();
}
?>
