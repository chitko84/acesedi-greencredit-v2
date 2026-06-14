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
        $reset_link = "https://greenyellow-llama-787938.hostingersite.com/greencredit/reset_password.php?token=$token";

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

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-green: #2E8B57;
        --light-green: #E8F5E9;
        --dark-green: #1B5E20;
        --white: #FFFFFF;
        --text-gray: #424242;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: url('assets/images/cfgs-pic.jpg') center center / cover no-repeat fixed !important;
        color: var(--text-gray);
        line-height: 1.6;
        min-height: 100vh;
    }

    .login-container {
        max-width: 500px;
        margin: 5rem auto;
        padding: 2.5rem;
        background: rgba(255, 255, 255, 0.94);
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(20, 36, 26, 0.24);
        backdrop-filter: blur(6px);
    }

    .login-title {
        font-weight: 700;
        color: var(--dark-green);
        margin-bottom: 0.5rem;
    }

    .form-label {
        font-weight: 500;
        color: var(--text-gray);
        margin-bottom: 0.5rem;
    }

    .form-control {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid #ddd;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 0.25rem rgba(46, 139, 87, 0.25);
    }

    .btn-login {
        width: 100%;
        padding: 0.75rem;
        border-radius: 8px;
        background-color: var(--primary-green);
        border: none;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .btn-login:hover {
        background-color: var(--dark-green);
        transform: translateY(-2px);
    }

    .alert-danger,
    .alert-success {
        border-radius: 8px;
        padding: 1rem;
    }

    @media (max-width: 576px) {
        .login-container {
            margin: 1.25rem;
            padding: 1.5rem;
        }
    }
</style>

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
