<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login SI-APIC - Pengadilan Negeri Yogyakarta</title>

    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        .bg-login-image { background: url('https://upload.wikimedia.org/wikipedia/commons/thumb/0/08/Logo_Mahkamah_Agung_RI.png/600px-Logo_Mahkamah_Agung_RI.png'); background-position: center; background-size: contain; background-repeat: no-repeat; }
        .bg-gradient-primary { background-color: #006B3F; background-image: linear-gradient(180deg, #006B3F 10%, #004d2d 100%); }
        .btn-primary { background-color: #006B3F; border-color: #006B3F; }
        .btn-primary:hover { background-color: #004d2d; border-color: #004d2d; }
    </style>
</head>

<body class="bg-gradient-primary">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Selamat Datang di <b>SI-APIC</b></h1>
                                        <p class="mb-4">Sistem Informasi Administrasi Pelayanan Izin Cuti</p>
                                    </div>
                                    
                                    <form class="user" action="cek_login.php" method="POST">
                                        <div class="form-group">
                                            <input type="text" name="nip" class="form-control form-control-user" placeholder="Masukkan NIP..." required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="password" class="form-control form-control-user" placeholder="Password" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login Masuk
                                        </button>
                                    </form>
                                    
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="#">Lupa Password? Hubungi Bagian Kepegawaian</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/vendor/jquery/jquery.min.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="assets/js/sb-admin-2.min.js"></script>

</body>
</html>