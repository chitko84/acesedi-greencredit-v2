<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$users = [];
$users_stmt = $conn->prepare("SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name ASC");
$users_stmt->execute();
$users_result = $users_stmt->get_result();
while ($row = $users_result->fetch_assoc()) {
    if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
        $users[] = $row;
    }
}
$users_stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_admin_email'])) {
    $recipient_mode = $_POST['recipient_mode'] ?? '';
    $subject = trim($_POST['email_subject'] ?? '');
    $body = trim($_POST['email_message'] ?? '');
    $selected_users = $_POST['selected_users'] ?? [];
    $recipient_emails = [];

    if ($subject === '' || $body === '') {
        $_SESSION['error'] = "Subject and message are required.";
        header('Location: message.php');
        exit();
    }

    if ($recipient_mode === 'all') {
        foreach ($users as $user) {
            $recipient_emails[] = $user['email'];
        }
    } elseif ($recipient_mode === 'single' || $recipient_mode === 'multiple') {
        $selected_ids = array_values(array_unique(array_map('intval', (array) $selected_users)));
        if (empty($selected_ids)) {
            $_SESSION['error'] = "Please select at least one user.";
            header('Location: message.php');
            exit();
        }

        $placeholders = implode(',', array_fill(0, count($selected_ids), '?'));
        $types = str_repeat('i', count($selected_ids));
        $stmt = $conn->prepare("SELECT email FROM users WHERE role = 'user' AND id IN ($placeholders)");
        $stmt->bind_param($types, ...$selected_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                $recipient_emails[] = $row['email'];
            }
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Please choose a valid recipient mode.";
        header('Location: message.php');
        exit();
    }

    $recipient_emails = array_values(array_unique($recipient_emails));
    if (empty($recipient_emails)) {
        $_SESSION['error'] = "No valid user email addresses found.";
        header('Location: message.php');
        exit();
    }

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: GreenCredit <no-reply@greencredit.com>\r\n";

    $html_message = nl2br(htmlspecialchars($body));
    $email_body = "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <p>{$html_message}</p>
            <hr>
            <p style='font-size: 12px; color: #888;'>This message was sent by the GreenCredit Admin Team.</p>
        </body>
        </html>
    ";

    $sent = 0;
    $failed = 0;
    foreach ($recipient_emails as $email) {
        if (mail($email, $subject, $email_body, $headers)) {
            $sent++;
        } else {
            $failed++;
        }
    }

    $_SESSION['success'] = "Email sent to {$sent} user(s)." . ($failed > 0 ? " Failed for {$failed} recipient(s)." : "");
    header('Location: message.php');
    exit();
}

// Handle deleting all messages
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_all'])) {
    $delete_query = "DELETE FROM contact_messages";
    if ($conn->query($delete_query)) {
        $_SESSION['success'] = "All messages deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting messages: " . $conn->error;
    }
    header('Location: message.php');
    exit();
}

