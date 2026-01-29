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

function validateDateYmd(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

$prayerDate = trim((string)($_POST['prayer_date'] ?? ''));
if ($prayerDate === '' || !validateDateYmd($prayerDate)) {
    json_error(['prayer_dateErr' => 'Invalid prayer_date. Use YYYY-MM-DD.'], 422);
}

try {
    $db = new DBconnection();
    $connection = $db->openConnection();

    $table = 'salah_log';

    $res = $db->CheckExistingSalahLog($connection, $table, (int)$_SESSION['userid'], $prayerDate);

    if ($res && $res->num_rows > 0) {
        $log = $res->fetch_assoc();
        http_response_code(200);
        echo json_encode($log);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Enter to save today's salah log"]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
    exit;
}
