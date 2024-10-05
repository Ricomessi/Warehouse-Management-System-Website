<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'firebaseconfig.php';

// Fetch staff data
$staffRef = $database->getReference('users');
$snapshot = $staffRef->getSnapshot();
$staff = $snapshot->exists() ? $snapshot->getValue() : [];

$successMessage = '';
if (isset($_SESSION['success']) && $_SESSION['success'] === true) {
    $successMessage = 'Data berhasil disimpan!';
    unset($_SESSION['success']);
}
?>
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/mainAdmin.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
    <link defer rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="icon" href="img/logo.png" type="image/png" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Basic */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

        :root {
            --color-main: #409fdc;
            --main-accent: #c3e7fd;
            --bg: #f5eedc;
            --bg-2: #dce5ff;
            --main: #292c6d;
            --shadow: rgba(17, 17, 26, 0.1) 2px 0px 16px;
        }

        * {
            padding: 0;
            margin: 0;
            text-decoration: none;
            font-family: "Poppins", sans-serif;
            list-style-type: none;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg);
            overflow-x: hidden;
        }

        .gradienHabibi {
            background-image: linear-gradient(to right, #e4ecfc 0%, #e4f3fc 100%);
        }

        img {
            max-width: 100%;
            height: auto;
        }

        #menu-toggle {
            display: block;
        }

        #menu-toggle:checked~.sidebar {
            left: -345px;
        }

        #menu-toggle:checked~.main-content {
            margin-left: 0;
            width: 100vw;
        }

        .overlay {
            position: fixed;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            z-index: 10;
            display: none;
            background-color: rgba(255, 255, 255, 0.5);
        }

        /* helper */
        .text-danger {
            color: red;
        }

        .text-success {
            color: #2ec3a3;
        }

        .text-main {
            color: var(--color-main);
        }

        /* Sidebar */
        .sidebar {
            width: 345px;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            padding: 1rem 1.2rem;
            transition: left 300ms;
        }

        .sidebar-container {
            height: 100%;
            width: 100%;
            background-color: #fff;
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 1.2rem;
            overflow-y: auto;
        }

        .sidebar-container::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-container::-webkit-scrollbar-track {
            box-shadow: var(--shadow);
        }

        .sidebar-container::-webkit-scrollbar-thumb {
            background-color: var(--main-accent);
            outline: 1px solid #ccc;
            border-radius: 2px;
        }

        .brand {
            padding-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* img.brand-img {
  width: 20px;
  height: auto;


} */

        .brand h3 {
            color: #444;
            font-size: 3rem;
        }

        .brand h3 span {
            color: var(--color-main);
            font-size: 2.5rem;
            display: inline-block;
            margin-right: 0.5rem;
        }

        .sidebar-avatar {
            display: grid;
            grid-template-columns: 70px auto;
            align-items: center;
            border: 2px solid var(--main-accent);
            padding: 0.1rem 0.7rem;
            border-radius: 7px;
            margin: 2rem 0rem;
        }

        .sidebar-avatar img {
            width: 40px;
            border-radius: 10px;
            margin: 5px 0;
        }

        .avatar-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sidebar-menu li {
            margin-bottom: 2rem;
        }

        .sidebar-menu a {
            color: var(--main-text);
            display: block;
            padding: 0.7rem 0;
        }

        .sidebar-menu a.active {
            background-color: var(--main-accent);
            border-radius: 7px;
            padding: 0.8rem;
        }

        .sidebar-menu a span:first-child {
            display: inline-block;
            margin-right: 0.8rem;
            font-size: 1.5rem;
            color: var(--color-main);
        }

        .sidebar-card {
            background-color: var(--main-accent);
            padding: 1rem;
            margin-top: 2rem;
            box-shadow: var(--shadow);
            text-align: center;
            border-radius: 7px;
        }

        .side-card-icon span {
            font-size: 8rem;
            color: var(--color-main);
            display: inline-block;
        }

        .side-card-icon {
            margin-bottom: 0.8rem;
        }

        .side-card-icon+div {
            margin-bottom: 1rem;
        }

        .side-card-icon+div p {
            font-size: 0.8rem;
            color: #555;
        }

        .btn {
            padding: 0.7rem 1rem;
            border: none;
            border-radius: 10px;
            display: flex;
            align-items: center;
        }

        .btn span {
            font-size: 1.2rem;
            display: inline-block;
            margin-right: 0.7rem;
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .btn-main {
            background-color: var(--color-main);
            color: #fff;
        }

        /* .btn-main:hover {
  background-color: var(--color-main);
  color: #292c6d;
} */
        /* main */
        .main-content {
            margin-left: 345px;
            width: calc(100vw - 345px);
            padding: 1rem 2rem 1.2rem 1.2rem;
            transition: margin-left 300ms;
        }

        /* Header */
        header {
            display: flex;
            padding-top: 0.5rem;
            justify-content: space-between;
        }

        .header-wrapper {
            display: flex;
        }

        .header-wrapper label {
            display: inline-block;
            color: var(--color-main);
            margin-right: 2rem;
            font-size: 1.8rem;
        }

        .header-wrapper label span:hover {
            cursor: pointer;
        }

        .header-title h1 {
            color: var(--main-text);
            font-weight: 600;
        }

        .header-title p {
            color: #666;
            font-size: 0.9rem;
        }

        .header-title p span {
            color: red;
            font-size: 1.2rem;
            display: inline-block;
            margin-left: 0.5rem;
        }

        .header-action button {
            padding: 0.85rem 2rem;
            font-size: 1rem;
        }

        main {
            padding: 2.5rem 0;
        }

        .analytics {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-gap: 2.5rem;
            margin-bottom: 3rem;
        }

        .analytic {
            box-shadow: var(--shadow);
            padding: 1.5rem;
            border-radius: 10px;
            display: flex;
            padding-left: 2rem;
        }

        .analytic-info h4 {
            font-weight: 400;
            color: #555;
            font-size: 0.98rem;
        }

        .analytic-info h1 {
            color: var(--main-text);
            font-weight: 600;
        }

        .analytic-info h1 small {
            font-size: 0.8rem;
            font-weight: 700;
        }

        .analytic:first-child .analytic-icon {
            background-color: #dce5ff;
            color: #6883db;
        }

        .analytic:nth-child(2) .analytic-icon {
            background-color: #ebf7f5;
            color: red;
        }

        .analytic:nth-child(3) .analytic-icon {
            background-color: #ebf7f5;
            color: #2ec3a3;
        }

        .analytic:nth-child(4) .analytic-icon {
            background-color: var(--main-accent);
            color: var(--color-main);
        }

        .analytic-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            margin-right: 0.8rem;
        }

        .section-head {
            font-size: 1.4rem;
            color: var(--main);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .block-grid {
            display: grid;
            grid-gap: 3rem;
            grid-template-columns: 30% auto;
        }

        .rev-content {
            background-color: #fff;
            box-shadow: var(--shadow);
            border-radius: 15px;
            padding: 1rem 2rem;
            text-align: center;
        }

        .rev-content img {
            width: 180px;
            margin: 1.5rem 0;
            border-radius: 50%;
        }

        .rev-info {
            margin-bottom: 1rem;
        }

        .rev-info h3,
        .rev-sum h4 {
            font-weight: 600;
            color: var(--main);
        }

        .rev-info h1,
        .rev-sum h2 {
            font-weight: 400;
            margin-top: 0.4rem;
            color: #555;
        }

        .rev-info h1 small {
            font-size: 1rem;
        }

        .rev-sum {
            background-color: var(--main-accent);
            border: 1px solid var(--color-main);
            padding: 1rem;
            border-radius: 10px;
        }

        .graph-content {
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 1rem;
            background-color: #fff;
        }

        .graph-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .icon-wrapper {
            display: flex;
            align-items: center;
        }

        .icon {
            height: 40px;
            width: 40px;
            border-radius: 7px;
            background-color: #eee;
            display: grid;
            place-items: center;
            font-size: 1.2rem;
            margin-right: 0.8rem;
        }

        .graph-head select {
            outline: none;
            background-color: #eee;
            height: 35px;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            border: none;
        }

        .overlay label {
            display: block;
            height: 100%;
            width: 100%;
        }

        /* start tabel */
        section.bagian-tabel {
            --_setting-padding: 2rem;
            --_setting-width: 1540px;
            padding: var(--_setting-padding) 0;
        }

        .bungkus-tabel-luar {
            margin: 0 auto;
            min-height: 20vh;
            width: 100%;
            max-width: var(--_setting-width);
            padding: var(--_setting-padding);
            border-radius: 2rem;
            background-color: white;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
        }

        th {
            background-color: var(--bg-2);
            text-transform: capitalize;
        }

        tbody tr {
            cursor: pointer;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:hover {
            background-color: #f0f0f0;
        }

        /* end tabel */

        @media only screen and (max-width: 1224px) {
            .sidebar {
                left: -345px;
                z-index: 30;
            }

            .main-content {
                width: 100vw;
                margin-left: 0;
            }

            #menu-toggle:checked~.sidebar {
                left: 0;
            }

            #menu-toggle:checked~.overlay {
                display: block;
                text-align: right;
            }
        }

        @media only screen and (max-width: 860px) {
            .analytics {
                grid-template-columns: repeat(2, 1fr);
            }

            .block-grid {
                grid-template-columns: 100%;
            }

            .revenue-card {
                order: 2;
            }
        }

        @media only screen and (max-width: 580px) {
            .analytics {
                grid-template-columns: 100%;
            }
        }

        @media only screen and (max-width: 500px) {

            .header,
            header-wrapper {
                align-items: center;
            }

            .header-title h1 {
                font-size: 1.2em;
            }

            .header-title p {
                display: none;
            }
        }
    </style>
</head>

<body class="gradienHabibi">
    <input type="checkbox" name="" id="menu-toggle">
    <div class="overlay"><label for="menu"></label></div>
    <div class="sidebar">
        <div class="sidebar-container">
            <div class="brand">
                <img class="brand-img" src="img/logo.png" alt="logo" style="width:200px; margin-bottom:2rem">
            </div>
            <div class="sidebar-menu">
                <ul>
                    <li><a href="mainAdmin.php"><span class="las la-adjust"></span><span>Dashboard</span></a></li>
                    <li><a href="menuAdmin.php"><span class="ti ti-address-book"></span><span>Table Staff</span></a></li>
                    <li><a href="tableBarang.php"><span class="ti ti-address-book"></span><span>Table Barang</span></a></li>
                    <li><a href="historiStaff.php"><span class="ti ti-history"></span><span>History Staff</span></a></li>
                    <li><a href="kepuasanPelanggan.php"><span class="ti ti-heart"></span><span>Kepuasan Pelanggan</span></a></li>
                    <li><a href="#" class="active"><span class="ti ti-alert-triangle"></span><span>Tingkat Kesalahan</span></a></li>
                    <li><a href="bestStaff.php"><span class="ti ti-award"></span><span>Staff Terbaik Mingguan</span></a></li>
                </ul>
            </div>
            <div class="sidebar-card">
                <a onclick="confirmLogout()" class="btn btn-main btn-block">
                    <i class="ti ti-logout-2"></i> Log Out
                </a>
            </div>
        </div>
    </div>
    <div class="main-content">
        <header>
            <div class="header-wrapper">
                <label for="menu-toggle"><span class="las la-bars"></span></label>
                <div class="header-title">
                    <h1>Tingkat Kesalahan</h1>
                </div>
            </div>
        </header>
        <main>
            <section>
                <div class="container mt-4">
                    <form action="submitTIngkatKesalahan.php" method="post">
                        <div class="form-group mb-3">
                            <label for="staff">Pilih Staff</label>
                            <select name="staff" id="staff" class="form-control">
                                <?php foreach ($staff as $key => $value) { ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($key); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="kesalahan">Jumlah Kesalahan</label>
                            <select name="kesalahan" id="kesalahan" class="form-control" required>
                                <?php for ($i = 1; $i <= 10; $i++) { ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </section>

            <section class="bagian-tabel" id="tabelid1">
                <div class="bungkus-tabel-luar">
                    <div class="bungkus-tabel-dalem">
                        <table>
                            <thead>
                                <tr>
                                    <th>tingkat</th>
                                    <th>keterangan</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Sangat Sedikit Kesalahan</td>
                                    <td>
                                        Terdapat kesalahan yang sangat kecil dan tidak signifikan.
                                        <br> Kesalahan jarang terjadi dan tidak mengganggu proses secara keseluruhan.
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Sedikit Kesalahan</td>
                                    <td>
                                        Beberapa kesalahan kecil yang mudah diperbaiki.
                                        <br> Kesalahan ini tidak berdampak besar pada operasional.
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Kesalahan Minor</td>
                                    <td>
                                        Ada sejumlah kesalahan kecil yang tidak terlalu mengganggu.
                                        <br> Kesalahan masih dalam batas toleransi dan mudah diatasi.
                                    </td>
                                </tr>
                                <tr>
                                    <td>4</td>
                                    <td>Kesalahan yang Terlihat</td>
                                    <td>
                                        Terdapat beberapa kesalahan yang mulai mengganggu, tetapi tidak kritis.
                                        <br> Kesalahan ini bisa diidentifikasi dan diperbaiki tanpa kesulitan besar.
                                    </td>
                                </tr>
                                <tr>
                                    <td>5</td>
                                    <td>Kesalahan Sedang</td>
                                    <td>
                                        Kesalahan cukup banyak dan mulai mengganggu proses.
                                        <br> Membutuhkan upaya perbaikan, tetapi masih dapat dikelola.
                                    </td>
                                </tr>
                                <tr>
                                    <td>6</td>
                                    <td>Cukup Banyak Kesalahan</td>
                                    <td>
                                        Terdapat cukup banyak kesalahan yang mengganggu operasional.
                                        <br> Kesalahan membutuhkan waktu dan upaya untuk diperbaiki.
                                    </td>
                                </tr>
                                <tr>
                                    <td>7</td>
                                    <td>Kesalahan Serius</td>
                                    <td>
                                        Terdapat kesalahan signifikan yang mengganggu proses secara substansial.
                                        <br> Membutuhkan perbaikan segera untuk menghindari dampak lebih lanjut.
                                    </td>
                                </tr>
                                <tr>
                                    <td>8</td>
                                    <td>Kesalahan Parah</td>
                                    <td>
                                        Banyak kesalahan serius yang mengakibatkan gangguan besar.
                                        <br> Kesalahan memerlukan intervensi signifikan dan mungkin mengganggu operasional.
                                    </td>
                                </tr>
                                <tr>
                                    <td>9</td>
                                    <td>Kesalahan Sangat Parah</td>
                                    <td>
                                        Kesalahan sangat parah dan menyebabkan gangguan besar.
                                        <br> Memerlukan waktu lama untuk diperbaiki dan berdampak signifikan pada operasional.
                                    </td>
                                </tr>
                                <tr>
                                    <td>10</td>
                                    <td>Kesalahan Kritikal</td>
                                    <td>
                                        Tingkat kesalahan yang sangat kritis dan mengganggu keseluruhan proses.
                                        Membutuhkan tindakan darurat dan bisa menyebabkan kerugian besar jika tidak segera ditangani.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Apakah Anda yakin ingin keluar?',
                text: "Anda akan logout dari akun ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout.php'; // Redirect ke halaman logout jika dikonfirmasi
                }
            });
        }
    </script>
    <?php if ($successMessage) { ?>
        <script>
            Swal.fire({
                title: 'Success!',
                text: '<?php echo $successMessage; ?>',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    <?php } ?>
</body>

</html>