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
</script>

</main>

<?php include '_footer.php'; ?>
