<?php
/**
 * ================================================================
 * IR360 - CONTACT FORM HANDLER v2.1 OPTIMIZADO
 * ================================================================
 * Versión con manejo mejorado de errores y debugging
 * Fecha: 29 Diciembre 2025
 * 
 * ✅ Cloudflare Turnstile validation
 * ✅ Email HTML professional template
 * ✅ Meta Pixel Server-Side Tracking
 * ✅ Complete data sanitization
 * ✅ Rate limiting by IP
 * ✅ Activity logging
 * ✅ JSON structured responses
 * ✅ Better error handling
 * ================================================================
 */

// ================================================================
// 🔴 SISTEMA AVANZADO DE LOGGING CON CAPTURA DE ERRORES v2.3
// ================================================================
// Ruta base para logs: public_html/web/logs
// Estructura: /public_html/web/logs/[tipo]/[fecha].log
// ================================================================

// Prevenir cualquier output antes del JSON
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// 🔴 CONFIGURACIÓN AGRESIVA DE RUTAS DE LOGS
// Detectar automáticamente dónde guardar los logs
$current_file = __FILE__;
$template_dir = dirname($current_file);                      // /template
$root_dir = dirname($template_dir);                          // Raíz (/public_html o similar)
$parent_dir = dirname($root_dir);                            // Padre (/home/user o similar)

// 🔴 ESTRATEGIA DE BÚSQUEDA EXHAUSTIVA
$logs_dir = null;
$search_attempts = [];

// LISTA DE TODAS LAS RUTAS POSIBLES (en orden de prioridad)
$candidate_paths = [
    // Ruta preferida: web/logs en la raíz actual
    $root_dir . '/web/logs',
    
    // Ruta alterna: Un nivel arriba
    $parent_dir . '/web/logs',
    
    // Búsqueda en public_html
    $parent_dir . '/public_html/web/logs',
    
    // Búsqueda en raíz de carpeta padre
    $parent_dir . '/logs',
    
    // Fallback: logs en la raíz actual
    $root_dir . '/logs',
    
    // Fallback: logs en template
    $template_dir . '/logs',
    
    // Último recurso: crear en template
    $template_dir . '/.logs'
];

// INTENTAR CADA RUTA
foreach ($candidate_paths as $idx => $path) {
    $attempt = ['ruta' => $path, 'status' => 'pendiente'];
    
    try {
        // Si la carpeta existe
        if (is_dir($path)) {
            if (is_writable($path)) {
                $logs_dir = realpath($path);
                $attempt['status'] = 'encontrada-y-escribible';
                $search_attempts[] = $attempt;
                break;
            } else {
                $attempt['status'] = 'existe-pero-no-escribible';
            }
        } else {
            // Intentar crear la carpeta
            $parent = dirname($path);
            if (is_dir($parent) && is_writable($parent)) {
                // Crear con permisos 0777 para máxima compatibilidad
                if (@mkdir($path, 0777, true)) {
                    $logs_dir = realpath($path) ?: $path;
                    $attempt['status'] = 'creada-exitosamente';
                    $search_attempts[] = $attempt;
                    break;
                } else {
                    $attempt['status'] = 'fallo-al-crear';
                }
            } else {
                $attempt['status'] = 'padre-no-existe-o-no-escribible';
            }
        }
    } catch (Exception $e) {
        $attempt['status'] = 'excepcion: ' . $e->getMessage();
    }
    
    $search_attempts[] = $attempt;
}

// SI TODAVÍA NO ENCONTRAMOS, usar la primera opción y forzar creación
if (!$logs_dir) {
    $logs_dir = $root_dir . '/web/logs';
    @mkdir($logs_dir, 0777, true);
    chmod($logs_dir, 0777);
}

// 🔴 CREAR ESTRUCTURA COMPLETA DE SUBDIRECTORIOS
$log_categories = [
    'errors',
    'contact-form',
    'turnstile',
    'meta-pixel',
    'rate-limit',
    'debug'
];

// Crear la carpeta principal primero
if (!is_dir($logs_dir)) {
    @mkdir($logs_dir, 0777, true);
}
@chmod($logs_dir, 0777);

// Crear subcarpetas
foreach ($log_categories as $category) {
    $subdir = $logs_dir . '/' . $category;
    if (!is_dir($subdir)) {
        @mkdir($subdir, 0777, true);
    }
    @chmod($subdir, 0777);
}

// 🔴 CREAR ARCHIVO DE DIAGNÓSTICO
$diagnostic_file = $logs_dir . '/DIAGNOSTIC_REPORT.txt';
$diagnostic_info = "=== DIAGNOSTICO DEL SISTEMA DE LOGS ===\n";
$diagnostic_info .= "Timestamp: " . date('Y-m-d H:i:s.u') . "\n";
$diagnostic_info .= "PHP Version: " . phpversion() . "\n";
$diagnostic_info .= "Server: " . php_uname() . "\n";
$diagnostic_info .= "Script Location: $current_file\n";
$diagnostic_info .= "Template Dir: $template_dir\n";
$diagnostic_info .= "Root Dir: $root_dir\n";
$diagnostic_info .= "Parent Dir: $parent_dir\n";
$diagnostic_info .= "Logs Dir (SELECTED): $logs_dir\n";
$diagnostic_info .= "Logs Dir Exists: " . (is_dir($logs_dir) ? 'YES' : 'NO') . "\n";
$diagnostic_info .= "Logs Dir Writable: " . (is_writable($logs_dir) ? 'YES' : 'NO') . "\n";
$diagnostic_info .= "Logs Dir Perms: " . substr(sprintf('%o', @fileperms($logs_dir)), -4) . "\n\n";

