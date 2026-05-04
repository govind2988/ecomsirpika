<?php
include '../includes/db.php';
$conn = getDbConnection();

/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
*/
function getProductStockColumn(): string
{
    // Change this if your products table uses another stock column name
    return 'stock';
}

function normalizeStatus(string $status): string
{
    return trim($status);
}

function shouldReduceStock(string $oldStatus, string $newStatus): bool
{
    $fromStatuses = ['New Order', 'Processing', 'Cancelled'];
    $toStatuses   = ['Shipped', 'Completed'];

    return in_array($oldStatus, $fromStatuses, true) && in_array($newStatus, $toStatuses, true);
}

function shouldRestoreStock(string $oldStatus, string $newStatus): bool
{
    $fromStatuses = ['Shipped', 'Completed'];
    $toStatuses   = ['New Order', 'Processing', 'Cancelled'];

    return in_array($oldStatus, $fromStatuses, true) && in_array($newStatus, $toStatuses, true);
}

function getOrderLowStockItems(mysqli $conn, int $orderId): array
{
    $stockCol = getProductStockColumn();

    $sql = "
        SELECT 
            oi.product_id,
            oi.quantity AS ordered_qty,
            p.name AS product_name,
            p.`$stockCol` AS available_stock
        FROM order_items oi
        INNER JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
          AND p.`$stockCol` < oi.quantity
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    $stmt->close();
    return $items;
}

