<?php
require 'koneksi.php';
$q = mysqli_query($conn, 'SELECT id FROM pelanggan');
if (!$q) {
    echo "ERROR: " . mysqli_error($conn);
} else {
    echo "OK. Rows: " . mysqli_num_rows($q);
}
?>
