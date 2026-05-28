<?php include 'includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Sustainability Actions</title>
    <link rel="icon" href="assets/images/gc_logo.jpg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            background: #f6faf7;
            color: var(--text-gray);
            line-height: 1.6;
        }

        .hero-section {
            background: linear-gradient(0deg, rgba(0,0,0,0.18), rgba(0,0,0,0.18)), 
                        url('uploads/guidelines_bg_image.jpg') center/cover no-repeat !important;
            color: var(--white);
            padding: 4rem 2rem;
            margin-bottom: 2rem;
            border-radius: 0 0 24px 24px;
            box-shadow: 0 18px 44px rgba(31, 51, 41, 0.16);
            text-align: center;
            position: relative;
            width: 100%;
            height: 400px;
        }

        @media (prefers-color-scheme: dark) {
            .hero-section {
                background: linear-gradient(0deg, rgba(0,0,0,0.35), rgba(0,0,0,0.35)), 
                            url('uploads/guidelines_bg_image.jpg') center/cover no-repeat !important;
                background-color: transparent !important;
            }
        }
        
        [data-bs-theme="dark"] .hero-section {
            background: linear-gradient(0deg, rgba(0,0,0,0.35), rgba(0,0,0,0.35)), 
                        url('uploads/guidelines_bg_image.jpg') center/cover no-repeat !important;
            background-color: transparent !important;
        }

        .hero-text, .hero-text-box {
            background-color: rgba(0, 0, 0, 0.48);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: inline-block;
            max-width: 80%;
            margin: 0 auto;
            border: 2px solid var(--white);
        }

        .hero-title {
            font-weight: 700;
            font-size: 3rem;
            margin-bottom: 1rem;
            letter-spacing: 2px;
        }

        .hero-subtitle {
            font-weight: 300;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        .btn-cta {
            background-color: var(--primary-green);
            color: var(--white);
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            border-radius: 30px;
            transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
        }

        .btn-cta:hover {
            background-color: var(--dark-green);
            transform: translateY(-2px);
            box-shadow: 0 12px 22px rgba(0,0,0,.16);
        }

        .category-card {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 14px 36px rgba(31, 51, 41, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }

        .category-header {
            background: #1f7a49;
            color: var(--white);
            padding: 1.5rem;
            position: relative;
        }

        .category-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 10px;
            background: #8fcca0;
            z-index: 1;
        }

        .category-title {
            font-weight: 600;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .category-badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-right: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .category-body {
            padding: 1.5rem;
        }

        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: var(--primary-green);
            color: var(--white);
            font-weight: 500;
            border: none;
            padding: 1rem;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:nth-child(even) {
            background-color: var(--light-green);
        }

        .table tbody tr:hover {
            background-color: rgba(129, 199, 132, 0.3);
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e0e0e0;
        }

        .action-type {
            font-weight: 600;
            color: var(--dark-green);
        }

        .points-display {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            background-color: var(--white);
            color: var(--primary-green);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .info-icon {
            color: var(--primary-green);
            margin-right: 0.5rem;
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 1rem;
                min-height: 360px;
                height: auto;
                display: flex;
                align-items: center;
            }
            
            .hero-text-box {
                padding: 1rem;
                max-width: 95%;
            }
            
            .hero-title {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
            
            .hero-subtitle {
                font-size: 0.9rem;
                margin-bottom: 1rem;
            }
            
            .btn-cta {
                font-size: 0.9rem;
                padding: 0.5rem 1.25rem;
            }
            
            .floating-action-btn {
                bottom: 2rem;
                right: 2rem;
                width: 60px;
                height: 60px;
            }
            
            .table thead {
                display: none;
            }
            
            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }
            
            .table tr {
                margin-bottom: 1.5rem;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }
            
            .table td {
                text-align: left;
                padding: .85rem .9rem .85rem 45%;
                position: relative;
                border-bottom: 1px solid #e0e0e0;
            }
            
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 1rem;
                width: 40%;
                padding-right: 1rem;
                font-weight: 600;
                color: var(--dark-green);
                text-align: left;
            }
            
            .action-type {
                background-color: var(--primary-green);
                color: var(--white);
                text-align: left !important;
                padding-left: 1rem !important;
            }
            
            .action-type::before {
                display: none;
            }
        }

        .floating-action-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background-color: var(--primary-green);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .floating-action-btn:hover {
            transform: scale(1.1);
            background-color: var(--dark-green);
        }

        .floating-action-btn i {
            font-size: 1.5rem;
        }

        @media (max-width: 768px) {
            .table td {
                text-align: left;
                padding-left: 45%;
                position: relative;
                border-bottom: 1px solid #e0e0e0;
            }
            
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 1rem;
                width: 45%;
                padding-right: 1rem;
                font-weight: 600;
                color: var(--dark-green);
                text-align: left;
            }
            
            .table td[data-label="Details"]::before {
                content: "Details";
            }
            
            .table td[data-label="Verification"]::before {
                content: "Verification";
            }
            
            .table td[data-label="Action Type"]::before {
                content: "Action Type";
            }
            
            .category-header {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .category-title {
                margin-bottom: 0.5rem;
            }
            
            .points-display {
                position: static;
                margin-top: 0.5rem;
            }
        }
        @media (max-width: 576px) {
            .container.mb-5 { padding-left: .75rem; padding-right: .75rem; }
            .hero-text-box { max-width: 100%; }
            .category-body { padding: 1rem; }
            .category-title { font-size: 1.18rem; }
            .table td { padding-left: .9rem; }
            .table td::before {
                position: static;
                display: block;
                width: auto;
                margin-bottom: .25rem;
            }
        }
        
    </style>
</head>
<body>
    <div class="hero-section">
        <div class="container text-center">
            <div class="hero-text-box">
                <h1 class="hero-title">Sustainability Action Points</h1>
                <p class="hero-subtitle">
                    Join the movement for zero poverty, zero unemployment, and zero net carbon emissions. Take action, earn the credit and make a difference
                </p>
                <a href="login.php" class="btn-cta" style="color:white;">Get Started</a>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Low Impact Section -->
        <div class="category-card">
            <div class="category-header">
                <h3 class="category-title">Low Impact Actions</h3>
                <span class="category-badge"><i class="fas fa-user info-icon"></i> Individual Level</span>
                <div class="points-display">25 points each</div>
            </div>
            <div class="category-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Action Type</th>
                                <th>Details</th>
                                <th>Verification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="action-type" data-label="Action Type">L1 - Use Reusable Products</td>
                                <td data-label="Details">Use reusable products such as grocery bags, water bottles, coffee cups, lunch containers, etc.</td>
                                <td data-label="Verification">Photo (one-time submission and verification)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">L2 - Waste Sorting</td>
                                <td data-label="Details">Properly sorting recyclable items (bottles, paper, plastic, fabric); composting personal food waste.</td>
                                <td data-label="Verification">Photo evidence of composting/sorting (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">L3 - Use Eco-Friendly or Biodegradable Products</td>
                                <td data-label="Details">Using eco-friendly or biodegradable products.</td>
                                <td data-label="Verification">Photo (one-time submission and verification)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">L4 - Simple Repair or Upcycling</td>
                                <td data-label="Details">Mending clothes, fixing small items instead of discarding, or creatively reusing waste materials (fabric, bottles, plastic, paper).</td>
                                <td data-label="Verification">Before/after project photo (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">L5 - Report Waste Issues/Incidences</td>
                                <td data-label="Details">Reporting water leaks, broken recycling bins, or energy waste via official channels (AIU/PPK).</td>
                                <td data-label="Verification">Screenshot of report or email (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">L6 - Participate in Sustainability Action Programme (On Campus)</td>
                                <td data-label="Details">Participating in sustainability action programmes on campus.</td>
                                <td data-label="Verification">QR Code Scan/Attendance/Photo of participation (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">L7 - Participate in Sustainability Action Programme (Outside Campus)</td>
                                <td data-label="Details">Participating in sustainability action programmes outside the campus.</td>
                                <td data-label="Verification">QR Code Scan/Attendance/Photo of participation (multiple submissions and verifications)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Medium Impact Section -->
        <div class="category-card">
            <div class="category-header">
                <h3 class="category-title">Medium Impact Actions</h3>
                <span class="category-badge"><i class="fas fa-users info-icon"></i> Group Level (5+ people)</span>
                <div class="points-display">50 points each</div>
            </div>
            <div class="category-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Action Type</th>
                                <th>Details</th>
                                <th>Verification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="action-type" data-label="Action Type">M1 - Register in the 3ZERO Club</td>
                                <td data-label="Details">In a group of 5 people, register a 3ZERO Club.</td>
                                <td data-label="Verification">Screenshot of registration proof (one-time submission and verification)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">M2 - Group Volunteering (Outside Campus Event)</td>
                                <td data-label="Details">Among the 3ZERO Club members: volunteer for a local environmental NGO programme.</td>
                                <td data-label="Verification">Letter/Email from organiser confirming participation or event photo (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">M3 - Sustainability Content Creation</td>
                                <td data-label="Details">Among the 3ZERO Club members: create social media content for environmental and sustainability campaigns/awareness.</td>
                                <td data-label="Verification">Social media account (monthly review) (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">M4 - Participate in Environmental/Sustainability Challenge or Competition</td>
                                <td data-label="Details">Among the 3ZERO Club members: participate in environmental or sustainability challenges/competitions locally or internationally.</td>
                                <td data-label="Verification">Registration proof or event photo (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">M5 - Participate in Mentorship and Leadership Programme</td>
                                <td data-label="Details">Among the 3ZERO Club members: participate in mentorship and leadership programmes related to the environment or any sustainability programme locally or internationally.</td>
                                <td data-label="Verification">Registration proof or event photo (multiple submissions and verifications)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- High Impact Section -->
        <div class="category-card">
            <div class="category-header">
                <h3 class="category-title">High Impact Actions</h3>
                <span class="category-badge"><i class="fas fa-users info-icon"></i> Group Level (5+ people)</span>
                <div class="points-display">75 points each</div>
            </div>
            <div class="category-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Action Type</th>
                                <th>Details</th>
                                <th>Verification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="action-type" data-label="Action Type">H1 - Organise 3R (Reduce-Reuse-Recycle) Programme on Campus</td>
                                <td data-label="Details">Among the 3ZERO Club members: organise the 3R programme on campus with minimum participation of 50 people.</td>
                                <td data-label="Verification">Programme tentative, photos, and impact report (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H2 - Organise Paper and Plastic Reduction Programme</td>
                                <td data-label="Details">Among the 3ZERO Club members: organise a paper and plastic reduction programme on campus with minimum participation of 50 people.</td>
                                <td data-label="Verification">Programme tentative, photos, and impact report (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H3 - Organise Food Waste Sorting Programme</td>
                                <td data-label="Details">Among the 3ZERO Club members: organise a food waste sorting programme on campus with minimum participation of 50 people.</td>
                                <td data-label="Verification">Programme tentative, photos, and impact report (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H4 - Organise Energy-Saving Programme</td>
                                <td data-label="Details">Among the 3ZERO Club members: organise an energy-saving programme on campus with minimum participation of 50 people.</td>
                                <td data-label="Verification">Programme tentative, photos, and impact report (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H5 - Organise Programme to Improve Socio-Economic Status</td>
                                <td data-label="Details">Among the 3ZERO Club members: organise campus or community outreach programmes on poverty, unemployment, or literacy with minimum participation of 50 people.</td>
                                <td data-label="Verification">Programme tentative, photos, and impact report (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H6 - Establish Social Business on Campus</td>
                                <td data-label="Details">Among the 3ZERO Club members: successfully establish a social business.</td>
                                <td data-label="Verification">Business registration with ACE-SEDI and monthly financial report (one-time submission and verification)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H7 - Write AIU 3ZERO Club Profile/Activity Book</td>
                                <td data-label="Details">Among the 3ZERO Club members: write AIU 3ZERO Club Profile/Activity Book.</td>
                                <td data-label="Verification">Completed book (one-time submission and verification)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H8 - Secure Grants/Funding for Sustainability Action Projects</td>
                                <td data-label="Details">Among the 3ZERO Club members: successfully secure internal or external grants/funding for product/solution/innovation projects.</td>
                                <td data-label="Verification">Grant award (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H9 - Develop and Copyright Sustainability Action Products/Solutions</td>
                                <td data-label="Details">Among the 3ZERO Club members: successfully develop and copyright sustainability action products/solutions for social innovation.</td>
                                <td data-label="Verification">Copyright evidence from DVCRI's office (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H10 - Participate and Win in Sustainability Action Challenge/Competition</td>
                                <td data-label="Details">Among the 3ZERO Club members: participate and win (minimum top 10) in sustainability action challenges/competitions locally or internationally.</td>
                                <td data-label="Verification">Certificate, award, or prize (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H11 - Organise and Drive a Campus/Community Sustainability Action Event</td>
                                <td data-label="Details">Among the 3ZERO Club members: organise and drive a campus/community sustainability action event with at least 50 participants.</td>
                                <td data-label="Verification">Programme proposal and event photo (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H12 - Develop and Copyright Sustainability Action Resources</td>
                                <td data-label="Details">Among the 3ZERO Club members: develop and copyright comprehensive sustainability education materials (workbook, case studies, manual, etc.) for campus or external use.</td>
                                <td data-label="Verification">Copyright evidence from DVCRI's office (multiple submissions and verifications)</td>
                            </tr>
                            <tr>
                                <td class="action-type" data-label="Action Type">H13 - Completed the AIU 3ZERO Club and Social Business Incubation Programme</td>
                                <td data-label="Details">Among the 3ZERO Club members: completed the AIU 3ZERO Club and Social Business Incubation Programme.</td>
                                <td data-label="Verification">Certificate of Completion</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <a href="#" class="floating-action-btn">
        <i class="fas fa-question"></i>
    </a>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Floating action button functionality
        document.querySelector('.floating-action-btn').addEventListener('click', function(e) {
            e.preventDefault();
            alert('Need help? Contact our sustainability team for assistance with action points!');
        });

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
