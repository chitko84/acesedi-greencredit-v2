<?php 
include 'includes/db.php';
session_start();

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header('Location: login.php');
        exit();
    }

    $allowed_roles = ['admin', 'user'];
    if (!in_array($role, $allowed_roles, true)) {
        $_SESSION['error'] = "Invalid role selected.";
        header('Location: login.php');
        exit();
    }

    $query = "SELECT id, name, role, profile_pic, eco_points, email, password
              FROM users
              WHERE email = ? AND role = ?
              LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['profile_pic'] = $user['profile_pic'];
        $_SESSION['eco_points'] = $user['eco_points'];
        $_SESSION['user_email'] = $user['email'];

        header('Location: ' . ($role === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php'));
        exit();
    } else {
        $_SESSION['error'] = "Invalid email, password, or role. Please try again.";
        header('Location: login.php');
        exit();
    }
}
?>

<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #2E8B57;
            --light-green: #E8F5E9;
            --dark-green: #1B5E20;
            --accent-green: #81C784;
            --white: #FFFFFF;
            --light-gray: #F5F5F5;
            --text-gray: #424242;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: url('assets/images/cfgs-pic.jpg') center center / cover no-repeat fixed;
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
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-title {
            font-weight: 700;
            color: var(--dark-green);
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--text-gray);
            opacity: 0.8;
            font-weight: 400;
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
        
        .input-group-text {
            background-color: var(--white);
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .input-group-text:hover {
            background-color: var(--light-green);
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
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .role-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .role-option {
            flex: 1;
            position: relative;
        }
        
        .role-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .role-label {
            display: block;
            padding: 1rem;
            background-color: var(--light-green);
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #1f2933 !important;
        }

        .role-label div {
            color: #1f2933 !important;
            font-weight: 600;
        }
        
        .role-option input[type="radio"]:checked + .role-label {
            border-color: var(--primary-green);
            background-color: rgba(46, 139, 87, 0.1);
            font-weight: 500;
            color: #1f2933 !important;
        }

        body.dark-mode .role-label,
        body.dark-mode .role-label div,
        body.dark-mode .role-option input[type="radio"]:checked + .role-label {
            color: #1f2933 !important;
        }
        
        .role-option input[type="radio"]:focus + .role-label {
            box-shadow: 0 0 0 0.25rem rgba(46, 139, 87, 0.25);
        }
        
        .role-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-green);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #999;
            font-size: 0.9rem;
        }
        
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        
        .divider::before {
            margin-right: 1rem;
        }
        
        .divider::after {
            margin-left: 1rem;
        }
        
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .register-link a {
            color: var(--primary-green);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .register-link a:hover {
            color: var(--dark-green);
            text-decoration: underline;
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
            
            .role-selector {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h2 class="login-title">Welcome Back</h2>
        <p class="login-subtitle">Login to your GreenCredit account</p>
    </div>
    
    <?php 
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo $_SESSION['error'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['error']);
    }
    ?>

    <form action="login.php" method="POST">
        <div class="mb-4">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                <span class="input-group-text" id="togglePassword">
                    <i class="bi bi-eye-slash"></i>
                </span>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Login as</label>
            <div class="role-selector">
                <div class="role-option">
                    <input type="radio" id="role_admin" name="role" value="admin" required>
                    <label for="role_admin" class="role-label">
                        <i class="bi bi-shield-lock role-icon"></i>
                        <div>Admin</div>
                    </label>
                </div>
                <div class="role-option">
                    <input type="radio" id="role_user" name="role" value="user" required>
                    <label for="role_user" class="role-label">
                        <i class="bi bi-person role-icon"></i>
                        <div>Student</div>
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-login mb-3">
            <i class="bi bi-box-arrow-in-right"></i> Login
        </button>
    </form>
    
    <div class="text-end mb-3">
    <a href="forgot_password.php" class="text-decoration-none" style="color: var(--primary-green); font-weight: 500;">Forgot Password?</a>
    </div>


    <div class="register-link">
        Don't have an account? <a href="register.php">Create one</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    
    togglePassword.addEventListener('click', function (e) {
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
        
        const icon = passwordField.type === 'password' ? 'bi-eye-slash' : 'bi-eye';
        togglePassword.querySelector('i').className = `bi ${icon}`;
    });
</script>
<script>
    document.addEventListener('click', function (event) {
        const navbarCollapse = document.getElementById('navbarNav');
        const navbarToggler = document.querySelector('.navbar-toggler');

        if (!navbarCollapse.contains(event.target) && !navbarToggler.contains(event.target)) {
            const bootstrapCollapse = new bootstrap.Collapse(navbarCollapse, {
                toggle: false
            });
            bootstrapCollapse.hide();
        }
    });
</script>
</body>
</html>
