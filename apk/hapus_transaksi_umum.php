<?php
require '../koneksi.php';

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $q = mysqli_query($conn, "SELECT * FROM transaksi_umum WHERE id = $id");
    if(mysqli_num_rows($q) > 0) {
        $trx = mysqli_fetch_assoc($q);
        
        mysqli_begin_transaction($conn);
        try {
            // Kembalikan saldo
            if($trx['jenis'] == 'Pemasukan') {
                mysqli_query($conn, "UPDATE dompet SET saldo = saldo - {$trx['nominal']} WHERE id = {$trx['id_dompet']}");
            } else { // Pengeluaran
                mysqli_query($conn, "UPDATE dompet SET saldo = saldo + {$trx['nominal']} WHERE id = {$trx['id_dompet']}");
            }
            
            mysqli_query($conn, "DELETE FROM transaksi_umum WHERE id = $id");
            
            mysqli_commit($conn);
            
            // Redirect based on referer to go back to correct history page
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'riwayat_pribadi.php';
            echo "<script>window.location.href='$referer';</script>";
        } catch(Exception $e) {
            mysqli_rollback($conn);
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'riwayat_pribadi.php';
            echo "<script>alert('Gagal menghapus: " . addslashes($e->getMessage()) . "'); window.location.href='$referer';</script>";
        }
    } else {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'riwayat_pribadi.php';
        echo "<script>window.location.href='$referer';</script>";
    }
} else {
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'riwayat_pribadi.php';
    header("Location: $referer");
}
