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

        // Handle image uploads - same as edit_news.php
        $target_dir = __DIR__ . "/../uploads/news/";
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                die("Failed to create upload directory.");
            }
        }

        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    if ($_FILES['images']['size'][$key] <= 5 * 1024 * 1024) { // 5MB limit
                        $image_name = time() . "_" . basename($_FILES['images']['name'][$key]);
                        if (move_uploaded_file($tmp_name, $target_dir . $image_name)) {
                            $stmt_img = $conn->prepare("INSERT INTO news_images (news_id, image) VALUES (?, ?)");
                            $stmt_img->bind_param("is", $news_id, $image_name);
                            $stmt_img->execute();
                            $stmt_img->close();
                        } else {
                            $error .= "Failed to upload " . htmlspecialchars($_FILES['images']['name'][$key]) . "<br>";
                        }
                    } else {
                        $error .= "File " . htmlspecialchars($_FILES['images']['name'][$key]) . " exceeds 5MB limit<br>";
                    }
                }
            }
        }

        // Redirect to prevent duplicate submissions
        $_SESSION['message'] = "News added successfully!";
        header("Location: admin_news.php");
        exit();
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

    <label>Upload Images (max 5MB each):</label><br>
    <input type="file" name="images[]" multiple accept="image/*"><br><br>

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
