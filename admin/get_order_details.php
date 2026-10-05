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

$itemList = [];
$Total = 0.00;
$hasBulk = false;
$hasRetail = false;

if ($items && $items->num_rows > 0) {
    while ($item = $items->fetch_assoc()) {
        $isBulk = !empty($item['is_bulk']);
        if ($isBulk) {
            $hasBulk = true;
        } else {
            $hasRetail = true;
            $subTotal = $item['quantity'] * $item['price'];
            $Total += $subTotal;
        }
        $itemList[] = $item;
    }
}
?>

<div class="flex gap-4 items-center justify-between">       
        <p><strong>Date:</strong> <br> <?= date('d-m-Y', strtotime($order['order_date'])) ?></p>
        <p><strong>Amount:</strong> <br> 
            <?php if ($hasRetail && $hasBulk): ?>
                ₹<?= number_format($Total, 2) ?> <span class="text-xs text-gray-500 font-normal">(+ Bulk)</span>
            <?php elseif ($hasBulk && !$hasRetail): ?>
                <span class="text-green-700 font-semibold text-xs">Price on Request</span>
            <?php else: ?>
                ₹<?= number_format((float)$order['total'], 2) ?>
            <?php endif; ?>
        </p>
        <p><strong>Status:</strong>  <br><?= htmlspecialchars($order['status']) ?></p>
        <p><strong>Name:</strong> <br> <?= htmlspecialchars($order['customer_name']) ?></p>
        <p><strong>Phone:</strong> <br> <?= htmlspecialchars($order['customer_phone']) ?></p>   
</div>

<h4 class="mt-3 mb-1 font-semibold">ITEMS:</h4>
<div class="orderTablePopup">
<table class="w-full border mt-2 text-sm">
  <thead>
    <tr class="bg-gray-100">
      <th class="py-1 px-2 text-left w-2/5">Product</th>
      <th class="py-1 px-2 text-left">Qty</th>
      <th class="py-1 px-2 text-left">Price</th>
	  <th class="py-1 px-2 text-left">Sub Total</th>
    </tr>
  </thead>
  <tbody>
	<?php foreach ($itemList as $item): ?>
	  <?php 
        $isBulk = !empty($item['is_bulk']);
        $subTotal = $isBulk ? 0 : ($item['quantity'] * $item['price']);
	  ?>
	  <tr class="border-b">
		<td class="py-1.5 px-2">
            <span class="font-medium"><?= htmlspecialchars($item['name']) ?></span>
            <?php if ($isBulk): ?>
                <span class="inline-block ml-2 px-2 py-0.5 text-xs bg-green-100 text-green-800 rounded-full font-semibold">Bulk Order</span>
            <?php endif; ?>
        </td>
		<td class="py-1.5 px-2"><?= $item['quantity'] ?></td>
		<td class="py-1.5 px-2">
            <?php if ($isBulk): ?>
                <span class="text-gray-500 italic text-xs">Price on Request</span>
            <?php else: ?>
                ₹<?= number_format($item['price'], 2) ?>
            <?php endif; ?>
        </td>
		<td class="py-1.5 px-2">
            <?php if ($isBulk): ?>
                <span class="text-gray-500 italic text-xs">Price on Request</span>
            <?php else: ?>
                ₹<?= number_format($subTotal, 2) ?>
            <?php endif; ?>
        </td>
	  </tr>
	<?php endforeach; ?>
	</tbody>	
</table>
<div class="text-right mt-3 font-semibold text-base">
    <?php if ($hasRetail && $hasBulk): ?>
        Order Total: ₹<?= number_format($Total, 2) ?> <span class="text-xs font-normal text-gray-500">(+ Bulk items on Request)</span>
    <?php elseif ($hasBulk && !$hasRetail): ?>
        Order Total: <span class="text-green-700 font-bold">Price on Request (Bulk Inquiry)</span>
    <?php else: ?>
        Order Total: ₹<?= number_format($Total, 2) ?>
    <?php endif; ?>
</div>
</div>