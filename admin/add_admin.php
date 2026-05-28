<?php
session_start();
include '../includes/db.php';

// --- Basic DB connection check ---
if (!isset($conn) || (isset($conn) && $conn->connect_errno)) {
    $_SESSION['error'] = 'Database connection error.';
    header('Location: manageuser.php');
    exit();
}

// Only allow logged-in admins to use this page
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Super Admin
$superAdmins = [47];
if (!in_array($_SESSION['user_id'], $superAdmins, true)) {
    $_SESSION['error'] = "Forbidden";
    header('Location: manageuser.php');
    exit();
}

// --- Helper: safe POST getter ---
function post_str(string $k, string $default = ''): string {
    return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $default;
}

// --- CSRF: generate token for form when first loaded ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CSRF check ---
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid CSRF token.';
        header('Location: manageuser.php');
        exit();
    }

    // required
    $name  = post_str('name');
    $email = post_str('email');
    $pass  = $_POST['password'] ?? '';

    // optional (defaults chosen to avoid NOT NULL DB errors)
    $date_of_birth = $_POST['date_of_birth'] ?? '2000-01-01';
    $phone_number  = post_str('phone_number', '0000000000');
    $program_of_study = post_str('program_of_study', 'N/A');
    $intake = post_str('intake', 'N/A');
    $country = post_str('country', 'N/A');
    $gender = in_array($_POST['gender'] ?? '', ['Male','Female','Other'], true) ? $_POST['gender'] : 'Other';
    $department = post_str('department', 'N/A');
    $expected_graduation_year = intval($_POST['expected_graduation_year'] ?? (date('Y') + 3));
    $profile_pic = 'default-profile.jpg';
    $eco_points = 0;

    // basic validation
    if ($name === '' || $email === '' || $pass === '') {
        $_SESSION['error'] = "Name, email and password are required.";
        header('Location: manageuser.php');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email address.";
        header('Location: manageuser.php');
        exit();
    }

    // validate expected year range (example)
    $currentYear = (int)date('Y');
    if ($expected_graduation_year < $currentYear || $expected_graduation_year > ($currentYear + 15)) {
        $_SESSION['error'] = 'Expected graduation year is out of allowed range.';
        header('Location: manageuser.php');
        exit();
    }

    // hash password and check
    $passwordHash = password_hash($pass, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        $_SESSION['error'] = 'Failed to hash password.';
        header('Location: manageuser.php');
        exit();
    }

    // -- 1) Check if user with this email exists (prepared stmt) --
    $checkSql = "SELECT id, role FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        $_SESSION['error'] = "DB prepare error: " . $conn->error;
        header('Location: manageuser.php');
        exit();
    }

    $checkStmt->bind_param('s', $email);
    if (!$checkStmt->execute()) {
        $_SESSION['error'] = 'DB execute error (check): ' . $checkStmt->error;
        header('Location: manageuser.php');
        exit();
    }

    // mysqlnd fallback: try get_result(), otherwise use store_result() + bind_result()
    $existingUserId = null;
    $existingUserRole = null;

    if (method_exists($checkStmt, 'get_result')) {
        $checkRes = $checkStmt->get_result();
        if ($checkRes && $checkRes->num_rows > 0) {
            $row = $checkRes->fetch_assoc();
            $existingUserId = (int)$row['id'];
            $existingUserRole = isset($row['role']) ? (string)$row['role'] : null;
        }
    } else {
        // fallback for PHP builds without mysqlnd
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $checkStmt->bind_result($f_id, $f_role);
            $checkStmt->fetch();
            $existingUserId = (int)$f_id;
            $existingUserRole = isset($f_role) ? (string)$f_role : null;
        }
    }

    // close check statement
    $checkStmt->close();

    if ($existingUserId !== null) {
        // Normalize role comparison (case-insensitive)
        $roleNormalized = strtolower((string)$existingUserRole);

        if ($roleNormalized === 'admin') {
            // user is already admin: treat as informational error (your original behavior)
            $_SESSION['error'] = "This user is already an admin.";
            header('Location: manageuser.php');
            exit();
        }

        // user exists but not admin -> promote (UPDATE)
        $updateSql = "UPDATE users SET 
                        name = ?, 
                        password = ?, 
                        role = 'admin',
                        date_of_birth = ?, 
                        phone_number = ?, 
                        program_of_study = ?, 
                        intake = ?, 
                        country = ?, 
                        gender = ?, 
                        department = ?, 
                        expected_graduation_year = ?, 
                        profile_pic = ?, 
                        eco_points = ?
                      WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        if (!$stmt) {
            $_SESSION['error'] = "DB prepare error: " . $conn->error;
            header('Location: manageuser.php');
            exit();
        }

        // types: 9 strings, 1 int, 1 string, 2 ints (uid last) => "sssssssssisii"
        $stmt->bind_param(
            "sssssssssisii",
            $name,
            $passwordHash,
            $date_of_birth,
            $phone_number,
            $program_of_study,
            $intake,
            $country,
            $gender,
            $department,
            $expected_graduation_year,
            $profile_pic,
            $eco_points,
            $existingUserId
        );

        if ($stmt->execute()) {
            $_SESSION['success'] = "User (ID: $existingUserId) upgraded to admin successfully.";
        } else {
            $_SESSION['error'] = "Failed to promote user: " . $stmt->error;
        }
        $stmt->close();

        header('Location: manageuser.php');
        exit();

    } else {
        // user does not exist -> insert new admin providing all NOT NULL fields
        $insertSql = "INSERT INTO users 
            (name, date_of_birth, phone_number, email, password, role, eco_points, profile_pic, program_of_study, intake, country, gender, department, expected_graduation_year)
            VALUES (?, ?, ?, ?, ?, 'admin', ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertSql);
        if (!$stmt) {
            $_SESSION['error'] = "DB prepare error: " . $conn->error;
            header('Location: manageuser.php');
            exit();
        }

        // bind types: sssssissssssi
        $stmt->bind_param(
            "sssssissssssi",
            $name,
            $date_of_birth,
            $phone_number,
            $email,
            $passwordHash,
            $eco_points,
            $profile_pic,
            $program_of_study,
            $intake,
            $country,
            $gender,
            $department,
            $expected_graduation_year
        );

        if ($stmt->execute()) {
            $_SESSION['success'] = "New admin created successfully (ID: " . $stmt->insert_id . ")";
        } else {
            // handle duplicate-key gracefully
            $errno = $stmt->errno;
            if ($errno === 1062) { // ER_DUP_ENTRY
                $_SESSION['error'] = 'An account with that email already exists.';
            } else {
                $_SESSION['error'] = "Failed to create admin: " . $stmt->error;
            }
        }
        $stmt->close();

        header('Location: manageuser.php');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create / Promote Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container my-5" style="max-width:720px;">
    <h3 class="mb-3">Create or Promote Admin</h3>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form id="adminForm" method="post" class="card card-body">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>" />

        <div class="row g-2">
            <div class="col-md-6 mb-2">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required />
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" required />
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" minlength="6" required />
            </div>

            <!-- optional fields (helpful to avoid NOT NULL errors on insert) -->
            <div class="col-md-6 mb-2">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" value="<?= date('2000-01-01') ?>" />
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone_number" class="form-control" value="0000000000" />
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Program of Study</label>
                <input type="text" name="program_of_study" class="form-control" value="N/A" />
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Intake</label>
                <input type="text" name="intake" class="form-control" value="N/A" />
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Country</label>
                <input type="text" name="country" class="form-control" value="N/A" />
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="Other">Other</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" value="N/A" />
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Expected Graduation Year</label>
                <input type="number" name="expected_graduation_year" class="form-control" value="<?= date('Y') + 3 ?>" />
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-success" type="submit">Create / Promote</button>
            <a href="manageuser.php" class="btn btn-secondary">Back to Manage Users</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Dropdown toggle function
    function simpleDropdown(toggleSelector) {
        const toggle = document.querySelector(toggleSelector);
        if (!toggle) return;

        toggle.addEventListener('click', function (e) {
            e.preventDefault(); // prevent page jump
            const menu = toggle.nextElementSibling;
            if (!menu) return;

            menu.classList.toggle('show'); // toggle visibility
        });
    }

    simpleDropdown('#adminDropdown');
    simpleDropdown('#adminProfileDropdown');

    // Confirmation for admin creation/promotion
    const form = document.getElementById('adminForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const confirmed = confirm('Are you sure you want to create or promote this user as admin?');
            if (!confirmed) {
                e.preventDefault(); // cancel submission
            }
        });
    }
});
</script>

</body>
</html>
