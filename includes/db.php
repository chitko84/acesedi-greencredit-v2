<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "acesedi_greencreditdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

include_once __DIR__ . '/default_admin.php';
gc_ensure_default_admin($conn);
?>
