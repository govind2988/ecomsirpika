<?php
include './../includes/auth.php';
include './../includes/db.php';
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
    $price = floatval($_POST['price']); // Final selling price
    $category_id = intval($_POST['category_id']);
    $description = $conn->real_escape_string($_POST['description'] ?? '');

    $selectedCategoryId = $category_id;

    $imagePath = '';
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../public/images/";
        $filename = basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $filename;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $imagePath = "images/" . $filename;
        } else {
            $error = "Image upload failed.";
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO products (name, rrp_price, sale_price, price, category_id, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdddiss", $name, $rrp_price, $sale_price, $price, $category_id, $description, $imagePath);
        if ($stmt->execute()) {
            $success = "✅ Product created successfully!";
            $selectedCategoryId = null; // reset after success
        } else {
            $error = "❌ Failed to insert product.";
        }
    }
}

// Fetch categories
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Product</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
  <div class="max-w-3xl mx-auto px-6 py-8">
    <h1 class="text-2xl font-bold mb-6">🛠️ Create New Product</h1>

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
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
        ➕ Add Category
      </button>
    </form>

    <!-- Product Creation Form -->
    <form action="product_create.php" method="POST" enctype="multipart/form-data" class="space-y-4 bg-white p-6 rounded shadow">
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
        <label class="block font-semibold mb-1">Selling Price (₹)</label>
        <input type="number" name="price" step="0.01" required class="w-full border px-3 py-2 rounded" />
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

      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded">
        ✅ Submit Product
      </button>
    </form>

    <div class="mt-6">
      <a href="dashboard.php" class="text-blue-600 hover:underline">⬅ Back to Dashboard</a>
    </div>
  </div>
</body>
</html>
