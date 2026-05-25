<?php
include '../includes/db.php';
$conn = getDbConnection();

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: products.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| SEARCH + SORT
|--------------------------------------------------------------------------
*/

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Allowed sortable columns
$allowedSortColumns = [
    'id',
    'name',
    'rrp_price',
    'sale_price',
    'stock'
];

// Sort column
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $allowedSortColumns)
    ? $_GET['sort']
    : 'id';

// Sort direction
$order = (isset($_GET['order']) && strtolower($_GET['order']) == 'asc')
    ? 'ASC'
    : 'DESC';

// Toggle next order
$nextOrder = $order == 'ASC' ? 'desc' : 'asc';

// Build query
$sql = "SELECT * FROM products";

if (!empty($search)) {
    $searchEscaped = $conn->real_escape_string($search);
    $sql .= " WHERE name LIKE '%$searchEscaped%'";
}

$sql .= " ORDER BY $sort $order";

$result = $conn->query($sql);

include '_header.php';

/*
|--------------------------------------------------------------------------
| SORT LINK FUNCTION
|--------------------------------------------------------------------------
*/
function sortLink($column, $label, $currentSort, $currentOrder, $search)
{
    $nextOrder = ($currentSort == $column && $currentOrder == 'ASC')
        ? 'desc'
        : 'asc';

    $arrow = '';

    if ($currentSort == $column) {
        $arrow = $currentOrder == 'ASC' ? '↑' : '↓';
    }

    return "
        <a href='?sort={$column}&order={$nextOrder}&search=" . urlencode($search) . "'
           class='flex items-center gap-1 hover:text-primary'>
           {$label} {$arrow}
        </a>
    ";
}
?>

<main class="p-6 mt-16 space-y-4">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Products</h2>

        <div class="space-x-3">
            <a href="upload_stock.php"
               class="bg-gray-600 text-white font-medium px-4 py-2 rounded">
                Upload Stock (Excel)
            </a>

            <a href="product_create.php"
               class="bg-primary text-white font-medium px-4 py-2 rounded">
                + New Product
            </a>
        </div>
    </div>

    <!-- SEARCH FORM -->
    <form method="GET" class="mb-4 flex gap-3">
        <input
            type="text"
            name="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Search product name..."
            class="border border-gray-300 rounded px-4 py-2 w-full md:w-80"
        >

        <button
            type="submit"
            class="bg-primary text-white px-5 py-2 rounded">
            Search
        </button>

        <?php if (!empty($search)): ?>
            <a href="products.php"
               class="bg-gray-500 text-white px-5 py-2 rounded">
                Reset
            </a>
        <?php endif; ?>
    </form>

    <section class="grid grid-cols-1 gap-6">

        <div class="flex flex-col md:col-span-2 md:row-span-2 bg-white shadow rounded-lg">

            <div class="flex-grow overflow-x-auto">

                <table id="order-table" class="min-w-full bg-white">

                    <thead class="bg-gray-200 text-gray-700">
                        <tr>

                            <th class="py-3 px-4 text-left border-b">
                                <?= sortLink('id', 'ID', $sort, $order, $search) ?>
                            </th>

                            <th class="py-3 px-4 text-left border-b">
                                <?= sortLink('name', 'Name', $sort, $order, $search) ?>
                            </th>

                            <th class="py-3 px-4 text-left border-b">
                                <?= sortLink('rrp_price', 'MRP', $sort, $order, $search) ?>
                            </th>

                            <th class="py-3 px-4 text-left border-b">
                                <?= sortLink('sale_price', 'Offer Price', $sort, $order, $search) ?>
                            </th>

                            <th class="py-3 px-4 text-left border-b">
                                <?= sortLink('stock', 'Stock', $sort, $order, $search) ?>
                            </th>

                            <th class="py-3 px-4 text-left border-b">
                                Image
                            </th>

                            <th class="py-3 px-4 text-left border-b">
                                Actions
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr class="hover:bg-gray-50">

                                <td class="py-2 px-4 border-b">
                                    <?= $row['id'] ?>
                                </td>

                                <td class="py-2 px-4 border-b">
                                    <?= htmlspecialchars($row['name']) ?>
                                </td>

                                <td class="py-2 px-4 border-b">
                                    ₹<?= number_format($row['rrp_price'], 2) ?>
                                </td>

                                <td class="py-2 px-4 border-b">
                                    ₹<?= number_format($row['sale_price'], 2) ?>
                                </td>

                                <td class="py-2 px-4 border-b">
                                    <?= $row['stock'] ?>
                                </td>

                                <td class="py-2 px-4 border-b">

                                    <?php
                                    $productImage = !empty($row['image'])
                                        ? '../uploads/' . $row['image']
                                        : '../assets/images/placeholder.png';
                                    ?>

                                    <img
                                        src="<?= $productImage ?>"
                                        alt="Product Image"
                                        class="w-10 h-10 object-cover rounded"
                                    >
                                </td>

                                <td class="py-2 px-4 border-b space-x-2">

                                    <a href="edit_product.php?id=<?= $row['id'] ?>"
                                       class="bg-gray-600 text-white px-3 py-1 rounded text-sm">
                                        Edit
                                    </a>

                                    <a href="products.php?delete=<?= $row['id'] ?>"
                                       class="bg-primary text-white px-3 py-1 rounded text-sm"
                                       onclick="return confirm('Delete this product?')">
                                        Delete
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7"
                                class="text-center py-6 text-gray-500">
                                No products found.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </section>

</main>

<?php include '_footer.php'; ?>