<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

setSecurityHeaders();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('Security validation failed');
    }

    $name = sanitizeInput($_POST['name']);
    $country = sanitizeInput($_POST['country']);
    $mobile = sanitizeInput($_POST['mobile']);
    $whatsapp = sanitizeInput($_POST['whatsapp']);
    $email = !empty($_POST['email']) ? sanitizeInput($_POST['email']) : null;
    $role = $_POST['role'] === 'admin' ? 'user' : 'user'; // Force user role, admin must be set via DB
    $looking_for = sanitizeInput($_POST['looking_for']);

    // Validate mobile
    if (!validateMobile($mobile)) {
        $error = 'Invalid mobile number format. Use 03XXXXXXXXX or +923XXXXXXXXX';
    }

    // Validate email if provided
    if ($email && !validateEmail($email)) {
        $error = 'Invalid email address';
    }

    // Check if mobile already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ?");
    $stmt->execute([$mobile]);
    if ($stmt->fetch()) {
        $error = 'Mobile number already registered';
    }

    if (empty($error)) {
        $login_id = !empty($email) ? $email : $mobile;
        $auto_password = generateRandomPassword();
        $hashed_password = password_hash($auto_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, country, mobile, whatsapp, email, role, looking_for, login_id, password) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt->execute([$name, $country, $mobile, $whatsapp, $email, $role, $looking_for, $login_id, $hashed_password])) {
            $success = "Registration successful!<br>Your Login ID: $login_id<br>Password: $auto_password<br>
                       <strong>Please save this password. You can change it after login.</strong>";
            securityLog('REGISTRATION_SUCCESS', "New user: $login_id");
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - IC Innovations</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .register-container h2 {
            margin-bottom: 20px;
            color: #0b2b40;
        }

        .register-container input,
        .register-container select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .success {
            color: green;
            margin-bottom: 15px;
        }

        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="register-container">
        <h2>Register with IC Innovations</h2>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
            <p><a href="login.php">Go to Login →</a></p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="text" name="name" placeholder="Full Name *" required>
                <input type="text" name="country" placeholder="Country *" required>
                <input type="tel" name="mobile" placeholder="Mobile Number * (03XXXXXXXXX)" required>
                <input type="tel" name="whatsapp" placeholder="WhatsApp Number">
                <input type="email" name="email" placeholder="Email (optional)">
                <select name="looking_for" required>
                    <option value="">I am looking for...</option>
                    <option value="freelance_consultant">Freelance Consultant</option>
                    <option value="developer">Developer (Website/Mobile Apps)</option>
                    <option value="hr_solvers">HR Solvers</option>
                    <option value="provider_saas">Provider SaaS</option>
                </select>
                <input type="hidden" name="role" value="user">
                <button type="submit">Register</button>
            </form>
            <p style="margin-top: 15px;">Already have an account? <a href="login.php">Login here</a></p>
        <?php endif; ?>
        <p><a href="index.php">← Back to Home</a></p>
    </div>
</body>

</html>