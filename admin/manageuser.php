<?php
session_start();
include '../includes/db.php';

// Check if the admin is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if the logged-in user is a super admin
$logged_in_user_id = $_SESSION['user_id'];
$is_super_admin = false;
$check_super_admin_query = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($check_super_admin_query);
$stmt->bind_param("i", $logged_in_user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
if ($user_data['role'] === 'admin') {
    // Super admins
    $is_super_admin = in_array($logged_in_user_id, [47]);
}

// ---------------- Bulk delete handling (super-admin only) ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
    if (!$is_super_admin) {
        $_SESSION['error'] = "You do not have permission to delete users.";
        header('Location: manageuser.php');
        exit();
    }

    $selected = $_POST['selected_users'] ?? [];
    if (!is_array($selected) || count($selected) === 0) {
        $_SESSION['error'] = "No users selected for deletion.";
        header('Location: manageuser.php');
        exit();
    }

    // validate and cast to ints, skip current super-admin id to avoid self-deletion
    $ids_to_delete = [];
    foreach ($selected as $s) {
        $id = intval($s);
        if ($id > 0 && $id !== $logged_in_user_id) {
            $ids_to_delete[] = $id;
        }
    }

    if (count($ids_to_delete) === 0) {
        $_SESSION['error'] = "No valid users selected (you cannot delete yourself).";
        header('Location: manageuser.php');
        exit();
    }

    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $failed = 0;
    foreach ($ids_to_delete as $did) {
        $delete_stmt->bind_param("i", $did);
        if (!$delete_stmt->execute()) $failed++;
    }
    $delete_stmt->close();

    if ($failed === 0) {
        $_SESSION['success'] = "Selected users deleted successfully.";
    } else {
        $_SESSION['error'] = ($failed === count($ids_to_delete))
            ? "Error deleting selected users."
            : "Deleted " . (count($ids_to_delete) - $failed) . " users, but $failed failed.";
    }

    header('Location: manageuser.php');
    exit();
}

// Handle single user deletion (GET delete_id) - kept as before
if (isset($_GET['delete_id'])) {
    if (!$is_super_admin) {
        $_SESSION['error'] = "You do not have permission to delete users.";
        header('Location: manageuser.php');
        exit();
    }

    $delete_id = intval($_GET['delete_id']);
    // prevent deleting self
    if ($delete_id === $logged_in_user_id) {
        $_SESSION['error'] = "You cannot delete yourself.";
        header('Location: manageuser.php');
        exit();
    }

    $delete_query = "DELETE FROM users WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $delete_id);
    if ($delete_stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting user.";
    }
    header('Location: manageuser.php');
    exit();
}

// Handle role update toggle (same as before)
if (isset($_GET['update_role_id'])) {
    if (!$is_super_admin) {
        $_SESSION['error'] = "You do not have permission to change user roles.";
        header('Location: manageuser.php');
        exit();
    }

    $update_role_id = intval($_GET['update_role_id']);
    $role_query = "SELECT role, name, email FROM users WHERE id = ?";
    $role_stmt = $conn->prepare($role_query);
    $role_stmt->bind_param("i", $update_role_id);
    $role_stmt->execute();
    $role_result = $role_stmt->get_result();
    $user = $role_result->fetch_assoc();

    if ($user) {
        $new_role = ($user['role'] === 'user') ? 'admin' : 'user';
        $user_name = $user['name'];

        echo "<script>
            if (confirm('Are you sure you want to change the role of " . addslashes($user_name) . " to $new_role?')) {
                window.location.href = 'manageuser.php?confirm_role_change=true&id=$update_role_id&new_role=$new_role';
            } else {
                window.location.href = 'manageuser.php';
            }
        </script>";
    } else {
        $_SESSION['error'] = "User not found.";
    }
}

