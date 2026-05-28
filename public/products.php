<?php
session_start();
include './../includes/db.php';
$conn = getDbConnection();

// ✅ Updated category map safely
$categoryMap = [];
$catRes = $conn->query("SELECT id, name 
    FROM categories
    ORDER BY sort_order ASC, id DESC");
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

    <div class="categoryMenu owl-carousel filter-button-group button-group mt-4 js-radio-button-group">
        <div class="item"><button class="button is-checked category-btn" data-filter="*" data-category-id="all">All</button></div>
        <?php 
        $categoryQuery = $conn->query("SELECT id, name FROM categories ORDER BY sort_order ASC, id DESC");
        while ($catButton = $categoryQuery->fetch_assoc()): 
            $catId = $catButton['id'];
            $catName = htmlspecialchars($catButton['name']);
        ?>
        <div class="item"><button class="button category-btn" data-filter="[data-category-id='<?= $catId ?>']" data-category-id="<?= $catId ?>"><?= $catName ?></button></div>
        <?php endwhile; ?>
    </div>

    <div class="container mx-auto"> 
        <!-- Search and Category Filter -->
        <div class="productFilter mb-8 p-0 bg-white shadow-md sm:rounded-full">
            <div class="w-full sm:ps-2 flex flex-col sm:flex-row items-center justify-center sm:gap-4 search-filters">
                <div class="w-full sm:w-3/5 relative">
                    <input 
                        id="searchInput" 
                        class="search w-full py-3 pl-10 border-1 sm:border-none sm:rounded-full outline-none transition-all duration-200 placeholder-gray-500 text-gray-700" 
                        placeholder="Search products..."  
                        onkeyup="filterProducts()">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>

                <div class="w-full sm:w-2/5 relative">
                    <select 
                        id="categoryFilter" 
                        class="w-full px-4 py-3 pl-4 pr-10 border-1 border-gray-100 sm:rounded-full bg-white text-gray-700 outline-none transition-all duration-200 appearance-none cursor-pointer font-medium" 
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

        <?php
        $catQuery = $conn->query("SELECT DISTINCT c.id AS category_id
                                  FROM categories c
                                  INNER JOIN products p ON p.category_id = c.id
                                  ORDER BY c.sort_order ASC, c.id DESC");

        while ($cat = $catQuery->fetch_assoc()):
            $categoryID = $cat['category_id'];
            $productQuery = $conn->query("SELECT * FROM products WHERE category_id = '{$categoryID}' ORDER BY id DESC");
            if ($productQuery->num_rows > 0):
                $categoryName = $categoryMap[$categoryID] ?? 'Unknown Category';
        ?>
            <div class="category-section mt-8" id="productGrid-<?= $categoryID ?>" data-category-section="<?= $categoryID ?>">
                <div class="col-span-full mt-4 category-header-wrap mb-2">
                    <h2 class="cathead"><?= htmlspecialchars($categoryName) ?></h2>
                </div>

                <div class="productGrid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-5 lg:gap-6">
                    <?php while ($row = $productQuery->fetch_assoc()):
                        $rrp = (float)$row['rrp_price'];
                        $sale = (float)$row['sale_price'];
                        $basePrice = $sale > 0 ? $sale : $rrp;
                        $productId = $row['id'];
                        $productImage = !empty($row['image']) ? 'uploads/' . $row['image'] : 'assets/images/placeholder.png';
                        $cartQty = $cartQuantities[$productId] ?? 0;
                        $orderedValue = $basePrice * $cartQty;
                        
                        // ✅ NEW: Check stock quantity (adjust column name if different)
                        $stockQuantity = (int)($row['stock_quantity'] ?? $row['qty'] ?? $row['stock'] ?? 0);
                        $hasStock = $stockQuantity > 0;
                    ?>
                    <div class="col-span-1 element-item product-card" data-category-id="<?= $row['category_id'] ?>">
                        <div class="item bg-white shadow-md hover:shadow-xl transition-shadow overflow-hidden h-full flex flex-col">
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
                            </div>

                            <div class="content p-4 flex flex-col flex-grow">
                                <h2 class="text-lg font-semibold text-gray-800 mb-2"><?= htmlspecialchars($row['name']) ?></h2>

                                <?php if (!empty($row['description'])): ?>
                                <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($row['description']) ?></p>
                                <?php endif; ?>

                                <div class="pricing">                         
                                    <span class="text-red-600 font-bold text-xl">₹<span id="price_<?= $row['id'] ?>"><?= number_format($basePrice, 2) ?></span></span>
                                    <?php if ($rrp > $basePrice): ?>
                                    <span class="text-sm text-gray-400 line-through mr-2">₹<?= number_format($rrp, 2) ?></span>
                                    <?php endif; ?>

                                    <br>

                                    <?php if ($hasStock): ?>                                       
                                        <div class="text-sm text-green-500 font-medium <?= $cartQty > 0 ? '' : 'invisible' ?>" id="ordered_value_<?= $productId ?>">
                                            Total Price: <?= $cartQty > 0 ? "₹" . number_format($orderedValue, 2) : '' ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-3 flex flex-col gap-2 quantityControls">
                                    <?php if (!$hasStock): ?>
                                        <!-- ✅ NEW: Show Out of Stock instead of controls -->
                                        <div class="out-of-stock-btn bg-gray-200 px-4 h-12 rounded-lg text-gray-600 font-semibold flex items-center justify-center cursor-not-allowed">
                                            <i class="fas fa-ban mr-2"></i>Out of Stock
                                        </div>
                                    <?php else: ?>
                                        <!-- ✅ Original Add to Cart + QTY controls (only when in stock) -->
                                        <button id="addBtn_<?= $productId ?>" class="addToCartBtn bg-yellow-400 px-4 h-12 rounded-lg hover:bg-yellow-500 text-red-700 transition font-semibold <?= $cartQty > 0 ? 'hidden' : '' ?>" onclick="addToCart(<?= $productId ?>)">
                                            <i class="fa-solid fa-shopping-cart mr-2 text-red-700"></i>Add to Cart
                                        </button>

                                        <div id="qtyDiv_<?= $productId ?>" class="<?= $cartQty > 0 ? 'flex' : 'hidden' ?> flex items-center justify-center gap-0 rounded-lg overflow-hidden">
                                            <button type="button" onclick="adjustQty(<?= $productId ?>, -1)" class="bg-yellow-400 text-red-700 hover:bg-yellow-500 px-3 py-3 h-12 font-bold text-lg transition flex items-center justify-center">
                                                <i class="fa-solid fa-minus"></i>
                                            </button>
                                            <input type="number" id="qty_<?= $productId ?>" value="<?= $cartQty ?>" min="0" class="bg-yellow-100 text-red-700 font-bold text-center border-0 w-full h-12 text-lg" onchange="updateCart(<?= $productId ?>)">
                                            <button type="button" onclick="adjustQty(<?= $productId ?>, 1)" class="bg-yellow-400 text-red-700 hover:bg-yellow-500 px-3 h-12 py-3 font-bold text-lg transition flex items-center justify-center">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php
            endif;
        endwhile;
        ?>

        <!-- ✅ No-products message kept outside product grid/cards -->
        <div id="noProductsWrap" class="w-full"></div>
    </div>
</section>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center hidden z-50">
  <div class="relative">
    <img id="modalImage" class="max-h-[80vh] max-w-full rounded shadow-lg" src="" alt="Expanded Image">
    <button onclick="closeImageModal()" class="absolute top-0 right-0 text-white text-xl bg-black bg-opacity-50 px-3 py-1 rounded">×</button>
  </div>
</div>

<style>
  /* Category menu navigation middle alignment */
  .categoryMenu .owl-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: absolute;
    top: 50%;
    width: 100%;
    transform: translateY(-50%);
    padding: 0 10px;
    pointer-events: none;
  }

  .categoryMenu .owl-nav button {
    pointer-events: auto;
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
  }

  .categoryMenu .owl-nav button:hover {
    background-color: rgba(0, 0, 0, 0.8);
  }

  .categoryMenu {
    position: relative;
  }

  /* Go to Top Button Styling */
  .goToTopBtn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background-color: #fbbf24;
    color: #b91c1c;
    border: none;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    z-index: 40;
  }

  .goToTopBtn.show {
    display: flex;
  }

  .goToTopBtn:hover {
    background-color: #f59e0b;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
  }

  .goToTopBtn:active {
    transform: translateY(-1px);
  }
