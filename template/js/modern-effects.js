/**
 * ========================================
 * MODERN EFFECTS JS
 * Efectos profesionales para IR360
 * ========================================
 */

// ===============================================
// PRELOADER - ULTRA OPTIMIZED - CLEAN NORMAL
// ===============================================
(function() {
  'use strict';
  
  // Step 1: Inject critical preloader CSS FIRST
  const criticalCSS = document.createElement('style');
  criticalCSS.textContent = `
    html, body { margin: 0; padding: 0; }
    body { overflow: hidden; }
    body.loaded { overflow: auto; }
    .preloader {
      position: fixed; top: 0; left: 0; right: 0; bottom: 0;
      width: 100vw; height: 100vh;
      background: linear-gradient(135deg, #032841 0%, #1a3a52 100%);
      display: flex; justify-content: center; align-items: center;
      z-index: 999999; opacity: 1; visibility: visible;
      will-change: opacity; transition: opacity 0.3s ease-out;
      backface-visibility: hidden; contain: layout style paint;
    }
    .preloader.hide { opacity: 0; visibility: hidden; }
    body:not(.loaded) > *:not(.preloader) { visibility: hidden !important; }
  `;
  document.head.insertBefore(criticalCSS, document.head.firstChild);
  
  // Step 2: Full preloader styles - NORMAL SIZE
  const preloaderStyles = document.createElement('style');
  preloaderStyles.textContent = `
    * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
    
    .preloader {
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      width: 100vw; height: 100vh;
      background: linear-gradient(135deg, #032841 0%, #1a3a52 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 999999;
      opacity: 1;
      visibility: visible;
      will-change: opacity;
      transition: opacity 0.3s ease-out;
      backface-visibility: hidden;
      -webkit-backface-visibility: hidden;
      contain: layout style paint;
    }
    
    .preloader.hide {
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
    }
    
    .preloader-content {
      text-align: center;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 1.2rem;
      will-change: transform;
      transform: translateZ(0);
    }
    
    /* Logo - NORMAL SIZE */
    .logo-container {
      animation: logoFloat 2s ease-in-out infinite;
      will-change: transform;
    }
    
    .preloader-logo {
      max-width: 140px;
      height: auto;
      display: block;
      filter: drop-shadow(0 4px 12px rgba(204, 255, 0, 0.2));
      will-change: transform;
    }
    
    @keyframes logoFloat {
      0%, 100% { transform: translateY(0) scale(1); opacity: 1; }
      50% { transform: translateY(-4px) scale(1.01); opacity: 0.98; }
    }
    
    /* Spinner */
    .spinner-container {
      width: 60px; height: 60px;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    .spinner {
      width: 50px; height: 50px;
      border: 2px solid rgba(204, 255, 0, 0.1);
      border-top: 2px solid #ccff00;
      border-radius: 50%;
      animation: spinnerRotate 1s linear infinite;
      will-change: transform;
      backface-visibility: hidden;
      box-shadow: 0 0 8px rgba(204, 255, 0, 0.15);
    }
    
    @keyframes spinnerRotate {
      to { transform: rotate(360deg); }
    }
    
    /* Progress bar */
    .progress-container {
      width: 200px; height: 2px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 1px;
      overflow: hidden;
      margin: 0;
    }
    
    .progress-bar {
      height: 100%; width: 0%;
      background: linear-gradient(90deg, #ccff00, #a8d400);
      animation: progressFill 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
      box-shadow: 0 0 8px rgba(204, 255, 0, 0.3);
    }
    
    @keyframes progressFill {
      0% { width: 0%; }
      100% { width: 100%; }
    }
    
    /* Loading text */
    .loading-text {
      font-size: 0.9rem;
      color: #ffffff;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      font-weight: 500;
      letter-spacing: 0.5px;
      opacity: 0.85;
      margin: 0;
      padding: 0;
      min-height: 20px;
    }
    
    .dots::after {
      content: '.';
      animation: dotsAnim 1.5s steps(3, end) infinite;
    }
    
    @keyframes dotsAnim {
      0%, 20% { content: '.'; }
      40% { content: '..'; }
      60% { content: '...'; }
      80%, 100% { content: ''; }
    }
    
    @media (max-width: 768px) {
      .preloader-logo { max-width: 120px; }
      .preloader-content { gap: 1rem; }
      .spinner { width: 45px; height: 45px; border-width: 2px; }
      .progress-container { width: 160px; }
      .loading-text { font-size: 0.85rem; }
    }
    
    @media (max-width: 480px) {
      .preloader-logo { max-width: 100px; }
      .spinner { width: 40px; height: 40px; }
      .progress-container { width: 140px; }
    }
  `;
  document.head.appendChild(preloaderStyles);
  
  // Step 3: Create preloader HTML
  const createPreloader = () => {
    if (!document.getElementById('preloader')) {
      const preloader = document.createElement('div');
      preloader.id = 'preloader';
      preloader.className = 'preloader';
      preloader.innerHTML = `
        <div class="preloader-content">
          <div class="logo-container">
            <img src="./images/IR360 Blanco.png" alt="IR360 Logo" class="preloader-logo" loading="eager">
          </div>
          <div class="spinner-container">
            <div class="spinner"></div>
          </div>
          <div class="progress-container">
            <div class="progress-bar"></div>
          </div>
          <p class="loading-text">Cargando<span class="dots"></span></p>
        </div>
      `;
      if (document.body) {
        document.body.insertBefore(preloader, document.body.firstChild);
      }
    }
  };
  
  // Step 4: Create preloader immediately
  if (document.body) {
    createPreloader();
  } else {
    document.addEventListener('DOMContentLoaded', createPreloader, { once: true });
  }
  
  // Step 5: Hide preloader function
  window.hidePreloader = function() {
    const preloader = document.getElementById('preloader');
    if (preloader && !preloader.classList.contains('hide')) {
      preloader.classList.add('hide');
      
      // Enable scrolling after fade starts
      setTimeout(() => {
        document.body.classList.add('loaded');
      }, 50);
      
      // Remove from DOM after transition
      setTimeout(() => {
        if (preloader.parentNode) preloader.remove();
      }, 350);
    }
  };
  
  // Step 6: Hide on page load
  window.addEventListener('load', () => {
    requestAnimationFrame(() => setTimeout(hidePreloader, 800));
  }, { once: true });
  
  // Step 7: Fallback (max 5 seconds)
  setTimeout(() => {
    if (!document.body.classList.contains('loaded')) {
      hidePreloader();
    }
  }, 5000);
})();