// Confirm and update role
if (isset($_GET['confirm_role_change'])) {
    if (!$is_super_admin) {
        $_SESSION['error'] = "You do not have permission to change user roles.";
        header('Location: manageuser.php');
        exit();
    }

    $update_role_id = intval($_GET['id']);
    $new_role = $_GET['new_role'];
    $update_role_query = "UPDATE users SET role = ? WHERE id = ?";
    $update_role_stmt = $conn->prepare($update_role_query);
    $update_role_stmt->bind_param("si", $new_role, $update_role_id);

    if ($update_role_stmt->execute()) {
        $_SESSION['success'] = "User role updated successfully!";
        
        // -------- Notify all admins --------
        $admins_query = "SELECT name, email FROM users WHERE role = 'admin'";
        $admins_result = $conn->query($admins_query);

        if ($admins_result && $admins_result->num_rows > 0) {
            $subject = "GreenCredit - User Role Changed";
            $message = "
                <html>
                <head><title>User Role Change Notification</title></head>
                <body>
                    <p>Dear Admin,</p>
                    <p>The following user's role has been updated:</p>
                    <ul>
                        <li><strong>Name:</strong> {$user_name}</li>
                        <li><strong>Email:</strong> {$user_email}</li>
                        <li><strong>Previous Role:</strong> {$old_role}</li>
                        <li><strong>New Role:</strong> {$new_role}</li>
                    </ul>
                    <p>This change was performed by: <strong>Admin ID #{$logged_in_user_id}</strong></p>
                    <p>Please log in to the admin panel if you wish to review this change.</p>
                    <p>Best Regards,<br>GreenCredit System</p>
                </body>
                </html>
            ";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: GreenCredit <no-reply@greencredit.com>\r\n";

            while ($admin = $admins_result->fetch_assoc()) {
                mail($admin['email'], $subject, $message, $headers);
            }
        }
        // -------- End Notify all admins --------
        header('Location: manageuser.php');
        exit();
    } else {
        $_SESSION['error'] = "Error updating user role.";
    }
}

// Handle delete all users
if (isset($_POST['delete_all'])) {
    if (!$is_super_admin) {
        $_SESSION['error'] = "You do not have permission to delete all users.";
        header('Location: manageuser.php');
        exit();
    }

    $delete_all_query = "DELETE FROM users";
    if ($conn->query($delete_all_query) === TRUE) {
        $_SESSION['success'] = "All users have been deleted.";
        header("Location: manageuser.php");
        exit();
    } else {
        $_SESSION['error'] = "Error deleting all users.";
    }
}

// Fetch users with sorting
$search = $_GET['search'] ?? '';
$sort_by = $_GET['sort_by'] ?? 'name';
$sort_order = $_GET['sort_order'] ?? 'ASC';

$allowed_sort_columns = [
    'id', 'name', 'email', 'role', 'date_of_birth', 'phone_number',
    'program_of_study', 'department', 'country', 'gender',
    'expected_graduation_year', 'intake', 'created_at', 'points',
    'submission_count'
];
$sort_by = in_array($sort_by, $allowed_sort_columns) ? $sort_by : 'name';
$sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
$sort_labels = [
    'id' => 'User ID',
    'name' => 'Name',
    'email' => 'Email',
    'role' => 'Role',
    'date_of_birth' => 'Date of Birth',
    'phone_number' => 'Phone',
    'program_of_study' => 'Program',
    'department' => 'Department',
    'country' => 'Country',
    'gender' => 'Gender',
    'expected_graduation_year' => 'Graduation Year',
    'intake' => 'Intake',
    'created_at' => 'Registration Date',
    'points' => 'Approved Points',
    'submission_count' => 'Approved Submissions'
];
$active_sort_label = ($sort_labels[$sort_by] ?? ucfirst(str_replace('_', ' ', $sort_by))) . ' (' . ($sort_order === 'ASC' ? 'Ascending' : 'Descending') . ')';
$user_sort_options = [
    ['group' => 'Identity', 'items' => [
        ['id', 'ASC', 'User ID', 'Smallest ID first', 'fa-hashtag'],
        ['id', 'DESC', 'User ID', 'Largest ID first', 'fa-arrow-down-9-1'],
        ['name', 'ASC', 'Name', 'A to Z', 'fa-user'],
        ['name', 'DESC', 'Name', 'Z to A', 'fa-user'],
        ['email', 'ASC', 'Email', 'A to Z', 'fa-envelope'],
        ['phone_number', 'ASC', 'Phone', 'A to Z', 'fa-phone'],
    ]],
    ['group' => 'Academic Profile', 'items' => [
        ['program_of_study', 'ASC', 'Program', 'A to Z', 'fa-graduation-cap'],
        ['department', 'ASC', 'Department', 'A to Z', 'fa-building-columns'],
        ['intake', 'DESC', 'Intake', 'Newest first', 'fa-calendar-plus'],
        ['intake', 'ASC', 'Intake', 'Oldest first', 'fa-calendar'],
        ['expected_graduation_year', 'DESC', 'Graduation Year', 'Newest first', 'fa-calendar-check'],
        ['expected_graduation_year', 'ASC', 'Graduation Year', 'Oldest first', 'fa-calendar-day'],
    ]],
    ['group' => 'Demographics', 'items' => [
        ['country', 'ASC', 'Country', 'A to Z', 'fa-earth-asia'],
        ['gender', 'ASC', 'Gender', 'A to Z', 'fa-venus-mars'],
        ['date_of_birth', 'DESC', 'Date of Birth', 'Newest first', 'fa-cake-candles'],
        ['date_of_birth', 'ASC', 'Date of Birth', 'Oldest first', 'fa-cake-candles'],
    ]],
    ['group' => 'System Activity', 'items' => [
        ['role', 'ASC', 'Role', 'Admins first', 'fa-user-shield'],
        ['role', 'DESC', 'Role', 'Users first', 'fa-users'],
        ['created_at', 'DESC', 'Registered', 'Newest first', 'fa-clock'],
        ['created_at', 'ASC', 'Registered', 'Oldest first', 'fa-clock-rotate-left'],
        ['points', 'DESC', 'Approved Points', 'Highest first', 'fa-star'],
        ['submission_count', 'DESC', 'Approved Submissions', 'Most first', 'fa-list-check'],
    ]],
];

