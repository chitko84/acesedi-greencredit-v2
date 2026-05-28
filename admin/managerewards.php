<?php
session_start();
include '../includes/db.php';

// Redirect if admin is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch all submissions
$query = "SELECT * FROM submissions WHERE reward IS NULL ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$submissions_result = $stmt->get_result();

// Handle reward assignment (if form is submitted)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $submission_id = $_POST['submission_id'];
    $reward_type = $_POST['reward_type'];
    $reward = '';

    // If the reward is a text message
    if ($reward_type == 'text') {
        $reward = $_POST['reward_text'];
    }

    // If the reward is a file
    if ($reward_type == 'file' && isset($_FILES['reward_file']) && $_FILES['reward_file']['error'] == 0) {
        $target_dir = "uploads/rewards/";
        $target_file = $target_dir . basename($_FILES['reward_file']['name']);
        move_uploaded_file($_FILES['reward_file']['tmp_name'], $target_file);
        $reward = 'uploads/rewards/' . basename($_FILES['reward_file']['name']);
    }

    // If the reward is an image
    if ($reward_type == 'image' && isset($_FILES['reward_image']) && $_FILES['reward_image']['error'] == 0) {
        $target_dir = "uploads/rewards/";
        $target_file = $target_dir . basename($_FILES['reward_image']['name']);
        move_uploaded_file($_FILES['reward_image']['tmp_name'], $target_file);
        $reward = 'uploads/rewards/' . basename($_FILES['reward_image']['name']);
    }

    // Update the reward for the submission
    $update_query = "UPDATE submissions SET reward = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $reward, $submission_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Reward assigned successfully!";
    } else {
        $_SESSION['error'] = "Error assigning reward: " . $stmt->error;
    }

    // Redirect back to manage rewards page
    header('Location: managerewards.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Manage Rewards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css"> <!-- Custom CSS for Admin Panel -->
</head>
<body>

<!-- Navbar for Admin -->
<?php include 'includes/header.php'; ?>

<!-- Manage Rewards Section -->
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h2 class="text-center mb-0">Manage Rewards</h2>
        <a href="export_csv.php?table=rewards" class="btn btn-outline-success">
            <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
    </div>

    <!-- Display error or success messages -->
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

    <!-- Rewards Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Submission ID</th>
                <th>Action</th>
                <th>Points</th>
                <th>Assigned Date</th>
                <th>Submitted Photo</th>
                <th>Assign Reward</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($submission = $submissions_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $submission['id']; ?></td>
                    <td><?php echo htmlspecialchars($submission['action']); ?></td>
                    <td><?php echo $submission['points']; ?></td>
                    <td><?php echo $submission['created_at']; ?></td>
                    <td>
                        <!-- Display the submitted image if it exists -->
                        <?php if ($submission['proof_image']) { ?>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#proofImageModal-<?php echo $submission['id']; ?>">
                                <img src="uploads/<?php echo $submission['proof_image']; ?>" alt="Submitted Image" width="50" height="50">
                            </a>

                            <!-- Modal to view submitted image -->
                            <div class="modal fade" id="proofImageModal-<?php echo $submission['id']; ?>" tabindex="-1" aria-labelledby="proofImageModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="proofImageModalLabel">Submitted Image</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="uploads/<?php echo $submission['proof_image']; ?>" alt="Proof Image" class="img-fluid">
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } else {
                            echo "No image";
                        } ?>
                    </td>
                    <td>
                        <!-- Reward Assignment Form -->
                        <form action="managerewards.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="submission_id" value="<?php echo $submission['id']; ?>">

                            <!-- Reward Type Selection -->
                            <div class="mb-3">
                                <label for="reward_type" class="form-label">Select Reward Type</label>
                                <select class="form-control" id="reward_type" name="reward_type" required>
                                    <option value="text">Text</option>
                                    <option value="image">Image</option>
                                    <option value="file">File</option>
                                </select>
                            </div>

                            <!-- Text Reward -->
                            <div class="mb-3" id="reward_text_container">
                                <label for="reward_text" class="form-label">Enter Reward Text</label>
                                <input type="text" class="form-control" name="reward_text" placeholder="Enter a text reward" required>
                            </div>

                            <!-- Image Reward -->
                            <div class="mb-3" id="reward_image_container" style="display: none;">
                                <label for="reward_image" class="form-label">Upload Reward Image</label>
                                <input type="file" class="form-control" name="reward_image">
                            </div>

                            <!-- File Reward -->
                            <div class="mb-3" id="reward_file_container" style="display: none;">
                                <label for="reward_file" class="form-label">Upload Reward File</label>
                                <input type="file" class="form-control" name="reward_file">
                            </div>

                            <button type="submit" class="btn btn-success mt-2">Assign Reward</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Toggle reward input fields based on reward type selection
    $('#reward_type').change(function() {
        var selectedReward = $(this).val();
        
        // Hide all containers first
        $('#reward_text_container, #reward_image_container, #reward_file_container').hide();
        
        // Show the relevant container based on the selected reward type
        if (selectedReward == 'text') {
            $('#reward_text_container').show();
        } else if (selectedReward == 'image') {
            $('#reward_image_container').show();
        } else if (selectedReward == 'file') {
            $('#reward_file_container').show();
        }
    });

    // Trigger change event on page load to show the correct input field
    $(document).ready(function() {
        $('#reward_type').change();
    });
</script>

</body>
</html>
