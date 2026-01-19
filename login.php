<?php
session_start();
include 'config/database.php';

// Jika sudah login, lempar ke index
if(isset($_SESSION['status']) && $_SESSION['status'] == "login"){
    header("location:index.php");
}

if(isset($_POST['login'])){
    // 1. Tangkap inputan NIP
    $nip = $_POST['nip'];
    $password = md5($_POST['password']); 

    // 2. Query cek ke database menggunakan kolom 'nip'
    // Pastikan di database tabel 'users' nama kolomnya benar-benar 'nip'
    $login = mysqli_query($koneksi, "SELECT * FROM users WHERE nip='$nip' AND password='$password'");
    $cek = mysqli_num_rows($login);

    if($cek > 0){
        $data = mysqli_fetch_assoc($login);
        
        // 3. Simpan NIP ke session
        $_SESSION['nip'] = $nip;
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['status'] = "login";

        // Redirect sesuai role
        if($data['role'] == "admin"){
            header("location:index.php?page=dashboard_admin");
        }else{
            header("location:index.php?page=dashboard_user");
        }
    }else{
        $error_msg = "NIP atau Password salah / tidak terdaftar.";
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- CSS TETAP SAMA SEPERTI SEBELUMNYA (MODERN & INKLUSIF) --- */
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
            background-image: radial-gradient(#e0e7e4 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-container {
            background: #ffffff;
            width: 100%;
            max-width: 500px;
            border-radius: 24px;
            box-shadow: 
                0 4px 6px -1px rgba(0, 0, 0, 0.05),
                0 10px 15px -3px rgba(0, 0, 0, 0.05),
                0 0 0 1px rgba(0, 0, 0, 0.02); 
            overflow: hidden;
            position: relative;
            padding: 50px 40px;
            text-align: center;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--pn-green) 0%, var(--pn-gold) 100%);
        }

        .logo-wrapper {
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }
        .logo-wrapper:hover { transform: scale(1.05); }
        
        .logo-img {
            width: 140px;
            height: auto;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }

        .app-name {
            font-size: 2rem;
            font-weight: 800;
            color: var(--pn-green);
            letter-spacing: -0.5px;
            margin: 0;
            line-height: 1.2;
        }
        
        .app-desc {
            font-size: 1rem;
            color: var(--text-dark);
            margin-top: 5px;
            margin-bottom: 35px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.95rem;
            padding-left: 5px;
        }

        .input-wrapper { position: relative; }

        .form-input {
            width: 100%;
            height: 60px;
            padding: 10px 20px 10px 55px;
            border: 2px solid #eaecf0;
            border-radius: 16px;
            font-size: 1.1rem;
            color: #333;
            background-color: #fcfcfd;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--pn-green);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(0, 104, 55, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: #98a2b3;
        }
        
        .toggle-password {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #98a2b3;
            font-size: 1.2rem;
            padding: 10px;
        }
        .toggle-password:hover { color: var(--pn-green); }

        .btn-submit {
            width: 100%;
            height: 60px;
            background: var(--pn-green);
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            border: none;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(0, 104, 55, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-submit:hover {
            background: var(--pn-green-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 104, 55, 0.3);
        }

        .alert-box {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            padding: 15px;
            border-radius: 12px;
            font-size: 0.95rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer {
            margin-top: 30px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        @media (max-width: 480px) {
            .login-container { padding: 30px 20px; }
            .app-name { font-size: 1.7rem; }
            .logo-img { width: 110px; }
            .form-input, .btn-submit { height: 55px; }
        }
    </style>
</head>
<body>

    <div class="login-container">
        
        <div class="logo-wrapper">
            <?php if(file_exists("assets/img/logo.png")): ?>
                <img src="assets/img/logo.png" alt="Logo PNYK" class="logo-img">
            <?php else: ?>
                <i class="fas fa-balance-scale" style="font-size: 80px; color: var(--pn-gold);"></i>
            <?php endif; ?>
        </div>

        <h1 class="app-name">SI-APIC</h1>
        <p class="app-desc">Administrasi Pelayanan Izin Cuti<br>Pengadilan Negeri Yogyakarta</p>

        <?php if(isset($error_msg)): ?>
            <div class="alert-box">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error_msg; ?></span>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == "belum_login"): ?>
            <div class="alert-box" style="background-color: #fffbeb; color: #b45309; border-color: #fcd34d;">
                <i class="fas fa-info-circle"></i>
                <span>Silakan login terlebih dahulu.</span>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            
            <div class="form-group">
                <label class="form-label" for="nip">NIP</label>
                <div class="input-wrapper">
                    <i class="fas fa-id-card input-icon"></i>
                    <input type="text" id="nip" name="nip" class="form-input" placeholder="Masukkan NIP Anda" inputmode="numeric" required autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan Password" required>
                    <i class="fas fa-eye toggle-password" onclick="togglePass()" id="eye-icon"></i>
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
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
                icon.style.color = "#006837"; 
            } else {
                passInput.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
                icon.style.color = "#98a2b3"; 
            }
        }
    </script>

</body>
</html>