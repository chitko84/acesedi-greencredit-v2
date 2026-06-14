<?php
include '../includes/db.php';
session_start();

// Only allow admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Initialize messages
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $date = $_POST['date'] ?? '';
    $content = $_POST['content'] ?? '';

    if (!$title || !$date || !$content) {
        $error = "Please fill in all required fields.";
    } else {
        $target_dir = __DIR__ . "/../uploads/news/";
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_file_size = 1048576;
        $valid_images = [];
        $file_count = isset($_FILES['images']['name']) ? count(array_filter($_FILES['images']['name'])) : 0;

        if ($file_count < 1) {
            $error = "Please upload at least 1 image.";
        } elseif ($file_count > 3) {
            $error = "You can upload a maximum of 3 images.";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
                    $error = "Failed to upload " . htmlspecialchars($name) . ". Please try again.";
                    break;
                }

                if ($_FILES['images']['size'][$key] > $max_file_size) {
                    $error = "Image " . htmlspecialchars($name) . " exceeds the 1MB limit.";
                    break;
                }

                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $mime = $finfo->file($_FILES['images']['tmp_name'][$key]);
                if (!in_array($extension, $allowed_extensions, true) || !in_array($mime, $allowed_mimes, true)) {
                    $error = "Only JPG, JPEG, PNG, GIF, and WEBP image files are allowed.";
                    break;
                }

                $valid_images[] = [
                    'tmp_name' => $_FILES['images']['tmp_name'][$key],
                    'extension' => $extension,
                ];
            }
        }

        if ($error) {
            // Do not save the news post if any image validation fails.
        } else {
        // Insert news item
        $stmt = $conn->prepare("INSERT INTO news_events (title, date, content) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sss", $title, $date, $content);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }
        $news_id = $stmt->insert_id;
        $stmt->close();

        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                die("Failed to create upload directory.");
            }
        }

            $moved_images = [];
            foreach ($valid_images as $image) {
                $image_name = uniqid('news_', true) . '.' . $image['extension'];
                if (move_uploaded_file($image['tmp_name'], $target_dir . $image_name)) {
                    $moved_images[] = $image_name;
                } else {
                    $error = "Failed to upload one or more images.";
                    break;
                }
            }

            if ($error) {
                foreach ($moved_images as $image_name) {
                    @unlink($target_dir . $image_name);
                }
                $stmt = $conn->prepare("DELETE FROM news_events WHERE id = ?");
                $stmt->bind_param("i", $news_id);
                $stmt->execute();
                $stmt->close();
            } else {
                foreach ($moved_images as $image_name) {
                    $stmt_img = $conn->prepare("INSERT INTO news_images (news_id, image) VALUES (?, ?)");
                    $stmt_img->bind_param("is", $news_id, $image_name);
                    $stmt_img->execute();
                    $stmt_img->close();
                }

        // Redirect to prevent duplicate submissions
        $_SESSION['message'] = "News added successfully!";
        header("Location: admin_news.php");
        exit();
            }
        }
    }
}

// Display session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch all news with their images - same as edit_news.php approach
$sql = "SELECT n.*, GROUP_CONCAT(i.image) AS images 
        FROM news_events n
        LEFT JOIN news_images i ON n.id = i.news_id
        GROUP BY n.id
        ORDER BY n.date DESC";
