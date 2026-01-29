<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
// --- LOGIKA PHP TETAP ---
if (!isset($_SESSION['id_user'])) {
    echo "<script>window.location='login.php';</script>";
    exit;
}

$id_user_login = $_SESSION['id_user'];
$query_profil = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user_login'");
$data_profil  = mysqli_fetch_array($query_profil);

$swal_script = ""; 

if (isset($_POST['simpan_password'])) {
    $pass_lama_input = $_POST['pass_lama'];
    $pass_baru       = $_POST['pass_baru'];
    $konfirmasi      = $_POST['konfirmasi'];
    $pass_db = $data_profil['password'];

    $cek_biasa = ($pass_lama_input == $pass_db);
    $cek_md5   = (md5($pass_lama_input) == $pass_db);
    $cek_hash  = password_verify($pass_lama_input, $pass_db);

    if (!$cek_biasa && !$cek_md5 && !$cek_hash) {
        $swal_script = "<script>Swal.fire({icon: 'error', title: 'Gagal!', text: 'Password lama salah.', confirmButtonColor: '#d33'});</script>";
    } else if ($pass_baru != $konfirmasi) {
        $swal_script = "<script>Swal.fire({icon: 'warning', title: 'Tidak Cocok', text: 'Konfirmasi password baru tidak sama.', confirmButtonColor: '#f6c23e'});</script>";
    } else if (strlen($pass_baru) < 6) {
         $swal_script = "<script>Swal.fire({icon: 'info', title: 'Terlalu Pendek', text: 'Password minimal 6 karakter.', confirmButtonColor: '#006B3F'});</script>";
    } else {
        $pass_hash = password_hash($pass_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($koneksi, "UPDATE users SET password='$pass_hash' WHERE id_user='$id_user_login'");
        if ($update) {
            $swal_script = "
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Password diperbarui. Silakan login ulang.',
                        confirmButtonColor: '#006B3F',
                        confirmButtonText: 'Logout Sekarang'
                    }).then((result) => {
                        if (result.isConfirmed) { window.location.href = 'logout.php'; }
                    });
                </script>";
        }
    }
}
?>

<style>
    :root {
        --pn-green: #004d00;
        --pn-green-light: #006B3F;
        --pn-gold: #FFD700;
        --soft-green: #e8f5e9; 
        --soft-gold: #fff9db;
    }

    .page-title-pn {
        font-weight: 700;
        border-left: 5px solid var(--pn-gold);
        padding-left: 15px;
        color: var(--pn-green) !important;
    }

    .card-pn {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .card-header-pn {
        background: linear-gradient(135deg, var(--pn-green) 0%, var(--pn-green-light) 100%);
        color: white;
        border-bottom: 4px solid var(--pn-gold);
        padding: 15px 20px;
        border-radius: 15px 15px 0 0;
    }

    .input-group-seamless {
        border: 1px solid #d1d3e2;
        border-radius: 15px; 
        background-color: #fff;
        display: flex;
        align-items: center;
        overflow: hidden;
    }
    .input-group-seamless:focus-within {
        border-color: var(--pn-green-light);
        box-shadow: 0 0 0 3px rgba(0, 107, 63, 0.1);
    }
    .input-group-seamless .form-control { border: none; box-shadow: none; background: transparent; padding: 12px; }
    .input-group-seamless .input-group-text { background: transparent; border: none; color: #b0b3b8; }

    .btn-brand { 
        background: linear-gradient(135deg, var(--pn-green) 0%, var(--pn-green-light) 100%);
        border: none; color: #fff; font-weight: 600; border-radius: 15px; padding: 12px;
    }
    .btn-brand:hover { color: var(--pn-gold); opacity: 0.9; }
    
    /* STYLE KOTAK PROFIL LAMA */
    .profile-circle {
        width: 80px; height: 80px; 
        background-color: var(--soft-green);
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; color: var(--pn-green-light); font-size: 2rem;
    }

    /* TIPS DENGAN SOROTAN KUNING */
    .card-tips {
        background-color: var(--soft-gold);
        border: 1px solid var(--pn-gold);
        border-left: 5px solid var(--pn-gold);
        border-radius: 12px;
    }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 page-title-pn">Pengaturan Akun</h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card card-pn mb-4">
            <div class="card-header-pn">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-key mr-2"></i>Form Ganti Password</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-gray-700 ml-1">Password Lama</label>
                        <div class="input-group input-group-seamless">
                            <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                            <input type="password" name="pass_lama" id="passLama" class="form-control" placeholder="Masukkan password saat ini" required>
                            <div class="input-group-append">
                                <span class="input-group-text" style="cursor:pointer" onclick="togglePass('passLama', 'iconLama')">
                                    <i class="fas fa-eye" id="iconLama"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label class="small font-weight-bold text-gray-700 ml-1">Password Baru</label>
                            <div class="input-group input-group-seamless">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-key"></i></span></div>
                                <input type="password" name="pass_baru" id="passBaru" class="form-control" placeholder="Min. 6 karakter" required>
                                <div class="input-group-append">
                                    <span class="input-group-text" style="cursor:pointer" onclick="togglePass('passBaru', 'iconBaru')">
                                        <i class="fas fa-eye" id="iconBaru"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-4">
                            <label class="small font-weight-bold text-gray-700 ml-1">Konfirmasi Password</label>
                            <div class="input-group input-group-seamless">
                                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-check-circle"></i></span></div>
                                <input type="password" name="konfirmasi" id="passKonfirm" class="form-control" placeholder="Ketik ulang" required>
                                <div class="input-group-append">
                                    <span class="input-group-text" style="cursor:pointer" onclick="togglePass('passKonfirm', 'iconKonfirm')">
                                        <i class="fas fa-eye" id="iconKonfirm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="simpan_password" class="btn btn-brand btn-block shadow-sm">SIMPAN PERUBAHAN</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <div class="profile-circle"><i class="fas fa-user"></i></div>
                </div>
                <h5 class="font-weight-bold text-gray-800 mb-0"><?php echo $data_profil['nama_lengkap']; ?></h5>
                <p class="text-muted small mb-2 mt-1">NIP. <?php echo $data_profil['nip']; ?></p>
                <div class="text-xs font-weight-bold text-uppercase text-gray-500 mb-3">
                    <?php echo ($data_profil['role'] == 'admin') ? "Administrator Sistem" : "Pegawai / Staff"; ?> 
                    <br> 
                    <i class="fas fa-phone fa-fw mt-1"></i> <?php echo $data_profil['no_telepon']; ?>
                </div>
                <span class="badge badge-pill badge-light text-success px-3 py-2 border">
                    <i class="fas fa-circle text-success mr-1" style="font-size: 8px;"></i> Akun Aktif
                </span>
            </div>
        </div>
        
        <div class="card shadow-sm card-tips mb-4">
            <div class="card-body">
                <h6 class="font-weight-bold text-warning mb-3 small text-uppercase">
                    <i class="fas fa-shield-alt mr-2"></i>Tips Keamanan
                </h6>
                <ul class="list-unstyled small text-dark mb-0" style="line-height: 1.7;">
                    <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i>Min. 6 Karakter</li>
                    <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i>Gunakan Kombinasi Unik</li>
                    <li><i class="fas fa-check-circle text-success mr-2"></i>Jangan Berikan ke Orang Lain</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePass(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        } else {
            input.type = "password";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        }
    }
</script>

<?php if($swal_script != "") echo $swal_script; ?>