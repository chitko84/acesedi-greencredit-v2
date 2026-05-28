<?php 
include 'includes/db.php';
session_start();

// Check if the email already exists
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get and trim email
    $email = trim($_POST['email'] ?? '');

    // 1) Basic email format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header('Location: register.php');
        exit();
    }

    // 2) Restrict to official AIU emails only
    $allowedDomains = ['student.aiu.edu.my', 'aiu.edu.my'];
    $emailDomain = substr(strrchr(strtolower($email), "@"), 1);

    if (!in_array($emailDomain, $allowedDomains, true)) {
        $_SESSION['error'] = "Only official AIU emails (@student.aiu.edu.my or @aiu.edu.my) are allowed.";
        header('Location: register.php');
        exit();
    }

    // Query to check if the email already exists in the database
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "This email is already registered. Please log in.";
        header('Location: login.php');
        exit();
    }

    $name = $_POST['name'];
    $date_of_birth = $_POST['date_of_birth'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $department = $_POST['department'];
    $program_of_study = $_POST['program_of_study'];
    $intake = $_POST['intake'];
    $country = $_POST['country'];
    $gender = $_POST['gender'];
    $expected_graduation_year = $_POST['expected_graduation_year'];

    // Validate phone number (at least 5 digits, can be longer)
    if (!preg_match('/^[0-9]{5,}$/', $phone_number)) {
        $_SESSION['error'] = "Please enter a valid phone number (at least 5 digits).";
        header('Location: register.php');
        exit();
    }

    // Validate password (at least 8 characters, must contain letters, numbers, and special characters)
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $_SESSION['error'] = "Password must be at least 8 characters long and contain letters, numbers, and special characters.";
        header('Location: register.php');
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
        header('Location: register.php');
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Handle file upload for profile picture (optional)
    $profile_pic = 'default-profile.jpg'; 
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $upload_dir = 'user/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $profile_pic = $upload_dir . uniqid() . '.' . $file_ext;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic);
    }

    $role = 'user';

    // Insert user into database
    $query = "INSERT INTO users 
            (name, date_of_birth, phone_number, email, password, role, profile_pic, 
             department, program_of_study, intake, country, gender, expected_graduation_year) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssssss", 
        $name, $date_of_birth, $phone_number, $email, $hashed_password, $role, $profile_pic, 
        $department, $program_of_study, $intake, $country, $gender, $expected_graduation_year
    );

    if ($stmt->execute()) {
        // Registration successful - send email notification
        $all_email_sent = true;
        $admin_to = 'chitko.ko@student.aiu.edu.my';
        $admin_subject = 'New User Registration on GreenCredit';
        $admin_message = "
            <html>
            <head>
                <title>New User Registration</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #2E8B57; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; }
                    .footer { margin-top: 20px; text-align: center; font-size: 0.8em; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>New User Registered</h2>
                    </div>
                    <div class='content'>
                        <p>A new user has registered on GreenCredit:</p>
                        <ul>
                            <li><strong>Name:</strong> $name</li>
                            <li><strong>Email:</strong> $email</li>
                            <li><strong>Phone:</strong> $phone_number</li>
                            <li><strong>Date of Birth:</strong> $date_of_birth</li>
                            <li><strong>Registration Date:</strong> ".date('Y-m-d H:i:s')."</li>
                        </ul>
                    </div>
                    <div class='footer'>
                        <p>This is an automated notification from GreenCredit</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Always set content-type when sending HTML email
        $admin_headers = "MIME-Version: 1.0" . "\r\n";
        $admin_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        
        // Use a real domain you control for the From address
        $admin_headers .= "From: GreenCredit <acesediaiuedu@ace-sedi.aiu.edu.my>" . "\r\n";
        
        // Send the email
        if(!mail($admin_to, $admin_subject, $admin_message, $admin_headers)){
            error_log("Failed to send admin email notification");
            $all_email_sent = false;
        };
        
        
        $user_to = $email;
        $user_subject = 'Welcome to GreenCredit!';
        
        $login_link = 'https://ace-sedi.aiu.edu.my/greenCredits/login.php';
        
        $user_message = "
            <html>
            <head>
                <title>Welcome to GreenCredit</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #2E8B57; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; }
                    .footer { margin-top: 20px; text-align: center; font-size: 0.8em; color: #666; }
                    .button {
                        background-color: #2E8B57;
                        color: white;
                        padding: 10px 20px;
                        text-decoration: none;
                        border-radius: 5px;
                        display: inline-block;
                        margin: 15px 0;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Welcome to GreenCredit!</h2>
                    </div>
                    <div class='content'>
                        <p>Dear $name,</p>
                        <p>Thank you for registering with GreenCredit. Your account has been successfully created.</p>
                        <p>You can now login using your credentials:</p>
                        <a href='$login_link' class='button'>Login to Your Account</a>
                        <p>Or copy this link to your browser:<br>$login_link</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message from GreenCredit</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $user_headers = "MIME-Version: 1.0" . "\r\n";
        $user_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $user_headers .= "From: GreenCredit <acesediaiuedu@ace-sedi.aiu.edu.my>" . "\r\n";
        
        if(!mail($user_to, $user_subject, $user_message, $user_headers)){
            error_log("Failed to send user email notification");
            $all_email_sent = false;
        };
        
        if ($all_email_sent) {
            $_SESSION['success'] = "Registration successful! You can now login.";
        } else {
            $_SESSION['success'] = "Registration successful! You can now log in. (Email notifications failed)";
        }
    
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary-green: #2E8B57;
        --light-green: #E8F5E9;
        --dark-green: #1B5E20;
        --accent-green: #81C784;
        --leaf-green: #4CAF50;
        --white: #FFFFFF;
        --light-gray: #F9FBF8;
        --text-gray: #3E3E3E;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: url('assets/images/cfgs-pic.jpg') center center / cover no-repeat fixed;
        color: var(--text-gray);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .auth-container {
        max-width: 520px;
        margin: auto;
        padding: 3rem 2.5rem;
        background: rgba(255, 255, 255, 0.94);
        border-radius: 20px;
        box-shadow: 0 24px 60px rgba(20, 36, 26, 0.24);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        width: 90%;
    }

    body.dark-mode .auth-container {
        background: rgba(17, 24, 20, 0.94) !important;
        color: #edf6ef !important;
        border-color: rgba(190, 233, 205, 0.16) !important;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.48);
    }

    body.dark-mode .auth-title,
    body.dark-mode .auth-container .form-label,
    body.dark-mode .auth-footer {
        color: #edf6ef !important;
    }

    body.dark-mode .auth-subtitle,
    body.dark-mode .form-text {
        color: #b8c8bd !important;
    }

    body.dark-mode .auth-container .form-control,
    body.dark-mode .auth-container .form-select,
    body.dark-mode .auth-container .input-group-text {
        background: #101713 !important;
        color: #edf6ef !important;
        border-color: rgba(190, 233, 205, 0.18) !important;
    }

    body.dark-mode .auth-container .form-control::placeholder {
        color: #839187;
    }

    .auth-header {
        text-align: center;
        margin-bottom: 2.5rem;
    }

    .logo {
        width: 80px;
        margin-bottom: 1.5rem;
        filter: drop-shadow(0 2px 4px rgba(46, 139, 87, 0.2));
    }

    .auth-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        color: var(--dark-green);
        margin-bottom: 0.5rem;
        font-size: 1.8rem;
    }

    .auth-subtitle {
        color: var(--text-gray);
        opacity: 0.8;
        font-weight: 400;
        font-size: 1rem;
    }

    .form-label {
        font-weight: 500;
        color: var(--text-gray);
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-control {
        padding: 0.85rem 1.25rem;
        border-radius: 12px;
        border: 1px solid #E0E0E0;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        background-color: var(--light-gray);
        font-size: 0.95rem;
    }

    .form-control:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 0.25rem rgba(46, 139, 87, 0.18);
    }

    .input-group-text {
        background-color: var(--light-gray);
        border-radius: 0 12px 12px 0;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 0 1.25rem;
    }

    .input-group-text:hover {
        background-color: rgba(129, 199, 132, 0.3);
    }

    .btn-auth {
        width: 100%;
        padding: 1rem;
        border-radius: 12px;
        background-color: var(--primary-green);
        border: none;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        font-size: 1rem;
        box-shadow: 0 4px 12px rgba(46, 139, 87, 0.25);
        margin-top: 1rem;
    }

    .btn-auth:hover {
        background-color: var(--dark-green);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(46, 139, 87, 0.3);
    }

    .btn-auth:active {
        transform: translateY(0);
    }

    .form-text {
        font-size: 0.85rem;
        color: #666;
        margin-top: 0.25rem;
    }

    .auth-footer {
        text-align: center;
        margin-top: 2rem;
        font-size: 0.95rem;
    }

    .auth-link {
        color: var(--primary-green);
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
    }

    .auth-link:hover {
        color: var(--dark-green);
    }

    .auth-link:after {
        content: '';
        position: absolute;
        width: 100%;
        height: 2px;
        bottom: -2px;
        left: 0;
        background-color: var(--primary-green);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .auth-link:hover:after {
        transform: scaleX(1);
    }

    .alert {
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .alert-danger {
        border-left: 4px solid #dc3545;
    }

    .alert-success {
        border-left: 4px solid var(--primary-green);
    }

    .password-strength {
        height: 4px;
        background-color: #e0e0e0;
        border-radius: 2px;
        margin-top: 0.5rem;
        overflow: hidden;
    }

    .password-strength-bar {
        height: 100%;
        width: 0%;
        background-color: #dc3545;
        transition: width 0.3s ease, background-color 0.3s ease;
    }

    .profile-pic-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--light-green);
        display: none;
        margin: 0 auto 1rem;
    }

    @media (max-width: 576px) {
        body {
            padding: 1rem;
        }

        .auth-container {
            padding: 2rem 1.5rem;
            margin: 2rem auto;
        }

        .auth-title {
            font-size: 1.5rem;
        }
    }

    body, html {
        width: 100%;
        overflow-x: hidden;
    }

    .navbar, footer {
        width: 100%;
    }

    @media (max-width: 576px) {
        .navbar, footer {
            width: 100%;
            padding-left: 0;
            padding-right: 0;
        }

        .navbar .container, footer .container {
            width: 100%;
            padding-left: 0;
            padding-right: 0;
        }

        .footer-links, .nature-divider, .copyright {
            text-align: center;
            width: 100%;
        }

        footer {
            padding: 2rem 0;
        }

        .footer-links a {
            display: inline-block;
            margin: 0.5rem;
            font-size: 0.9rem;
        }

        .social-media-links a {
            display: inline-block;
            margin: 0.5rem;
            font-size: 1.3rem;
        }
    }
</style>
</head>

<?php include 'includes/header.php'; ?>

<body>
    <div class="container py-4">
        <div class="auth-container">
            <div class="auth-header">
                <img src="assets/images/gc_logo.jpg" alt="GreenCredit Logo" class="logo">
                <h2 class="auth-title">Join Our Community</h2>
                <p class="auth-subtitle">Start your sustainability journey with GreenCredit</p>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form action="register.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                        <input type="text" class="form-control" id="phone_number" name="phone_number" placeholder="01123456789" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="email" class="form-label">Email Address <strong style="color:red;">(@student.aiu.edu.my or @aiu.edu.my only)</strong></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="your@student.aiu.edu.my" pattern="^[A-Za-z0-9._%+\-]+@(student\.aiu\.edu\.my|aiu\.edu\.my)$" title="Use an email ending with @student.aiu.edu.my or @aiu.edu.my" required>
                    </div>
                    <small class="form-text">Only official AIU email addresses are accepted.</small>
                </div>
                
                <!-- Department -->
                <div class="mb-4">
                    <label for="department" class="form-label">Department</label>
                    <select class="form-control" id="department" name="department" required>
                        <option value="">-- Select Department --</option>
                        <option value="School Of Business & Social Sciences">School of Business & Social Sciences</option>
                        <option value="School Of Education & Human Sciences">School of Education & Human Sciences</option>
                        <option value="School Of Computing and Informatics">School of Computing and Informatics</option>
                        <option value="Centre for Foundation and General Studies">Centre for Foundation and General Studies</option>
                        <option value="Language Center (LC)">Language Center (LC)</option>
                    </select>
                </div>
                
                <!-- Program of Study -->
                <div class="mb-4">
                    <label for="program_of_study" class="form-label">Program of Study</label>
                    <select class="form-control" id="program_of_study" name="program_of_study" required>
                        <option value="">-- Select Program --</option>
                    </select>
                </div>
                
                <!-- Intake -->
                <div class="mb-4">
                    <label for="intake" class="form-label">Intake</label>
                    <select class="form-control" id="intake" name="intake" required>
                        <option value="">-- Select Intake --</option>
                    </select>
                </div>
                
                <!-- Country -->
                <div class="mb-4">
                    <label for="country" class="form-label">Country of Origin</label>
                    <input list="countryList" class="form-control" id="country" name="country" placeholder="Type to search..." required>
                    <datalist id="countryList">
                    </datalist>
                </div>
                
                <!-- Gender -->
                <div class="mb-4">
                    <label for="gender" class="form-label">Gender</label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="">-- Select Gender --</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                
                <!-- Expected Graduation Year -->
                <div class="mb-4">
                    <label for="expected_graduation_year" class="form-label">Expected Graduation Year</label>
                    <select class="form-control" id="expected_graduation_year" name="expected_graduation_year" required>
                        <option value="">-- Select Year --</option>
                    </select>
                </div>


                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
                        <span class="input-group-text" id="togglePassword">
                            <i class="bi bi-eye-slash-fill"></i>
                        </span>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <small class="form-text">Minimum 8 characters with letters, numbers, and special characters</small>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                        <span class="input-group-text" id="toggleConfirmPassword">
                            <i class="bi bi-eye-slash-fill"></i>
                        </span>
                    </div>
                </div>

                <!--<div class="mb-4">-->
                <!--    <label for="profile_pic" class="form-label">Profile Picture (Optional)</label>-->
                <!--    <img id="profilePicPreview" class="profile-pic-preview" alt="Profile preview">-->
                <!--    <input type="file" class="form-control" id="profile_pic" name="profile_pic" accept="image/*">-->
                <!--</div>-->

                <button type="submit" class="btn btn-primary btn-auth">
                    <i class="bi bi-person-plus-fill me-2"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php" class="auth-link">Sign in here</a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
            } else {
                password.type = 'password';
                icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
            }
        });

        // Toggle confirm password visibility
        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPassword = document.getElementById('confirm_password');
            const icon = this.querySelector('i');
            
            if (confirmPassword.type === 'password') {
                confirmPassword.type = 'text';
                icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
            } else {
                confirmPassword.type = 'password';
                icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
            }
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 25;
            if (password.length >= 12) strength += 25;
            
            // Character diversity
            if (/[A-Z]/.test(password)) strength += 15;
            if (/[0-9]/.test(password)) strength += 15;
            if (/[^A-Za-z0-9]/.test(password)) strength += 20;
            
            // Update strength bar
            strengthBar.style.width = strength + '%';
            
            // Update color based on strength
            if (strength < 50) {
                strengthBar.style.backgroundColor = '#dc3545'; // Red
            } else if (strength < 75) {
                strengthBar.style.backgroundColor = '#fd7e14'; // Orange
            } else {
                strengthBar.style.backgroundColor = '#28a745'; // Green
            }
        });

        // Profile picture preview
        const profilePicInput = document.getElementById('profile_pic');
        if (profilePicInput) profilePicInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                const preview = document.getElementById('profilePicPreview');
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            }
        });
        
        const navbarToggler = document.querySelector('.navbar-toggler');
        const navbarCollapse = document.getElementById('navbarNav');

        // Toggle open/close on icon click
        navbarToggler.addEventListener('click', function (e) {
            e.stopPropagation(); // Prevent closing immediately on open
            if (navbarCollapse.classList.contains('show')) {
                new bootstrap.Collapse(navbarCollapse).hide();
            } else {
                new bootstrap.Collapse(navbarCollapse).show();
            }
        });

        // Close navbar when clicking outside
        document.addEventListener('click', function (event) {
            const isClickInsideNavbar = navbarCollapse.contains(event.target) || navbarToggler.contains(event.target);
            if (!isClickInsideNavbar && navbarCollapse.classList.contains('show')) {
                new bootstrap.Collapse(navbarCollapse).hide();
            }
        });
    </script>

    <script>
        const programsByDept = {
            "School Of Business & Social Sciences": [
                "Bachelor of Business Administration (Honours)",
                "Bachelor of Business Administration with Computer Science (Honours)",
                "Bachelor of Business Administration (Honours) (Marketing)",
                "Bachelor of Business Administration (Honours) (Human Resource Management)",
                "Bachelor of Economics (Honours)",
                "Bachelor of Social Development (Honours)",
                "Bachelor of Finance (Islamic Finance) (Honours)",
                "Bachelor of Politics and International Relations (Honours)",
                "Master of Business Management",
                "Master in Social Business",
                "Doctor of Philosophy (Business Management)"
            ],
            "School Of Education & Human Sciences": [
                "Bachelor of Elementary Education (Honours)",
                "Bachelor in Early Childhood Education (Honours)",
                "Bachelor of Media and Communication (Honours)",
                "Master of Education",
                "Doctor of Philosophy (Education)"
            ],
            "School Of Computing and Informatics": [
                "Bachelor in Computer Science (Honours)",
                "Bachelor in Data Science (Honours)",
                "Master of Computing (by Research)",
                "Doctor of Philosophy in Computer Science"
            ],
            "Centre for Foundation and General Studies": [
                "Foundation in Computing",
                "Foundation in Arts"
            ],
            "Language Center (LC)": [] // No programs for LC
        };

        document.getElementById('department').addEventListener('change', function() {
            const dept = this.value;
            const programSelect = document.getElementById('program_of_study');
            
            // Clear existing options
            programSelect.innerHTML = '<option value="">-- Select Program --</option>';
            
            if (dept === "Language Center (LC)") {
                // Add a disabled option with message
                let opt = document.createElement('option');
                opt.value = "";
                opt.textContent = "No program selection needed for LC";
                opt.disabled = true;
                opt.selected = true;
                programSelect.appendChild(opt);
            } else {
                // Add normal program options for other departments
                if (programsByDept[dept]) {
                    programsByDept[dept].forEach(p => {
                        let opt = document.createElement('option');
                        opt.value = p;
                        opt.textContent = p;
                        programSelect.appendChild(opt);
                    });
                }
            }
        });

        // Intake options: March/October 2020 → 2031
        const intakeSelect = document.getElementById('intake');
        for (let year = 2020; year <= 2031; year++) {
            ["March", "October"].forEach(month => {
                let opt = document.createElement('option');
                opt.value = `${month} ${year}`;
                opt.textContent = `${month} ${year}`;
                intakeSelect.appendChild(opt);
            });
        }

        // Country list (shortened for brevity)
        const countries = [
            "Afghanistan","Albania","Algeria","Andorra","Angola","Antigua and Barbuda","Argentina","Armenia","Australia","Austria",
            "Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bhutan","Bolivia",
            "Bosnia and Herzegovina","Botswana","Brazil","Brunei","Bulgaria","Burkina Faso","Burundi","Côte d'Ivoire","Cabo Verde",
            "Cambodia","Cameroon","Canada","Central African Republic","Chad","Chile","China","Colombia","Comoros","Costa Rica",
            "Croatia","Cuba","Cyprus","Czechia","Democratic Republic of the Congo","Denmark","Djibouti","Dominica","Dominican Republic",
            "Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Eswatini","Ethiopia","Federated States of Micronesia",
            "Fiji","Finland","France","Gabon","Gambia","Georgia","Germany","Ghana","Greece","Grenada","Guatemala","Guinea",
            "Guinea-Bissau","Guyana","Haiti","Honduras","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel",
            "Italy","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kiribati","Kosovo","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon",
            "Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Madagascar","Malawi","Malaysia","Maldives","Mali",
            "Malta","Marshall Islands","Mauritania","Mauritius","Mexico","Moldova","Monaco","Mongolia","Montenegro","Morocco",
            "Mozambique","Myanmar","Namibia","Nauru","Nepal","Netherlands","New Zealand","Nicaragua","Niger","Nigeria","North Korea",
            "North Macedonia","Norway","Oman","Pakistan","Palau","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland",
            "Portugal","Qatar","Republic of the Congo","Romania","Russia","Rwanda","Saint Kitts and Nevis","Saint Lucia","Saint Vincent and the Grenadines",
            "Samoa","San Marino","Sao Tome and Principe","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore",
            "Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Korea","South Sudan","Spain","Sri Lanka","Sudan",
            "Suriname","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Timor-Leste","Togo","Tonga",
            "Trinidad and Tobago","Tunisia","Turkey","Turkmenistan","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom",
            "United States","Uruguay","Uzbekistan","Vanuatu","Vatican City","Venezuela","Vietnam","Yemen","Zambia","Zimbabwe"
        ];

        const countryList = document.getElementById('countryList');
        countries.forEach(c => {
            let opt = document.createElement('option');
            opt.value = c;
            countryList.appendChild(opt);
        });

        // Expected graduation years
        const gradSelect = document.getElementById('expected_graduation_year');
        const currentYear = new Date().getFullYear();
        for (let y = currentYear; y <= 2040; y++) {
            let opt = document.createElement('option');
            opt.value = y;
            opt.textContent = y;
            gradSelect.appendChild(opt);
        }
        
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
