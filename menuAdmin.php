<?php
session_start();
include 'firebaseconfig.php';

use Kreait\Firebase\Exception\FirebaseException;

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$ref = $database->getReference('users/' . $username);
$snapshot = $ref->getSnapshot();
$userData = $snapshot->exists() ? $snapshot->getValue() : [];

// Query users from Firebase
$staffRef = $database->getReference('users');
$staffSnapshot = $staffRef->getSnapshot();
$staffData = $staffSnapshot->getValue();

$staffList = [];
foreach ($staffData as $key => $value) {
    if ($value['role'] === 'staff') {
        $value['username'] = $key; // Menambahkan username ke data staff
        $staffList[$key] = $value;
    }
}

// Pagination variables
$total_records = count($staffList);
$records_per_page = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;
$staffListPaginated = array_slice($staffList, $offset, $records_per_page);

function getProfileImageUrl($profile)
{
    global $storage;

    if ($profile) {
        $bucket = $storage->getBucket();
        $object = $bucket->object('imageprofile/' . $profile);

        if ($object->exists()) {
            return $object->signedUrl(new \DateTime('9999-12-31')); // URL valid forever
        }
    }

    return 'default.png'; // URL ke gambar default jika gambar tidak ada
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/menuAdmin.css">
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
                    <li><a href="#" class="active"><span class="ti ti-address-book"></span><span>Table Staff</span></a></li>
                    <li><a href="tableBarang.php"><span class="ti ti-address-book"></span><span>Table Barang</span></a></li>
                    <li><a href="historiStaff.php"><span class="ti ti-history"></span><span>History Staff</span></a></li>
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
                        <h3 class="section-head">Data Staff</h3>
                        <div class="rev-content">
                            <form action="registerStaff.php" method="post" class="mb-4">
                                <button type="submit" name="registerStaff" class="btn btn-success">Registrasi Akun Staff</button>
                            </form>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="table-primary">Nama</th>
                                        <th class="table-primary">Username</th>
                                        <th class="table-primary">Email</th>
                                        <th class="table-primary">Role</th>
                                        <th class="table-primary">Profile</th>
                                        <th class="table-primary">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staffListPaginated as $staff) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($staff['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($staff['username']); ?></td>
                                            <td><?php echo htmlspecialchars($staff['email']); ?></td>
                                            <td><?php echo htmlspecialchars($staff['role']); ?></td>
                                            <td class="text-center">
                                                <img src="<?php echo (htmlspecialchars($staff['profile'])); ?>" alt="Profile Picture" style="width: 100px; height: 100px; object-fit: cover;" class="mx-auto">
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column justify-content-center align-items-center">
                                                    <!-- <a href="editProfile.php?username=<?php echo htmlspecialchars($staff['username']); ?>" class="btn btn-sm btn-primary mb-2">Update</a> -->
                                                    <a onclick="confirmDelete('<?php echo htmlspecialchars($staff['username']); ?>')" class="btn btn-sm btn-danger">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <?php
                            $total_pages = ceil($total_records / $records_per_page);

                            if ($total_pages > 1) {
                                echo '<div class="btn-group mt-3">';
                                if ($page > 1) {
                                    $prev_page = $page - 1;
                                    echo '<a class="btn btn-primary" href="menuAdmin.php?page=' . $prev_page . '">Previous</a>';
                                }

                                for ($i = 1; $i <= $total_pages; $i++) {
                                    echo '<a class="btn btn-primary ' . ($i === $page ? 'active' : '') . '" href="menuAdmin.php?page=' . $i . '">' . $i . '</a>';
                                }

                                if ($page < $total_pages) {
                                    $next_page = $page + 1;
                                    echo '<a class="btn btn-primary" href="menuAdmin.php?page=' . $next_page . '">Next</a>';
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
            </section>
        </main>
    </div>

    <script>
        function confirmDelete(username) {
            Swal.fire({
                title: 'Apakah Anda yakin ingin menghapus data ini?',
                text: "Tindakan ini tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'deleteProfile.php?username=' + username; // Redirect ke halaman delete jika dikonfirmasi
                }
            });
        }

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