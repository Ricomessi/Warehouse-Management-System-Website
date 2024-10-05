<?php
session_start();
include 'firebaseconfig.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/menuStaff.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css">
    <link defer rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
                    <li><a href="#" class="active"><span class="ti ti-address-book"></span><span>Table Barang</span></a></li>
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
                    <p>Menampilkan hasil analisa Barang<span class="las la-chart-line"></span></p>
                </div>
            </div>
        </header>
        <main>
            <section>
                <div class="block-grid">
                    <div class="revenue-card">
                        <h3 class="section-head">Data Barang</h3>
                        <div class="rev-content">
                            <div class="search-results mt-3">
                                <?php
                                $resultsPerPage = 5; // Number of results per page
                                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $offset = ($page - 1) * $resultsPerPage;

                                // Fetch all data
                                $barangReference = $database->getReference('barang')
                                    ->orderByKey()
                                    ->getSnapshot();

                                $searchResults = $barangReference->getValue();
                                $searchMessage = "";

                                if (empty($searchResults)) {
                                    $searchMessage = "Tidak ada hasil pencarian.";
                                }

                                if (!empty($searchResults)) {
                                    // Reverse the array to get the latest items first
                                    $searchResults = array_reverse($searchResults, true);
                                    $total_records = count($searchResults);
                                    $total_pages = ceil($total_records / $resultsPerPage);

                                    // Slice the array to get the current page items
                                    $current_page_items = array_slice($searchResults, $offset, $resultsPerPage, true);

                                    echo "<table class='table'>";
                                    echo "<thead>";
                                    echo "<tr><th class='table-primary'>ID Barang</th>";
                                    echo "<th class='table-primary'>Nama Barang</th>";
                                    echo "<th class='table-primary'>Jenis Barang</th>";
                                    echo "<th class='table-primary'>Stock</th>";
                                    echo "<th class='table-primary'>Gambar Barang</th>";
                                    echo "</tr></thead><tbody>";

                                    $num = $offset + 1; // Initialize the numeric ID based on the current page and offset
                                    foreach ($current_page_items as $key => $result) {
                                        echo "<tr>";
                                        echo "<td>" . $num . "</td>"; // Display the numeric ID
                                        echo "<td>" . (isset($result['nama_barang']) ? $result['nama_barang'] : '-') . "</td>";
                                        echo "<td>" . (isset($result['jenis_barang']) ? $result['jenis_barang'] : '-') . "</td>";
                                        echo "<td>" . (isset($result['stock']) ? $result['stock'] : '-') . "</td>";
                                        echo "<td class='text-center'>";
                                        if (isset($result['gambar_barang'])) {
                                            echo "<img src='" . $result['gambar_barang'] . "' alt='Gambar Barang' style='width: 100px; height: 100px; object-fit: cover;' class='mx-auto'>";
                                        } else {
                                            echo "No Image";
                                        }
                                        echo "</td>";
                                        echo "</tr>";
                                        $num++; // Increment the numeric ID for the next row
                                    }
                                    echo "</tbody></table>";

                                    // Pagination links only if total records are more than 5
                                    if ($total_records > $resultsPerPage) {
                                        echo '<div class="btn-group mt-3">';
                                        if ($page > 1) {
                                            $prev_page = $page - 1;
                                            echo '<a class="btn btn-custom" href="tableBarang.php?page=' . $prev_page . '">Previous</a>';
                                        }

                                        for ($i = 1; $i <= $total_pages; $i++) {
                                            echo '<a class="btn ' . ($i === $page ? 'btn-custom active' : 'btn-custom') . '" href="tableBarang.php?page=' . $i . '">' . $i . '</a>';
                                        }

                                        if ($page < $total_pages) {
                                            $next_page = $page + 1;
                                            echo '<a class="btn btn-custom" href="tableBarang.php?page=' . $next_page . '">Next</a>';
                                        }
                                        echo '</div>';
                                    }
                                } else {
                                    echo "<div class='search-results'><p>Data tidak ditemukan.</p></div>";
                                }
                                ?>
                            </div>
                            <script>
                                $(document).ready(function() {
                                    $('#search-input').focus();

                                    $('#search-input').keyup(function() {
                                        var query = $(this).val(); // Ambil nilai input pencarian
                                        $.ajax({
                                            url: 'handleSearch.php', // Ganti dengan nama file PHP yang menangani pencarian
                                            method: 'GET',
                                            data: {
                                                query: query
                                            },
                                            success: function(response) {
                                                $('.search-results').html(response); // Perbarui bagian hasil pencarian dengan respons dari server
                                            }
                                        });
                                    });
                                });
                            </script>
                            <script>
                                // Konfirmasi logout dengan SweetAlert
                                function confirmLogout() {
                                    Swal.fire({
                                        title: 'Apakah Anda yakin ingin keluar?',
                                        text: 'Anda akan logout dari akun ini.',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#d33',
                                        cancelButtonColor: '#3085d6',
                                        confirmButtonText: 'Ya, logout',
                                        cancelButtonText: 'Batal'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // Redirect ke halaman logout di sini
                                            window.location.href = 'logout.php';
                                        }
                                    });
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>

</html>