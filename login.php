<?php
session_start();
include 'config/database.php';

// Jika sudah login, lempar ke index
if(isset($_SESSION['status']) && $_SESSION['status'] == "login"){
    header("location:index.php");
    exit;
}

$error_msg = null; 

if(isset($_POST['login'])){
    $nip = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $password_input = $_POST['password'];

    $login = mysqli_query($koneksi, "SELECT * FROM users WHERE nip='$nip'");
    $cek = mysqli_num_rows($login);

    if($cek > 0){
        $data = mysqli_fetch_assoc($login);
        $password_db = $data['password'];

        // 1. Cek Password (Support: Hash PHP, MD5, & Plain Text)
        $cek_hash   = password_verify($password_input, $password_db);
        $cek_md5    = (md5($password_input) == $password_db);
        $cek_plain  = ($password_input == $password_db);

        if($cek_hash || $cek_md5 || $cek_plain) {
            
            // 2. CEK STATUS AKUN
            if($data['status_akun'] != 'aktif') {
                $error_msg = "Akun Anda telah dinonaktifkan/diblokir. Silakan hubungi Administrator.";
            } else {
                $level_user = strtolower($data['role']); 

                // Buat Session
                $_SESSION['nip']          = $nip;
                $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
                $_SESSION['id_user']      = $data['id_user'];
                $_SESSION['is_pejabat']   = $data['is_pejabat'];
                $_SESSION['status']       = "login";
                $_SESSION['level']        = $level_user; 
                $_SESSION['role']         = $level_user; 

                // Redirect sesuai role
                if($level_user == "admin" || $level_user == "administrator"){
                    header("location:index.php?page=dashboard_admin");
                }else{
                    header("location:index.php?page=dashboard_user");
                }
                exit; 
            }

        } else {
            $error_msg = "Password yang Anda masukkan salah.";
        }
    } else {
        $error_msg = "NIP tidak terdaftar dalam sistem.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - SI-APIC Pengadilan Negeri Yogyakarta</title>
    
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- RESET --- */
        * { box-sizing: border-box; }

        :root {
            --pn-green: #006837;
            --pn-green-dark: #004d29;
            --pn-gold: #F9A825;
            --bg-color: #f0f4f3;
            --text-dark: #2c3e50;
            --text-muted: #7f8c8d;
        }

        body {
            background-color: var(--bg-color);
            
            /* --- BACKGROUND IMAGE SETTING --- */
            /* Menggunakan Linear Gradient hitam transparan (0.6) di atas gambar agar tulisan lebih terbaca */
            background-image: linear-gradient(rgba(0, 50, 20, 0.7), rgba(0, 50, 20, 0.7)), url('assets/img/pn-yk.jpg');
            
            /* Agar gambar memenuhi layar dan responsive */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            /* -------------------------------- */

            font-family: 'Poppins', sans-serif;
            height: 100vh; 
            width: 100vw;
            overflow: hidden; /* No Scroll */
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        /* --- CARD LOGIN --- */
        .login-container {
            /* Sedikit transparansi pada card agar menyatu background, tapi tetap terbaca (opsional) */
            background: rgba(255, 255, 255, 0.95);
            width: 90%;
            max-width: 500px;
            border-radius: 20px;
            /* Efek backdrop blur (blur background di belakang kaca) */
            backdrop-filter: blur(5px); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.3); /* Shadow lebih gelap sedikit */
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 30px 45px;
            max-height: 98vh;
        }

        /* --- GRADASI BAR --- */
        .top-bar {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 8px;
            background: linear-gradient(90deg, var(--pn-green) 0%, var(--pn-gold) 100%);
        }

        /* --- HEADER AREA --- */
        .header-area {
            text-align: center;
            margin-bottom: 2vh; 
        }

        /* LOGO BESAR */
        .logo-img {
            height: 12vh;
            max-height: 130px;
            width: auto;
            margin-bottom: 10px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }

        /* JUDUL */
        .app-name {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--pn-green);
            line-height: 1;
            margin: 0;
            letter-spacing: -1px;
        }
        
        /* DESKRIPSI */
        .app-desc {
            font-size: 1.1rem;
            color: var(--text-dark);
            margin-top: 8px;
            font-weight: 500;
            line-height: 1.4;
        }

        /* --- FORM AREA --- */
        .form-content {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 1rem;
        }

        .input-wrapper { position: relative; }

        .form-input {
            width: 100%;
            height: 55px;
            padding: 10px 50px;
            border: 2px solid #eaecf0;
            border-radius: 14px;
            font-size: 1.1rem;
            color: #333;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--pn-green);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(0, 104, 55, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #98a2b3;
            font-size: 1.3rem;
        }
        
        .toggle-password {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #98a2b3;
            font-size: 1.2rem;
        }
        .toggle-password:hover { color: var(--pn-green); }

        /* --- BUTTON --- */
        .btn-submit {
            width: 100%;
            height: 60px;
            background: var(--pn-green);
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-top: 10px;
            box-shadow: 0 6px 15px rgba(0, 104, 55, 0.25);
        }

        .btn-submit:hover {
            background: var(--pn-green-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 104, 55, 0.35);
        }

        /* --- ALERTS --- */
        .alert-box {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.95rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer {
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--text-muted);
            text-align: center;
        }

        /* RESPONSIF */
        @media screen and (max-height: 720px) {
            .login-container { padding: 20px 30px; }
            .logo-img { height: 70px; margin-bottom: 5px; }
            .app-name { font-size: 2rem; }
            .app-desc { font-size: 0.95rem; margin-top: 5px; }
            .form-input { height: 48px; font-size: 1rem; }
            .btn-submit { height: 50px; font-size: 1.1rem; }
            .form-content { gap: 10px; }
            .footer { margin-top: 10px; font-size: 0.8rem; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        
        <div class="top-bar"></div>
        
        <div class="header-area">
            <?php if(file_exists("assets/img/logo.png")): ?>
                <img src="assets/img/logo.png" alt="Logo PNYK" class="logo-img">
            <?php else: ?>
                <i class="fas fa-balance-scale" style="font-size: 80px; color: var(--pn-gold); margin-bottom:10px;"></i>
            <?php endif; ?>

            <h1 class="app-name">SI-APIC</h1>
            <p class="app-desc">Administrasi Pelayanan Izin Cuti<br>Pengadilan Negeri Yogyakarta</p>
        </div>

        <?php if($error_msg != null): ?>
            <div class="alert-box">
                <i class="fas fa-exclamation-triangle"></i> <span><?php echo $error_msg; ?></span>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == "belum_login"): ?>
            <div class="alert-box" style="background-color: #fffbeb; color: #b45309; border-color: #fcd34d;">
                <i class="fas fa-info-circle"></i> <span>Silakan login terlebih dahulu.</span>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == "logout"): ?>
            <div class="alert-box" style="background-color: #ecfdf5; color: #047857; border-color: #6ee7b7;">
                <i class="fas fa-check-circle"></i> <span>Anda telah berhasil logout.</span>
            </div>
        <?php endif; ?>

        <form action="" method="post" class="form-content">
            
            <div>
                <label class="form-label" for="nip">NIP</label>
                <div class="input-wrapper">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" id="nip" name="nip" class="form-input" placeholder="Masukkan NIP" inputmode="numeric" required autocomplete="off">
                </div>
            </div>

            <div>
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan Password" required>
                    <i class="fas fa-eye-slash toggle-password" onclick="togglePass()" id="eye-icon"></i>
                </div>
            </div>

            <button type="submit" name="login" class="btn-submit">
                MASUK SEKARANG <i class="fas fa-arrow-right"></i>
            </button>

        </form>

        <div class="footer">
            &copy; <?php echo date('Y'); ?> Pengadilan Negeri Yogyakarta
        </div>

    </div>

    <script>
        function togglePass() {
            var passInput = document.getElementById("password");
            var icon = document.getElementById("eye-icon");
            if (passInput.type === "password") {
                passInput.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
                icon.style.color = "#006837";
            } else {
                passInput.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
                icon.style.color = "#98a2b3";
            }
        }
    </script>

</body>
</html>