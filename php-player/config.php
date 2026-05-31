<?php
/**
 * ============================================================
 *  config.php — AppX PHP Player Configuration
 *  InfinityFree / cPanel Hosting Ready
 * ============================================================
 *
 *  STEP 1: Render pe server.js deploy karo
 *  STEP 2: Render URL copy karke neeche paste karo
 *  STEP 3: Ye folder InfinityFree ke htdocs me upload karo
 *
 * ============================================================
 */

// ★ APNA RENDER URL YAHAN DAALO ★
// Example: https://appx-proxy-server.onrender.com
define('PROXY_SERVER', 'https://YOUR-APP-NAME.onrender.com');

// Default decryption key (AppX standard)
define('DEFAULT_KEY', 'appx-pdf-keyset');

// Player branding
define('PLAYER_TITLE', 'SpidyUniverse Player');
define('APP_NAME',     'SPIDY UNIVERSE');
define('APP_VERSION',  '2.0.0');

// Domain whitelist — sirf inhi domains ki URLs allow hongi
// Empty array = sab allow
define('ALLOWED_DOMAINS', [
    'akamai.net.in',
    'appx',
    // Aur domains add karo zaroorat ho to
]);

// Cache time seconds
define('CACHE_SECONDS', 3600);
