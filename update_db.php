<?php
require 'koneksi.php';

echo "<h2>Proses Update Database...</h2>";

// 1. Buat tabel pelanggan jika belum ada
$q_pelanggan = "CREATE TABLE IF NOT EXISTS pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if(mysqli_query($conn, $q_pelanggan)) {
    echo "<p>✅ Tabel <b>pelanggan</b> berhasil dicek/dibuat.</p>";
} else {
    echo "<p>❌ Gagal membuat tabel pelanggan: " . mysqli_error($conn) . "</p>";
}

// 2. Insert Kategori Default jika belum ada
$q_kategori = "INSERT IGNORE INTO kategori (nama_kategori, jenis, tipe) VALUES 
('Gaji Utama', 'Pemasukan', 'Pribadi'), 
('Makan & Minum', 'Pengeluaran', 'Pribadi'), 
('Bensin', 'Pengeluaran', 'Pribadi'), 
('Deposit Saldo Pulsa', 'Pengeluaran', 'Bisnis'), 
('Operasional Konter', 'Pengeluaran', 'Bisnis'), 
('Suntik Modal', 'Pemasukan', 'Bisnis')";

if(mysqli_query($conn, $q_kategori)) {
    echo "<p>✅ Kategori default berhasil disuntikkan ke tabel <b>kategori</b>.</p>";
} else {
    echo "<p>❌ Gagal menyuntikkan kategori: " . mysqli_error($conn) . "</p>";
}

echo "<h3>🎉 Update Database Selesai! Silakan hapus file ini jika sudah tidak digunakan.</h3>";
echo "<a href='index.php'>Kembali ke Dashboard</a>";
?>
