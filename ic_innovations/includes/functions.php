<?php

/**
 * Helper Functions
 */

require_once __DIR__ . '/security.php';

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

// Check if user is admin
function isAdmin()
{
    return isLoggedIn() && $_SESSION['user']['role'] === 'admin';
}

// Redirect if not authenticated
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin()
{
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

// Get user by ID
function getUserById($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT id, name, country, mobile, whatsapp, email, role, looking_for, login_id, created_at FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all active news
function getActiveNews($pdo, $limit = 10)
{
    $stmt = $pdo->query("SELECT news_text FROM news_bulletin WHERE is_active=1 ORDER BY id DESC LIMIT $limit");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get all services
function getAllServices($pdo)
{
    $stmt = $pdo->query("SELECT id, category, service_name, charge, description FROM services ORDER BY category, service_name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get projects with completion percentage
function getProjects($pdo)
{
    $stmt = $pdo->query("SELECT project_name, completion_percent FROM projects ORDER BY completion_percent DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get I-Corner links
function getICornerLinks($pdo)
{
    $stmt = $pdo->query("SELECT title, url, category FROM icorner_links ORDER BY category, title");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Save quotation request
function saveQuotation($pdo, $session_id, $services, $subtotal, $tax, $total)
{
    $stmt = $pdo->prepare("INSERT INTO quotation_requests (session_id, selected_services, subtotal, tax, total, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    return $stmt->execute([$session_id, json_encode($services), $subtotal, $tax, $total]);
}

// Save user opinion
function saveOpinion($pdo, $name, $contact, $email, $message)
{
    $stmt = $pdo->prepare("INSERT INTO user_opinions (name, contact, email, message) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$name, $contact, $email, $message]);
}
