<?php
require_once('TCPDF-main/tcpdf.php');
include 'firebaseconfig.php';

if (isset($_GET['id'])) {
    $id_transaksi = $_GET['id'];

    // Fetch data of the transaksi
    $transaksiRef = $database->getReference('transaksi/' . $id_transaksi);
    $transaksiSnapshot = $transaksiRef->getSnapshot();
    $transaksiData = $transaksiSnapshot->getValue();

    if ($transaksiData) {
        $username = $transaksiData['username'];
        $id_barang = $transaksiData['id_barang'];

        // Fetch user data
        $userRef = $database->getReference('users/' . $username);
        $userSnapshot = $userRef->getSnapshot();
        $userData = $userSnapshot->getValue();

        // Fetch barang data
        $barangRef = $database->getReference('barang/' . $id_barang);
        $barangSnapshot = $barangRef->getSnapshot();
        $barangData = $barangSnapshot->getValue();

        // Check if user and barang data are fetched successfully
        if ($userData && $barangData) {
            // Create TCPDF instance
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Set document metadata
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('Your Name');
            $pdf->SetTitle('Histori Transaksi');
            $pdf->SetSubject('Histori Transaksi');
            $pdf->SetKeywords('Histori, Transaksi, PDF');

            // Set margins
            $pdf->SetMargins(10, 10, 10);

            // Add a page
            $pdf->AddPage();

            // Add content to PDF
            $html = '
            <style>
                .container { width: 100%; padding: 10px; }
                .header { text-align: center; font-size: 16px; }
                .sub-header { text-align: center; font-size: 14px; margin-top: 5px; }
                .content { margin-top: 10px; }
                .footer { margin-top: 10px; text-align: center; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
            </style>
            <div class="container">
                <div class="header">Histori Transaksi</div>
                <div class="content">
                    <table>
                        <tr>
                            <th>Nama User:</th>
                            <td>' . $userData['nama'] . '</td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td>' . $userData['role'] . '</td>
                        </tr>
                        <tr>
                            <th>Nama Barang:</th>
                            <td>' . $barangData['nama_barang'] . '</td>
                        </tr>
                        <tr>
                            <th>Jenis Barang:</th>
                            <td>' . $barangData['jenis_barang'] . '</td>
                        </tr>
                        <tr>
                            <th>Jumlah:</th>
                            <td>' . $transaksiData['jumlah'] . '</td>
                        </tr>
                        <tr>
                            <th>Tanggal Transaksi:</th>
                            <td>' . $transaksiData['tanggal_transaksi'] . '</td>
                        </tr>
                        <tr>
                            <th>Jenis Transaksi:</th>
                            <td>' . $transaksiData['jenis_transaksi'] . '</td>
                        </tr>
                    </table>
                </div>
            </div>';

            // Output HTML to PDF
            $pdf->SetFont('helvetica', '', 12);
            $pdf->writeHTML($html, true, false, true, false, '');

            // Save PDF
            $pdf->Output('histori_transaksi.pdf', 'D');
        } else {
            echo "Data pengguna atau barang tidak ditemukan.";
        }
    } else {
        echo "Data transaksi tidak ditemukan.";
    }
} else {
    echo "Permintaan tidak valid.";
}
