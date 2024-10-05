<?php
session_start();

include 'firebaseconfig.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = $_POST['id'];
    $nama_barang = $_POST['nama_barang'];
    $jenis_barang = $_POST['jenis_barang'];
    $stock = $_POST['stock'];

    // Initialize errors array
    $errors = array();

    if (empty($nama_barang)) {
        $errors[] = 'Nama barang tidak boleh kosong.';
    }

    if (empty($jenis_barang)) {
        $errors[] = 'Jenis barang tidak boleh kosong.';
    }

    if (empty($stock)) {
        $errors[] = 'Stock tidak boleh kosong.';
    }

    if (isset($_FILES['gambar_barang']) && $_FILES['gambar_barang']['size'] > 0) {
        $maxFileSize = 2 * 1024 * 1024; // dalam bytes
        if ($_FILES['gambar_barang']['size'] > $maxFileSize) {
            $errors[] = "Ukuran file terlalu besar. Maksimum 2MB.";
        }
    }

    // If no errors, proceed with update
    if (empty($errors)) {
        $reference = $database->getReference('barang/' . $id);
        $snapshot = $reference->getSnapshot();
        $barang_lama = $snapshot->getValue();

        $url = $barang_lama['gambar_barang']; // Default URL if no new image

        if (isset($_FILES['gambar_barang']) && $_FILES['gambar_barang']['size'] > 0) {
            $bucket = $storage->getBucket();
            $gambar_barang = $_FILES['gambar_barang']['name'];
            $gambar_barang_tmp = $_FILES['gambar_barang']['tmp_name'];
            $newImageName = uniqid() . '-' . $gambar_barang;

            // Upload image to Firebase Storage
            $file = fopen($gambar_barang_tmp, 'r');
            $object = $bucket->upload($file, [
                'name' => 'barang/' . $newImageName
            ]);
            $url = $object->signedUrl(new DateTime('now +10 years')); // Generate signed URL

            // Delete old image if exists
            if (!empty($barang_lama['gambar_barang'])) {
                $oldImageReference = $bucket->object($barang_lama['gambar_barang']);
                if ($oldImageReference->exists()) {
                    $oldImageReference->delete();
                }
            }
        }

        $updatedBarang = [
            'nama_barang' => $nama_barang,
            'jenis_barang' => $jenis_barang,
            'stock' => $stock,
            'gambar_barang' => $url
        ];

        $reference->update($updatedBarang);

        logTransaction($database, $_SESSION['username'], $id, $stock, "Mengupdate Barang $nama_barang");

        $_SESSION['success'] = "Anda telah mengupdate $nama_barang. Barang berhasil diubah.";
        header("Location: menuStaff.php");
        exit();
    } else {
        $_SESSION['errors'] = $errors;
        header("Location: updateBarang.php?id=" . $id);
        exit();
    }
}

function logTransaction($database, $username, $barang_id, $stock, $jenis_transaksi)
{
    $tanggal_transaksi = date("Y-m-d");

    $transaksiData = [
        'username' => $username,
        'id_barang' => $barang_id,
        'jumlah' => $stock,
        'jenis_transaksi' => $jenis_transaksi,
        'tanggal_transaksi' => $tanggal_transaksi
    ];

    $database->getReference('transaksi')->push($transaksiData);
}
