<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../includes/config.php';
include_once 'settings.php';


// AJAX Cart Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'update_cart') {
    $product_id = (int)$_POST['product_id'];
    $quantity = max(0, (int)$_POST['quantity']);

    if ($quantity === 0) {
        unset($_SESSION['cart'][$product_id]);
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    $cartCount = array_sum($_SESSION['cart']);
    echo json_encode(['status' => 'success', 'cartCount' => $cartCount]);
    exit;
}

$cartCount = array_sum($_SESSION['cart'] ?? []);

?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <base href="<?= htmlspecialchars(BASE_URL) ?>">
   <title><?= htmlspecialchars($settings['meta_title'] ?? 'Admin Panel') ?></title>
   <meta name="keywords" content="<?= htmlspecialchars($settings['meta_keywords'] ?? '') ?>">
   <meta name="description" content="<?= htmlspecialchars($settings['meta_description'] ?? '') ?>">

  <meta property="og:title" content="<?= htmlspecialchars($settings['meta_title'] ?? 'Admin Panel') ?>">
  <meta property="og:description" content="<?= htmlspecialchars($settings['meta_description'] ?? '') ?>">
  <meta property="og:url" content="<?= htmlspecialchars(BASE_URL) ?>">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="<?= htmlspecialchars(BASE_URL . 'uploads/' . $settings['favicon'] ?? 'favicon.ico') ?>" type="image/x-icon">



 <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
  <!-- Bootstrap CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css"/>
  <!-- WOW/Animate CSS CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <!-- Use BASE_URL to ensure correct path -->
  <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL) ?>assets/css/custom.css">
  <link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL) ?>assets/css/responsive.css">

  
  


<?php
// Output Google Analytics code as raw HTML (not escaped)
if (!empty($settings['google_analytics'])) {
    echo $settings['google_analytics'];
}
?>


</head>


<body class="flex flex-col min-h-screen">

      <div class="marquee-container w-full bg-white text-primary py-1 overflow-hidden shadow-md  relative">                
         <div class="whitespace-nowrap marquee-text text-center font-semibold text-sm">         
            <?php echo $settings['header_message']?>
         </div>            
      </div>

       <!-- Sticky Header -->    
      <header class="sticky top-0 z-30 bg-white shadow-md">
                 
      <div class="container mx-auto flex items-center justify-between px-4">
           

       <!-- Logo -->           
            <div class="logo text-center start-0 container-fluid">
              <a href="index.php" class="flex items-center gap-2">
              <?php if (!empty($settings['logo'])): ?>
                  <span class="text-2xl font-bold text-red-700 m-auto">
                    <img src="uploads/<?= htmlspecialchars($settings['logo']) ?>" alt="Logo">
                  </span>
              <?php else: ?>
                <h1 class="text-xl font-bold text-gray-700"><?= htmlspecialchars($settings['company_name'] ?? 'Company Name') ?>
                </h1>
              <?php endif; ?>
                    </a>
            </div>



                       
            <!-- Menu -->
            <nav class="hidden lg:flex space-x-6 uppercase deskMenu">
              <a href="index.php" class="text-primary font-medium border-b-4 border-yellow-400 hover:border-b-4 py-6">Home</a>
              <a href="products.php" class="text-gray-700 font-medium border-b-4 border-transparent hover:border-yellow-400 py-6">Products</a>
              <a href="aboutus.php" class="text-gray-700 font-medium border-b-4 border-transparent hover:border-yellow-400 py-6">About Us</a>
              <a href="contactus.php" class="text-gray-700 font-medium border-b-4 border-transparent hover:border-yellow-400 py-6">Contact Us</a>
            </nav>


          



            <!-- Icons -->
            <div class="flex items-center space-x-4">
              <!-- Cart Icon -->                              
               <div class="relative mr-4">
                 <a href="cart.php">
                   <i class="fa fa-shopping-cart text-2xl text-pri"></i>

                    <?php if (isset($cartCount) && $cartCount > 0): ?>
                      <span class="absolute -top-2 -right-2 bg-yellow-400 text-red-700 text-xs font-bold rounded-full px-1.5"><?= $cartCount ?></span>
                    <?php endif; ?>
                  </a>
               </div>
           

              <a class="online-order btn btn-primary text-uppercase" href="products.php">
                   Online Order
              </a>

              <!-- Mobile Menu Button -->
              <button class="lg:hidden text-red-600 text-2xl focus:outline-none" id="mobile-menu-btn">
                  <i class="fa fa-bars"></i>
              </button>
            </div>



             <!-- Mobile Menu -->
         <div class="hidden px-4 pb-3 mobileViewMenu" id="mobile-menu">
                      
         <nav class="flex flex-column uppercase">
              <i class="fa fa-times text-2xl text-right mb-4 cursor-pointer mm_closebtn" id="mobile-menu-close"></i>
              <a href="index.php" class="text-primary font-medium border-b-4 border-yellow-400 hover:border-b-4 py-3">Home</a>
              <a href="products.php" class="text-white font-medium border-b border-gray-300 hover:border-yellow-400 py-6">Products</a>
              <a href="aboutus.php" class="text-white font-medium border-b border-gray-300  hover:border-yellow-400 py-6">About Us</a>
              <a href="contactus.php" class="text-white font-medium border-b border-gray-300  hover:border-yellow-400 py-6">Contact Us</a>
            </nav>

        </div>

      </header>

      <script>
        // Mobile menu toggle functionality
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuCloseBtn = document.getElementById('mobile-menu-close');
        
        if (mobileMenuBtn && mobileMenu) {
          mobileMenuBtn.addEventListener('click', function(e) {
            e.preventDefault();
            mobileMenu.classList.toggle('hidden');
          });
          
          // Close mobile menu when close button is clicked
          if (mobileMenuCloseBtn) {
            mobileMenuCloseBtn.addEventListener('click', function(e) {
              e.preventDefault();
              mobileMenu.classList.add('hidden');
            });
          }
          
          // Close mobile menu when a link is clicked
          const mobileLinks = mobileMenu.querySelectorAll('a');
          mobileLinks.forEach(link => {
            link.addEventListener('click', function() {
              mobileMenu.classList.add('hidden');
            });
          });
        }
        
        // Add headerSticky class on scroll
        const header = document.querySelector('header');
        
        window.addEventListener('scroll', function() {
          if (window.scrollY > 0) {
            header.classList.add('headerSticky');
          } else {
            header.classList.remove('headerSticky');
          }
        });
      </script>



