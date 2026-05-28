<?php
session_start();
include '../includes/db.php';

// Redirect if admin is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Fetch admin data from the database (ONLY the logged-in admin)
$query = "SELECT * FROM users WHERE id = ?";
$stmt  = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_row = $user_result->fetch_assoc();
} else {
    $_SESSION['error'] = "Admin data not found!";
    header('Location: dashboard.php');
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Regular profile update
    if (isset($_POST['update_profile'])) {
        $name  = $_POST['name'];
        $email = $_POST['email'];

        $date_of_birth = $_POST['date_of_birth'];
        $phone_number  = $_POST['phone_number'];
        $program_of_study = $_POST['program_of_study'];
        $intake            = $_POST['intake'];
        $country           = $_POST['country'];
        $gender            = $_POST['gender'];
        $department        = $_POST['department'];
        $expected_graduation_year = $_POST['expected_graduation_year'];

        // Profile pic handling
        $profile_pic_current = $user_row['profile_pic'];
        $profile_pic_to_store = $profile_pic_current;

        if (!empty($_POST['profile_pic_data'])) {
            $imageData = $_POST['profile_pic_data'];

            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // jpg|jpeg|png|gif

                if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $_SESSION['error'] = "Invalid image type";
                    header('Location: admin_profile.php');
                    exit();
                }

                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    $_SESSION['error'] = "Failed to decode image";
                    header('Location: admin_profile.php');
                    exit();
                }

                $target_dir  = "../uploads/";
                $filename    = uniqid('profile_', true) . '.' . $type;
                $target_file = $target_dir . $filename;

                if (file_put_contents($target_file, $imageData)) {
                    // Delete old pic if not default
                    if ($profile_pic_current && $profile_pic_current !== 'default-profile.jpg') {
                        // compute actual path
                        $old_path = (strpos($profile_pic_current, 'uploads/') !== false || strpos($profile_pic_current, '../uploads/') !== false)
                            ? $profile_pic_current
                            : "../uploads/" . $profile_pic_current;

                        if (file_exists($old_path)) {
                            @unlink($old_path);
                        }
                    }
                    // Store a consistent value; we'll store just the filename to avoid path duplication issues
                    $profile_pic_to_store = $filename;
                } else {
                    $_SESSION['error'] = "Failed to save image";
                    header('Location: admin_profile.php');
                    exit();
                }
            }
        }

        // Update admin data
        $update_query = "UPDATE users SET 
            name = ?, email = ?, date_of_birth = ?, phone_number = ?, 
            profile_pic = ?, program_of_study = ?, intake = ?, country = ?, 
            gender = ?, department = ?, expected_graduation_year = ?
            WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param(
            "sssssssssssi",
            $name,
            $email,
            $date_of_birth,
            $phone_number,
            $profile_pic_to_store,
            $program_of_study,
            $intake,
            $country,
            $gender,
            $department,
            $expected_graduation_year,
            $user_id
        );

        if ($stmt->execute()) {
            // refresh $user_row so the page shows latest data
            $query = "SELECT * FROM users WHERE id = ?";
            $stmt2 = $conn->prepare($query);
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $user_result2 = $stmt2->get_result();
            $user_row = $user_result2->fetch_assoc();

            $_SESSION['profile_pic'] = $user_row['profile_pic'];
            $_SESSION['success'] = "Profile updated successfully!";
            header('Location: admin_profile.php');
            exit();
        } else {
            $_SESSION['error'] = "Error updating profile: " . $stmt->error;
        }
    }

    // Change password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password     = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        if (!password_verify($current_password, $user_row['password'])) {
            $_SESSION['error'] = "Current password is incorrect.";
            header('Location: admin_profile.php?tab=password');
            exit();
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = "New passwords do not match.";
            header('Location: admin_profile.php?tab=password');
            exit();
        }

        if (strlen($new_password) < 8) {
            $_SESSION['error'] = "Password must be at least 8 characters long.";
            header('Location: admin_profile.php?tab=password');
            exit();
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_pass_query = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_pass_query);
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Password changed successfully!";
            header('Location: admin_profile.php?tab=password');
            exit();
        } else {
            $_SESSION['error'] = "Error changing password: " . $stmt->error;
        }
    }
}

