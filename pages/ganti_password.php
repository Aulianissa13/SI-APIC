<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
/** @var mysqli $koneksi */

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
    $pass_db         = $data_profil['password'];

    $cek_biasa = ($pass_lama_input == $pass_db);
    $cek_md5   = (md5($pass_lama_input) == $pass_db);
    $cek_hash  = password_verify($pass_lama_input, $pass_db);

    if (!$cek_biasa && !$cek_md5 && !$cek_hash) {
        $swal_script = "<script>Swal.fire({icon: 'error', title: 'Gagal!', text: 'Password lama salah.', confirmButtonColor: '#d33'});</script>";
    } else if ($pass_baru != $konfirmasi) {
        $swal_script = "<script>Swal.fire({icon: 'warning', title: 'Tidak Cocok', text: 'Konfirmasi password baru tidak sama.', confirmButtonColor: '#F9A825'});</script>";
    } else if (strlen($pass_baru) < 6) {
        $swal_script = "<script>Swal.fire({icon: 'info', title: 'Terlalu Pendek', text: 'Password minimal 6 karakter.', confirmButtonColor: '#004d00'});</script>";
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
                        confirmButtonColor: '#004d00',
                        confirmButtonText: 'Logout Sekarang'
                    }).then((result) => {
                        if (result.isConfirmed) { window.location.href = 'logout.php'; }
                    });
                </script>";
        }
    }
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --pn-green: #004d00;
        --pn-dark-green: #003300;
        --pn-gold: #F9A825;
        --pn-gold-dark: #F9A825;
    }

    body{
        font-family: 'Poppins', sans-serif !important;
        background-color: #f4f6f9;
    }

    .compact-container { max-width: 1100px; margin: 0 auto; }

    .page-header-title{
        border-left: 5px solid var(--pn-gold);
        padding-left: 15px;
        color: var(--pn-green);
        font-weight: 700;
        font-size: 1.6rem;
        margin: 0;
    }

    .card-clean{
        border: none;
        border-radius: 10px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        overflow: hidden;
        background: #fff;
        height: fit-content;
    }

    .card-header-pn{
        background-color: var(--pn-green);
        color: #fff;
        border-bottom: 4px solid var(--pn-gold);
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .input-group-seamless{
        border: 1px solid #ddd;
        border-radius: 50px;
        background-color: #f8f9fc;
        display: flex;
        align-items: center;
        overflow: hidden;
        margin-bottom: 5px;
    }
    .input-group-seamless .form-control{
        border: none;
        box-shadow: none;
        background: transparent;
        padding: 12px 20px;
        font-size: 14px;
    }
    .input-group-seamless .input-group-text{
        background: transparent;
        border: none;
        color: #aaa;
        padding-left: 20px;
    }

    .btn-brand{
        background: linear-gradient(45deg, var(--pn-green), var(--pn-dark-green));
        border: none;
        color: #fff;
        font-weight: 600;
        border-radius: 50px;
        padding: 14px;
        transition: all .3s;
        box-shadow: 0 4px 6px rgba(0,0,0,0.15);
        margin-top: 10px;
    }
    .btn-brand:hover{
        color: var(--pn-gold);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.2);
    }

    .badge-status-pn{
        border-radius: 50px;
        padding: 6px 18px;
        font-weight: 600;
        font-size: 11px;
    }

    /* ====== UPDATE PROFIL FOTO: SAMA KAYAK FORM CUTI ====== */
    .profile-avatar-wrap{
        position: relative;
        display: inline-block;
        width: 80px;
        height: 80px;
    }
    .profile-avatar{
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 3px solid var(--pn-gold);
        object-fit: cover;
        box-shadow: 0 3px 15px rgba(0,0,0,0.10);
        background: #fff;
    }
    .profile-status-dot{
        width: 15px;
        height: 15px;
        background: var(--pn-green);
        border-radius: 50%;
        position: absolute;
        bottom: 0;
        right: 0;
        border: 2px solid #fff;
    }

    /* TIPS KEAMANAN */
    .card-tips{
        background: #fff8e6;
        border: 1px solid rgba(249,168,37,.25);
        border-left: 8px solid var(--pn-gold);
        border-radius: 18px;
        box-shadow: 0 10px 22px rgba(0,0,0,.10);
        overflow: hidden;
    }
    .card-tips .card-body{ padding: 18px 20px !important; }
    .card-tips h6{ color: #6f5a23 !important; }
    .card-tips ul li{ color: #6b5a2b; }
    .card-tips .fa-check-circle{ color: var(--pn-gold) !important; }
</style>

<div class="container-fluid mb-5 mt-4">
    <div class="compact-container">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="page-header-title">Pengaturan Akun</h1>
        </div>

        <div class="row align-items-start">
            <!-- KIRI: FORM -->
            <div class="col-lg-7 mb-4">
                <div class="card card-clean">
                    <div class="card-header-pn">
                        <span class="font-weight-bold" style="font-size: 1.05rem;">
                            <i class="fas fa-key mr-2"></i>Form Ganti Password
                        </span>
                        <i class="fas fa-user-cog text-white-50"></i>
                    </div>

                    <div class="card-body p-4">
                        <form method="POST" action="" class="mb-0">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-dark ml-2">Password Lama</label>
                                <div class="input-group-seamless">
                                    <div class="input-group-text"><i class="fas fa-lock"></i></div>
                                    <input type="password" name="pass_lama" id="passLama" class="form-control" placeholder="Masukkan password saat ini" required>
                                    <span class="input-group-text pr-3" style="cursor:pointer" onclick="togglePass('passLama', 'iconLama')">
                                        <i class="fas fa-eye-slash" id="iconLama"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="small font-weight-bold text-dark ml-2">Password Baru</label>
                                    <div class="input-group-seamless">
                                        <div class="input-group-text"><i class="fas fa-key"></i></div>
                                        <input type="password" name="pass_baru" id="passBaru" class="form-control" placeholder="Min. 6 karakter" required>
                                        <span class="input-group-text pr-3" style="cursor:pointer" onclick="togglePass('passBaru', 'iconBaru')">
                                            <i class="fas fa-eye-slash" id="iconBaru"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-6 form-group mb-3">
                                    <label class="small font-weight-bold text-dark ml-2">Konfirmasi Password</label>
                                    <div class="input-group-seamless">
                                        <div class="input-group-text"><i class="fas fa-check-circle"></i></div>
                                        <input type="password" name="konfirmasi" id="passKonfirm" class="form-control" placeholder="Ketik ulang" required>
                                        <span class="input-group-text pr-3" style="cursor:pointer" onclick="togglePass('passKonfirm', 'iconKonfirm')">
                                            <i class="fas fa-eye-slash" id="iconKonfirm"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" name="simpan_password" class="btn btn-brand btn-block">
                                <i class="fas fa-save mr-2"></i> SIMPAN PERUBAHAN
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- KANAN: PROFIL + TIPS -->
            <div class="col-lg-5">
                <div class="card card-clean mb-4">
                    <div class="card-header-pn">
                        <span class="font-weight-bold" style="font-size: 1.05rem;">
                            <i class="fas fa-id-badge mr-2"></i>Profil Pengguna
                        </span>
                        <i class="fas fa-user text-white-50"></i>
                    </div>

                    <div class="card-body text-center p-4">
                        <!-- FOTO PROFIL MODEL FORM CUTI -->
                        <div class="profile-avatar-wrap mb-3">
                            <img
                                class="profile-avatar"
                                src="assets/img/undraw_profile.svg"
                                alt="Foto Profil"
                            >
                            <span class="profile-status-dot"></span>
                        </div>

                        <h5 class="font-weight-bold text-dark mb-1"><?php echo $data_profil['nama_lengkap']; ?></h5>
                        <p class="text-muted small mb-3">NIP: <?php echo $data_profil['nip']; ?></p>

                        <div class="mb-3">
                            <span class="badge-status-pn" style="background-color: #e8f5e9; color: #004d00;">
                                <i class="fas fa-user-shield mr-1"></i> <?php echo strtoupper($data_profil['role']); ?>
                            </span>
                        </div>

                        <hr class="my-3">

                        <span class="badge-status-pn" style="background-color: #d4edda; color: #155724;">
                            <i class="fas fa-check-circle mr-1"></i> AKUN TERVERIFIKASI
                        </span>
                    </div>
                </div>

                <div class="card card-tips">
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-3 small text-uppercase" style="letter-spacing: 1px;">
                            <i class="fas fa-shield-alt mr-2" style="color: var(--pn-gold);"></i>Tips Keamanan
                        </h6>
                        <ul class="list-unstyled small mb-0" style="line-height: 1.8;">
                            <li><i class="fas fa-check-circle mr-2"></i> Gunakan minimal <b>6 karakter</b></li>
                            <li><i class="fas fa-check-circle mr-2"></i> Kombinasi <b>Huruf & Angka</b></li>
                            <li><i class="fas fa-check-circle mr-2"></i> Jangan berikan password ke orang lain</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
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