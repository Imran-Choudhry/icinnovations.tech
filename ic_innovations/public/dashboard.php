<?php

/**
 * User Dashboard - View orders, track projects, update profile
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

setSecurityHeaders();
requireLogin();

$success_message = '';
$error_message = '';

$user_id = $_SESSION['user']['id'];

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('Security validation failed');
    }

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($current_password, $user['password'])) {
        $error_message = "Current password is incorrect";
    } elseif (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $user_id])) {
            $success_message = "Password changed successfully!";
            securityLog('PASSWORD_CHANGE', "User ID: $user_id");
        } else {
            $error_message = "Failed to change password";
        }
    }
}

// Get user's quotations (by session or user_id)
$stmt = $pdo->prepare("SELECT * FROM quotation_requests WHERE session_id = ? OR user_id = ? ORDER BY created_at DESC");
$stmt->execute([session_id(), $user_id]);
$quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's projects (if any)
$stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? OR user_id IS NULL ORDER BY updated_at DESC");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - IC Innovations</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 100px auto 50px;
            padding: 20px;
        }

        .welcome-card {
            background: linear-gradient(135deg, #0b2b40, #123c4f);
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .dashboard-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card h3 {
            color: #0b2b40;
            border-bottom: 2px solid #2a9d8f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .profile-info {
            list-style: none;
        }

        .profile-info li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .profile-info strong {
            display: inline-block;
            width: 120px;
            color: #0b2b40;
        }

        .order-item,
        .project-item {
            padding: 12px;
            background: #f9f9f9;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .status-pending {
            color: #f39c12;
            font-weight: bold;
        }

        .status-accepted {
            color: #27ae60;
            font-weight: bold;
        }

        .status-rejected {
            color: #e74c3c;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #0b2b40;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="welcome-card">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
            <p>Manage your account, track orders, and monitor project progress.</p>
            <p style="margin-top: 15px;">
                <a href="index.php" style="color: #ffd966;">← Back to Home</a> |
                <a href="logout.php" style="color: #ffd966;">Logout</a>
            </p>
        </div>

        <?php if ($success_message): ?>
            <div class="success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Profile Information -->
            <div class="dashboard-card">
                <h3>My Profile</h3>
                <ul class="profile-info">
                    <li><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['user']['name']); ?></li>
                    <li><strong>Login ID:</strong> <?php echo htmlspecialchars($_SESSION['user']['login_id']); ?></li>
                    <li><strong>Mobile:</strong> <?php echo htmlspecialchars($_SESSION['user']['mobile']); ?></li>
                    <li><strong>Role:</strong> <?php echo ucfirst($_SESSION['user']['role']); ?></li>
                </ul>
            </div>

            <!-- Change Password -->
            <div class="dashboard-card">
                <h3>Change Password</h3>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password (min 6 characters)</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="change_password">Update Password</button>
                </form>
            </div>
        </div>

        <!-- My Quotations -->
        <div class="dashboard-card" style="margin-bottom: 25px;">
            <h3>My Quotation Requests</h3>
            <?php if (count($quotations) > 0): ?>
                <?php foreach ($quotations as $quote): ?>
                    <div class="order-item">
                        <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($quote['created_at'])); ?></p>
                        <p><strong>Services Selected:</strong> <?php
                                                                $services = json_decode($quote['selected_services'], true);
                                                                echo count($services) . ' service(s)';
                                                                ?></p>
                        <p><strong>Total Amount:</strong> $<?php echo number_format($quote['total'], 2); ?></p>
                        <p><strong>Status:</strong> <span class="status-<?php echo $quote['status']; ?>"><?php echo ucfirst($quote['status']); ?></span></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No quotation requests yet. <a href="index.php">Request a quotation</a></p>
            <?php endif; ?>
        </div>

        <!-- Project Tracker -->
        <div class="dashboard-card">
            <h3>My Project Progress</h3>
            <?php if (count($projects) > 0): ?>
                <?php foreach ($projects as $project): ?>
                    <div class="project-item">
                        <strong><?php echo htmlspecialchars($project['project_name']); ?></strong>
                        <div class="gantt-bar" style="margin-top: 10px;">
                            <div class="gantt-fill" style="width: <?php echo $project['completion_percent']; ?>%;">
                                <?php echo $project['completion_percent']; ?>% Complete
                            </div>
                        </div>
                        <p style="margin-top: 10px; font-size: 0.9rem; color: #666;">
                            Last updated: <?php echo date('Y-m-d', strtotime($project['updated_at'])); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No active projects assigned yet. Check back later!</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>