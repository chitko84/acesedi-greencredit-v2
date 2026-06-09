<?php 
include '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch all users with role = 'user' only, selecting email too
$users = [];
$user_query = $conn->query("SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name ASC");
if ($user_query) {
    while ($row = $user_query->fetch_assoc()) {
        $users[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $category = $_POST['category'];
    $action = $_POST['action'];
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $club_id = isset($_POST['club_id']) ? $_POST['club_id'] : null;
    
    // Validate club_id format (only numbers and dashes)
    if (!empty($club_id) && !preg_match('/^[0-9\-]+$/', $club_id)) {
        $_SESSION['error'] = "Club ID can only contain numbers and dashes.";
        header('Location: submit_item.php');
        exit();
    }

    $three_zero_cluster = isset($_POST['three_zero_cluster']) ? $_POST['three_zero_cluster'] : [];
    if (count($three_zero_cluster) != 1) {
        $_SESSION['error'] = "Please select exactly one 3ZERO cluster.";
        header('Location: submit_item.php');
        exit();
    }
    $three_zero_json = json_encode($three_zero_cluster);
    
    // Club ID is required for Medium/High Impact
    if (($category == 'Medium Impact' || $category == 'High Impact') && empty($club_id)) {
        $_SESSION['error'] = "Club ID is required for Medium and High Impact submissions.";
        header('Location: submit_item.php');
        exit();
    }

    // Validate description is not empty
    if (empty($description)) {
        $_SESSION['error'] = "Please fill in all required details in the description field.";
        header('Location: submit_item.php');
        exit();
    }

    $user_details = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
    $user_details->bind_param("i", $user_id);
    $user_details->execute();
    $result = $user_details->get_result();
    $user = $result->fetch_assoc();
    $name = $user['name'];
    $user_email = $user['email'];

    // Validate uploaded files
    $allowed_types = [
        'image/png',
        'image/jpeg',
        'image/jpg',
        'image/gif',
        'application/pdf'
    ];
    $max_file_size = 15 * 1024 * 1024; // 15 MB
    
    $uploaded_files = $_FILES['proof_image'];
    $num_files = count(array_filter($uploaded_files['name']));

    // Check PDF vs Image rules
    $pdf_files = array_filter($uploaded_files['type'], function($type) {
        return $type === 'application/pdf';
    });
    $image_files = array_filter($uploaded_files['type'], function($type) {
        return in_array($type, ['image/png', 'image/jpeg', 'image/jpg', 'image/gif']);
    });
    
    // If PDFs are uploaded
    if (count($pdf_files) > 0) {
        if (count($pdf_files) !== $num_files) {
            $_SESSION['error'] = "PDF files cannot be mixed with images. Please upload only PDFs or only images.";
            header('Location: submit_item.php');
            exit();
        }
        if (count($pdf_files) !== 1) {
            $_SESSION['error'] = "Please upload exactly 1 PDF file.";
            header('Location: submit_item.php');
            exit();
        }
    } else {
        // Image validation
        if ($num_files < 2) {
            $_SESSION['error'] = "Please upload at least 2 images.";
            header('Location: submit_item.php');
            exit();
        }
        if ($num_files > 5) {
            $_SESSION['error'] = "You can upload a maximum of 5 images.";
            header('Location: submit_item.php');
            exit();
        }
    }

    $uploaded_file_names = [];
    
    for ($i = 0; $i < $num_files; $i++) {
        $file_type = $uploaded_files['type'][$i];
        $file_size = $uploaded_files['size'][$i];
    
        // Check file type
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = "Only PNG, JPG, JPEG, GIF, and PDF files are allowed.";
            header('Location: submit_item.php');
            exit();
        }
    
        // Check file size
        if ($file_size > $max_file_size) {
            $_SESSION['error'] = "Each file must be less than 15 MB.";
            header('Location: submit_item.php');
            exit();
        }
    
        // Process file upload
        $tmp_name = $uploaded_files['tmp_name'][$i];
        $original_name = basename($uploaded_files['name'][$i]);
        $target_dir = "uploads/";
        $new_name = uniqid() . "-" . preg_replace("/[^a-zA-Z0-9.\-_]/", "", $original_name);
        $target_file = $target_dir . $new_name;
    
        if (move_uploaded_file($tmp_name, $target_file)) {
            $uploaded_file_names[] = $new_name;
        } else {
            $_SESSION['error'] = "Error uploading file: " . htmlspecialchars($original_name);
            header('Location: submit_item.php');
            exit();
        }
    }
    
    // Assign points by category
    if ($category == 'Low Impact') {
        $points = 25;
    } elseif ($category == 'Medium Impact') {
        $points = 50;
    } elseif ($category == 'High Impact') {
        $points = 75;
    } else {
        $points = 0;
    }

    // Team members handling
    $team_members = [];
    $selected_team_members = !empty($_POST['team_members']) ? $_POST['team_members'] : [];

    // Add submitter as a team member if not already included
    if (!in_array($user_id, $selected_team_members)) {
        $selected_team_members[] = $user_id;
    }

    $team_number = count($selected_team_members);

    // Updated validation: 3-5 members total (including submitter)
    if (($category == 'Medium Impact' || $category == 'High Impact') && ($team_number < 3 || $team_number > 5)) {
        $_SESSION['error'] = "You must have between 3-5 team members (including you) for Medium and High Impact.";
        header('Location: submit_item.php');
        exit();
    }

    if (!empty($selected_team_members)) {
        $team_member_ids = $selected_team_members;

        // Fetch member names by IDs safely
        $placeholders = implode(',', array_fill(0, count($team_member_ids), '?'));
        $types = str_repeat('i', count($team_member_ids));

        $stmt_names = $conn->prepare("SELECT name FROM users WHERE id IN ($placeholders)");
        if ($stmt_names === false) {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header('Location: submit_item.php');
            exit();
        }
        
        $stmt_names->bind_param($types, ...array_map('intval', $team_member_ids));
        $stmt_names->execute();
        $result_names = $stmt_names->get_result();

        while ($row = $result_names->fetch_assoc()) {
            $team_members[] = $row['name'];
        }
        $stmt_names->close();
    }

    $team_members_json = json_encode($team_members);
    $proof_image_json = json_encode($uploaded_file_names);

    // Insert submission
    $query = "INSERT INTO submissions 
          (user_id, category, action, points, proof_image, status, description, team_number, team_members, club_id, three_zero_cluster) 
          VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        $_SESSION['error'] = "Database prepare failed: " . $conn->error;
        header('Location: submit_item.php');
        exit();
    }
    
    $stmt->bind_param(
        "isssssisss",
        $user_id,
        $category,
        $action,
        $points,
        $proof_image_json,
        $description,
        $team_number,
        $team_members_json,
        $club_id,
        $three_zero_json
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Your submission has been successfully recorded! Please check your email for further updates. Sometimes it can be in the spam folder so always check your spam folder too so that you won't miss out any detail from us";
        $display_points = 0;

        // Send email to user (submitter)
        $user_subject = 'Thank You for Your Submission!';
        $user_message = "
            <html>
            <head>
                <title>Thank You for Your Submission!</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #2E8B57; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; }
                    .footer { margin-top: 20px; text-align: center; font-size: 0.8em; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Thank You for Your Submission!</h2>
                    </div>
                    <div class='content'>
                        <p>Dear $name,</p>
                        <p>Thank you for submitting your sustainability action. We appreciate your participation in GreenCredit.</p>
                        <p>Stay tuned for updates, and we will notify you once your submission has been reviewed and approved.</p>
                        <p><strong>Team Members:</strong> " . implode(", ", $team_members) . "</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message from GreenCredit</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $user_headers = "MIME-Version: 1.0" . "\r\n";
        $user_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $user_headers .= "From: GreenCredit <acesediaiuedu@ace-sedi.aiu.edu.my>" . "\r\n";
        
        // Send email to submitter
        mail($user_email, $user_subject, $user_message, $user_headers);
        
        // Send email to each team member (excluding the submitter)
        if (!empty($selected_team_members)) {
            $team_subject = 'You Have Been Added to a GreenCredit Submission!';
            
            foreach ($selected_team_members as $member_id) {
                // Skip the submitter (they already got the email)
                if ($member_id == $user_id) {
                    continue;
                }
                
                // Get team member details
                $member_stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
                $member_stmt->bind_param("i", $member_id);
                $member_stmt->execute();
                $member_result = $member_stmt->get_result();
                
                if ($member_row = $member_result->fetch_assoc()) {
                    $member_name = $member_row['name'];
                    $member_email = $member_row['email'];
                    
                    $team_message = "
                        <html>
                        <head>
                            <title>You Have Been Added to a GreenCredit Submission!</title>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                .header { background-color: #2E8B57; color: white; padding: 10px; text-align: center; }
                                .content { padding: 20px; background-color: #f9f9f9; }
                                .footer { margin-top: 20px; text-align: center; font-size: 0.8em; color: #666; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <div class='header'>
                                    <h2>You're Part of a GreenCredit Team!</h2>
                                </div>
                                <div class='content'>
                                    <p>Dear $member_name,</p>
                                    <p>You have been added as a team member to an eco-friendly action submission by $name.</p>
                                    <p>The submission details:</p>
                                    <ul>
                                        <li><strong>Category:</strong> $category</li>
                                        <li><strong>Action:</strong> $action</li>
                                        <li><strong>Team Members:</strong> " . implode(", ", $team_members) . "</li>
                                    </ul>
                                    <p>Stay tuned for updates, and we will notify you once the submission has been reviewed and approved.</p>
                                </div>
                                <div class='footer'>
                                    <p>This is an automated message from GreenCredit</p>
                                </div>
                            </div>
                        </body>
                        </html>
                    ";
                    
                    $team_headers = "MIME-Version: 1.0" . "\r\n";
                    $team_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $team_headers .= "From: GreenCredit <acesediaiuedu@ace-sedi.aiu.edu.my>" . "\r\n";
                    
                    // Send email to team member
                    mail($member_email, $team_subject, $team_message, $team_headers);
                }
                $member_stmt->close();
            }
        }

        // Admin email list (add more later as needed)
        $admin_emails = [
            'chitko.ko@student.aiu.edu.my',
            'another.admin@example.com',
        ];
        
        // Email subject and message
        $admin_subject = 'New User Submission';
        $admin_message = "
            <html>
            <head>
                <title>New Submission Received</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #2E8B57; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; }
                    .footer { margin-top: 20px; text-align: center; font-size: 0.8em; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>New User Submission</h2>
                    </div>
                    <div class='content'>
                        <p>A new submission has been made by a user.</p>
                        <ul>
                            <li><strong>User Name:</strong> $name</li>
                            <li><strong>Category:</strong> $category</li>
                            <li><strong>Action:</strong> $action</li>
                            <li><strong>Points:</strong> $display_points</li>
                            <li><strong>Team Members:</strong> " . implode(", ", $team_members) . "</li>
                            <li><strong>Description:</strong> $description</li>
                        </ul>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message from GreenCredit</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Email headers
        $admin_headers = "MIME-Version: 1.0" . "\r\n";
        $admin_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $admin_headers .= "From: GreenCredit <acesediaiuedu@ace-sedi.aiu.edu.my>" . "\r\n";
        
        // Loop through admin emails and send individually
        foreach ($admin_emails as $admin_email) {
            mail($admin_email, $admin_subject, $admin_message, $admin_headers);
        }

        header('Location: submission_success.php');
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
        header('Location: submit_item.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Submit Eco-Friendly Action</title>
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --gc-primary: #2E8B57;
            --gc-primary-dark: #216a43;
            --gc-primary-soft: #E8F5E9;
            --gc-secondary: #FFC107;
            --gc-bg: #f4f8f5;
            --gc-card: #ffffff;
            --gc-text: #253238;
            --gc-muted: #6c757d;
            --gc-border: #dfe7e2;
            --gc-danger: #dc3545;
            --gc-shadow: 0 12px 32px rgba(31, 90, 58, 0.10);
            --gc-shadow-soft: 0 6px 18px rgba(31, 90, 58, 0.08);
        }

        body {
            background:
                radial-gradient(circle at top left, rgba(46, 139, 87, 0.12), transparent 32rem),
                linear-gradient(180deg, #f7fbf8 0%, #eef6f1 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--gc-text);
        }

        .page-wrapper {
            max-width: 1240px;
            margin: 0 auto;
            padding: 32px 16px 56px;
        }

        .hero-card {
            background: linear-gradient(135deg, #2E8B57 0%, #1f6b43 100%);
            color: #fff;
            border-radius: 24px;
            padding: 32px;
            box-shadow: var(--gc-shadow);
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .hero-card::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.10);
            right: -70px;
            top: -80px;
        }

        .hero-card::before {
            content: "";
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 193, 7, 0.16);
            right: 110px;
            bottom: -90px;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.28);
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 0.86rem;
            margin-bottom: 14px;
        }

        .hero-title {
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 10px;
        }

        .hero-text {
            max-width: 720px;
            color: rgba(255, 255, 255, 0.88);
            margin-bottom: 0;
            line-height: 1.7;
        }

        .points-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #ffffff;
            color: var(--gc-primary);
            border-radius: 14px;
            padding: 12px 18px;
            text-decoration: none;
            font-weight: 700;
            box-shadow: 0 10px 22px rgba(0,0,0,0.12);
            transition: 0.2s ease;
            white-space: nowrap;
        }

        .points-link:hover {
            color: var(--gc-primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(0,0,0,0.16);
        }

        .layout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 22px;
            align-items: start;
        }

        .form-panel,
        .side-panel,
        .form-section {
            background: var(--gc-card);
            border: 1px solid rgba(46, 139, 87, 0.12);
            border-radius: 22px;
            box-shadow: var(--gc-shadow-soft);
        }

        .form-panel {
            padding: 22px;
        }

        .form-section {
            padding: 22px;
            margin-bottom: 18px;
            box-shadow: none;
            border-radius: 18px;
        }

        .section-header {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
        }

        .section-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: var(--gc-primary-soft);
            color: var(--gc-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            font-size: 1.05rem;
        }

        .section-title {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            color: #1f2f28;
        }

        .section-subtitle {
            margin: 4px 0 0;
            color: var(--gc-muted);
            font-size: 0.88rem;
            line-height: 1.45;
        }

        .form-label {
            font-weight: 700;
            margin-bottom: 8px;
            color: #263b31;
            font-size: 0.92rem;
        }

        .form-control,
        .form-select {
            border: 1px solid var(--gc-border);
            border-radius: 12px;
            padding: 11px 14px;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--gc-primary);
            box-shadow: 0 0 0 0.22rem rgba(46, 139, 87, 0.14);
        }

        .helper-text {
            color: var(--gc-muted);
            font-size: 0.82rem;
            margin-top: 6px;
        }

        .info-callout {
            background: #f7faf8;
            border: 1px solid var(--gc-border);
            border-left: 4px solid var(--gc-primary);
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 0.9rem;
            color: #3a4a42;
        }

        .cluster-container {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .cluster-option {
            padding: 18px 14px;
            border: 1.5px solid var(--gc-border);
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            background: #fff;
            min-height: 128px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .cluster-option:hover {
            border-color: var(--gc-primary);
            background: #fbfffc;
            transform: translateY(-2px);
        }

        .cluster-option.selected {
            background: var(--gc-primary);
            color: #fff;
            border-color: var(--gc-primary);
            box-shadow: 0 12px 22px rgba(46, 139, 87, 0.22);
        }

        .cluster-option i {
            font-size: 1.6rem;
        }

        .cluster-option label {
            font-weight: 700;
            cursor: pointer;
            margin: 0;
            line-height: 1.25;
        }

        .cluster-option input {
            display: none;
        }

        .required-field::after {
            content: " *";
            color: var(--gc-danger);
        }

        .error-message {
            color: var(--gc-danger);
            font-size: 0.85rem;
            margin-top: 8px;
            display: none;
        }

        .team-member-counter {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--gc-primary-soft);
            color: var(--gc-primary-dark);
            font-weight: 800;
            border-radius: 999px;
            padding: 8px 14px;
            margin-bottom: 12px;
            font-size: 0.88rem;
        }

        .search-container {
            position: relative;
            margin-bottom: 12px;
        }

        .search-results {
            position: absolute;
            top: calc(100% + 2px);
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--gc-border);
            border-radius: 0 0 14px 14px;
            max-height: 240px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: var(--gc-shadow-soft);
        }

        .search-result-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #edf2ef;
            transition: 0.15s ease;
        }

        .search-result-item:hover {
            background-color: #f5faf7;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .selected-members-container,
        .team-confirmed-list {
            margin-top: 14px;
            border: 1px solid var(--gc-border);
            border-radius: 16px;
            padding: 16px;
            background-color: #f8fbf9;
        }

        .selected-member,
        .confirmed-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            background-color: #fff;
            border: 1px solid #e6eee9;
            border-radius: 12px;
            margin-bottom: 8px;
        }

        .selected-member-info {
            display: flex;
            align-items: center;
            min-width: 0;
        }

        .selected-member-info span,
        .confirmed-item div {
            word-break: break-word;
        }

        .selected-member-remove {
            color: var(--gc-danger);
            cursor: pointer;
            background: none;
            border: none;
            font-size: 16px;
            padding: 4px 8px;
            border-radius: 8px;
        }

        .selected-member-remove:hover {
            background: #fff0f0;
        }

        .team-selection-controls {
            display: flex;
            gap: 10px;
            margin-top: 14px;
            flex-wrap: wrap;
        }

        .file-upload-container {
            margin-bottom: 16px;
        }

        .file-upload-dropzone {
            border: 2px dashed #b9cfc1;
            border-radius: 18px;
            padding: 34px 24px;
            text-align: center;
            background: linear-gradient(180deg, #ffffff, #f8fcfa);
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 14px;
        }

        .file-upload-dropzone:hover,
        .file-upload-dropzone.active {
            border-color: var(--gc-primary);
            background: var(--gc-primary-soft);
        }

        .file-upload-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            background: var(--gc-primary-soft);
            color: var(--gc-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin-bottom: 14px;
        }

        .file-upload-text {
            font-size: 1rem;
            margin-bottom: 8px;
            color: #263b31;
        }

        .file-upload-btn {
            background: var(--gc-primary);
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .file-upload-btn:hover {
            background: var(--gc-primary-dark);
        }

        .file-upload-note {
            background: #fffdf4;
            border: 1px solid #ffe8a3;
            border-left: 4px solid var(--gc-secondary);
            padding: 14px 16px;
            margin-bottom: 0;
            font-size: 0.9rem;
            border-radius: 14px;
        }

        .file-list {
            list-style: none;
            padding: 0;
            margin-top: 14px;
        }

        .file-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            background-color: white;
            border: 1px solid #e6eee9;
            border-radius: 12px;
            margin-bottom: 8px;
        }

        .file-list-item-info {
            display: flex;
            align-items: center;
            flex-grow: 1;
            min-width: 0;
        }

        .file-list-item-icon {
            color: var(--gc-primary);
            margin-right: 10px;
            font-size: 20px;
            width: 24px;
            text-align: center;
            flex: 0 0 auto;
        }

        .file-list-item-name {
            font-weight: 600;
            margin-right: 8px;
            word-break: break-all;
        }

        .file-list-item-size {
            color: var(--gc-muted);
            font-size: 0.85rem;
            white-space: nowrap;
        }

        .file-list-item-remove {
            color: var(--gc-danger);
            cursor: pointer;
            background: none;
            border: none;
            font-size: 18px;
            padding: 4px 8px;
            border-radius: 8px;
            flex: 0 0 auto;
        }

        .file-list-item-remove:hover {
            background: #fff0f0;
        }

        .submit-btn {
            border-radius: 16px;
            padding: 15px 24px;
            font-weight: 800;
            font-size: 1rem;
            background: linear-gradient(135deg, var(--gc-primary), var(--gc-primary-dark));
            border: none;
            box-shadow: 0 12px 24px rgba(46, 139, 87, 0.25);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 30px rgba(46, 139, 87, 0.30);
            background: linear-gradient(135deg, #349c63, #1f6b43);
        }

        .side-panel {
            position: sticky;
            top: 18px;
            padding: 20px;
        }

        .guide-card {
            border: 1px solid #e4eee7;
            border-radius: 18px;
            padding: 16px;
            background: #fff;
            margin-bottom: 14px;
        }

        .guide-card:last-child {
            margin-bottom: 0;
        }

        .guide-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            margin-bottom: 10px;
            color: #1f2f28;
        }

        .guide-title i {
            color: var(--gc-primary);
        }

        .points-grid {
            display: grid;
            gap: 8px;
        }

        .point-pill {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f7faf8;
            border: 1px solid #e5eee9;
            border-radius: 12px;
            padding: 9px 11px;
            font-size: 0.88rem;
        }

        .point-pill strong {
            color: var(--gc-primary);
        }

        .mini-list {
            padding-left: 1.1rem;
            margin-bottom: 0;
            color: #52635a;
            font-size: 0.9rem;
            line-height: 1.65;
        }

        body.dark-mode {
            --gc-primary: #66e08f;
            --gc-primary-dark: #43bd70;
            --gc-primary-soft: rgba(102, 224, 143, 0.12);
            --gc-bg: #07110d;
            --gc-card: #111c17;
            --gc-text: #e8fff0;
            --gc-muted: #b8cfc1;
            --gc-border: #244638;
            --gc-danger: #ff8f8f;
            --gc-shadow: 0 18px 42px rgba(0, 0, 0, 0.34);
            --gc-shadow-soft: 0 10px 28px rgba(0, 0, 0, 0.28);
            background:
                radial-gradient(circle at top left, rgba(102, 224, 143, 0.10), transparent 32rem),
                linear-gradient(180deg, #07110d 0%, #0b1210 100%);
            color: var(--gc-text);
        }

        body.dark-mode .form-panel,
        body.dark-mode .side-panel,
        body.dark-mode .form-section,
        body.dark-mode .guide-card {
            background: var(--gc-card);
            border-color: var(--gc-border);
            color: var(--gc-text);
            box-shadow: var(--gc-shadow-soft);
        }

        body.dark-mode .form-section {
            background: #121a17;
        }

        body.dark-mode .section-title,
        body.dark-mode .form-label,
        body.dark-mode .file-upload-text,
        body.dark-mode .guide-title,
        body.dark-mode .cluster-option label,
        body.dark-mode .selected-member-info span,
        body.dark-mode .confirmed-item div,
        body.dark-mode .file-list-item-name,
        body.dark-mode .fw-bold,
        body.dark-mode .fw-semibold {
            color: var(--gc-text);
        }

        body.dark-mode .section-subtitle,
        body.dark-mode .helper-text,
        body.dark-mode .info-callout,
        body.dark-mode .file-upload-note,
        body.dark-mode .file-list-item-size,
        body.dark-mode .mini-list,
        body.dark-mode .text-muted,
        body.dark-mode small {
            color: var(--gc-muted) !important;
        }

        body.dark-mode .section-icon,
        body.dark-mode .file-upload-icon,
        body.dark-mode .team-member-counter {
            background: var(--gc-primary-soft);
            color: var(--gc-primary);
            border: 1px solid rgba(102, 224, 143, 0.20);
        }

        body.dark-mode .form-control,
        body.dark-mode .form-select,
        body.dark-mode textarea.form-control {
            background-color: #0b1511;
            border-color: var(--gc-border);
            color: var(--gc-text);
        }

        body.dark-mode .form-control::placeholder,
        body.dark-mode textarea.form-control::placeholder {
            color: #86a894;
            opacity: 1;
        }

        body.dark-mode .form-control:focus,
        body.dark-mode .form-select:focus,
        body.dark-mode textarea.form-control:focus {
            background-color: #0d1813;
            border-color: var(--gc-primary);
            color: var(--gc-text);
            box-shadow: 0 0 0 0.22rem rgba(102, 224, 143, 0.18);
        }

        body.dark-mode .form-select option {
            background: #0b1511;
            color: var(--gc-text);
        }

        body.dark-mode .info-callout {
            background: rgba(102, 224, 143, 0.08);
            border-color: var(--gc-border);
            border-left-color: var(--gc-primary);
        }

        body.dark-mode .cluster-option {
            background: #0b1511;
            border-color: var(--gc-border);
            color: var(--gc-text);
        }

        body.dark-mode .cluster-option:hover {
            background: #13231b;
            border-color: var(--gc-primary);
        }

        body.dark-mode .cluster-option.selected {
            background: linear-gradient(135deg, #1f8f4f, #16703d);
            border-color: var(--gc-primary);
            color: #ffffff;
            box-shadow: 0 14px 26px rgba(102, 224, 143, 0.18);
        }

        body.dark-mode .search-results {
            background: #0b1511;
            border-color: var(--gc-border);
            box-shadow: var(--gc-shadow-soft);
        }

        body.dark-mode .search-result-item {
            color: var(--gc-text);
            border-bottom-color: var(--gc-border);
        }

        body.dark-mode .search-result-item:hover {
            background-color: #13231b;
        }

        body.dark-mode .selected-members-container,
        body.dark-mode .team-confirmed-list,
        body.dark-mode .point-pill {
            background-color: #0b1511;
            border-color: var(--gc-border);
            color: var(--gc-text);
        }

        body.dark-mode .selected-member,
        body.dark-mode .confirmed-item,
        body.dark-mode .file-list-item {
            background-color: #111c17;
            border-color: var(--gc-border);
            color: var(--gc-text);
        }

        body.dark-mode .selected-member-remove:hover,
        body.dark-mode .file-list-item-remove:hover {
            background: rgba(255, 143, 143, 0.12);
        }

        body.dark-mode .file-upload-dropzone {
            background: #0b1511;
            border-color: #2f6d4b;
            color: var(--gc-text);
        }

        body.dark-mode .file-upload-dropzone:hover,
        body.dark-mode .file-upload-dropzone.active {
            background: #13231b;
            border-color: var(--gc-primary);
        }

        body.dark-mode .file-upload-note {
            background: rgba(255, 193, 7, 0.08);
            border-color: rgba(255, 193, 7, 0.26);
            border-left-color: var(--gc-secondary);
        }

        body.dark-mode .guide-card {
            background: #121a17;
        }

        body.dark-mode .guide-title i,
        body.dark-mode .point-pill strong,
        body.dark-mode .file-list-item-icon {
            color: var(--gc-primary);
        }

        body.dark-mode .submit-btn {
            background: linear-gradient(135deg, #2bbf68, #14723e);
            color: #ffffff;
            box-shadow: 0 14px 28px rgba(43, 191, 104, 0.22);
        }

        body.dark-mode .submit-btn:hover {
            background: linear-gradient(135deg, #3bd97d, #198a4b);
            box-shadow: 0 18px 34px rgba(43, 191, 104, 0.28);
        }

        body.dark-mode .team-selection-controls .btn-secondary {
            background: #1f8f4f;
            border-color: #2bbf68;
            color: #ffffff;
        }

        body.dark-mode .team-selection-controls .btn-secondary:hover {
            background: #27a95e;
            border-color: var(--gc-primary);
        }

        body.dark-mode .team-selection-controls .btn-outline-secondary {
            background: transparent;
            border-color: var(--gc-border);
            color: var(--gc-muted);
        }

        body.dark-mode .team-selection-controls .btn-outline-secondary:hover {
            background: var(--gc-primary-soft);
            border-color: var(--gc-primary);
            color: var(--gc-text);
        }

        body.dark-mode .alert-success {
            background: rgba(102, 224, 143, 0.12);
            color: var(--gc-text);
            border: 1px solid rgba(102, 224, 143, 0.32);
        }

        body.dark-mode .alert-danger {
            background: rgba(255, 143, 143, 0.12);
            color: var(--gc-text);
            border: 1px solid rgba(255, 143, 143, 0.32);
        }

        .alert {
            border-radius: 16px;
            border: none;
            box-shadow: var(--gc-shadow-soft);
        }

        .mobile-only {
            display: none;
        }

        @media (max-width: 992px) {
            .layout-grid {
                grid-template-columns: 1fr;
            }

            .side-panel {
                position: static;
                order: -1;
            }

            .hero-card {
                padding: 26px 22px;
            }
        }

        @media (max-width: 768px) {
            .page-wrapper {
                padding: 18px 12px 40px;
            }

            .hero-card {
                border-radius: 20px;
            }

            .hero-title {
                font-size: 1.8rem;
            }

            .hero-actions {
                margin-top: 18px;
            }

            .form-panel {
                padding: 14px;
                border-radius: 18px;
            }

            .form-section {
                padding: 18px;
            }

            .cluster-container {
                grid-template-columns: 1fr;
            }

            .team-selection-controls .btn {
                width: 100%;
            }

            .file-upload-dropzone {
                padding: 26px 18px;
            }

            .selected-member,
            .confirmed-item,
            .file-list-item {
                align-items: flex-start;
            }

            .file-list-item {
                flex-direction: column;
            }

            .file-list-item-remove {
                align-self: flex-end;
            }

            .desktop-only {
                display: none;
            }

            .mobile-only {
                display: block;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="page-wrapper">

        <section class="hero-card">
            <div class="hero-content">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <div class="hero-badge">
                            <i class="fas fa-leaf"></i>
                            GreenCredit Submission Portal
                        </div>
                        <h1 class="hero-title">Submit Eco-Friendly Action</h1>
                        <p class="hero-text">
                            Record your sustainability action, upload proof, select the relevant 3ZERO cluster,
                            and include your team members if the activity is Medium or High Impact.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end hero-actions">
                        <a href="points.php" class="points-link">
                            <i class="fas fa-star"></i>
                            View Points System
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <?php 
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger mb-4'><i class='fas fa-circle-exclamation me-2'></i>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<div class='alert alert-success mb-4'><i class='fas fa-circle-check me-2'></i>" . $_SESSION['success'] . "</div>";
            unset($_SESSION['success']);
        }
        ?>

        <div class="layout-grid">
            <main class="form-panel">
                <form action="submit_item.php" method="POST" enctype="multipart/form-data" id="submissionForm">

                    <section class="form-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-clipboard-list"></i></div>
                            <div>
                                <h2 class="section-title">Action Details</h2>
                                <p class="section-subtitle">Choose the impact category and the specific sustainability action.</p>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="category" class="form-label required-field">Select Category</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Select a Category</option>
                                    <option value="Low Impact">Low Impact (25 points)</option>
                                    <option value="Medium Impact">Medium Impact (50 points)</option>
                                    <option value="High Impact">High Impact (75 points)</option>
                                </select>
                            </div>

                            <div class="col-md-6" id="club_id_div" style="display:none;">
                                <label for="club_id" class="form-label required-field">Club ID</label>
                                <input type="text" class="form-control" id="club_id" name="club_id"
                                       pattern="[0-9\-]+" title="Only numbers and dashes are allowed"
                                       placeholder="Example: 123-456-789">
                                <div class="helper-text">Required for Medium and High Impact. Only numbers and dashes are allowed.</div>
                            </div>

                            <div class="col-12">
                                <label for="action" class="form-label required-field">Type of Action</label>
                                <select class="form-select" id="action" name="action" required>
                                    <option value="">Select an Action</option>
                                </select>
                            </div>
                        </div>
                    </section>

                    <section class="form-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-seedling"></i></div>
                            <div>
                                <h2 class="section-title">3ZERO Cluster</h2>
                                <p class="section-subtitle">Select exactly one cluster that best represents your action.</p>
                            </div>
                        </div>

                        <div class="cluster-container">
                            <div class="cluster-option" data-value="Zero Poverty">
                                <i class="fas fa-hand-holding-heart"></i>
                                <input type="radio" name="three_zero_cluster[]" value="Zero Poverty" id="zero_poverty">
                                <label for="zero_poverty">Zero Poverty</label>
                            </div>

                            <div class="cluster-option" data-value="Zero Unemployment">
                                <i class="fas fa-briefcase"></i>
                                <input type="radio" name="three_zero_cluster[]" value="Zero Unemployment" id="zero_unemployment">
                                <label for="zero_unemployment">Zero Unemployment</label>
                            </div>

                            <div class="cluster-option" data-value="Zero Net Carbon Emission">
                                <i class="fas fa-leaf"></i>
                                <input type="radio" name="three_zero_cluster[]" value="Zero Net Carbon Emission" id="zero_carbon">
                                <label for="zero_carbon">Zero Net Carbon Emission</label>
                            </div>
                        </div>

                        <div id="cluster-error" class="error-message">Please select exactly one cluster.</div>
                    </section>

                    <section class="form-section" id="team_members_div" style="display:none;">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-user-group"></i></div>
                            <div>
                                <h2 class="section-title">Team Members</h2>
                                <p class="section-subtitle">
                                    You are automatically included. For Medium/High Impact, select 2–4 additional members.
                                </p>
                            </div>
                        </div>

                        <div class="team-member-counter" id="team_counter">
                            Selected: 1 (you) + 0 = 1/5
                        </div>

                        <div class="search-container">
                            <label for="team_search" class="form-label">Search Team Members</label>
                            <input type="text" id="team_search" class="form-control" placeholder="Start typing name or email...">
                            <div class="search-results" id="search_results"></div>
                        </div>

                        <div class="selected-members-container" id="selected_members_container" style="display: none;">
                            <h6 class="fw-bold mb-3">Selected Team Members</h6>
                            <div id="selected_members_list"></div>
                        </div>

                        <div id="selected_team_inputs"></div>

                        <div class="team-selection-controls">
                            <button type="button" id="confirm_team_btn" class="btn btn-secondary">
                                <i class="fas fa-check me-1"></i> Confirm Selection
                            </button>
                            <button type="button" id="edit_team_btn" class="btn btn-outline-secondary" style="display:none;">
                                <i class="fas fa-pen me-1"></i> Edit Selection
                            </button>
                        </div>

                        <div id="confirmed_team_list" class="team-confirmed-list" style="display:none;">
                            <h6 class="fw-bold mb-3">Confirmed Team Members</h6>
                            <div id="confirmed_team_members">
                                <p class="mb-2"><i class="fas fa-user-check me-2 text-success"></i>You (automatically included)</p>
                            </div>
                            <div id="additional_members_list"></div>
                        </div>

                        <div id="team-error" class="text-danger mt-2" style="display:none;">
                            Please select 2–4 additional team members for Medium/High Impact.
                        </div>
                    </section>

                    <section class="form-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                            <div>
                                <h2 class="section-title">Upload Proof</h2>
                                <p class="section-subtitle">Upload clear evidence of your action. Use either images or one PDF.</p>
                            </div>
                        </div>

                        <div class="file-upload-container">
                            <div id="fileDropzone" class="file-upload-dropzone">
                                <div class="file-upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="file-upload-text">
                                    <strong>Drop your files here</strong> or click to browse
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Supported formats: PDF, PNG, JPG, JPEG, GIF</small>
                                </div>
                                <button type="button" class="file-upload-btn" id="fileUploadBtn">
                                    <i class="fas fa-folder-open me-1"></i> Choose Files
                                </button>
                            </div>

                            <input
                                type="file"
                                class="d-none"
                                id="proof_image"
                                name="proof_image[]"
                                accept="image/png,image/jpeg,image/jpg,image/gif,application/pdf"
                                multiple
                                required
                            />

                            <ul id="file_list" class="file-list"></ul>
                        </div>

                        <div class="file-upload-note">
                            <strong><i class="fas fa-info-circle me-2"></i>Upload Rules</strong>
                            <ul class="mb-0 mt-2">
                                <li>Submit 2–5 images for PNG, JPG, JPEG, or GIF.</li>
                                <li>Submit exactly 1 file if the proof is PDF.</li>
                                <li>Do not mix PDF with images.</li>
                                <li>Maximum file size is 15 MB per file.</li>
                            </ul>
                        </div>
                    </section>

                    <section class="form-section">
                        <div class="section-header">
                            <div class="section-icon"><i class="fas fa-align-left"></i></div>
                            <div>
                                <h2 class="section-title">Additional Details</h2>
                                <p class="section-subtitle">Provide enough information for admins to verify your submission.</p>
                            </div>
                        </div>

                        <label for="description" class="form-label required-field">Description</label>
                        <div class="info-callout mb-2">
                            Please include important details such as date, time, venue, activity summary, and your role.
                        </div>
                        <textarea class="form-control" id="description" name="description" rows="8" required placeholder="Example: On 20 April 2026, our team organized..."></textarea>
                        <div class="helper-text">Complete details help admins review your submission faster.</div>
                    </section>

                    <button type="submit" class="btn btn-primary w-100 submit-btn" id="submitBtn">
                        <i class="fas fa-paper-plane me-2"></i> Submit Action
                    </button>
                </form>
            </main>

            <aside class="side-panel">
                <div class="guide-card">
                    <div class="guide-title">
                        <i class="fas fa-star"></i>
                        Points Guide
                    </div>
                    <div class="points-grid">
                        <div class="point-pill">
                            <span>Low Impact</span>
                            <strong>25 pts</strong>
                        </div>
                        <div class="point-pill">
                            <span>Medium Impact</span>
                            <strong>50 pts</strong>
                        </div>
                        <div class="point-pill">
                            <span>High Impact</span>
                            <strong>75 pts</strong>
                        </div>
                    </div>
                </div>

                <div class="guide-card">
                    <div class="guide-title">
                        <i class="fas fa-users"></i>
                        Team Rules
                    </div>
                    <ul class="mini-list">
                        <li>You are automatically included.</li>
                        <li>Medium/High Impact requires 3–5 members total.</li>
                        <li>Select 2–4 additional members.</li>
                        <li>Confirm your team before submitting.</li>
                    </ul>
                </div>

                <div class="guide-card">
                    <div class="guide-title">
                        <i class="fas fa-file-upload"></i>
                        Proof Rules
                    </div>
                    <ul class="mini-list">
                        <li>Images: 2–5 files.</li>
                        <li>PDF: exactly 1 file.</li>
                        <li>Do not mix PDF and images.</li>
                        <li>Max 15 MB per file.</li>
                    </ul>
                </div>

                <div class="guide-card">
                    <div class="guide-title">
                        <i class="fas fa-check-circle"></i>
                        Before Submit
                    </div>
                    <ul class="mini-list">
                        <li>Choose category and action.</li>
                        <li>Select one 3ZERO cluster.</li>
                        <li>Add Club ID if required.</li>
                        <li>Upload valid proof.</li>
                        <li>Write clear details.</li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
// DOM elements
        const categorySelect = document.getElementById('category');
        const actionSelect = document.getElementById('action');
        const teamMembersDiv = document.getElementById('team_members_div');
        const clubIdDiv = document.getElementById('club_id_div');
        const submitBtn = document.getElementById('submitBtn');
        
        // Team members elements
        const teamSearchInput = document.getElementById('team_search');
        const searchResultsDiv = document.getElementById('search_results');
        const selectedMembersContainer = document.getElementById('selected_members_container');
        const selectedMembersList = document.getElementById('selected_members_list');
        const selectedTeamInputsDiv = document.getElementById('selected_team_inputs');
        const teamErrorDiv = document.getElementById('team-error');
        const confirmTeamBtn = document.getElementById('confirm_team_btn');
        const editTeamBtn = document.getElementById('edit_team_btn');
        const confirmedTeamList = document.getElementById('confirmed_team_list');
        const confirmedTeamMembers = document.getElementById('confirmed_team_members');
        const additionalMembersList = document.getElementById('additional_members_list');
        const teamCounter = document.getElementById('team_counter');
        
        // File handling
        const fileInput = document.getElementById('proof_image');
        const fileList = document.getElementById('file_list');
        let filesArray = [];
        
        // Selected team members tracking
        let selectedTeamMembers = new Map(); // Using Map to store user objects
        let isTeamConfirmed = false;

        // 3ZERO Cluster elements
        const clusterOptions = document.querySelectorAll('.cluster-option');
        const clusterErrorDiv = document.getElementById('cluster-error');

        // User data from PHP
        const users = <?php echo json_encode($users); ?>;
        
        // Action arrays
        const lowImpactActions = [
            "L1-Use Reusable Items",
            "L2-Waste Sorting",
            "L3-Use Eco-Friendly or Biodegradable Products",
            "L4-Simple Repair or Upcycling",
            "L5-Report Waste Issue/Incidence",
            "L6-Participate in the Sustainability Action Programme on Campus",
            "L7-Participate in a Sustainability Action Programme Outside Campus"
        ];
        
        const mediumImpactActions = [
            "M1-Register in the 3ZERO Club",
            "M2-Group Volunteering (Outside Campus Event)",
            "M3-Sustainability Content Creation",
            "M4-Participate in Environmental/Sustainability Challenge/Competition Locally/Internationally",
            "M5-Participate in Mentorship and Leadership Programme related to the Environment"
        ];
        
        const highImpactActions = [
            "H1-Organize 3R (Reduce-Reuse-Recycle) Programme on Campus",
            "H2-Organize Paper and Plastic Reduction Programme on Campus",
            "H3-Organize Food Waste Sorting Programme on Campus",
            "H4-Organize an Energy-Saving Programme on Campus",
            "H5-Organize a Programme to Improve Socio-Economic Status on Campus and in the Community",
            "H6-Establish Social Business on Campus",
            "H7-Write AIU 3ZERO Club Profile/Activity Book",
            "H8-Securing Grants/Funding for Sustainability Action Projects",
            "H9-Develop and Copyright Sustainability Action Products/Solutions for Social Innovation",
            "H10-Participate and Win (Top 10) in Sustainability Action Challenge/Competition Locally/Internationally",
            "H11-Organize and Drive a Campus/Community Sustainability Action Event",
            "H12-Develop and Copyright Sustainability Action Resources",
            "H13-Completed the AIU 3ZERO Club and Social Business Incubation Programme"
        ];
        
        // Category change handler
        categorySelect.addEventListener('change', function() {
            let actions = [];
        
            // Hide all fields by default
            teamMembersDiv.style.display = 'none';
            clubIdDiv.style.display = 'none';
        
            // Reset selections
            resetTeamSelectionUI();
        
            switch (this.value) {
                case 'Low Impact':
                    actions = lowImpactActions;
                    break;
                case 'Medium Impact':
                    actions = mediumImpactActions;
                    teamMembersDiv.style.display = 'block';
                    clubIdDiv.style.display = 'block';
                    break;
                case 'High Impact':
                    actions = highImpactActions;
                    teamMembersDiv.style.display = 'block';
                    clubIdDiv.style.display = 'block';
                    break;
                default:
                    actions = [];
            }
        
            // Populate action dropdown
            actionSelect.innerHTML = '<option value="">Select an Action</option>';
            actions.forEach(function(action) {
                const option = document.createElement('option');
                option.value = action;
                option.textContent = action;
                actionSelect.appendChild(option);
            });
        });

        // 3ZERO Cluster functionality - now single selection
        clusterOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Deselect all first
                clusterOptions.forEach(opt => {
                    opt.classList.remove('selected');
                    opt.querySelector('input').checked = false;
                });
                
                // Select clicked one
                this.classList.add('selected');
                this.querySelector('input').checked = true;
                
                // Hide error
                clusterErrorDiv.style.display = 'none';
            });
        });

        // Google-like search functionality
        teamSearchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            // Clear previous results
            searchResultsDiv.innerHTML = '';
            
            if (searchTerm.length < 2) {
                searchResultsDiv.style.display = 'none';
                return;
            }
            
            // Filter users based on search term
            const filteredUsers = users.filter(user => {
                const nameMatch = user.name.toLowerCase().includes(searchTerm);
                const emailMatch = user.email.toLowerCase().includes(searchTerm);
                return nameMatch || emailMatch;
            });
            
            if (filteredUsers.length === 0) {
                searchResultsDiv.innerHTML = '<div class="search-result-item">No users found</div>';
            } else {
                filteredUsers.forEach(user => {
                    const isSelected = selectedTeamMembers.has(user.id.toString());
                    
                    const resultItem = document.createElement('div');
                    resultItem.className = `search-result-item ${isSelected ? 'selected' : ''}`;
                    resultItem.dataset.userId = user.id;
                    resultItem.dataset.userName = user.name;
                    resultItem.dataset.userEmail = user.email;
                    resultItem.innerHTML = `
                        <strong>${user.name}</strong> (${user.email})
                    `;
                    
                    resultItem.addEventListener('click', () => {
                        handleTeamMemberSelection(user);
                    });
                    
                    searchResultsDiv.appendChild(resultItem);
                });
            }
            
            searchResultsDiv.style.display = 'block';
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!teamSearchInput.contains(e.target) && !searchResultsDiv.contains(e.target)) {
                searchResultsDiv.style.display = 'none';
            }
        });

        // Handle team member selection
        function handleTeamMemberSelection(user) {
            if (isTeamConfirmed) return;
            
            const category = categorySelect.value;
            const maxAdditionalMembers = (category === 'Medium Impact' || category === 'High Impact') ? 4 : 0;
            
            if (selectedTeamMembers.has(user.id.toString())) {
                // Deselect
                selectedTeamMembers.delete(user.id.toString());
            } else {
                // Select only if we haven't reached the limit
                if (selectedTeamMembers.size < maxAdditionalMembers) {
                    selectedTeamMembers.set(user.id.toString(), user);
                }
            }
            
            updateSelectedTeamDisplay();
            updateSelectedTeamInputs();
            updateTeamCounter();
            
            // Hide error if selection is valid
            const minAdditionalMembers = (category === 'Medium Impact' || category === 'High Impact') ? 2 : 0;
            if (category === 'Low Impact' || 
                (category !== 'Low Impact' && selectedTeamMembers.size >= minAdditionalMembers && selectedTeamMembers.size <= maxAdditionalMembers)) {
                teamErrorDiv.style.display = 'none';
            }
            
            // Update search results display
            updateSearchResultsDisplay();
        }

        // Update selected team members display
        function updateSelectedTeamDisplay() {
            selectedMembersList.innerHTML = '';
            
            if (selectedTeamMembers.size > 0) {
                selectedMembersContainer.style.display = 'block';
                
                selectedTeamMembers.forEach((user, userId) => {
                    const memberDiv = document.createElement('div');
                    memberDiv.className = 'selected-member';
                    memberDiv.innerHTML = `
                        <div class="selected-member-info">
                            <i class="fas fa-user me-2"></i>
                            <span>${user.name} (${user.email})</span>
                        </div>
                        <button type="button" class="selected-member-remove" data-user-id="${userId}">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    
                    // Add remove event listener
                    const removeBtn = memberDiv.querySelector('.selected-member-remove');
                    removeBtn.addEventListener('click', () => {
                        handleTeamMemberSelection(user);
                    });
                    
                    selectedMembersList.appendChild(memberDiv);
                });
            } else {
                selectedMembersContainer.style.display = 'none';
            }
        }

        // Update search results display to show selected status
        function updateSearchResultsDisplay() {
            const searchResults = searchResultsDiv.querySelectorAll('.search-result-item');
            searchResults.forEach(item => {
                const userId = item.dataset.userId;
                if (selectedTeamMembers.has(userId)) {
                    item.classList.add('selected');
                    item.style.backgroundColor = '#2E8B57';
                    item.style.color = 'white';
                } else {
                    item.classList.remove('selected');
                    item.style.backgroundColor = '';
                    item.style.color = '';
                }
            });
        }

        // Update team counter display
        function updateTeamCounter() {
            const category = categorySelect.value;
            if (category === 'Medium Impact' || category === 'High Impact') {
                teamCounter.textContent = `Selected: 1 (you) + ${selectedTeamMembers.size} = ${1 + selectedTeamMembers.size}/5`;
            } else {
                teamCounter.textContent = `Selected: 1 (you)`;
            }
        }

        // Confirm team selection
        confirmTeamBtn.addEventListener('click', function() {
            const category = categorySelect.value;
            const minAdditionalMembers = (category === 'Medium Impact' || category === 'High Impact') ? 2 : 0;
            const maxAdditionalMembers = (category === 'Medium Impact' || category === 'High Impact') ? 4 : 0;
            
            if ((category === 'Medium Impact' || category === 'High Impact') && 
                (selectedTeamMembers.size < minAdditionalMembers || selectedTeamMembers.size > maxAdditionalMembers)) {
                teamErrorDiv.style.display = 'block';
                return;
            }
            
            // Show confirmed team list
            confirmedTeamList.style.display = 'block';
            additionalMembersList.innerHTML = '';
            
            // Add selected members to confirmed list
            selectedTeamMembers.forEach(user => {
                const div = document.createElement('div');
                div.className = 'confirmed-item';
                div.innerHTML = `
                    <div><i class="fas fa-user-check me-2"></i>${user.name} (${user.email})</div>
                `;
                additionalMembersList.appendChild(div);
            });
            
            // Update UI
            isTeamConfirmed = true;
            teamSearchInput.disabled = true;
            searchResultsDiv.style.display = 'none';
            selectedMembersContainer.style.display = 'none';
            confirmTeamBtn.style.display = 'none';
            editTeamBtn.style.display = 'inline-block';
        });

        // Edit team selection
        editTeamBtn.addEventListener('click', function() {
            isTeamConfirmed = false;
            teamSearchInput.disabled = false;
            selectedMembersContainer.style.display = selectedTeamMembers.size > 0 ? 'block' : 'none';
            confirmTeamBtn.style.display = 'inline-block';
            editTeamBtn.style.display = 'none';
            confirmedTeamList.style.display = 'none';
        });

        function updateSelectedTeamInputs() {
            selectedTeamInputsDiv.innerHTML = '';
            selectedTeamMembers.forEach((user, userId) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'team_members[]';
                input.value = userId;
                selectedTeamInputsDiv.appendChild(input);
            });
        }

        function resetTeamSelectionUI() {
            selectedTeamMembers.clear();
            teamErrorDiv.style.display = 'none';
            confirmedTeamList.style.display = 'none';
            selectedMembersContainer.style.display = 'none';
            isTeamConfirmed = false;
            teamSearchInput.disabled = false;
            teamSearchInput.value = '';
            searchResultsDiv.innerHTML = '';
            searchResultsDiv.style.display = 'none';
            confirmTeamBtn.style.display = 'inline-block';
            editTeamBtn.style.display = 'none';
            updateSelectedTeamInputs();
            updateTeamCounter();
        }

        // File handling functionality
        const fileDropzone = document.getElementById('fileDropzone');
        const fileUploadBtn = document.getElementById('fileUploadBtn');

        // Handle click on the dropzone
        fileDropzone.addEventListener('click', () => {
            fileInput.click();
        });

        // Handle drag and drop
        fileDropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileDropzone.classList.add('active');
        });

        fileDropzone.addEventListener('dragleave', () => {
            fileDropzone.classList.remove('active');
        });

        fileDropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            fileDropzone.classList.remove('active');
            
            if (e.dataTransfer.files.length) {
                const newFiles = Array.from(e.dataTransfer.files);
                newFiles.forEach(file => {
                    if (!filesArray.some(f => f.name === file.name && f.size === file.size)) {
                        filesArray.push(file);
                    }
                });
                updateFileList();
                updateInputFiles();
            }
        });

        fileInput.addEventListener('change', (event) => {
            const newFiles = Array.from(event.target.files);
            newFiles.forEach(file => {
                if (!filesArray.some(f => f.name === file.name && f.size === file.size)) {
                    filesArray.push(file);
                }
            });
            updateFileList();
            updateInputFiles();
        });

        function updateFileList() {
            fileList.innerHTML = '';
            filesArray.forEach((file, index) => {
                const li = document.createElement('li');
                li.className = 'file-list-item';
                
                const fileInfo = document.createElement('div');
                fileInfo.className = 'file-list-item-info';
                
                const fileIcon = document.createElement('i');
                fileIcon.className = 'file-list-item-icon';
                
                // Set icon based on file type
                if (file.type.startsWith('image/')) {
                    fileIcon.className += ' fas fa-image';
                } else if (file.type === 'application/pdf') {
                    fileIcon.className += ' fas fa-file-pdf';
                } else {
                    fileIcon.className += ' fas fa-file';
                }
                
                const fileName = document.createElement('span');
                fileName.className = 'file-list-item-name';
                fileName.textContent = file.name;
                
                const fileSize = document.createElement('span');
                fileSize.className = 'file-list-item-size';
                fileSize.textContent = `(${Math.round(file.size / 1024)} KB)`;
                
                fileInfo.appendChild(fileIcon);
                fileInfo.appendChild(fileName);
                fileInfo.appendChild(fileSize);
                
                const removeBtn = document.createElement('button');
                removeBtn.className = 'file-list-item-remove';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.addEventListener('click', () => {
                    filesArray.splice(index, 1);
                    updateFileList();
                    updateInputFiles();
                });
                
                li.appendChild(fileInfo);
                li.appendChild(removeBtn);
                fileList.appendChild(li);
            });
        }

        function updateInputFiles() {
            const dataTransfer = new DataTransfer();
            filesArray.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }
        
        // Form validation and confirmation
        submitBtn.addEventListener('click', function(event) {
            event.preventDefault();
            
            // Validate form first
            if (!validateForm()) {
                return;
            }
            
            // Show confirmation dialog
            if (confirm('Are you sure you want to submit this action? Please verify all details before proceeding.')) {
                document.getElementById('submissionForm').submit();
            }
        });
        
        function validateForm() {
            const category = categorySelect.value;
            const description = document.getElementById('description').value.trim();
            const action = document.getElementById('action').value;

            // Validate action
            if (!action) {
                alert('Please select an action.');
                return false;
            }
            
            // Validate 3ZERO cluster selection
            const selectedClusters = document.querySelectorAll('.cluster-option.selected');
            if (selectedClusters.length !== 1) {
                clusterErrorDiv.style.display = 'block';
                clusterErrorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            
            // Validate team members for Medium/High Impact
            const minAdditionalMembers = (category === 'Medium Impact' || category === 'High Impact') ? 2 : 0;
            const maxAdditionalMembers = (category === 'Medium Impact' || category === 'High Impact') ? 4 : 0;
            
            if ((category === 'Medium Impact' || category === 'High Impact') && 
                (selectedTeamMembers.size < minAdditionalMembers || selectedTeamMembers.size > maxAdditionalMembers || !isTeamConfirmed)) {
                teamErrorDiv.style.display = 'block';
                teamErrorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            
            // Validate file upload with PDF special rule
            const pdfFiles = filesArray.filter(file => file.type === 'application/pdf');
            const imageFiles = filesArray.filter(file => file.type.startsWith('image/'));
            
            if (pdfFiles.length > 0) {
                if (pdfFiles.length !== filesArray.length) {
                    alert('PDF files cannot be mixed with images. Please upload only PDFs or only images.');
                    return false;
                }
                if (pdfFiles.length !== 1) {
                    alert('Please upload exactly 1 PDF file.');
                    return false;
                }
            } else {
                if (filesArray.length < 2 || filesArray.length > 5) {
                    alert('Please upload between 2 and 5 images.');
                    return false;
                }
            }
            
            // Validate description
            if (description === '') {
                alert('Please fill in all required details in the description field.');
                document.getElementById('description').focus();
                return false;
            }
            
            return true;
        }
    // Add input validation for club_id to only allow numbers and dashes
    const clubIdInput = document.getElementById('club_id');
    
    if (clubIdInput) {
        clubIdInput.addEventListener('input', function(e) {
            // Remove any characters that are not numbers or dashes
            this.value = this.value.replace(/[^0-9\-]/g, '');
        });
    
        // Also add keydown event to prevent non-numeric/dash input
        clubIdInput.addEventListener('keydown', function(e) {
            // Allow: backspace, delete, tab, escape, enter
            if ([46, 8, 9, 27, 13, 110, 190].includes(e.keyCode) || 
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) || 
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Allow: numbers and numpad numbers
                (e.keyCode >= 48 && e.keyCode <= 57) ||
                (e.keyCode >= 96 && e.keyCode <= 105) ||
                // Allow: dash key (both regular and numpad)
                e.keyCode === 189 || e.keyCode === 109) {
                return;
            }
            // Prevent the rest
            e.preventDefault();
        });
    }
    </script>
</body>
</html>
