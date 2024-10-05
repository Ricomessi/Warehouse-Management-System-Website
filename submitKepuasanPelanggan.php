<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'firebaseconfig.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staffId = $_POST['staff'];
    $kepuasan = $_POST['kepuasan'];

    // Reference to the kepuasanPelanggan for the staff
    $ref = $database->getReference('kepuasanPelanggan/' . $staffId);
    $snapshot = $ref->getSnapshot();
    $data = $snapshot->exists() ? $snapshot->getValue() : [];

    // Delete all existing entries for the staff
    $ref->remove();

    // Add the new entry
    $ref->push([
        'nilai' => $kepuasan,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

    $_SESSION['success'] = true;
    header("Location: kepuasanPelanggan.php");
    exit();
}
