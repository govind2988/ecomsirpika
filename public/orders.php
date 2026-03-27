<?php
session_start();
include './../includes/db.php';
$conn = getDbConnection();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$res = $conn->query("SELECT o.id, p.name, o.quantity, o.status, o.order_date 
                     FROM orders o JOIN products p ON o.product_id = p.id 
                     WHERE o.user_id = $uid ORDER BY o.id DESC");
?>

<h2>Your Orders</h2>
<table border="1">
<tr><th>ID</th><th>Product</th><th>Qty</th><th>Status</th><th>Date</th></tr>
<?php while ($row = $res->fetch_assoc()): ?>
<tr>
  <td><?= $row['id'] ?></td>
  <td><?= htmlspecialchars($row['name']) ?></td>
  <td><?= $row['quantity'] ?></td>
  <td><?= $row['status'] ?></td>
  <td><?= $row['order_date'] ?></td>
</tr>
<?php endwhile; ?>
</table>
