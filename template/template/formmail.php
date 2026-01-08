<?php
/**
 * ================================================================
 * IR360 - CONTACT FORM HANDLER v2.0 OPTIMIZADO
 * ================================================================
 * Versión optimizada y unificada - Sin dependencias
 * Fecha: 28 Diciembre 2025
 * 
 * ✅ Cloudflare Turnstile validation
 * ✅ Email HTML professional template
 * ✅ Meta Pixel Server-Side Tracking
 * ✅ Complete data sanitization
 * ✅ Rate limiting by IP
 * ✅ Activity logging
 * ✅ JSON structured responses
 * ================================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php-errors.log');
ob_start();

// ============================================
// CONFIGURATION
// ============================================

// Cloudflare Turnstile
define('TURNSTILE_SECRET_KEY', '0x4AAAAAACCRj1iC8bSsrxnSFf7Bds');

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
define('MIN_MESSAGE_LENGTH', 10);
define('RATE_LIMIT_ATTEMPTS', 5);
define('RATE_LIMIT_TIME', 3600);

// ============================================
// SECURITY HEADERS
// ============================================
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['success' => false, 'mensaje' => 'Método no permitido'], JSON_UNESCAPED_UNICODE));
}

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

function log_action($message) {
    $dir = __DIR__ . '/logs';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $log = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), get_client_ip(), $message);
    @file_put_contents($dir . '/contact-form.log', $log, FILE_APPEND | LOCK_EX);
}

function check_rate_limit() {
    $file = __DIR__ . '/logs/rate_' . md5(get_client_ip()) . '.json';
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : ['attempts' => 0, 'time' => time()];
    
    if (time() - $data['time'] > RATE_LIMIT_TIME) {
        $data = ['attempts' => 1, 'time' => time()];
    } else {
        if ($data['attempts'] >= RATE_LIMIT_ATTEMPTS) {
            return false;
        }
        $data['attempts']++;
    }
    
    @file_put_contents($file, json_encode($data), LOCK_EX);
    return true;
}

function verify_turnstile($token) {
    if (empty($token)) return false;
    
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
    curl_close($ch);
    
    if ($http_code !== 200) return false;
    
    $data = json_decode($response, true);
    return isset($data['success']) && $data['success'] === true;
}

function send_meta_conversion_event($event, $user_data, $custom_data) {
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
    curl_close($ch);
    
    log_action("Meta Pixel: $event - HTTP $http_code");
    return $http_code === 200;
}

// ============================================
// PROCESSING
// ============================================

// Rate limiting
if (!check_rate_limit()) {
    log_action("RATE LIMIT EXCEEDED");
    ob_end_clean();
    http_response_code(429);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['success' => false, 'mensaje' => 'Demasiadas solicitudes. Intenta en 1 hora.'], JSON_UNESCAPED_UNICODE));
}

// Turnstile validation
$token = $_POST['cf-turnstile-response'] ?? '';
if (!verify_turnstile($token)) {
    log_action("TURNSTILE FAILED");
    ob_end_clean();
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['success' => false, 'mensaje' => 'Verificación de seguridad falló. Recarga la página.'], JSON_UNESCAPED_UNICODE));
}

// Get and sanitize data
$name = sanitize_input($_POST['name'] ?? '');
$email = sanitize_input($_POST['email'] ?? '');
$phone = sanitize_input($_POST['phone'] ?? '');
$empresa = sanitize_input($_POST['empresa'] ?? '');
$servicio = sanitize_input($_POST['servicio'] ?? '');
$message = sanitize_input($_POST['message'] ?? '');

// Validation
$errors = [];
if (empty($name) || strlen($name) < 2) $errors[] = 'Nombre inválido';
if (!validate_email($email)) $errors[] = 'Email inválido';
if (empty($phone) || strlen($phone) < 8) $errors[] = 'Teléfono inválido';
if (empty($empresa)) $errors[] = 'Empresa requerida';
if (empty($servicio)) $errors[] = 'Servicio requerido';
if (strlen($message) < MIN_MESSAGE_LENGTH) $errors[] = 'Mensaje muy corto';
if (strlen($message) > MAX_MESSAGE_LENGTH) $errors[] = 'Mensaje muy largo';

if (!empty($errors)) {
    log_action("VALIDATION FAILED: " . implode(', ', $errors));
    ob_end_clean();
    http_response_code(400);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['success' => false, 'mensaje' => implode('. ', $errors)], JSON_UNESCAPED_UNICODE));
}

// Build email
$subject = "🚨 NUEVO LEAD - $empresa - $servicio";
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
                    ⚡ Alta Prioridad - Servicio: <strong>$servicio</strong>
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

            <!-- Service -->
            <div style="background:linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);padding:20px;border-radius:8px;margin-bottom:25px;border:2px solid #fbbf24;">
                <div style="font-size:11px;color:#92400e;text-transform:uppercase;font-weight:700;margin-bottom:8px;">🛠️ SERVICIO SOLICITADO</div>
                <div style="font-size:18px;color:#78350f;font-weight:700;">$servicio</div>
            </div>

            <!-- Message -->
            <div style="background:#f9fafb;padding:20px;border-radius:8px;border:1px solid #e5e7eb;margin-bottom:25px;">
                <div style="font-size:11px;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:12px;">💬 MENSAJE</div>
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
$headers .= "From: $NOMBRE_REMITENTE <$EMAIL_REMITENTE>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Send email
$email_sent = @mail(EMAIL_DESTINO, $subject, $html_body, $headers);

if (!$email_sent) {
    log_action("EMAIL FAILED");
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(['success' => false, 'mensaje' => 'Error al enviar email. Contacta a soporte.'], JSON_UNESCAPED_UNICODE));
}

log_action("EMAIL SENT: $email - $servicio");

// Meta Pixel tracking
$first_name = explode(' ', $name)[0];
send_meta_conversion_event('Lead', [
    'em' => $email,
    'ph' => $phone,
    'fn' => $first_name
], [
    'value' => 100.00,
    'currency' => 'CLP',
    'content_name' => $servicio,
    'content_category' => 'Consulta'
]);

// Success response
ob_end_clean();
http_response_code(200);
header('Content-Type: application/json; charset=UTF-8');
echo json_encode([
    'success' => true,
    'mensaje' => '✅ Solicitud enviada correctamente. Nos contactaremos contigo pronto.'
], JSON_UNESCAPED_UNICODE);

log_action("SUCCESS: Form processed - $email");
exit;
