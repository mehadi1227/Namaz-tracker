<?php
session_start();

    if (isset($_SESSION['userid'])) {
         header('Location: ../../Home/Dashboard/');
        exit();
    }

    require './Login.php';
