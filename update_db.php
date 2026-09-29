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

// 3. Buat tabel transaksi_penjualan jika belum ada
$q_transaksi = "CREATE TABLE IF NOT EXISTS transaksi_penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    produk VARCHAR(100) NOT NULL,
    no_tujuan VARCHAR(50) NOT NULL,
    harga_modal DECIMAL(15,2) NOT NULL,
    harga_jual DECIMAL(15,2) NOT NULL,
    laba DECIMAL(15,2) AS (harga_jual - harga_modal) STORED,
    id_dompet_modal INT NOT NULL,
    id_dompet_pemasukan INT NOT NULL,
    status ENUM('Sukses', 'Pending', 'Gagal') DEFAULT 'Sukses',
    status_pembayaran ENUM('Lunas', 'Hutang') DEFAULT 'Lunas',
    keterangan TEXT
)";

if(mysqli_query($conn, $q_transaksi)) {
    echo "<p>✅ Tabel <b>transaksi_penjualan</b> berhasil dicek/dibuat.</p>";
} else {
    echo "<p>❌ Gagal membuat tabel transaksi_penjualan: " . mysqli_error($conn) . "</p>";
}

// 4. Tambah kolom status_pembayaran jika tabel sudah ada sebelumnya tanpa kolom ini
$cek_kolom = mysqli_query($conn, "SHOW COLUMNS FROM transaksi_penjualan LIKE 'status_pembayaran'");
if(mysqli_num_rows($cek_kolom) == 0) {
    if(mysqli_query($conn, "ALTER TABLE transaksi_penjualan ADD COLUMN status_pembayaran ENUM('Lunas', 'Hutang') DEFAULT 'Lunas' AFTER status")) {
        echo "<p>✅ Kolom <b>status_pembayaran</b> berhasil ditambahkan ke transaksi_penjualan.</p>";
    } else {
        echo "<p>❌ Gagal menambahkan kolom status_pembayaran: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p>✅ Kolom <b>status_pembayaran</b> sudah ada di transaksi_penjualan.</p>";
}

echo "<h3>🎉 Update Database Selesai! Silakan hapus file ini jika sudah tidak digunakan.</h3>";
echo "<a href='index.php'>Kembali ke Dashboard</a>";
?>
