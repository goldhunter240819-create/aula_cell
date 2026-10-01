<?php
require '../koneksi.php';

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $q = mysqli_query($conn, "SELECT * FROM transaksi_penjualan WHERE id = $id");
    if(mysqli_num_rows($q) > 0) {
        $trx = mysqli_fetch_assoc($q);
        
        mysqli_begin_transaction($conn);
        try {
            // Kembalikan saldo modal
            mysqli_query($conn, "UPDATE dompet SET saldo = saldo + {$trx['harga_modal']} WHERE id = {$trx['id_dompet_modal']}");
            
            // Tarik kembali saldo pemasukan jika Lunas
            if($trx['status_pembayaran'] == 'Lunas') {
                mysqli_query($conn, "UPDATE dompet SET saldo = saldo - {$trx['harga_jual']} WHERE id = {$trx['id_dompet_pemasukan']}");
            }
            
            mysqli_query($conn, "DELETE FROM transaksi_penjualan WHERE id = $id");
            
            mysqli_commit($conn);
            echo "<script>window.location.href='riwayat_bisnis.php';</script>";
        } catch(Exception $e) {
            mysqli_rollback($conn);
            echo "<script>alert('Gagal menghapus: " . addslashes($e->getMessage()) . "'); window.location.href='riwayat_bisnis.php';</script>";
        }
    } else {
        echo "<script>window.location.href='riwayat_bisnis.php';</script>";
    }
} else {
    header("Location: riwayat_bisnis.php");
}
