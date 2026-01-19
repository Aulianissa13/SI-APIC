<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
// --- 2. LOGIKA PHP DENGAN SWEETALERT ---
$id_user_login = $_SESSION['id_user'];
$query_profil = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id_user_login'");
$data_profil  = mysqli_fetch_array($query_profil);

// Variabel penampung script alert
$swal_script = ""; 

if (isset($_POST['simpan_password'])) {
    $pass_lama  = md5($_POST['pass_lama']); 
    $pass_baru  = md5($_POST['pass_baru']);
    $konfirmasi = md5($_POST['konfirmasi']);

    if ($pass_lama != $data_profil['password']) {
        // GAGAL: Password Lama Salah
        $swal_script = "
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Password lama yang Anda masukkan salah.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Coba Lagi'
                });
            </script>";
            
    } else if ($pass_baru != $konfirmasi) {
        // GAGAL: Konfirmasi Tidak Cocok
        $swal_script = "
            <script>
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Cocok',
                    text: 'Konfirmasi password baru tidak sama.',
                    confirmButtonColor: '#f6c23e',
                    confirmButtonText: 'Perbaiki'
                });
            </script>";

    } else if (strlen($_POST['pass_baru']) < 6) {
        // GAGAL: Kurang dari 6 karakter
         $swal_script = "
            <script>
                Swal.fire({
                    icon: 'info',
                    title: 'Terlalu Pendek',
                    text: 'Password minimal harus 6 karakter.',
                    confirmButtonColor: '#36b9cc',
                    confirmButtonText: 'Oke'
                });
            </script>";

    } else {
        // SUKSES
        $update = mysqli_query($koneksi, "UPDATE users SET password='$pass_baru' WHERE id_user='$id_user_login'");
        if ($update) {
            $swal_script = "
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Password Anda telah diperbarui.',
                        confirmButtonColor: '#1e5c3e', // Warna Logo
                        confirmButtonText: 'Selesai'
                    });
                </script>";
        }
    }
}
?>

<style>
    :root {
        --main-color: #1e5c3e; 
        --soft-green: #e8f5e9; 
    }
    .input-group-seamless {
        border: 1px solid #d1d3e2;
        border-radius: 15px; 
        background-color: #fff;
        display: flex;
        align-items: center;
        overflow: hidden;
        transition: border-color 0.2s;
    }
    .input-group-seamless:focus-within {
        border-color: var(--main-color);
        box-shadow: 0 0 0 3px rgba(30, 92, 62, 0.1);
    }
    .input-group-seamless .input-group-text {
        background-color: transparent; border: none; color: #b0b3b8; padding-left: 15px; 
    }
    .input-group-seamless .form-control {
        border: none; box-shadow: none; background-color: transparent; color: #5a5c69; height: auto;
    }
    .input-group-seamless:focus-within .input-group-text i { color: var(--main-color); }

    .btn-brand { 
        background-color: var(--main-color); border-color: var(--main-color); color: #fff; 
        font-weight: 600; border-radius: 15px; padding: 0.375rem 0.75rem; 
    }
    .btn-brand:hover { background-color: #14402b; border-color: #14402b; color: #fff; }
    
    .border-top-brand { border-top: 3px solid var(--main-color) !important; }
    .text-brand { color: var(--main-color) !important; }
    .toggle-password { cursor: pointer; padding: 0.375rem 0.75rem; }
    .toggle-password:hover { color: var(--main-color); }
</style>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Pengaturan Akun</h1>
</div>

<div class="row">

    <div class="col-lg-8">
        <div class="card shadow-sm mb-4 border-top-brand" style="border-radius: 15px;">
            <div class="card-header py-3 bg-white border-bottom-0" style="border-radius: 15px 15px 0 0;">
                <h6 class="m-0 font-weight-bold text-brand">
                    <i class="fas fa-key mr-2"></i>Form Ganti Password
                </h6>
            </div>
            <div class="card-body p-4 pt-0">
                <p class="text-muted small mb-4">Amankan akun Anda dengan memperbarui password secara berkala.</p>
                
                <form method="POST" action="">
                    
                    <div class="form-group mb-4">
                        <label class="small font-weight-bold text-gray-600 ml-1">Password Lama</label>
                        <div class="input-group input-group-seamless">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            </div>
                            <input type="password" name="pass_lama" id="passLama" class="form-control" placeholder="Masukkan password saat ini" required>
                            <div class="input-group-append">
                                <span class="input-group-text toggle-password" onclick="togglePass('passLama', 'iconLama')">
                                    <i class="fas fa-eye" id="iconLama"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group mb-4">
                            <label class="small font-weight-bold text-gray-600 ml-1">Password Baru</label>
                            <div class="input-group input-group-seamless">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                </div>
                                <input type="password" name="pass_baru" id="passBaru" class="form-control" placeholder="Min. 6 karakter" required>
                                <div class="input-group-append">
                                    <span class="input-group-text toggle-password" onclick="togglePass('passBaru', 'iconBaru')">
                                        <i class="fas fa-eye" id="iconBaru"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 form-group mb-4">
                            <label class="small font-weight-bold text-gray-600 ml-1">Konfirmasi Password</label>
                            <div class="input-group input-group-seamless">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                </div>
                                <input type="password" name="konfirmasi" id="passKonfirm" class="form-control" placeholder="Ketik ulang" required>
                                <div class="input-group-append">
                                    <span class="input-group-text toggle-password" onclick="togglePass('passKonfirm', 'iconKonfirm')">
                                        <i class="fas fa-eye" id="iconKonfirm"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="mt-0 mb-4 border-light">

                    <button type="submit" name="simpan_password" class="btn btn-brand btn-block shadow-sm py-2">
                        SIMPAN PERUBAHAN
                    </button>

                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; background-color: var(--soft-green);">
                        <i class="fas fa-user text-brand" style="font-size: 2rem;"></i>
                    </div>
                </div>
                <h5 class="font-weight-bold text-gray-800 mb-0"><?php echo $data_profil['nama_lengkap']; ?></h5>
                <p class="text-muted small mb-2 mt-1">NIP. <?php echo $data_profil['nip']; ?></p>
                <div class="text-xs font-weight-bold text-uppercase text-gray-500 mb-3">
                    <?php echo $data_profil['jabatan']; ?> <br> <?php echo $data_profil['unit_kerja']; ?>
                </div>
                <span class="badge badge-pill badge-light text-success px-3 py-2 border">
                    <i class="fas fa-circle text-success mr-1" style="font-size: 8px;"></i> Pegawai Aktif
                </span>
            </div>
        </div>
        
        <div class="card shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-body">
                <h6 class="font-weight-bold text-gray-700 mb-3 small text-uppercase">
                    <i class="fas fa-shield-alt mr-2 text-muted"></i>Tips Keamanan
                </h6>
                <ul class="list-unstyled small text-gray-600 mb-0">
                    <li class="mb-2"><i class="fas fa-check text-success mr-2"></i>Min. 6 Karakter</li>
                    <li class="mb-2"><i class="fas fa-check text-success mr-2"></i>Gunakan Angka & Huruf</li>
                    <li><i class="fas fa-check text-success mr-2"></i>Rahasiakan Password</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePass(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash"); 
        } else {
            passwordInput.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye"); 
        }
    }
</script>

<?php if($swal_script != "") { echo $swal_script; } ?>