// safe search param for links
$search_query_param = !empty($search) ? '&search=' . urlencode($search) : '';
$per_page_param = $_GET['per_page'] ?? '10';
$allowed_per_page = ['10', '25', '50', '100', 'all'];
$per_page_param = in_array($per_page_param, $allowed_per_page, true) ? $per_page_param : '10';
$per_page_query_param = '&per_page=' . urlencode($per_page_param);

// --- Fetch all users (we will calculate points/submissions using all users, then paginate the final array) ---
if (!empty($search)) {
    $users_query = "SELECT * FROM users WHERE name LIKE ?";
    $stmt = $conn->prepare($users_query);
    $search_param = "%" . $search . "%";
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $users_result = $stmt->get_result();
} else {
    $users_query = "SELECT * FROM users";
    $users_result = $conn->query($users_query);
}

// Build users map
$users_map = [];
$name_to_id = [];
if ($users_result) {
    $users_result->data_seek(0);
    while ($user = $users_result->fetch_assoc()) {
        $users_map[$user['id']] = [
            'name' => $user['name'],
            'points' => 0,
            'submission_count' => 0
        ];
        $name_to_id[$user['name']] = $user['id'];
    }
}

// Fetch approved submissions only for points/submission totals.
$submissions_query = "SELECT id, user_id, points, team_members FROM submissions WHERE status = 'approved'";
$submissions_result = $conn->query($submissions_query);

if ($submissions_result && $submissions_result->num_rows > 0) {
    while ($sub = $submissions_result->fetch_assoc()) {
        $submitter_id = (int)$sub['user_id'];
        $points = (int)$sub['points'];

        // decode and normalize team members to unique list of trimmed strings
        $team_members = json_decode($sub['team_members'], true) ?? [];
        if (!is_array($team_members)) $team_members = [];
        $team_members = array_values(array_unique(array_map('trim', $team_members)));

        // Count the submitter's own submission once
        if (isset($users_map[$submitter_id])) {
            $users_map[$submitter_id]['submission_count']++;
            $users_map[$submitter_id]['points'] += $points;
        }

        // For each team member, add submission_count and points, but skip the submitter to avoid double count
        foreach ($team_members as $member_name) {
            if (!isset($name_to_id[$member_name])) continue;
            $member_id = (int)$name_to_id[$member_name];

            if ($member_id === $submitter_id) {
                // skip — already counted for submitter
                continue;
            }

            if (isset($users_map[$member_id])) {
                $users_map[$member_id]['submission_count']++;
                $users_map[$member_id]['points'] += $points;
            }
        }
    }
}


// Rewind users_result for later iteration
if ($users_result) $users_result->data_seek(0);

// Fetch submissions for display mapping
$all_submissions_query = "SELECT * FROM submissions";
$all_submissions_result = $conn->query($all_submissions_query);
$user_submissions_data = [];

