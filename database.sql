CREATE DATABASE IF NOT EXISTS aula_cell;
USE aula_cell;

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel WebAuthn Credentials (Login Biometrik)
CREATE TABLE IF NOT EXISTS webauthn_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    credential_id TEXT NOT NULL,
    public_key TEXT NOT NULL,
    device_name VARCHAR(255) DEFAULT 'Perangkat Biometrik',
    sign_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Kategori (Pribadi & Bisnis)
CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    jenis ENUM('Pemasukan', 'Pengeluaran') NOT NULL,
    tipe ENUM('Pribadi', 'Bisnis') NOT NULL
);

-- Tabel Dompet / Rekening / Saldo
CREATE TABLE IF NOT EXISTS dompet (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_dompet VARCHAR(100) NOT NULL,
    tipe ENUM('Pribadi', 'Bisnis') NOT NULL,
    saldo DECIMAL(15, 2) DEFAULT 0
);

-- Tabel Transaksi Umum (Untuk Keuangan Pribadi & Operasional Bisnis seperti sewa, listrik)
CREATE TABLE IF NOT EXISTS transaksi_umum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    jenis ENUM('Pemasukan', 'Pengeluaran') NOT NULL,
    id_kategori INT NOT NULL,
    id_dompet INT NOT NULL,
    nominal DECIMAL(15, 2) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori(id),
    FOREIGN KEY (id_dompet) REFERENCES dompet(id)
);

-- Tabel Transaksi Penjualan Pulsa / E-Wallet (Khusus Bisnis)
CREATE TABLE IF NOT EXISTS transaksi_penjualan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    produk VARCHAR(100) NOT NULL,
    no_tujuan VARCHAR(20) NOT NULL,
    harga_modal DECIMAL(15, 2) NOT NULL,
    harga_jual DECIMAL(15, 2) NOT NULL,
    laba DECIMAL(15, 2) AS (harga_jual - harga_modal) STORED,
    id_dompet_modal INT NOT NULL, -- Dompet yang terpotong (misal: Saldo Digipos)
    id_dompet_pemasukan INT NOT NULL, -- Dompet penerima uang (misal: Laci Konter atau BCA)
    status ENUM('Sukses', 'Pending', 'Gagal') DEFAULT 'Sukses',
    keterangan TEXT,
    FOREIGN KEY (id_dompet_modal) REFERENCES dompet(id),
    FOREIGN KEY (id_dompet_pemasukan) REFERENCES dompet(id)
);

-- Hapus data lama agar tidak duplikat jika file di-import berulang
TRUNCATE TABLE transaksi_penjualan;
TRUNCATE TABLE transaksi_umum;
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE dompet;
TRUNCATE TABLE kategori;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert Data Dummy / Default Awal
INSERT INTO users (username, password, nama_lengkap) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Owner Aula Cell'); -- Password default: password

INSERT INTO kategori (nama_kategori, jenis, tipe) VALUES 
('Gaji Utama', 'Pemasukan', 'Pribadi'),
('Makan & Minum', 'Pengeluaran', 'Pribadi'),
('Bensin', 'Pengeluaran', 'Pribadi'),
('Deposit Saldo Pulsa', 'Pengeluaran', 'Bisnis'),
('Operasional Konter', 'Pengeluaran', 'Bisnis');

INSERT INTO dompet (nama_dompet, tipe, saldo) VALUES 
('Kas Pribadi', 'Pribadi', 0),
('Cash Konter', 'Bisnis', 0),
('Saldo DANA', 'Bisnis', 0);
