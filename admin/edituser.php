<?php
session_start();
include '../includes/db.php';

// Redirect if admin is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch user data for the selected user (based on user ID passed in the URL)
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch user data from the database
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();

    // Check if user data was fetched
    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
    } else {
        $_SESSION['error'] = "User not found!";
        header('Location: manageuser.php'); // Redirect to the user management page if the user does not exist
        exit();
    }
}

// Handle profile update (if form is submitted)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Password validation: at least 8 characters, contains letters, numbers, and special characters
    $password_pattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    if (!empty($password) && !preg_match($password_pattern, $password)) {
        $_SESSION['error'] = "Password must be at least 8 characters long and contain letters, numbers, and special characters.";
        header('Location: edituser.php?id=' . $user_id); // Stay on the edit page
        exit();
    }

    // Check if password is provided and hash it
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $hashed_password = $user_data['password'];  // Retain the original password if no new one is provided
    }

    // Update user data in the database
    $update_query = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        header('Location: manageuser.php'); // Redirect to manage users page after updating the profile
        exit();
    } else {
        $_SESSION['error'] = "Error updating profile: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>

<!-- Navbar for Admin -->
<?php include 'includes/header.php'; ?>

<!-- Edit User Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">Edit User Profile</h2>

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

    <!-- Profile Information Form -->
    <form action="edituser.php?id=<?php echo $user_id; ?>" method="POST" onsubmit="return validatePassword()">
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">New Password (Leave blank to keep current)</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter a new password if you want to change it">
                    <div id="password-error" style="color: red; display: none;">Password must be at least 8 characters long and contain letters, numbers, and special characters.</div>
                </div>

                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </div>
    </form>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Password validation function
    function validatePassword() {
        var password = document.getElementById('password').value;
        var passwordPattern = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        var errorDiv = document.getElementById('password-error');

        if (password && !passwordPattern.test(password)) {
            errorDiv.style.display = 'block';
            return false;
        } else {
            errorDiv.style.display = 'none';
            return true;
        }
    }
</script>

</body>
</html>
