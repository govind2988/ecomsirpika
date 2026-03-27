<?php
include '../includes/db.php';
$conn = getDbConnection();

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['order_id']) &&
    (isset($_POST['status']) || isset($_POST['om_status']))) {
	
    $id = intval($_POST['order_id']);
	if(isset($_POST['status']))
		{
		$status = $conn->real_escape_string($_POST['status']);
		}
	else
		{
		$status = $conn->real_escape_string($_POST['om_status']);
		}
    $conn->query("UPDATE orders SET status = '$status' WHERE id = $id");
	header("Location: orders.php?updated=1");
	exit;
	}

// Fetch orders (order_items are handled in modal)
$sql = "SELECT id, customer_name, status, DATE_FORMAT(order_date, '%d/%m/%Y') AS order_date, total, created_at 
        FROM orders 
        ORDER BY order_date DESC";
		
$result = $conn->query($sql);

include '_header.php';
?>

<main class="p-6 mt-16 space-y-4">
   
	
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
                        <td class="py-2 px-4 border-b"><?= $row['id'] ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td class="py-2 px-4 border-b">₹<?= number_format($row['total'], 2) ?></td>
                        <td class="py-2 px-4 border-b"><?= $row['status'] ?></td>
                        <td class="py-2 px-4 border-b"><?= $row['order_date'] ?></td>
                        <td class="py-2 px-4 border-b">
                            <form method="POST" class="flex items-center space-x-2">
                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                <select name="status" class="border rounded px-2 py-1 text-sm">
                                    <option <?= $row['status'] == 'New Order' ? 'selected' : '' ?>>New Order</option>
                                    <option <?= $row['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                                    <option <?= $row['status'] == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                    <option <?= $row['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option <?= $row['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="bg-primary text-white px-3 py-1 rounded  text-sm">Update</button>

                              	<button type="button"
									class="bg-gray-600 text-white px-3 py-1 rounded view-btn" 
									data-order-id="<?= $row['id'] ?>" 
									data-status="<?= htmlspecialchars($row['status']) ?>">View</button>

                                <div class="relative inline-block">
                                    <button  onclick="printItem('invoice', <?= $row['id'] ?>)" type="button" class="bg-gray-600 text-white px-3 py-1 rounded text-sm">Print</button>
                                     <div class="print-menu hidden absolute z-10 bg-white shadow border rounded mt-1 text-sm">
                                        <a href="javascript:void(0)" onclick="printItem('address', <?= $row['id'] ?>)" class="block px-4 py-2 hover:bg-gray-100">Address</a>
                                        <a href="javascript:void(0)" onclick="printItem('invoice', <?= $row['id'] ?>)" class="block px-4 py-2 hover:bg-gray-100">Invoice</a>
                                    </div>
                                </div>

                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
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
                     <select name="om_status" class="border p-2 rounded w-full">
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
          <i class="fa-regular fa-circle-check text-green-600 text-4xl"></i>
				<h2 class="text-xl font-semibold mb-4 text-gray-600">Order status updated</h2>
				<button onclick="closeStatusModal()" class="bg-primary text-white px-4 py-2 rounded">OK</button>
			  </div>
			</div>			
</main>

<!-- Modal & Print Scripts -->
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
	//
	// order status modal handlers
	//
	function closeStatusModal() {
	  document.getElementById('statusModal').classList.add('hidden');
	  // Remove query string from URL without reloading
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
