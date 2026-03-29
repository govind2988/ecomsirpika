<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
include_once 'settings.php';

include_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../vendor/autoload.php';


use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;


$conn = getDbConnection();
if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

$message = '';
$bShowCart = true;
$showQrModal = false;
$qrDataUri = '';
$orderTotal = 0;
$merchantUpi = MERCHANT_UPI;
$merchantName = $settings['company_name'];
$whNumber = WHATSAPP_NO;
$mode = ORDER_MODE;
$order_id = 0;
if (isset($_POST['place_order'])) {
    $captcha_input = strtoupper(trim($_POST['captcha_input'] ?? ''));
    $captcha_code = $_SESSION['captcha'] ?? '';
    $cart = $_SESSION['cart'];
  //  $mode = $_POST['mode'] ?? 'whatsapp';
	
    if ($captcha_input !== $captcha_code) {
        $message = "❌ Invalid CAPTCHA.";
    } elseif (empty($cart)) {
        $message = "🛒 Cart is empty!";
    }
	else {
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $email = $conn->real_escape_string($_POST['email']);
        $address = $conn->real_escape_string($_POST['address']);

        $product_ids = array_keys($cart);
        $idList = implode(',', array_map('intval', $product_ids));
        $res = $conn->query("SELECT id,name,rrp_price,sale_price FROM products WHERE id IN ($idList)");
        $order_items = []; $total = 0;

        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['id'];
            $qty = (int)($cart[$pid] ?? 0);
            if ($qty <= 0) continue;
            $price = ($row['sale_price'] ?? 0) > 0 ? $row['sale_price'] : $row['rrp_price'];
            $subtotal = $price * $qty;
            $total += $subtotal;
            $order_items[] = ['product_id'=>$pid,'product_name'=>$row['name'],'price'=>$price,'quantity'=>$qty];
        }

        $conn->query("INSERT INTO orders (status, customer_name, customer_phone, customer_email, customer_address, total)
                      VALUES ('New Order','$name','$phone','$email','$address',$total)");
        $order_id = $conn->insert_id;
		
        foreach ($order_items as $it) {
            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price)
                          VALUES ($order_id,{$it['product_id']},{$it['quantity']},{$it['price']})");
        }

     //   unset($_SESSION['cart'], $_SESSION['captcha']);
	 //	$_SESSION['cart'] = [];
     //   $bShowCart = false;
		
     //   $orderTotal = $total + $settings['shipping_charges']; // include shipping if desired
		$orderTotal = $total;

       if ($mode === 'whatsapp') {

		// Build clean message (NO %0A here)		
		$msg = "*New Order Received*\n\n";
		
		$msg .= "Order ID: #{$order_id}\n\n";

		$msg .= "Name: $name\n";
		$msg .= "Phone: $phone\n";
		$msg .= "Email: $email\n";
		$msg .= "Address: $address\n\n";

		$msg .= "*Order Items:*\n";

		foreach ($order_items as $it) {
			$lineTotal = $it['price'] * $it['quantity'];
			$msg .= "• {$it['product_name']} (x{$it['quantity']}) - ₹{$lineTotal}\n";
		}


		$msg .= "\n\n Payment: WhatsApp Order";

		$msg .= "Total: Rs {$orderTotal}";

		$encoded = rawurlencode($msg);

		header("Location: https://wa.me/$whNumber?text=$encoded");
		exit;
		
		} else {
            $message = "✅ Order placed successfully!";
			
			// Build QR Code
			$upiStr = "upi://pay?pa={$merchantUpi}&pn={$merchantName}&am=" . number_format($orderTotal, 2) . "&cu=INR";

			// Create QR code image using Builder (v6+ style)
			$result = Builder::create()
				->writer(new PngWriter())
				->data($upiStr)
				->encoding(new Encoding('UTF-8'))
				->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
				->size(300)
				->margin(10)
				->build();
		    $qrDataUri = 'data:'.$result->getMimeType().';base64,'.base64_encode($result->getString());
            $showQrModal = true;
        }
    }
}
// Handle AJAX quantity update
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    $pid = (int)$_POST['cart_id'];
    $qty = max(1, (int)$_POST['quantity']);
    if (isset($_SESSION['cart'][$pid])) {
        $_SESSION['cart'][$pid] = $qty;
		$cartCount = array_sum($_SESSION['cart']);
        echo json_encode(['success' => true, 'cartCount' => $cartCount]);
        exit;
    }
	
    echo json_encode(['success' => false, 'cartCount' => 0]);
    exit;
}

