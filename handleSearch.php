<?php

include 'firebaseconfig.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';
$resultsPerPage = 5; // Number of results per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $resultsPerPage;

$searchResults = [];
$searchMessage = "";

// Fetch data with pagination
if (!isset($_GET['query']) || empty($_GET['query'])) {
    $barangReference = $database->getReference('barang')
        ->orderByKey()
        ->limitToFirst($resultsPerPage)
        ->getSnapshot();
} else {
    $query = $_GET['query'];

    $barangReference = $database->getReference('barang')
        ->orderByChild('nama_barang')
        ->startAt($query)
        ->endAt($query . "\uf8ff")
        ->limitToFirst($resultsPerPage)
        ->getSnapshot();
}


$searchResults = $barangReference->getValue();
$totalCountReference = $database->getReference('barang')->getSnapshot();
$total_records = $totalCountReference->numChildren();
$total_pages = ceil($total_records / $resultsPerPage);

if (empty($searchResults)) {
    $searchMessage = "Tidak ada hasil pencarian untuk '$query'.";
    echo "<div class='search-results'><p>$searchMessage</p></div>";
} else {
    echo "<table class='table'>";
    echo "<thead>";
    echo "<tr><th class='table-primary'>ID Barang</th>";
    echo "<th class='table-primary'>Nama Barang</th>";
    echo "<th class='table-primary'>Jenis Barang</th>";
    echo "<th class='table-primary'>Stock</th>";
    echo "<th class='table-primary'>Gambar Barang</th>";
    echo "<th class='table-primary'>Action</th>";
    echo "</tr></thead><tbody>";

    foreach ($searchResults as $key => $result) {
        echo "<tr>";
        echo "<td>" . $key . "</td>";
        echo "<td>" . $result['nama_barang'] . "</td>";
        echo "<td>" . $result['jenis_barang'] . "</td>";
        echo "<td>" . $result['stock'] . "</td>";
        echo "<td class='text-center'><img src='" . $result['gambar_barang'] . "' alt='Gambar Barang' style='width: 100px; height: 100px; object-fit: cover;' class='mx-auto'></td>";
        echo "<td>
                <div class='d-flex flex-column justify-content-center align-items-center'>
                    <a href='ambilBarang.php?id=" . $key . "' class='btn btn-sm btn-warning mb-2'>Ambil Barang</a>    
                    <a href='updateBarang.php?id=" . $key . "' class='btn btn-sm btn-primary mb-2'>Update</a>
                    <a href='deleteBarang.php?id=" . $key . "' class='btn btn-sm btn-danger'>Delete</a>     
                </div>
              </td>";
        echo "</tr>";
    }
    echo "</tbody></table></div>";

    if ($total_pages > 1) {
        echo '<div class="btn-group mt-3">';
        if ($page > 1) {
            $prev_page = $page - 1;
            echo '<a class="btn btn-custom" href="search.php?page=' . $prev_page . '">Previous</a>';
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            echo '<a class="btn ' . ($i === $page ? 'btn-custom' : '') . '" href="search.php?page=' . $i . '">' . $i . '</a>';
        }

        if ($page < $total_pages) {
            $next_page = $page + 1;
            echo '<a class="btn btn-custom" href="search.php?page=' . $next_page . '">Next</a>';
        }

        echo '</div>';
    }
}
