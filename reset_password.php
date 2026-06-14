<?php
include 'includes/db.php';
session_start();

$token = $_GET['token'] ?? '';

if (!$token) {
    $_SESSION['error'] = "Invalid or missing token.";
    header("Location: login.php");
    exit();
}

// Check if the token is valid and not expired
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$reset = $result->fetch_assoc();

if (!$reset || strtotime($reset['expires_at']) < time()) {
    $_SESSION['error'] = "This reset link is invalid or has expired.";
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters.";
    } else {
        // Hash the new password
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the user's password
        $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->bind_param("ss", $hashed, $reset['email']);
        $update->execute();

        // Remove the token
        $delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $delete->bind_param("s", $reset['email']);
        $delete->execute();

        $_SESSION['message'] = "Your password has been reset. You can now log in.";
        header("Location: login.php");
        exit();
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

    .login-container .btn-success {
        width: 100%;
        padding: 0.75rem;
        border-radius: 8px;
        background-color: var(--primary-green);
        border: none;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .login-container .btn-success:hover {
        background-color: var(--dark-green);
        transform: translateY(-2px);
    }

    .alert-danger {
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
    <h2 class="login-title text-center">Reset Password</h2>
    <p class="text-center">Enter a new password for your account.</p>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required minlength="6">
        </div>
        <button type="submit" class="btn btn-success w-100">Reset Password</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
