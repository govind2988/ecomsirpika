<?php

include '../includes/db.php';
include '../includes/helpers.php';

$conn = getDbConnection();

$productId = intval($_GET['id']);
$result = $conn->query("SELECT * FROM products WHERE id = $productId");
$product = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmpPath = $_FILES['image']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        $error = "❌ Only JPG, PNG, and WEBP images allowed.";
    } else {
        $filename = uniqid('product_') . '.jpg';
        $destination = __DIR__ . '/../uploads/' . $filename;

        if (!resizeAndCompressImage($tmpPath, $destination)) {
            $error = "❌ Failed to resize/compress image.";
        } else {
            $conn->query("UPDATE products SET image = '$filename' WHERE id = $productId");
        }
    }
}

    $name = $conn->real_escape_string($_POST['name']);
 //   $price = floatval($_POST['price']);
    $rrp_price = floatval($_POST['rrp_price']);
    $sale_price = floatval($_POST['sale_price']);
    $stock = intval($_POST['stock']);
	$youtubelink = $conn->real_escape_string($_POST['youtubelink']);
    if (!empty($_FILES['image']['name'])) {
        $image = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $image);
        $conn->query("UPDATE products SET name='$name', rrp_price=$rrp_price, sale_price=$sale_price, stock=$stock, image='$image', youtube_url='$youtubelink' WHERE id = $productId");
    } else {
        $conn->query("UPDATE products SET name='$name', rrp_price=$rrp_price, sale_price=$sale_price, stock=$stock WHERE id = $productId");
    }

    header("Location: products.php");
    exit;
}
include '_header.php';
?>




<main class="p-6 mt-16 space-y-4">
   

    <section class="grid grid-cols-1 gap-6">
        <div class="w-1/2 mx-auto px-6 py-8">
            <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Edit Product</h2>
            <div class="space-x-3">

            <a href="products.php" class="bg-gray-600 text-white px-3 py-1 rounded view-btn">
            Back
            </a>
            </div>
        </div>

     <form method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow grid grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold mb-1">Product Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required class="w-full border rounded px-3 py-2" />
            </div>

            <div>
                <label class="block font-semibold mb-1">MRP Price:</label>
                <input type="number" step="0.01" name="rrp_price" value="<?= $product['rrp_price'] ?>" class="w-full border rounded px-3 py-2" />
            </div>

            <div>
                <label class="block font-semibold mb-1">Offer Price:</label>
                <input type="number" step="0.01" name="sale_price" value="<?= $product['sale_price'] ?>" class="w-full border rounded px-3 py-2" />
            </div>

            <div>
                <label class="block font-semibold mb-1">Stock:</label>
                <input type="number" name="stock" value="<?= $product['stock'] ?>" required class="w-full border rounded px-3 py-2" />
            </div>

            <div>
                <label class="block font-semibold mb-1">Image:</label>
                <input type="file" name="image" class="w-full border rounded px-3 py-2" />
                <div class="mt-2">
                    <img src="../uploads/<?= $product['image'] ?>" width="120" class="rounded shadow" />
                </div>
            </div>
			
			<div>
                <label class="block font-semibold mb-1">You Tube Link:</label>
                <input type="text" name="youtubelink" class="w-full border rounded px-3 py-2" value=<?=$product['youtube_url'] ?>/>
            </div>

            <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Update</button>
        </form>

    
  </div>
           
    </section>

</main>


<?php include '_footer.php'; ?>