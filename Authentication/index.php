<?php
session_start();
if(isset($_SESSION['userid']) && !empty($_SESSION['userid'])) {
    header('Location: ../Home/Dashboard/');
    exit();
}

if(!isset($_SESSION['userid']) || empty($_SESSION['userid'])) {
    header('Location: ./Login/');
    exit();
}