// Wait for page to load and initialize effects
document.addEventListener('DOMContentLoaded', function() {
  
  // 1. NAVBAR SCROLL EFFECTS
  initNavbarScroll();
});

/**
 * 1. NAVBAR SCROLL EFFECTS
 * Navbar transparente que cambia al hacer scroll
 */
function initNavbarScroll() {
  const navbar = document.querySelector('.navbar');
  let lastScrollTop = 0;
  
  window.addEventListener('scroll', function() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    // Cambiar estilo al hacer scroll
    if (scrollTop > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
    
    // Ocultar/mostrar navbar al hacer scroll (solo desktop)
    if (window.innerWidth >= 992) {
      if (scrollTop > lastScrollTop && scrollTop > 100) {
        // Scrolling down
        navbar.classList.add('hide-menu');
      } else {
        // Scrolling up
        navbar.classList.remove('hide-menu');
      }
    }
    
    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
  });
}

/**
 * 2. SCROLL ANIMATIONS
 * Revelar elementos al hacer scroll
 */
function initScrollAnimations() {
  const reveals = document.querySelectorAll('.reveal');
  
  if (reveals.length === 0) return;
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  });
  
  reveals.forEach(reveal => {
    observer.observe(reveal);
  });
}

/**
 * 3. SMOOTH SCROLL
 * Scroll suave para enlaces ancla
 */
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      
      if (href === '#' || href === '') return;
      
      const target = document.querySelector(href);
      
      if (target) {
        e.preventDefault();
        
        const offsetTop = target.offsetTop - 80; // Ajuste por navbar
        
        window.scrollTo({
          top: offsetTop,
          behavior: 'smooth'
        });
        
        // Cerrar offcanvas si está abierto
        const offcanvasElements = document.querySelectorAll('.offcanvas.show');
        offcanvasElements.forEach(el => {
          const bsOffcanvas = bootstrap.Offcanvas.getInstance(el);
          if (bsOffcanvas) {
            bsOffcanvas.hide();
          }
        });
      }
    });
  });
}

/**
 * 4. SCROLL TO TOP BUTTON
 * Botón para volver arriba
 */
function initScrollToTop() {
  // Crear botón si no existe
  let scrollTopBtn = document.querySelector('.scroll-to-top');
  
  if (!scrollTopBtn) {
    scrollTopBtn = document.createElement('button');
    scrollTopBtn.className = 'scroll-to-top';
    scrollTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollTopBtn.setAttribute('aria-label', 'Volver arriba');
    document.body.appendChild(scrollTopBtn);
    
    // Agregar estilos inline si no existen en CSS
    const style = document.createElement('style');
    style.textContent = `
      .scroll-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: #ccff00;
        color: #000;
        border: none;
        border-radius: 50%;
        font-size: 20px;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        box-shadow: 0 4px 15px rgba(204, 255, 0, 0.4);
      }
      
      .scroll-to-top.visible {
        opacity: 1;
        visibility: visible;
      }
      
      .scroll-to-top:hover {
        background: #a8d400;
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(204, 255, 0, 0.6);
      }
      
      @media (max-width: 768px) {
        .scroll-to-top {
          width: 45px;
          height: 45px;
          bottom: 20px;
          right: 20px;
          font-size: 18px;
        }
      }
    `;
    document.head.appendChild(style);
  }
  
  // Mostrar/ocultar botón
  window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
      scrollTopBtn.classList.add('visible');
    } else {
      scrollTopBtn.classList.remove('visible');
    }
  });
  
  // Click event
  scrollTopBtn.addEventListener('click', function() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });
}

