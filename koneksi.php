<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "aula_cell";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Global Auth Check
$current_page = basename($_SERVER['PHP_SELF']);
if(!isset($_SESSION['user_id']) && $current_page != 'login.php') {
    header("Location: login.php");
    exit;
}
?>
