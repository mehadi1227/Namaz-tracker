<?php

session_start();

if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_unset();
    $true =  session_destroy();
    setcookie('userid', '', time() - 3600, "/");
    setcookie('latitude', '', time() - 3600, "/");
    setcookie('longitude', '', time() - 3600, "/");
    setcookie('timezone', '', time() - 3600, "/");
    setcookie('location_label', '', time() - 3600, "/");

    if ($true) {
        http_response_code(200);
    } else {
        http_response_code(500);
    }
}
