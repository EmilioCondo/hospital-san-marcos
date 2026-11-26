<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}

require __DIR__ . '/../partials/head.php';
?>
  <body class="bg-surface text-on-surface font-body-md antialiased selection:bg-primary-container selection:text-on-primary">
    <div id="app" class="flex flex-col min-h-screen bg-surface text-on-surface">
      <?php require __DIR__ . '/../partials/navbar.php'; ?>

      <main class="w-full pt-[117px] bg-surface flex-1">
        <?php require $viewFile; ?>
      </main>

      <?php
      require __DIR__ . '/../partials/footer.php';
      require __DIR__ . '/../partials/whatsapp-float.php';
      ?>
    </div>
    <script type="module" src="<?= e(asset('js/app.js')) ?>"></script>
  </body>
</html>
