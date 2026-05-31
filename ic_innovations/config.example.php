<?php

/**
 * Configuration File Template
 * 
 * INSTALLATION:
 * 1. Copy this file to includes/config.php
 * 2. Update database credentials below
 * 3. For production, use environment variables (see .htaccess or .env)
 * 
 * IMPORTANT: includes/config.php is excluded from git for security
 */

// ============================================
// DATABASE CONFIGURATION (Development)
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'ic_innovations');
define('DB_USER', 'root');
define('DB_PASS', '');

// ============================================
// SITE CONFIGURATION
// ============================================

define('SITE_NAME', 'IC Innovations Tech & Business Management Consultancy');
define('SITE_URL', 'http://localhost/ic-innovations-website/public/');
define('ADMIN_EMAIL', 'info@icinnovations.tech');

// ============================================
// SECURITY SETTINGS
// ============================================

define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_LIFETIME', 7200); // 2 hours in seconds

// ============================================
// BUSINESS RULES (Can be overridden by environment)
// ============================================

define('TAX_RATE', 10);           // Tax percentage
define('PAYMENT_ADVANCE', 50);     // Advance payment %
define('PAYMENT_MID', 35);         // Mid payment %
define('PAYMENT_FINAL', 15);       // Final payment %

// ============================================
// PRODUCTION ENVIRONMENT VARIABLES (EXAMPLE)
// ============================================

/*
 * For production, use Apache SetEnv or Nginx fastcgi_param:
 * 
 * Apache (.htaccess):
 *   SetEnv DB_HOST "production_host"
 *   SetEnv DB_USER "production_user"
 *   SetEnv DB_PASS "secure_password"
 *   SetEnv APP_ENV "production"
 * 
 * Nginx:
 *   fastcgi_param DB_HOST "production_host";
 *   fastcgi_param DB_USER "production_user";
 *   fastcgi_param DB_PASS "secure_password";
 *   fastcgi_param APP_ENV "production";
 */

// ============================================
// SESSION START
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// ERROR REPORTING (Development only)
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
