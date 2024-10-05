<?php
session_start();
include 'firebaseconfig.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch transactions from Firebase
$reference = $database->getReference('transaksi');
$snapshot = $reference->getSnapshot();
$transaksi = $snapshot->getValue();

// If no transactions are found, initialize as an empty array
if (!$transaksi) {
    $transaksi = [];
}

// Convert the associative array to a list of transactions with their keys included
$transaksi_with_keys = [];
foreach ($transaksi as $key => $value) {
    $value['key'] = $key; // Include the key in the transaction data
    $transaksi_with_keys[] = $value;
}

// Sort transactions by 'tanggal_transaksi' in descending order
usort($transaksi_with_keys, function ($a, $b) {
    return strtotime($b['tanggal_transaksi']) - strtotime($a['tanggal_transaksi']);
});

// Determine the date one week ago
$one_week_ago = strtotime('-1 week');

// Identify and delete transactions older than one week
foreach ($transaksi_with_keys as $trans) {
    if (strtotime($trans['tanggal_transaksi']) < $one_week_ago) {
        $reference->getChild($trans['key'])->remove();
    }
}

// Re-fetch transactions after deletion
$snapshot = $reference->getSnapshot();
$transaksi = $snapshot->getValue();

// Convert the associative array to a list of transactions with their keys included
$transaksi_with_keys = [];
foreach ($transaksi as $key => $value) {
    $value['key'] = $key; // Include the key in the transaction data
    $transaksi_with_keys[] = $value;
}

// Sort transactions by 'tanggal_transaksi' in descending order
usort($transaksi_with_keys, function ($a, $b) {
    return strtotime($b['tanggal_transaksi']) - strtotime($a['tanggal_transaksi']);
});

// Pagination
$records_per_page = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;
$total_records = count($transaksi_with_keys);
$total_pages = ceil($total_records / $records_per_page);

// Get transactions for the current page
$transaksi_paged = array_slice($transaksi_with_keys, $offset, $records_per_page);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/historyStaff.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
    <link defer rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="gradienHabibi">
    <input type="checkbox" name="" id="menu-toggle">
    <div class="overlay"><label for="menu-toggle"></label></div>

    <div class="sidebar">
        <div class="sidebar-container" style="position: relative; margin-bottom: 120px;">
            <div class="brand">
                <img class="brand-img" src="img/logo.png" alt="logo" style="width:200px; margin-bottom:2rem">
            </div>

            <div class="sidebar-menu">
                <ul>
                    <li><a href="mainAdmin.php"><span class="las la-adjust"></span><span>Dashboard</span></a></li>
                    <li><a href="menuAdmin.php"><span class="ti ti-address-book"></span><span>Table Staff</span></a></li>
                    <li><a href="tableBarang.php"><span class="ti ti-address-book"></span><span>Table Barang</span></a></li>
                    <li><a href="#" class="active"><span class="ti ti-history"></span><span>History Staff</span></a></li>
                    <li><a href="kepuasanPelanggan.php"><span class="ti ti-heart"></span><span>Kepuasan Pelanggan</span></a></li>
                    <li><a href="tingkatKesalahan.php"><span class="ti ti-alert-triangle"></span><span>Tingkat Kesalahan</span></a></li>
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
                <label for="menu-toggle">
                    <span class="las la-bars"></span>
                </label>
                <div class="header-title">
                    <h1>Analisa</h1>
                    <p>Menampilkan hasil analisa transaksi<span class="las la-chart-line"></span></p>
                </div>
            </div>
        </header>
        <main>
            <section>
                <div class="block-grid">
                    <div class="revenue-card">
                        <h3 class="section-head">History Staff</h3>
                        <div class="rev-content">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="table-primary">Username Staff</th>
                                        <th class="table-primary">ID Barang</th>
                                        <th class="table-primary">Jumlah</th>
                                        <th class="table-primary">Tanggal Transaksi</th>
                                        <th class="table-primary">Jenis Transaksi</th>
                                        <th class="table-primary">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transaksi_paged as $trans) { ?>
                                        <tr>
                                            <td><?php echo $trans['username']; ?></td>
                                            <td><?php echo $trans['id_barang']; ?></td>
                                            <td><?php echo $trans['jumlah']; ?></td>
                                            <td><?php echo $trans['tanggal_transaksi']; ?></td>
                                            <td><?php echo $trans['jenis_transaksi']; ?></td>
                                            <td>
                                                <div class="d-flex flex-column justify-content-center align-items-center">
                                                    <a href="printHistori.php?id=<?php echo $trans['key']; ?>" class="btn btn-sm btn-primary mb-2">Print History</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <?php if ($total_pages > 1) { ?>
                                <div class="btn-group mt-3">
                                    <?php if ($page > 1) { ?>
                                        <a class="btn btn-primary" href="historiStaff.php?page=<?php echo $page - 1; ?>">Previous</a>
                                    <?php } ?>
                                    <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                                        <a class="btn btn-primary <?php echo $i === $page ? 'active' : ''; ?>" href="historiStaff.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php } ?>
                                    <?php if ($page < $total_pages) { ?>
                                        <a class="btn btn-primary" href="historiStaff.php?page=<?php echo $page + 1; ?>">Next</a>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
            </section>
        </main>
    </div>
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
</body>

</html>