// Compute profile pic src reliably whether DB stored filename or path
$picValue = $user_row['profile_pic'] ?: 'default-profile.jpg';
if (strpos($picValue, 'uploads/') !== false || strpos($picValue, '../uploads/') !== false) {
    $profile_pic_src = $picValue; // already a path
} else {
    $profile_pic_src = '../uploads/' . $picValue; // stored as filename
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
    <style>
        :root{
            --primary-color:#2e7d32;
            --secondary-color:#81c784;
            --light-green:#e8f5e9;
            --dark-green:#1b5e20;
            --text-color:#333;
            --light-gray:#f8f9fa;
            --border-radius:8px;
        }
        body{background:#f5f5f5;color:var(--text-color);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;}
        .profile-container{max-width:1200px;margin:30px auto;padding:30px;background:#fff;border-radius:var(--border-radius);box-shadow:0 4px 20px rgba(0,0,0,.08);}
        .profile-header{text-align:center;margin-bottom:30px;color:var(--primary-color);}
        .profile-pic-container{position:relative;width:150px;height:150px;margin:0 auto 20px;}
        .profile-pic{width:100%;height:100%;object-fit:cover;border-radius:50%;border:4px solid var(--secondary-color);box-shadow:0 4px 10px rgba(0,0,0,.1);}
        .profile-pic-upload{position:absolute;bottom:10px;right:10px;background:var(--primary-color);color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:.3s;}
        .profile-pic-upload:hover{background:var(--dark-green);transform:scale(1.1);}
        .profile-section{margin-bottom:30px;padding:20px;background:var(--light-gray);border-radius:var(--border-radius);}
        .form-label{font-weight:600;color:var(--primary-color);}
        .form-control{border-radius:var(--border-radius);border:1px solid #ddd;padding:10px 15px;}
        .form-control:focus{border-color:var(--secondary-color);box-shadow:0 0 0 .25rem rgba(46,125,50,.25);}
        .btn-primary{background:var(--primary-color);border-color:var(--primary-color);border-radius:var(--border-radius);padding:10px 20px;font-weight:600;}
        .btn-primary:hover{background:var(--dark-green);border-color:var(--dark-green);}
        .stats-card{background:#fff;border-radius:var(--border-radius);padding:20px;box-shadow:0 4px 10px rgba(0,0,0,.05);text-align:center;margin-bottom:20px;border-top:4px solid var(--secondary-color);}
        .stats-value{font-size:24px;font-weight:700;color:var(--primary-color);margin:10px 0;}
        .stats-label{font-size:14px;color:#666;}
        .rank-badge{background:var(--primary-color);color:#fff;padding:5px 15px;border-radius:20px;font-weight:600;display:inline-block;}
        /* Tabs styled to match "My Profile" color */
        .nav-tabs .nav-link{color:var(--primary-color);font-weight:600;}
        .nav-tabs .nav-link.active{
            color:#fff !important;
            background-color:var(--primary-color) !important;
            border-color:var(--primary-color) var(--primary-color) #fff !important;
        }
        .nav-tabs{border-bottom:2px solid var(--secondary-color);}
        /* Cropper modal */
        .modal-dialog{max-width:800px;}
        .cropper-preview{width:150px;height:150px;overflow:hidden;border-radius:50%;margin:10px auto;border:3px solid #eee;}
        @media (max-width:768px){.profile-container{padding:20px}.profile-pic-container{width:120px;height:120px}.stats-card{padding:15px}.stats-value{font-size:20px}}
        @media (max-width:576px){.profile-container{padding:15px}.profile-section{padding:15px}.form-control{padding:8px 12px}}

.nav-tabs .nav-link {
  color: #2e7d32 !important;
  background: transparent !important;
  border: none !important;
  font-weight: 600;
}

.nav-tabs .nav-link.active {
  color: #fff !important;
  background-color: #2e7d32 !important;
  border-radius: 6px;
}

.nav-tabs .nav-link:hover {
  background-color: #e8f5e9 !important;
  color: #2e7d32 !important;
}
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="profile-container">
    <h1 class="profile-header">Admin Profile</h1>

    <!-- Messages -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <!-- Profile Picture -->
            <div class="profile-section text-center">
                <div class="profile-pic-container">
                    <img src="<?= htmlspecialchars($profile_pic_src); ?>" alt="Profile Picture" class="profile-pic" id="profile-pic-preview">
                    <label for="profile_pic" class="profile-pic-upload"><i class="fas fa-camera"></i></label>
                </div>

                <h3><?= htmlspecialchars($user_row['name']); ?></h3>
                <p class="text-muted"><?= htmlspecialchars($user_row['email']); ?></p>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    <span class="rank-badge"><i class="fas fa-user-shield me-2"></i>Admin</span>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="stats-card">
                            <div class="stats-label">Admin ID</div>
                            <div class="stats-value"><?= (int)$user_id; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
<li class="nav-item" role="presentation">
  <button
    class="nav-link active"
    id="profile-tab"
    data-bs-toggle="tab"
    data-bs-target="#profile"
    type="button"
    role="tab"
  >
    Profile Information
  </button>
</li>
<li class="nav-item" role="presentation">
  <button
    class="nav-link"
    id="password-tab"
    data-bs-toggle="tab"
    data-bs-target="#password"
    type="button"
    role="tab"
  >
    Change Password
  </button>
</li>
            </ul>

            <div class="tab-content" id="profileTabsContent">
                <!-- Profile Information -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <form action="admin_profile.php" method="POST" enctype="multipart/form-data">
                        <input type="file" class="d-none" id="profile_pic" name="profile_pic" accept="image/*">
                        <input type="hidden" id="profile_pic_data" name="profile_pic_data">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user_row['name']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user_row['email']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?= htmlspecialchars($user_row['date_of_birth']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="phone_number" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?= htmlspecialchars($user_row['phone_number']); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-control" id="gender" name="gender" required>
                                        <option value="Male"   <?= $user_row['gender']=='Male'   ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= $user_row['gender']=='Female' ? 'selected' : '' ?>>Female</option>
                                        <option value="Other"  <?= $user_row['gender']=='Other'  ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" value="<?= htmlspecialchars($user_row['country']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="program_of_study" class="form-label">Program of Study</label>
                                    <input type="text" class="form-control" id="program_of_study" name="program_of_study" value="<?= htmlspecialchars($user_row['program_of_study']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="department" class="form-label">Department</label>
                                    <input type="text" class="form-control" id="department" name="department" value="<?= htmlspecialchars($user_row['department']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="intake" class="form-label">Intake</label>
                                    <input type="text" class="form-control" id="intake" name="intake" value="<?= htmlspecialchars($user_row['intake']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expected_graduation_year" class="form-label">Expected Graduation Year</label>
                                    <input type="number" class="form-control" id="expected_graduation_year" name="expected_graduation_year" min="2000" max="2100" value="<?= htmlspecialchars($user_row['expected_graduation_year']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="tab-pane fade" id="password" role="tabpanel">
                    <form action="admin_profile.php" method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-key me-2"></i>Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- col-md-8 -->
    </div><!-- row -->
</div><!-- container -->

<!-- Cropper Modal -->
<div class="modal fade" id="profilePicModal" tabindex="-1" aria-labelledby="profilePicModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="profilePicModalLabel">Crop Profile Picture</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-8">
                <div class="img-container">
                    <img id="image-to-crop" src="#" alt="Profile Picture" style="max-width:100%;">
                </div>
            </div>
            <div class="col-md-4">
                <div class="cropper-preview"></div>
                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-primary" id="rotate-left" type="button"><i class="fas fa-undo"></i> Rotate Left</button>
                    <button class="btn btn-primary" id="rotate-right" type="button"><i class="fas fa-redo"></i> Rotate Right</button>
                    <button class="btn btn-success" id="crop-btn" type="button"><i class="fas fa-crop"></i> Crop & Save</button>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
<script>
let cropper;
const profilePicInput = document.getElementById('profile_pic');
const profilePicModal = new bootstrap.Modal(document.getElementById('profilePicModal'));
const imageToCrop = document.getElementById('image-to-crop');
const profilePicPreview = document.getElementById('profile-pic-preview');
const profilePicData = document.getElementById('profile_pic_data');
const preview = document.querySelector('.cropper-preview');

profilePicInput.addEventListener('change', function(){
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e){
            imageToCrop.src = e.target.result;
            profilePicModal.show();

            document.getElementById('profilePicModal').addEventListener('shown.bs.modal', function initOnce(){
                if (cropper) cropper.destroy();
                cropper = new Cropper(imageToCrop, {
                    aspectRatio:1, viewMode:1, autoCropArea:0.8, responsive:true,
                    preview:preview, guides:false, center:false, highlight:false,
                    cropBoxMovable:true, cropBoxResizable:true, toggleDragModeOnDblclick:false
                });

                document.getElementById('rotate-left').onclick = () => cropper.rotate(-90);
                document.getElementById('rotate-right').onclick = () => cropper.rotate(90);
                document.getElementById('crop-btn').onclick = () => {
                    const canvas = cropper.getCroppedCanvas({
                        width:500, height:500, minWidth:256, minHeight:256, maxWidth:1000, maxHeight:1000,
                        fillColor:'#fff', imageSmoothingEnabled:true, imageSmoothingQuality:'high'
                    });
                    if (canvas) {
                        profilePicPreview.src = canvas.toDataURL('image/jpeg');
                        profilePicData.value   = canvas.toDataURL('image/jpeg');
                        profilePicModal.hide();
                    }
                };
                // remove this init listener after first run each time modal shows
                document.getElementById('profilePicModal').removeEventListener('shown.bs.modal', initOnce);
            }, { once:true });
        };
        reader.readAsDataURL(this.files[0]);
    }
});

document.getElementById('profilePicModal').addEventListener('hidden.bs.modal', function(){
    if (cropper) { cropper.destroy(); cropper = null; }
});

// Show requested tab via ?tab=profile or ?tab=password
document.addEventListener('DOMContentLoaded', function(){
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    if (activeTab) {
        const trigger = document.querySelector(`[data-bs-target="#${activeTab}"]`);
        if (trigger) new bootstrap.Tab(trigger).show();
    }
});
</script>
</body>
</html>