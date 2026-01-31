<?php
session_start();
if(isset($_SESSION['userid']) && !empty($_SESSION['userid'])) {
    header('Location: ./Home/Dashboard/');
    exit();
}

if(!isset($_SESSION['userid']) || empty($_SESSION['userid'])) {
    header('Location: ./Authentication/Login/');
    exit();
}

if (isset($_COOKIE['userid']) && !empty($_COOKIE['userid'])) {

    $_SESSION['userid'] = $_COOKIE['userid'];
    $_SESSION['latitude'] = $_COOKIE['latitude'];
    $_SESSION['longitude'] = $_COOKIE['longitude'];
    $_SESSION['timezone'] = $_COOKIE['timezone'];
    $_SESSION['location_label'] = $_COOKIE['location_label'];
    header("Location: ./Home/Dashboard");
    exit();
}