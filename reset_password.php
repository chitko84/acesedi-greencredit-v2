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
