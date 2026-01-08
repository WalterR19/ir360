/**
 * IR360 - Enhanced Form Handler
 * Validación en cliente + envío AJAX
 * No incluir tags <script> ni <style> en este archivo
 */

document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.querySelector('.contact-form');
    
    if (!contactForm) return;
    
    // Interceptar envío del formulario
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleFormSubmit(this);
    });
});

/**
 * Procesar envío del formulario
 */
function handleFormSubmit(form) {
    // Limpiar mensajes previos
    clearMessages(form);
    
    // Mostrar indicador de carga
    showLoading(form, true);
    
    // Obtener datos del formulario
    const formData = new FormData(form);
    
    // Enviar vía AJAX
    fetch('formmail.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        showLoading(form, false);
        
        if (data.success) {
            // Mostrar mensaje de éxito
            showSuccessMessage(form, data.mensaje);
            
            // Limpiar formulario
            form.reset();
            
            // Redirigir si es necesario
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            }
        } else {
            // Mostrar errores
            if (data.errores && Array.isArray(data.errores)) {
                showErrorMessages(form, data.errores);
            } else {
                showErrorMessage(form, data.mensaje || 'Ocurrió un error al enviar el mensaje');
            }
        }
    })
    .catch(error => {
        showLoading(form, false);
        console.error('Error:', error);
        showErrorMessage(form, 'Error de conexión. Por favor, intenta de nuevo.');
    });
}

/**
 * Mostrar mensaje de éxito
 */
function showSuccessMessage(form, mensaje) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        <strong>¡Éxito!</strong> ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    form.insertBefore(alertDiv, form.firstChild);
    
    // Auto-desaparecer después de 5 segundos
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

/**
 * Mostrar mensaje de error
 */
function showErrorMessage(form, mensaje) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        <strong>Error:</strong> ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    form.insertBefore(alertDiv, form.firstChild);
}

/**
 * Mostrar múltiples errores
 */
function showErrorMessages(form, errores) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.setAttribute('role', 'alert');
    
    let htmlErrores = '<strong>Por favor corrige los siguientes errores:</strong><ul style="margin-top: 10px; margin-bottom: 0;">';
    
    errores.forEach(error => {
        htmlErrores += `<li>${error}</li>`;
    });
    
    htmlErrores += '</ul>';
    htmlErrores += '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="position: absolute; top: 10px; right: 10px;"></button>';
    
    alertDiv.innerHTML = htmlErrores;
    form.insertBefore(alertDiv, form.firstChild);
}

/**
 * Limpiar mensajes previos
 */
function clearMessages(form) {
    const alerts = form.querySelectorAll('.alert');
    alerts.forEach(alert => alert.remove());
}

/**
 * Mostrar/ocultar indicador de carga
 */
function showLoading(form, show) {
    let loadingDiv = form.querySelector('.form-loading');
    
    if (show) {
        if (!loadingDiv) {
            loadingDiv = document.createElement('div');
            loadingDiv.className = 'form-loading';
            loadingDiv.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Enviando...</span></div> Enviando...';
            form.insertBefore(loadingDiv, form.firstChild);
        }
        
        // Desactivar botón de envío
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
    } else {
        if (loadingDiv) {
            loadingDiv.remove();
        }
        
        // Reactivar botón
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
        }
    }
}

/**
 * Validación en cliente antes de enviar
 */
function validateFormClient(form) {
    const nombre = form.querySelector('input[name="nombre"]').value.trim();
    const email = form.querySelector('input[name="email"]').value.trim();
    const mensaje = form.querySelector('textarea[name="mensaje"]').value.trim();
    
    if (!nombre || nombre.length < 2) {
        alert('Por favor ingresa un nombre válido');
        return false;
    }
    
    if (!email || !isValidEmail(email)) {
        alert('Por favor ingresa un correo electrónico válido');
        return false;
    }
    
    if (!mensaje || mensaje.length < 10) {
        alert('El mensaje debe tener al menos 10 caracteres');
        return false;
    }
    
    return true;
}

/**
 * Validar formato de email
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}
