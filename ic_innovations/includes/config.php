<?php

/**
 * Configuration File
 * Supports both environment variables (production) and hardcoded (development)
 * 
 * For Production:
 * - Set environment variables in Apache/Nginx
 * - OR use .env file (requires PHP dotenv)
 * 
 * For Development:
 * - Edit the fallback values below
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================

// Priority: Environment Variable > Hardcoded Fallback
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'ic_innovations');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// ============================================
// SITE CONFIGURATION
// ============================================

define('SITE_NAME', getenv('SITE_NAME') ?: 'IC Innovations Tech & Business Management Consultancy');

// Auto-detect URL if not set in environment
$site_url = getenv('SITE_URL');
if (!$site_url) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $site_url = $protocol . $host . '/';
}
define('SITE_URL', $site_url);

define('ADMIN_EMAIL', getenv('ADMIN_EMAIL') ?: 'info@icinnovations.tech');

// ============================================
// SECURITY SETTINGS
// ============================================

define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 7200); // 2 hours default

// ============================================
// BUSINESS RULES
// ============================================

define('TAX_RATE', getenv('TAX_RATE') ?: 10); // Percentage
define('PAYMENT_ADVANCE', getenv('PAYMENT_ADVANCE') ?: 50);
define('PAYMENT_MID', getenv('PAYMENT_MID') ?: 35);
define('PAYMENT_FINAL', getenv('PAYMENT_FINAL') ?: 15);

// ============================================
// SESSION CONFIGURATION
// ============================================

// Set session lifetime
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// ERROR REPORTING (Development vs Production)
// ============================================

$environment = getenv('APP_ENV') ?: 'development';

if ($environment === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// ============================================
// TIME ZONE
// ============================================

date_default_timezone_set(getenv('TIMEZONE') ?: 'Asia/Karachi');
