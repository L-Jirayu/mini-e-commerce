<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * ลำดับอ่านค่าการเชื่อมต่อ:
 * 1) DATABASE_URL (เช่น postgres://user:pass@host:5432/db)
 * 2) ชุด DB_* (เดิมของคุณ: DB_DRIVER, DB_HOST, DB_NAME, DB_USER, DB_PASS)
 */

$pdo = null;

// 1) ลองใช้ DATABASE_URL ก่อน (Render จะใส่ให้เอง)
$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl) {
  // PDO pgsql รับรูปแบบ DSN พิเศษของ Render ได้โดยตรง
  $pdo = new PDO($databaseUrl, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} else {
  // 2) fallback ไปใช้ DB_* env ชุดเดิม
  $driver = getenv('DB_DRIVER') ?: 'pgsql'; // pgsql or mysql
  $host   = getenv('DB_HOST')   ?: '127.0.0.1';
  $name   = getenv('DB_NAME')   ?: 'mini_shop';
  $user   = getenv('DB_USER')   ?: 'postgres';
  $pass   = getenv('DB_PASS')   ?: '1234';
  $port   = getenv('DB_PORT')   ?: ($driver === 'mysql' ? '3306' : '5432');

  if ($driver === 'pgsql') {
    $dsn = "pgsql:host={$host};port={$port};dbname={$name}";
  } else {
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
  }

  $pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
}

// ==== ตัวอย่างฟังก์ชันเดิมของคุณ ====
function cart_count(): int {
  if (empty($_SESSION['cart'])) return 0;
  return array_sum(array_map('intval', $_SESSION['cart']));
}
