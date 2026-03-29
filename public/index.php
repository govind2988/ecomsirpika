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
          <a href="products.html">
            <figure><img src="assets/images/products/MilletsRice.png" alt="Sirpika Millets" /></figure>
            <h2>Unpolished Rice & Millets</h2>
          </a>
        </div>

        <div class="col-md-3 catItem shineEffect">
          <a href="products.html">
            <figure><img src="assets/images/products/TraditionalRice.png" alt="Sirpika Millets" /></figure>
            <h2>Traditional Rice</h2>
          </a>
        </div>

        <div class="col-md-3 catItem shineEffect">
          <a href="products.html">
            <figure><img src="assets/images/products/MilletsFlakes.png" alt="Sirpika Millets" /></figure>
            <h2>Millets flakes</h2>
          </a>
        </div>

         <div class="col-md-3 catItem shineEffect">
          <a href="products.html">
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
            <a href="./products.html" class="btn btn-primary float-end">View All Products</a>
          </h2>

          <div id="productSlider" class="owl-carousel owl-theme productList">

       
          <div class="item">
            <div class="zoomOut shineEffect">
              <figure>
                <a class="popup" href="assets/images/products/flakes/FoxtailMilletFlakes.png" title="500g - ₹75.00">
                  <img src="assets/images/products/flakes/FoxtailMilletFlakes.png" alt="Sirpika Millets" />
                </a>

              </figure>
            </div>
            <div class="content">
              <h2>Foxtail Millet Flakes</h2>
              <p class="weight">400g</p>
              <p class="price">50.00 <span>55.00</span></p>
            </div>
          </div>
       
          <div class="item">
            <div class="zoomOut shineEffect">
              <figure>
                <a class="popup" href="assets/images/products/flakes/LittleMilletFlakes.png" title="500g - ₹75.00">
                  <img src="assets/images/products/flakes/LittleMilletFlakes.png" alt="Sirpika Millets" />
                </a>

              </figure>
            </div>
            <div class="content">
              <h2>Little Millet Flakes</h2>
              <p class="weight">400g</p>
              <p class="price">50.00 <span>55.00</span></p>
            </div>
          </div>


          
          <div class="item">
            <div class="zoomOut shineEffect">
              <figure>
                <a class="popup" href="assets/images/placeholder.png" title="500g - ₹150.00">
                  <img src="assets/images/placeholder.png" alt="Sirpika Millets" />
                </a>

              </figure>
            </div>
            <div class="content">
              <h2>Karapkuvani Rice Flakes</h2>
              <p class="weight">500g</p>
              <p class="price">150.00 <span>160.00</span></p>
            </div>
          </div>
      
          <div class="item">
            <div class="zoomOut shineEffect">
              <figure>
                <a class="popup" href="assets/images/placeholder.png" title="500g - ₹130.00">
                  <img src="assets/images/placeholder.png" alt="Sirpika Millets" />
                </a>

              </figure>
            </div>
            <div class="content">
              <h2>Rice Flakes</h2>
              <p class="weight">500g</p>
              <p class="price">130.00 <span>150.00</span></p>
            </div>
          </div>
        
       



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
    items: 1,
    loop: true,
    autoplay: true,
    autoplayTimeout: 5000,
    dots: true,
    nav: true,
    navText: ['<i class="fa fa-chevron-left"></i>', '<i class="fa fa-chevron-right"></i>'],
    responsive: {
      480: {
        items: 1
      },
      768: {
        items: 2
      },
      992: {
        items: 3
      }
    }
  });

  // Instagram Slider - Multiple items visible
  $('#instaSlider').owlCarousel({
    items: 4,
    loop: true,
    autoplay: false,
    autoplayTimeout: 4000,
    dots: true,
    nav: false,
    responsive: {
      480: {
        items: 2
      },
      768: {
        items: 3
      },
      992: {
        items: 4
      }
    }
  });
});
</script>

</main>

<?php include '_footer.php'; ?>
