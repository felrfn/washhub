<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db_connect.php';

try {
  $conn = db();
  $ok = $conn->ping();
  if (!$ok) { throw new RuntimeException('Ping gagal'); }
  echo json_encode(['status' => 'ok', 'db' => 'connected']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
