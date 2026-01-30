<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function json_error(string $msg, int $status = 400): void {
  http_response_code($status);
  echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

if (empty($_SESSION['userid'])) {
  json_error('Unauthorized. Please login first.', 401);
}

$userId = (int)$_SESSION['userid'];

// Path: /api/routineSave.php  ->  /Database/routine/{userid}.json
$dir  = __DIR__ . '/../Database/routine';
$file = $dir . '/' . $userId . '.json';

if (!is_dir($dir)) {
  if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
    json_error('Could not create routine folder. Check permissions.', 500);
  }
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// ------------------ GET: Load routine ------------------
if ($method === 'GET') {
  if (!file_exists($file)) {
    echo json_encode(['ok' => true, 'exists' => false], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
  }

  $raw = file_get_contents($file);
  $data = json_decode($raw ?: '', true);

  if (!is_array($data)) {
    echo json_encode(['ok' => true, 'exists' => false], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
  }

  echo json_encode([
    'ok' => true,
    'exists' => true,
    'routine' => $data
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

// ------------------ POST: Save routine ------------------
if ($method === 'POST') {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw ?: '', true);

  if (!is_array($data)) {
    json_error('Invalid JSON body.', 422);
  }

  // Validate times: allow "" or HH:MM (24h)
  $isTime = function($v): bool {
    if ($v === null) return true;
    $s = trim((string)$v);
    if ($s === '') return true;
    return (bool)preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $s);
  };

  $get = function(string $k) use ($data) {
    return isset($data[$k]) ? trim((string)$data[$k]) : '';
  };

  $sleep_from = $get('sleep_from');
  $sleep_to   = $get('sleep_to');
  $work_from  = $get('work_from');
  $work_to    = $get('work_to');

  // If one side exists, require both
  $pairCheck = function(string $label, string $a, string $b) use ($isTime): ?string {
    if (($a !== '' && $b === '') || ($a === '' && $b !== '')) return "$label: fill both From and To.";
    if (!$isTime($a)) return "$label: invalid From time.";
    if (!$isTime($b)) return "$label: invalid To time.";
    return null;
  };

  if ($err = $pairCheck('Sleep', $sleep_from, $sleep_to)) json_error($err, 422);
  if ($err = $pairCheck('Work/Study', $work_from, $work_to)) json_error($err, 422);

  $blocks = [];
  if (isset($data['blocks']) && is_array($data['blocks'])) {
    foreach ($data['blocks'] as $b) {
      if (!is_array($b)) continue;

      $name = isset($b['name']) ? trim((string)$b['name']) : '';
      $from = isset($b['from']) ? trim((string)$b['from']) : '';
      $to   = isset($b['to'])   ? trim((string)$b['to'])   : '';

      if (($from !== '' && $to === '') || ($from === '' && $to !== '')) {
        json_error('A block has only From or To. Fill both.', 422);
      }
      if (!$isTime($from)) json_error('A block has invalid From time.', 422);
      if (!$isTime($to))   json_error('A block has invalid To time.', 422);

      $blocks[] = [
        'name' => $name,
        'from' => $from,
        'to'   => $to,
      ];
    }
  }

  // Save ONLY user inputs (suggestions are recomputed each page load)
  $payload = [
    'sleep_from' => $sleep_from,
    'sleep_to'   => $sleep_to,
    'work_from'  => $work_from,
    'work_to'    => $work_to,
    'blocks'     => $blocks,
    'updated_at' => date('c'),
  ];

  $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  if ($json === false) {
    json_error('Failed to encode JSON.', 500);
  }

  $ok = file_put_contents($file, $json, LOCK_EX);
  if ($ok === false) {
    json_error('Could not write routine file. Check folder permissions.', 500);
  }

  echo json_encode([
    'ok' => true,
    'message' => 'Routine saved',
    'file' => basename($file)
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

json_error('Method Not Allowed', 405);
