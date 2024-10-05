<?php
session_start();

// Set timezone to Indonesia
date_default_timezone_set('Asia/Jakarta');

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Konfigurasi Firebase
include 'firebaseconfig.php';

// Ambil data pengguna berdasarkan informasi sesi
$username = $_SESSION['username'];
$reference = $database->getReference('users/' . $username);  // Pastikan referensi ke 'users' yang benar
$userDataSnapshot = $reference->getSnapshot();

if ($userDataSnapshot->exists()) {
    $userData = $userDataSnapshot->getValue();
} else {
    $userData = array(); // Atur ke array kosong atau tangani sesuai kebutuhan
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/mainStaff.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link defer rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
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
                    <li><a href="#" class="active"><span class="las la-adjust"></span><span>Dashboard</span></a></li>
                    <li><a href="menuStaff.php"><span class="ti ti-address-book"></span><span>Table Barang</span></a></li>
                </ul>
            </div>
            <div class="sidebar-card" style="position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%);">
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
                    <p>Menampilkan hasil analisa barang<span class="las la-chart-line"></span></p>
                </div>
            </div>
        </header>
        <main>
            <section>
                <div class="block-grid">
                    <div class="revenue-card">
                        <h3 class="section-head">Selamat Datang, <?php echo isset($userData['nama']) ? htmlspecialchars($userData['nama']) : 'User'; ?>!</h3>
                        <div class="rev-content">

                            <img src="<?php echo (htmlspecialchars($userData['profile'])); ?>" alt="Profile Picture" class="mx-auto rounded-circle img-thumbnail mb-4" style="width: 150px; height: 150px; object-fit: cover;">
                            <div class="rev-sum">
                                <h4><?php echo isset($userData['nama']) ? htmlspecialchars($userData['nama']) : ''; ?></h4>
                                <h2><?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?></h2>
                            </div>
                            <div class="text-center mt-4">
                                <div class="row justify-content-center">
                                    <div class="col-auto">
                                        <button id="attendanceBtn" class="btn btn-success mb-2">Mark Attendance</button>
                                    </div>
                                    <div class="col-auto">
                                        <a href="editProfile.php?username=<?php echo urlencode($username); ?>" class="btn btn-primary mb-2">Update Profile</a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                    <!-- Chart Container -->
                    <div class="graph-card">
                        <h3 class="section-head">Chart Transaksi</h3>
                        <div class="graph-content">
                            <div class="graph-head">
                                <div class="icon-wrapper">
                                    <div class="icon"><span class="las la-eye text-main"></span></div>
                                    <div class="icon"><span class="las la-clock text-success"></span></div>
                                </div>
                            </div>
                            <div class="graph-board">
                                <canvas id="jenisBarangChart" height="400"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Chart Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('jenisBarangChart').getContext('2d');

            fetch('chartdata.php')
                .then(response => response.json())
                .then(data => {
                    var chartData = {
                        labels: data.labels,
                        datasets: [{
                            data: data.values,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)',
                                'rgba(75, 192, 192, 0.7)',
                                'rgba(153, 102, 255, 0.7)',
                                'rgba(255, 159, 64, 0.7)',
                                'rgba(201, 203, 207, 0.7)',
                                'rgba(255, 99, 132, 0.7)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)',
                                'rgba(201, 203, 207, 1)',
                                'rgba(255, 99, 132, 1)'
                            ],
                            borderWidth: 1
                        }]
                    };

                    var options = {
                        responsive: true,
                        maintainAspectRatio: false
                    };

                    var myPieChart = new Chart(ctx, {
                        type: 'pie',
                        data: chartData,
                        options: options
                    });
                })
                .catch(error => console.error('Error fetching data:', error));
        });

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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('checkAttendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username: '<?php echo htmlspecialchars($username); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.attendanceMarked) {
                        document.getElementById('attendanceBtn').classList.add('disabled');
                        document.getElementById('attendanceBtn').innerText = 'Attendance Marked';
                    }
                })
                .catch(error => console.error('Error:', error));

            document.getElementById('attendanceBtn').addEventListener('click', function() {
                fetch('markAttendance.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            username: '<?php echo htmlspecialchars($username); ?>'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Success', 'Attendance marked successfully!', 'success');
                            document.getElementById('attendanceBtn').classList.add('disabled');
                            document.getElementById('attendanceBtn').innerText = 'Attendance Marked';
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'An error occurred.', 'error');
                    });
            });
        });
    </script>

</body>

</html>