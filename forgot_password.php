<?php
include 'includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if the email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Generate a secure token and expiration (1 hour)
        $token = bin2hex(random_bytes(32));
        $expires_at = date("Y-m-d H:i:s", time() + 3600);

        // Store in database (create if not exists: password_resets table)
        $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token=?, expires_at=?");
        $insert->bind_param("sssss", $email, $token, $expires_at, $token, $expires_at);
        $insert->execute();

        // Create reset link
        $reset_link = "https://ace-sedi.aiu.edu.my/greenCredits/reset_password.php?token=$token";

        // Send email
        $subject = "Password Reset Request - GreenCredits";
        $message = "Hi,\n\nYou requested to reset your GreenCredits password.\n\nClick or Copy the link below to reset it:\n$reset_link\n\nThis link will expire in 1 hour.\n\nIf you didn’t request this, please ignore this email.";
        $headers = "From: no-reply@ace-sedi.aiu.edu.my";

        if (mail($email, $subject, $message, $headers)) {
            $_SESSION['message'] = "A password reset link has been sent to your email. Please check your inbox or spam folder.";
        } else {
            $_SESSION['error'] = "Failed to send email. Please try again later.";
        }
    } else {
        $_SESSION['error'] = "Email not found. Please check and try again.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="login-container">
    <h2 class="login-title text-center">Forgot Password</h2>
    <p class="text-center">Enter your registered email to receive a reset link.</p>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="youremail@student.aiu.edu.my" required>
        </div>
        <button type="submit" class="btn btn-primary btn-login w-100">Send Reset Link</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
