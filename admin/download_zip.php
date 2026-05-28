<?php
include '../includes/db.php';

if (!isset($_GET['id'])) {
    die("Submission ID not specified.");
}

$submission_id = intval($_GET['id']);

// Get submission to retrieve images
$stmt = $conn->prepare("SELECT proof_image FROM submissions WHERE id = ?");
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$result = $stmt->get_result();
$submission = $result->fetch_assoc();

if (!$submission) {
    die("Submission not found.");
}

$images = json_decode($submission['proof_image'], true);
if (!is_array($images) || count($images) === 0) {
    die("No images to download.");
}

$zip = new ZipArchive();
$zip_filename = "submission_$submission_id.zip";

$temp_zip = tempnam(sys_get_temp_dir(), $zip_filename);
if ($zip->open($temp_zip, ZipArchive::CREATE) !== TRUE) {
    die("Could not create ZIP file.");
}

// Add each image to ZIP
foreach ($images as $img) {
    $file_path = "../uploads/" . basename($img);
    if (file_exists($file_path)) {
        $zip->addFile($file_path, basename($img));
    }
}

$zip->close();

// Send the file
header('Content-Type: application/zip');
header("Content-Disposition: attachment; filename=\"$zip_filename\"");
header('Content-Length: ' . filesize($temp_zip));
readfile($temp_zip);
unlink($temp_zip);
exit();
