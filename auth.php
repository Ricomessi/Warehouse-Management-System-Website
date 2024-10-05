<?php
session_start();
include 'firebaseconfig.php';

use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Storage\Upload;
use Kreait\Firebase\Storage\Bucket;
use Kreait\Firebase\Storage\StorageObject;

// Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['loginSubmit'])) {
    $errorsu = array();
    $username = $_POST['loginUsername'];
    $password = $_POST['loginPassword'];

    // Google reCAPTCHA
    $recaptcha_secret_key = '6LeCJx4pAAAAAGktvemOUkAuceeYhJIE_U8F7YWo';
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $recaptcha_url = "https://www.google.com/recaptcha/api/siteverify";
    $recaptcha_data = [
        'secret' => $recaptcha_secret_key,
        'response' => $recaptcha_response,
    ];

    $recaptcha_options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($recaptcha_data),
        ],
    ];

    $recaptcha_context = stream_context_create($recaptcha_options);
    $recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
    $recaptcha_result_data = json_decode($recaptcha_result, true);

    if (!$recaptcha_result_data['success']) {
        $errorsu[] = "*Captcha kosong atau tidak valid, silahkan dicoba kembali";
        $_SESSION['errorsu'] = $errorsu;
        header("Location: login.php");
        exit;
    }

    $ref = $database->getReference('users/' . $username);
    $snapshot = $ref->getSnapshot();

    if ($snapshot->exists()) {
        $userData = $snapshot->getValue();
        if ($password === $userData['password']) { // Tidak ada hash untuk password
            $_SESSION['role'] = $userData['role'];
            $_SESSION['username'] = $username;
            if ($userData['role'] === 'admin') {
                header("Location: mainAdmin.php");
                exit;
            } elseif ($userData['role'] === 'staff') {
                header("Location: mainStaff.php");
                exit;
            }
        } else {
            $errorsu[] = "*Gagal, silahkan login kembali";
            $_SESSION['errorsu'] = $errorsu;
            header("Location: login.php");
            exit;
        }
    } else {
        $errorsu[] = "*Gagal, silahkan login kembali";
        $_SESSION['errorsu'] = $errorsu;
        header("Location: login.php");
        exit;
    }
}

// Registrasi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registerSubmit'])) {
    $fullName = $_POST['registerFullName'];
    $username = $_POST['registerUsername'];
    $email = $_POST['registerEmail'];
    $password = $_POST['registerPassword'];
    $passwordConfirmation = $_POST['registerPasswordConfirmation'];
    $profilePicture = $_FILES['profile'];

    $errors = array();

    if (empty($fullName)) {
        $errors[] = "Nama tidak boleh kosong.";
    }

    if (empty($username)) {
        $errors[] = "Username tidak boleh kosong.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        $errors[] = "Username hanya boleh mengandung huruf, angka, dan underscore.";
    }

    if (empty($email)) {
        $errors[] = "Email tidak boleh kosong.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid. Mohon masukkan alamat email yang valid.";
    }

    if (empty($password)) {
        $errors[] = "Password tidak boleh kosong.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password harus memiliki setidaknya 8 karakter.";
    }

    if (empty($passwordConfirmation)) {
        $errors[] = "Konfirmasi password tidak boleh kosong.";
    } elseif ($passwordConfirmation !== $password) {
        $errors[] = "Konfirmasi password tidak sesuai dengan password.";
    }

    if (!empty($profilePicture['tmp_name']) && is_uploaded_file($profilePicture['tmp_name'])) {
        $allowedFormats = array("jpg", "jpeg", "png", "gif");

        $imageFileType = strtolower(pathinfo($profilePicture['name'], PATHINFO_EXTENSION));

        if (!in_array($imageFileType, $allowedFormats)) {
            $errors[] = "Hanya file JPG, JPEG, PNG, dan GIF yang diizinkan.";
        }

        $isValid = getimagesize($profilePicture['tmp_name']);
        if (!$isValid) {
            $errors[] = "File yang diunggah bukan gambar.";
        }
    } else {
        $errors[] = "Gagal mengunggah file. Silakan coba lagi.";
    }

    if (empty($errors)) {
        $ref = $database->getReference('users/' . $username);
        $snapshot = $ref->getSnapshot();

        if ($snapshot->exists()) {
            $errors[] = "Username sudah digunakan. Silakan pilih username yang lain.";
        } else {
            try {
                // Upload to Firebase Storage
                $bucket = $storage->getBucket();
                $filePath = $profilePicture['tmp_name'];
                $storagePath = 'imageprofile/' . $username . '.' . $imageFileType;
                $object = $bucket->upload(
                    fopen($filePath, 'r'),
                    ['name' => $storagePath]
                );

                // Get download URL
                $profilePictureUrl = $object->signedUrl(new \DateTime('now +10 years')); // URL valid forever

                // Save user data including profile picture URL
                $data = [
                    'nama' => $fullName,
                    'email' => $email,
                    'password' => $password, // Tidak ada hash untuk password
                    'role' => 'staff',
                    'profile' => $profilePictureUrl
                ];

                $ref->set($data);

                $_SESSION['success'] = "Registrasi Berhasil!";
                header("Location: registerStaff.php");
                exit;
            } catch (FirebaseException $e) {
                $errors[] = "Gagal mengunggah file. Silakan coba lagi. Error: " . $e->getMessage();
            }
        }
    } else {
        $_SESSION['errors'] = $errors;
        header("Location: registerStaff.php");
        exit;
    }
}
