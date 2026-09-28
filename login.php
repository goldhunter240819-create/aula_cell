<?php
require 'koneksi.php';

// Jika sudah login, lempar kembali ke dashboard
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; 
    
    $q = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if(mysqli_num_rows($q) > 0) {
        $user = mysqli_fetch_assoc($q);
        // Verifikasi hash password atau password plaintext (dukungan untuk akun lama)
        if(password_verify($password, $user['password']) || $password === $user['password']) {
            
            // Auto-upgrade password ke hash jika masih plaintext
            if($password === $user['password'] && !password_verify($password, $user['password'])) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET password = '$hashed_password' WHERE id = " . $user['id']);
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aula Cell</title>
    <link rel="icon" href="aulalogo.png" type="image/jpeg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], },
                    colors: {
                        primary: '#1d4ed8', secondary: '#0ea5e9',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #e2e8f0; /* slate-100 */
            color: #1e293b; /* slate-800 */
        }
        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        }
        .input-glass { background: #ffffff; border: 1px solid rgba(0, 0, 0, 0.05); color: #0f172a; }
        .input-glass:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center p-4">

    <!-- Ornaments -->
    <div class="fixed top-20 left-20 w-72 h-72 bg-primary/20 rounded-full blur-3xl z-0"></div>
    <div class="fixed bottom-20 right-20 w-72 h-72 bg-purple-600/20 rounded-full blur-3xl z-0"></div>

    <div class="glass-card w-full max-w-md rounded-3xl p-8 relative z-10 shadow-2xl">
        <div class="flex flex-col items-center mb-8">
            <div class="w-20 h-20 rounded-2xl overflow-hidden shadow-lg shadow-primary/30 mb-4 border border-slate-200">
                <img src="aulalogo.png" alt="Aula Cell Logo" class="w-full h-full object-cover">
            </div>
            <h1 class="text-2xl font-bold tracking-tight">Selamat Datang</h1>
            <p class="text-sm text-slate-500 mt-1">Masuk ke sistem manajemen Aula Cell</p>
        </div>

        <?php if($error): ?>
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm text-center">
                <i class="fa-solid fa-circle-exclamation mr-1"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="flex flex-col gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <input type="text" name="username" required value="Maul" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass transition-all">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input type="password" name="password" required placeholder="Masukkan password" class="w-full pl-11 pr-4 py-3 rounded-xl input-glass transition-all">
                </div>
            </div>

            <button type="submit" name="login" class="w-full bg-primary hover:bg-blue-600 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-primary/30 transition-all transform hover:-translate-y-0.5 mt-2">
                Masuk <i class="fa-solid fa-arrow-right-to-bracket ml-1"></i>
            </button>
        </form>

        <div class="mt-8 text-center text-xs text-slate-500">
            &copy; <?= date('Y') ?> Aula Cell Finance System
        </div>
    </div>

</body>
</html>
