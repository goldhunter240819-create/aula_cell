<?php
require 'koneksi.php';
echo "TRANSAKSI UMUM:\n";
$q = mysqli_query($conn, 'DESCRIBE transaksi_umum');
while($r = mysqli_fetch_assoc($q)) print_r($r);

echo "DOMPET:\n";
$q2 = mysqli_query($conn, 'DESCRIBE dompet');
while($r2 = mysqli_fetch_assoc($q2)) print_r($r2);

echo "USERS:\n";
$q3 = mysqli_query($conn, 'DESCRIBE users');
while($r3 = mysqli_fetch_assoc($q3)) print_r($r3);
?>
