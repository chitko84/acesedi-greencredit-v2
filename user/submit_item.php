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
        $_SESSION['error'] = "Your File Size is exceeding maximum limit of 15MB.";
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
                            <li><strong>Points:</strong> $points</li>
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
    <title>GreenCredit - Submit Item</title>
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    :root {
        --primary-color: #2E8B57;
        --primary-light: #E8F5E9;
        --secondary-color: #FFC107;
        --dark-color: #343A40;
        --light-color: #F8F9FA;
        --danger-color: #DC3545;
    }
    
    body {
        background-color: #f5f5f5;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--dark-color);
    }
    
    .container {
        max-width: 800px;
        margin-top: 30px;
        margin-bottom: 50px;
    }
    
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }
    
    .card-header {
        background-color: var(--primary-color);
        color: white;
        padding: 20px;
        border-bottom: none;
    }
    
    .card-body {
        padding: 30px;
    }
    
    .form-label {
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--dark-color);
    }
    
    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 6px;
        padding: 10px 15px;
        transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(46, 139, 87, 0.25);
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn-primary:hover {
        background-color: #247a4a;
        transform: translateY(-2px);
    }
    
    .btn-secondary {
        background-color: #6c757d;
        border: none;
    }
    
    .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
    }
    
    /* Checkbox group styling */
    .checkbox-group {
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 15px;
        background-color: var(--light-color);
    }
    
    /* Team members styling */
    .team-member-item {
        padding: 10px 15px;
        margin: 5px 0;
        border: 1px solid #ddd;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }
    
    .team-member-item:hover {
        background-color: #e9ecef;
    }
    
    .team-member-item.selected {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .team-member-item i {
        margin-right: 10px;
    }
    
    .team-members-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 10px;
        background-color: white;
    }
    
    .confirmed-item {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        border-radius: 6px;
        padding: 10px;
        margin: 5px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    /* 3ZERO Cluster styling */
    .cluster-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    
    .cluster-option {
        padding: 15px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        flex: 1;
        min-width: 120px;
        text-align: center;
        background-color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .cluster-option:hover {
        border-color: var(--primary-color);
    }
    
    .cluster-option.selected {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        box-shadow: 0 4px 8px rgba(46, 139, 87, 0.2);
    }
    
    .cluster-option i {
        font-size: 24px;
        margin-bottom: 8px;
    }
    
    .cluster-option input {
        display: none;
    }
    
    /* File upload styling */
    .file-upload-container {
        margin-bottom: 20px;
    }
    
    .file-upload-dropzone {
        border: 2px dashed #ced4da;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        background-color: white;
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: 15px;
    }
    
    .file-upload-dropzone:hover {
        border-color: var(--primary-color);
        background-color: rgba(46, 139, 87, 0.05);
    }
    
    .file-upload-dropzone.active {
        border-color: var(--primary-color);
        background-color: rgba(46, 139, 87, 0.1);
    }
    
    .file-upload-icon {
        font-size: 48px;
        color: var(--primary-color);
        margin-bottom: 15px;
    }
    
    .file-upload-text {
        font-size: 16px;
        margin-bottom: 10px;
    }
    
    .file-upload-btn {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .file-upload-btn:hover {
        background-color: #247a4a;
    }
    
    .file-upload-note {
        background-color: #f8f9fa;
        border-left: 4px solid var(--primary-color);
        padding: 15px;
        margin-bottom: 20px;
        font-size: 0.9rem;
        border-radius: 0 6px 6px 0;
    }
    
    .file-list {
        list-style: none;
        padding: 0;
        margin-top: 15px;
    }
    
    .file-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        background-color: white;
        border: 1px solid #eee;
        border-radius: 6px;
        margin-bottom: 8px;
    }
    
    .file-list-item-info {
        display: flex;
        align-items: center;
    }
    
    .file-list-item-icon {
        color: var(--primary-color);
        margin-right: 10px;
        font-size: 20px;
    }
    
    .file-list-item-name {
        font-weight: 500;
    }
    
    .file-list-item-size {
        color: #6c757d;
        font-size: 0.85rem;
        margin-left: 10px;
    }
    
    .file-list-item-remove {
        color: var(--danger-color);
        cursor: pointer;
        background: none;
        border: none;
        font-size: 18px;
    }
    
    /* Team selection controls */
    .team-selection-controls {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .team-confirmed-list {
        margin-top: 20px;
        padding: 15px;
        background-color: var(--light-color);
        border-radius: 8px;
    }
    
    /* Error messages */
    .error-message {
        color: var(--danger-color);
        font-size: 0.85rem;
        margin-top: 5px;
        display: none;
    }
    
    /* Points system button */
    .points-system-btn {
        display: inline-flex;
        align-items: center;
        background-color: white;
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        padding: 12px 20px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        margin-bottom: 20px;
    }
    
    .points-system-btn:hover {
        background-color: var(--primary-light);
        transform: translateY(-2px);
        color: var(--primary-color);
    }
    
    .points-system-btn i {
        margin-right: 8px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-body {
            padding: 20px;
        }
        
        .cluster-option {
            min-width: 100%;
        }
    }
    
    .file-list-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    background-color: white;
    border: 1px solid #eee;
    border-radius: 6px;
    margin-bottom: 8px;
}

.file-list-item-info {
    display: flex;
    align-items: center;
    flex-grow: 1;
}

.file-list-item-icon {
    color: var(--primary-color);
    margin-right: 10px;
    font-size: 20px;
    width: 24px;
    text-align: center;
}

.file-list-item-name {
    font-weight: 500;
    margin-right: 8px;
    word-break: break-all;
}

.file-list-item-size {
    color: #6c757d;
    font-size: 0.85rem;
    white-space: nowrap;
}

.file-list-item-remove {
    color: var(--danger-color);
    cursor: pointer;
    background: none;
    border: none;
    font-size: 18px;
    margin-left: 10px;
    padding: 0 5px;
}

.file-list-item-remove:hover {
    color: #a71d2a;
}

    .cluster-option {
        padding: 15px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        flex: 1;
        min-width: 120px;
        text-align: center;
        background-color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .cluster-option.selected {
        background-color: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
        box-shadow: 0 4px 8px rgba(46, 139, 87, 0.2);
    }
    
    /* Team member counter */
    .team-member-counter {
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    /* Description required indicator */
    .required-field::after {
        content: " *";
        color: red;
    }

    /* Search box styling */
    .search-container {
        position: relative;
        margin-bottom: 10px;
    }
    
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ced4da;
        border-top: none;
        border-radius: 0 0 6px 6px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 100;
        display: none;
    }
    
    .search-result-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    
    .search-result-item:hover {
        background-color: #f8f9fa;
    }
    
    .search-result-item:last-child {
        border-bottom: none;
    }
    
    .selected-members-container {
        margin-top: 15px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        padding: 15px;
        background-color: #f8f9fa;
    }
    
    .selected-member {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 6px;
        margin-bottom: 8px;
    }
    
    .selected-member-info {
        display: flex;
        align-items: center;
    }
    
    .selected-member-remove {
        color: var(--danger-color);
        cursor: pointer;
        background: none;
        border: none;
        font-size: 16px;
        padding: 0 5px;
    }
    
    .button-59 {
        align-items: center;
        background-color: #fff;
        border: 2px solid #000;
        box-sizing: border-box;
        color: #000;
        cursor: pointer;
        display: inline-flex;
        fill: #000;
        font-family: Inter,sans-serif;
        font-size: 16px;
        font-weight: 600;
        height: 48px;
        justify-content: center;
        letter-spacing: -.8px;
        line-height: 24px;
        min-width: 140px;
        outline: 0;
        padding: 0 17px;
        text-align: center;
        text-decoration: none;
        transition: all .3s;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
    }

    .button-59:focus {
        color: #171e29;
    }

    .button-59:hover {
        border-color: #06f;
        color: #06f;
        fill: #06f;
    }

    .button-59:active {
        border-color: #06f;
        color: #06f;
        fill: #06f;
    }

    @media (min-width: 768px) {
        .button-59 {
            min-width: 170px;
        }
    }
    .submission-shell {
        max-width: 980px;
        background: #ffffff;
        border: 1px solid rgba(46, 139, 87, 0.14);
        border-radius: 18px;
        box-shadow: 0 18px 50px rgba(31, 51, 41, 0.12);
        padding: clamp(1.25rem, 3vw, 2.25rem);
    }

    body {
        background:
            linear-gradient(180deg, #f5faf6 0%, #eef7f1 52%, #f9fbfa 100%);
    }

    .submit-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: center;
        background: linear-gradient(135deg, #1f7a49, #2E8B57);
        color: #fff;
        border-radius: 16px;
        padding: clamp(1.1rem, 3vw, 1.7rem);
        margin-bottom: 1.25rem;
        box-shadow: 0 16px 34px rgba(46,139,87,.24);
    }

    .submit-hero h2 {
        color: #fff !important;
        margin: 0 0 .35rem !important;
        text-align: left !important;
    }

    .submit-hero p {
        margin: 0;
        color: rgba(255,255,255,.88);
    }

    .submit-hero .button-59 {
        background: #fff;
        color: #1f7a49;
        border: 0;
        box-shadow: 0 10px 22px rgba(0,0,0,.16);
    }

    .submit-hero .button-59 a {
        color: #1f7a49 !important;
        font-weight: 800;
    }

    .submission-shell h2 {
        color: #183b28;
        font-weight: 800;
        margin-bottom: 1rem;
    }

    .submission-shell form {
        display: grid;
        gap: 1rem;
    }

    .submission-shell form > .mb-3 {
        background: #ffffff;
        border: 1px solid #e3eee6;
        border-radius: 14px;
        padding: 1rem;
        box-shadow: 0 8px 20px rgba(31, 51, 41, 0.05);
    }

    .submission-shell .mb-3 {
        margin-bottom: 0 !important;
    }

    .submission-shell .form-label {
        color: #22352b;
        font-size: 0.96rem;
    }

    .submission-shell .form-control,
    .submission-shell .form-select {
        border-color: #dce7df;
        border-radius: 10px;
        min-height: 46px;
        background: #fbfdfb;
    }

    .submission-shell .form-control:focus,
    .submission-shell .form-select:focus {
        border-color: #2E8B57;
        box-shadow: 0 0 0 0.22rem rgba(46, 139, 87, 0.16);
    }

    .submission-shell .btn-primary,
    .submission-shell .file-upload-btn {
        border-radius: 10px;
        box-shadow: 0 10px 22px rgba(46, 139, 87, 0.18);
    }

    .submission-shell .cluster-container {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .submission-shell .cluster-option {
        border-radius: 14px;
        min-height: 116px;
    }

    .submission-shell .file-upload-dropzone {
        border-radius: 16px;
        background: #f8fbf8;
    }

    .submission-shell .selected-member,
    .submission-shell .confirmed-item,
    .submission-shell .team-member-counter,
    .submission-shell .file-upload-note {
        border-radius: 12px;
    }

    body.dark-mode {
        background:
            radial-gradient(circle at top left, rgba(46, 139, 87, 0.16), transparent 34%),
            #0f1411 !important;
        color: #edf6ef;
    }

    body.dark-mode .submission-shell {
        background: #171d19 !important;
        color: #edf6ef !important;
        border-color: rgba(190, 233, 205, 0.14) !important;
        box-shadow: 0 18px 50px rgba(0, 0, 0, 0.42);
    }

    body.dark-mode .submission-shell h2,
    body.dark-mode .submission-shell .form-label,
    body.dark-mode .submission-shell label,
    body.dark-mode .submission-shell .file-upload-text,
    body.dark-mode .submission-shell .file-list-item-name,
    body.dark-mode .submission-shell .team-member-counter {
        color: #edf6ef !important;
    }

    body.dark-mode .submission-shell form > .mb-3,
    body.dark-mode .checkbox-group,
    body.dark-mode .team-members-container,
    body.dark-mode .team-confirmed-list,
    body.dark-mode .selected-members-container,
    body.dark-mode .selected-member,
    body.dark-mode .confirmed-item,
    body.dark-mode .file-list-item,
    body.dark-mode .cluster-option,
    body.dark-mode .file-upload-dropzone,
    body.dark-mode .file-upload-note,
    body.dark-mode .search-results {
        background: #1d2620 !important;
        color: #edf6ef !important;
        border-color: rgba(190, 233, 205, 0.14) !important;
        box-shadow: none;
    }

    body.dark-mode .submission-shell .form-control,
    body.dark-mode .submission-shell .form-select,
    body.dark-mode .submission-shell textarea {
        background: #101713 !important;
        color: #edf6ef !important;
        border-color: rgba(190, 233, 205, 0.18) !important;
    }

    body.dark-mode .submission-shell .form-control::placeholder,
    body.dark-mode .submission-shell .file-list-item-size,
    body.dark-mode .submission-shell .file-upload-note,
    body.dark-mode .search-result-item {
        color: #a9b8ad !important;
    }

    body.dark-mode .team-member-item,
    body.dark-mode .search-result-item {
        background: #171d19 !important;
        color: #edf6ef !important;
        border-color: rgba(190, 233, 205, 0.12) !important;
    }

    body.dark-mode .team-member-item:hover,
    body.dark-mode .search-result-item:hover,
    body.dark-mode .cluster-option:hover,
    body.dark-mode .file-upload-dropzone:hover {
        background: rgba(120, 217, 154, 0.13) !important;
        border-color: rgba(120, 217, 154, 0.45) !important;
    }

    body.dark-mode .team-member-item.selected,
    body.dark-mode .cluster-option.selected {
        background: #2e8b57 !important;
        color: #ffffff !important;
        border-color: #78d99a !important;
    }

    body.dark-mode .submit-hero {
        background: linear-gradient(135deg, #155c36, #1f7a49);
        box-shadow: 0 16px 34px rgba(0, 0, 0, 0.36);
    }

    body.dark-mode .submit-hero .button-59 {
        background: #edf6ef;
        color: #155c36;
    }

    @media (max-width: 768px) {
        body {
            background: #f7faf7;
        }

        body.dark-mode {
            background: #0f1411 !important;
        }

        .submission-shell {
            width: calc(100% - 1rem);
            margin-top: 1rem !important;
            padding: 1rem;
            border-radius: 14px;
        }

        .submit-hero {
            grid-template-columns: 1fr;
            border-radius: 14px;
        }

        .submit-hero .button-59 {
            width: 100%;
        }

        .submission-shell h2 {
            font-size: 1.45rem;
            text-align: left !important;
        }

        .submission-shell .cluster-container {
            grid-template-columns: 1fr;
        }

        .submission-shell .file-upload-dropzone {
            padding: 1.25rem 0.85rem;
        }

        .submission-shell .selected-member,
        .submission-shell .confirmed-item {
            align-items: flex-start;
            gap: 0.75rem;
        }

        .submission-shell textarea {
            min-height: 180px;
        }
    }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5 submission-shell">
        <div class="submit-hero">
            <div>
                <h2 class="text-center">Submit Eco-Friendly Action</h2>
                <p>Choose your impact level, add the right evidence, and submit for review.</p>
            </div>
            <button class="button-59">
                <a href="points.php" style="text-decoration: none; color:rgb(210, 46, 46)">
                    *Click here to see our Points System*
                </a>
            </button>
        </div>

        <?php 
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
            unset($_SESSION['success']);
        }
        ?>

        <form action="submit_item.php" method="POST" enctype="multipart/form-data" id="submissionForm">
            <div class="mb-3">
                <label for="category" class="form-label">Select Category</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Select a Category</option>
                    <option value="Low Impact">Low Impact (25 points)</option>
                    <option value="Medium Impact">Medium Impact (50 points)</option>
                    <option value="High Impact">High Impact (75 points)</option>
                </select>
            </div>
            
            <div class="mb-3" id="club_id_div" style="display:none;">
                <label for="club_id" class="form-label">Club ID</label>
                <!-- Changed from type="number" to type="text" -->
                <input type="text" class="form-control" id="club_id" name="club_id" 
                       pattern="[0-9\-]+" title="Only numbers and dashes are allowed"
                       placeholder="Enter Club ID (numbers and dashes only)">
                <small class="text-muted">Only numbers and dashes are allowed     (Example format: 123-456-789) </small>
            </div>

            <div class="mb-3">
                <label for="action" class="form-label">Type of Action</label>
                <select class="form-select" id="action" name="action" required>
                    <option value="">Select an Action</option>
                </select>
            </div>
            
                        <!-- 3ZERO Cluster section -->
            <div class="mb-3">
                <label class="form-label">3ZERO Cluster (Select 1)
                  <span style="color:red;">Click the edge of the box to select if you cannot click in the middle. Click once to select and another time to de-select.</span>
                </label>
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
                <div id="cluster-error" class="error-message">Please select exactly one cluster</div>
            </div>

            <!-- Team Members selection -->
            <div class="mb-3" id="team_members_div" style="display:none;">
                <label class="form-label">Select Team Members (1st click - select, 2nd click - deselect)
                <strong>(You are automatically included. Do not select your own email. For Medium/High Impact, you need 2-4 additional members.)</strong></label>
                
                <div class="team-member-counter" id="team_counter">Selected: 1 (you) + 0 = 1/5</div>

                <!-- Search Bar with Google-like functionality -->
                <div class="search-container">
                    <input type="text" id="team_search" class="form-control mb-2" placeholder="Start typing to search for team members...">
                    <div class="search-results" id="search_results"></div>
                </div>

                <!-- Selected members display -->
                <div class="selected-members-container" id="selected_members_container" style="display: none;">
                    <h6>Selected Team Members:</h6>
                    <div id="selected_members_list"></div>
                </div>

                <!-- Hidden inputs for selected team members -->
                <div id="selected_team_inputs"></div>

                <div class="team-selection-controls">
                    <button type="button" id="confirm_team_btn" class="btn btn-secondary">Confirm Selection</button>
                    <button type="button" id="edit_team_btn" class="btn btn-outline-secondary" style="display:none;">Edit Selection</button>
                </div>
                
                <div id="confirmed_team_list" class="team-confirmed-list" style="display:none;">
                    <h6>Confirmed Team Members:</h6>
                    <div id="confirmed_team_members">
                        <p>You (automatically included)</p>
                    </div>
                    <div id="additional_members_list"></div>
                </div>
                
                <div id="team-error" class="text-danger mt-2" style="display:none;">Please select 2-4 additional team members for Medium/High Impact</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload Proof</label>
                
                <div class="file-upload-container">
                    <div id="fileDropzone" class="file-upload-dropzone">
                        <div class="file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="file-upload-text">
                            <strong>Drop your files here</strong> or click to browse
                        </div>
                        <div class="mb-2">
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
                    <strong><i class="fas fa-info-circle me-2"></i>Note:</strong> 
                    <ul class="mb-0 mt-2">
                        <li>You can submit 2-5 images for PNG, JPG, JPEG, GIF formats</li>
                        <li>You can only submit 1 file for PDF format</li>
                        <li>Maximum file size is 15 MB per file</li>
                        <li>Files are securely transferred with TLS encryption</li>
                    </ul>
                </div>
            </div>

            <div class="mb-3">
    <label for="description" class="form-label required-field">Additional Details</label>
    
    <div class="alert alert-light border p-2 mb-2" style="font-size: 0.9rem;">
        Please provide the following details:<br>
        (Date, Time, Venue, etc...)
    </div>

    <textarea class="form-control" id="description" name="description" rows="8" required></textarea>
    <small class="text-muted">All fields above are required. Please fill in complete details.</small>
</div>

            <button type="submit" class="btn btn-primary w-100" id="submitBtn">Submit Item</button>
        </form>
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

        // Handle click on the dropzone or button
        fileDropzone.addEventListener('click', (e) => {
            // Prevent triggering when clicking on child elements
            if (e.target === fileDropzone || e.target === fileUploadBtn) {
                fileInput.click();
            }
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
