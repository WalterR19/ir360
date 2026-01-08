/**
 * Meta Pixel - Configuración para Conversión de Leads
 * IR360 Soluciones de Ciberseguridad
 * Objetivo: Captar clientes y convertir en leads calificados
 */

// Pixel ID: 1132638565607970
window.META_PIXEL_ID = '1132638565607970';

// Inicializar Meta Pixel
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');

fbq('init', window.META_PIXEL_ID);
fbq('track', 'PageView');

/**
 * EVENTOS DE CONVERSIÓN - Lead Calificado
 */

// 1. LEAD - Cuando envía formulario de contacto
window.trackLead = function(formData) {
    fbq('track', 'Lead', {
        content_name: 'Formulario de Contacto',
        content_category: 'Consulta Ciberseguridad',
        value: 100.00, // Valor estimado de un lead
        currency: 'CLP'
    });
    console.log('✅ Meta Pixel: Lead tracked');
};

// 2. CONTACT - Clicks en teléfono/email (alta intención)
document.addEventListener('DOMContentLoaded', function() {
    
    // Clicks en teléfono
    const phoneLinks = document.querySelectorAll('a[href^="tel:"]');
    phoneLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            fbq('track', 'Contact', {
                content_name: 'Click Teléfono',
                content_category: 'Contacto Directo'
            });
            console.log('📞 Meta Pixel: Contact (phone) tracked');
        });
    });
    
    // Clicks en email
    const emailLinks = document.querySelectorAll('a[href^="mailto:"]');
    emailLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            fbq('track', 'Contact', {
                content_name: 'Click Email',
                content_category: 'Contacto Directo'
            });
            console.log('📧 Meta Pixel: Contact (email) tracked');
        });
    });
    
    // Clicks en WhatsApp
    const whatsappLinks = document.querySelectorAll('a[href*="wa.me"], a[href*="whatsapp"], a[href*="api.whatsapp"]');
    whatsappLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            fbq('track', 'Contact', {
                content_name: 'Click WhatsApp',
                content_category: 'Contacto Directo'
            });
            console.log('💬 Meta Pixel: Contact (whatsapp) tracked');
        });
    });
});

// 3. VIEW_CONTENT - Páginas de servicios (interés específico)
if (window.location.pathname.includes('service')) {
    fbq('track', 'ViewContent', {
        content_name: document.title,
        content_category: 'Servicios Ciberseguridad',
        content_type: 'service_page'
    });
    console.log('👁️ Meta Pixel: ViewContent (service) tracked');
}

// 4. SCHEDULE - Agendar reunión/demo (conversión alta)
window.trackSchedule = function() {
    fbq('track', 'Schedule', {
        content_name: 'Agendar Demo/Reunión',
        content_category: 'Conversión Alta'
    });
    console.log('📅 Meta Pixel: Schedule tracked');
};

// 5. COMPLETE_REGISTRATION - Descarga de recursos/cotización
window.trackCompleteRegistration = function(resourceName) {
    fbq('track', 'CompleteRegistration', {
        content_name: resourceName || 'Descarga de Recurso',
        content_category: 'Lead Nurturing'
    });
    console.log('📥 Meta Pixel: CompleteRegistration tracked');
};

// 6. SUBMIT_APPLICATION - Solicitud de cotización detallada
window.trackSubmitApplication = function() {
    fbq('track', 'SubmitApplication', {
        content_name: 'Solicitud de Cotización',
        content_category: 'Lead Calificado Alto',
        value: 500.00,
        currency: 'CLP'
    });
    console.log('💼 Meta Pixel: SubmitApplication tracked');
};

/**
 * EVENTOS AUTOMÁTICOS DE ENGAGEMENT
 */

// Scroll profundo (interés alto)
let scrollTracked = false;
window.addEventListener('scroll', function() {
    if (!scrollTracked) {
        const scrollPercent = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
        if (scrollPercent > 75) {
            fbq('trackCustom', 'DeepScroll', {
                scroll_depth: '75%',
                page: window.location.pathname
            });
            scrollTracked = true;
            console.log('📜 Meta Pixel: Deep scroll tracked');
        }
    }
});

// Tiempo en página (engagement)
let timeTracked = false;
setTimeout(function() {
    if (!timeTracked) {
        fbq('trackCustom', 'TimeOnPage', {
            time_seconds: 30,
            page: window.location.pathname
        });
        timeTracked = true;
        console.log('⏱️ Meta Pixel: 30 seconds on page tracked');
    }
}, 30000);

console.log('🚀 Meta Pixel inicializado - Modo: Conversión de Leads');
