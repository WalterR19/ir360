<?php
/**
 * TEST DE SISTEMA DE LOGS v2.3
 * Ejecuta este archivo para verificar dónde se guardan los logs
 * URL: http://tudominio.com/template/test-logs.php
 */

// Detener buffering para ver output inmediato
ob_start();

echo "<h2>🧪 TEST DE SISTEMA DE LOGS v2.3 MEJORADO</h2>";
echo "<hr>";

// Simular la configuración del formmail.php
$current_file = __FILE__;
$template_dir = dirname($current_file);
$root_dir = dirname($template_dir);
$parent_dir = dirname($root_dir);

echo "<h3>📍 Rutas del Sistema:</h3>";
echo "<pre style='background:#f0f0f0; padding:10px;'>";
echo "File: $current_file\n";
echo "Template Dir: $template_dir\n";
echo "Root Dir: $root_dir\n";
echo "Parent Dir: $parent_dir\n";
echo "</pre>";

// Rutas posibles
$candidate_paths = [
    $root_dir . '/web/logs',
    $parent_dir . '/web/logs',
    $parent_dir . '/public_html/web/logs',
    $parent_dir . '/logs',
    $root_dir . '/logs',
    $template_dir . '/logs',
    $template_dir . '/.logs'
];

echo "<h3>🔍 Búsqueda de Rutas Disponibles:</h3>";
echo "<table border='1' cellpadding='10' style='width:100%; border-collapse:collapse;'>";
echo "<tr style='background:#333; color:white;'><th>Ruta</th><th>Existe</th><th>Escribible</th><th>Permisos</th><th>Status</th></tr>";

$logs_dir = null;
$search_attempts = [];

