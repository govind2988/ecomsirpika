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

<!-- Banner Section -->


<main>

<section class="innerProductBanner mb-0">   
    <div class="innerBanner">
    <img src="assets/images/InnerBanner.jpg" alt="Sirpika Millets">
        <h2 class="title">Our Products</h2>
  </div>
</section>

<!-- Product Section -->
<section class="productsList mt-0" id="onlineOrder">
    <div class="container mx-auto"> 
        <!-- Search and Category Filter -->
        <div class="productFilter w-full mb-8 p-6 bg-white">
            <div class="w-full flex flex-col sm:flex-row items-center justify-center gap-4 search-filters">
                <!-- Search Input -->
                <div class="w-full sm:w-3/5 relative">
                    <input 
                        id="searchInput" 
                        class="search w-full py-3 pl-10 border-1 border-gray-100 rounded-lg focus:outline-none focus:border-gray-300 focus:ring-1 focus:ring-gray-200 transition-all duration-200 placeholder-gray-500 text-gray-700" 
                        placeholder="Search products..."  
                        onkeyup="filterProducts()">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>

                <!-- Category Filter -->
                <div class="w-full sm:w-2/5 relative">
                    <select 
                        id="categoryFilter" 
                        class="w-full px-4 py-3 pl-4 pr-10 border-1 border-gray-100 rounded-lg bg-white text-gray-700 focus:outline-none focus:border-gray-600 focus:ring-1 focus:ring-gray-200 transition-all duration-200 appearance-none cursor-pointer font-medium" 
                        onchange="filterProducts()">
                        <option value="">All Categories</option>
                        <?php while($cat = $catRes->fetch_assoc()): ?>
                            <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div id="productGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <?php
            $catQuery = $conn->query("SELECT DISTINCT category_id FROM products ORDER BY category_id ASC");
            $isCategoryFirst = true;
            while ($cat = $catQuery->fetch_assoc()):
                $categoryID = $cat['category_id'];
                $productQuery = $conn->query("SELECT * FROM products WHERE category_id = '{$categoryID}' ORDER BY id DESC");
                if ($productQuery->num_rows > 0):
                    $categoryName = $categoryMap[$categoryID] ?? 'Unknown Category';
                    
                    if (!$isCategoryFirst):
            ?>
        </div>
        <div id="productGrid-<?= $categoryID ?>" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
            <div class="col-span-full mt-4">
                <h2 class="Cathead"><?= htmlspecialchars($categoryName) ?></h2>
            </div>
            <?php else: ?>
            <div class="col-span-full mt-4">
                <h2 class="Cathead"><?= htmlspecialchars($categoryName) ?></h2>
            </div>
            <?php 
                    $isCategoryFirst = false;
                    endif;
                    
                    while ($row = $productQuery->fetch_assoc()):
                        $rrp = (float)$row['rrp_price'];
                        $sale = (float)$row['sale_price'];
                        $basePrice = $sale > 0 ? $sale : $rrp;
                        $productId = $row['id'];
                        $productImage = !empty($row['image']) ? 'uploads/' . $row['image'] : 'assets/images/placeholder.png';
                        $cartQty = $cartQuantities[$productId] ?? 0;
                        $orderedValue = $basePrice * $cartQty;
            ?>
            <!-- Product Card -->
            <div class="col-span-1 element-item product-card" data-category-id="<?= $row['category_id'] ?>">
                <div class="item bg-white shadow-md hover:shadow-xl transition-shadow overflow-hidden h-full flex flex-col">
                    <!-- Image Section -->
                    <div class="zoomOut shineEffect overflow-hidden bg-gray-100 h-48 flex items-center justify-center">
                        <figure class="w-full h-full">
                            <a class="popup block w-full h-full" href="<?= htmlspecialchars($productImage) ?>" title="<?= htmlspecialchars($row['name']) ?> - ₹<?= number_format($basePrice, 2) ?>">
                                <img src="<?= htmlspecialchars($productImage) ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="w-full h-full object-cover hover:scale-110 transition-transform cursor-pointer">
                            </a>
                             <?php if (!empty($row['youtube_url'])): ?>
                        <a href="<?= htmlspecialchars($row['youtube_url']) ?>" target="_blank" class="youtubeBtn inline-flex items-center gap-2 px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm w-fit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M23.5 6.4c-.3-1.2-1.2-2.1-2.4-2.4C18.8 3.5 12 3.5 12 3.5s-6.8 0-9.1.5c-1.2.3-2.1 1.2-2.4 2.4C0 8.7 0 12 0 12s0 3.3.5 5.6c.3 1.2 1.2 2.1 2.4 2.4 2.3.5 9.1.5 9.1.5s6.8 0 9.1-.5c1.2-.3 2.1-1.2 2.4-2.4.5-2.3.5-5.6.5-5.6s0-3.3-.5-5.6zM9.8 15.6V8.4l6.2 3.6-6.2 3.6z"/></svg>
                            
                        </a>
                        <?php endif; ?>
                        </figure>
                         <!-- YouTube Link -->
                       
                    </div>

                    <!-- Content Section -->
                    <div class="content p-4 flex flex-col flex-grow">
                       

                        <!-- Product Name -->
                        <h2 class="text-lg font-semibold text-gray-800 mb-2"><?= htmlspecialchars($row['name']) ?></h2>

                        <!-- Description -->
                        <?php if (!empty($row['description'])): ?>
                        <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($row['description']) ?></p>
                        <?php endif; ?>

                        <!-- Price Section -->
                        <div class="mb-3 flex items-start gap-2">

                        <div class="pricing">
                            <?php if ($rrp > $basePrice): ?>
                            <span class="text-sm text-gray-500 line-through mr-2">₹<?= number_format($rrp, 2) ?></span>
                            <?php endif; ?>
                            
                            <span class="text-red-600 font-bold text-xl">₹<span id="price_<?= $row['id'] ?>"><?= number_format($basePrice, 2) ?></span></span>

                             <span class="text-lg text-green-700 font-medium <?= $cartQty > 0 ? '' : 'hidden' ?>" id="ordered_value_<?= $productId ?>">
                                Total: <?= $cartQty > 0 ? "₹" . number_format($orderedValue, 2) : '' ?>
                            </span>                            
                           
                        </div>

                        <!-- Ordered Value -->
                        
                           

                        <!-- Quantity Controls -->
                        <div class="mt-auto">
                            <div class="flex items-center justify-center gap-0">
                                <button type="button" onclick="adjustQty(<?= $productId ?>, -1)" class="bg-yellow-400 text-red-700 hover:bg-yellow-500 w-10 h-10 rounded-l-lg font-bold text-lg transition">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <input type="number" id="qty_<?= $productId ?>" value="<?= $cartQty ?>" min="0" class="bg-yellow-100 text-red-700 font-semibold text-center border-0 w-16 h-10 text-lg" onchange="updateCart(<?= $productId ?>)">
                                <button type="button" onclick="adjustQty(<?= $productId ?>, 1)" class="bg-yellow-400 text-red-700 hover:bg-yellow-500 h-10 w-10 rounded-r-lg font-bold text-lg transition">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                         </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php endif; endwhile; ?>
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
      orderedEl.textContent = "Total: ₹" + orderedValue.toFixed(2);
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
  const cartLink = document.querySelector(".relative a"); // target <a> inside .relative
  let badge = cartLink.querySelector(".cart-badge"); // use specific class

  if (count > 0) {
    if (!badge) {
      badge = document.createElement("span");
      badge.className = "cart-badge absolute -top-2 -right-2 bg-yellow-400 text-red-700 text-xs font-bold rounded-full px-1.5";
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
  const categoryVisibility = {}; // track visible products per category

  let hasVisibleProducts = false;

  // Step 1: Filter products
  cards.forEach(card => {
    const productName = card.querySelector("h2")?.textContent.toLowerCase() || "";
    const catId = card.getAttribute("data-category-id");

    const match =
      productName.includes(input) &&
      (!selectedCategory || selectedCategory === catId);

    if (match) {
      card.style.display = "block";
      categoryVisibility[catId] = true; // mark category as visible
      hasVisibleProducts = true;
    } else {
      card.style.display = "none";
    }
  });

  // Step 2: Hide/Show category headers
  const headers = document.querySelectorAll(".Cathead");

  headers.forEach(header => {
    // Find category id by checking next products
    let parent = header.closest("div");
    let nextCards = parent.parentElement.querySelectorAll(".product-card");

    let visible = false;

    nextCards.forEach(card => {
      if (card.style.display !== "none") {
        visible = true;
      }
    });

    header.parentElement.style.display = visible ? "block" : "none";
  });

  // Step 3: No products message
  let noMsg = document.getElementById("noProductsMsg");

  if (!noMsg) {
    noMsg = document.createElement("div");
    noMsg.id = "noProductsMsg";
    noMsg.className = "text-center text-red-600 text-lg font-semibold my-6";
    noMsg.innerText = "No products found matching your search.";
    document.getElementById("productGrid").appendChild(noMsg);
  }

  noMsg.style.display = hasVisibleProducts ? "none" : "block";
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
