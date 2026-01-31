<?php
session_start();
if(isset($_SESSION['userid']) && !empty($_SESSION['userid'])) {
    header('Location: ./Dashboard/');
    exit();
}

if(!isset($_SESSION['userid']) || empty($_SESSION['userid'])) {
    header('Location: ../Authentication/Login/');
    exit();
}