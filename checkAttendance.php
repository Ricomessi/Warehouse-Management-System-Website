<?php
session_start();

// Set timezone to Indonesia
date_default_timezone_set('Asia/Jakarta');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['attendanceMarked' => false]);
    exit();
}

// Include Firebase configuration
include 'firebaseconfig.php';

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);

$username = $_SESSION['username'];
$date = date('Y-m-d');

// Reference to attendance node
$attendanceReference = $database->getReference('attendance/' . $username . '/' . $date);
$attendanceSnapshot = $attendanceReference->getSnapshot();

header('Content-Type: application/json');
if ($attendanceSnapshot->exists()) {
    echo json_encode(['attendanceMarked' => true]);
} else {
    echo json_encode(['attendanceMarked' => false]);
}
