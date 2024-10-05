<?php
session_start();

// Validasi apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include konfigurasi Firebase
include 'firebaseconfig.php';

use Kreait\Firebase\Exception\FirebaseException;

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['username'])) {
    $username = $_GET['username'];

    // Path pengguna di Realtime Database
    $userPath = 'users/' . $username;

    try {
        // Ambil data pengguna
        $snapshot = $database->getReference($userPath)->getSnapshot();

        if ($snapshot->exists()) {
            $userData = $snapshot->getValue();

            // Cek dan hapus gambar profil dari Firebase Storage
            if (isset($userData['profile']) && !empty($userData['profile'])) {
                $profileUrl = $userData['profile'];

                // Ekstrak nama file dari URL
                $profilePath = parse_url($profileUrl, PHP_URL_PATH);
                $fileName = basename($profilePath);

                // Hapus file dari Firebase Storage
                $bucket = $storage->getBucket();
                $object = $bucket->object('imageprofile/' . $fileName);

                if ($object->exists()) {
                    $object->delete();
                }
            }

            // Hapus data pengguna dari Realtime Database
            $database->getReference($userPath)->remove();
        }

        // Redirect kembali ke halaman admin setelah penghapusan
        header("Location: menuAdmin.php");
        exit();
    } catch (FirebaseException $e) {
        echo "Gagal menghapus data: " . $e->getMessage();
    }
} else {
    echo "Parameter ID tidak valid.";
}
