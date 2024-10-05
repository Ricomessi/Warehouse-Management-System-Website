<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'firebaseconfig.php';

// Fetch data from Firebase
$staffRef = $database->getReference('users');
$staffSnapshot = $staffRef->getSnapshot();
$staff = $staffSnapshot->exists() ? $staffSnapshot->getValue() : [];

$attendanceRef = $database->getReference('attendance');
$attendanceSnapshot = $attendanceRef->getSnapshot();
$attendance = $attendanceSnapshot->exists() ? $attendanceSnapshot->getValue() : [];

$transactionRef = $database->getReference('transaksi');
$transactionSnapshot = $transactionRef->getSnapshot();
$transactions = $transactionSnapshot->exists() ? $transactionSnapshot->getValue() : [];

$satisfactionRef = $database->getReference('kepuasanPelanggan');
$satisfactionSnapshot = $satisfactionRef->getSnapshot();
$satisfactions = $satisfactionSnapshot->exists() ? $satisfactionSnapshot->getValue() : [];

$errorRef = $database->getReference('tingkatKesalahan');
$errorSnapshot = $errorRef->getSnapshot();
$errors = $errorSnapshot->exists() ? $errorSnapshot->getValue() : [];

// Function to calculate scores
function calculateScores($staff, $attendance, $transactions, $satisfactions, $errors)
{
    $scores = [];

    foreach ($staff as $key => $value) {
        $absensiScore = calculateAttendanceScore($attendance, $key);
        $kinerjaScore = calculatePerformanceScore($transactions, $key);
        $kepuasanScore = calculateSatisfactionScore($satisfactions, $key);
        $kesalahanScore = calculateErrorScore($errors, $key);

        $totalScore = ($absensiScore['normalized'] * 0.15) + ($kinerjaScore['normalized'] * 0.25) + ($kepuasanScore['normalized'] * 0.35) + ($kesalahanScore['normalized'] * 0.25);
        $scores[$key] = [
            'total' => $totalScore,
            'absensi' => $absensiScore,
            'kinerja' => $kinerjaScore,
            'kepuasan' => $kepuasanScore,
            'kesalahan' => $kesalahanScore
        ];
    }

    uasort($scores, function ($a, $b) {
        return $b['total'] <=> $a['total'];
    });

    return $scores;
}

function normalize($value, $min, $max)
{
    if ($max - $min == 0) {
        return 10; // Nilai normalisasi maksimum jika max dan min sama
    }
    if ($value == $min) {
        return 0; // Nilai normalisasi minimum jika value sama dengan min
    }
    return (($value - $min) / ($max - $min)) * 10;
}

function normalizeCost($value, $min, $max)
{
    if ($max == $min) {
        return 10; // Nilai normalisasi maksimum jika max dan min sama
    }
    if ($value == $min) {
        return 10; // Nilai normalisasi maksimum jika value sama dengan min
    }
    return (($max - $value) / ($max - $min)) * 10;
}

function getCurrentWeekDates()
{
    $currentWeek = [];
    $monday = strtotime('last monday', strtotime('tomorrow'));
    for ($i = 0; $i < 7; $i++) {
        $currentWeek[] = date('Y-m-d', strtotime("+$i day", $monday));
    }
    return $currentWeek;
}

function calculateAttendanceScore($attendance, $username)
{

    $totalDays = 7; // Jumlah hari dalam seminggu
    $presentDays = 0;

    $currentWeek = getCurrentWeekDates();
    if (isset($attendance[$username])) {
        foreach ($currentWeek as $date) {
            if (isset($attendance[$username][$date]) && $attendance[$username][$date]['status'] === 'present') {
                $presentDays++;
            }
        }
    }

    // Cari nilai maksimum presentDays dari seluruh staff
    $maxPresentDays = 0;
    $minPresentDays = PHP_INT_MAX;

    foreach ($attendance as $staff) {
        $staffPresentDays = 0;
        foreach ($currentWeek as $date) {
            if (isset($staff[$date]) && $staff[$date]['status'] === 'present') {
                $staffPresentDays++;
            }
        }
        if ($staffPresentDays > $maxPresentDays) {
            $maxPresentDays = $staffPresentDays;
        }
        if ($staffPresentDays < $minPresentDays) {
            $minPresentDays = $staffPresentDays;
        }
    }

    // Jika tidak ada absensi yang tercatat, atur minPresentDays ke 0
    if ($minPresentDays === PHP_INT_MAX) {
        $minPresentDays = 0;
    }
    $fixday = ($presentDays / 7) * 10;
    $minPresentdayFix = ($minPresentDays / 7) * 10;
    $maxPresentdayFix = ($maxPresentDays / 7) * 10;
    // Normalisasi presentDays dengan menggunakan nilai minimal dan maksimal
    $normalized = $presentDays > 0 ? normalize($fixday, $minPresentdayFix, $maxPresentdayFix) : 0;

    // Konversi dari skala 0-1 ke skala 1-10


    return [
        'original' => $presentDays,
        'normalized' => $normalized
    ];
}



