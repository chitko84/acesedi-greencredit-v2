<?php
session_start();
include '../includes/db.php';
include_once __DIR__ . '/../includes/super_admin.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if (!gc_is_super_admin($conn, (int) $_SESSION['user_id'])) {
    $_SESSION['error'] = "You do not have permission to manage super admins.";
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetUserId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
    $action = $_POST['action'] ?? '';

    $stmt = $conn->prepare("SELECT id, role FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $targetUserId);
    $stmt->execute();
    $targetUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$targetUser || $targetUser['role'] !== 'admin') {
        $_SESSION['error'] = "Only existing admin accounts can be made super admins.";
    } elseif ($action === 'promote') {
        $_SESSION['success'] = gc_add_super_admin($conn, $targetUserId)
            ? "Admin promoted to super admin."
            : "Could not promote admin.";
    } elseif ($action === 'revoke') {
        $_SESSION['success'] = gc_remove_super_admin($conn, $targetUserId)
            ? "Super admin access revoked."
            : "This protected super admin cannot be revoked.";
    }

    header('Location: manage_superadmins.php');
    exit();
}

$admins = [];
$result = $conn->query("SELECT id, name, email, role FROM users WHERE role = 'admin' ORDER BY name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['is_super_admin'] = gc_is_super_admin($conn, (int) $row['id']);
        $row['is_protected'] = gc_is_protected_super_admin($conn, (int) $row['id']);
        $admins[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Super Admins - GreenCredit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="mb-3">Manage Super Admins</h2>
            <p class="text-muted">Grant or revoke super-admin access for existing admin accounts.</p>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admins)): ?>
                            <tr><td colspan="4" class="text-center">No admin accounts found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?= htmlspecialchars($admin['name']); ?></td>
                                    <td><?= htmlspecialchars($admin['email']); ?></td>
                                    <td>
                                        <?php if ($admin['is_super_admin']): ?>
                                            <span class="badge bg-danger">Super Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Admin</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($admin['is_super_admin']): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?= (int) $admin['id']; ?>">
                                                <input type="hidden" name="action" value="revoke">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" <?= $admin['is_protected'] ? 'disabled' : ''; ?>>
                                                    Revoke Super Admin
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?= (int) $admin['id']; ?>">
                                                <input type="hidden" name="action" value="promote">
                                                <button type="submit" class="btn btn-success btn-sm">
                                                    Make Super Admin
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
