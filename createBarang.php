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
    <title>Tambah Barang</title>
    <link rel="stylesheet" href="css/createBarang.css">

</head>


<body class="gradienHabibi">
    <div class="container">
        <div class="register">
            <h1>Tambah Barang</h1>
            <div class="line gradienHabibi"></div>


            <form action="prosesCreate.php" method="post" enctype="multipart/form-data" class="firstForm">
                <div class="details">
                    <label for="nama_barang">
                        <p>Nama Barang</p>
                    </label>
                    <input class="registration" type="text" name="nama_barang" id="nama_barang" placeholder="Masukkan Nama Barang">
                </div>

                <div class="details">
                    <label for="jenis_barang">
                        <p>Jenis Barang</p>
                    </label>
                    <select class="registration" name="jenis_barang">
                        <option value="" disabled selected>Pilih Jenis Barang</option>
                        <option value="Elektronik">Elektronik</option>
                        <option value="Pakaian">Pakaian</option>
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Alat Tulis">Alat Tulis</option>
                        <option value="Mainan">Mainan</option>
                        <option value="Otomotif">Otomotif</option>
                        <option value="Perabotan">Perabotan</option>
                        <option value="Barang Antik">Barang Antik</option>
                    </select>
                </div>

                <div class="details">
                    <label for="stock">
                        <p>Stock</p>
                    </label>
                    <input class="registration" type="number" name="stock" id="stock" value="1" min="1" max="1000">

                </div>

                <div class="details">
                    <label for="gambar_barang">
                        <p>Gambar Barang</p>
                    </label>
                    <input class="registration" type="file" id="gambar_barang" name="gambar_barang" accept="image/*">
                </div>

                <div class="button-container">
                    <!-- Tombol Kembali -->
                    <a href="menuStaff.php" class="btn-back">Kembali</a>
                </div>
                <div class="button-container">
                    <!-- Tombol Register -->
                    <button type="submit" name="tbSubmit">Submit</button>
                </div>
            </form>


            <?php

            if (!empty($_SESSION['errors'])) :
            ?>
                <div class="text-danger">
                    <h4>Pesan Kesalahan Menambahkan Data Barang:</h4>
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