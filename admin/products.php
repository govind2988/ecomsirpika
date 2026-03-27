<?php
include '../includes/db.php';
$conn = getDbConnection();

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: products.php");
    exit;
}

$result = $conn->query("SELECT * FROM products");
include '_header.php';
?>




<main class="p-6 mt-16 space-y-4">
   

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">All Products</h2>
        <div class="space-x-3">
            <a href="upload_stock.php" class="bg-gray-600  text-white font-medium px-4 py-2 rounded">
                Upload Stock (Excel)
            </a>
            <a href="product_create.php" class="bg-primary text-white font-medium px-4 py-2 rounded">
                + New Product
            </a>
        </div>
    </div>
    


    <section class="grid grid-cols-1 gap-6">
        <div class="flex flex-col md:col-span-2 md:row-span-2 bg-white shadow rounded-lg">                 
            <div class="flex-grow">
                <table id="order-table" class="min-w-full bg-white">
                    <thead class="bg-gray-200 text-gray-700">
                        <tr>
                            <th class="py-3 px-4 text-left border-b">                        
                                <span class="flex items-center">
                                    ID  
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="py-3 px-4 text-left border-b">

                            <span class="flex items-center">
                                    Name  
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>

                            </th>
                            <th class="py-3 px-4 text-left border-b">
                                <span class="flex items-center">
                                    MRP  
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
							<th class="py-3 px-4 text-left border-b">
                                <span class="flex items-center">
                                    Offer Price  
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="py-3 px-4 text-left border-b">
                                <span class="flex items-center">
                                    Stock  
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="py-3 px-4 text-left border-b">
                                <span class="flex items-center">
                                    Image  
                                    <svg class="w-4 h-4 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 15 4 4 4-4m0-6-4-4-4 4" />
                                    </svg>
                                </span>
                            </th>
                            <th class="py-3 px-4 text-left border-b">
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
                                <td class="py-2 px-4 border-b"><?= htmlspecialchars($row['name']) ?></td>
                                <td class="py-2 px-4 border-b">₹<?= $row['rrp_price'] ?></td>
								<td class="py-2 px-4 border-b">₹<?= $row['sale_price'] ?></td>
                                <td class="py-2 px-4 border-b"><?= $row['stock'] ?></td>
                                <td class="py-2 px-4 border-b">
                                    <img src="../uploads/<?= $row['image'] ?>" alt="Product Image" class="w-8 h-8 object-cover rounded">
                                </td>
                                <td class="py-2 px-4 border-b">
                                    <a href="edit_product.php?id=<?= $row['id'] ?>" class="bg-gray-600 text-white px-3 py-1 rounded view-btn text-sm">Edit</a>
                                    <a href="products.php?delete=<?= $row['id'] ?>" class="bg-primary text-white px-3 py-1 rounded view-btn text-sm" onclick="return confirm('Delete this product?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </section>

</main>


<?php include '_footer.php'; ?>
