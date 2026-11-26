<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}
?>
<a
  aria-label="Contacto directo por WhatsApp con Hospital San Marcos"
  class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 rounded-full bg-secondary text-on-secondary shadow-lg hover:scale-110 active:scale-95 transition-all duration-300 ring-4 ring-secondary/20"
  href="https://wa.me/593987973511"
  rel="noopener noreferrer"
  target="_blank"
>
  <span class="material-symbols-outlined text-[28px]">chat</span>
</a>
