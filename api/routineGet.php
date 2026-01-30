<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

function json_error(string $msg, int $status=400): void {
  http_response_code($status);
  echo json_encode(['ok'=>false,'error'=>$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

if (empty($_SESSION['userid'])) json_error('Unauthorized', 401);

$userId = (int)$_SESSION['userid'];
$path = __DIR__ . '/../Database/routine/' . $userId . '.json';

if (!file_exists($path)) {
  echo json_encode(['ok'=>true,'exists'=>false], JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents($path);
$data = json_decode($raw, true);

if (!is_array($data)) {
  echo json_encode(['ok'=>true,'exists'=>false], JSON_UNESCAPED_UNICODE);
  exit;
}

echo json_encode([
  'ok' => true,
  'exists' => true,
  'routine' => $data
], JSON_UNESCAPED_UNICODE);