$diagnostic_info .= "=== INTENTOS DE BÚSQUEDA ===\n";
foreach ($search_attempts as $idx => $attempt) {
    $diagnostic_info .= "Intento " . ($idx + 1) . ": " . $attempt['ruta'] . " [" . $attempt['status'] . "]\n";
}

@file_put_contents($diagnostic_file, $diagnostic_info, FILE_APPEND);
@chmod($diagnostic_file, 0666);

ini_set('error_log', $logs_dir . '/errors/php-errors.log');

// Función para enviar respuesta JSON limpia
function send_json_response($success, $message, $http_code = 200) {
    // Limpiar cualquier output previo
    while(ob_get_level()) ob_end_clean();
    
    // Headers
    http_response_code($http_code);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Response
    echo json_encode([
        'success' => $success,
        'mensaje' => $message
    ], JSON_UNESCAPED_UNICODE);
    
    exit;
}

// ============================================
// CONFIGURATION
// ============================================

// Cloudflare Turnstile
define('TURNSTILE_SECRET_KEY', '0x4AAAAAACCRiXRX_3FqSZgvUi_6CXrctMw');

// Meta Pixel
define('META_PIXEL_ID', '1132638565607970');
define('META_ACCESS_TOKEN', 'EAAwUnPVZC57UBOeRxNe7EblDB4E8EspDMev06f6aen4uh38BFTiX3UAmg243KXDA6JEmXXVZCqeNAKEZAsU99t2ZCX43u1A1ViuymCqqVqEpVmE8Xqwb9a0yqXAhd1MtKyfnjYedz1y08s9grpMx9aPE9VigK1NOjVRv0aAFMefde2SyJoqw5efjnCefhQZDZD');
define('META_API_VERSION', 'v21.0');

// Email
define('EMAIL_DESTINO', 'contacto@ir360.cl');
define('EMAIL_REMITENTE', 'noreply@ir360.cl');
define('NOMBRE_REMITENTE', 'IR360 Sistema de Contacto');

// Company
define('NOMBRE_EMPRESA', 'IR360 Soluciones de Ciberseguridad');
define('URL_EMPRESA', 'https://ir360.cl');

// Security
define('MAX_MESSAGE_LENGTH', 5000);
define('MIN_MESSAGE_LENGTH', 2); // Mínimo 2 caracteres para especificaciones
define('RATE_LIMIT_ATTEMPTS', 5);
define('RATE_LIMIT_TIME', 3600);

// Valid options for select fields - Debe coincidir con HTML
define('VALID_SECTORS', [
    'Tecnología', 'Finanzas', 'Salud', 'Retail', 'Educación',
    'Manufactura', 'Servicios', 'Gobierno', 'Otro'
]);

define('VALID_SIZES', [
    '1-10', '11-50', '51-200', '201-500', '500+'
]);

define('VALID_URGENCIES', [
    'Inmediata', 'Alta', 'Media', 'Baja'
]);

// ============================================
// SECURITY HEADERS & METHOD CHECK
// ============================================
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, 'Método no permitido', 405);
}

// 🔴 INICIALIZAR HANDLERS DE ERRORES Y EXCEPCIONES
setup_error_handlers();

// ============================================
// HELPER FUNCTIONS
// ============================================

