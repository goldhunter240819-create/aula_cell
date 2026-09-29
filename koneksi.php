<?php
// Session lifetime 1 tahun (365 hari)
$session_lifetime = 365 * 24 * 60 * 60; // 31536000 detik
ini_set('session.gc_maxlifetime', $session_lifetime);
ini_set('session.cookie_lifetime', $session_lifetime);
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);
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