</style>

<script>
$(document).ready(function () {
  // Product carousel (if exists)
  $('.owl-carousel:not(.categoryMenu)').owlCarousel({
    items: 1,
    loop: true,
    autoplay: true,
    autoplayTimeout: 4000,
    dots: true
  });

  // Category menu carousel - enable only on screens below 640px (mobile)
  var $categoryMenu = $('.categoryMenu.owl-carousel');
  var $categoryContainer = $categoryMenu.closest('.productsList');
  
  function initCategoryCarousel() {
    var screenWidth = $(window).width();
    
    // Enable carousel only on screens below 640px
    if (screenWidth < 640) {
      if ($categoryMenu.hasClass('owl-carousel')) {
        if ($categoryMenu.data('owl.carousel')) {
          $categoryMenu.trigger('destroy.owl.carousel');
        }
        $categoryMenu.owlCarousel({
          items: 2,
          responsive: {
            0: { items: 2 },
            480: { items: 3 }
          },
          loop: false,
          margin: 10,
          autoplay: false,
          nav: true,
          navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
          dots: false,
          smartSpeed: 500
        });
      }
    } else {
      // Destroy carousel on larger screens
      if ($categoryMenu.data('owl.carousel')) {
        $categoryMenu.trigger('destroy.owl.carousel');
      }
    }
  }
  
  // Initialize on load and window resize
  setTimeout(initCategoryCarousel, 100);
  $(window).on('resize', function() {
    initCategoryCarousel();
  });
});
</script>



