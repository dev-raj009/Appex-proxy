<?php
/**
 * ============================================================
 *  proxy.php — PHP CURL Proxy
 *  Routes: ?type=video | pdf | health
 *  InfinityFree / cPanel Compatible
 * ============================================================
 */

require_once __DIR__ . '/config.php';

// Disable PHP output buffering completely for streaming
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 'off');
@ini_set('implicit_flush', 1);
if (ob_get_level() > 0) ob_end_clean();

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, HEAD, OPTIONS');
header('Access-Control-Allow-Headers: Range, Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ---- Get request type ----
$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : 'video';

// ============================================================
//  HEALTH CHECK
// ============================================================
if ($type === 'health') {
    $nodeUrl = rtrim(PROXY_SERVER, '/') . '/health';
    $ch = curl_init($nodeUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $body    = curl_exec($ch);
    $code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    header('Content-Type: application/json');

    if ($curlErr || !$body) {
        http_response_code(502);
        echo json_encode([
            'php_proxy'   => 'ok',
            'node_server' => 'unreachable',
            'error'       => $curlErr ?: 'Empty response',
            'render_url'  => PROXY_SERVER
        ]);
    } else {
        http_response_code($code);
        $decoded = json_decode($body, true);
        echo json_encode([
            'php_proxy'   => 'ok',
            'node_server' => ($code === 200) ? 'ok' : 'error',
            'node_code'   => $code,
            'node_data'   => $decoded,
            'render_url'  => PROXY_SERVER,
            'php_version' => PHP_VERSION
        ]);
    }
    exit;
}

// ============================================================
//  VIDEO / PDF PROXY
// ============================================================
$rawUrl = isset($_GET['url']) ? trim($_GET['url']) : '';
$key    = isset($_GET['key']) ? trim($_GET['key']) : DEFAULT_KEY;

// Validation
if (empty($rawUrl)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing url parameter']);
    exit;
}

// Decode URL
$decodedUrl = urldecode($rawUrl);

// Validate URL
if (!filter_var($decodedUrl, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid URL format: ' . htmlspecialchars($decodedUrl)]);
    exit;
}

// Domain whitelist check
$allowedDomains = ALLOWED_DOMAINS;
if (!empty($allowedDomains)) {
    $host    = parse_url($decodedUrl, PHP_URL_HOST) ?: '';
    $allowed = false;
    foreach ($allowedDomains as $domain) {
        if ($domain && strpos($host, $domain) !== false) {
            $allowed = true;
            break;
        }
    }
    if (!$allowed) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Domain not allowed: ' . $host]);
        exit;
    }
}

// Build target URL on Node server
$endpoint  = ($type === 'pdf') ? '/pdf' : '/video';
$targetUrl = rtrim(PROXY_SERVER, '/') . $endpoint
           . '?url=' . urlencode($decodedUrl)
           . '&key=' . urlencode($key);

// Forward Range header if present (for video seeking)
$curlHeaders = [];
if (!empty($_SERVER['HTTP_RANGE'])) {
    $curlHeaders[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
}

// ---- CURL streaming ----
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $targetUrl,
    CURLOPT_RETURNTRANSFER => false,        // Stream directly, don't buffer
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 5,
    CURLOPT_TIMEOUT        => 0,            // No timeout — full video must stream
    CURLOPT_CONNECTTIMEOUT => 20,
    CURLOPT_SSL_VERIFYPEER => false,        // InfinityFree SSL workaround
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_HTTPHEADER     => $curlHeaders,
    CURLOPT_ENCODING       => '',           // Accept all encodings
    CURLOPT_BUFFERSIZE     => 131072,       // 128KB buffer for smooth streaming

    // Forward response headers from Node to browser
    CURLOPT_HEADERFUNCTION => function ($curl, $header) {
        $len  = strlen($header);
        $trim = trim($header);

        // Forward only useful headers
        $forward = [
            'content-type',
            'content-length',
            'content-range',
            'accept-ranges',
        ];
        foreach ($forward as $h) {
            if (stripos($trim, $h . ':') === 0) {
                header($trim, true);
                break;
            }
        }

        // Set HTTP status code
        if (preg_match('/^HTTP\/[\d\.]+ (\d+)/i', $trim, $m)) {
            http_response_code((int)$m[1]);
        }

        return $len;
    },

    // Write response body directly to output
    CURLOPT_WRITEFUNCTION  => function ($curl, $data) {
        echo $data;
        if (ob_get_level() > 0) ob_flush();
        @flush();
        return strlen($data);
    },
]);

// Cache header
header('Cache-Control: public, max-age=' . CACHE_SECONDS);
header('X-Proxy-By: SpidyUniverse-PHP/' . APP_VERSION);

// Execute
curl_exec($ch);
$curlErrno = curl_errno($ch);
$curlError = curl_error($ch);
curl_close($ch);

// Report CURL errors if headers not yet sent
if ($curlErrno && !headers_sent()) {
    http_response_code(502);
    header('Content-Type: application/json');
    echo json_encode([
        'error'      => 'PHP proxy failed to reach Node server',
        'curl_error' => $curlError,
        'curl_errno' => $curlErrno,
        'target'     => PROXY_SERVER
    ]);
}

exit;
