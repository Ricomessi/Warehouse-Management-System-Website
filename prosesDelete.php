<?php
session_start();
ob_start(); // Mulai output buffering

include 'firebaseconfig.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch data of the barang before deleting it
    $barangRef = $database->getReference('barang/' . $id);
    $barangSnapshot = $barangRef->getSnapshot();
    $barangData = $barangSnapshot->getValue();

    if ($barangData) {
        $nama_barang = $barangData['nama_barang'];
        $stock = $barangData['stock'];
        $gambar_barang = $barangData['gambar_barang'];

        // Delete the image from Firebase Storage
        $bucket = $storage->getBucket();
        $objectName = 'barang/' . parseFirebaseStorageUrl($gambar_barang);
        $object = $bucket->object($objectName);

        if ($object->exists()) {
            $object->delete();
        } else {
            echo "Error: Object does not exist in Firebase Storage.";
        }

        // Delete all related transaksi of the barang
        try {
            $transaksiRef = $database->getReference('transaksi')->orderByChild('id_barang')->equalTo($id)->getSnapshot();
            if ($transaksiRef->exists()) {
                foreach ($transaksiRef->getValue() as $key => $transaksi) {
                    $database->getReference('transaksi/' . $key)->remove();
                }
            }
        } catch (\Kreait\Firebase\Exception\Database\UnsupportedQuery $e) {
            echo "Error: {$e->getMessage()}";
            exit();
        }

        // Delete the barang
        $barangRef->remove();

        // Set session success and redirect
        $_SESSION['success'] = "Data Barang Berhasil Dihapus!";
        header("Location: menuStaff.php");
        exit(); // Pastikan untuk menghentikan eksekusi script setelah header redirect
    } else {
        echo "Error: Failed to fetch barang data.";
    }
}

function parseFirebaseStorageUrl($url)
{
    // Parse the Firebase Storage URL to get the path without query parameters
    $parsedUrl = parse_url($url);
    $path = $parsedUrl['path'];
    // Remove leading '/' if it exists
    if (substr($path, 0, 1) === '/') {
        $path = substr($path, 1);
    }
    return $path;
}

ob_end_flush(); // Akhiri output buffering
