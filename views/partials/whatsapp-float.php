<?php
declare(strict_types=1);

if (!defined('HSM_APP')) {
    http_response_code(403);
    exit('Acceso directo no permitido.');
}
?>
<a
  aria-label="Contacto directo por WhatsApp con Hospital San Marcos"
  class="fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 rounded-full shadow-lg hover:scale-110 active:scale-95 transition-all duration-300"
  style="background-color: #25D366; color: #ffffff;"
  href="https://wa.me/593987973511"
  rel="noopener noreferrer"
  target="_blank"
>
  <i class="fa-brands fa-whatsapp" style="font-size: 34px;" aria-hidden="true"></i>
</a>