function getOrderItems(mysqli $conn, int $orderId): array
{
    $stmt = $conn->prepare("
        SELECT product_id, quantity
        FROM order_items
        WHERE order_id = ?
    ");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    $stmt->close();
    return $items;
}

function reduceOrderStock(mysqli $conn, int $orderId): void
{
    $stockCol = getProductStockColumn();
    $items = getOrderItems($conn, $orderId);

    foreach ($items as $item) {
        $productId = (int)$item['product_id'];
        $qtyNeeded = (int)$item['quantity'];

        $stmt = $conn->prepare("
            UPDATE products
            SET `$stockCol` = `$stockCol` - ?
            WHERE id = ? AND `$stockCol` >= ?
        ");
        $stmt->bind_param("iii", $qtyNeeded, $productId, $qtyNeeded);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            $stmt->close();
            throw new Exception("Insufficient stock for product ID {$productId}");
        }

        $stmt->close();
    }
}

function restoreOrderStock(mysqli $conn, int $orderId): void
{
    $stockCol = getProductStockColumn();
    $items = getOrderItems($conn, $orderId);

    foreach ($items as $item) {
        $productId = (int)$item['product_id'];
        $qtyToRestore = (int)$item['quantity'];

        $stmt = $conn->prepare("
            UPDATE products
            SET `$stockCol` = `$stockCol` + ?
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $qtyToRestore, $productId);
        $stmt->execute();
        $stmt->close();
    }
}

/*
|--------------------------------------------------------------------------
| Handle order status update
|--------------------------------------------------------------------------
*/
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['order_id']) &&
    (isset($_POST['status']) || isset($_POST['om_status']))
) {
    $id = (int)$_POST['order_id'];
    $status = isset($_POST['status']) ? normalizeStatus($_POST['status']) : normalizeStatus($_POST['om_status']);

    $validStatuses = ['New Order', 'Processing', 'Shipped', 'Completed', 'Cancelled'];
    if (!in_array($status, $validStatuses, true)) {
        header("Location: orders.php?error=invalid_status");
        exit;
    }

    $orderStmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $orderStmt->bind_param("i", $id);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();

    if (!$orderResult || $orderResult->num_rows === 0) {
        $orderStmt->close();
        header("Location: orders.php?error=invalid_order");
        exit;
    }

    $orderRow = $orderResult->fetch_assoc();
    $oldStatus = normalizeStatus($orderRow['status']);
    $newStatus = $status;
    $orderStmt->close();

    $needsStockReduction = shouldReduceStock($oldStatus, $newStatus);
    $needsStockRestore   = shouldRestoreStock($oldStatus, $newStatus);

    if ($needsStockReduction) {
        $lowStockItems = getOrderLowStockItems($conn, $id);

        if (!empty($lowStockItems)) {
            $messages = [];
            foreach ($lowStockItems as $item) {
                $messages[] = $item['product_name'] . ' (Ordered: ' . (int)$item['ordered_qty'] . ', Stock: ' . (int)$item['available_stock'] . ')';
            }

            header("Location: orders.php?stock_error=1&msg=" . urlencode(implode(' | ', $messages)));
            exit;
        }
    }

    try {
        $conn->begin_transaction();

        if ($needsStockReduction) {
            reduceOrderStock($conn, $id);
        }

        if ($needsStockRestore) {
            restoreOrderStock($conn, $id);
        }

        $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newStatus, $id);
        $updateStmt->execute();
        $updateStmt->close();

        $conn->commit();
        header("Location: orders.php?updated=1");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: orders.php?stock_error=1&msg=" . urlencode($e->getMessage()));
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Fetch orders
|--------------------------------------------------------------------------
*/
$sql = "SELECT id, customer_name, status, DATE_FORMAT(order_date, '%d/%m/%Y') AS order_date, total, created_at 
        FROM orders 
        ORDER BY order_date DESC";

$result = $conn->query($sql);

include '_header.php';
?>

<main class="p-6 mt-16 space-y-4">

    <?php if (isset($_GET['stock_error']) && $_GET['stock_error'] == '1'): ?>
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded">
            <strong>Stock update failed:</strong>
            <?= htmlspecialchars($_GET['msg'] ?? 'Unknown error') ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_order'): ?>
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded">
            Invalid order selected.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_status'): ?>
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded">
            Invalid order status selected.
        </div>
    <?php endif; ?>

    <section class="overflow-x-auto bg-white shadow rounded-lg">
        <table id="order-table11" class="min-w-full bg-white border-gray-300">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="py-3 px-4 text-left border-b">
                        <span class="flex items-center">
                            Order ID
                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                            </svg>
                        </span>
                    </th>
                    <th class="py-3 px-4 text-left border-b">
                        <span class="flex items-center">
                            Customer
                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                            </svg>
                        </span>
                    </th>
                    <th class="py-3 px-4 text-left border-b">
                        <span class="flex items-center">
                            Total
                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                            </svg>
                        </span>
                    </th>
                    <th class="py-3 px-4 text-left border-b">
                        <span class="flex items-center">
                            Status
                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                            </svg>
                        </span>
                    </th>
                    <th class="py-3 px-4 text-left border-b">
                        <span class="flex items-center">
                            Date
                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                            </svg>
                        </span>
                    </th>
                    <th class="py-3 px-4 text-left border-b max-w-32">
                        <span class="flex items-center">
                            Actions
                            <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                            </svg>
                        </span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b"><?= (int)$row['id'] ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td class="py-2 px-4 border-b">₹<?= number_format((float)$row['total'], 2) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['status']) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['order_date']) ?></td>
                        <td class="py-2 px-4 border-b">
                            <form method="POST" class="flex items-center space-x-2">
                                <input type="hidden" name="order_id" value="<?= (int)$row['id'] ?>">

                                <select name="status" class="border rounded px-2 py-1 text-sm">
                                    <option value="New Order" <?= $row['status'] === 'New Order' ? 'selected' : '' ?>>New Order</option>
                                    <option value="Processing" <?= $row['status'] === 'Processing' ? 'selected' : '' ?>>Processing</option>
                                    <option value="Shipped" <?= $row['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option value="Completed" <?= $row['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Cancelled" <?= $row['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>

                                <button type="submit" class="bg-primary text-white px-3 py-1 rounded text-sm">Update</button>

                                <button
                                    type="button"
                                    class="bg-gray-600 text-white px-3 py-1 rounded view-btn"
                                    data-order-id="<?= (int)$row['id'] ?>"
                                    data-status="<?= htmlspecialchars($row['status']) ?>"
                                >
                                    View
                                </button>

                                <div class="relative inline-block">
                                    <button onclick="printItem('invoice', <?= (int)$row['id'] ?>)" type="button" class="bg-gray-600 text-white px-3 py-1 rounded text-sm">
                                        Print
                                    </button>
                                    <div class="print-menu hidden absolute z-10 bg-white shadow border rounded mt-1 text-sm">
                                        <a href="javascript:void(0)" onclick="printItem('address', <?= (int)$row['id'] ?>)" class="block px-4 py-2 hover:bg-gray-100">Address</a>
                                        <a href="javascript:void(0)" onclick="printItem('invoice', <?= (int)$row['id'] ?>)" class="block px-4 py-2 hover:bg-gray-100">Invoice</a>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div id="orderModal" class="fixed inset-0 -top-10 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white w-full max-w-3xl rounded-lg shadow-lg relative">
                <div class="flex items-center justify-between px-4 py-3 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Order #<span id="modalOrderId"></span>
                    </h3>
                    <button onclick="closeModal()" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <div class="p-4" id="orderDetails">Loading...</div>

                <form id="statusForm" method="POST" class="p-4">
                    <input type="hidden" name="order_id" id="formOrderId">

                    <label class="block mb-2 font-semibold">Update Status:</label>

                    <div class="flex items-baseline gap-2 mb-4">
                        <select name="om_status" class="border p-2 rounded w-full">
                            <option value="New Order">New Order</option>
                            <option value="Processing">Processing</option>
                            <option value="Shipped">Shipped</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>

                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="statusModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
                <i class="fa-regular fa-circle-check text-green-600 text-4xl"></i>
                <h2 class="text-xl font-semibold mb-4 text-gray-600">Order status updated</h2>
                <button onclick="closeStatusModal()" class="bg-primary text-white px-4 py-2 rounded">OK</button>
            </div>
        </div>
    </section>
</main>

<script>
function closeModal() {
    document.getElementById('orderModal').classList.add('hidden');
}

document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const orderId = this.dataset.orderId;
        const orderStatus = this.dataset.status;

        document.getElementById('modalOrderId').innerText = orderId;
        document.getElementById('formOrderId').value = orderId;
        document.querySelector('select[name="om_status"]').value = orderStatus;

        document.getElementById('orderModal').classList.remove('hidden');

        fetch('get_order_details.php?order_id=' + orderId)
            .then(res => res.text())
            .then(html => {
                document.getElementById('orderDetails').innerHTML = html;
            })
            .catch(() => {
                document.getElementById('orderDetails').innerHTML = '<div class="text-red-600">Failed to load order details.</div>';
            });
    });
});

function togglePrintMenu(btn) {
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.print-menu').forEach(m => {
        if (m !== menu) m.classList.add('hidden');
    });
    menu.classList.toggle('hidden');
}

function printItem(type, id) {
    const url = type === 'address'
        ? 'print_address.php?order_id=' + id
        : 'print_invoice.php?order_id=' + id;
    window.open(url, '_blank');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
    const url = new URL(window.location);
    url.searchParams.delete('updated');
    window.history.replaceState({}, document.title, url.pathname);
}

window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('updated') === '1') {
        document.getElementById('statusModal').classList.remove('hidden');
    }
});
</script>

<?php include '_footer.php'; ?>