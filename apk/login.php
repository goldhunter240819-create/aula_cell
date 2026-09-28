<?php
require '../koneksi.php';

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
        if(password_verify($password, $user['password']) || $password === $user['password']) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <link rel="icon" href="../aulalogo.png" type="image/jpeg">
    <title>Login Aula Cell Mobile</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ["Nunito", "sans-serif"], },
                    colors: {
                        primary: "#2563eb", bglight: "#f8fafc",
                    }
                }
            }
        }
    </script>
    <style>
        body { color: #334155; -webkit-tap-highlight-color: transparent; }
        ::-webkit-scrollbar { width: 0px; background: transparent; }
        .header-curve {
            border-bottom-left-radius: 3rem;
            border-bottom-right-radius: 3rem;
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-900 flex justify-center items-center h-[100dvh] overflow-hidden md:p-4">

    <!-- Mobile Frame -->
    <div class="w-full h-[100dvh] md:max-w-[400px] md:h-[95dvh] md:max-h-[850px] bg-slate-50 relative md:shadow-2xl md:rounded-[2.5rem] overflow-hidden flex flex-col md:border-8 md:border-slate-800">
        
        <!-- Curved Blue Background -->
        <div class="absolute top-0 w-full h-80 bg-gradient-to-b from-blue-700 to-blue-500 header-curve z-0"></div>
        
        <div class="relative z-10 flex-1 flex flex-col justify-center px-6 py-10">
            
            <div class="flex flex-col items-center mb-8">
                <div class="w-24 h-24 rounded-2xl bg-white p-1 shadow-lg mb-4">
                    <img src="../aulalogo.png" class="w-full h-full object-cover rounded-xl">
                </div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Aula Cell</h1>
                <p class="text-blue-100 font-semibold text-sm">Finance Manager</p>
            </div>
            
            <form action="" method="POST" class="bg-white w-full rounded-[2rem] p-6 shadow-[0_10px_40px_rgba(0,0,0,0.08)]">
                <h2 class="text-xl font-extrabold text-slate-800 mb-6 text-center">Silakan Masuk</h2>
                
                <?php if($error): ?>
                    <div class="mb-4 p-3 bg-red-50 border border-red-100 text-red-500 rounded-xl text-sm font-bold flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
                    </div>
                <?php endif; ?>
                
                <div class="space-y-4">
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <input type="text" name="username" required 
                                class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-slate-700"
                                placeholder="Username">
                        </div>
                    </div>
                    
                    <div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <input type="password" id="password" name="password" required 
                                class="w-full pl-11 pr-12 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-slate-700"
                                placeholder="Password">
                            <button type="button" onclick="togglePass()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-primary transition-colors focus:outline-none">
                                <i class="fa-solid fa-eye-slash" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="login" 
                    class="w-full mt-8 bg-primary hover:bg-blue-700 text-white font-extrabold py-3.5 rounded-xl shadow-lg shadow-blue-500/30 active:scale-95 transition-transform">
                    Masuk
                </button>
            </form>
            
            <button id="installAppBtn" class="mx-auto mt-6 px-6 py-3 bg-white text-slate-700 font-extrabold text-xs rounded-full shadow-[0_4px_15px_rgba(0,0,0,0.05)] border border-slate-200 active:scale-95 transition-transform flex items-center justify-center gap-2">
                <i class="fa-solid fa-download text-primary"></i> Install ke Layar Utama
            </button>
            
            <p class="text-slate-400 font-bold text-[10px] mt-8 text-center uppercase tracking-widest">&copy; <?= date('Y') ?> Aula Cell App</p>
        </div>
        
    </div>
    
    <script>
        function togglePass() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                pwd.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js');
        }

        let deferredPrompt;
        const installBtn = document.getElementById('installAppBtn');

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    installBtn.classList.add('hidden');
                }
                deferredPrompt = null;
            } else {
                alert("Sistem belum siap atau aplikasi sudah diinstall. \n\nTips: Klik icon titik tiga di pojok kanan atas browser (Chrome), lalu pilih 'Tambahkan ke Layar Utama' atau 'Install Aplikasi'.");
            }
        });
    </script>
</body>
</html>