<?php
session_start();
if (!isset($_SESSION['success'])) {
    header('Location: submit_item.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submission Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/images/gc_logo_1.png" type="image/x-icon">
</head>
<body>
<div class='container mt-5'>
    <div class='alert alert-success text-center fs-3 p-4 mx-auto' style='max-width: 600px;'>
        <?= htmlspecialchars($_SESSION['success']) ?>
        <br>
        <a href='dashboard.php' class='btn btn-primary mt-3'>Back to Dashboard</a>
    </div>
</div>
</body>
</html>
