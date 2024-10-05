<?php

// Enable URL fopen wrappers
ini_set('allow_url_fopen', 1);

// Parse the request URI to determine the requested path
$request_path = @parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize the path to avoid issues with trailing slashes
$request_path = rtrim($request_path, '/');

// Route requests based on the path
switch ($request_path) {
    case '':
    case '/':
        require 'index.php';
        break;

    case '/index':
    case '/index.php':
        require 'index.php';
        break;

    case '/login':
    case '/login.php':
        require 'login.php';
        break;

    case '/mainAdmin':
    case '/mainAdmin.php':
        require 'mainAdmin.php';
        break;

    case '/menuAdmin':
    case '/menuAdmin.php':
        require 'menuAdmin.php';
        break;

    case '/registerStaff':
    case '/registerStaff.php':
        require 'registerStaff.php';
        break;

    case '/editProfile':
    case '/editProfile.php':
        require 'editProfile.php';
        break;

    case '/deleteProfile':
    case '/deleteProfile.php':
        require 'deleteProfile.php';
        break;

    case '/historyStaff':
    case '/historyStaff.php':
        require 'historyStaff.php';
        break;

    case '/ambilBarang':
    case '/ambilBarang.php':
        require 'ambilBarang.php';
        break;

    case '/auth':
    case '/auth.php':
        require 'auth.php';
        break;

    case '/createBarang':
    case '/createBarang.php':
        require 'createBarang.php';
        break;

    case '/chartdata':
    case '/chartdata.php':
        require 'chartdata.php';
        break;

    case '/firebaseconfig':
    case '/firebaseconfig.php':
        require 'firebaseconfig.php';
        break;

    case '/handler':
    case '/handler.php':
        require 'handler.php';
        break;

    case '/handleSearch':
    case '/handleSearch.php':
        require 'handleSearch.php';
        break;

    case '/logout':
    case '/logout.php':
        require 'logout.php';
        break;

    case '/mainStaff':
    case '/mainStaff.php':
        require 'mainStaff.php';
        break;

    case '/menuStaff':
    case '/menuStaff.php':
        require 'menuStaff.php';
        break;

    case '/historiStaff':
    case '/historiStaff.php':
        require 'historiStaff.php';
        break;

    case '/printHistori':
    case '/printHistori.php':
        require 'printHistori.php';
        break;

    case '/prosesCreate':
    case '/prosesCreate.php':
        require 'prosesCreate.php';
        break;

    case '/prosesDelete':
    case '/prosesDelete.php':
        require 'prosesDelete.php';
        break;

    case '/prosesUpdate':
    case '/prosesUpdate.php':
        require 'prosesUpdate.php';
        break;

    case '/prosesUpdateProfile':
    case '/prosesUpdateProfile.php':
        require 'prosesUpdateProfile.php';
        break;

    case '/updateBarang':
    case '/updateBarang.php':
        require 'updateBarang.php';
        break;

    case '/tableBarang':
    case '/tableBarang.php':
        require 'tableBarang.php';
        break;

    case '/kepuasanPelanggan':
    case '/kepuasanPelanggan.php':
        require 'kepuasanPelanggan.php';
        break;

    case '/tingkatKesalahan':
    case '/tingkatKesalahan.php':
        require 'tingkatKesalahan.php';
        break;

    case '/submitTingkatKesalahan':
    case '/submitTingkatKesalahan.php':
        require 'submitTingkatKesalahan.php';
        break;

    case '/bestStaff':
    case '/bestStaff.php':
        require 'bestStaff.php';
        break;

    case '/absenStaff':
    case '/absenStaff.php':
        require 'absenStaff.php';
        break;

    case '/checkAttendance':
    case '/checkAttendance.php':
        require 'checkAttendance.php';
        break;

    case '/markAttendance':
    case '/markAttendance.php':
        require 'markAttendance.php';
        break;

    case '/clases/Clientes':
    case '/clases/Clientes.php':
        require __DIR__ . '/clases/Clientes.php';
        break;

    case '/clasesDAO/ClientesDAO':
    case '/clasesDAO/ClientesDAO.php':
        require __DIR__ . '/clasesDAO/ClientesDAO.php';
        break;

    default:
        // Set HTTP response code to 404
        http_response_code(404);
        echo "404 - Page not found: " . htmlspecialchars($request_path);
        exit;
}