function calculatePerformanceScore($transactions, $username)
{
    $totalTransactions = 0;
    $allTransactions = 0;
    // Hitung jumlah transaksi yang dilakukan oleh user
    foreach ($transactions as $transaction) {
        if ($transaction['username'] === $username) {
            $totalTransactions++;
        }
    }

    foreach ($transactions as $transaction) {
        $allTransactions++;
    }

    // Cari nilai maksimum dan minimum totalTransactions dari seluruh staff
    $maxTransactions = 0;
    $minTransactions = PHP_INT_MAX;

    foreach ($transactions as $transaction) {
        $currentUserTransactions = 0;
        foreach ($transactions as $trans) {
            if ($trans['username'] === $transaction['username']) {
                $currentUserTransactions++;
            }
        }

        if ($currentUserTransactions > $maxTransactions) {
            $maxTransactions = $currentUserTransactions;
        }
        if ($currentUserTransactions < $minTransactions) {
            $minTransactions = $currentUserTransactions;
        }
    }

    // Jika tidak ada transaksi yang tercatat, atur minTransactions ke 0
    if ($minTransactions === PHP_INT_MAX) {
        $minTransactions = 0;
    }

    $fixTotalTransactions = scaleToRange($totalTransactions / $allTransactions, 1, 10);
    $fixMinTransactions = scaleToRange($minTransactions / $allTransactions, 1, 10);
    $fixMaxTransactions = scaleToRange($maxTransactions / $allTransactions, 1, 10);

    // Normalisasi totalTransactions dengan menggunakan nilai minimal dan maksimal
    $normalized = $totalTransactions > 0 ? normalize($fixTotalTransactions, $fixMinTransactions, $fixMaxTransactions) : 0;

    return [
        'original' => $totalTransactions,
        'normalized' => $normalized
    ];
}

function scaleToRange($value, $minScale, $maxScale)
{
    return ($maxScale - $minScale) * $value + $minScale;
}


function calculateSatisfactionScore($satisfactions, $username)
{
    $totalSatisfaction = 0;

    if (isset($satisfactions[$username])) {
        foreach ($satisfactions[$username] as $entry) {
            $totalSatisfaction += $entry['nilai'];
        }
    }

    // Cari nilai maksimum dan minimum totalSatisfaction dari seluruh staff
    $maxSatisfaction = 0;
    $minSatisfaction = PHP_INT_MAX;
    $overallTotalSatisfaction = 0;

    foreach ($satisfactions as $staffSatisfaction) {
        $staffTotalSatisfaction = 0;
        foreach ($staffSatisfaction as $entry) {
            $staffTotalSatisfaction += $entry['nilai'];
        }

        if ($staffTotalSatisfaction > $maxSatisfaction) {
            $maxSatisfaction = $staffTotalSatisfaction;
        }
        if ($staffTotalSatisfaction < $minSatisfaction) {
            $minSatisfaction = $staffTotalSatisfaction;
        }

        $overallTotalSatisfaction += $staffTotalSatisfaction;
    }

    // Jika tidak ada kepuasan yang tercatat, atur minSatisfaction ke 0
    if ($minSatisfaction === PHP_INT_MAX) {
        $minSatisfaction = 0;
    }

    // Normalisasi totalSatisfaction dengan menggunakan nilai minimal dan maksimal
    $normalized = $totalSatisfaction > 0 ? normalize($totalSatisfaction, $minSatisfaction, $maxSatisfaction) : 0;

    // Konversi ke skala 1-10 berdasarkan total kepuasan keseluruhan


    return [
        'original' => $totalSatisfaction,
        'normalized' => $normalized,
    ];
}

function calculateErrorScore($errors, $username)
{
    $totalErrors = 0;
    $numEntries = 0;

    if (isset($errors[$username])) {
        foreach ($errors[$username] as $entry) {
            $totalErrors += $entry['jumlah'];
            $numEntries++;
        }
    }

    // Jika tidak ada entri, return 0
    if ($numEntries === 0) {
        return [
            'original' => 0,
            'normalized' => 10 // Nilai maksimum untuk kesalahan 0
        ];
    }

    // Cari nilai maksimum dan minimum kesalahan dari seluruh staff
    $maxErrors = 0;
    $minErrors = PHP_INT_MAX;
    foreach ($errors as $staffErrors) {
        foreach ($staffErrors as $entry) {
            $jumlah = $entry['jumlah'];
            if ($jumlah > $maxErrors) {
                $maxErrors = $jumlah;
            }
            if ($jumlah < $minErrors) {
                $minErrors = $jumlah;
            }
        }
    }

    // Jika tidak ada kesalahan yang tercatat, atur minErrors ke 0
    if ($minErrors === PHP_INT_MAX) {
        $minErrors = 0;
    }

    // Normalisasi totalErrors dengan menggunakan nilai minimal dan maksimal
    $normalized = normalizeCost($totalErrors, $minErrors, $maxErrors);

    return [
        'original' => $totalErrors,
        'normalized' => $normalized
    ];
}



