<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$role_stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
$role_stmt->bind_param("i", $_SESSION['user_id']);
$role_stmt->execute();
$role = $role_stmt->get_result()->fetch_assoc()['role'] ?? '';
$role_stmt->close();

if ($role !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

function gc_backup_identifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function gc_backup_tables(mysqli $conn): array
{
    $tables = [];
    $result = $conn->query('SHOW TABLES');
    if ($result) {
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $tables[] = $row[0];
        }
    }
    return $tables;
}

function gc_download_sql_backup(mysqli $conn): void
{
    $timestamp = date('Ymd_His');
    $filename = "greencredit_full_backup_{$timestamp}.sql";

    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "-- GreenCredit full database backup\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach (gc_backup_tables($conn) as $table) {
        $quoted_table = gc_backup_identifier($table);
        $create_result = $conn->query("SHOW CREATE TABLE {$quoted_table}");
        if (!$create_result) {
            continue;
        }

        $create_row = $create_result->fetch_assoc();
        $create_sql = $create_row['Create Table'] ?? '';
        if ($create_sql === '') {
            continue;
        }

        echo "-- Table structure for {$table}\n";
        echo "DROP TABLE IF EXISTS {$quoted_table};\n";
        echo $create_sql . ";\n\n";

        $data_result = $conn->query("SELECT * FROM {$quoted_table}");
        if (!$data_result || $data_result->num_rows === 0) {
            echo "\n";
            continue;
        }

        $fields = $data_result->fetch_fields();
        $columns = array_map(fn($field) => gc_backup_identifier($field->name), $fields);
        echo "-- Data for {$table}\n";

        while ($row = $data_result->fetch_assoc()) {
            $values = [];
            foreach ($fields as $field) {
                $value = $row[$field->name];
                $values[] = $value === null ? 'NULL' : "'" . $conn->real_escape_string((string) $value) . "'";
            }
            echo "INSERT INTO {$quoted_table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
        }
        echo "\n";
    }

    echo "SET FOREIGN_KEY_CHECKS=1;\n";
    exit();
}

function gc_download_csv(mysqli $conn, string $table): void
{
    $tables = gc_backup_tables($conn);
    if (!in_array($table, $tables, true)) {
        http_response_code(400);
        exit('Invalid export table');
    }

    $timestamp = date('Ymd_His');
    $filename = 'greencredit_' . preg_replace('/[^a-zA-Z0-9_]/', '', $table) . "_{$timestamp}.csv";
    $quoted_table = gc_backup_identifier($table);
    $result = $conn->query("SELECT * FROM {$quoted_table}");
    if (!$result) {
        http_response_code(500);
        exit('Export query failed');
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    $fields = $result->fetch_fields();
    fputcsv($out, array_map(fn($field) => $field->name, $fields));
    while ($row = $result->fetch_assoc()) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit();
}

$action = $_GET['action'] ?? '';
if ($action === 'sql') {
    gc_download_sql_backup($conn);
}

$available_tables = gc_backup_tables($conn);
$major_tables = [
    'users' => 'Users CSV',
    'submissions' => 'Submissions CSV',
    'news_events' => 'News CSV',
    'news_images' => 'News Images CSV',
    'contact_messages' => 'Messages CSV',
    'super_admins' => 'Super Admins CSV',
];
$export_tables = array_intersect_key($major_tables, array_flip($available_tables));

if ($action === 'csv') {
    $table = $_GET['table'] ?? '';
    if (!isset($export_tables[$table])) {
        http_response_code(400);
        exit('Invalid export table');
    }
    gc_download_csv($conn, $table);
}
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <div class="admin-page-shell">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="mb-1"><i class="fas fa-database me-2 text-success"></i>Admin Backup</h2>
                <p class="text-muted mb-0">Download a full SQL backup or export major project tables as CSV files.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        Full Database Backup
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Includes all database tables, table structure, and table data in a timestamped SQL file.
                        </p>
                        <a class="btn btn-success" href="backup.php?action=sql">
                            <i class="fas fa-download me-2"></i>Download Full SQL Backup
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        CSV Exports
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Export major project tables as timestamped CSV files.
                        </p>
                        <div class="d-grid gap-2">
                            <?php foreach ($export_tables as $table => $label): ?>
                                <a class="btn btn-outline-success" href="backup.php?action=csv&amp;table=<?= urlencode($table) ?>">
                                    <i class="fas fa-file-csv me-2"></i>Export <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if (empty($export_tables)): ?>
                            <div class="alert alert-warning mb-0">No exportable project tables were found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
(function backupNavbarDropdowns() {
    function closeMenus(exceptMenu) {
        document.querySelectorAll('.navbar-admin .dropdown-menu.show').forEach(function(menu) {
            if (menu !== exceptMenu) {
                menu.classList.remove('show');
                const toggle = menu.closest('.dropdown')?.querySelector('[data-bs-toggle="dropdown"]');
                if (toggle) {
                    toggle.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            }
        });
    }

    function wireDropdown(toggleId) {
        const toggle = document.getElementById(toggleId);
        if (!toggle || toggle.dataset.backupDropdownReady === '1') return;

        const menu = toggle.closest('.dropdown')?.querySelector('.dropdown-menu');
        if (!menu) return;

        toggle.dataset.backupDropdownReady = '1';
        toggle.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            const open = !menu.classList.contains('show');
            closeMenus(menu);
            menu.classList.toggle('show', open);
            toggle.classList.toggle('show', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        }, true);
    }

    document.addEventListener('DOMContentLoaded', function() {
        wireDropdown('adminDropdown');
        wireDropdown('adminProfileDropdown');

        document.addEventListener('click', function(event) {
            if (!event.target.closest('.navbar-admin .dropdown')) {
                closeMenus(null);
            }
        }, true);

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMenus(null);
            }
        });
    });
})();
</script>
