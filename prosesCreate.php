<?php
session_start();

include 'firebaseconfig.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_barang = $_POST['nama_barang'];
    $jenis_barang = $_POST['jenis_barang'];
    $stock = $_POST['stock'];
    $gambar_barang = $_FILES['gambar_barang'];

    $errors = array();

    if (empty($nama_barang)) {
        $errors[] = "Nama Barang tidak boleh kosong";
    } elseif (!preg_match("/^[a-zA-Z0-9 ]*$/", $nama_barang)) {
        $errors[] = "Nama Barang hanya boleh berisi huruf, angka, dan spasi";
    }

    if (empty($jenis_barang)) {
        $errors[] = "Pilih jenis barang";
    }

    if (empty($stock) || !is_numeric($stock) || $stock < 0) {
        $errors[] = "Stock tidak valid";
    }

    if (!empty($gambar_barang['name'])) {
        $allowedFormats = array("jpg", "jpeg", "png", "gif");
        $imageFileType = strtolower(pathinfo($gambar_barang['name'], PATHINFO_EXTENSION));
        $isValidImage = getimagesize($gambar_barang['tmp_name']);

        if (!in_array($imageFileType, $allowedFormats)) {
            $errors[] = "Hanya file JPG, JPEG, PNG, dan GIF yang diizinkan.";
        }

        if (!$isValidImage) {
            $errors[] = "File yang diunggah bukan gambar.";
        }

        $maxFileSize = 2 * 1024 * 1024;
        if ($gambar_barang['size'] > $maxFileSize) {
            $errors[] = "Ukuran file terlalu besar. Maksimum 2MB.";
        }
    } else {
        $errors[] = "Gambar Barang tidak boleh kosong";
    }

    if (empty($errors)) {
        $reference = $database->getReference('barang');
        $snapshot = $reference->orderByChild('nama_barang')->equalTo($nama_barang)->getSnapshot();

        if ($snapshot->numChildren() > 0) {
            $item = array_values($snapshot->getValue())[0];
            $existingStock = $item['stock'];
            $newStock = $existingStock + $stock;

            $itemKey = array_keys($snapshot->getValue())[0];
            $updateReference = $database->getReference('barang/' . $itemKey);
            $updateReference->update(['stock' => $newStock]);

            logTransaction($database, $_SESSION['username'], $itemKey, $stock, "Menambah Stok Barang $nama_barang");

            $_SESSION['success'] = "Data Barang Berhasil Diperbarui!!";
            header("Location: menuStaff.php");
        } else {
            // Upload gambar ke Firebase Storage
            $bucket = $storage->getBucket();
            $fileName = $gambar_barang['name'];
            $filePath = $gambar_barang['tmp_name'];
            $file = fopen($filePath, 'r');

            $object = $bucket->upload($file, [
                'name' => 'barang/' . $fileName,
                'metadata' => [
                    'contentType' => $gambar_barang['type']
                ]
            ]);

            $url = $object->signedUrl(new DateTime('now +10 years')); // URL kedaluwarsa besok

            if ($url) {
                $newItem = [
                    'nama_barang' => $nama_barang,
                    'jenis_barang' => $jenis_barang,
                    'stock' => $stock,
                    'gambar_barang' => $url
                ];

                $newReference = $database->getReference('barang')->push($newItem);
                $newItemKey = $newReference->getKey();

                logTransaction($database, $_SESSION['username'], $newItemKey, $stock, "Menambah Barang $nama_barang");

                $_SESSION['success'] = "Data Barang Baru Berhasil Ditambahkan!!";
                header("Location: menuStaff.php");
            } else {
                $errors[] = "Gagal mengunggah file ke Firebase Storage. Silakan coba lagi.";
                $_SESSION['errors'] = $errors;
                header("Location: createBarang.php");
            }
        }
    } else {
        $_SESSION['errors'] = $errors;
        header("Location: createBarang.php");
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
