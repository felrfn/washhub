<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db_connect.php';

$vehicleTypeId = isset($_GET['vehicle_type_id']) ? (int)$_GET['vehicle_type_id'] : 0;
$washPackageId = isset($_GET['wash_package_id']) ? (int)$_GET['wash_package_id'] : 0;

if ($vehicleTypeId <= 0 || $washPackageId <= 0) {
  echo json_encode(['error' => 'invalid_params']);
  exit;
}

try {
  $conn = db();
  $stmt = $conn->prepare('SELECT price FROM package_prices WHERE vehicle_type_id = ? AND wash_package_id = ? LIMIT 1');
  $stmt->bind_param('ii', $vehicleTypeId, $washPackageId);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) {
    echo json_encode(['price' => (float)$row['price']]);
  } else {
    echo json_encode(['price' => null]);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'db_error', 'message' => $e->getMessage()]);
}