foreach ($candidate_paths as $path) {
    $exists = is_dir($path) ? '✓' : '✗';
    $writable = is_dir($path) && is_writable($path) ? '✓' : '✗';
    $perms = is_dir($path) ? substr(sprintf('%o', fileperms($path)), -4) : 'N/A';
    
    $status = '';
    if (is_dir($path) && is_writable($path) && !$logs_dir) {
        $logs_dir = $path;
        $status = "<span style='background:green; color:white; padding:3px 8px; border-radius:3px; font-weight:bold;'>✓ USANDO</span>";
    } else if (!is_dir($path)) {
        // Intentar crear
        $parent = dirname($path);
        if (is_dir($parent) && is_writable($parent)) {
            if (@mkdir($path, 0777, true)) {
                $status = "<span style='background:blue; color:white; padding:3px 8px; border-radius:3px;'>✓ CREADA</span>";
                $logs_dir = $logs_dir ?: $path;
                $exists = '✓';
                $writable = '✓';
                $perms = substr(sprintf('%o', fileperms($path)), -4);
            } else {
                $status = "<span style='background:orange; color:white; padding:3px 8px; border-radius:3px;'>⚠ Fallo crear</span>";
            }
        } else {
            $status = "<span style='background:red; color:white; padding:3px 8px; border-radius:3px;'>✗ No puede crear</span>";
        }
    }
    
    echo "<tr>";
    echo "<td><code style='font-size:11px;'>$path</code></td>";
    echo "<td style='text-align:center;'>$exists</td>";
    echo "<td style='text-align:center;'>$writable</td>";
    echo "<td style='text-align:center;'>$perms</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}
echo "</table>";

if (!$logs_dir) {
    $logs_dir = $root_dir . '/web/logs';
    @mkdir($logs_dir, 0777, true);
}

echo "<h3 style='color:green;'>✅ RUTA SELECCIONADA PARA LOGS:</h3>";
echo "<p style='background: #e8f5e9; padding: 15px; border-left: 4px solid green; font-size:14px;'>";
echo "<code style='font-size:16px; font-weight:bold;'>$logs_dir</code>";
echo "<br><span style='font-size:12px;'>Existe: " . (is_dir($logs_dir) ? "✓ SÍ" : "✗ NO") . "</span>";
echo "<br><span style='font-size:12px;'>Escribible: " . (is_writable($logs_dir) ? "✓ SÍ" : "✗ NO") . "</span>";
echo "</p>";

// Crear estructura
echo "<h3>🏗️ Crear Estructura de Directorios:</h3>";
$log_categories = [
    'errors',
    'contact-form',
    'turnstile',
    'meta-pixel',
    'rate-limit',
    'debug'
];

if (!is_dir($logs_dir)) {
    @mkdir($logs_dir, 0777, true);
}
@chmod($logs_dir, 0777);

echo "<ul>";
foreach ($log_categories as $category) {
    $subdir = $logs_dir . '/' . $category;
    if (!is_dir($subdir)) {
        if (@mkdir($subdir, 0777, true)) {
            echo "<li style='color:green;'>✓ Creado: <code>$category/</code></li>";
        } else {
            echo "<li style='color:red;'>✗ ERROR creando: <code>$category/</code></li>";
        }
    } else {
        echo "<li style='color:blue;'>✓ Existe: <code>$category/</code></li>";
    }
    @chmod($subdir, 0777);
}
echo "</ul>";

// Test de escritura
echo "<h3>📝 Test de Escritura:</h3>";
$test_file = $logs_dir . '/contact-form/test-' . date('Y-m-d') . '.log';
$test_message = "[TEST] " . date('Y-m-d H:i:s.u') . " [INFO] [" . get_client_ip() . "] Sistema de logs funcionando correctamente\n";

if (@file_put_contents($test_file, $test_message, FILE_APPEND | LOCK_EX)) {
    @chmod($test_file, 0666);
    echo "<p style='background:#e8f5e9; color:green; padding:10px; border-radius:3px;'>";
    echo "<b>✓ Éxito escribiendo en logs</b></p>";
    echo "<p><code style='font-size:12px;'>$test_file</code></p>";
    
    // Mostrar contenido
    $content = file_get_contents($test_file);
    $lines = explode("\n", trim($content));
    $last_10 = array_slice($lines, -10);
    
    echo "<p><b>Últimas 10 líneas del log:</b></p>";
    echo "<pre style='background:#f5f5f5; padding:10px; overflow-x:auto; max-height:300px;'>";
    echo htmlspecialchars(implode("\n", $last_10));
    echo "</pre>";
} else {
    echo "<p style='background:#ffebee; color:red; padding:10px; border-radius:3px;'>";
    echo "<b>✗ Error al escribir</b></p>";
    echo "<p>No se pudo escribir en: <code>$test_file</code></p>";
}

// Diagnóstico
echo "<h3>📋 Archivo de Diagnóstico:</h3>";
$diagnostic_file = $logs_dir . '/DIAGNOSTIC_REPORT.txt';
if (file_exists($diagnostic_file)) {
    echo "<p style='color: green;'>✓ Archivo de diagnóstico encontrado</p>";
    echo "<pre style='background:#f5f5f5; padding:10px; font-size:11px; max-height:400px; overflow-y:auto;'>";
    echo htmlspecialchars(file_get_contents($diagnostic_file));
    echo "</pre>";
} else {
    echo "<p style='color: orange;'>⚠ Archivo de diagnóstico no encontrado</p>";
}

// Listar archivos en logs
echo "<h3>📂 Contenido de Directorios:</h3>";
if (is_dir($logs_dir)) {
    echo "<p>📁 Archivos en: <code>$logs_dir</code></p>";
    $files = @scandir($logs_dir);
    if ($files) {
        echo "<ul>";
        foreach ($files as $f) {
            if ($f != '.' && $f != '..') {
                if (is_dir($logs_dir . '/' . $f)) {
                    echo "<li style='font-weight:bold;'>📁 $f/";
                    $subfiles = @scandir($logs_dir . '/' . $f);
                    if ($subfiles) {
                        echo "<ul>";
                        foreach ($subfiles as $sf) {
                            if ($sf != '.' && $sf != '..') {
                                $size = filesize($logs_dir . '/' . $f . '/' . $sf);
                                echo "<li>📄 $sf (" . formatBytes($size) . ")</li>";
                            }
                        }
                        echo "</ul>";
                    }
                    echo "</li>";
                } else {
                    $size = filesize($logs_dir . '/' . $f);
                    echo "<li>📄 $f (" . formatBytes($size) . ")</li>";
                }
            }
        }
        echo "</ul>";
    }
}

function get_client_ip() {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (isset($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function formatBytes($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes, $k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

echo "<hr style='margin-top:40px;'>";
echo "<p style='color: #666; font-size: 12px;'>Test ejecutado: " . date('Y-m-d H:i:s') . "</p>";
echo "<p style='color: #999; font-size: 11px;'>Versión de prueba: 2.3 | PHP " . phpversion() . "</p>";

?>