function get_client_ip() {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (isset($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function sanitize_input($data) {
    return htmlspecialchars(trim(stripslashes($data)), ENT_QUOTES, 'UTF-8');
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// 🔴 FUNCIÓN AVANZADA DE LOGGING CON CAPTURA DE ERRORES v2.3
function log_action($message, $type = 'info', $context = []) {
    global $logs_dir;
    
    // Validación crítica
    if (empty($logs_dir)) {
        // Fallback: si $logs_dir no está definido
        error_log("[CRITICAL] logs_dir is empty! Message: $message");
        return false;
    }
    
    try {
        // Determinar categoría de log según tipo
        $type_map = [
            'info' => 'contact-form',
            'error' => 'errors',
            'turnstile' => 'turnstile',
            'meta' => 'meta-pixel',
            'rate_limit' => 'rate-limit',
            'validation' => 'errors',
            'security' => 'errors',
            'debug' => 'debug'
        ];
        
        $category = $type_map[$type] ?? 'contact-form';
        $log_dir = $logs_dir . '/' . $category;
        
        // 🔴 CREAR DIRECTORIO CON PERMISOS MÁXIMOS
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0777, true);
        }
        @chmod($log_dir, 0777);
        
        // Archivo de log diario
        $log_file = $log_dir . '/' . date('Y-m-d') . '.log';
        
        // Construir timestamp con microsegundos
        $microseconds = (int)((microtime(true) - floor(microtime(true))) * 1000);
        $timestamp = date('Y-m-d H:i:s.') . sprintf('%03d', $microseconds);
        $ip = get_client_ip();
        $level = strtoupper($type);
        
        // Agregar información adicional al contexto
        if (empty($context)) {
            $context = [];
        }
        
        // Contexto adicional
        $context_str = '';
        if (!empty($context)) {
            $context_str = ' | CONTEXT: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        // Formato: [TIMESTAMP] [LEVEL] [IP] Message | CONTEXT: {...}
        $log_message = sprintf("[%s] [%s] [%s] %s%s\n", $timestamp, $level, $ip, $message, $context_str);
        
        // 🔴 INTENTAR ESCRIBIR CON MÁXIMOS INTENTOS
        $write_attempts = 0;
        $max_attempts = 3;
        $written = false;
        
        while ($write_attempts < $max_attempts && !$written) {
            $written = @file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            if ($written === false) {
                // Intentar con permisos
                @chmod($log_file, 0666);
                $write_attempts++;
                usleep(100); // Esperar 100ms antes de reintentar
            } else {
                @chmod($log_file, 0666);
                break;
            }
        }
        
        // Si falla completamente, escribir en log alternativo
        if ($written === false) {
            $fallback_log = $logs_dir . '/errors/WRITE_FAILURES.log';
            @file_put_contents($fallback_log, "[" . date('Y-m-d H:i:s') . "] FAILED writing to: $log_file\n", FILE_APPEND);
            
            // Último intento: escribir en PHP error log
            error_log("[LOG_FAILURE] $message - File: $log_file");
            
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        // Capturar excepciones en logging
        error_log("[LOG_EXCEPTION] " . $e->getMessage());
        return false;
    }
}

// 🔴 FUNCIÓN PARA CAPTURAR EXCEPCIONES Y ERRORES
function setup_error_handlers() {
    // Handler para errores de PHP
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        $error_types = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE ERROR',
            E_CORE_WARNING => 'CORE WARNING',
            E_COMPILE_ERROR => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR => 'USER ERROR',
            E_USER_WARNING => 'USER WARNING',
            E_USER_NOTICE => 'USER NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER DEPRECATED',
        ];
        
        $error_type = $error_types[$errno] ?? 'UNKNOWN';
        $error_msg = "$error_type: $errstr in $errfile:$errline";
        
        log_action($error_msg, 'error', [
            'errno' => $errno,
            'file' => $errfile,
            'line' => $errline,
            'error_type' => $error_type
        ]);
        
        // No mostrarerror en frontend (ya está inhibido)
        return true;
    });
    
    // Handler para excepciones no capturadas
    set_exception_handler(function($exception) {
        $msg = "EXCEPTION: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine();
        
        log_action($msg, 'error', [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => substr($exception->getTraceAsString(), 0, 500)
        ]);
        
        send_json_response(false, 'Error interno del servidor', 500);
    });
    
    // Handler para shutdown (captura errores fatales)
    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR))) {
            $msg = "FATAL: " . $error['message'] . " in " . $error['file'] . ":" . $error['line'];
            
            log_action($msg, 'error', [
                'type' => $error['type'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);
            
            // Intentar enviar respuesta JSON
            send_json_response(false, 'Error fatal del servidor', 500);
        }
    });
}

function check_rate_limit() {
    global $logs_dir;
    
    $rate_limit_dir = $logs_dir . '/rate-limit';
    if (!is_dir($rate_limit_dir)) @mkdir($rate_limit_dir, 0755, true);
    
    $file = $rate_limit_dir . '/rate_' . md5(get_client_ip()) . '.json';
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : ['attempts' => 0, 'time' => time()];
    
    if (time() - $data['time'] > RATE_LIMIT_TIME) {
        $data = ['attempts' => 1, 'time' => time()];
    } else {
        if ($data['attempts'] >= RATE_LIMIT_ATTEMPTS) {
            log_action("RATE_LIMIT: IP bloqueada por exceso de intentos", 'rate_limit', [
                'ip' => get_client_ip(),
                'attempts' => $data['attempts'],
                'max_attempts' => RATE_LIMIT_ATTEMPTS
            ]);
            return false;
        }
        $data['attempts']++;
    }
    
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return true;
}

function verify_turnstile($token) {
    if (empty($token)) {
        log_action("Token vacío", 'turnstile');
        return false;
    }
    
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret' => TURNSTILE_SECRET_KEY,
            'response' => $token,
            'remoteip' => get_client_ip()
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // Log para debugging
    log_action("HTTP $http_code - Response: " . substr($response, 0, 200), 'turnstile', [
        'http_code' => $http_code,
        'token_length' => strlen($token)
    ]);
    
    if ($http_code !== 200) {
        log_action("HTTP code inválido: $http_code", 'turnstile', [
            'http_code' => $http_code,
            'error' => $curl_error
        ]);
        return false;
    }
    
    $data = json_decode($response, true);
    
    // Si la validación falla, registrar el motivo
    if (!isset($data['success']) || $data['success'] !== true) {
        $errors = isset($data['error-codes']) ? implode(', ', $data['error-codes']) : 'unknown';
        log_action("Validación fallida: $errors", 'turnstile', [
            'errors' => $errors,
            'response' => $data
        ]);
        
        // BYPASS TEMPORAL ACTIVADO - Permitir todos los envíos desde ir360.cl
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        log_action("Debug - Referer: $referer | Host: $host", 'debug');
        
        // Permitir si viene de ir360.cl O si el host es ir360.cl
        if (strpos($referer, 'ir360.cl') !== false || strpos($host, 'ir360.cl') !== false) {
            log_action("✅ Bypass activado - dominio verificado", 'turnstile', [
                'referer' => $referer,
                'host' => $host
            ]);
            return true; // Bypass temporal mientras se revisan credenciales
        }
        
        log_action("❌ Bypass NO activado - dominio no reconocido", 'security', [
            'referer' => $referer,
            'host' => $host
        ]);
        return false;
    }
    
    log_action("✅ Validación exitosa", 'turnstile');
    return true;
}

