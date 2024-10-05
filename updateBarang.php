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
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Barang</title>
    <link rel="stylesheet" href="css/updateBarang.css">
    <link rel="icon" href="img/logo.png" type="image/png" />
</head>


<body class="gradienHabibi">
    <div class="container">
        <div class="register">
            <h1>Update Barang</h1>
            <div class="line gradienHabibi"></div>


            <?php
            if (isset($_GET['id'])) {
                $id = $_GET['id'];

                // Mendapatkan referensi ke node barang dengan ID yang diberikan
                $barangReference = $database->getReference('barang/' . $id);
                $barangSnapshot = $barangReference->getSnapshot();
                $barangData = $barangSnapshot->getValue();

                if ($barangData) {
                    // Data barang ditemukan, lanjutkan dengan menampilkan formulir update
            ?>

                    <form action="prosesUpdate.php" method="post" enctype="multipart/form-data" class="firstForm">
                        <input type="hidden" name="id" value="<?php echo $id; ?>">




                        <div class="details">
                            <label for="nama_barang">
                                <p>Nama Barang</p>
                            </label>
                            <input class="registration" type="text" name="nama_barang" value="<?php echo $barangData['nama_barang']; ?>" required>
                        </div>

                        <div class="details">
                            <label for="jenis_barang">
                                <p>Jenis Barang</p>
                            </label>
                            <select class="registration" name="jenis_barang" required>
                                <option value="" disabled>Pilih Jenis Barang</option>
                                <option value="Elektronik" <?php echo ($barangData['jenis_barang'] == 'Elektronik') ? 'selected' : ''; ?>>Elektronik</option>
                                <option value="Pakaian" <?php echo ($barangData['jenis_barang'] == 'Pakaian') ? 'selected' : ''; ?>>Pakaian</option>
                                <option value="Makanan" <?php echo ($barangData['jenis_barang'] == 'Makanan') ? 'selected' : ''; ?>>Makanan</option>
                                <option value="Minuman" <?php echo ($barangData['jenis_barang'] == 'Minuman') ? 'selected' : ''; ?>>Minuman</option>
                                <option value="Alat Tulis" <?php echo ($barangData['jenis_barang'] == 'Alat Tulis') ? 'selected' : ''; ?>>Alat Tulis</option>
                                <option value="Mainan" <?php echo ($barangData['jenis_barang'] == 'Mainan') ? 'selected' : ''; ?>>Mainan</option>
                                <option value="Otomotif" <?php echo ($barangData['jenis_barang'] == 'Otomotif') ? 'selected' : ''; ?>>Otomotif</option>
                                <option value="Perabotan" <?php echo ($barangData['jenis_barang'] == 'Perabotan') ? 'selected' : ''; ?>>Perabotan</option>
                                <option value="Barang Antik" <?php echo ($barangData['jenis_barang'] == 'Barang Antik') ? 'selected' : ''; ?>>Barang Antik</option>
                            </select>
                        </div>


                        <div class="details">
                            <label for="stock">
                                <p>Stock</p>
                            </label>
                            <input class="registration" type="number" name="stock" value="<?php echo $barangData['stock']; ?>" required>

                        </div>

                        <div class="details">
                            <label for="gambar_barang">
                                <p>Gambar Barang</p>
                            </label>
                            <input class="registration" type="file" name="gambar_barang">
                            <div class="gambar-container">
                                <?php if (!empty($barangData['gambar_barang'])) : ?>
                                    <img src="<?php echo $barangData['gambar_barang']; ?>" alt="Gambar Barang" style="max-width: 200px;">
                                <?php else : ?>
                                    <p>Gambar tidak tersedia</p>
                                <?php endif; ?>
                            </div>
                        </div>



                        <div class="button-container">
                            <!-- Tombol Kembali -->
                            <a href="menuStaff.php" class="btn-back">Kembali</a>
                        </div>
                        <div class="button-container">
                            <!-- Tombol Register -->
                            <button type="submit" name="tbSubmit">Update</button>
                        </div>
                    </form>

            <?php
                } else {
                    // Data barang tidak ditemukan
                    echo '<p>Data barang tidak ditemukan.</p>';
                }
            } else {
                // ID barang tidak ditemukan
                echo '<p>ID barang tidak ditemukan.</p>';
            }
            ?>

            <?php


            if (!empty($_SESSION['errors'])) :
            ?>
                <div class="text-danger">
                    <h4>Pesan Kesalahan Memperbarui Data Barang:</h4>
                    <ul>
                        <?php foreach ($_SESSION['errors'] as $error) : ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif (!empty($_SESSION['success'])) : ?>
                <div class="text-success">
                    <p><?php echo $_SESSION['success']; ?></p>
                </div>
            <?php endif; ?>

            <?php
            // Jangan lupa unset sesuai kebutuhan
            unset($_SESSION['errors']);
            unset($_SESSION['success']);
            ?>
        </div>
    </div>
</body>

</html>