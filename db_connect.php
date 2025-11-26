<?php
require_once __DIR__ . '/config.php';

/**
 * Dapatkan koneksi MySQLi.
 * @throws RuntimeException ketika gagal terkoneksi.
 */
function db(): mysqli {
  mysqli_report(MYSQLI_REPORT_OFF);
  $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if ($conn->connect_errno) {
    throw new RuntimeException('MySQL connection error: ' . $conn->connect_error);
  }
  $conn->set_charset(DB_CHARSET);
  return $conn;
}
