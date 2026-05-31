<?php

/**
 * Security Functions & Headers
 */

// Set security headers for every page
function setSecurityHeaders()
{
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:;");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}

// Generate CSRF token
function generateCSRFToken()
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

// Verify CSRF token
function verifyCSRFToken($token)
{
    if (!isset($_SESSION[CSRF_TOKEN_NAME]) || !hash_equals($_SESSION[CSRF_TOKEN_NAME], $token)) {
        die("CSRF token validation failed");
    }
    return true;
}

// Sanitize input (for extra safety, but prepared statements handle main injection)
function sanitizeInput($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate email
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate mobile number (Pakistan format)
function validateMobile($mobile)
{
    return preg_match('/^(\+92|0)[0-9]{10}$/', $mobile);
}

// Generate random password
function generateRandomPassword($length = 8)
{
    $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
    return substr(str_shuffle($chars), 0, $length);
}

// Log security events
function securityLog($event, $details = '')
{
    $logFile = __DIR__ . '/../logs/security.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $logEntry = "[$timestamp] [$ip] $event - $details" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
