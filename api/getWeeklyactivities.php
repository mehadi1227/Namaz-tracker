<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['userid'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method Not Allowed"]);
    exit();
}

require_once '../Database/DBconnection.php';






$tzName = $_SESSION['timezone'] ?? 'UTC';
$tz = new DateTimeZone($tzName);

$today = new DateTime('now', $tz);

// start of week (Monday)
$weekStart = (clone $today)->modify('monday this week')->format('Y-m-d');
// end of week (Sunday)
$weekEnd   = (clone $today)->modify('sunday this week')->format('Y-m-d');

try {
    $db = new DBconnection();
    $connection = $db->openConnection();

    $table = 'salah_log';

    $res = $db->WeeklyActivities($connection, (int)$_SESSION['userid'], $weekStart, $weekEnd);

    $activities = [];
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $activities[] = $row;
        }
    }

    http_response_code(200);
    echo json_encode([
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'activities' => $activities
        ]);
    // echo json_encode($activities);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
    exit();
}
