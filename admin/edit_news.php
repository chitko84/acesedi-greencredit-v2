<?php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = intval($_GET['id']);

// Fetch news
$stmt = $conn->prepare("SELECT * FROM news_events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$news = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("UPDATE news_events SET title=?, date=?, content=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $date, $content, $id);
    $stmt->execute();

    // Upload new images if any
    $target_dir = "../uploads/news/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            if ($_FILES['images']['size'][$key] <= 5 * 1024 * 1024) {
                $image_name = time() . "_" . basename($_FILES['images']['name'][$key]);
                if (move_uploaded_file($tmp_name, $target_dir . $image_name)) {
                    $stmt_img = $conn->prepare("INSERT INTO news_images (news_id, image) VALUES (?, ?)");
                    $stmt_img->bind_param("is", $id, $image_name);
                    $stmt_img->execute();
                }
            }
        }
    }

    header("Location: admin_news.php");
    exit();
}

// Fetch existing images
$images = $conn->query("SELECT * FROM news_images WHERE news_id = $id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
    <style>
    /* Reset all margins and padding */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Base Styles */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f7f6;
        color: #333;
        line-height: 1.6;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    /* Main Content Container */
    .main-content {
        flex: 1;
        padding: 20px 0;
    }

    /* Form Container */
    .card {
        background: white;
        padding: 2.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        max-width: 800px;
    }

    /* Heading Styles */
    h2 {
        color: #2c3e50;
        margin: 20px 0;
        font-weight: 700;
        text-align: center;
    }

    /* Form Element Styles */
    label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        display: block;
    }

    input[type="text"],
    input[type="date"],
    textarea,
    input[type="file"] {
        width: 100%;
        padding: 1rem;
        border: 1px solid #ced4da;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-size: 1rem;
    }

    textarea {
        min-height: 180px;
        resize: vertical;
    }

    button[type="submit"] {
        background-color: #28a745;
        color: white;
        padding: 1rem 2rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
    }

    /* Image Styles */
    .image-container {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin: 1rem 0;
    }

    .image-preview {
        position: relative;
    }

    .image-preview img {
        width: 90px;
        height: 90px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .card {
            padding: 1.5rem;
        }
    }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="main-content">
    <h2>Edit News</h2>
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="admin_news.php" class="btn btn-secondary">← Back to News</a>
    </div>
    <form id="editNewsForm" method="post" enctype="multipart/form-data" class="card">
        <label>Title:</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($news['title']); ?>" required>

        <label>Date:</label>
        <input type="date" name="date" value="<?php echo $news['date']; ?>" required>

        <label>Content:</label>
        <textarea name="content" rows="5" required><?php echo htmlspecialchars($news['content']); ?></textarea>

        <label>Existing Images:</label>
        <div class="image-container">
            <?php while ($img = $images->fetch_assoc()) { ?>
                <div class="image-preview">
                    <img src="../uploads/news/<?php echo $img['image']; ?>" width="100">
                    <a href="delete_image.php?id=<?php echo $img['id']; ?>&news_id=<?php echo $id; ?>" data-confirm="Delete this image? This action cannot be undone.">[x]</a>
                </div>
            <?php } ?>
        </div>

        <label>Add More Images (max 5MB each):</label>
        <input type="file" name="images[]" multiple accept="image/*">

        <button type="submit">Save Changes</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const editForm = document.getElementById('editNewsForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const confirmed = confirm('Are you sure you want to save changes to this news item?');
            if (!confirmed) {
                e.preventDefault(); // cancel form submission
            }
        });
    }

    function simpleDropdown(toggleSelector) {
        const toggle = document.querySelector(toggleSelector);
        if (!toggle) return;

        toggle.addEventListener('click', function (e) {
            e.preventDefault(); // prevent page jump
            const menu = toggle.nextElementSibling;
            if (!menu) return;

            menu.classList.toggle('show');
        });
    }

    simpleDropdown('#adminDropdown');       
    simpleDropdown('#adminProfileDropdown');

});
</script>
</body>
</html>