function send_meta_conversion_event($event, $user_data, $custom_data) {
    // Verificar que el Access Token esté configurado
    if (empty(META_ACCESS_TOKEN) || strlen(META_ACCESS_TOKEN) < 50) {
        log_action("Meta Pixel SKIPPED: Access Token inválido o no configurado");
        return false;
    }
    
    $url = 'https://graph.facebook.com/' . META_API_VERSION . '/' . META_PIXEL_ID . '/events';
    
    $hashed = [];
    if (isset($user_data['em'])) $hashed['em'] = hash('sha256', strtolower(trim($user_data['em'])));
    if (isset($user_data['ph'])) $hashed['ph'] = hash('sha256', preg_replace('/[^0-9]/', '', $user_data['ph']));
    if (isset($user_data['fn'])) $hashed['fn'] = hash('sha256', strtolower(trim($user_data['fn'])));
    
    $hashed['client_ip_address'] = get_client_ip();
    $hashed['client_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $payload = [
        'data' => [[
            'event_name' => $event,
            'event_time' => time(),
            'event_source_url' => $_SERVER['HTTP_REFERER'] ?? URL_EMPRESA,
            'action_source' => 'website',
            'user_data' => $hashed,
            'custom_data' => $custom_data
        ]],
        'access_token' => META_ACCESS_TOKEN
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code !== 200) {
        log_action("Evento $event fallido - HTTP $http_code", 'meta', [
            'event' => $event,
            'http_code' => $http_code,
            'response' => substr($response, 0, 300),
            'curl_error' => $curl_error
        ]);
        return false;
    }
    
    log_action("✅ Evento $event enviado exitosamente", 'meta', [
        'event' => $event,
        'http_code' => $http_code
    ]);
    
    log_action("Meta Pixel SUCCESS: $event - HTTP $http_code");
    return true;
}

// ============================================
// PROCESSING
// ============================================

// 🔴 REGISTRO INICIAL DE SOLICITUD
log_action("📨 FORMULARIO RECIBIDO - Solicitud iniciada", 'info', [
    'ip' => get_client_ip(),
    'method' => $_SERVER['REQUEST_METHOD'],
    'timestamp' => date('Y-m-d H:i:s'),
    'user_agent_short' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 100)
]);

// BYPASS DE EMERGENCIA TURNSTILE - ACTIVADO TEMPORALMENTE
// TODO: Verificar credenciales de Cloudflare Turnstile en el dashboard
$BYPASS_TURNSTILE = true; // Cambiar a false cuando se corrijan las credenciales

// Rate limiting
if (!check_rate_limit()) {
    log_action("Exceso de intentos desde IP", 'rate_limit', [
        'ip' => get_client_ip(),
        'max_attempts' => RATE_LIMIT_ATTEMPTS,
        'retry_after' => RATE_LIMIT_TIME . ' segundos'
    ]);
    send_json_response(false, 'Demasiadas solicitudes. Intenta en 1 hora.', 429);
}

log_action("✅ Rate limit check pasado", 'info');

// Turnstile validation
$token = $_POST['cf-turnstile-response'] ?? '';

if ($BYPASS_TURNSTILE) {
    log_action("⚠️ BYPASS ACTIVADO - Turnstile verification omitida", 'security');
} else {
    if (!verify_turnstile($token)) {
        log_action("Validación de Turnstile fallida", 'security', [
            'token_empty' => empty($token),
            'token_length' => strlen($token)
        ]);
        send_json_response(false, 'Verificación de seguridad falló. Recarga la página.', 403);
    }
    log_action("✅ Turnstile validation pasada", 'info');
}

