/**
 * Módulo de Navegación (Navbar responsive móvil)
 */

export function initNavbar() {
  const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
  const mobileMenuDropdown = document.getElementById('mobile-menu-dropdown');
  const mobileMenuIcon = document.getElementById('mobile-menu-icon');

  if (!mobileMenuToggle || !mobileMenuDropdown || !mobileMenuIcon) {
    return;
  }

  mobileMenuToggle.addEventListener('click', () => {
    const isHidden = mobileMenuDropdown.classList.contains('hidden');
    if (isHidden) {
      mobileMenuDropdown.classList.remove('hidden');
      mobileMenuIcon.textContent = 'close';
      mobileMenuToggle.setAttribute('aria-expanded', 'true');
      mobileMenuToggle.setAttribute('aria-label', 'Cerrar menú');
    } else {
      mobileMenuDropdown.classList.add('hidden');
      mobileMenuIcon.textContent = 'menu';
      mobileMenuToggle.setAttribute('aria-expanded', 'false');
      mobileMenuToggle.setAttribute('aria-label', 'Abrir menú de navegación');
    }
  });
}
