<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

setSecurityHeaders();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('Security validation failed');
    }

    $login_id = sanitizeInput($_POST['login_id']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE login_id = ?");
    $stmt->execute([$login_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'mobile' => $user['mobile'],
            'role' => $user['role'],
            'login_id' => $user['login_id']
        ];

        securityLog('LOGIN_SUCCESS', "User: {$user['login_id']}");

        if ($user['role'] === 'admin') {
            header('Location: admin_panel.php');
        } else {
            header('Location: dashboard.php');
        }
        exit();
    } else {
        $error = 'Invalid login ID or password';
        securityLog('LOGIN_FAILED', "Login ID: $login_id");
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - IC Innovations</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #0b2b40;
        }

        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Login to IC Innovations</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="text" name="login_id" placeholder="Email or Mobile Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p style="margin-top: 15px;">Don't have an account? <a href="register.php">Register here</a></p>
        <p><a href="index.php">← Back to Home</a></p>
    </div>
</body>

</html>