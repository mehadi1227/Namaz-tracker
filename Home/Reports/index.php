<?php
session_start();

    if (!isset($_SESSION['userid']) || empty($_SESSION['userid'])) {
        header('Location: ../../Authentication/Login/');
        exit();
    }

    require './Reports.php';