// Handle item removal
if (isset($_GET['remove'])) {
    $pid = (int)$_GET['remove'];
    unset($_SESSION['cart'][$pid]);
}
// handle quantity ajax, updates, remove, and compute $cartItems, $total for initial display
$productIds = array_keys($_SESSION['cart']);
$cartItems = []; $total = 0;
if (!empty($productIds)) {
	$idList = implode(',', array_map('intval', $productIds));
	$res = $conn->query("SELECT id,name,
		CASE WHEN sale_price IS NOT NULL AND sale_price!=0 THEN sale_price ELSE rrp_price END AS price
		FROM products WHERE id IN ($idList)");
	while ($row = $res->fetch_assoc()) {
		$pid = $row['id']; $qty = $_SESSION['cart'][$pid];
		$sub = $qty * $row['price']; $total += $sub;
		$row['quantity'] = $qty; $row['subtotal'] = $sub;
		$cartItems[] = $row;
	}
}

include_once '_header.php';
?>

<main class="container mx-auto mt-8">
 <?php if ($message): ?>
   <div id="msgHolder"class="mb-4 bg-yellow-100 text-yellow-800 p-3 rounded"><?= $message ?></div>
 <?php else: ?>
   <div id="msgHolder"class="mb-4 bg-yellow-100 text-yellow-800 p-3 rounded hidden"></div>
  <h1 class="text-2xl font-bold mb-6">Shopping Cart</h1>
 <?php endif; ?>
 <?php if (!empty($cartItems)): ?>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
   <p id="cartMsg" class="text-lg text-gray-600 hidden"></p>
    <div class="md:col-span-2">
      <form id="cartForm" method="post">
        <div class="overflow-x-auto rounded-lg shadow-lg bg-white">
          <table class="min-w-full">
            <thead class="sticky top-0 z-20 bg-yellow-400 text-red-700 text-left">
              <tr>
                <th class="p-3 font-semibold">Product</th>
                <th class="p-3 font-semibold">Price</th>
                <th class="p-3 font-semibold">Quantity</th>
                <th class="p-3 font-semibold">Subtotal</th>
                <th class="p-3 font-semibold text-center">Remove</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($cartItems as $item): ?>
              <tr class="border-t hover:bg-yellow-50">
                <td class="w-1/3 p-3 font-medium"><?= htmlspecialchars($item['name']) ?></td>
                <td class="p-3 text-lg ">₹<?= number_format($item['price'], 2) ?></td>
                <td class="p-3">
                  <div class="flex items-center">
                    <button type="button" onclick="changeQty(<?= $item['id'] ?>, -1)" class="bg-yellow-400 text-red-700 px-2 py-1 rounded-l hover:bg-yellow-500 w-10 h-10"><i class="fa-solid fa-minus"></i></button>
                    <input type="number"
                           id="qty-<?= $item['id'] ?>"
                           class="qty-input px-4 bg-yellow-100 text-red-700 font-semibold text-center border-0 w-16 h-10"
                           min="1"
                           data-cart-id="<?= $item['id'] ?>"
                           data-price="<?= $item['price'] ?>"
                           name="quantities[<?= $item['id'] ?>]"
                           value="<?= $item['quantity'] ?>" />
                    <button type="button" onclick="changeQty(<?= $item['id'] ?>, 1)" class="h-10 w-10 bg-yellow-400 text-red-700 px-2 py-1 rounded-r hover:bg-yellow-500"><i class="fa-solid fa-plus"></i></button>
                  </div>
                </td>
                <td class="p-3 subtotal text-green-700 font-semibold" id="subtotal-<?= $item['id'] ?>">₹<?= number_format($item['subtotal'], 2) ?></td>
                <td class="p-3 text-center">
                  <a href="cart.php?remove=<?= $item['id'] ?>" class="text-red-600 hover:text-red-800 font-bold px-2 py-1 rounded hover:bg-yellow-200 transition"><i class="fa-solid fa-trash-can"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </form>
    </div>
    <div>
      <div class="bg-white rounded-lg shadow-lg p-6 mb-4">
        <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
        <?php
          $itemCount = array_sum(array_column($cartItems, 'quantity'));
        //  $shipping = $total > 0 ? $settings['shipping_charges'] : 0;
		  $shipping = 0;
          $orderTotal = $total + $shipping;
        ?>
        <div class="flex justify-between py-2 border-b">
          <span class="font-medium">No. of Items</span>
          <span id="cart-count"><?= $itemCount ?></span>
        </div>
        <div class="flex justify-between py-2 border-b">
          <span class="font-medium">Subtotal</span>
          <span>₹<span id="summary-subtotal"><?= number_format($total, 2) ?></span></span>
        </div>
        <div class="flex justify-between py-2 border-b">
          <span class="font-medium">Shipping</span>
          <span>₹<span id="summary-shipping"><?= number_format($shipping, 2) ?></span></span>
        </div>
        <div class="flex justify-between py-2 text-lg font-bold">
          <span>Order Total</span>
          <span>₹<span id="summary-total"><?= number_format($orderTotal, 2) ?></span></span>
        </div>
      </div>
	   <div class="bg-white rounded-lg shadow-lg p-6 ">
        <h2 class="text-xl font-semibold mb-4">Delivery Address</h2>
      
      <form id="checkoutForm" method="post">
      <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>">

      <div  class="grid grid-cols-2 gap-4">

      <div class="mb-2">
        <label>Name:</label>
        <input type="text" name="name" required class="w-full border p-2 rounded" />
      </div>
      <div class="mb-2">
        <label>Phone:</label>
        <input type="text" name="phone" required class="w-full border p-2 rounded" />
      </div>
      <div class="mb-2">
        <label>Email:</label>
        <input type="email" name="email" required class="w-full border p-2 rounded" />
      </div>
      <div class="mb-2">
        <label>State:</label>
        <input type="text" name="state" required class="w-full border p-2 rounded" />
      </div>
      <div class="mb-2">
        <label>City:</label>
        <input type="text" name="city" required class="w-full border p-2 rounded" />
      </div>
      <div class="mb-2">
        <label>Pincode:</label>
        <input type="text" name="pincode" required class="w-full border p-2 rounded" />
      </div>
      <div class="mb-2">
        <label>Address:</label>
        <textarea name="address" required class="w-full h-24 border p-2 rounded"></textarea>
      </div>

      <!-- CAPTCHA -->
      <div class="mb-2">
	    <base href="<?= htmlspecialchars(BASE_URL) ?>">
		<script src="./assets/js/captcha.js"></script>
        <label>CAPTCHA:</label>
        <div class="flex items-center space-x-4">
          <div id="captchaText" class="font-mono text-lg bg-gray-200 px-4 py-2 rounded select-none"></div>
          <button type="button" onclick="generateCaptcha()" class="text-sm text-blue-600 hover:underline">↻ Refresh</button>
        </div>
        <input type="text" name="captcha_input" id="captcha_input" placeholder="Enter above text" required class="mt-2 w-full border p-2 rounded uppercase tracking-widest" />
		<p id="captchaError" class="text-red-600 text-sm mt-1 hidden">❌ Invalid CAPTCHA. Please try again.</p>
      </div>
      </div>

      <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded font-bold shadow transition uppercase">
      Confirm & Place Order
      </button>
    </form>   
       
      </div>
    </div>
  </div>
  <?php else: ?>
    <div class="bg-white rounded-lg shadow p-8 text-center">
    <?php if ($bShowCart): ?>
		<p id="cartMsg" class="text-lg">Your cart is empty.</p>		
    <?php endif; ?>
    <a href="<?= htmlspecialchars(BASE_URL) ?>" class="mt-4 bg-primary  inline-block text-white px-4 py-2 rounded font-medium">Continue Shopping</a>
    </div>
  <?php endif; ?>

  

 <?php if ($showQrModal): ?>
  <div id="qrModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
     <div class="bg-white p-6 rounded-lg text-center w-full max-w-sm relative">
       <button onclick="closeQrModal(<?=$order_id ?>)" class="absolute -top-6 -right-6 text-white text-2xl"><i class="fa-solid fa-xmark"></i></button>
       <h2 class="text-xl font-semibold mb-4">Optional UPI Payment</h2>
       <img src="<?= $qrDataUri ?>" alt="UPI QR" class="mx-auto border p-2 shadow" width="250" height="250">
       <p class="mt-2"><strong>Amount:</strong> ₹<?= number_format($orderTotal,2) ?></p>
       <p><strong>UPI ID:</strong> <?= htmlspecialchars($merchantUpi) ?></p>
       <a href="https://wa.me/<?= htmlspecialchars($whNumber) ?>?text=<?= urlencode("Paid ₹".number_format($orderTotal,2)." to {$merchantUpi}. Screenshot attached.") ?>"
          target="_blank"
          class="inline-block bg-green-600 text-white px-4 py-2 rounded mt-4">
         Share the details via WhatsApp
       </a>
       <button onclick="closeQrModal(<?=$order_id ?>)" class="mx-auto block text-red-600 mt-4">Done</button>
     </div>
   </div>
 <?php endif; ?>

</main>
<script>
  
 
  var shippingCharges = 0;
  
  function changeQty(cartId, delta) {
    const input = document.getElementById('qty-' + cartId);
    let qty = parseInt(input.value) + delta;
    if (qty < 1) qty = 1;
    input.value = qty;
    input.dispatchEvent(new Event('change'));
  }
  
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.qty-input').forEach(input => {
      input.addEventListener('change', () => {
        const cartId = input.dataset.cartId;
        const quantity = parseInt(input.value);
        const price = parseFloat(input.dataset.price);

        if (quantity < 1 || isNaN(quantity)) return;

        const newSubtotal = quantity * price;
        document.getElementById(`subtotal-${cartId}`).innerText = '₹' + newSubtotal.toFixed(2);

        let newTotal = 0;
        let totalCount = 0;

        document.querySelectorAll('.qty-input').forEach(i => {
          const q = parseInt(i.value);
          const p = parseFloat(i.dataset.price);
          if (!isNaN(q) && !isNaN(p)) {
            newTotal += q * p;
            totalCount += q;
          }
        });

        document.getElementById('summary-subtotal').innerText = newTotal.toFixed(2);
        document.getElementById('cart-count').innerText = totalCount;
		const shipping = newTotal > 0 ? shippingCharges : 0;
        document.getElementById('summary-shipping').innerText = shipping.toFixed(2);
        document.getElementById('summary-total').innerText = (newTotal + shipping).toFixed(2);
		
		fetch('cart.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `cart_id=${cartId}&quantity=${quantity}`
        })
		.then(response => response.json())
		.then(data => {
			console.log(data);
			console.log(data.success);
			console.log(data.cartCount);
			if(data.success)
				{
				updateCartCount(data.cartCount);
				}
			else 
				{
				alert('Failed to update quantity.');
				}
		  })
		  .catch(error => {
			console.error("Error:", error);
			alert('Failed to update quantity.');
		  });       
      });
    });
  });
  
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

   function closeQrModal(orderID) {
    const modal = document.getElementById('qrModal');
    if (modal) {
      modal.remove();
	 
	  window.location.href = "thankyou.php?order_id=" + orderID;
	
   // Show countdown message
   /*   const msg = document.getElementById('msgHolder');
	  if(msg!= null)
		{
	
	  msg.classList.remove('hidden');
      msg.innerText = 'Redirecting to homepage in 5 seconds...';
			}
      // Update countdown every second
      let counter = 5;
      const interval = setInterval(() => {
        counter--;
        if (counter > 0 && msg != null) {
          msg.innerText = `Redirecting to homepage in ${counter} seconds...`;
        } else {
          clearInterval(interval);
		  if(msg != null){
		  msg.classList.add('hidden');		 
		  }
		  window.location.href = "index.php";          
        }
      }, 1000); */
	window.location.href = "thankyou.php?order_id=" + orderID;
    }
  }
  
