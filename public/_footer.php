    </main>
<!--    <footer class="bg-gray-800 text-white p-4 text-center">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($settings['company_name'] ?? 'My Company') ?>. All rights reserved.
    </footer>
-->

<footer class="bg-red-500 py-4 text-center mt-20">
  <div class="container mx-auto text-sm text-white flex flex-col sm:flex-row justify-between gap-4 items-center">
   

    <div class="text-sm text-center sm:text-left">
      &copy; <?= date('Y') ?> <?=htmlspecialchars($settings['company_name'] ?? 'My Company') ?>. All rights reserved.
    </div>

     <div class="flex flex-col sm:flex-row items-center gap-2">
      <a href="index.php" class="hover:underline hover:text-yellow-400">Home</a>  |  
    <a href="info.php?page=about" class="hover:underline hover:text-yellow-400">About Us</a>  |  
    <a href="info.php?page=terms" class="hover:underline hover:text-yellow-400">Terms & Conditions</a>  |  
    <a href="contact.php" class="hover:underline hover:text-yellow-400">Contact Us</a>
    </div>

     <div class="flex space-x-4 text-xl">
      <?php if (!empty($settings['fb'])): ?>
        <a href="<?= htmlspecialchars($settings['fb']) ?>" target="_blank" class="hover:text-yellow-400" aria-label="Facebook">
         <i class="fa-brands fa-facebook"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($settings['x'])): ?>
        <a href="<?= htmlspecialchars($settings['x']) ?>" target="_blank" class="hover:text-yellow-400" aria-label="X (Twitter)">
          <i class="fa-brands fa-x"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($settings['instagram'])): ?>
        <a href="<?= htmlspecialchars($settings['instagram']) ?>" target="_blank" class="hover:text-yellow-400" aria-label="Instagram">
          <i class="fa-brands fa-instagram"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($settings['linkedin'])): ?>
        <a href="<?= htmlspecialchars($settings['linkedin']) ?>" target="_blank" class="hover:text-yellow-400" aria-label="LinkedIn">
         <i class="fa-brands fa-linkedin"></i>
        </a>
      <?php endif; ?>

      <?php if (!empty($settings['youtube'])): ?>
        <a href="<?= htmlspecialchars($settings['youtube']) ?>" target="_blank" class="hover:text-yellow-400" aria-label="YouTube">
          <i class="fa-brands fa-youtube"></i>
        </a>
      <?php endif; ?>
    </div>

  </div>
</footer>


</body>
</html>
