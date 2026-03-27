<?php
require_once '../includes/db.php';
$conn = getDbConnection();

$order_id = (int)($_GET['order_id'] ?? 0);

$order = $conn->query("
    SELECT o.*
    FROM orders o
    WHERE o.id = $order_id")->fetch_assoc();

$items = $conn->query("
    SELECT oi.*, p.name
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");
?>

<div class="flex gap-4 items-center justify-between">       
        <p><strong>Date:</strong> <br> <?= date('d-m-Y', strtotime($order['order_date'])) ?></p>
        <p><strong>Amount:</strong> <br> ₹<?= number_format($order['total'], 2) ?></p>
        <p><strong>Status:</strong>  <br><?= htmlspecialchars($order['status']) ?></p>
        <p><strong>Name:</strong> <br> <?= htmlspecialchars($order['customer_name']) ?></p>
        <p><strong>Phone:</strong> <br> <?= htmlspecialchars($order['customer_phone']) ?></p>   
</div>


<h4 class="mt-3 mb-1 font-semibold">ITEMS:</h4>
<div class="orderTablePopup">
<table class="w-full border mt-2 text-sm ">
  <thead>
    <tr class="bg-gray-100">
      <th class="py-1 px-2 text-left w-2/5">Product</th>
      <th class="py-1 px-2 text-left">Qty</th>
      <th class="py-1 px-2 text-left">Price</th>
	  <th class="py-1 px-2 text-left">Sub Total</th>
    </tr>
  </thead>
  <tbody>
	<?php $Total = 0.00; ?>
	<?php while ($item = $items->fetch_assoc()): ?>
	  <?php 
		$subTotal = $item['quantity'] * $item['price'];
		$Total += $subTotal;
	  ?>
	  <tr>
		<td class="py-1 px-2"><?= htmlspecialchars($item['name']) ?></td>
		<td class="py-1 px-2"><?= $item['quantity'] ?></td>
		<td class="py-1 px-2">₹<?= number_format($item['price'], 2) ?></td>
		<td class="py-1 px-2">₹<?= number_format($subTotal, 2) ?></td>
	  </tr>
	<?php endwhile; ?>
	</tbody>	
</table>
<div class="text-right mt-2 font-semibold text-base">
	  Order Total: ₹<?= number_format($Total, 2)?>
	</div>
</div>