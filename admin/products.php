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
         <!-- SEARCH FORM -->
    <div class="mb-4 flex gap-3 items-center">
        <div class="relative flex-1 md:w-80">
            <input
                type="text"
                id="productSearch"
                placeholder="Search product name or ID..."
                class="border border-gray-300 rounded px-4 py-2 w-full pr-10"
                onkeyup="filterProducts()"
            >
            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
            <button 
                type="button"
                id="clearSearchBtn"
                onclick="clearSearch()"
                class="absolute right-10 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 cursor-pointer hidden"
                title="Clear search"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="searchStats" class="text-sm text-gray-600 whitespace-nowrap">
            Showing all products
        </div>
    </div>

    

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

   

    <section class="grid grid-cols-1 gap-6">

        <div class="flex flex-col md:col-span-2 md:row-span-2 bg-white shadow rounded-lg">

            <div class="flex-grow overflow-x-auto scrollTable">

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

                            <tr class="hover:bg-gray-50 product-row" data-product-id="<?= (int)$row['id'] ?>" data-product-name="<?= strtolower(htmlspecialchars($row['name'])) ?>">

                                <td class="py-2 px-4 border-b">
                                    <?= $row['id'] ?>
                                </td>

                                <td class="py-2 px-4 border-b product-name">
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

<script>
// ✅ Real-time Product Search Functionality
let searchTimeout;

function filterProducts() {
    clearTimeout(searchTimeout);
    
    // Debounce search to avoid excessive DOM updates
    searchTimeout = setTimeout(() => {
        const searchInput = document.getElementById('productSearch').value.toLowerCase().trim();
        const clearBtn = document.getElementById('clearSearchBtn');
        const searchStats = document.getElementById('searchStats');
        const rows = document.querySelectorAll('.product-row');
        
        // Show/hide clear button
        if (searchInput.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        let visibleCount = 0;

        rows.forEach(row => {
            const productId = row.getAttribute('data-product-id');
            const productName = row.getAttribute('data-product-name');

            // Search by product name or ID
            const matchesSearch = 
                productName.includes(searchInput) || 
                productId.includes(searchInput);

            if (matchesSearch) {
                row.style.display = 'table-row';
                visibleCount++;
                
                // Highlight matching text in product name
                if (searchInput.length > 0) {
                    const nameCell = row.querySelector('.product-name');
                    const originalName = nameCell.textContent;
                    const regex = new RegExp(`(${searchInput})`, 'gi');
                    nameCell.innerHTML = originalName.replace(regex, '<mark class="bg-yellow-200 font-semibold">$1</mark>');
                }
            } else {
                row.style.display = 'none';
            }
        });

        // Update search stats
        if (searchInput.length === 0) {
            searchStats.textContent = 'Showing all products';
            // Remove highlights when search is cleared
            document.querySelectorAll('mark').forEach(mark => {
                mark.replaceWith(mark.textContent);
            });
        } else {
            const totalProducts = rows.length;
            searchStats.innerHTML = `<span class="font-semibold text-gray-800">${visibleCount}</span> of <span class="font-semibold text-gray-800">${totalProducts}</span> products`;
            
            if (visibleCount === 0) {
                searchStats.innerHTML += ' <span class="text-red-600 ml-2">No products found</span>';
            }
        }

        // Show/hide no products message
        const noProductsRow = document.querySelector('tr[colspan="7"]');
        if (noProductsRow) {
            noProductsRow.parentElement.parentElement.style.display = visibleCount === 0 && searchInput.length > 0 ? 'table-row' : 'none';
        }
    }, 300); // 300ms debounce
}

function clearSearch() {
    document.getElementById('productSearch').value = '';
    document.getElementById('clearSearchBtn').classList.add('hidden');
    document.getElementById('productSearch').focus();
    
    // Remove highlights
    document.querySelectorAll('mark').forEach(mark => {
        mark.replaceWith(mark.textContent);
    });
    
    // Show all rows
    document.querySelectorAll('.product-row').forEach(row => {
        row.style.display = 'table-row';
    });
    
    // Reset stats
    document.getElementById('searchStats').textContent = 'Showing all products';
}
</script>

<?php include '_footer.php'; ?>