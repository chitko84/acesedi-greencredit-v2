<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>GreenCredit - Points</title>
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
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
        margin-right: 5px;
    }
    
    .navbar-toggler {
        border: 1px solid #e9ecef;
        padding: 5px 10px;
    }
    
    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(46, 139, 87, 1)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    
    .partner-logos {
        padding-right: 15px;
        border-right: 1px solid #e9ecef;
        margin-right: 15px;
    }
    
    .dropdown-menu {
        border: 1px solid rgba(46, 139, 87, 0.2);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #2E8B57;
    }
    
    .dropdown-item i {
        color: #2E8B57;
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
        
        .dropdown-menu {
            border: none;
            box-shadow: none;
        }
    }
    body.dark-mode .navbar-custom {
    background-color: #1a1a1a !important;
    box-shadow: 0 2px 10px rgba(255,255,255,0.05);
    }
    
    body.dark-mode .navbar-brand {
        color: #ffffff !important;
    }
    
    body.dark-mode .navbar-brand:hover {
        color: #a8ffb3 !important;
    }
    
    body.dark-mode .nav-link {
        color: #dddddd !important;
    }
    
    body.dark-mode .nav-link:hover {
        background-color: rgba(255,255,255,0.05);
        color: #a8ffb3 !important;
    }
    
    body.dark-mode .nav-link i {
        color: #a8ffb3;
    }
    
    body.dark-mode .dropdown-menu {
        background-color: #2a2a2a;
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    body.dark-mode .dropdown-item {
        color: #ddd;
    }
    
    body.dark-mode .dropdown-item:hover {
        background-color: rgba(255,255,255,0.1);
        color: #a8ffb3;
    }
    </style>
</head>
<body>

<!-- Navbar for User -->
<nav class="navbar navbar-expand-lg navbar-light navbar-custom">
    <div class="container-fluid px-3">
        <!-- Brand Logo + Partner Logos -->
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <!-- Partner Logos -->
            <div class="d-flex align-items-center gap-2 partner-logos">
                <!--<img src="../assets/images/3 zero club (1).png" alt="Partner 1" height="31">-->
                <img src="../assets/images/aiu_logo.png" alt="Partner 2" height="31">
                <!--<img src="../assets/images/ace sedi logo.png" alt="Partner 3" height="31">-->
            </div>
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
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="submit_item.php">
                        <i class="fas fa-recycle"></i>
                        <span>Submit Item</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="leaderboard.php">
                        <i class="fas fa-trophy"></i>
                        <span>Leaderboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="history.php">
                        <i class="fas fa-history"></i>
                        <span>History</span>
                    </a>
                </li>
                  <!-- Grouped Dropdown for Sustainability & Responses -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="toolsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-gear"></i>
                        <span>Others</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="toolsDropdown">
                        <li>
                            <a class="dropdown-item" href="response.php">
                                <i class="fas fa-comments me-2"></i> Admin Responses
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="sustainability_calculator.php">
                                <i class="fas fa-calculator me-2"></i> Sustainability Calculator
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            
            <?php
                include_once __DIR__ . '/../../includes/profile_image.php';
                $profilePicSrc = gc_profile_image_src($_SESSION['profile_pic'] ?? '');
            ?>
            <ul class="navbar-nav ms-3">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo htmlspecialchars($profilePicSrc); ?>" 
                        alt="Profile Picture" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li class="dropdown-item">
                            <div class="form-check form-switch text-black">
                            <input class="form-check-input" type="checkbox" id="themeToggle">
                            <label class="form-check-label" for="themeToggle" style="font-size: 0.9rem;">Dark Mode</label>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php" id="logoutLink"><i class="fas fa-sign-out-alt me-2"></i> Log Out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
