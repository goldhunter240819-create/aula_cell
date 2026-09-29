<?php
require 'koneksi.php';
$q = mysqli_query($conn, "SELECT SUM(harga_jual) as total FROM transaksi_penjualan WHERE status_pembayaran = 'Hutang'");
var_dump($q);
if(!$q) echo mysqli_error($conn);
