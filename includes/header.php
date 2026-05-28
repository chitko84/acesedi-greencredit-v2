<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
    .navbar-custom {
        background-color: #ffffff !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 10px 0;
    }
    
    .navbar-brand {
        font-weight: 700;
        color: #2E8B57 !important;
        font-size: 1.4rem;
    }
    
    .navbar-brand:hover {
        color: #196b2b !important;
    }
    
    .nav-link {
        color: #555555 !important;
        font-weight: 500;
        transition: all 0.3s ease;
        padding: 8px 12px !important;
        border-radius: 4px;
        margin: 0 2px;
    }
    
    .nav-link:hover {
        color: #2E8B57 !important;
        background-color: #f8f9fa;
    }
    
    .nav-link i {
        color: #2E8B57;
        width: 20px;
        text-align: center;
    }
    
    .navbar-toggler {
        border: 1px solid #e9ecef;
        padding: 5px 10px;
    }
    
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(46, 139, 87, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    .theme-switch-public {
        display: flex;
        align-items: center;
        gap: .45rem;
        padding: .5rem .75rem;
        border: 1px solid rgba(46, 139, 87, .16);
        border-radius: 999px;
        color: #2E8B57;
        background: #f6fbf8;
    }

    .theme-switch-public .form-check-input {
        cursor: pointer;
        margin-top: 0;
    }

    body.dark-mode .navbar-custom {
        background-color: #111814 !important;
        box-shadow: 0 2px 14px rgba(0, 0, 0, .4);
    }

    body.dark-mode .navbar-brand,
    body.dark-mode .nav-link {
        color: #edf6ef !important;
    }

    body.dark-mode .nav-link:hover {
        color: #9be7b3 !important;
        background-color: rgba(120, 217, 154, .12);
    }

    body.dark-mode .theme-switch-public {
        background: #1d2620;
        border-color: rgba(190, 233, 205, .16);
        color: #d9f7e2;
    }
    
    .partner-logos {
        padding-right: 15px;
        border-right: 1px solid #e9ecef;
        margin-right: 15px;
    }
    
    @media (max-width: 991px) {
        .partner-logos {
            border-right: none;
            padding-right: 0;
            margin-right: 0;
            margin-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 10px;
            width: 100%;
            justify-content: center;
        }
        
        .navbar-nav {
            padding-top: 15px;
        }
        
        .nav-link {
            margin: 2px 0;
        }
    }
    </style>
    <script>
        (function() {
            const storedTheme = localStorage.getItem("theme") || (localStorage.getItem("darkMode") === "enabled" ? "dark" : "light");
            if (storedTheme === "dark") {
                document.documentElement.classList.add("dark-mode");
                document.addEventListener("DOMContentLoaded", function() {
                    document.body.classList.add("dark-mode");
                    const themeToggle = document.getElementById("themeToggle");
                    if (themeToggle) themeToggle.checked = true;
                });
            }
        })();
    </script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container-fluid px-3">
            <!-- Brand Logo + Partner Logos -->
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <!-- Partner Logos -->
                <div class="d-flex align-items-center gap-2 partner-logos">
                    <!--<img src="assets/images/3 zero club (1).png" alt="Partner 1" height="31">-->
                    <img src="assets/images/aiu_logo.png" alt="Partner 2" height="31">
                    <!--<img src="assets/images/ace sedi logo.png" alt="Partner 3" height="31">-->
                </div>
                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    <img src="assets/images/gc_logo_1.png" alt="GreenCredit Logo" height="32">
                    <span class="ms-2">GreenCredit</span>
                </a>
            </div>
    
            <!-- Toggle Button (Mobile) -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
    
            <!-- Navbar Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="guidelines.php">
                            <i class="fas fa-book me-1"></i>Points System
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="news_and_events.php">
                            <i class="fa fa-newspaper me-1"></i>News & Events
                        </a>
                    </li>
                    <li class="nav-item d-flex align-items-center px-lg-2">
                        <div class="theme-switch-public">
                            <i class="fas fa-moon"></i>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="themeToggle" aria-label="Toggle dark mode">
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