$scores = calculateScores($staff, $attendance, $transactions, $satisfactions, $errors);


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
                    <li><a href="tingkatKesalahan.php"><span class="ti ti-alert-triangle"></span><span>Tingkat Kesalahan</span></a></li>
                    <li><a href="#" class="active"><span class="ti ti-award"></span><span>Staff Terbaik Mingguan</span></a></li>
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
                    <h1>Staff Terbaik Mingguan</h1>
                </div>
            </div>
        </header>
        <main>
            <section>
                <div class="container mt-4">
                    <h2>Urutan Staff Terbaik</h2>
                    <ul class="list-group">
                        <?php foreach ($scores as $username => $score) {
                            if (stripos($username, 'admin') === false) { ?>
                                <li class="list-group-item">
                                    <?php echo htmlspecialchars($username) . ': ' . number_format($score['total'], 2); ?>
                                </li>
                        <?php }
                        } ?>
                    </ul>

                </div>
                <div class="container mt-4">
                    <h2>Histogram Skor Staff</h2>
                    <canvas id="staffChart"></canvas>
                </div>
            </section>
        </main>

        <div class="container mt-4">
            <h2>Tabel Bobot</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Keterangan</th>
                        <th>Bobot</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Absensi</td>
                        <td>15%</td>
                    </tr>
                    <tr>
                        <td>Kinerja</td>
                        <td>25%</td>
                    </tr>
                    <tr>
                        <td>Kepuasan Pelanggan</td>
                        <td>35%</td>
                    </tr>
                    <tr>
                        <td>Kesalahan</td>
                        <td>25%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="container mt-4">
            <h2>Urutan Staff Terbaik Original</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <!-- <th>Total Score</th> -->
                        <th>Absensi Score</th>
                        <th>Kinerja Score</th>
                        <th>Kepuasan Score</th>
                        <th>Kesalahan Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scores as $username => $score) {
                        if (stripos($username, 'admin') === false) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($username); ?></td>
                                <!-- <td><?php echo number_format($score['total'], 2); ?></td> -->
                                <td><?php echo number_format($score['absensi']['original'], 2); ?></td>
                                <td><?php echo number_format($score['kinerja']['original'], 2); ?></td>
                                <td><?php echo number_format($score['kepuasan']['original'], 2); ?></td>
                                <td><?php echo number_format($score['kesalahan']['original'], 2); ?></td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>

        <div class="container mt-4">
            <h2>Urutan Staff Terbaik Normalized</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <!-- <th>Total Score</th> -->
                        <th>Absensi Score</th>
                        <th>Kinerja Score</th>
                        <th>Kepuasan Score</th>
                        <th>Kesalahan Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scores as $username => $score) {
                        if (stripos($username, 'admin') === false) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($username); ?></td>
                                <!-- <td><?php echo number_format($score['total'], 2); ?></td> -->
                                <td><?php echo number_format($score['absensi']['normalized'], 2); ?></td>
                                <td><?php echo number_format($score['kinerja']['normalized'], 2); ?></td>
                                <td><?php echo number_format($score['kepuasan']['normalized'], 2); ?></td>
                                <td><?php echo number_format($score['kesalahan']['normalized'], 2); ?></td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>




        <!-- $totalScore = ($absensiScore['normalized'] * 0.15) + ($kinerjaScore['normalized'] * 0.25) + ($kepuasanScore['normalized'] * 0.35) + ($kesalahanScore['normalized'] * 0.25); -->
        <div class="container mt-4">
            <h2>Urutan Staff Terbaik Final Score</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Total Score</th>
                        <th>Absensi Score</th>
                        <th>Kinerja Score</th>
                        <th>Kepuasan Score</th>
                        <th>Kesalahan Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scores as $username => $score) {
                        if (stripos($username, 'admin') === false) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($username); ?></td>
                                <td><?php echo number_format($score['total'], 2); ?></td>
                                <td><?php echo number_format($score['absensi']['normalized'] * 0.15, 2); ?></td>
                                <td><?php echo number_format($score['kinerja']['normalized'] * 0.25, 2); ?></td>
                                <td><?php echo number_format($score['kepuasan']['normalized'] * 0.35, 2); ?></td>
                                <td><?php echo number_format($score['kesalahan']['normalized'] * 0.25, 2); ?></td>
                            </tr>
                    <?php }
                    } ?>
                </tbody>
            </table>
        </div>




    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('staffChart').getContext('2d');
        var staffChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($scores)); ?>,
                datasets: [{
                    label: 'Skor Staff',
                    data: <?php echo json_encode(array_column($scores, 'total')); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
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
</body>

</html>