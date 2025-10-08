<?php
require_once __DIR__ . '/includes/db.php';
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $pid = (int)($_POST['product_id'] ?? 0);
  $qty = max(1, (int)($_POST['qty'] ?? 1));
  if ($pid > 0) $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + $qty;

  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'count' => cart_count()]);
    exit;
  }
  header('Location: cart.php'); exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($_POST['qty'] ?? [] as $pid => $q) {
    $pid = (int)$pid; $q = (int)$q;
    if ($q <= 0) unset($_SESSION['cart'][$pid]);
    else $_SESSION['cart'][$pid] = $q;
  }
  header('Location: cart.php'); exit;
}

if ($action === 'remove' && isset($_GET['id'])) {
  unset($_SESSION['cart'][(int)$_GET['id']]);
  header('Location: cart.php'); exit;
}

if ($action === 'clear') {
  $_SESSION['cart'] = [];
  header('Location: cart.php'); exit;
}

if ($action === 'count') {
  header('Content-Type: application/json');
  echo json_encode(['count' => cart_count()]); exit;
}

$items = []; $total = 0.0;
if (!empty($_SESSION['cart'])) {
  $ids = array_map('intval', array_keys($_SESSION['cart']));
  $in  = implode(',', array_fill(0, count($ids), '?'));
  $stmt = $pdo->prepare("SELECT id, name, price, image_url FROM products WHERE id IN ($in)");
  $stmt->execute($ids);
  foreach ($stmt->fetchAll() as $row) {
    $qty = (int)$_SESSION['cart'][$row['id']];
    $line = $qty * (float)$row['price'];
    $total += $line;
    $items[] = ['id'=>$row['id'],'name'=>$row['name'],'price'=>(float)$row['price'],'image_url'=>$row['image_url'],'qty'=>$qty,'line'=>$line];
  }
}

$page_title = "ตะกร้าสินค้า";
require_once __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">ตะกร้าสินค้า</h1>

<?php if (empty($items)): ?>
  <div class="empty">
    <p>ตะกร้าของคุณยังว่างอยู่</p>
    <a href="index.php" class="btn">เลือกซื้อสินค้าต่อ</a>
  </div>
<?php else: ?>
  <form method="post" action="cart.php?action=update">
    <table class="cart-table">
      <thead><tr><th>สินค้า</th><th>ราคา</th><th>จำนวน</th><th>ราคารวม</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td class="cart-prod">
            <img src="<?= htmlspecialchars($it['image_url']) ?>" alt="<?= htmlspecialchars($it['name']) ?>">
            <span><?= htmlspecialchars($it['name']) ?></span>
          </td>
          <td>฿<?= number_format($it['price'], 2) ?></td>
          <td class="qty"><input type="number" name="qty[<?= (int)$it['id'] ?>]" min="0" value="<?= (int)$it['qty'] ?>"></td>
          <td>฿<?= number_format($it['line'], 2) ?></td>
          <td><a class="link danger" href="cart.php?action=remove&id=<?= (int)$it['id'] ?>">ลบ</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td colspan="3" class="right"><strong>รวมทั้งหมด</strong></td>
            <td colspan="2"><strong>฿<?= number_format($total, 2) ?></strong></td></tr>
      </tfoot>
    </table>

    <div class="cart-actions">
      <a href="cart.php?action=clear" class="link">ล้างตะกร้า</a>
      <div class="actions-right">
        <a href="index.php" class="btn ghost">เลือกซื้อเพิ่ม</a>
        <button type="submit" class="btn">อัปเดตจำนวน</button>
        <a href="checkout.php" class="btn primary">ชำระเงิน</a>
      </div>
    </div>
  </form>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
