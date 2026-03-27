<?php
require_once '../includes/db.php';
$conn = getDbConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $oid = (int)$_POST['order_id'];
    $status = $conn->real_escape_string($_POST['status']);
    $conn->query("UPDATE orders SET status = '$status' WHERE id = $oid");
	header("Location: dashboard.php?statusupdated=1");
	exit;
	}

// Fetch metrics
$orderCount   = (int)$conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc()['count'];
$productCount = (int)$conn->query("SELECT COUNT(*) AS count FROM products")->fetch_assoc()['count'];
$userCount    = (int)$conn->query("SELECT COUNT(*) AS count FROM (
									SELECT customer_name, customer_phone
									FROM orders
									GROUP BY customer_name, customer_phone) AS unique_customers;")->fetch_assoc()['count'];
$salesRow     = $conn->query("SELECT SUM(total) AS sum FROM orders")->fetch_assoc();
$totalSales   = $salesRow['sum'] ?? 0;

// Fetch recent orders
$orders = $conn->query("
    SELECT id, user_id, total AS total_amount, status, DATE_FORMAT(order_date, '%d/%m/%Y') AS order_date, customer_name, customer_phone
    FROM orders
    ORDER BY order_date DESC
    LIMIT 10");

// Include shared header
include '_header.php';
?>

       <main class="p-6 mt-16 space-y-4">
			<script src="https://cdn.tailwindcss.com"></script>
            <div class="flex flex-col space-y-6 md:space-y-0 md:flex-row justify-between">
                <div class="mr-6">
                    <h1 class="text-3xl uppercase font-bold mb-2">Dashboard</h1>
                </div>
            </div>

             <!-- Sales Data -->

             <section class="grid md:grid-cols-2 xl:grid-cols-4 gap-6">
                <div class="flex items-center p-8 bg-white shadow rounded-lg">
                    <div
                        class="inline-flex flex-shrink-0 items-center justify-center h-16 w-16 text-purple-600 bg-purple-100 rounded-full mr-6">
                     <i class="fa-solid fa-basket-shopping text-2xl"></i>
                    </div>
                    <div>
                        <span class="block text-2xl font-bold"><?= $orderCount ?></span>
                        <span class="block text-gray-500">Total Orders</span>
                    </div>
                </div>
                <div class="flex items-center p-8 bg-white shadow rounded-lg">
                    <div
                        class="inline-flex flex-shrink-0 items-center justify-center h-16 w-16 text-green-600 bg-green-100 rounded-full mr-6">
                         <i class="fa-solid fa-user-group text-2xl"></i>
                    </div>
                    <div>
                        <span class="block text-2xl font-bold"><?= $userCount ?></span>
                        <span class="block text-gray-500">Total Customers</span>
                    </div>
                </div>
                <div class="flex items-center p-8 bg-white shadow rounded-lg">
                    <div
                        class="inline-flex flex-shrink-0 items-center justify-center h-16 w-16 text-red-600 bg-red-100 rounded-full mr-6">
                         <i class="fa-solid fa-boxes-stacked text-2xl"></i>
                    </div>
                    <div>
                        <span class="inline-block text-2xl font-bold"><?= $productCount ?></span>
                        <span class="block text-gray-500">Total Products</span>
                    </div>
                </div>
                <div class="flex items-center p-8 bg-white shadow rounded-lg">
                    <div
                        class="inline-flex flex-shrink-0 items-center justify-center h-16 w-16 text-blue-600 bg-blue-100 rounded-full mr-6">
                       <i class="fa-solid fa-chart-column text-2xl"></i>
                    </div>
                    <div>
                        <span class="block text-2xl font-bold">₹<?= number_format($totalSales, 2) ?></span>
                        <span class="block text-gray-500">Total Sales</span>
                    </div>
                </div>
            </section>

             <!-- Recent Orders -->

             <section class="grid grid-cols-1 gap-6">
                <div class="flex flex-col md:col-span-2 md:row-span-2 bg-white shadow rounded-lg">
                    <div class="flex justify-between items-center px-6 py-3 border-b border-gray-100">
                    <h2 class="font-semibold text-xl uppercase">Recent Orders</h2>
                    <a href="orders.php" class="bg-primary text-white px-3 py-1 rounded viewall-btn text-sm">View All</a>

                    </div>
                    

                    <div class="flex-grow">
                       <table class="min-w-full bg-white shadow rounded">
                          <thead>
                            <tr class="bg-gray-200 text-left text-sm">
                              <th class="py-2 px-4">Order ID</th>
                              <th class="py-2 px-4">Customer Name</th>
                              <th class="py-2 px-4">Mobile No</th>
                              <th class="py-2 px-4">Amount</th>
                              <th class="py-2 px-4">Status</th>
                              <th class="py-2 px-4">Date</th>
                              <th class="py-2 px-4">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php while ($row = $orders->fetch_assoc()): ?>
                            <tr class="border-t text-sm">
                              <td class="py-2 px-4"><?= $row['id'] ?></td>
                              <td class="py-2 px-4"><?= htmlspecialchars($row['customer_name']) ?></td>
                              <td class="py-2 px-4"><?= htmlspecialchars($row['customer_phone']) ?></td>
                              <td class="py-2 px-4">₹<?= number_format($row['total_amount'], 2) ?></td>
                              <td class="py-2 px-4"><?= htmlspecialchars($row['status']) ?></td>
                              <td class="py-2 px-4"><?= $row['order_date'] ?></td>
                              <td class="py-2 px-4">
                                <button 
									class="bg-gray-600 text-white px-3 py-1 rounded view-btn" 
									data-order-id="<?= $row['id'] ?>" 
									data-status="<?= htmlspecialchars($row['status']) ?>">View</button>
                              </td>
                            </tr>
                            <?php endwhile; ?>
                          </tbody>
                        </table>

                    </div>
                </div>
            </section>

            <!-- Order Modal -->
            <div id="orderModal" class="fixed inset-0 -top-10 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
              <div class="bg-white w-full max-w-3xl rounded-lg shadow-lg relative">
                <div class="flex items-center justify-between px-4 py-3 border-b rounded-t dark:border-gray-600 border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Order #<span id="modalOrderId"></span>
                </h3>
                <button onclick="closeModal()" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="default-modal">
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
                      <select name="status" class="border p-2 rounded w-full">
                        <option value="New Order">New Order</option>
                        <option value="Processing">Processing</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                      </select>
                      <button class="bg-primary text-white px-4 py-2 rounded">Update</button>
                      </div>
                    </form>
              </div>
            </div>
			
			<!-- Order Status Modal -->
			<div id="statusModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
			  <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
				<h2 class="text-xl font-semibold mb-4 text-green-700">✅ Order status updated</h2>
				<button onclick="closeStatusModal()" class="bg-primary text-white px-4 py-2 rounded">OK</button>
			  </div>
			</div>			
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
		document.querySelector('select[name="status"]').value = orderStatus;

		document.getElementById('orderModal').classList.remove('hidden');

		fetch('get_order_details.php?order_id=' + orderId)
		  .then(res => res.text())
		  .then(html => {
			document.getElementById('orderDetails').innerHTML = html;
		  });
	  });
	});
	//
	// order status modal handlers
	//
	function closeStatusModal() {
	  document.getElementById('statusModal').classList.add('hidden');
	  // Remove query string from URL without reloading
	  const url = new URL(window.location);
	  url.searchParams.delete('statusupdated');
	  window.history.replaceState({}, document.title, url.pathname);
	}

	window.addEventListener('DOMContentLoaded', () => {
	  const urlParams = new URLSearchParams(window.location.search);
	  if (urlParams.get('statusupdated') === '1') {
		document.getElementById('statusModal').classList.remove('hidden');
	  }
	});
</script>

<?php include '_footer.php'; ?>