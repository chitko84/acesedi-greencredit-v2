<?php
$pending_submission_count = 0;
if (!isset($conn)) {
    $admin_header_db = __DIR__ . '/../../includes/db.php';
    if (file_exists($admin_header_db)) {
        include $admin_header_db;
    }
}
if (isset($conn) && $conn instanceof mysqli) {
    $pending_result = $conn->query("SELECT COUNT(*) AS total FROM submissions WHERE status = 'pending'");
    if ($pending_result) {
        $pending_submission_count = (int)($pending_result->fetch_assoc()['total'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .navbar-admin {
            background-color: #ffffff !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 10px 0;
        }

        .navbar-admin .navbar-brand {
            font-weight: 700;
            color: #2E8B57 !important;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
        }

        .navbar-admin .navbar-brand img {
            height: 36px;
            margin-right: 10px;
        }

        .navbar-admin .navbar-brand:hover {
            color: #196b2b !important;
        }

        .navbar-admin .nav-link {
            color: #555 !important;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 12px !important;
            border-radius: 4px;
            margin: 0 2px;
        }

        .navbar-admin .nav-link:hover {
            color: #2E8B57 !important;
            background-color: #f8f9fa;
        }

        .navbar-admin .dropdown-menu {
            border: 1px solid rgba(46, 139, 87, 0.2);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .navbar-admin .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #2E8B57;
        }
        .nav-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.35rem;
            height: 1.35rem;
            padding: 0 .38rem;
            border-radius: 999px;
            background: #dc3545;
            color: #fff;
            font-size: .72rem;
            font-weight: 800;
            margin-left: .35rem;
            box-shadow: 0 6px 14px rgba(220,53,69,.25);
        }

        .navbar-admin .navbar-toggler {
            border: 1px solid #e9ecef;
            padding: 5px 10px;
        }

        .navbar-admin .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(46, 139, 87, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Dark Mode Styling */
        body.dark-mode .navbar-admin {
            background-color: #1a1a1a !important;
            box-shadow: 0 2px 10px rgba(255,255,255,0.05);
        }

        body.dark-mode .navbar-admin .navbar-brand {
            color: #ffffff !important;
        }

        body.dark-mode .navbar-admin .navbar-brand:hover {
            color: #a8ffb3 !important;
        }

        body.dark-mode .navbar-admin .nav-link {
            color: #ddd !important;
        }

        body.dark-mode .navbar-admin .nav-link:hover {
            background-color: rgba(255,255,255,0.05);
            color: #a8ffb3 !important;
        }

        body.dark-mode .navbar-admin .dropdown-menu {
            background-color: #2a2a2a;
            border: 1px solid rgba(255,255,255,0.1);
        }

        body.dark-mode .navbar-admin .dropdown-item {
            color: #ddd;
        }

        body.dark-mode .navbar-admin .dropdown-item:hover {
            background-color: rgba(255,255,255,0.1);
            color: #a8ffb3;
        }
        /* Dropdown styling (Light Mode) */
    .dropdown-menu {
        background-color: #ffffff !important;
        border: 1px solid rgba(46, 139, 87, 0.2);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .dropdown-item {
        color: #000000 !important; /* Text stays black */
        font-weight: 500;
    }
    
    .dropdown-item i {
        color: #2E8B57; /* Green icons */
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #2E8B57 !important; /* Green on hover */
    }
    /* Dropdown styling (Dark Mode) */
    body.dark-mode .dropdown-menu {
        background-color: #2a2a2a;
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    body.dark-mode .dropdown-item {
        color: #dddddd !important;
    }
    
    body.dark-mode .dropdown-item:hover {
        background-color: rgba(255,255,255,0.1);
        color: #a8ffb3 !important; /* Lighter green on hover */
    }
    </style>
    <script>
        (function () {
            const storedTheme = localStorage.getItem('theme') || (localStorage.getItem('darkMode') === 'disabled' ? 'light' : 'dark');
            if (storedTheme === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
</head>
<body>

<!-- Navbar for Admin -->
<nav class="navbar navbar-expand-lg navbar-light navbar-admin">
    <div class="container">
        <!-- Partner Logos + GreenCredit Logo (All in One Row Below) -->
        <div class="d-flex flex-column align-items-center">
            <!-- Partner Logos Row -->
            <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap mb-2 partner-logos">
                <!--<img src="../assets/images/3 zero club (1).png" alt="Partner 1" height="31">-->
                <img src="../assets/images/aiu_logo.png" alt="Partner 2" height="31">
                <!--<img src="../assets/images/ace sedi logo.png" alt="Partner 3" height="31">-->
            </div>
        
            <!-- GreenCredit Logo Row -->
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <img src="../assets/images/gc_logo_1.png" alt="GreenCredit Logo" height="32">
                <span class="ms-2 fw-bold">GreenCredit</span>
            </a>
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="submissions.php">Submissions<?php if ($pending_submission_count > 0): ?><span class="nav-count-badge"><?= number_format($pending_submission_count) ?></span><?php endif; ?></a></li>
                <li class="nav-item"><a class="nav-link" href="leaderboard.php">Leaderboard</a></li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Admin Tasks
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="admin_news.php">Update News</a></li>
                        <li><a class="dropdown-item" href="add_admin.php">Add Admin</a></li>
                        <?php
                            include_once __DIR__ . '/../../includes/super_admin.php';
                            if (isset($_SESSION['user_id']) && gc_is_super_admin($conn, (int) $_SESSION['user_id'])):
                        ?>
                            <li><a class="dropdown-item" href="manage_superadmins.php">Manage Super Admins</a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="manageuser.php">Manage Users</a></li>
                        <li><a class="dropdown-item" href="message.php">Message User</a></li>
                        <li><a class="dropdown-item" href="backup.php">Backup</a></li>
                    </ul>
                </li>

                <?php
                    include_once __DIR__ . '/../../includes/profile_image.php';
                    $profilePicSrc = gc_profile_image_src($_SESSION['profile_pic'] ?? '');
                ?>
                <!-- Profile Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminProfileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo htmlspecialchars($profilePicSrc); ?>" 
                             alt="Profile Picture" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminProfileDropdown">
                        <li class="dropdown-item">
                            <div class="form-check form-switch text-black">
                                <input class="form-check-input" type="checkbox" id="themeToggle">
                                <label class="form-check-label" for="themeToggle" style="font-size: 0.9rem;">Dark Mode</label>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="admin_profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- JS for Bootstrap & Theme Toggle -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('click', function(event) {
        const navbarCollapse = document.querySelector('#navbarNav');
        const isClickInsideNavbar = event.target.closest('.navbar-collapse, .navbar-toggler');
        if (navbarCollapse.classList.contains('show') && !isClickInsideNavbar) {
            const collapseInstance = bootstrap.Collapse.getInstance(navbarCollapse);
            if (collapseInstance) collapseInstance.hide();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const body = document.body;
        const toggleDesktop = document.getElementById('themeToggle');
        const storedTheme = localStorage.getItem('theme') || (localStorage.getItem('darkMode') === 'disabled' ? 'light' : 'dark');
        if (storedTheme === 'dark') {
            body.classList.add('dark-mode');
            document.documentElement.classList.add('dark-mode');
            if (toggleDesktop) toggleDesktop.checked = true;
        } else {
            body.classList.remove('dark-mode');
            document.documentElement.classList.remove('dark-mode');
            if (toggleDesktop) toggleDesktop.checked = false;
        }
        function toggleTheme(checked) {
            if (checked) {
                body.classList.add('dark-mode');
                document.documentElement.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                body.classList.remove('dark-mode');
                document.documentElement.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
                localStorage.setItem('darkMode', 'disabled');
            }
        }
        if (toggleDesktop) {
            toggleDesktop.addEventListener('change', function () {
                toggleTheme(this.checked);
            });
        }
    });
</script>