if ($all_submissions_result) {
    while ($sub = $all_submissions_result->fetch_assoc()) {
        $submitter_id = $sub['user_id'];
        $team_members = json_decode($sub['team_members'], true) ?? [];

        if (!isset($user_submissions_data[$submitter_id])) {
            $user_submissions_data[$submitter_id] = [];
        }
        $user_submissions_data[$submitter_id][] = $sub;

        foreach ($team_members as $member_name) {
            if (isset($name_to_id[$member_name])) {
                $member_id = $name_to_id[$member_name];
                if (!isset($user_submissions_data[$member_id])) {
                    $user_submissions_data[$member_id] = [];
                }
                $user_submissions_data[$member_id][] = $sub;
            }
        }
    }
}

// Prepare final user data (full list)
$final_users = [];
if ($users_result) {
    $users_result->data_seek(0);
    while ($user = $users_result->fetch_assoc()) {
        $user_id = $user['id'];
        $user['total_points'] = $users_map[$user_id]['points'] ?? 0;
        $user['submission_count'] = $users_map[$user_id]['submission_count'] ?? 0;
        $final_users[] = $user;
    }
}

// Sort the full array in PHP
usort($final_users, function($a, $b) use ($sort_by, $sort_order) {
    if (in_array($sort_by, ['id', 'points', 'submission_count', 'expected_graduation_year'], true)) {
        $aval = $a['total_points'] ?? 0;
        $bval = $b['total_points'] ?? 0;
        if ($sort_by === 'id') {
            $aval = (int)($a['id'] ?? 0);
            $bval = (int)($b['id'] ?? 0);
        } elseif ($sort_by === 'submission_count') {
            $aval = (int)($a['submission_count'] ?? 0);
            $bval = (int)($b['submission_count'] ?? 0);
        } elseif ($sort_by === 'expected_graduation_year') {
            $aval = (int)($a['expected_graduation_year'] ?? 0);
            $bval = (int)($b['expected_graduation_year'] ?? 0);
        }
    } elseif (in_array($sort_by, ['created_at', 'date_of_birth'], true)) {
        $aval = !empty($a[$sort_by]) ? strtotime($a[$sort_by]) : 0;
        $bval = !empty($b[$sort_by]) ? strtotime($b[$sort_by]) : 0;
    } else {
        $aval = strtolower((string)($a[$sort_by] ?? ''));
        $bval = strtolower((string)($b[$sort_by] ?? ''));
    }

    $result = is_string($aval) || is_string($bval)
        ? strnatcasecmp((string)$aval, (string)$bval)
        : ($aval <=> $bval);

    if ($sort_order === 'ASC') {
        return $result;
    }
    return -$result;
});

function gc_count_by_field(mysqli $conn, string $field): array {
    $allowed = ['program_of_study', 'department', 'gender', 'country', 'intake', 'expected_graduation_year'];
    if (!in_array($field, $allowed, true)) return [];

    $sql = "SELECT COALESCE(NULLIF($field, ''), 'Not specified') AS label, COUNT(*) AS total
            FROM users
            GROUP BY label
            ORDER BY total DESC, label ASC";
    $result = $conn->query($sql);
    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = ['label' => $row['label'], 'total' => (int)$row['total']];
        }
    }
    return $rows;
}

function gc_registration_trends(mysqli $conn): array {
    $result = $conn->query("
        SELECT DATE_FORMAT(created_at, '%Y-%m') AS label, COUNT(*) AS total
        FROM users
        WHERE created_at IS NOT NULL
        GROUP BY label
        ORDER BY label ASC
    ");
    $rows = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = ['label' => $row['label'], 'total' => (int)$row['total']];
        }
    }
    return $rows;
}

$analytics = [
    'programs' => gc_count_by_field($conn, 'program_of_study'),
    'departments' => gc_count_by_field($conn, 'department'),
    'genders' => gc_count_by_field($conn, 'gender'),
    'countries' => gc_count_by_field($conn, 'country'),
    'intakes' => gc_count_by_field($conn, 'intake'),
    'gradYears' => gc_count_by_field($conn, 'expected_graduation_year'),
    'trends' => gc_registration_trends($conn),
];

