<?php
session_start();
include 'firebaseconfig.php';

use Kreait\Firebase\Exception\FirebaseException;

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['updateProfile'])) {
    $errors = [];

    $id_user = $_POST['id_user'];
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $profilePictureUrl = null;

    // Validasi nama
    if (empty($nama)) {
        $errors[] = "Nama tidak boleh kosong.";
    } elseif (strlen($nama) < 3) {
        $errors[] = "Nama harus memiliki minimal 3 karakter.";
    }

    // Validasi email
    if (empty($email)) {
        $errors[] = "Email tidak boleh kosong.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid.";
    }

    // Validasi password
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = "Password harus memiliki minimal 6 karakter.";
    }

    // Validasi gambar
    if (!empty($_FILES['profile']['tmp_name']) && is_uploaded_file($_FILES['profile']['tmp_name'])) {
        $imageFileType = strtolower(pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION));
        $allowedFormats = ["jpg", "jpeg", "png", "gif"];

        if (!in_array($imageFileType, $allowedFormats)) {
            $errors[] = "Hanya file JPG, JPEG, PNG, dan GIF yang diizinkan untuk gambar profil.";
        } else {
            $isValid = getimagesize($_FILES['profile']['tmp_name']);
            if (!$isValid) {
                $errors[] = "File yang diunggah bukan gambar.";
            }
        }
    }

    // Jika ada kesalahan, simpan ke session dan kembalikan ke form
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: editProfile.php?username=$id_user");
        exit();
    }

    // File upload handling
    if (!empty($_FILES['profile']['tmp_name']) && is_uploaded_file($_FILES['profile']['tmp_name'])) {
        try {
            $bucket = $storage->getBucket();
            $filePath = $_FILES['profile']['tmp_name'];
            $storagePath = 'imageprofile/' . $id_user . '.' . $imageFileType;

            $object = $bucket->upload(
                fopen($filePath, 'r'),
                ['name' => $storagePath]
            );

            // Get download URL
            $profilePictureUrl = $object->signedUrl(new \DateTime('9999-12-31')); // URL valid forever

        } catch (FirebaseException $e) {
            $_SESSION['errors'] = ["Gagal mengunggah file ke Firebase. Silakan coba lagi. Error: " . $e->getMessage()];
            header("Location: editProfile.php?username=$id_user");
            exit();
        }
    }

    // Prepare data for updating
    $data = [
        'nama' => $nama,
        'email' => $email,
    ];

    if (!empty($password)) {
        $data['password'] = $password;
    }

    if ($profilePictureUrl) {
        $data['profile'] = $profilePictureUrl;
    }

    // Update the user data in Firebase
    $reference = $database->getReference('users/' . $id_user);
    $reference->update($data);

    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: editProfile.php?username=$id_user");
    exit();
}