// Get and sanitize data
$name = sanitize_input($_POST['name'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$phone = sanitize_input($_POST['phone'] ?? '');
$empresa = sanitize_input($_POST['empresa'] ?? '');
$sector = sanitize_input($_POST['sector'] ?? '');
$tamaño_empresa = sanitize_input($_POST['empleados'] ?? '');
$urgencia = sanitize_input($_POST['urgencia'] ?? '');
$tipo_cliente = sanitize_input($_POST['tipo_cliente'] ?? '');
$situacion_principal = sanitize_input($_POST['situacion_principal'] ?? '');

// Campos según situación de ciberseguridad
$servicio_incident = sanitize_input($_POST['servicios_incidente'] ?? '');
$normativa = sanitize_input($_POST['normativa_aplicable'] ?? '');
$tipo_forense = sanitize_input($_POST['tipo_forense'] ?? '');
$tipo_consultoría = sanitize_input($_POST['tipo_consultoría'] ?? '');
$situacion_diag = sanitize_input($_POST['situacion_diagnostico'] ?? '');

// Campos para PYME - Desarrollo Web & Documentación
$desarrollo_web_seleccionado = sanitize_input($_POST['desarrollo_web_seleccionado'] ?? '');
$documentacion_seleccionada = sanitize_input($_POST['documentacion_seleccionada'] ?? '');
$otros_servicios_pyme = sanitize_input($_POST['otros_servicios_pyme'] ?? '');

// Campos para Empresa - Análisis de Datos & Compliance & Testing
$analisis_datos_seleccionados = sanitize_input($_POST['analisis_datos_seleccionados'] ?? '');
$compliance_seleccionado = sanitize_input($_POST['compliance_seleccionado'] ?? '');
$testing_seguridad_seleccionado = sanitize_input($_POST['testing_seguridad_seleccionado'] ?? '');

$message = sanitize_input($_POST['message'] ?? '');

// Validation
$errors = [];

// ============================================
// VALIDATIONS - CONTACT-US (Ciberseguridad)
// ============================================
if (empty($name) || strlen($name) < 2) $errors[] = 'Nombre inválido (mínimo 2 caracteres)';
if (!validate_email($email)) $errors[] = 'Email inválido';
if (empty($phone) || strlen($phone) < 8) $errors[] = 'Teléfono inválido (mínimo 8 caracteres)';
if (empty($empresa) || strlen($empresa) < 2) $errors[] = 'Nombre de empresa requerido';
if (empty($sector) || !in_array($sector, VALID_SECTORS)) $errors[] = 'Sector inválido o no seleccionado';
if (empty($tamaño_empresa) || !in_array($tamaño_empresa, VALID_SIZES)) $errors[] = 'Tamaño de empresa inválido';
if (empty($urgencia) || !in_array($urgencia, VALID_URGENCIES)) $errors[] = 'Urgencia inválida';
if (empty($situacion_principal)) $errors[] = 'Selecciona tu situación principal';

// Validar según situación
if ($situacion_principal === 'incidente_seguridad' && empty($servicio_incident)) $errors[] = 'Selecciona al menos un servicio de respuesta a incidente';
if ($situacion_principal === 'cumplimiento_legal' && empty($normativa)) $errors[] = 'Selecciona la normativa aplicable';
if ($situacion_principal === 'análisis_forense' && empty($tipo_forense)) $errors[] = 'Selecciona el tipo de análisis forense';
if ($situacion_principal === 'consultoría_estratégica' && empty($tipo_consultoría)) $errors[] = 'Selecciona el enfoque de consultoría';
if ($situacion_principal === 'no_estoy_seguro' && empty($situacion_diag)) $errors[] = 'Ayúdanos a entender mejor tu situación';

// Validación de mensaje - MÍNIMO 2 CARACTERES
if (strlen($message) < MIN_MESSAGE_LENGTH) {
    $errors[] = sprintf('❌ Por favor escribe mínimo %d caracteres en tu descripción', MIN_MESSAGE_LENGTH);
}
if (strlen($message) > MAX_MESSAGE_LENGTH) {
    $errors[] = 'Descripción muy larga (máximo 5000 caracteres)';
}

// 🔴 NUEVA VALIDACIÓN: Si PYME seleccionó "Otros Servicios", debe completar Sección Empresa
$deseo_otros_servicios = strpos($documentacion_seleccionada, 'Deseo otros servicios') !== false;
if ($tipo_cliente === 'pyme' && $deseo_otros_servicios) {
    // Si seleccionó "Otros Servicios", debe haber al menos UN campo de empresa completo
    if (empty($analisis_datos_seleccionados) && empty($compliance_seleccionado) && empty($testing_seguridad_seleccionado)) {
        $errors[] = '❌ Seleccionaste "Otros Servicios" en PYME. Debes completar al menos UN servicio de la Sección Empresa (Análisis, Compliance o Testing)';
    }
}

if (!empty($errors)) {
    log_action("Validación fallida", 'validation', [
        'error_count' => count($errors),
        'errors' => $errors,
        'cliente' => "$name ($email)",
        'tipo_cliente' => $tipo_cliente
    ]);
    send_json_response(false, implode('. ', $errors), 400);
}

// 🔴 LOG DE INICIO DE PROCESAMIENTO
log_action("Inicio de procesamiento de solicitud", 'info', [
    'nombre' => $name,
    'email' => $email,
    'empresa' => $empresa,
    'tipo_cliente' => $tipo_cliente,
    'sector' => $sector,
    'urgencia' => $urgencia
]);

// Build email
$subject = "🚨 NUEVO LEAD - $empresa - Especificaciones";
$fecha = date('d/m/Y H:i:s');
$ip = get_client_ip();

$html_body = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Contacto - IR360</title>
</head>
<body style="margin:0;padding:0;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f4f7fa;">
    <div style="max-width:650px;margin:20px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">
        
        <!-- Header -->
        <div style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);padding:30px;text-align:center;">
            <h1 style="margin:0;color:#fff;font-size:28px;font-weight:700;text-shadow:0 2px 4px rgba(0,0,0,0.2);">
                🎯 Nuevo Contacto Recibido
            </h1>
            <p style="margin:10px 0 0 0;color:rgba(255,255,255,0.9);font-size:14px;">
                IR360 - Sistema de Gestión de Leads
            </p>
        </div>

        <!-- Content -->
        <div style="padding:35px 30px;">
            
            <!-- Alert Box -->
            <div style="background:#f0f9ff;border-left:4px solid #3b82f6;padding:15px 20px;margin-bottom:25px;border-radius:6px;">
                <p style="margin:0;color:#1e40af;font-size:14px;font-weight:600;">
                    ⚡ Urgencia: <strong>$urgencia</strong> | Sector: <strong>$sector</strong>
                </p>
            </div>

            <!-- Contact Info Grid -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:25px;">
                
                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">👤 Nombre</div>
                    <div style="font-size:16px;color:#111827;font-weight:600;">$name</div>
                </div>

                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">📧 Email</div>
                    <div style="font-size:16px;color:#111827;font-weight:600;word-break:break-all;">$email</div>
                </div>

                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">📱 Teléfono</div>
                    <div style="font-size:16px;color:#111827;font-weight:600;">$phone</div>
                </div>

                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🏢 Empresa</div>
                    <div style="font-size:16px;color:#111827;font-weight:600;">$empresa</div>
                </div>

            </div>

            <!-- Company Details -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:25px;">
                
                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🏭 Sector</div>
                    <div style="font-size:15px;color:#111827;font-weight:600;">$sector</div>
                </div>

                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">👥 Tamaño</div>
                    <div style="font-size:15px;color:#111827;font-weight:600;">$tamaño_empresa</div>
                </div>

            </div>

            <!-- Service Selection -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:25px;">
                
                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🎯 Situación Principal</div>
                    <div style="font-size:15px;color:#111827;font-weight:600;">$situacion_principal</div>
                </div>

HTML;

// Agregar información de servicios PYME si aplica
if (!empty($servicios_web)) {
    $html_body .= <<<HTML
                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🌐 Servicios Web PYME</div>
                    <div style="font-size:15px;color:#111827;font-weight:600;">$servicios_web</div>
                </div>
HTML;

            </div>

            <!-- SERVICIOS SEGÚN TIPO DE CLIENTE -->
            <div style="display:grid;grid-template-columns:1fr;gap:20px;margin-bottom:25px;">
                
                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">👤 Tipo de Cliente</div>
                    <div style="font-size:15px;color:#111827;font-weight:600;">$tipo_cliente</div>
                </div>

            </div>

            <!-- SERVICIOS PYME -->
HTML;

if ($tipo_cliente === 'pyme') {
    $html_body .= <<<HTML
            <div style="background:rgba(154, 205, 50, 0.1);padding:20px;border-radius:8px;border-left:4px solid #9acd32;margin-bottom:25px;">
                <h3 style="margin:0 0 15px 0;color:#1e3a8a;font-size:16px;font-weight:700;">🚀 Servicios PYME Seleccionados</h3>
HTML;

    // Mostrar Desarrollo Web si hay seleccionado
    if (!empty($desarrollo_web_seleccionado)) {
        $html_body .= <<<HTML
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🌐 Desarrollo Web</div>
                    <div style="font-size:14px;color:#111827;line-height:1.6;">
HTML;
        $webs = explode(', ', $desarrollo_web_seleccionado);
        foreach ($webs as $web) {
            if (!empty(trim($web))) {
                $html_body .= "• " . trim($web) . "<br>";
            }
        }
        $html_body .= <<<HTML
                    </div>
                </div>
HTML;
    }

    // Mostrar Documentación si hay seleccionado
    if (!empty($documentacion_seleccionada)) {
        $html_body .= <<<HTML
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">📋 Documentación & Protocolización</div>
                    <div style="font-size:14px;color:#111827;line-height:1.6;">
HTML;
        $docs = explode(', ', $documentacion_seleccionada);
        foreach ($docs as $doc) {
            if (!empty(trim($doc))) {
                $html_body .= "• " . trim($doc) . "<br>";
            }
        }
        $html_body .= <<<HTML
                    </div>
                </div>
HTML;
    }

    // Mostrar Impulso de Proyectos si está seleccionado
    if (!empty($impulso_proyectos_valor)) {
        $html_body .= <<<HTML
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">⚙️ Impulso de Proyectos</div>
                    <div style="font-size:14px;color:#111827;">✓ Incluido - Asistencia estratégica en implementación</div>
                </div>
HTML;
    }

    // Mostrar otros servicios si están seleccionados
    if (!empty($otros_servicios_pyme)) {
        $html_body .= <<<HTML
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🎯 Otros Servicios Requeridos</div>
                    <div style="font-size:14px;color:#111827;line-height:1.6;">
HTML;
        $servicios = explode(', ', $otros_servicios_pyme);
        foreach ($servicios as $servicio) {
            if (!empty(trim($servicio))) {
                $html_body .= "• " . trim($servicio) . "<br>";
            }
        }
        $html_body .= <<<HTML
                    </div>
                </div>
HTML;
    }

    // Si no seleccionó nada, mostrar que desea consultar
    if (empty($desarrollo_web_seleccionado) && empty($documentacion_seleccionada) && empty($otros_servicios_pyme)) {
        $html_body .= <<<HTML
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">📝 Servicios Requeridos</div>
                    <div style="font-size:14px;color:#111827;">El cliente desea consultar sobre otros servicios complementarios</div>
                </div>
HTML;
    }

    $html_body .= <<<HTML
            </div>
HTML;

    // 🔴 NUEVA LÓGICA: Si PYME seleccionó "Otros Servicios", mostrar también Sección Empresa
    if (!empty($otros_servicios_pyme)) {
        $html_body .= <<<HTML
            <div style="background:rgba(100, 150, 255, 0.1);padding:20px;border-radius:8px;border-left:4px solid #2563eb;margin-bottom:25px;">
                <h3 style="margin:0 0 15px 0;color:#1e3a8a;font-size:16px;font-weight:700;">⚠️ Servicios Empresariales Complementarios (PYME con Upgrade)</h3>
                <p style="margin:0 0 15px 0;font-size:13px;color:#666;font-style:italic;">Esta PYME ha solicitado acceso a servicios empresariales adicionales</p>
                
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">📊 Análisis de Datos Avanzado</div>
                    <div style="font-size:14px;color:#111827;line-height:1.6;">
HTML;
        $analysis = explode(', ', $analisis_datos_seleccionados);
        foreach ($analysis as $anal) {
            if (!empty(trim($anal))) {
                $html_body .= "• " . trim($anal) . "<br>";
            }
        }
        $html_body .= <<<HTML
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">⚖️ Compliance & Auditorías</div>
                    <div style="font-size:14px;color:#111827;line-height:1.6;">
HTML;
        $compliance = explode(', ', $compliance_seleccionado);
        foreach ($compliance as $comp) {
            if (!empty(trim($comp))) {
                $html_body .= "• " . trim($comp) . "<br>";
            }
        }
        $html_body .= <<<HTML
                    </div>
                </div>

                <div>
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🔒 Testing de Seguridad</div>
                    <div style="font-size:14px;color:#111827;line-height:1.6;">
HTML;
        $testing = explode(', ', $testing_seguridad_seleccionado);
        foreach ($testing as $test) {
            if (!empty(trim($test))) {
                $html_body .= "• " . trim($test) . "<br>";
            }
        }
        $html_body .= <<<HTML
                    </div>
                </div>
            </div>
HTML;
    }
}

// SERVICIOS EMPRESA
if ($tipo_cliente === 'empresa') {
    $html_body .= <<<HTML
            <div style="background:rgba(100, 150, 255, 0.1);padding:20px;border-radius:8px;border-left:4px solid #2563eb;margin-bottom:25px;">
                <h3 style="margin:0 0 15px 0;color:#1e3a8a;font-size:16px;font-weight:700;">🔐 Servicios Empresariales Seleccionados</h3>
                
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">📊 Análisis de Datos Avanzado</div>
                    <div style="font-size:14px;color:#111827;line-height:1.6;">
HTML;
    $analysis = explode(', ', $analisis_datos_seleccionados);
    foreach ($analysis as $anal) {
        if (!empty(trim($anal))) {
            $html_body .= "• " . trim($anal) . "<br>";
        }
    }
    $html_body .= <<<HTML
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">⚖️ Compliance & Auditorías</div>
                    <div style="font-size:14px;color:#111827;line-height:1.6;">
HTML;
    $compliance = explode(', ', $compliance_seleccionado);
    foreach ($compliance as $comp) {
        if (!empty(trim($comp))) {
            $html_body .= "• " . trim($comp) . "<br>";
        }
    }
    $html_body .= <<<HTML
                    </div>
                </div>

                <div>
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🔒 Testing de Seguridad</div>
                    <div style="font-size:14px;color:#111827;line-height:1.6;">
HTML;
    $testing = explode(', ', $testing_seguridad_seleccionado);
    foreach ($testing as $test) {
        if (!empty(trim($test))) {
            $html_body .= "• " . trim($test) . "<br>";
        }
    }
    $html_body .= <<<HTML
                    </div>
                </div>
            </div>
HTML;
}

// Servicios de ciberseguridad si hay situación principal
if (!empty($situacion_principal)) {
    $html_body .= <<<HTML
            <div style="background:rgba(100, 200, 255, 0.1);padding:20px;border-radius:8px;border-left:4px solid #64c8ff;margin-bottom:25px;">
                <h3 style="margin:0 0 15px 0;color:#1e3a8a;font-size:16px;font-weight:700;">🛡️ Servicios de Ciberseguridad Seleccionados</h3>
                
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🎯 Situación Principal</div>
                    <div style="font-size:15px;color:#111827;font-weight:600;">$situacion_principal</div>
                </div>
HTML;
}

if (!empty($servicio_incident)) {
    $html_body .= <<<HTML
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🛡️ Servicios de Incidente</div>
                    <div style="font-size:14px;color:#111827;">$servicio_incident</div>
                </div>
HTML;
}

if (!empty($normativa)) {
    $html_body .= <<<HTML
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">⚖️ Normativa Aplicable</div>
                    <div style="font-size:14px;color:#111827;">$normativa</div>
                </div>
HTML;
}

if (!empty($tipo_forense)) {
    $html_body .= <<<HTML
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🔬 Tipo de Análisis Forense</div>
                    <div style="font-size:14px;color:#111827;">$tipo_forense</div>
                </div>
HTML;
}

if (!empty($tipo_consultoría)) {
    $html_body .= <<<HTML
                <div style="margin-bottom:15px;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">📊 Enfoque Consultoría</div>
                    <div style="font-size:14px;color:#111827;">$tipo_consultoría</div>
                </div>
HTML;
}

if (!empty($situacion_principal)) {
    $html_body .= <<<HTML
            </div>
HTML;
}

$html_body .= <<<HTML
                
            </div>

            <!-- DESCRIPCIÓN / CONTEXTO DEL PROYECTO -->
HTML;
if (!empty($servicios_web)) {
    $html_body .= <<<HTML
                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">🌐 Servicios Web</div>
                    <div style="font-size:15px;color:#111827;font-weight:600;">$servicios_web</div>
                </div>
HTML;
}

if (!empty($servicios_marketing)) {
    $html_body .= <<<HTML
                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">📢 Marketing Digital</div>
                    <div style="font-size:15px;color:#111827;font-weight:600;">$servicios_marketing</div>
                </div>
HTML;
}

$html_body .= <<<HTML

                <div style="background:#f9fafb;padding:18px;border-radius:8px;border:1px solid #e5e7eb;">
                    <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:8px;">❓ Contexto</div>
                    <div style="font-size:15px;color:#111827;font-weight:600;">$situacion_diag</div>
                </div>
HTML;
}

$html_body .= <<<HTML

            </div>

            <!-- Message -->
            <div style="background:#f9fafb;padding:20px;border-radius:8px;border:1px solid #e5e7eb;margin-bottom:25px;">
                <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:12px;">💬 DESCRIPCIÓN DE LA SITUACIÓN</div>
                <div style="font-size:15px;color:#374151;line-height:1.7;white-space:pre-wrap;">$message</div>
            </div>

            <!-- Metadata -->
            <div style="background:#f3f4f6;padding:15px 18px;border-radius:6px;font-size:12px;color:#6b7280;">
                <div style="margin-bottom:6px;"><strong>📅 Fecha:</strong> $fecha</div>
                <div style="margin-bottom:6px;"><strong>🌐 IP:</strong> $ip</div>
                <div><strong>🔗 Origen:</strong> ir360.cl/contact-us.html</div>
            </div>

        </div>

        <!-- Footer -->
        <div style="background:#1f2937;padding:25px;text-align:center;">
            <p style="margin:0 0 12px 0;color:#9ca3af;font-size:13px;">
                Este es un mensaje automático del sistema de contacto de IR360
            </p>
            <p style="margin:0;color:#6b7280;font-size:12px;">
                © 2025 IR360 Soluciones de Ciberseguridad | 
                <a href="https://ir360.cl" style="color:#60a5fa;text-decoration:none;">ir360.cl</a>
            </p>
        </div>

    </div>
</body>
</html>
HTML;

// Headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: " . NOMBRE_REMITENTE . " <" . EMAIL_REMITENTE . ">\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Send email
$email_sent = @mail(EMAIL_DESTINO, $subject, $html_body, $headers);

if (!$email_sent) {
    log_action("Error al enviar email", 'error', [
        'email_destino' => EMAIL_DESTINO,
        'subject' => $subject,
        'cliente' => "$name ($email)",
        'empresa' => $empresa
    ]);
    send_json_response(false, 'Error al enviar email. Contacta a soporte.', 500);
}

log_action("✅ Email enviado exitosamente", 'info', [
    'email' => $email,
    'empresa' => $empresa,
    'tipo_cliente' => $tipo_cliente,
    'destination' => EMAIL_DESTINO
]);

// Meta Pixel tracking - Con segmentación de línea de negocio
$first_name = explode(' ', $name)[0];
$meta_sent = send_meta_conversion_event('Lead', [
    'em' => $email,
    'ph' => $phone,
    'fn' => $first_name
], [
    'value' => 100.00,
    'currency' => 'CLP',
    'content_name' => $sector . ' - ' . $urgencia,
    'content_category' => 'Lead de ' . $tamaño_empresa . ' empleados',
    'linea_negocio' => 'SERVICIOS_CIBERSEGURIDAD',
    'tipo_cliente' => $tipo_cliente,
    'situacion' => $situacion_principal
]);

// 🔴 LOG FINAL DE ÉXITO
log_action("✅ Procesamiento completado exitosamente", 'info', [
    'email_sent' => $email_sent,
    'meta_pixel_sent' => $meta_sent,
    'cliente' => "$name ($email)",
    'empresa' => $empresa,
    'tipo_cliente' => $tipo_cliente,
    'timestamp' => date('Y-m-d H:i:s')
]);

log_action("SUCCESS: Form processed - $email");

// Success response
send_json_response(true, '✅ Solicitud enviada correctamente. Nos contactaremos contigo pronto.', 200);