$query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$messages_result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];
    $response = $_POST['response'];

    // Step 1: Get user's name and email from the message
    $get_user_stmt = $conn->prepare("SELECT name, email FROM contact_messages WHERE id = ?");
    $get_user_stmt->bind_param("i", $message_id);
    $get_user_stmt->execute();
    $user_result = $get_user_stmt->get_result();
    $user = $user_result->fetch_assoc();

    if (!$user) {
        $_SESSION['error'] = "User not found for this message.";
        header('Location: message.php');
        exit();
    }

    // Step 2: Update the message with admin's response
    $update_query = "UPDATE contact_messages SET response = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $response, $message_id);

    if ($stmt->execute()) {
    // Step 3: Send email to the user
    $to = $user['email'];
    $subject = "GreenCredit - Response to Your Message";

    $message = "
        <html>
        <head>
            <title>Response from GreenCredit</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <p>Dear {$user['name']},</p>
            <p>Thank you for reaching out to GreenCredit. Our administrator has carefully reviewed your message and provided the following response:</p>

            <blockquote style='border-left: 4px solid #4CAF50; padding-left: 10px; margin: 15px 0; background: #f9f9f9;'>
                {$response}
            </blockquote>

            <p>If you have further questions or require additional assistance, feel free to reply to this email or contact us again via our support page.</p>

            <p>Kind regards,<br>
            <strong>GreenCredit Admin Team</strong></p>

            <hr style='border: none; border-top: 1px solid #ddd;'>
            <p style='font-size: 12px; color: #888;'>This is an automated message. Please do not reply directly to this email.</p>
        </body>
        </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: GreenCredit <no-reply@greencredit.com>\r\n";

    mail($to, $subject, $message, $headers);

    $_SESSION['success'] = "Response submitted and an email notification has been sent to the user.";
    } else {
        $_SESSION['error'] = "Error submitting response: " . $stmt->error;
    }

    header('Location: message.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Admin Responses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h2 class="text-center mb-0">Admin Responses to User Messages</h2>
        <a href="export_csv.php?table=messages" class="btn btn-outline-success">
            <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
    </div>

    <?php 
    if (isset($_SESSION['error'])) {
        echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
        unset($_SESSION['success']);
    }
    ?>

    <div class="alert alert-info">
        Email sending may require server mail configuration. This may not work on localhost but should work after hosting.
    </div>

    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            Send Email to Users
        </div>
        <div class="card-body">
            <form action="message.php" method="POST">
                <input type="hidden" name="send_admin_email" value="1">
                <div class="mb-3">
                    <label class="form-label">Recipients</label>
                    <select class="form-select" name="recipient_mode" id="recipientMode" required>
                        <option value="">Choose recipient mode</option>
                        <option value="all">All Users</option>
                        <option value="single">Single User</option>
                        <option value="multiple">Multiple Users</option>
                    </select>
                </div>
                <div class="mb-3" id="userSelectWrap" style="display:none;">
                    <label class="form-label">Select User(s)</label>
                    <select class="form-select" name="selected_users[]" id="selectedUsers">
                        <?php foreach ($users as $user): ?>
                            <option value="<?= (int) $user['id']; ?>">
                                <?= htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <input type="text" class="form-control" name="email_subject" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" name="email_message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-success">Send Email</button>
            </form>
        </div>
    </div>

    <!-- Delete All Messages Button -->
    <form class="text-center mb-4" action="message.php" method="POST" data-confirm="Are you sure you want to delete ALL messages? This action cannot be undone.">
        <button type="submit" name="delete_all" class="btn btn-danger mb-4">
            Delete All Messages
        </button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Contact Message</th>
                <th>Admin Response</th>
                <th>Message Sent On</th>
                <th>Respond</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($message = $messages_result->fetch_assoc()) { ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($message['name']); ?></strong><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($message['email']); ?><br>
                        <strong>Message:</strong><br>
                        <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                    </td>
                    <td>
                        <?php if (!empty($message['response'])) { ?>
                            <strong>Response:</strong><br>
                            <p><?php echo nl2br(htmlspecialchars($message['response'])); ?></p>
                        <?php } else { ?>
                            <p>No response yet</p>
                        <?php } ?>
                    </td>
                    <td>
                        <?php 
                        $created_at = new DateTime($message['created_at']);
                        echo $created_at->format('Y-m-d H:i:s');
                        ?>
                    </td>
                    <td>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#responseModal-<?php echo $message['id']; ?>">
                            Respond
                        </button>

                        <div class="modal fade" id="responseModal-<?php echo $message['id']; ?>" tabindex="-1" aria-labelledby="responseModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="responseModalLabel">Respond to Message</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form action="message.php" method="POST"onsubmit="return confirm('Are you sure you want to send this response?');">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                            <div class="mb-3">
                                                <label for="response" class="form-label">Your Response</label>
                                                <textarea class="form-control" id="response" name="response" rows="4" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Send Response</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mode = document.getElementById('recipientMode');
    const wrap = document.getElementById('userSelectWrap');
    const select = document.getElementById('selectedUsers');
    if (!mode || !wrap || !select) return;

    mode.addEventListener('change', function() {
        if (this.value === 'single') {
            wrap.style.display = 'block';
            select.multiple = false;
            select.required = true;
        } else if (this.value === 'multiple') {
            wrap.style.display = 'block';
            select.multiple = true;
            select.required = true;
        } else {
            wrap.style.display = 'none';
            select.multiple = false;
            select.required = false;
        }
    });
});
</script>

</body>
</html>