<script>
// ✅ Updated functions skip out-of-stock products
function adjustQty(id, delta) {
  const input = document.getElementById('qty_' + id);
  if (!input) return; // Skip if out of stock
  
  let value = parseInt(input.value) || 0;
  value += delta;
  if (value < 0) value = 0;
  input.value = value;
  updateCart(id);
}

function addToCart(productId) {
  const addBtn = document.getElementById('addBtn_' + productId);
  const qtyDiv = document.getElementById('qtyDiv_' + productId);
  const qtyInput = document.getElementById('qty_' + productId);

  if (!addBtn || !qtyDiv || !qtyInput) return; // Skip out-of-stock

  addBtn.classList.add('hidden');
  qtyDiv.classList.remove('hidden');
  qtyInput.value = 1;
  updateCart(productId);
}

function updateCart(productId) {
  const qtyInput = document.getElementById('qty_' + productId);
  const priceEl = document.getElementById('price_' + productId);
  const addBtn = document.getElementById('addBtn_' + productId);
  const qtyDiv = document.getElementById('qtyDiv_' + productId);

  if (!qtyInput || !priceEl || !addBtn || !qtyDiv) return; // Skip out-of-stock

  const qty = parseInt(qtyInput.value) || 0;
  const price = parseFloat(priceEl.textContent) || 0;

  const orderedValue = qty * price;
  const orderedEl = document.getElementById('ordered_value_' + productId);

  if (orderedEl) {
    if (qty > 0) {
      orderedEl.textContent = "Total Price: ₹" + orderedValue.toFixed(2);
      orderedEl.classList.remove("invisible");
    } else {
      orderedEl.classList.add("invisible");
    }
  }

  if (qty === 0) {
    addBtn.classList.remove('hidden');
    qtyDiv.classList.add('hidden');
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

function updateCartCount(count) {
  const cartLink = document.querySelector(".relative a");
  let badge = cartLink ? cartLink.querySelector(".cart-badge") : null;

  if (count > 0) {
    if (!badge && cartLink) {
      badge = document.createElement("span");
      badge.className = "cart-badge absolute -top-2 -right-2 bg-yellow-400 text-red-700 text-xs font-bold rounded-full px-1.5";
      cartLink.appendChild(badge);
    }
    if (badge) badge.textContent = count;
  } else if (badge) {
    badge.remove();
  }
}

function filterProducts() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  const selectedCategory = document.getElementById("categoryFilter").value;

  const cards = document.querySelectorAll(".product-card");
  const categorySections = document.querySelectorAll(".category-section");

  let hasVisibleProducts = false;

  cards.forEach(card => {
    const productName = card.querySelector("h2")?.textContent.toLowerCase() || "";
    const catId = card.getAttribute("data-category-id");

    const match =
      productName.includes(input) &&
      (!selectedCategory || selectedCategory === catId);

    if (match) {
      card.style.display = "block";
      hasVisibleProducts = true;
    } else {
      card.style.display = "none";
    }
  });

  categorySections.forEach(section => {
    const visibleCards = section.querySelectorAll(".product-card[style*='display: block'], .product-card:not([style*='display: none'])");
    let hasVisibleInSection = false;

    visibleCards.forEach(card => {
      if (card.style.display !== "none") {
        hasVisibleInSection = true;
      }
    });

    section.style.display = hasVisibleInSection ? "block" : "none";
  });

  let noMsg = document.getElementById("noProductsMsg");

  if (!noMsg) {
    noMsg = document.createElement("div");
    noMsg.id = "noProductsMsg";
    noMsg.className = "text-center text-red-600 text-lg font-semibold my-6";
    noMsg.innerText = "No products found matching your search.";
    document.getElementById("noProductsWrap").appendChild(noMsg);
  }

  noMsg.style.display = hasVisibleProducts ? "none" : "block";
}

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

document.querySelectorAll(".category-btn").forEach(button => {
  button.addEventListener("click", function(e) {
    e.preventDefault();
    const categoryId = this.getAttribute("data-category-id");
    let targetElement;

    if (categoryId === "all") {
      targetElement = document.querySelector(".productsList");
    } else {
      targetElement = document.getElementById("productGrid-" + categoryId);
    }

    if (targetElement) {
      targetElement.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  });
});

// ✅ Go to Top functionality
document.addEventListener('DOMContentLoaded', function() {
  const goToTopBtn = document.getElementById('goToTopBtn');
  
  if (goToTopBtn) {
    window.addEventListener('scroll', function() {
      if (window.pageYOffset > 300) {
        goToTopBtn.classList.add('show');
      } else {
        goToTopBtn.classList.remove('show');
      }
    });

    goToTopBtn.addEventListener('click', function() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }
});
</script>

<!-- Go to Top Button -->
<button id="goToTopBtn" class="goToTopBtn" title="Go to top">
  <i class="fas fa-arrow-up"></i>
</button>

</main>

<?php include '_footer.php'; ?>