$result = $conn->query($sql);
if (!$result) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenCredit - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Admin News Management</title>
    <style>
    /* Base Styles */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        color: #333;
        line-height: 1.6;
    }
    
    h2, h3 {
        color: #2c3e50;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }
    
    /* Form Styles */
    form {
    max-width: 600px; /* Limit the width of the form */
    margin: 0 auto; /* Center the form */
    padding: 15px; /* Reduced padding for a more compact look */
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
    
    label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    input[type="text"],
    input[type="date"],
    textarea,
    input[type="file"] {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        margin-bottom: 1rem;
        transition: border-color 0.15s;
    }
    
    input[type="text"]:focus,
    input[type="date"]:focus,
    textarea:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    
    textarea {
        min-height: 150px;
        resize: vertical;
    }
    
    button[type="submit"] {
        background-color: #28a745;
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 500;
        transition: background-color 0.15s;
    }
    
    button[type="submit"]:hover {
        background-color: #218838;
    }
    
    /* Messages */
    p[style*="color:green"] {
        background: #d4edda;
        color: #155724;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
    }
    
    p[style*="color:red"] {
        background: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border-radius: 4px;
        margin-bottom: 1rem;
    }
    
    /* Table Styles */
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    th, td {
        padding: 1rem;
        border: 1px solid #dee2e6;
        text-align: left;
        vertical-align: middle;
    }
    
    th {
        background-color: #f1f3f5;
        font-weight: 600;
        color: #495057;
    }
    
    tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    tr:hover {
        background-color: #f1f3f5;
    }

    body.dark-mode {
        background: #07110d;
        color: #e8fff0;
    }

    body.dark-mode h2,
    body.dark-mode h3,
    body.dark-mode label {
        color: #e8fff0 !important;
    }

    body.dark-mode form#newsForm {
        background: #111c17;
        color: #e8fff0;
        border: 1px solid #244638;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.28);
    }

    body.dark-mode input[type="text"],
    body.dark-mode input[type="date"],
    body.dark-mode textarea,
    body.dark-mode input[type="file"] {
        background: #0b1511 !important;
        color: #e8fff0 !important;
        border-color: #244638 !important;
    }

    body.dark-mode input[type="text"]::placeholder,
    body.dark-mode textarea::placeholder {
        color: #8ba99a;
    }

    body.dark-mode input[type="text"]:focus,
    body.dark-mode input[type="date"]:focus,
    body.dark-mode textarea:focus {
        background: #0d1813 !important;
        color: #ffffff !important;
        border-color: #66e08f !important;
        box-shadow: 0 0 0 0.2rem rgba(102, 224, 143, 0.18);
    }

    body.dark-mode input[type="file"]::file-selector-button {
        background: #13231b;
        color: #e8fff0;
        border: 1px solid #244638;
    }

    body.dark-mode input[type="file"]::file-selector-button:hover {
        background: rgba(102, 224, 143, 0.14);
        border-color: #66e08f;
    }

    body.dark-mode button[type="submit"] {
        background: linear-gradient(135deg, #2bbf68, #14723e);
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(43, 191, 104, 0.22);
    }

    body.dark-mode button[type="submit"]:hover {
        background: linear-gradient(135deg, #3bd97d, #198a4b);
    }

    body.dark-mode .btn-outline-success {
        color: #d9f7e2 !important;
        border-color: rgba(102, 224, 143, 0.6) !important;
    }

    body.dark-mode .btn-outline-success:hover {
        background: rgba(102, 224, 143, 0.16) !important;
        color: #ffffff !important;
        border-color: #66e08f !important;
    }

    body.dark-mode table {
        background: #111c17;
        color: #e8fff0;
        border: 1px solid #244638;
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.28);
    }

    body.dark-mode th {
        background: #1f7045 !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.16) !important;
    }

    body.dark-mode td {
        background: #111c17 !important;
        color: #e8fff0 !important;
        border-color: #244638 !important;
    }

    body.dark-mode tr:nth-child(even) td {
        background: #121a17 !important;
    }

    body.dark-mode tr:hover td {
        background: rgba(102, 224, 143, 0.12) !important;
        color: #ffffff !important;
    }

    body.dark-mode td p {
        color: #b8cfc1 !important;
    }

    body.dark-mode .image-preview img {
        border-color: #244638;
    }

    body.dark-mode a:not(.btn):not(.nav-link):not(.dropdown-item) {
        color: #7df0a0 !important;
    }

    body.dark-mode a:not(.btn):not(.nav-link):not(.dropdown-item):hover {
        color: #b8ffd0 !important;
    }

    body.dark-mode a[onclick],
    body.dark-mode a[data-confirm] {
        color: #ff9b9b !important;
    }

    body.dark-mode a[onclick]:hover,
    body.dark-mode a[data-confirm]:hover {
        color: #ffc4c4 !important;
    }

    body.dark-mode p[style*="color:green"] {
        background: rgba(102, 224, 143, 0.12) !important;
        color: #e8fff0 !important;
        border: 1px solid rgba(102, 224, 143, 0.32);
    }

    body.dark-mode p[style*="color:red"] {
        background: rgba(255, 143, 143, 0.12) !important;
        color: #ffe0e0 !important;
        border: 1px solid rgba(255, 143, 143, 0.32);
    }
    
    /* Image Styles */
    .image-container {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    
    .image-preview img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        transition: transform 0.2s;
    }
    
    .image-preview img:hover {
        transform: scale(1.05);
    }
    
    /* Action Links */
    a {
        color: #007bff;
        text-decoration: none;
        margin-right: 0.5rem;
        transition: color 0.15s;
    }
    
    a:hover {
        color: #0056b3;
        text-decoration: underline;
    }
    
    a[onclick] {
        color: #dc3545;
    }
    
    a[onclick]:hover {
        color: #a71d2a;
    }
    
    h2, h3 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-align: center;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        form, table {
            padding: 1rem;
        }
        
        th, td {
            padding: 0.75rem;
        }
    }
</style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h2 class="mb-0">Manage News & Events</h2>
    <a href="export_csv.php?table=news" class="btn btn-outline-success">Export CSV</a>
</div>
<?php if ($message): ?>
    <p style='color:green;'><?php echo $message; ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p style='color:red;'><?php echo $error; ?></p>
<?php endif; ?>

<form id="newsForm" method="post" enctype="multipart/form-data">
    <label>Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Date:</label><br>
    <input type="date" name="date" required><br><br>

    <label>Content:</label><br>
    <textarea name="content" rows="5" required></textarea><br><br>

    <label>Upload Images:</label><br>
    <input type="file" name="images[]" multiple accept="image/*" required><br>
    <small class="text-muted">Upload 1 to 3 images. Each image must be 1MB or below.</small><br><br>

    <button type="submit">Add News</button>
</form>

<hr>

<h3>Existing News</h3>
<table>
    <thead>
        <tr>
            <th>Images</th>
            <th>Title</th>
            <th>Date</th>
            <th>Content</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td>
                <div class="image-container">
                <?php if (!empty($row['images'])): ?>
                    <?php foreach (explode(",", $row['images']) as $img): ?>
                        <?php if (!empty($img)): ?>
                            <div class="image-preview">
                                <img src="../uploads/news/<?php echo htmlspecialchars($img); ?>" alt="News Image">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    No images
                <?php endif; ?>
                </div>
            </td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['date']); ?></td>
            <td>
                <p style="word-wrap: break-word; white-space: pre-line;">
                    <?php
                        $words = explode(' ', $row['content']);
                        $preview = implode(' ', array_slice($words, 0, 5));
                        echo htmlspecialchars($preview . '...');
                    ?>
                </p>
            </td>
            <td>
                <a href="edit_news.php?id=<?php echo $row['id']; ?>">Edit</a> | 
                <a href="delete_news.php?id=<?php echo $row['id']; ?>" data-confirm="Delete this news? This action cannot be undone.">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<?php include 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dropdown toggles
    function simpleDropdown(toggleSelector) {
        const toggle = document.querySelector(toggleSelector);
        if (!toggle) return;

        toggle.addEventListener('click', function (e) {
            e.preventDefault(); 
            const menu = toggle.nextElementSibling;
            if (!menu) return;
            menu.classList.toggle('show');
        });
    }

    simpleDropdown('#adminDropdown');
    simpleDropdown('#adminProfileDropdown');

    // Confirmation for Add News
    const newsForm = document.getElementById('newsForm');
    if (newsForm) {
        newsForm.addEventListener('submit', function (e) {
            const imageInput = newsForm.querySelector('input[name="images[]"]');
            const files = imageInput ? Array.from(imageInput.files) : [];
            const maxSize = 1048576;
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (files.length < 1) {
                alert('Please upload at least 1 image.');
                e.preventDefault();
                return;
            }
            if (files.length > 3) {
                alert('You can upload a maximum of 3 images.');
                e.preventDefault();
                return;
            }
            for (const file of files) {
                if (file.size > maxSize) {
                    alert('Each image must be 1MB or below.');
                    e.preventDefault();
                    return;
                }
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG, JPEG, PNG, GIF, and WEBP image files are allowed.');
                    e.preventDefault();
                    return;
                }
            }
            const confirmed = confirm('Are you sure you want to add this news item?');
            if (!confirmed) {
                e.preventDefault(); // cancel form submission
            }
        });
    }

});
</script>
</body>
</html>
