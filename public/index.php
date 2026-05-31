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

<main>

<!-- Banner Section -->
<section class="homeBanner">   
    <div class="mx-auto">
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


 <section class="home-category">
    <div class="container">
      <div class="row justify-content-center">
        <!-- <div class="col-md-3 catItem shineEffect">
          <a href="products.html">
            <figure><img src="assets/images/products/MilletsParboiledRice.png" alt="Sirpika Millets" /></figure>
            <h2>Millets parboiled rice</h2>
          </a>
        </div> -->

        <div class="col-md-3 catItem shineEffect">
          <a href="products.php">
            <figure><img src="assets/images/products/MilletsRice.png" alt="Sirpika Millets" /></figure>
            <h2>Unpolished Rice & Millets</h2>
          </a>
        </div>

        <div class="col-md-3 catItem shineEffect">
          <a href="products.php">
            <figure><img src="assets/images/products/TraditionalRice.png" alt="Sirpika Millets" /></figure>
            <h2>Traditional Rice</h2>
          </a>
        </div>

        <div class="col-md-3 catItem shineEffect">
          <a href="products.php">
            <figure><img src="assets/images/products/MilletsFlakes.png" alt="Sirpika Millets" /></figure>
            <h2>Millets flakes</h2>
          </a>
        </div>

         <div class="col-md-3 catItem shineEffect">
          <a href="products.php">
            <figure><img src="assets/images/products/RiceFlakes.png" alt="Sirpika Millets" /></figure>
            <h2>Rice flakes</h2>
          </a>
        </div>

      
      </div>
    </div>
  </section>


    <section class="trendingProducts">
    <div class="container">
      <h2 class="head">Trending Products</h2>
      <div class="row">
        <div class="col-md-4">
          <div class="item">
            <img src="assets/images/KarunguruvaiFlakes.png" alt="Sirpika Millets" />
            <div class="content">
              <h2>Karunguruvai Flakes</h2>
              <p>
                High Protein <br />
                Hight Fiber<br />
                No Chemicals
              </p>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="item">
            <img src="assets/images/TangaSamba.png" alt="Sirpika Millets" />
            <div class="content">
              <h2>Tanga Samba</h2>
              <p>
                Energy<br />
                Immunity<br />
                Heart health

              </p>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="item">
            <img src="assets/images/FlakesHoneyMuseli.png" alt="Sirpika Millets" />
            <div class="content">
              <h2>Flakes Honey Museli</h2>
              <p>
                Rich Protein <br />
                Hight Fiber<br />
                Multi Grains Content
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- Welcome Content -->
  <section class="welcome-content text-center">
    <div class="container">
      <h2 class="head">Welcome to Sirpika Millets</h2>
      <h1 class="head">
        Sirpika Millets has taken steps to restore and bring to you today's
        traditional whole grain foods essential for a healthy life
      </h1>
      <p>
        Where we bring you the finest selection of millet-based products that
        are as delicious as they are nutritious. At Sirpika Millets, we are
        passionate about promoting healthy lifestyles through the power of
        millets, one of nature's most nutrient-dense grains. Whether you are a
        health enthusiast, a culinary explorer, or someone looking to make
        healthier dietary choices, you've come to the right place!
      </p>

      <div class="row">
        <div class="col-md-12">
          <iframe class="youtubeVideo" src="https://www.youtube.com/embed/CBbF-qt9oAA?si=Mt3h-Jqhw4pmqFub"
            title="YouTube video player" frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
      </div>
    </div>
  </section>

  <!-- Products Section -->
  <section class="products-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-4 left">
          <img src="assets/images/offer.png" alt="Sirpika Millets" />
        </div>

        <div class="col-md-8 right">
          <h2 class="head bgLeft">
            <span> Explore Our Products</span>
            <a href="./products.php" class="btn btn-primary float-end">View All Products</a>
          </h2>

          <div id="productSlider" class="owl-carousel owl-theme productList">
            <?php
            // Fetch 5 most recently added products
            $recentProductsQuery = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 5");
            if ($recentProductsQuery->num_rows > 0):
              while ($row = $recentProductsQuery->fetch_assoc()):
                $rrp = (float)$row['rrp_price'];
                $sale = (float)$row['sale_price'];
                $basePrice = $sale > 0 ? $sale : $rrp;
                $productId = $row['id'];
                $productImage = !empty($row['image']) ? 'uploads/' . $row['image'] : 'assets/images/placeholder.png';
                $cartQty = $cartQuantities[$productId] ?? 0;
                $orderedValue = $basePrice * $cartQty;
            ?>
            <!-- Product Card -->
            <div class="item element-item product-card" data-category-id="<?= $row['category_id'] ?>">
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
                        <div class="pricing">                           
                            <span class="text-red-600 font-bold text-xl">₹<span id="price_<?= $row['id'] ?>"><?= number_format($basePrice, 2) ?></span></span>
                             <?php if ($rrp > $basePrice): ?>
                            <span class="text-sm text-gray-400 line-through mr-2">₹<?= number_format($rrp, 2) ?></span>
                            <?php endif; ?>
