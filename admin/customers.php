<?php
session_start();
include './../includes/db.php';
$conn = getDbConnection();

$result = $conn->query("
	SELECT 
		customer_name AS name,
		customer_email AS email,
		customer_phone AS phone,
		customer_address AS address
	FROM (
    SELECT * FROM orders
    GROUP BY customer_name, customer_phone
	) AS unique_customers; ");
	
include '_header.php';
?>


<main class="p-6 mt-16 space-y-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Customers</h2>
    </div>

    <section class="grid grid-cols-1 gap-6">
        <div class="flex flex-col md:col-span-2 md:row-span-2 bg-white shadow rounded-lg">                 
            <div class="flex-grow">
                  <table id="order-table" class="min-w-full bg-white rounded-lg">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="py-3 px-4 text-left border-b">#</th>
                    <th class="py-3 px-4 text-left border-b">Name</th>
                    <th class="py-3 px-4 text-left border-b">Email</th>
                    <th class="py-3 px-4 text-left border-b">Phone</th>
                    <th class="py-3 px-4 text-left border-b">Address</th>
                </tr>
            </thead>
            <tbody>
				<?php $indexNo = 1; ?>
                <?php while($user = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b"><?= $indexNo++ ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($user['name']) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($user['phone']) ?></td>
                        <td class="py-2 px-4 border-b"><?= htmlspecialchars($user['address']) ?></td>                        
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
            </div>
        </div>
    </section>

</main>



<?php include '_footer.php'; ?>
