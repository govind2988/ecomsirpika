<?php
include './../includes/auth.php';
include './../includes/db.php';
include './../includes/helpers.php';
$conn = getDbConnection();

$success = "";
$error = "";
$selectedCategoryId = null;

// Handle new category addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category'])) {
    $newCat = trim($conn->real_escape_string($_POST['new_category']));
    if (!empty($newCat)) {
        $exists = $conn->query("SELECT id FROM categories WHERE name = '$newCat'");
        if ($exists->num_rows === 0) {
            $conn->query("INSERT INTO categories (name) VALUES ('$newCat')");
            $selectedCategoryId = $conn->insert_id;
        } else {
            $cat = $exists->fetch_assoc();
            $selectedCategoryId = $cat['id'];
        }
    }
}

// Handle product creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $rrp_price = floatval($_POST['rrp_price']);
    $sale_price = floatval($_POST['sale_price']);
 //   $price = floatval($_POST['price']); // Final selling price
    $category_id = intval($_POST['category_id']);
    $description = $conn->real_escape_string($_POST['description'] ?? '');
	$stock =  intval($_POST['stock']);
    $selectedCategoryId = $category_id;

    $imagePath = '';
   if (!empty($_FILES['image']['name'])) {
		$targetDir = __DIR__ . '/../uploads/';
		$filename = uniqid("img_") . ".jpg";  // Always save as .jpg
		$targetFile = $targetDir . $filename;

		$tmpFile = $_FILES["image"]["tmp_name"];

		if (resizeAndCompressImage($tmpFile, $targetFile)) {
			$imagePath = '../uploads/'.$filename;
		} else {
			$error = "Image resize/compression failed or unsupported format.";
		}
	}
	 $youtubelink = $conn->real_escape_string($_POST['youtubelink']);
	


    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO products (name, rrp_price, sale_price, category_id, stock, description, image, youtube_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sddiisss", $name, $rrp_price, $sale_price, $category_id, $stock, $description, $imagePath, $youtubelink);
        if ($stmt->execute()) {
            $success = "✅ Product created successfully!";
            $selectedCategoryId = null; // reset after success
        } else {
            $error = "❌ Failed to insert product.";
        }
    }
}

include '_header.php';

// Fetch categories
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>




<main class="p-6 mt-16 space-y-4">
   

    


    <section class="grid grid-cols-1 gap-6">
       

                <div class="w-1/2 mx-auto px-6 py-8">
   
                <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Create New Product</h2>
        <div class="space-x-3">
          
            <a href="products.php" class="bg-gray-600 text-white px-3 py-1 rounded view-btn">
                Back
            </a>
        </div>
    </div>



    <?php if ($success): ?>
      <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <!-- Add New Category Form -->
    <form action="product_create.php" method="POST" class="mb-6 flex items-center gap-3">
      <input type="text" name="new_category" placeholder="Enter category name" required
             class="flex-1 border px-3 py-2 rounded" />
      <button type="submit"
              class="bg-primary text-white px-4 py-2 rounded">
      Add Category
      </button>
    </form>

    <!-- Product Creation Form -->
    <form action="product_create.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow grid grid-cols-2 gap-4">

      <div>
        <label class="block font-semibold mb-1">Product Name</label>
        <input type="text" name="name" required class="w-full border px-3 py-2 rounded" />
      </div>

      <div>
        <label class="block font-semibold mb-1">MRP Price (₹)</label>
        <input type="number" name="rrp_price" step="0.01" required class="w-full border px-3 py-2 rounded" />
      </div>

      <div>
        <label class="block font-semibold mb-1">Offer Price (₹)</label>
        <input type="number" name="sale_price" step="0.01" required class="w-full border px-3 py-2 rounded" />
      </div>

      <div>
        <label class="block font-semibold mb-1">Category</label>
        <select name="category_id" class="w-full border px-3 py-2 rounded" required>
          <option value="">-- Select Category --</option>
          <?php while ($cat = $categories->fetch_assoc()): ?>
            <option value="<?= $cat['id'] ?>" <?= ($selectedCategoryId == $cat['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div>
        <label class="block font-semibold mb-1">Description</label>
        <textarea name="description" class="w-full border px-3 py-2 rounded"></textarea>
      </div>

      <div>
        <label class="block font-semibold mb-1">Image</label>
        <input type="file" name="image" class="w-full" />
      </div>
	  
	    <div>
        <label class="block font-semibold mb-1">You Tube Link</label>
        <input type="text" name="youtubelink" class="w-full" />
      </div>
	  
	  <div>
        <label class="block font-semibold mb-1">Stock:</label>
        <input type="number" name="stock" class="w-full border px-3 py-2 rounded" required />
      </div>

      <button type="submit" class="bg-primary text-white font-semibold px-6 py-2 rounded">
         Submit Product
      </button>
    </form>

    
  </div>
           
    </section>

</main>


<?php include '_footer.php'; ?>