<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate submission ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid submission ID.";
    header('Location: dashboard.php');
    exit();
}
$submission_id = intval($_GET['id']);

// Fetch submission (ensure current user is owner)
$query = "SELECT * FROM submissions WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $submission_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Submission not found or access denied.";
    header('Location: dashboard.php');
    exit();
}
$submission = $result->fetch_assoc();

// Fetch all users with role = 'user' only, selecting email too
$users = [];
$user_query = $conn->query("SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name ASC");
if ($user_query) {
    while ($row = $user_query->fetch_assoc()) {
        $users[] = $row;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category']);
    $action = trim($_POST['action']);
    $description = trim($_POST['description']);
    $club_id = isset($_POST['club_id']) ? intval($_POST['club_id']) : null;
    
    $three_zero_cluster = isset($_POST['three_zero_cluster']) ? $_POST['three_zero_cluster'] : [];
    if (count($three_zero_cluster) < 1 || count($three_zero_cluster) > 3) {
        $_SESSION['error'] = "Please select at least one and at most three 3ZERO clusters.";
        header("Location: edit_submission.php?id=$submission_id");
        exit();
    }
    $three_zero_json = json_encode($three_zero_cluster);
    
    // Club ID is required for Medium/High Impact
    if (($category == 'Medium Impact' || $category == 'High Impact') && empty($club_id)) {
        $_SESSION['error'] = "Club ID is required for Medium and High Impact submissions.";
        header("Location: edit_submission.php?id=$submission_id");
        exit();
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

    if (($category == 'Medium Impact' || $category == 'High Impact') && $team_number < 5) {
        $_SESSION['error'] = "You must have at least 5 team members (including you) for Medium and High Impact.";
        header("Location: edit_submission.php?id=$submission_id");
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
            header("Location: edit_submission.php?id=$submission_id");
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

    // Handle file uploads and optional replacement
    $proof_files = json_decode($submission['proof_image'], true) ?? [];
    
    // Validate uploaded files
    $allowed_types = [
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/webp',
        'application/pdf'
    ];
    $allowed_extensions = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'pdf'];
    $max_file_size = 1048576; // 1 MB
    $delete_files = $_POST['delete_files'] ?? [];
    if (!empty($delete_files)) {
        $proof_files = array_values(array_filter($proof_files, function($file) use ($delete_files) {
            return !in_array($file, $delete_files, true);
        }));
    }
    
    if (!empty($_FILES['proof_image']['name'][0])) {
        $uploaded_files = $_FILES['proof_image'];
        $num_files = count(array_filter($uploaded_files['name']));
        $new_uploaded_files = [];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detected_types = [];
        for ($i = 0; $i < $num_files; $i++) {
            $detected_types[] = $finfo->file($uploaded_files['tmp_name'][$i]);
        }

        // Check PDF vs Image rules
        $pdf_files = array_filter($detected_types, function($type) {
            return $type === 'application/pdf';
        });
        
        // If PDFs are uploaded
        if (count($pdf_files) > 0) {
            if (count($pdf_files) !== $num_files) {
                $_SESSION['error'] = "PDF files cannot be mixed with images. Please upload only PDFs or only images.";
                header("Location: edit_submission.php?id=$submission_id");
                exit();
            }
            if (count($pdf_files) !== 1) {
                $_SESSION['error'] = "Please upload exactly 1 PDF file.";
                header("Location: edit_submission.php?id=$submission_id");
                exit();
            }
        } else {
            // Image validation
            if ($num_files < 2) {
                $_SESSION['error'] = "Please upload at least 2 images.";
                header("Location: edit_submission.php?id=$submission_id");
                exit();
            }
            if ($num_files > 5) {
                $_SESSION['error'] = "You can upload a maximum of 5 images.";
                header("Location: edit_submission.php?id=$submission_id");
                exit();
            }
        }

        $validated_uploads = [];
        for ($i = 0; $i < $num_files; $i++) {
            $file_type = $detected_types[$i];
            $file_size = $uploaded_files['size'][$i];
            $extension = strtolower(pathinfo($uploaded_files['name'][$i], PATHINFO_EXTENSION));
        
            // Check file type
            if (!in_array($file_type, $allowed_types, true) || !in_array($extension, $allowed_extensions, true)) {
                $_SESSION['error'] = "Only PNG, JPG, JPEG, GIF, WEBP, and PDF files are allowed.";
                header("Location: edit_submission.php?id=$submission_id");
                exit();
            }
        
            // Check file size
            if ($file_size > $max_file_size) {
                $_SESSION['error'] = "Each evidence file must be 1MB or below.";
                header("Location: edit_submission.php?id=$submission_id");
                exit();
            }

            $validated_uploads[] = [
                'tmp_name' => $uploaded_files['tmp_name'][$i],
                'original_name' => basename($uploaded_files['name'][$i]),
            ];
        }

        $target_dir = "uploads/";
        foreach ($validated_uploads as $upload) {
            $tmp_name = $upload['tmp_name'];
            $original_name = $upload['original_name'];
            $new_name = uniqid() . "-" . preg_replace("/[^a-zA-Z0-9.\-_]/", "", $original_name);
            $target_file = $target_dir . $new_name;
        
            if (move_uploaded_file($tmp_name, $target_file)) {
                $new_uploaded_files[] = $new_name;
            } else {
                foreach ($new_uploaded_files as $uploaded_name) {
                    $uploaded_path = $target_dir . basename($uploaded_name);
                    if (file_exists($uploaded_path)) {
                        @unlink($uploaded_path);
                    }
                }
                $_SESSION['error'] = "Error uploading file: " . htmlspecialchars($original_name);
                header("Location: edit_submission.php?id=$submission_id");
                exit();
            }
        }

        foreach ($proof_files as $old_file) {
            $old_path = "uploads/" . basename($old_file);
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }
        $proof_files = $new_uploaded_files;
    }

    foreach ($delete_files as $deleted_file) {
        $deleted_path = "uploads/" . basename($deleted_file);
        if (file_exists($deleted_path)) {
            @unlink($deleted_path);
        }
    }

    if (empty($proof_files)) {
        $_SESSION['error'] = "Please upload at least one file as proof.";
        header("Location: edit_submission.php?id=$submission_id");
        exit();
    }

    $proof_files_json = json_encode($proof_files, JSON_UNESCAPED_UNICODE);

    // Update query
    $update_query = "UPDATE submissions SET 
        category=?, 
        action=?, 
        points=?, 
        team_number=?, 
        team_members=?, 
        three_zero_cluster=?, 
        description=?, 
        proof_image=?,
        club_id=?,
        status='pending',
        verified_date=NULL,
        admin_remarks=NULL,
        superadmin_remarks=NULL
        WHERE id=? AND user_id=?";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param(
        "ssisssssiii",
        $category,
        $action,
        $points,
        $team_number,
        $team_members_json,
        $three_zero_json,
        $description,
        $proof_files_json,
        $club_id,
        $submission_id,
        $user_id
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Submission updated successfully and returned to Pending for admin review.";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update submission. Please try again.";
        header("Location: edit_submission.php?id=$submission_id");
        exit();
    }
}

// Action arrays
$lowImpactActions = [
    "L1-Use Reusable Items",
    "L2-Waste Sorting",
    "L3-Use Eco-Friendly or Biodegradable Products",
    "L4-Simple Repair or Upcycling",
    "L5-Report Waste Issue/Incidence",
    "L6-Participate in the Sustainability Action Programme on Campus",
    "L7-Participate in a Sustainability Action Programme Outside Campus"
];

$mediumImpactActions = [
    "M1-Register in the 3ZERO Club",
    "M2-Group Volunteering (Outside Campus Event)",
    "M3-Sustainability Content Creation",
    "M4-Participate in Environmental/Sustainability Challenge/Competition Locally/Internationally",
    "M5-Participate in Mentorship and Leadership Programme related to the Environment"
];

$highImpactActions = [
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
    "H12-Develop and Copyright Sustainability Action Resources"
];

// Get current values
$current_category = $submission['category'];
$current_action = $submission['action'];
$current_description = $submission['description'];
$current_club_id = $submission['club_id'];
$current_team_members = json_decode($submission['team_members'], true) ?? [];
$current_team_number = $submission['team_number'];
$current_three_zero_cluster = json_decode($submission['three_zero_cluster'], true) ?? [];
$current_proof_files = json_decode($submission['proof_image'], true) ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Edit Submission</title>
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
</style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <h2 class="text-center">Edit Submission #<?= htmlspecialchars($submission['id']) ?></h2>

        <div class="d-flex justify-content-center align-items-center mb-3">
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

        <form action="edit_submission.php?id=<?= $submission_id ?>" method="POST" enctype="multipart/form-data" id="submissionForm">
            <div class="mb-3">
                <label for="category" class="form-label">Select Category</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Select a Category</option>
                    <option value="Low Impact" <?= $current_category == 'Low Impact' ? 'selected' : '' ?>>Low Impact (25 points)</option>
                    <option value="Medium Impact" <?= $current_category == 'Medium Impact' ? 'selected' : '' ?>>Medium Impact (50 points)</option>
                    <option value="High Impact" <?= $current_category == 'High Impact' ? 'selected' : '' ?>>High Impact (75 points)</option>
                </select>
            </div>
            
            <div class="mb-3" id="club_id_div" style="<?= ($current_category == 'Medium Impact' || $current_category == 'High Impact') ? '' : 'display:none;' ?>">
                <label for="club_id" class="form-label">Club ID</label>
                <input type="number" class="form-control" id="club_id" name="club_id" min="1" 
                       placeholder="Enter Club ID" value="<?= htmlspecialchars($current_club_id) ?>">
            </div>
            
            <!-- 3ZERO Cluster section -->
            <div class="mb-3">
                <label class="form-label">3ZERO Cluster (Select 1-3)
                  <span style="color:red;">Click the edge of the box to select if you cannot click in the middle. Click once to select and another time to de-select.</span>
                </label>
                <div class="cluster-container">
                    <div class="cluster-option <?= in_array('Zero Poverty', $current_three_zero_cluster) ? 'selected' : '' ?>" data-value="Zero Poverty">
                        <i class="fas fa-hand-holding-heart"></i>
                        <input type="checkbox" name="three_zero_cluster[]" value="Zero Poverty" id="zero_poverty" <?= in_array('Zero Poverty', $current_three_zero_cluster) ? 'checked' : '' ?>>
                        <label for="zero_poverty">Zero Poverty</label>
                    </div>
                    <div class="cluster-option <?= in_array('Zero Unemployment', $current_three_zero_cluster) ? 'selected' : '' ?>" data-value="Zero Unemployment">
                        <i class="fas fa-briefcase"></i>
                        <input type="checkbox" name="three_zero_cluster[]" value="Zero Unemployment" id="zero_unemployment" <?= in_array('Zero Unemployment', $current_three_zero_cluster) ? 'checked' : '' ?>>
                        <label for="zero_unemployment">Zero Unemployment</label>
                    </div>
                    <div class="cluster-option <?= in_array('Zero Net Carbon Emission', $current_three_zero_cluster) ? 'selected' : '' ?>" data-value="Zero Net Carbon Emission">
                        <i class="fas fa-leaf"></i>
                        <input type="checkbox" name="three_zero_cluster[]" value="Zero Net Carbon Emission" id="zero_carbon" <?= in_array('Zero Net Carbon Emission', $current_three_zero_cluster) ? 'checked' : '' ?>>
                        <label for="zero_carbon">Zero Net Carbon Emission</label>
                    </div>
                </div>
                <div id="cluster-error" class="error-message">Please select 1-3 clusters</div>
            </div>

            <div class="mb-3">
                <label for="action" class="form-label">Type of Action</label>
                <select class="form-select" id="action" name="action" required>
                    <option value="">Select an Action</option>
                    <?php 
                    $actions = [];
                    switch($current_category) {
                        case 'Low Impact': $actions = $lowImpactActions; break;
                        case 'Medium Impact': $actions = $mediumImpactActions; break;
                        case 'High Impact': $actions = $highImpactActions; break;
                    }
                    
                    foreach ($actions as $action): ?>
                        <option value="<?= htmlspecialchars($action) ?>" <?= $current_action == $action ? 'selected' : '' ?>>
                            <?= htmlspecialchars($action) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Team Members selection -->
            <div class="mb-3" id="team_members_div" style="<?= ($current_category == 'Medium Impact' || $current_category == 'High Impact') ? '' : 'display:none;' ?>">
                <label class="form-label">Select Team Members (1st click - select, 2nd click - deselect)
                <strong>(You are automatically included. For Medium/High Impact, you need at least 4 additional members. Do not select yourself for the team member selection. Otherwise, there will be an error message.)</strong></label>

                <!-- Search Bar -->
                <input type="text" id="team_search" class="form-control mb-2" placeholder="Search for team members...">

                <!-- Team Members List with single-click selection -->
                <div class="team-members-list" id="team_members_list">
                    <?php foreach ($users as $user): 
                        if ($user['id'] == $user_id) continue; // Skip current user
                    ?>
                        <div class="team-member-item" 
                             data-user-id="<?= htmlspecialchars($user['id']) ?>" 
                             data-user-name="<?= htmlspecialchars($user['name']) ?>"
                             data-user-email="<?= htmlspecialchars($user['email']) ?>">
                            <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Hidden inputs for selected team members -->
                <div id="selected_team_inputs">
                    <?php 
                    // Get team member IDs from the submission (excluding current user)
                    $team_member_ids = [];
                    if (!empty($submission['team_members'])) {
                        $team_members = json_decode($submission['team_members'], true);
                        foreach ($users as $user) {
                            if ($user['id'] != $user_id && in_array($user['name'], $team_members)) {
                                $team_member_ids[] = $user['id'];
                            }
                        }
                    }
                    
                    foreach ($team_member_ids as $id): ?>
                        <input type="hidden" name="team_members[]" value="<?= $id ?>">
                    <?php endforeach; ?>
                </div>

                <div class="team-selection-controls">
                    <button type="button" id="confirm_team_btn" class="btn btn-secondary">Confirm Selection</button>
                    <button type="button" id="edit_team_btn" class="btn btn-outline-secondary" style="display:none;">Edit Selection</button>
                </div>
                
                <div id="confirmed_team_list" class="team-confirmed-list" style="<?= !empty($team_member_ids) ? '' : 'display:none;' ?>">
                    <h6>Confirmed Team Members:</h6>
                    <div id="confirmed_team_members">
                        <p>You (automatically included)</p>
                    </div>
                    <div id="additional_members_list">
                        <?php 
                        if (!empty($team_member_ids)) {
                            foreach ($users as $user) {
                                if (in_array($user['id'], $team_member_ids)) {
                                    echo "<div class='confirmed-item'>" . 
                                         htmlspecialchars($user['name']) . " (" . 
                                         htmlspecialchars($user['email']) . ")</div>";
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <div id="team-error" class="text-danger mt-2" style="display:none;">Please select at least 4 additional team members for Medium/High Impact</div>
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
                <small class="text-muted">Supported formats: PDF, PNG, JPG, JPEG, GIF, WEBP. Max 1MB per file.</small>
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
            accept="image/png,image/jpeg,image/jpg,image/gif,image/webp,application/pdf" 
            multiple 
        />
        
        <ul id="file_list" class="file-list">
            <?php foreach ($current_proof_files as $file): 
                $file_path = "uploads/" . $file;
                $file_size = file_exists($file_path) ? round(filesize($file_path) / 1024) : 0;
            ?>
                <li class="file-list-item">
                    <div class="file-list-item-info">
                        <i class="file-list-item-icon fas 
                            <?= strpos($file, '.pdf') !== false ? 'fa-file-pdf' : 'fa-image' ?>">
                        </i>
                        <span class="file-list-item-name">
                            <a href="<?= $file_path ?>" target="_blank"><?= htmlspecialchars($file) ?></a>
                        </span>
                        <span class="file-list-item-size">(<?= $file_size ?> KB)</span>
                    </div>
                    <button type="button" class="file-list-item-remove" data-file="<?= htmlspecialchars($file) ?>">
                        <i class="fas fa-times"></i>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="file-upload-note">
        <strong><i class="fas fa-info-circle me-2"></i>Note:</strong> 
        <ul class="mb-0 mt-2">
            <li>Submit 2-5 images for PNG, JPG, JPEG, GIF, or WEBP formats</li>
            <li>Submit 1 file for PDF format</li>
            <li>Maximum file size is 1MB per file</li>
            <li>Files are securely transferred with TLS encryption</li>
        </ul>
    </div>

            <div class="mb-3">
                <label for="description" class="form-label">Additional Description (Optional)</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($current_description) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100">Update Submission</button>
            <a href="dashboard.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
        </form>
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
        
        // Team members elements
        const teamMembersList = document.getElementById('team_members_list');
        const teamSearchInput = document.getElementById('team_search');
        const selectedTeamInputsDiv = document.getElementById('selected_team_inputs');
        const teamErrorDiv = document.getElementById('team-error');
        const confirmTeamBtn = document.getElementById('confirm_team_btn');
        const editTeamBtn = document.getElementById('edit_team_btn');
        const confirmedTeamList = document.getElementById('confirmed_team_list');
        const confirmedTeamMembers = document.getElementById('confirmed_team_members');
        const additionalMembersList = document.getElementById('additional_members_list');
        
        // File handling
        const fileInput = document.getElementById('proof_image');
        const fileList = document.getElementById('file_list');
        let filesArray = [];
        const allowedEvidenceTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp', 'application/pdf'];
        const maxEvidenceSize = 1048576;
        
        // Selected team members tracking
        let selectedTeamMembers = new Set();
        // Initialize with existing team members
        document.querySelectorAll('#selected_team_inputs input[name="team_members[]"]').forEach(input => {
            selectedTeamMembers.add(input.value);
        });
        let isTeamConfirmed = document.getElementById('confirmed_team_list').style.display !== 'none';

        // 3ZERO Cluster elements
        const clusterOptions = document.querySelectorAll('.cluster-option');
        const clusterErrorDiv = document.getElementById('cluster-error');

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
            "H12-Develop and Copyright Sustainability Action Resources"
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

        // 3ZERO Cluster functionality
        clusterOptions.forEach(option => {
            option.addEventListener('click', function() {
                const checkbox = this.querySelector('input');
                const selectedCount = document.querySelectorAll('.cluster-option.selected').length;
                
                if (this.classList.contains('selected')) {
                    // Deselect
                    this.classList.remove('selected');
                    checkbox.checked = false;
                } else {
                    // Select if less than 3 are selected
                    if (selectedCount < 3) {
                        this.classList.add('selected');
                        checkbox.checked = true;
                    }
                }
                
                // Hide error if at least one is selected
                if (document.querySelectorAll('.cluster-option.selected').length > 0) {
                    clusterErrorDiv.style.display = 'none';
                }
            });
        });

        // Team Members functionality
        teamSearchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const memberItems = teamMembersList.querySelectorAll('.team-member-item');
            
            memberItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(filter) ? 'block' : 'none';
            });
        });

        // Initialize team member selection UI
        function initializeTeamSelection() {
            const memberItems = teamMembersList.querySelectorAll('.team-member-item');
            memberItems.forEach(item => {
                const userId = item.dataset.userId;
                if (selectedTeamMembers.has(userId)) {
                    item.classList.add('selected');
                }
            });
            
            if (isTeamConfirmed) {
                teamMembersList.style.pointerEvents = 'none';
                teamSearchInput.disabled = true;
                confirmTeamBtn.style.display = 'none';
                editTeamBtn.style.display = 'inline-block';
            }
        }
        
        // Call initialization
        initializeTeamSelection();

        // Single-click to select/deselect team members
        teamMembersList.addEventListener('click', function(e) {
            if (isTeamConfirmed) return;
            
            const memberItem = e.target.closest('.team-member-item');
            if (!memberItem) return;
            
            const userId = memberItem.dataset.userId;
            const userName = memberItem.dataset.userName;
            const userEmail = memberItem.dataset.userEmail;
            
            if (selectedTeamMembers.has(userId)) {
                // Deselect
                selectedTeamMembers.delete(userId);
                memberItem.classList.remove('selected');
            } else {
                // Select
                selectedTeamMembers.add(userId);
                memberItem.classList.add('selected');
            }
            
            updateSelectedTeamInputs();
            
            // Hide error if selection is valid
            const category = categorySelect.value;
            if (category === 'Low Impact' || 
                (category !== 'Low Impact' && selectedTeamMembers.size >= 4)) {
                teamErrorDiv.style.display = 'none';
            }
        });

        // Confirm team selection
        confirmTeamBtn.addEventListener('click', function() {
            const category = categorySelect.value;
            
            if ((category === 'Medium Impact' || category === 'High Impact') && selectedTeamMembers.size < 4) {
                teamErrorDiv.style.display = 'block';
                return;
            }
            
            // Show confirmed team list
            confirmedTeamList.style.display = 'block';
            additionalMembersList.innerHTML = '';
            
            // Add selected members to confirmed list
            teamMembersList.querySelectorAll('.team-member-item.selected').forEach(item => {
                const div = document.createElement('div');
                div.className = 'confirmed-item';
                div.textContent = item.textContent;
                additionalMembersList.appendChild(div);
            });
            
            // Update UI
            isTeamConfirmed = true;
            teamMembersList.style.pointerEvents = 'none';
            teamSearchInput.disabled = true;
            confirmTeamBtn.style.display = 'none';
            editTeamBtn.style.display = 'inline-block';
        });

        // Edit team selection
        editTeamBtn.addEventListener('click', function() {
            isTeamConfirmed = false;
            teamMembersList.style.pointerEvents = 'auto';
            teamSearchInput.disabled = false;
            confirmTeamBtn.style.display = 'inline-block';
            editTeamBtn.style.display = 'none';
            confirmedTeamList.style.display = 'none';
        });

        function updateSelectedTeamInputs() {
            selectedTeamInputsDiv.innerHTML = '';
            selectedTeamMembers.forEach(userId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'team_members[]';
                input.value = userId;
                selectedTeamInputsDiv.appendChild(input);
            });
        }

        function resetTeamSelectionUI() {
            selectedTeamMembers.clear();
            teamMembersList.querySelectorAll('.team-member-item').forEach(item => {
                item.classList.remove('selected');
            });
            teamErrorDiv.style.display = 'none';
            confirmedTeamList.style.display = 'none';
            isTeamConfirmed = false;
            teamMembersList.style.pointerEvents = 'auto';
            teamSearchInput.disabled = false;
            confirmTeamBtn.style.display = 'inline-block';
            editTeamBtn.style.display = 'none';
            updateSelectedTeamInputs();
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
                    if (!allowedEvidenceTypes.includes(file.type)) {
                        alert('Only PNG, JPG, JPEG, GIF, WEBP, and PDF files are allowed.');
                        return;
                    }
                    if (file.size > maxEvidenceSize) {
                        alert('Each evidence file must be 1MB or below.');
                        return;
                    }
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
                if (!allowedEvidenceTypes.includes(file.type)) {
                    alert('Only PNG, JPG, JPEG, GIF, WEBP, and PDF files are allowed.');
                    return;
                }
                if (file.size > maxEvidenceSize) {
                    alert('Each evidence file must be 1MB or below.');
                    return;
                }
                if (!filesArray.some(f => f.name === file.name && f.size === file.size)) {
                    filesArray.push(file);
                }
            });
            updateFileList();
            updateInputFiles();
        });

        // Handle removal of existing files
        document.querySelectorAll('.file-list-item-remove').forEach(btn => {
            btn.addEventListener('click', function() {
                const fileName = this.dataset.file;
                const listItem = this.closest('.file-list-item');
                
                // Create hidden input to mark file for deletion
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete_files[]';
                deleteInput.value = fileName;
                document.getElementById('submissionForm').appendChild(deleteInput);
                
                // Remove from UI
                listItem.remove();
            });
        });

        function updateFileList() {
            fileList.innerHTML = '';
            
            // Add existing files
            document.querySelectorAll('.file-list-item').forEach(item => {
                fileList.appendChild(item);
            });
            
            // Add new files
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
        
        // Form validation
        document.getElementById('submissionForm').addEventListener('submit', function(event) {
            const category = categorySelect.value;
            
            // Validate 3ZERO cluster selection
            const selectedClusters = document.querySelectorAll('.cluster-option.selected');
            if (selectedClusters.length < 1 || selectedClusters.length > 3) {
                clusterErrorDiv.style.display = 'block';
                event.preventDefault();
                return;
            }
            
            // Validate team members for Medium/High Impact
            if ((category === 'Medium Impact' || category === 'High Impact') && 
                (selectedTeamMembers.size < 4 || !isTeamConfirmed)) {
                teamErrorDiv.style.display = 'block';
                event.preventDefault();
                return;
            }
            
            // Get all files (existing and new)
            const allFiles = Array.from(document.querySelectorAll('.file-list-item-name')).map(el => {
                const link = el.querySelector('a');
                return link ? link.textContent : el.textContent.trim();
            });
            
            // Check if we have any files (existing or new)
            if (allFiles.length === 0) {
                alert('Please upload at least one file as proof.');
                event.preventDefault();
                return;
            }

            for (const file of filesArray) {
                if (!allowedEvidenceTypes.includes(file.type)) {
                    alert('Only PNG, JPG, JPEG, GIF, WEBP, and PDF files are allowed.');
                    event.preventDefault();
                    return;
                }
                if (file.size > maxEvidenceSize) {
                    alert('Each evidence file must be 1MB or below.');
                    event.preventDefault();
                    return;
                }
            }
            
            // Check PDF vs image rules
            const pdfFiles = allFiles.filter(name => name.toLowerCase().endsWith('.pdf'));
            if (pdfFiles.length > 0) {
                if (pdfFiles.length !== allFiles.length) {
                    alert('PDF files cannot be mixed with images. Please upload only PDFs or only images.');
                    event.preventDefault();
                    return;
                }
                if (pdfFiles.length !== 1) {
                    alert('Please upload exactly 1 PDF file.');
                    event.preventDefault();
                    return;
                }
            } else {
                if (allFiles.length < 2 || allFiles.length > 5) {
                    alert('Please upload between 2 and 5 images.');
                    event.preventDefault();
                    return;
                }
            }
        });
    </script>
</body>
</html>
