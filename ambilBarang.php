<?php
session_start();
include 'firebaseconfig.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$response = array('message' => '', 'success' => false);

if (isset($_GET['id'])) {
    $barang_id = $_GET['id'];

    // Dapatkan data barang berdasarkan ID dari Firebase
    $barangReference = $database->getReference('barang/' . $barang_id);
    $snapshot = $barangReference->getSnapshot();

    if ($snapshot->exists()) {
        $row = $snapshot->getValue();
        $nama_barang = $row['nama_barang'];
        $stock = $row['stock'];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $jumlah_ambil = $_POST['jumlah_ambil'];

            // Pastikan jumlah yang diambil tidak melebihi stok yang tersedia
            if ($jumlah_ambil <= $stock && $jumlah_ambil > 0) {
                $new_stock = $stock - $jumlah_ambil;

                // Update stok barang di Firebase
                $barangReference->update([
                    'stock' => $new_stock
                ]);

                // Ambil nama staff dari session username
                $username = $_SESSION['username'];
                $tanggal_transaksi = date("Y-m-d");

                // Simpan informasi transaksi ke dalam tabel transaksi di Firebase
                $transaksiReference = $database->getReference('transaksi');
                $transaksiReference->push([
                    'username' => $username,
                    'id_barang' => $barang_id,
                    'jumlah' => $jumlah_ambil,
                    'jenis_transaksi' => "Mengambil Barang $nama_barang sejumlah $jumlah_ambil",
                    'tanggal_transaksi' => $tanggal_transaksi
                ]);

                $response['message'] = "Anda telah berhasil mengambil $jumlah_ambil $nama_barang. Stok barang berhasil diperbarui.";
                $response['success'] = true;
            } else {
                $response['message'] = "Jumlah yang diminta melebihi stok yang tersedia atau tidak valid.";
            }

            // Echo JSON response
            echo json_encode($response);
            exit;
        }
    } else {
        $response['message'] = "Barang tidak ditemukan.";
    }
} else {
    $response['message'] = "Invalid request.";
}

// Jika tidak ada permintaan ambil barang, lanjutkan dengan tampilan antarmuka HTML
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Barang</title>
    <link rel="stylesheet" href="css/ambilBarang.css">
</head>

<body class="gradienHabibi">
    <div class="container">
        <div class="register">
            <h1>Detail Barang: <?php echo htmlspecialchars($nama_barang); ?></h1>
            <div class="line gradienHabibi"></div>
            <form id="ambilForm" method="post" action="" class="firstForm">
                <div class="details">
                    <p>Stok Tersedia: <?php echo htmlspecialchars($stock); ?></p>
                    <label for="jumlah_ambil">
                        <p>Berapa jumlah barang yang anda ambil?</p>
                    </label>
                    <input class="registration" type="number" id="jumlah_ambil" name="jumlah_ambil" min="1" max="<?php echo htmlspecialchars($stock); ?>" required>
                    <div id="responseContainer"></div>
                </div>
                <div class="button-container">
                    <!-- Tombol Kembali -->
                    <a href="menuStaff.php" class="btn-back">Kembali</a>
                </div>
                <div class="button-container">
                    <button type="submit" name="tbSubmit">Ambil</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('ambilForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent form submission

            var jumlahAmbil = document.getElementById('jumlah_ambil').value;
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'ambilBarang.php?id=<?php echo $barang_id; ?>', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == XMLHttpRequest.DONE) {
                    if (xhr.status == 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            var responseContainer = document.getElementById('responseContainer');
                            responseContainer.innerHTML = response.message;

                            // Add color based on the condition
                            responseContainer.style.color = response.success ? '#28a745' : '#dc3545';

                            // Update stock if the request is successful
                            if (response.success) {
                                var stok = <?php echo $stock; ?>;
                                var newStock = stok - jumlahAmbil;
                                document.getElementById('stok').textContent = newStock;
                            }
                        } catch (e) {
                            console.error('JSON parse error: ', e);
                            console.error('Response text: ', xhr.responseText);
                        }
                    } else {
                        console.error('AJAX request error');
                    }
                }
            };
            xhr.send('jumlah_ambil=' + jumlahAmbil);
        });
    </script>
</body>

</html>