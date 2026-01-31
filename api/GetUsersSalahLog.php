<?php
session_start();

if(!isset($_SESSION['userid']) || empty($_SESSION['userid'])){
    http_response_code(401);
    header('Location: ../Pages/Login.php');
    exit();
}
require_once '../Database/DBconnection.php';

$conn = new DBconnection();
$connection = $conn->openConnection();
$userSalahLog  = [];
$log= $conn->ReturnAllSalahLogOfUSer($connection, 'salah_log', $_SESSION['userid']);

if($log->num_rows === 0){
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve Salah log.']);
    exit();
}else
{

    while ($row = $log->fetch_assoc()) {
        $userSalahLog[] = $row;
    }
    http_response_code(200);
    echo(json_encode($userSalahLog));
}
