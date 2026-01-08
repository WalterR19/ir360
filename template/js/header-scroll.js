/**
 * ========================================
 * HEADER SCROLL TRANSPARENCY EFFECT
 * Hace el header transparente al hacer scroll,
 * mostrando solo el logo, y reaparece al hover o volver arriba
 * ========================================
 */

(function() {
  'use strict';

  // Variables
  const navbar = document.querySelector('.navbar');
  const navbarBrand = document.querySelector('.navbar-brand');
  const navbarCollapse = document.querySelector('.navbar-collapse');
  const desktopToggler = document.querySelector('.desktop-toggler');
  const mobileToggler = document.querySelector('.navbar-toggler');
  
  let scrollTimeout;
  let lastScrollTop = 0;
  const scrollThreshold = 100; // Píxeles antes de activar el efecto
  
  // Estado del header
  let isHeaderHidden = false;
  let isHovering = false;

  /**
   * Agregar clase de transparencia al header
   */
  function hideHeader() {
    if (!isHovering && window.scrollY > scrollThreshold) {
      navbar.classList.add('header-transparent');
      isHeaderHidden = true;
    }
  }

  /**
   * Mostrar el header completo
   */
  function showHeader() {
    navbar.classList.remove('header-transparent');
    isHeaderHidden = false;
  }

  /**
   * Manejar el scroll
   */
  function handleScroll() {
    const currentScroll = window.scrollY;

    // Si estamos cerca del top, mostrar header
    if (currentScroll < scrollThreshold) {
      showHeader();
      return;
    }

    // Si estamos scrolleando hacia arriba, mostrar header
    if (currentScroll < lastScrollTop) {
      showHeader();
    } else {
      // Scrolleando hacia abajo - ocultar después de 2 segundos sin actividad
      clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(() => {
        if (!isHovering) {
          hideHeader();
        }
      }, 2000);
    }

    lastScrollTop = currentScroll;
  }

  /**
   * Manejar hover sobre el navbar
   */
  function handleMouseEnter() {
    isHovering = true;
    showHeader();
  }

  /**
   * Manejar cuando el mouse sale del navbar
   */
  function handleMouseLeave() {
    isHovering = false;
    // Si estamos scrolleados, ocultar después de 1 segundo
    if (window.scrollY > scrollThreshold) {
      clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(() => {
        if (!isHovering) {
          hideHeader();
        }
      }, 1000);
    }
  }

  /**
   * Inicializar los event listeners
   */
  function init() {
    if (!navbar) return;

    // Event listener para scroll
    window.addEventListener('scroll', handleScroll, { passive: true });

    // Event listeners para hover
    navbar.addEventListener('mouseenter', handleMouseEnter);
    navbar.addEventListener('mouseleave', handleMouseLeave);

    // Mostrar header cuando se abre el menú mobile
    if (mobileToggler) {
      mobileToggler.addEventListener('click', () => {
        showHeader();
      });
    }

    // Mostrar header cuando se abre el offcanvas desktop
    if (desktopToggler) {
      desktopToggler.addEventListener('click', () => {
        showHeader();
      });
    }

    // Prevenir que el header se oculte cuando el menú está abierto
    const offcanvasElements = document.querySelectorAll('.offcanvas');
    offcanvasElements.forEach(offcanvas => {
      offcanvas.addEventListener('show.bs.offcanvas', () => {
        isHovering = true;
        showHeader();
      });
      offcanvas.addEventListener('hide.bs.offcanvas', () => {
        isHovering = false;
        if (window.scrollY > scrollThreshold) {
          setTimeout(hideHeader, 1000);
        }
      });
    });

    // Verificar estado inicial
    if (window.scrollY < scrollThreshold) {
      showHeader();
    }
  }

  // Inicializar cuando el DOM esté listo
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
