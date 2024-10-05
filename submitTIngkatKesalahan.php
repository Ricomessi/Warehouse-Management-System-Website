<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'firebaseconfig.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staffId = $_POST['staff'];
    $kesalahan = $_POST['kesalahan'];

    // Reference to the tingkatKesalahan for the staff
    $ref = $database->getReference('tingkatKesalahan/' . $staffId);
    $snapshot = $ref->getSnapshot();
    $data = $snapshot->exists() ? $snapshot->getValue() : [];

    // Delete all existing entries for the staff
    foreach ($data as $key => $entry) {
        $ref->getChild($key)->remove();
    }

    // Add the new entry
    $ref->push([
        'jumlah' => $kesalahan,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    $_SESSION['success'] = true;
    header("Location: tingkatKesalahan.php");
    exit();
}
