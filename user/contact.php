<?php
session_start();
include '../includes/db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Validate inputs
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['error'] = "All fields are required!";
        header('Location: contact.php');
        exit();
    }

    // Insert the message into the database
    $query = "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $name, $email, $message);

    if ($stmt->execute()) {
        // ---Send Email to All Admins ---
        $admin_emails = [
            'chitko.ko@student.aiu.edu.my',
            'second.admin@example.com',
        ];

        $subject = 'New Contact Form Message Submitted';
        $email_body = "
            <html>
            <head><title>New Contact Message</title></head>
            <body style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
                <div style='max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
                    <h2 style='text-align:center; color:#2E8B57;'>📩 New Contact Message</h2>
                    <p>You have received a new message from the <strong>GreenCredit</strong> contact form:</p>
                    <ul>
                        <li><strong>Name:</strong> {$name}</li>
                        <li><strong>Email:</strong> {$email}</li>
                        <li><strong>Message:</strong><br><em>{$message}</em></li>
                    </ul>
                    <p style='margin-top: 20px; font-size: 0.9em; color: #555; text-align:center;'>
                        Please log in to the admin panel to respond.
                    </p>
                </div>
            </body>
            </html>
        ";

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: GreenCredit <acesediaiuedu@ace-sedi.aiu.edu.my>\r\n";

        // Loop through all admins and send email
        foreach ($admin_emails as $admin_email) {
            mail($admin_email, $subject, $email_body, $headers);
        }

        $_SESSION['success'] = "Your message has been sent successfully!";
        header('Location: contact.php');
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
        header('Location: contact.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
</head>
<body>

<!-- Navbar for User -->
<?php include 'includes/header.php'; ?>

<!-- Contact Form Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">Contact Us</h2>

    <!-- Display error or success messages -->
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

    <!-- Contact Form -->
    <form action="contact.php" method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Your Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Your Email <span style="color: red;">(use the email that you logged in)</span></label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Your Message</label>
            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
