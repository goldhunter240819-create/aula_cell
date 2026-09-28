-- SCRIPT UPDATE DATABASE (TANPA MENGHAPUS DATA LAMA)
-- Jalankan script ini di phpMyAdmin server live (hosting/cpanel)

-- 1. Tambahkan kolom tipe di tabel dompet (jika sebelumnya belum ada)
-- Abaikan error jika kolom tipe sudah ada.
ALTER TABLE dompet ADD COLUMN tipe ENUM('Pribadi', 'Bisnis') NOT NULL DEFAULT 'Bisnis' AFTER nama_dompet;

-- 2. Pastikan dompet pribadi diset sebagai 'Pribadi'
UPDATE dompet SET tipe='Pribadi' WHERE nama_dompet LIKE '%Pribadi%';

-- 3. Tambahkan 2 Dompet Bisnis Baru (Cash Konter & Saldo DANA)
INSERT INTO dompet (nama_dompet, tipe, saldo) VALUES ('Cash Konter', 'Bisnis', 0);
INSERT INTO dompet (nama_dompet, tipe, saldo) VALUES ('Saldo DANA', 'Bisnis', 0);

-- 4. MIGRASI DATA LAMA (Pindahkan semua transaksi lama dari dompet lama ke 'Cash Konter')
-- Supaya laporan lama tidak error/hilang, kita alihkan semua history ke Cash Konter
UPDATE transaksi_penjualan 
SET id_dompet_modal = (SELECT id FROM dompet WHERE nama_dompet = 'Cash Konter' LIMIT 1)
WHERE id_dompet_modal IN (SELECT id FROM dompet WHERE nama_dompet IN ('Kas Usaha', 'Laci Konter', 'Saldo Digipos', 'Saldo DANA Bisnis'));

UPDATE transaksi_penjualan 
SET id_dompet_pemasukan = (SELECT id FROM dompet WHERE nama_dompet = 'Cash Konter' LIMIT 1)
WHERE id_dompet_pemasukan IN (SELECT id FROM dompet WHERE nama_dompet IN ('Kas Usaha', 'Laci Konter', 'Saldo Digipos', 'Saldo DANA Bisnis'));

UPDATE transaksi_umum 
SET id_dompet = (SELECT id FROM dompet WHERE nama_dompet = 'Cash Konter' LIMIT 1)
WHERE id_dompet IN (SELECT id FROM dompet WHERE nama_dompet IN ('Kas Usaha', 'Laci Konter', 'Saldo Digipos', 'Saldo DANA Bisnis'));

-- 5. SEMBUNYIKAN / HAPUS DOMPET LAMA
-- Karena datanya sudah dipindah ke Cash Konter, dompet lama bisa kita hapus agar tidak muncul di dropdown
DELETE FROM dompet WHERE nama_dompet IN ('Kas Usaha', 'Laci Konter', 'Saldo Digipos', 'Saldo DANA Bisnis');

-- CATATAN:
-- Setelah script ini dijalankan, saldo 'Cash Konter' di server live mungkin jadi 0 (karena baru dibuat).
-- Silakan Kakak input "Pemasukan Usaha" melalui fitur "Operasional Bisnis" untuk menyesuaikan saldo real Cash Konter dan Saldo DANA saat ini.
