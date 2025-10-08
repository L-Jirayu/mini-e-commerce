<?php
require_once __DIR__ . '/includes/db.php';
if (empty($_SESSION['cart'])) { header('Location: cart.php'); exit; }

$ids = array_map('intval', array_keys($_SESSION['cart']));
$in  = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id IN ($in)");
$stmt->execute($ids);
$rows = $stmt->fetchAll();

$total = 0.0; $lines = [];
foreach ($rows as $r) {
  $qty = (int)$_SESSION['cart'][$r['id']];
  $line = $qty * (float)$r['price'];
  $total += $line;
  $lines[] = ['id'=>$r['id'],'name'=>$r['name'],'price'=>$r['price'],'qty'=>$qty,'line'=>$line];
}

$success = false; $order_id = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $address = trim($_POST['address'] ?? '');

  if ($name === '' || $email === '' || $address === '') {
    $error = 'กรุณากรอกข้อมูลให้ครบ';
  } 
  else {
    try {
      $pdo->beginTransaction();

      if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
        // ✅ PostgreSQL ใช้ RETURNING id
        $stmt = $pdo->prepare("
          INSERT INTO orders (customer_name, customer_email, customer_address, total_amount, created_at)
          VALUES (?,?,?,?, CURRENT_TIMESTAMP)
          RETURNING id
        ");
        $stmt->execute([$name, $email, $address, $total]);
        $order_id = (int)$stmt->fetchColumn();
      } 
      else {
        // ✅ MySQL
        $stmt = $pdo->prepare("
          INSERT INTO orders (customer_name, customer_email, customer_address, total_amount, created_at)
          VALUES (?,?,?,?, NOW())
        ");
        $stmt->execute([$name, $email, $address, $total]);
        $order_id = (int)$pdo->lastInsertId();
      }

      $oi = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?,?,?,?)");
      foreach ($lines as $L) {
        $oi->execute([$order_id, $L['id'], $L['qty'], $L['price']]);
      }

      $pdo->commit();
      $_SESSION['cart'] = [];
      $success = true;
    } 
    catch (Exception $e) {
      $pdo->rollBack();
      $error = 'เกิดข้อผิดพลาดในการบันทึกคำสั่งซื้อ';
    }
  }
}


$page_title = "ชำระเงิน";
require_once __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">ชำระเงิน</h1>

<?php if ($success): ?>
  <div class="success-box">
    <h3>คำสั่งซื้อเสร็จสมบูรณ์ 🎉</h3>
    <p>หมายเลขคำสั่งซื้อ: <strong>#<?= (int)$order_id ?></strong></p>
    <a class="btn" href="index.php">กลับสู่หน้าแรก</a>
  </div>
<?php else: ?>
  <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="checkout">
    <div class="summary">
      <h3>สรุปคำสั่งซื้อ</h3>
      <ul>
        <?php foreach ($lines as $L): ?>
          <li><span><?= htmlspecialchars($L['name']) ?> × <?= (int)$L['qty'] ?></span>
              <strong>฿<?= number_format($L['line'], 2) ?></strong></li>
        <?php endforeach; ?>
      </ul>
      <div class="total"><span>รวมทั้งหมด</span><strong>฿<?= number_format($total, 2) ?></strong></div>
    </div>

    <form class="checkout-form" method="post">
      <h3>ข้อมูลผู้รับ</h3>
      <label>ชื่อ-นามสกุล <input type="text" name="name" required></label>
      <label>Email <input type="email" name="email" required></label>
      <label>ที่อยู่จัดส่ง <textarea name="address" rows="4" required></textarea></label>
      <button type="submit" class="btn primary">ยืนยันสั่งซื้อ</button>
      <a href="cart.php" class="btn ghost">กลับไปตะกร้า</a>
    </form>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