$analytics_summary = [
    'totalUsers' => (int)($conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0),
    'students' => (int)($conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'")->fetch_assoc()['total'] ?? 0),
    'admins' => (int)($conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'admin'")->fetch_assoc()['total'] ?? 0),
    'new30' => (int)($conn->query("SELECT COUNT(*) AS total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['total'] ?? 0),
];

// ---------------- Pagination logic ----------------
$total_users = count($final_users);
$per_page = $per_page_param === 'all' ? max(1, $total_users) : (int)$per_page_param;
$total_pages = $per_page_param === 'all' ? 1 : ($total_users > 0 ? (int) ceil($total_users / $per_page) : 1);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
if ($current_page > $total_pages) $current_page = $total_pages;
$offset = ($current_page - 1) * $per_page;
$paged_users = $per_page_param === 'all' ? $final_users : array_slice($final_users, $offset, $per_page);

// helper to build links preserving current GET params
function build_page_link($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

$colspan = $is_super_admin ? 16 : 15;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GreenCredit - Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon" />
    <style>
        .profile-pic-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .profile-pic-thumb:hover { transform: scale(1.1); }
        .modal-content { background-color: #f8f9fa; }
        .table-responsive { width: 100%; overflow-x: auto; }
        .table { font-size: 0.9rem; }
        .table th { white-space: nowrap; }
        .dropdown-menu { max-height: 300px; overflow-y: auto; }
        .admin-page-shell {
            background: #fff;
            border: 1px solid rgba(46,139,87,.12);
            border-radius: 16px;
            box-shadow: 0 18px 46px rgba(31,51,41,.10);
            padding: clamp(1rem, 2vw, 1.5rem);
        }
        .analytics-panel {
            display: none;
            border: 1px solid rgba(46,139,87,.14);
            border-radius: 16px;
            background: #f8fbf9;
            padding: clamp(1rem, 2.5vw, 1.5rem);
            margin-bottom: 1.5rem;
        }
        .analytics-panel.show { display: block; animation: fadeSlide .24s ease; }
        .analytics-card {
            background: #fff;
            border: 1px solid rgba(46,139,87,.12);
            border-radius: 14px;
            padding: 1rem;
            height: 100%;
            box-shadow: 0 10px 24px rgba(31,51,41,.08);
        }
        .analytics-number {
            color: #1f7a49;
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 800;
        }
        .chart-box {
            position: relative;
            min-height: 280px;
        }
        .progress-list {
            display: grid;
            gap: .75rem;
        }
        .progress-label {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            font-size: .88rem;
            font-weight: 700;
        }
        .table-toolbar {
            display: flex;
            gap: .75rem;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .sort-panel {
            border: 1px solid rgba(46,139,87,.14);
            background: linear-gradient(180deg, #ffffff 0%, #f7fbf8 100%);
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 10px 24px rgba(16,24,40,.06);
        }
        .sort-panel-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            flex-wrap: wrap;
            margin-bottom: .85rem;
        }
        .sort-chip-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: .65rem;
        }
        .sort-chip {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .75rem .85rem;
            border: 1px solid #dde8df;
            border-radius: 12px;
            color: #263238;
            text-decoration: none;
            background: #fff;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .sort-chip:hover {
            transform: translateY(-2px);
            border-color: #2E8B57;
            box-shadow: 0 12px 24px rgba(46,139,87,.13);
            color: #1f6d43;
        }
        .sort-chip.active {
            border-color: #2E8B57;
            background: #eaf6ef;
            color: #145c35;
            font-weight: 700;
        }
        .sort-chip i {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #edf7f1;
            color: #2E8B57;
            flex: 0 0 auto;
        }
        .sort-chip small {
            display: block;
            color: #6c757d;
            font-weight: 500;
            margin-top: .1rem;
        }
        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: none; }
        }
        @media (max-width: 768px) {
            .admin-page-shell { padding: .85rem; border-radius: 12px; }
            .table-toolbar { align-items: stretch; }
            .table-toolbar > * { width: 100%; }
            .btn-group { width: 100%; }
            .btn-group > .btn { width: 100%; }
            .sort-panel-title { align-items: flex-start; }
            .sort-chip-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container-fluid my-5">
  <div class="admin-page-shell">
    <h2 class="text-center mb-4">Manage Users</h2>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
        <?php unset($_SESSION['success']); endif; ?>

    <div class="table-toolbar">
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-success" id="toggleAnalyticsBtn">
                <i class="fas fa-chart-pie me-1"></i> Show Analytics
            </button>
            <a class="btn btn-outline-success" href="export_csv.php?table=users">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>
        </div>
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sort_by) ?>">
            <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sort_order) ?>">
            <label for="per_page" class="fw-semibold mb-0">Rows</label>
            <select name="per_page" id="per_page" class="form-select" style="width:auto;" onchange="this.form.submit()">
                <?php foreach (['10','25','50','100','all'] as $option): ?>
                    <option value="<?= $option ?>" <?= $per_page_param === $option ? 'selected' : '' ?>><?= $option === 'all' ? 'All rows' : $option . ' rows' ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <section class="analytics-panel" id="analyticsPanel" aria-live="polite">
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3"><div class="analytics-card"><div class="text-muted">Total Users</div><div class="analytics-number"><?= number_format($analytics_summary['totalUsers']) ?></div></div></div>
            <div class="col-6 col-lg-3"><div class="analytics-card"><div class="text-muted">Students</div><div class="analytics-number"><?= number_format($analytics_summary['students']) ?></div></div></div>
            <div class="col-6 col-lg-3"><div class="analytics-card"><div class="text-muted">Admins</div><div class="analytics-number"><?= number_format($analytics_summary['admins']) ?></div></div></div>
            <div class="col-6 col-lg-3"><div class="analytics-card"><div class="text-muted">New in 30 Days</div><div class="analytics-number"><?= number_format($analytics_summary['new30']) ?></div></div></div>
        </div>
        <div class="row g-3">
            <div class="col-lg-6"><div class="analytics-card"><h5>Users by Department</h5><div class="chart-box"><canvas id="departmentChart"></canvas></div></div></div>
            <div class="col-lg-6"><div class="analytics-card"><h5>Users by Program</h5><div class="chart-box"><canvas id="programChart"></canvas></div></div></div>
            <div class="col-lg-4"><div class="analytics-card"><h5>Gender</h5><div class="chart-box"><canvas id="genderChart"></canvas></div></div></div>
            <div class="col-lg-8"><div class="analytics-card"><h5>Registration Trends</h5><div class="chart-box"><canvas id="trendChart"></canvas></div></div></div>
            <div class="col-lg-4"><div class="analytics-card"><h5>Top Countries</h5><div class="progress-list" id="countryProgress"></div></div></div>
            <div class="col-lg-4"><div class="analytics-card"><h5>Top Intakes</h5><div class="progress-list" id="intakeProgress"></div></div></div>
            <div class="col-lg-4"><div class="analytics-card"><h5>Graduation Years</h5><div class="progress-list" id="gradProgress"></div></div></div>
        </div>
    </section>

    <!-- Delete All Users Button (only for super-admin) -->
    <?php if ($is_super_admin): ?>
        <form method="POST" class="text-center mb-4" data-confirm="Are you sure you want to delete ALL users? This action cannot be undone.">
            <button type="submit" name="delete_all" class="btn btn-danger">Delete All Users</button>
        </form>
    <?php endif; ?>

    <!-- Search -->
    <form method="GET" class="mb-4">
        <div class="row justify-content-center mb-2">
            <div class="col-12 col-md-6">
                <input type="text" name="search" class="form-control" placeholder="Search user by name..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            </div>
        </div>
        <div class="row justify-content-center g-2">
            <div class="col-6 col-md-auto">
                <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
            <div class="col-6 col-md-auto">
                <a href="manageuser.php" class="btn btn-secondary w-100">See All Users</a>
            </div>
        </div>
    </form>

    <section class="sort-panel">
        <div class="sort-panel-title">
            <div>
                <h5 class="mb-1"><i class="fas fa-arrow-up-wide-short me-2 text-success"></i>Sort Users</h5>
                <div class="text-muted small">Current view: <?= htmlspecialchars($active_sort_label) ?></div>
            </div>
            <a class="btn btn-sm btn-outline-secondary" href="manageuser.php?per_page=<?= urlencode($per_page_param) ?>">
                <i class="fas fa-rotate-left me-1"></i> Reset
            </a>
        </div>

        <?php foreach ($user_sort_options as $sort_group): ?>
            <div class="mb-3">
                <div class="text-uppercase small fw-bold text-muted mb-2"><?= htmlspecialchars($sort_group['group']) ?></div>
                <div class="sort-chip-grid">
                    <?php foreach ($sort_group['items'] as $option):
                        [$option_by, $option_order, $option_title, $option_hint, $option_icon] = $option;
                        $is_active = ($sort_by === $option_by && $sort_order === $option_order);
                        $url = '?sort_by=' . urlencode($option_by) . '&sort_order=' . urlencode($option_order) . $search_query_param . $per_page_query_param;
                    ?>
                        <a class="sort-chip <?= $is_active ? 'active' : '' ?>" href="<?= $url ?>">
                            <i class="fas <?= htmlspecialchars($option_icon) ?>"></i>
                            <span>
                                <?= htmlspecialchars($option_title) ?>
                                <small><?= htmlspecialchars($option_hint) ?></small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <!-- Bulk delete form starts -->
    <form method="POST" id="bulkDeleteForm">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <?php if ($is_super_admin): ?><th style="width:40px;"><input type="checkbox" id="selectAll" title="Select all on page"></th><?php endif; ?>
                    <th>User ID</th><th>Name</th><th>Email</th><th>Role</th>
                    <th>Date of Birth</th><th>Phone</th><th>Program</th>
                    <th>Department</th><th>Country</th><th>Gender</th>
                    <th>Graduation Year</th><th>Intake</th><th>Points</th>
                    <th>Submissions</th><th>Profile Pic</th>
                    <?php if ($is_super_admin): ?><th>Actions</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($paged_users) === 0): ?>
                    <tr><td colspan="<?= $colspan ?>" class="text-center">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($paged_users as $user):
                        $uid = $user['id'];
                        $profile_pic = $user['profile_pic'] ?: 'default-profile.jpg';
                        $submission_count = $user['submission_count'] ?? 0;
                    ?>
                        <tr>
                            <?php if ($is_super_admin): ?>
                                <td>
                                    <input type="checkbox" class="select-user" name="selected_users[]" value="<?= (int)$uid ?>" <?= ((int)$uid === $logged_in_user_id) ? 'disabled title="You cannot select yourself"' : '' ?>>
                                </td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars($uid) ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= htmlspecialchars($user['date_of_birth']) ?></td>
                            <td><?= htmlspecialchars($user['phone_number']) ?></td>
                            <td><?= htmlspecialchars($user['program_of_study']) ?></td>
                            <td><?= htmlspecialchars($user['department']) ?></td>
                            <td><?= htmlspecialchars($user['country']) ?></td>
                            <td><?= htmlspecialchars($user['gender']) ?></td>
                            <td><?= htmlspecialchars($user['expected_graduation_year']) ?></td>
                            <td><?= htmlspecialchars($user['intake']) ?></td>
                            <td><?= number_format($user['total_points'] ?? 0) ?></td>
                            <td><a href="user_submissions.php?user_id=<?= $uid ?>"><?= $submission_count ?></a></td>
                            <td>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#profileImageModal-<?= $uid ?>">
                                    <img src="../uploads/<?= htmlspecialchars($profile_pic) ?>" alt="Profile Picture" class="profile-pic-thumb" />
                                </a>
                                <div class="modal fade" id="profileImageModal-<?= $uid ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Profile Image</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="../uploads/<?= htmlspecialchars($profile_pic) ?>" class="img-fluid rounded" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <?php if ($is_super_admin): ?>
                            <td>
                                <a href="edituser.php?id=<?= $uid ?>" class="btn btn-warning btn-sm text-dark">Edit</a>
                                <a href="manageuser.php?delete_id=<?= $uid ?>" class="btn btn-danger btn-sm text-dark" data-confirm="Are you sure you want to delete this user? This action cannot be undone.">Delete</a>
                                <a href="manageuser.php?update_role_id=<?= $uid ?>" class="btn btn-info btn-sm text-dark">
                                    <?= $user['role'] === 'user' ? 'Make Admin' : 'Make User'; ?>
                                </a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($is_super_admin): ?>
        <div class="d-flex gap-2 mt-3 mb-4">
            <button type="button" id="deleteSelectedBtn" class="btn btn-danger">Delete Selected</button>
            <small class="text-muted align-self-center">You cannot delete yourself. Selected items will be deleted permanently.</small>
        </div>
        <input type="hidden" name="bulk_delete" value="1">
    <?php endif; ?>
    </form>
    <!-- Bulk delete form ends -->

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="User pagination">
            <ul class="pagination justify-content-center">
                <!-- Previous -->
                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $current_page <= 1 ? '#' : build_page_link($current_page - 1) ?>" aria-label="Previous">Prev</a>
                </li>

                <?php
                // show a sliding window of pages around current page
                $start = max(1, $current_page - 2);
                $end = min($total_pages, $current_page + 2);
                if ($start > 1) {
                    echo '<li class="page-item"><a class="page-link" href="' . build_page_link(1) . '">1</a></li>';
                    if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                }
                for ($p = $start; $p <= $end; $p++):
                ?>
                    <li class="page-item <?= ($p == $current_page) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= build_page_link($p) ?>"><?= $p ?></a>
                    </li>
                <?php endfor;
                if ($end < $total_pages) {
                    if ($end < $total_pages - 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
                    echo '<li class="page-item"><a class="page-link" href="' . build_page_link($total_pages) . '">' . $total_pages . '</a></li>';
                }
                ?>

                <!-- Next -->
                <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= $current_page >= $total_pages ? '#' : build_page_link($current_page + 1) ?>" aria-label="Next">Next</a>
                </li>
            </ul>
            <p class="text-center small">Showing <?= ($total_users == 0) ? 0 : ($offset + 1) ?> to <?= min($offset + $per_page, $total_users) ?> of <?= $total_users ?> users</p>
        </nav>
    <?php endif; ?>

  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
    const analyticsData = <?= json_encode($analytics, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    let analyticsChartsRendered = false;

    function chartData(rows, max = 8) {
        const selected = rows.slice(0, max);
        return {
            labels: selected.map(row => row.label),
            values: selected.map(row => row.total)
        };
    }

    function renderBarChart(id, rows, color) {
        const data = chartData(rows, 8);
        new Chart(document.getElementById(id), {
            type: 'bar',
            data: { labels: data.labels, datasets: [{ data: data.values, backgroundColor: color, borderRadius: 8 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    }

    function renderProgressList(id, rows) {
        const el = document.getElementById(id);
        const top = rows.slice(0, 7);
        const max = Math.max(...top.map(row => row.total), 1);
        el.innerHTML = top.map(row => {
            const pct = Math.round((row.total / max) * 100);
            return `<div><div class="progress-label"><span>${row.label}</span><span>${row.total}</span></div><div class="progress" style="height:9px;"><div class="progress-bar bg-success" style="width:${pct}%"></div></div></div>`;
        }).join('');
    }

    function renderAnalyticsCharts() {
        if (analyticsChartsRendered) return;
        renderBarChart('departmentChart', analyticsData.departments, '#2E8B57');
        renderBarChart('programChart', analyticsData.programs, '#3d8bfd');
        const gender = chartData(analyticsData.genders, 6);
        new Chart(document.getElementById('genderChart'), {
            type: 'doughnut',
            data: { labels: gender.labels, datasets: [{ data: gender.values, backgroundColor: ['#2E8B57', '#3d8bfd', '#ffc107', '#6f42c1'] }] },
            options: { responsive: true, maintainAspectRatio: false }
        });
        const trend = chartData(analyticsData.trends, 18);
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: { labels: trend.labels, datasets: [{ data: trend.values, borderColor: '#2E8B57', backgroundColor: 'rgba(46,139,87,.12)', fill: true, tension: .35 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
        renderProgressList('countryProgress', analyticsData.countries);
        renderProgressList('intakeProgress', analyticsData.intakes);
        renderProgressList('gradProgress', analyticsData.gradYears);
        analyticsChartsRendered = true;
    }

    document.getElementById('toggleAnalyticsBtn')?.addEventListener('click', function() {
        const panel = document.getElementById('analyticsPanel');
        panel.classList.toggle('show');
        this.innerHTML = panel.classList.contains('show')
            ? '<i class="fas fa-chart-pie me-1"></i> Hide Analytics'
            : '<i class="fas fa-chart-pie me-1"></i> Show Analytics';
        if (panel.classList.contains('show')) renderAnalyticsCharts();
    });

    // Select All checkbox behavior (only present if super admin)
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checked = this.checked;
            document.querySelectorAll('.select-user').forEach(cb => {
                if (!cb.disabled) cb.checked = checked;
            });
        });

        // If any individual checkbox is unchecked -> uncheck Select All
        document.querySelectorAll('.select-user').forEach(cb => {
            cb.addEventListener('change', function() {
                if (!this.checked) {
                    selectAll.checked = false;
                } else {
                    const enabled = Array.from(document.querySelectorAll('.select-user')).filter(c => !c.disabled);
                    const allChecked = enabled.every(c => c.checked);
                    selectAll.checked = allChecked;
                }
            });
        });

        // Delete selected button action
        document.getElementById('deleteSelectedBtn').addEventListener('click', function() {
            const checkedBoxes = Array.from(document.querySelectorAll('.select-user:checked'));
            if (checkedBoxes.length === 0) {
                alert('Please select at least one user to delete.');
                return;
            }

            window.showConfirmModal(
                'Are you sure you want to delete the selected ' + checkedBoxes.length + ' user(s)? This action cannot be undone.',
                () => document.getElementById('bulkDeleteForm').submit()
            );
        });
    }
</script>
</body>
</html>
