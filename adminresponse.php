<?php 
include 'includes/db.php';
session_start();

// Get the dynamic message ID from the URL or set a default value (for testing)
$message_id = isset($_GET['id']) ? $_GET['id'] : 1; // Default to 1 for testing

// Fetch the contact message based on the dynamic message ID
$query = "SELECT * FROM contact_messages WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $message_id); // Get message based on the ID
$stmt->execute();
$result = $stmt->get_result();
$message = $result->fetch_assoc();

// Handle form submission for admin response
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $_POST['response'];

    // Update the response in the database
    $update_query = "UPDATE contact_messages SET response = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $response, $message_id);

    if ($update_stmt->execute()) {
        $_SESSION['success'] = "Response sent successfully!";
        header('Location: contact.php?id=' . $message_id); // Redirect to the same page to prevent resubmission
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $update_stmt->error;
        header('Location: contact.php?id=' . $message_id);
        exit();
    }
}
?>

<!-- Common Header -->
<?php include 'includes/header.php'; ?>

<!-- Contact Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">Contact Us</h2>

    <!-- Contact Information Section -->
    <div class="row">
        <div class="col-md-6">
            <h4>Contact Information</h4>
            <p>
                <strong>Email:</strong> <a href="mailto:info@greencredit.com">info@greencredit.com</a><br>
                <strong>Phone Number:</strong> +123 123 123<br>
                <strong>WhatsApp:</strong> <a href="https://wa.me/+1234567890" target="_blank">Click to chat</a>
            </p>
        </div>

        <!-- Embedded Google Map Section -->
        <div class="col-md-6">
            <h4>Our Location</h4>
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.978022254398!2d100.38382217422982!3d6.133655127572455!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x304b5ac489a998b9%3A0x3fa53e13f07fbb01!2sAlbukhary%20International%20University%20(AIU)!5e0!3m2!1sen!2smy!4v1746077900027!5m2!1sen!2smy" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>

    <!-- Feedback Form Section -->
    <h4 class="mt-4">Send Us a Message</h4>
    
    <!-- Display success or error message -->
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

    <!-- Display the user's message -->
    <div class="mb-3">
        <strong>User's Message:</strong>
        <p><?php echo htmlspecialchars($message['message']); ?></p>
    </div>

    <!-- Admin's Response Section -->
    <?php if ($_SESSION['role'] === 'admin') { // Only allow admin to respond ?>
        <h4>Admin Response</h4>
        <form action="contact.php?id=<?php echo $message_id; ?>" method="POST">
            <div class="mb-3">
                <label for="response" class="form-label">Your Response</label>
                <textarea class="form-control" id="response" name="response" rows="4" required><?php echo htmlspecialchars($message['response']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Response</button>
        </form>
    <?php } ?>

</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
