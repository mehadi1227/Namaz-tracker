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

function json_error(array $payload, int $status = 422): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

function validateDateYmd(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

$SALAH_LOG_KEYS = [
    'Fajr_Fard','Fajr_Sunnah','Fajr_Nafl','Fajr_Status',
    'Dhuhr_Fard','Dhuhr_Sunnah','Dhuhr_Nafl','Dhuhr_Status',
    'Asr_Fard','Asr_Sunnah','Asr_Nafl','Asr_Status',
    'Maghrib_Fard','Maghrib_Sunnah','Maghrib_Nafl','Maghrib_Status',
    'Isha_Fard','Isha_Sunnah','Isha_Nafl','Isha_Status',
];

$prayerDate = trim((string)($_POST['prayer_date'] ?? ''));
if ($prayerDate === '' || !validateDateYmd($prayerDate)) {
    json_error(['prayer_dateErr' => 'Invalid prayer_date. Use YYYY-MM-DD.'], 422);
}

$allowedStatus = ['On time', 'Late', 'Missed'];

$PrayerLogs = [];
foreach ($SALAH_LOG_KEYS as $key) {
    if (!isset($_POST[$key])) continue;

    $val = trim((string)$_POST[$key]);
    if ($val === '') continue;

    if (str_ends_with($key, '_Status')) {
        if (!in_array($val, $allowedStatus, true)) {
            json_error([$key . 'Err' => 'Invalid status value.'], 422);
        }
        $PrayerLogs[$key] = $val;
    } else {
        if (!ctype_digit($val)) {
            json_error([$key . 'Err' => 'Must be a non-negative integer.'], 422);
        }
        $PrayerLogs[$key] = (int)$val;
    }
}

if (empty($PrayerLogs)) {
    json_error(['emptyFieldsErr' => 'At least one salah field is required.'], 422);
}

try {
    $db = new DBconnection();
    $connection = $db->openConnection();

    $table = 'salah_log'; 

    $res = $db->CheckExistingSalahLog($connection, $table, (int)$_SESSION['userid'], $prayerDate);

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $id = (int)$row['id'];
        $ok = $db->UpdateExistingSalahLog($connection, $table, $PrayerLogs, (int)$_SESSION['userid'], $prayerDate, $id);
    } else {
        $ok = $db->InsertNewSalahLog($connection, $table, $PrayerLogs, (int)$_SESSION['userid'], $prayerDate);
    }

    if ($ok) {
        http_response_code(200);
        echo json_encode(["message" => "Salah log saved successfully."]);
        exit;
    }

    http_response_code(500);
    echo json_encode(["error" => "Failed to save salah log."]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
    exit;
}
