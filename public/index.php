<?php
session_start();
include './../includes/db.php';
$conn = getDbConnection();

// ✅ Updated category map safely
$categoryMap = [];
$catRes = $conn->query("SELECT id, name FROM categories");
while ($catRow = $catRes->fetch_assoc()) {
    if (isset($catRow['id']) && isset($catRow['name'])) {
        $categoryMap[$catRow['id']] = $catRow['name'];
    }
}
$catRes->data_seek(0);

$cartQuantities = $_SESSION['cart'] ?? [];

include '_header.php';
?>

<main class="mx-auto">

<!-- Banner Section -->
<section class="homeBanner">   
    <div class="container mx-auto">
        <?php
        $res = $conn->query("SELECT banner_images FROM settings");
        $row = $res->fetch_assoc();
        $images = json_decode($row['banner_images'], true);
        if (!empty($images)) {
            echo "<div class='owl-carousel owl-theme overflow-hidden'>";
            foreach ($images as $img) {
                $imgFilePath = 'uploads/' . basename($img);
                echo "<div><img src='$imgFilePath' class='w-full'></div>";
            }
            echo "</div>";
        }
        ?>
    </div>
</section>

<!-- Product Section -->
<section class="productsList" id="onlineOrder">
    <div class="container mx-auto mt-6"> 
        <!-- Search and Category Filter -->
        <div class="mb-6 flex flex-col sm:flex-row items-center justify-center gap-4 search-filters">             
            <input type="text" id="searchInput" class="search" placeholder="Search products..."  onkeyup="filterProducts()">
            <select id="categoryFilter" class="" onchange="filterProducts()">
                <option value="">All Categories</option>
                <?php while($cat = $catRes->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Products Table -->
        <div id="productGrid">
            <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-md">
                <thead class="shadow-md bg-blue-900 text-white">
                    <tr>
                        <th class="px-4 py-3">Image</th>
                        <th class="px-4 py-3">YouTube</th>
                        <th class="px-4 py-3 w-1/3">Product Name</th>
                        <th class="px-4 py-3">Unit</th>
                        <th class="px-4 py-3">MRP</th>
                        <th class="px-4 py-3">Sale Price</th>
                        <th class="px-4 py-3">Sub Total</th>
                        <th class="px-4 py-3">Cart</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                <?php
                $catQuery = $conn->query("SELECT DISTINCT category_id FROM products ORDER BY category_id ASC");
                while ($cat = $catQuery->fetch_assoc()):
                    $categoryID = $cat['category_id'];
                    $productQuery = $conn->query("SELECT * FROM products WHERE category_id = '{$categoryID}' ORDER BY id DESC");
                    if ($productQuery->num_rows > 0):
                        $categoryName = $categoryMap[$categoryID] ?? 'Unknown Category';
                ?>
                    <!-- Category Title -->
                    <tr class="bg-yellow-400">
                        <td colspan="8" class="text-2xl font-normal text-center uppercase text-red-700 py-2">
                            <?= htmlspecialchars($categoryName) ?>
                        </td>
                    </tr>
                    <?php while ($row = $productQuery->fetch_assoc()):
                        $rrp = (float)$row['rrp_price'];
                        $sale = (float)$row['sale_price'];
                        $basePrice = $sale > 0 ? $sale : $rrp;
                        $productId = $row['id'];
                        $productImage = !empty($row['image']) ? 'uploads/' . $row['image'] : 'assets/images/place_holder_img.jpg';
                        $cartQty = $cartQuantities[$productId] ?? 0;
                        $orderedValue = $basePrice * $cartQty;
                    ?>
                    <tr class="product-card rounded-md mb-4 shadow" data-category-id="<?= $row['category_id'] ?>">
                        <td class="px-4 py-2">
                            <img src="<?= $productImage ?>" alt="<?= htmlspecialchars($row['name']) ?>"
                                class="w-16 h-16 object-cover mx-auto rounded cursor-pointer"
                                onclick="openImageModal('<?= $productImage ?>')">
                        </td>
                        <td class="px-4 py-2">
                            <?php if (!empty($row['youtube_url'])): ?>
                            <a href="<?= htmlspecialchars($row['youtube_url']) ?>" target="_blank"
                                class="w-16 h-10 inline-flex items-center gap-2 px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm w-fit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M23.5 6.4c-.3-1.2-1.2-2.1-2.4-2.4C18.8 3.5 12 3.5 12 3.5s-6.8 0-9.1.5c-1.2.3-2.1 1.2-2.4 2.4C0 8.7 0 12 0 12s0 3.3.5 5.6c.3 1.2 1.2 2.1 2.4 2.4 2.3.5 9.1.5 9.1.5s6.8 0 9.1-.5c1.2-.3 2.1-1.2 2.4-2.4.5-2.3.5-5.6.5-5.6s0-3.3-.5-5.6zM9.8 15.6V8.4l6.2 3.6-6.2 3.6z"/></svg>
                            </a>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2 text-md font-medium"><?= htmlspecialchars($row['name']) ?></td>
                        <td class="px-4 py-2 text-gray-600 text-md"><?= htmlspecialchars($row['description']) ?></td>
                        <td class="px-4 py-2">
                            <?php if ($rrp > $basePrice): ?>
                            <span class="text-md text-gray-500 line-through">₹<?= number_format($rrp, 2) ?></span>
                            <?php endif; ?> 
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-red-600 font-bold text-lg">₹<span id="price_<?= $row['id'] ?>"><?= number_format($basePrice, 2) ?></span></span> 
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-lg text-green-700 font-medium <?= $cartQty > 0 ? '' : 'hidden' ?>" id="ordered_value_<?= $productId ?>">
                                <?= $cartQty > 0 ? "₹" . number_format($orderedValue, 2) : '' ?>
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex items-center justify-center">
                                <button type="button" onclick="adjustQty(<?= $productId ?>, -1)" class="bg-yellow-400 text-red-700 px-2 py-1 rounded-l hover:bg-yellow-500 w-10 h-10">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" id="qty_<?= $productId ?>" value="<?= $cartQty ?>" min="0" class="px-4 bg-yellow-100 text-red-700 font-semibold text-center border-0 w-16 h-10" onchange="updateCart(<?= $productId ?>)">
                                <button type="button" onclick="adjustQty(<?= $productId ?>, 1)" class="h-10 w-10 bg-yellow-400 text-red-700 px-2 py-1 rounded-r hover:bg-yellow-500">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</section>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center hidden z-50">
  <div class="relative">
    <img id="modalImage" class="max-h-[80vh] max-w-full rounded shadow-lg" src="" alt="Expanded Image">
    <button onclick="closeImageModal()" class="absolute top-0 right-0 text-white text-xl bg-black bg-opacity-50 px-3 py-1 rounded">×</button>
  </div>
</div>

<script>
$(document).ready(function () {
  $('.owl-carousel').owlCarousel({
    items: 1,
    loop: true,
    autoplay: true,
    autoplayTimeout: 4000,
    dots: true
  });
});

// Quantity +/- button
function adjustQty(id, delta) {
  const input = document.getElementById('qty_' + id);
  let value = parseInt(input.value) || 0;
  value += delta;
  if (value < 0) value = 0;
  input.value = value;
  updateCart(id);
}

// Update cart and UI
function updateCart(productId) {
  const qty = parseInt(document.getElementById('qty_' + productId).value) || 0;
  const price = parseFloat(document.getElementById('price_' + productId).textContent) || 0;

  const orderedValue = qty * price;
  const orderedEl = document.getElementById('ordered_value_' + productId);

  if (orderedEl) {
    if (qty > 0) {
      orderedEl.textContent = "₹" + orderedValue.toFixed(2);
      orderedEl.classList.remove("hidden");
    } else {
      orderedEl.classList.add("hidden");
    }
  }

  $.post("index.php", {
    ajax: 'update_cart',
    product_id: productId,
    quantity: qty
  }, function (response) {
    try {
      const res = JSON.parse(response);
      if (res.status === 'success') {
        updateCartCount(res.cartCount);
      }
    } catch (e) {
      console.error('Cart update failed', e);
    }
  });
}

// Update the cart count badge in header
function updateCartCount(count) {
  const cartLink = document.querySelector(".relative");
  let badge = cartLink.querySelector("span");

  if (count > 0) {
    if (!badge) {
      badge = document.createElement("span");
      badge.className = "absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full px-2";
      cartLink.appendChild(badge);
    }
    badge.textContent = count;
  } else if (badge) {
    badge.remove();
  }
}

// Search + Category Filter
function filterProducts() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  const selectedCategory = document.getElementById("categoryFilter").value;
  const cards = document.querySelectorAll(".product-card");

  let currentCategoryRow = null;
  let categoryHasVisibleProducts = false;

  const allRows = document.querySelectorAll("#productGrid table tr");

  allRows.forEach(row => {
    if (row.classList.contains("bg-yellow-400")) {
	  if (currentCategoryRow) {
		if(!categoryHasVisibleProducts) {
			currentCategoryRow.classList.add("hidden");
			}
		else {
			currentCategoryRow.classList.remove("hidden");
			}
		}
      currentCategoryRow = row;
      categoryHasVisibleProducts = false;	  
    } else if (row.classList.contains("product-card")) {
      const productName = row.querySelector("td:nth-child(3)").textContent.toLowerCase();
      const catId = row.getAttribute("data-category-id");
      const match = productName.includes(input) && (!selectedCategory || selectedCategory === catId);
      row.style.display = match ? "table-row" : "none";
      if (match) categoryHasVisibleProducts = true;
    }
  });

  // Hide last category if no products matched
  if (currentCategoryRow) {
	if(!categoryHasVisibleProducts) {
		currentCategoryRow.classList.add("hidden");
		}
	else {
		currentCategoryRow.classList.remove("hidden");
		}
	}
}
// Image modal logic
function openImageModal(src) {
  document.getElementById("modalImage").src = src;
  document.getElementById("imageModal").classList.remove("hidden");
}
function closeImageModal() {
  document.getElementById("imageModal").classList.add("hidden");
}
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") closeImageModal();
});
</script>

</main>

<?php include '_footer.php'; ?>
