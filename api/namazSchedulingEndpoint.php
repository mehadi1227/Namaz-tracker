<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

function json_error(string $msg, int $status = 400): void {
  http_response_code($status);
  echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  exit;
}

// Require login (you store userid in session on login)
if (empty($_SESSION['userid'])) {
  json_error('Unauthorized. Please login first.', 401);
}

// Helpers: GET param OR fallback to session
function get_param(string $key, array $aliases = [], ?string $sessionKey = null): ?string {
  $keys = array_merge([$key], $aliases);

  foreach ($keys as $k) {
    if (isset($_GET[$k]) && trim((string)$_GET[$k]) !== '') {
      return trim((string)$_GET[$k]);
    }
  }

  if ($sessionKey !== null && isset($_SESSION[$sessionKey]) && trim((string)$_SESSION[$sessionKey]) !== '') {
    return trim((string)$_SESSION[$sessionKey]);
  }

  return null;
}

$lat = get_param('lat', ['latitude'], 'latitude');
$lng = get_param('lng', ['lon', 'longitude'], 'longitude');

$tz  = get_param('tz', ['timezonestring'], 'timezone'); // IANA tz like "Asia/Dhaka"
$locationLabel = $_SESSION['location_label'] ?? null;

// Optional tuning (you can store these in DB later per-user)
$method = isset($_GET['method']) ? (int)$_GET['method'] : 1; // example default
$school = isset($_GET['school']) ? (int)$_GET['school'] : 0; // 0=Shafi, 1=Hanafi

// Date: dd-mm-YYYY. If tz is valid, compute date in that tz for correctness.
$date = null;
if (!empty($_GET['date'])) {
  $date = trim((string)$_GET['date']);
} else {
  $tzObjForDate = null;
  if ($tz && in_array($tz, timezone_identifiers_list(), true)) {
    $tzObjForDate = new DateTimeZone($tz);
  }
  $date = (new DateTime('now', $tzObjForDate ?: new DateTimeZone('UTC')))->format('d-m-Y');
}

// Validate lat/lng from session or GET
if ($lat === null || $lng === null) json_error('Location missing. Please set location in profile or click "Use my location".', 422);
if (!is_numeric($lat) || !is_numeric($lng)) json_error('lat/lng must be numeric', 422);

$latF = (float)$lat;
$lngF = (float)$lng;

if ($latF < -90 || $latF > 90 || $lngF < -180 || $lngF > 180) {
  json_error('Invalid lat/lng range', 422);
}

// Only send timezonestring if it's a valid IANA timezone (avoid bad requests)
$tzToSend = '';
if ($tz && in_array($tz, timezone_identifiers_list(), true)) {
  $tzToSend = $tz;
}

$query = [
  'latitude'  => $latF,
  'longitude' => $lngF,
  'method'    => $method,
  'school'    => $school,
  'iso8601'   => 'true',
];

if ($tzToSend !== '') {
  $query['timezonestring'] = $tzToSend;
}

$url = 'https://api.aladhan.com/v1/timings/' . rawurlencode($date) . '?' . http_build_query($query);

// Call AlAdhan API
$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CONNECTTIMEOUT => 6,
  CURLOPT_TIMEOUT => 12,
  CURLOPT_HTTPHEADER => [
    'Accept: application/json',
    'User-Agent: SalahTracker/1.0'
  ],
]);

$body = curl_exec($ch);
$err  = curl_error($ch);
$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($body === false) json_error('cURL error: ' . $err, 502);

$data = json_decode($body, true);
if (!is_array($data)) json_error('Bad JSON from upstream API', 502);

if ($http !== 200 || ($data['code'] ?? 0) !== 200) {
  json_error('Upstream API error', 502);
}

$timings = $data['data']['timings'] ?? [];
$metaTz  = $data['data']['meta']['timezone'] ?? ($tzToSend !== '' ? $tzToSend : 'UTC');

function clean_time(string $t): string {
  if (preg_match('/^\s*(\d{1,2}:\d{2})/', $t, $m)) return $m[1];
  return $t;
}

$greg = $data['data']['date']['gregorian']['date'] ?? null; // dd-mm-YYYY
$tzObj = new DateTimeZone($metaTz);

$todayYmd = $greg
  ? (DateTime::createFromFormat('d-m-Y', $greg, $tzObj)?->format('Y-m-d') ?? (new DateTime('now', $tzObj))->format('Y-m-d'))
  : (new DateTime('now', $tzObj))->format('Y-m-d');

$now = new DateTime('now', $tzObj);

$prayers = ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
$nextPrayer = null;
$secondsToNext = null;

foreach ($prayers as $p) {
  if (!isset($timings[$p])) continue;
  $time = clean_time((string)$timings[$p]);
  $dt = DateTime::createFromFormat('Y-m-d H:i', $todayYmd . ' ' . $time, $tzObj);

  if ($dt && $dt > $now) {
    $nextPrayer = $p;
    $secondsToNext = $dt->getTimestamp() - $now->getTimestamp();
    break;
  }
}

if ($nextPrayer === null && isset($timings['Fajr'])) {
  $time = clean_time((string)$timings['Fajr']);
  $dt = DateTime::createFromFormat('Y-m-d H:i', $todayYmd . ' ' . $time, $tzObj);
  if ($dt) {
    $dt->modify('+1 day');
    $nextPrayer = 'Fajr';
    $secondsToNext = $dt->getTimestamp() - $now->getTimestamp();
  }
}

echo json_encode([
  'ok' => true,
  'source' => 'api.aladhan.com',
  'user' => [
    'userid' => $_SESSION['userid'],
    'latitude' => $latF,
    'longitude' => $lngF,
    'timezone' => $tzToSend ?: $metaTz,
    'location_label' => $locationLabel
  ],
  'timings' => $timings,
  'date' => $data['data']['date'] ?? null,
  'meta' => $data['data']['meta'] ?? null,
  'next' => [
    'prayer' => $nextPrayer,
    'seconds' => $secondsToNext
  ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
