
<?php
include '../includes/db.php';

require '../vendor/autoload.php'; // PHPSpreadsheet path

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel'])) {
    $file = $_FILES['excel']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

 	$conn = getDbConnection(); // assuming includes/db.php defines this

	$header = array_map('strtolower', $rows[0]); // e.g., "id", "name", "description", etc.

	for ($i = 1; $i < count($rows); $i++) {
	$data = array_combine($header, $rows[$i]);

	// Get category ID
	$categoryID = getOrCreateCategoryId($data['category']);

	// Check if product ID is provided and exists
	$productId = isset($data['id']) ? intval($data['id']) : 0;
	$exists = false;

	if ($productId > 0) {
		$checkStmt = $conn->prepare("SELECT 1 FROM products WHERE id = ? LIMIT 1");
		$checkStmt->bind_param("i", $productId);
		$checkStmt->execute();
		$checkStmt->store_result();
		$exists = $checkStmt->num_rows > 0;
		$checkStmt->close();
	}

	if ($exists) {
		// Update existing product
		$stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, category_id = ?, rrp_price = ?, sale_price = ?, stock = ? WHERE id = ?");
		$stmt->bind_param("ssiddii",
			$data['name'],
			$data['description'],
			$categoryID,
			$data['mrp'],
			$data['offer_price'],
			$data['stock'],
			$productId
		);
	} else {
		// Insert new product
		$stmt = $conn->prepare("INSERT INTO products (name, description, category_id, rrp_price, sale_price, stock) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("ssiddi",
			$data['name'],
			$data['description'],
			$categoryID,
			$data['mrp'],
			$data['offer_price'],
			$data['stock']
		);
	}

	$stmt->execute();
	$stmt->close();
	}
	header("Location: upload_stock.php?productsuploaded=1");
	exit;
    
}

function getOrCreateCategoryId(string $categoryName): int {
	$conn = getDbConnection(); // assuming includes/db.php defines this

    // Sanitize input
    $categoryName = trim($conn->real_escape_string($categoryName));

    // 1. Check if category exists
    $query = "SELECT id FROM categories WHERE name = '$categoryName' LIMIT 1";
    $result = $conn->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        return (int)$row['id']; // Found existing category
		}

    // 2. If not found, insert new category
    $insert = "INSERT INTO categories (name) VALUES ('$categoryName')";
    if ($conn->query($insert)) {
        return (int)$conn->insert_id; // Return the new category ID
		} else {
        throw new Exception("Failed to insert new category: " . $conn->error);
		}
	}

include '_header.php';
?>




<main class="p-6 mt-16 space-y-4">

     <section class="grid grid-cols-1 gap-6">

    <div class="w-1/2 mx-auto px-6 py-8">

      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Bulk Upload</h2>
        <div class="space-x-3">          
            <a href="products.php" class="bg-gray-600 view-btn text-white font-medium px-3 py-1 rounded">
            Back
            </a>
        </div>
    </div>

     <form class="bg-white shadow rounded-lg px-8 pt-6 pb-8 mb-4 text-center" method="post" enctype="multipart/form-data">
      <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-64 rounded-lg  bg-gray-50">
        <div class="flex flex-col items-center justify-center pt-5 pb-6">
            <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
            </svg>
            <p class="mb-2 text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold">Click to upload</span></p>

            <p class="text-xs text-gray-500 dark:text-gray-400">File format should be XLSX only</p>
        </div>


        <input class="fileUpload hover:bg-gray-200 cursor-pointer border-2 border-gray-300 border-dashed focus:border-dashed focus:outline-none" type="file" name="excel" accept=".xlsx" required>
        </label>


        <button class="bg-primary text-white font-medium px-4 py-2 rounded mt-4 mx-auto" type="submit">Upload</button>

        <p> <a href="download_products.php"> Download Template </a></p>


    </form>
    </div>           
    </section>
	<!-- Products Upload Status Modal -->
			<div id="statusModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center">
			  <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
				<h2 class="text-xl font-semibold mb-4 text-green-700">✅ Products uploaded successfully</h2>
				<button onclick="closeUploadStatusModal()" class="bg-primary text-white px-4 py-2 rounded">OK</button>
			  </div>
			</div>
</main>
<script>
	//
	// order status modal handlers
	//
	function closeUploadStatusModal() {
	  document.getElementById('statusModal').classList.add('hidden');
	  // Remove query string from URL without reloading
	  const url = new URL(window.location);
	  url.searchParams.delete('productsuploaded');
	  window.history.replaceState({}, document.title, url.pathname);
	}

	window.addEventListener('DOMContentLoaded', () => {
	  const urlParams = new URLSearchParams(window.location.search);
	  if (urlParams.get('productsuploaded') === '1') {
		document.getElementById('statusModal').classList.remove('hidden');
	  }
	});
</script>

<?php include '_footer.php'; ?>