/**
 * 5. LAZY LOAD IMAGES
 * Carga diferida de imágenes
 */
function initLazyLoad() {
  const images = document.querySelectorAll('img[data-src]');
  
  if (images.length === 0) return;
  
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.removeAttribute('data-src');
        observer.unobserve(img);
      }
    });
  });
  
  images.forEach(img => imageObserver.observe(img));
}

/**
 * 6. ANIMATED COUNTERS
 * Contadores animados para números
 */
function initCounters() {
  const counters = document.querySelectorAll('[data-counter]');
  
  if (counters.length === 0) return;
  
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const counter = entry.target;
        const target = parseInt(counter.dataset.counter);
        const duration = 2000; // 2 segundos
        const increment = target / (duration / 16); // 60fps
        
        let current = 0;
        
        const updateCounter = () => {
          current += increment;
          
          if (current < target) {
            counter.textContent = Math.floor(current);
            requestAnimationFrame(updateCounter);
          } else {
            counter.textContent = target;
          }
        };
        
        updateCounter();
        counterObserver.unobserve(counter);
      }
    });
  }, { threshold: 0.5 });
  
  counters.forEach(counter => counterObserver.observe(counter));
}

/**
 * 7. CARD TILT EFFECT
 * Efecto 3D en cards al mover el mouse
 */
function initTiltEffect() {
  const cards = document.querySelectorAll('.service-wrapper, .auditoria-card');
  
  cards.forEach(card => {
    card.addEventListener('mousemove', function(e) {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      const rotateX = (y - centerY) / 10;
      const rotateY = (centerX - x) / 10;
      
      card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
    });
    
    card.addEventListener('mouseleave', function() {
      card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale3d(1, 1, 1)';
    });
  });
}

/**
 * 8. RIPPLE EFFECT ON BUTTONS
 * Efecto ripple Material Design en botones
 */
function initRippleEffect() {
  const buttons = document.querySelectorAll('.btn-contacto, .btn-consulta, .btn-cumple, .contact-highlight');
  
  buttons.forEach(button => {
    button.addEventListener('click', function(e) {
      const ripple = document.createElement('span');
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;
      
      ripple.style.width = ripple.style.height = size + 'px';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';
      ripple.classList.add('ripple-effect');
      
      this.appendChild(ripple);
      
      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
  });
  
  // Agregar estilos CSS para el efecto
  if (!document.querySelector('#ripple-styles')) {
    const style = document.createElement('style');
    style.id = 'ripple-styles';
    style.textContent = `
      .btn-contacto, .btn-consulta, .btn-cumple, .contact-highlight {
        position: relative;
        overflow: hidden;
      }
      
      .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
      }
      
      @keyframes ripple-animation {
        to {
          transform: scale(4);
          opacity: 0;
        }
      }
    `;
    document.head.appendChild(style);
  }
}

/**
 * 9. PARALLAX EFFECT
 * Efecto parallax en secciones con background
 */
function initParallax() {
  const parallaxSections = document.querySelectorAll('.casos-exito-section, .auditoria-section, .ley-datos-section');
  
  if (parallaxSections.length === 0) return;
  
  window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    
    parallaxSections.forEach(section => {
      const rect = section.getBoundingClientRect();
      const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
      
      if (isVisible) {
        const yPos = -(scrolled * 0.3);
        section.style.backgroundPosition = `center ${yPos}px`;
      }
    });
  });
}

/**
 * 10. SCROLL PROGRESS BAR
 * Barra de progreso de scroll
 */
function initProgressBar() {
  // Crear barra si no existe
  let progressBar = document.querySelector('.scroll-progress');
  
  if (!progressBar) {
    progressBar = document.createElement('div');
    progressBar.className = 'scroll-progress';
    document.body.appendChild(progressBar);
    
    // Agregar estilos
    const style = document.createElement('style');
    style.textContent = `
      .scroll-progress {
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 4px;
        background: linear-gradient(90deg, #ccff00, #a8d400);
        z-index: 9999;
        transition: width 0.1s ease;
        box-shadow: 0 2px 10px rgba(204, 255, 0, 0.5);
      }
    `;
    document.head.appendChild(style);
  }
  
  // Actualizar progreso
  window.addEventListener('scroll', function() {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    
    progressBar.style.width = scrolled + '%';
  });
}

/**
 * UTILITY: Detectar si es dispositivo móvil
 */
function isMobile() {
  return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

/**
 * UTILITY: Debounce function
 */
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Optimizar scroll events con debounce
if (window.addEventListener) {
  const scrollHandler = debounce(function() {
    // Eventos optimizados
  }, 10);
  
  window.addEventListener('scroll', scrollHandler, { passive: true });
}

console.log('✅ Modern Effects JS loaded successfully!');
