<?php
require __DIR__ . '/vendor/autoload.php';

include 'firebaseconfig.php';

// Ambil data dari Firebase Realtime Database
$reference = $database->getReference('barang');
$snapshot = $reference->getSnapshot();

$barangData = $snapshot->getValue();

$jenisBarangCount = [];

if (is_array($barangData)) {
    foreach ($barangData as $barang) {
        $jenis = $barang['jenis_barang'] ?? 'Unknown';
        if (isset($jenisBarangCount[$jenis])) {
            $jenisBarangCount[$jenis]++;
        } else {
            $jenisBarangCount[$jenis] = 1;
        }
    }
}

// Format data untuk Chart.js
$data = [
    'labels' => array_keys($jenisBarangCount),
    'values' => array_values($jenisBarangCount),
];

// Menghasilkan JSON yang valid
header('Content-Type: application/json');
echo json_encode($data);
