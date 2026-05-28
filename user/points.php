<?php 
include '../includes/db.php';
session_start();
?>

<?php include 'includes/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Points</title>
    <link rel="icon" href="assets/images/gc_logo.jpg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f6faf7;
            color: #24362a;
        }

        .points-page {
            max-width: 1180px;
        }

        .points-hero {
            background: #ffffff;
            border: 1px solid rgba(46, 139, 87, 0.14);
            border-radius: 18px;
            box-shadow: 0 18px 46px rgba(31, 51, 41, 0.12);
            padding: clamp(1.5rem, 4vw, 2.5rem);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .points-hero h2 {
            color: #173f27;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .points-section {
            background: #ffffff;
            border: 1px solid rgba(46, 139, 87, 0.12);
            border-radius: 16px;
            box-shadow: 0 12px 34px rgba(31, 51, 41, 0.09);
            padding: clamp(1rem, 3vw, 1.5rem);
            margin-bottom: 1.25rem;
            overflow: hidden;
        }

        .points-section h4 {
            color: #1f5d38;
            font-weight: 800;
            margin-bottom: 0.25rem;
        }

        .points-section p {
            color: #637268;
        }

        .table {
            border-color: #e1ebe4;
            overflow: hidden;
            border-radius: 12px;
        }

        .table thead th {
            background: #1f7a49;
            color: #fff;
            border: 0;
            font-weight: 700;
        }

        .table td {
            background: #fff;
            border-color: #e7efe9;
            vertical-align: middle;
        }

        .table tbody tr:nth-child(even) td {
            background: #f4faf5;
        }

        .table tbody tr {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .table tbody tr:hover td {
            background: #edf7ef;
        }

        body.dark-mode {
            background:
                radial-gradient(circle at top left, rgba(46, 139, 87, 0.16), transparent 34%),
                #0f1411 !important;
            color: #edf6ef;
        }

        body.dark-mode .points-hero,
        body.dark-mode .points-section {
            background: #171d19 !important;
            color: #edf6ef !important;
            border-color: rgba(190, 233, 205, 0.14) !important;
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.42);
        }

        body.dark-mode .points-hero h2,
        body.dark-mode .points-section h4 {
            color: #d9f7e2 !important;
        }

        body.dark-mode .points-hero p,
        body.dark-mode .points-section p {
            color: #a9b8ad !important;
        }

        body.dark-mode .table {
            border-color: rgba(190, 233, 205, 0.14);
        }

        body.dark-mode .table td {
            background: #171d19 !important;
            color: #edf6ef !important;
            border-color: rgba(190, 233, 205, 0.14) !important;
        }

        body.dark-mode .table tbody tr:nth-child(even) td {
            background: #1d2620 !important;
        }

        body.dark-mode .table tbody tr:hover td {
            background: rgba(120, 217, 154, 0.13) !important;
        }

         .table tbody tr:nth-child(1) td:first-child::before {
            content: '';
            margin-right: 8px;
        }

        .table tbody tr:nth-child(2) td:first-child::before {
            content: '';
            margin-right: 8px;
        }

        .table tbody tr:nth-child(3) td:first-child::before {
            content: '';
            margin-right: 8px;
        }

        table {
            margin-bottom: 20px;
        }
        th, td {
            text-align: center;
        }

        @media (max-width: 768px) {
            .points-page {
                width: calc(100% - 1rem);
                margin-top: 1rem !important;
            }

            .points-hero {
                text-align: left;
                border-radius: 14px;
            }

            .points-hero h2 {
                font-size: 1.45rem;
            }

            .points-section {
                border-radius: 14px;
                padding: 1rem;
            }

            .points-section h4 {
                font-size: 1.05rem;
                line-height: 1.35;
            }

            .table thead {
                display: none;
            }

            .table, .table tbody, .table tr, .table td {
                display: block;
                width: 100%;
            }

            .table tr {
                border: 1px solid #e1ebe4;
                border-radius: 12px;
                margin-bottom: 0.85rem;
                overflow: hidden;
                background: #fff;
            }

            body.dark-mode .table tr {
                background: #171d19 !important;
                border-color: rgba(190, 233, 205, 0.14) !important;
            }

            .table td {
                text-align: left;
                padding: 0.8rem 0.9rem;
                border-left: 0;
                border-right: 0;
            }

            .table td::before {
                display: block;
                color: #1f7a49;
                font-weight: 800;
                font-size: 0.78rem;
                text-transform: uppercase;
                margin-bottom: 0.25rem;
            }

            body.dark-mode .table td::before {
                color: #9be7b3;
            }

            .table td:nth-child(1)::before { content: "Label"; }
            .table td:nth-child(2)::before { content: "Type of Action"; }
            .table td:nth-child(3)::before { content: "Action Clarifications"; }
            .table td:nth-child(4)::before { content: "Verification Process"; }
        }
    </style>
</head>
<body>

<div class="container my-5 points-page">

    <div class="points-hero">
        <a href="submit_item.php" class="btn btn-primary" style="margin-bottom: 20px; background-color: #2e8b57; border: none;">Back to Submit Item</a>
        <h2>GREEN CREDIT SYSTEM</h2>
        <p class="mb-0">Review action categories, points, and evidence requirements before submitting.</p>
    </div>

    <!-- Low Impact Section -->
    <section class="points-section">
    <h4>LOW IMPACT LEVEL (INDIVIDUAL ACTION - 25 POINTS)</h4>
    <p>Evidence and Verification Process</p>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Label</th>
                <th>Type of Action</th>
                <th>Action Clarifications</th>
                <th>Verification Process</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td data-label="Label">L1</td>
                <td data-label="Type of Action">Use Reusable Products</td>
                <td data-label="Action Clarifications">Use reusable grocery bags, water bottles, coffee cups, lunch containers, etc.</td>
                <td data-label="Verification Process">Evidence: Photo (one-time submission and verification)</td>
            </tr>
            <tr>
                <td>L2</td>
                <td>Waste Sorting</td>
                <td>Properly sorting recyclable items (bottles, paper, plastic, fabric); composting personal food waste.</td>
                <td>Evidence: Photo evidence of composting/sorting (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>L3</td>
                <td>Use Eco-Friendly or Biodegradable Products</td>
                <td>Using eco-friendly or biodegradable products.</td>
                <td>Evidence: Photo (one-time submission and verification)</td>
            </tr>
            <tr>
                <td>L4</td>
                <td>Simple Repair or Upcycling</td>
                <td>Mending clothes, fixing a small item instead of discarding, simple creative reuse of waste (fabric, bottles, plastic, paper).</td>
                <td>Evidence: Before/After project photo (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>L5</td>
                <td>Report Waste Issue/Incidence</td>
                <td>Reporting water leaks, broken recycling bins, or energy waste via official channels (AIU/PPK).</td>
                <td>Evidence: Screenshot of report or email (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>L6</td>
                <td>Participate in the Sustainability Action Programme on Campus</td>
                <td>Participating in sustainability action programme on campus.</td>
                <td>Evidence: QR Code Scan/Attendance/Photo of participation (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>L7</td>
                <td>Participate in a Sustainability Action Programme Outside Campus</td>
                <td>Participating in sustainability action programme outside campus.</td>
                <td>Evidence: QR Code Scan/Attendance/Photo of participation (multiple submissions and verifications)</td>
            </tr>
        </tbody>
    </table>
    </section>

    <!-- Medium Impact Section -->
    <section class="points-section">
    <h4>MEDIUM IMPACT LEVEL (GROUP ACTION - 50 POINTS)</h4>
    <p>Evidence and Verification Process</p>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Label</th>
                <th>Type of Action</th>
                <th>Action Clarifications</th>
                <th>Verification Process</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>MI1</td>
                <td>Register in the 3ZERO Club</td>
                <td>In a group of 5 people, register a 3ZERO Club.</td>
                <td>Evidence: Screenshot of registration proof (one-time submission and verification)</td>
            </tr>
            <tr>
                <td>MI2</td>
                <td>Group Volunteering (Outside Campus Event)</td>
                <td>Among the 3ZERO Club members: volunteer for a local environmental NGO programme.</td>
                <td>Evidence: A letter/email from the organiser confirms group participation (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>MI3</td>
                <td>Sustainability Content Creation</td>
                <td>Among the 3ZERO Club members: create social media content for environmental and sustainability campaigns/awareness.</td>
                <td>Evidence: Social media account (monthly review) (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>MI4</td>
                <td>Participate in Environmental/Sustainability Challenge/Competition Locally/Internationally</td>
                <td>Among the 3ZERO Club members: participate in environmental/sustainability challenge/competition locally/internationally.</td>
                <td>Evidence: Registration Proof/Event photo (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>MI5</td>
                <td>Participate in Mentorship and Leadership Programme related to the Environment</td>
                <td>Among the 3ZERO Club members: participate in mentorship and leadership programmes related to the environment locally/internationally.</td>
                <td>Evidence: Registration Proof/Event Photo (multiple submissions and verifications)</td>
            </tr>
        </tbody>
    </table>
    </section>

    <!-- High Impact Section -->
    <section class="points-section">
    <h4>HIGH IMPACT LEVEL (GROUP ACTION - 75 POINTS)</h4>
    <p>Evidence and Verification Process</p>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Label</th>
                <th>Type of Action</th>
                <th>Action Clarifications</th>
                <th>Verification Process</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>H1</td>
                <td>Organize 3R (Reduce-Reuse-Recycle) Programme on Campus</td>
                <td>Among the 3ZERO Club members: organise a 3R programme on campus with minimum participation of 50 people.</td>
                <td>Evidence: Programme tentative/photos/impact report (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>H2</td>
                <td>Organize Paper and Plastic Reduction Programme on Campus</td>
                <td>Among the 3ZERO Club members: organise a paper and plastic reduction programme on campus with minimum participation of 50 people.</td>
                <td>Evidence: Programme tentative/photos/impact report (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>H3</td>
                <td>Organize Food Waste Sorting Programme on Campus</td>
                <td>Among the 3ZERO Club members: organise a food waste sorting programme on campus with minimum participation of 50 people.</td>
                <td>Evidence: Programme tentative/photos/impact report (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>H4</td>
                <td>Organize an Energy-Saving Programme on Campus</td>
                <td>Among the 3ZERO Club members: organise an energy-saving programme on campus with minimum participation of 50 people.</td>
                <td>Evidence: Programme tentative/photos/impact report (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>H5</td>
                <td>Organize a Programme to Improve Socio-Economic Status on Campus and in the Community</td>
                <td>Among the 3ZERO Club members: organise campus/community outreach programmes improving socio-economic status (poverty, unemployment, literacy) with minimum participation of 50 people.</td>
                <td>Evidence: Programme tentative/photos/impact report (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>H6</td>
                <td>Establish Social Business on Campus</td>
                <td>Among the 3ZERO Club members: successfully establish a social business.</td>
                <td>Evidence: Business registration with ACE-SEDI and monthly financial report (one-time submission and verification)</td>
            </tr>
            <tr>
                <td>H7</td>
                <td>Write AIU 3ZERO Club Profile/Activity Book</td>
                <td>Among the 3ZERO Club members: write AIU 3ZERO Club Profile/Activity Book.</td>
                <td>Evidence: Completed Book (one-time submission and verification)</td>
            </tr>
            <tr>
                <td>H8</td>
                <td>Securing Grants/Funding for Sustainability Action Projects</td>
                <td>Among the 3ZERO Club members: successfully secure internal or external grant/funding for sustainability projects.</td>
                <td>Evidence: Grant Award (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>H9</td>
                <td>Develop and Copyright Sustainability Action Products/Solutions for Social Innovation</td>
                <td>Among the 3ZERO Club members: successfully develop and copyright sustainability action products/solutions for social innovation.</td>
                <td>Evidence: Copyright evidence from DVCRI’s office (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>H10</td>
                <td>Participate and Win (Top 10) in Sustainability Action Challenge/Competition Locally/Internationally</td>
                <td>Among the 3ZERO Club members: participate and win in sustainability action challenge/competition.</td>
                <td>Evidence: Certificate/Award/Prizes (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>H11</td>
                <td>Organize and Drive a Campus/Community Sustainability Action Event</td>
                <td>Among the 3ZERO Club members: organise and drive a campus/community sustainability action event with at least 50 participants.</td>
                <td>Evidence: Programme proposal/Event photo (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>H12</td>
                <td>Develop and Copyright Sustainability Action Resources</td>
                <td>Among the 3ZERO Club members: develop and copyright sustainability education materials (workbook, case studies, manual, etc.) for campus or external use.</td>
                <td>Evidence: Copyright evidence from DVCRI’s office (multiple submissions and verifications)</td>
            </tr>
            <tr>
                <td>H13</td>
                <td>Completed the AIU 3ZERO Club and Social Business Incubation Programme</td>
                <td>Among the 3ZERO Club members: completed the AIU 3ZERO Club and Social Business Incubation Programme.</td>
                <td>Evidence: Certificate of Completion</td>
            </tr>
        </tbody>
    </table>
    </section>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