document.getElementById('checkoutForm').addEventListener('submit', async function (event) {
    event.preventDefault(); // Stop form from reloading the page

    const captchaInput = document.getElementById('captcha_input').value.trim();
    const captchaError = document.getElementById('captchaError');
    captchaError.classList.add('hidden');
    captchaError.innerText = "";

    if (captchaInput === "") {
      captchaError.innerText = "Please enter CAPTCHA.";
      captchaError.classList.remove('hidden');
      return;
    }

    try {
      const response = await fetch('validate_captcha.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `captcha_input=${encodeURIComponent(captchaInput)}`
      });

      if (!response.ok) throw new Error("Network error or invalid response");

      const data = await response.json();

      if (data.success) {
        // CAPTCHA valid - submit form
		// Create hidden input to mimic button press
		  const form = document.getElementById('checkoutForm');
		  
		  const hiddenInput = document.createElement('input');
		  hiddenInput.type = 'hidden';
		  hiddenInput.name = 'place_order';   // same as the button's name
		  hiddenInput.value = '1';            // same as the button's value		  		  
		  form.appendChild(hiddenInput);
		  form.submit();
      } else {
        captchaError.innerText = "Invalid CAPTCHA. Please try again.";
        captchaError.classList.remove('hidden');
      }

    } catch (error) {
      captchaError.innerText = "Error validating CAPTCHA: " + error.message;
      captchaError.classList.remove('hidden');
      console.error("AJAX Error:", error);
    }
  });
</script>

<?php include '_footer.php'; ?>