<br>
                      <div class="text-sm text-green-500 font-medium <?= $cartQty > 0 ? '' : 'invisible' ?>" id="ordered_value_<?= $productId ?>">
                        Total Price: <?= $cartQty > 0 ? "₹" . number_format($orderedValue, 2) : '' ?>
                    </div>                            
                        </div>

                        <!-- Quantity Controls -->
                        <div class="mt-3 flex flex-col gap-2 quantityControls">
                          <button id="addBtn_<?= $productId ?>" class="addToCartBtn bg-yellow-400 px-4 h-12 rounded-lg hover:bg-yellow-500 text-red-700  transition font-semibold <?= $cartQty > 0 ? 'hidden' : '' ?>" onclick="addToCart(<?= $productId ?>)">
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
                        </div>
                    </div>
                </div>
            </div>
            <?php 
              endwhile;
            else:
            ?>
              <div class="item">
                <div class="content text-center">
                  <h2>No Products Available</h2>
                  <p>Check back soon for new products!</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="why-millets-section">
    <div class="container">
      <h2 class="head">Why Choose Millets?</h2>
      <div class="row content">
        <div class="col-md-4 left-column">
          <p>
            <strong>Unlock Nutritional Power</strong><br />
            Discover the incredible health benefits of millets, packed with essential vitamins, minerals, and fiber to
            boost your overall well-being.
          </p>

          <p>
            <strong>
              Gluten-Free Goodness</strong><br />
            Enjoy a naturally gluten-free diet with millets, perfect for those with gluten sensitivities or anyone
            looking to diversify their grain choices.
          </p>

          <p>
            <strong>Sustained Energy</strong><br />
            Experience sustained energy throughout the day with millets' complex carbohydrates and low glycemic index,
            keeping you fuller for longer.
          </p>
        </div>
        <div class="col-md-4 image-container">
          <img src="assets/images/why-millets.png" alt="Sirpika Millets" />
        </div>
        <div class="col-md-4 right-column">
          <p>
            <strong>Heart-Healthy Grains</strong><br />
            Support your heart health with millets, rich in magnesium and potassium, which help regulate blood pressure
            and improve cardiovascular function.
          </p>

          <p>
            <strong>Versatile and Delicious</strong><br />
            Explore the culinary versatility of millets, ideal for creating a wide range of delicious recipes from
            breakfast to dinner.
          </p>

          <p>
            <strong>Eco-Friendly Choice</strong><br />
            Make an environmentally conscious choice with millets, as they require less water and are more resilient to
            climate change compared to other grains.
          </p>
        </div>
      </div>
    </div>
  </section>

  <section class="instagram-section">
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <h2 class="head">
            Instagram feed
          </h2>

          <div id="instaSlider" class="owl-carousel owl-theme productList">
            <div class="item">
              <a href="https://www.instagram.com/sirpikamillets20/" target="_blank">
                <img src="assets/images/insta1.png" alt="Sirpika Millets" />
              </a>
            </div>
            <div class="item">
              <a href="https://www.instagram.com/sirpikamillets20/" target="_blank">
                <img src="assets/images/insta2.png" alt="Sirpika Millets" />
              </a>
            </div>
            <div class="item">
              <a href="https://www.instagram.com/sirpikamillets20/" target="_blank">
                <img src="assets/images/insta3.png" alt="Sirpika Millets" />
              </a>
            </div>
            <div class="item">
              <a href="https://www.instagram.com/sirpikamillets20/" target="_blank">
                <img src="assets/images/insta4.png" alt="Sirpika Millets" />
              </a>
            </div>
            <div class="item">
              <a href="https://www.instagram.com/sirpikamillets20/" target="_blank">
                <img src="assets/images/insta5.png" alt="Sirpika Millets" />
              </a>
            </div>
            <div class="item">
              <a href="https://www.instagram.com/sirpikamillets20/" target="_blank">
                <img src="assets/images/insta6.png" alt="Sirpika Millets" />
              </a>
            </div>
          </div>


        </div>
      </div>
    </div>
    </div>
  </section>



<script>
$(document).ready(function () {
  // Home Banner Slider - Full width, 1 item
  $('.homeBanner .owl-carousel').owlCarousel({
    items: 1,
    loop: true,
    autoplay: true,
    autoplayTimeout: 4000,
    dots: false,
    nav: true,
    navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
    
  });

  // Product Slider - Multiple items visible
  $('#productSlider').owlCarousel({
    items: 2,
    loop: true,
    autoplay: false,
    autoplayTimeout: 5000,
    margin: 10,
    dots: true,
    nav: true,
    navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
    responsive: {
      480: {
        items: 2
      },
      768: {
        items: 3
      },
      992: {
        items: 3
      }
    }
  });

  // Instagram Slider - Multiple items visible
  $('#instaSlider').owlCarousel({
    items: 2,
    loop: true,
    autoplay: false,
    autoplayTimeout: 4000,
    dots: true,
    nav: false,
    responsive: {
      480: {
        items: 3
      },
      768: {
        items: 3
      },
      992: {
        items: 4
      }
    }
  });

  // Colorbox for popup images
  $("a.popup").colorbox({
    rel: 'gal',
    width: "80%",
    height: "80%"
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

// Add to Cart button handler
function addToCart(productId) {
  const addBtn = document.getElementById('addBtn_' + productId);
  const qtyDiv = document.getElementById('qtyDiv_' + productId);
  const qtyInput = document.getElementById('qty_' + productId);
  
  // Hide button, show quantity controls
  addBtn.classList.add('hidden');
  qtyDiv.classList.remove('hidden');
  
  // Set initial quantity to 1
  qtyInput.value = 1;
  updateCart(productId);
}

// Update cart and UI
function updateCart(productId) {
  const qty = parseInt(document.getElementById('qty_' + productId).value) || 0;
  const price = parseFloat(document.getElementById('price_' + productId).textContent) || 0;
  const addBtn = document.getElementById('addBtn_' + productId);
  const qtyDiv = document.getElementById('qtyDiv_' + productId);

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

  // If quantity is 0, show button and hide quantity controls
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
</script>

</main>

<?php include '_footer.php'; ?>
