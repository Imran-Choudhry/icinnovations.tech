<?php
/**
 * Admin Panel - Complete Control Panel
 * Only accessible by users with 'admin' role
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/functions.php';

setSecurityHeaders();
requireAdmin(); // Only admin can access

$success_message = '';
$error_message = '';

// Handle different admin actions
$action = $_GET['action'] ?? 'dashboard';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        die('Security validation failed');
    }
    
    // Update Service Charge
    if (isset($_POST['update_charge'])) {
        $service_id = (int)$_POST['service_id'];
        $new_charge = (float)$_POST['charge'];
        
        $stmt = $pdo->prepare("UPDATE services SET charge = ? WHERE id = ?");
        if ($stmt->execute([$new_charge, $service_id])) {
            $success_message = "Service charge updated successfully!";
            securityLog('ADMIN_UPDATE_SERVICE', "Service ID: $service_id, New Charge: $new_charge");
        } else {
            $error_message = "Failed to update service charge";
        }
    }
    
    // Add News Bulletin
    if (isset($_POST['add_news'])) {
        $news_text = sanitizeInput($_POST['news_text']);
        
        $stmt = $pdo->prepare("INSERT INTO news_bulletin (news_text, is_active) VALUES (?, 1)");
        if ($stmt->execute([$news_text])) {
            $success_message = "News added successfully!";
            securityLog('ADMIN_ADD_NEWS', "News: $news_text");
        } else {
            $error_message = "Failed to add news";
        }
    }
    
    // Delete News
    if (isset($_POST['delete_news'])) {
        $news_id = (int)$_POST['news_id'];
        
        $stmt = $pdo->prepare("DELETE FROM news_bulletin WHERE id = ?");
        if ($stmt->execute([$news_id])) {
            $success_message = "News deleted successfully!";
            securityLog('ADMIN_DELETE_NEWS', "News ID: $news_id");
        } else {
            $error_message = "Failed to delete news";
        }
    }
    
    // Add I-Corner Link
    if (isset($_POST['add_icorner_link'])) {
        $title = sanitizeInput($_POST['title']);
        $url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
        $category = sanitizeInput($_POST['category']);
        
        $stmt = $pdo->prepare("INSERT INTO icorner_links (title, url, category) VALUES (?, ?, ?)");
        if ($stmt->execute([$title, $url, $category])) {
            $success_message = "I-Corner link added successfully!";
            securityLog('ADMIN_ADD_ICORNER', "Title: $title");
        } else {
            $error_message = "Failed to add I-Corner link";
        }
    }
    
    // Delete I-Corner Link
    if (isset($_POST['delete_icorner'])) {
        $link_id = (int)$_POST['link_id'];
        
        $stmt = $pdo->prepare("DELETE FROM icorner_links WHERE id = ?");
        if ($stmt->execute([$link_id])) {
            $success_message = "I-Corner link deleted successfully!";
            securityLog('ADMIN_DELETE_ICORNER', "Link ID: $link_id");
        } else {
            $error_message = "Failed to delete I-Corner link";
        }
    }
    
    // Add/Update Project
    if (isset($_POST['update_project'])) {
        $project_id = (int)$_POST['project_id'];
        $completion = (int)$_POST['completion_percent'];
        
        $stmt = $pdo->prepare("UPDATE projects SET completion_percent = ? WHERE id = ?");
        if ($stmt->execute([$completion, $project_id])) {
            $success_message = "Project progress updated!";
            securityLog('ADMIN_UPDATE_PROJECT', "Project ID: $project_id, Completion: $completion%");
        } else {
            $error_message = "Failed to update project";
        }
    }
    
    // Add New Project
    if (isset($_POST['add_project'])) {
        $project_name = sanitizeInput($_POST['project_name']);
        $completion = (int)$_POST['completion_percent'];
        
        $stmt = $pdo->prepare("INSERT INTO projects (project_name, completion_percent) VALUES (?, ?)");
        if ($stmt->execute([$project_name, $completion])) {
            $success_message = "New project added successfully!";
            securityLog('ADMIN_ADD_PROJECT', "Project: $project_name");
        } else {
            $error_message = "Failed to add project";
        }
    }
    
    // Update User Role
    if (isset($_POST['update_user_role'])) {
        $user_id = (int)$_POST['user_id'];
        $new_role = sanitizeInput($_POST['role']);
        
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        if ($stmt->execute([$new_role, $user_id])) {
            $success_message = "User role updated successfully!";
            securityLog('ADMIN_UPDATE_USER_ROLE', "User ID: $user_id, New Role: $new_role");
        } else {
            $error_message = "Failed to update user role";
        }
    }
    
    // Reset User Password
    if (isset($_POST['reset_password'])) {
        $user_id = (int)$_POST['user_id'];
        $new_password = generateRandomPassword();
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $user_id])) {
            // Get user login_id to show
            $user_stmt = $pdo->prepare("SELECT login_id FROM users WHERE id = ?");
            $user_stmt->execute([$user_id]);
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
            
            $success_message = "Password reset successful!<br>Login ID: {$user['login_id']}<br>New Password: $new_password";
            securityLog('ADMIN_RESET_PASSWORD', "User ID: $user_id");
        } else {
            $error_message = "Failed to reset password";
        }
    }
}

// Fetch data for display
$services = $pdo->query("SELECT * FROM services ORDER BY category, service_name")->fetchAll();
$news = $pdo->query("SELECT * FROM news_bulletin ORDER BY id DESC")->fetchAll();
$icorner_links = $pdo->query("SELECT * FROM icorner_links ORDER BY category, title")->fetchAll();
$projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();
$users = $pdo->query("SELECT id, name, email, mobile, role, login_id, created_at FROM users ORDER BY created_at DESC")->fetchAll();
$quotes = $pdo->query("SELECT * FROM quotation_requests ORDER BY created_at DESC LIMIT 20")->fetchAll();
$opinions = $pdo->query("SELECT * FROM user_opinions ORDER BY submitted_at DESC LIMIT 20")->fetchAll();

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - IC Innovations</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1400px;
            margin: 100px auto 50px;
            padding: 20px;
        }
        .admin-header {
            background: linear-gradient(135deg, #0b2b40, #123c4f);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .admin-nav {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 30px;
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-nav a {
            text-decoration: none;
            padding: 10px 20px;
            background: #e9f0f5;
            color: #0b2b40;
            border-radius: 25px;
            font-weight: 600;
            transition: 0.2s;
        }
        .admin-nav a:hover, .admin-nav a.active {
            background: #2a9d8f;
            color: white;
        }
        .admin-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-section h3 {
            color: #0b2b40;
            border-bottom: 2px solid #2a9d8f;
            padding-bottom: 10px;
            margin-bottom: 20px;
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background: #f4f7fc;
            font-weight: 600;
        }
        input[type="text"], input[type="number"], input[type="url"], select, textarea {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 100%;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .btn-small {
            padding: 5px 12px;
            font-size: 0.9rem;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        @media (max-width: 768px) {
            .grid-2 { grid-template-columns: 1fr; }
            .admin-nav a { padding: 5px 12px; font-size: 0.9rem; }
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #2a9d8f, #264653);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }
        .stat-card h4 { font-size: 2rem; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Control Panel</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> | 
               <a href="logout.php" style="color: #ffd966;">Logout</a> | 
               <a href="index.php" style="color: #ffd966;">View Site</a>
            </p>
        </div>
        
        <?php if($success_message): ?>
            <div class="success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <div class="admin-nav">
            <a href="?action=dashboard" class="<?php echo $action == 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
            <a href="?action=services" class="<?php echo $action == 'services' ? 'active' : ''; ?>">Services & Charges</a>
            <a href="?action=news" class="<?php echo $action == 'news' ? 'active' : ''; ?>">News Bulletin</a>
            <a href="?action=projects" class="<?php echo $action == 'projects' ? 'active' : ''; ?>">Projects</a>
            <a href="?action=icorner" class="<?php echo $action == 'icorner' ? 'active' : ''; ?>">I-Corner</a>
            <a href="?action=users" class="<?php echo $action == 'users' ? 'active' : ''; ?>">Users</a>
            <a href="?action=quotes" class="<?php echo $action == 'quotes' ? 'active' : ''; ?>">Quotations</a>
            <a href="?action=opinions" class="<?php echo $action == 'opinions' ? 'active' : ''; ?>">Opinions</a>
        </div>
        
        <!-- Dashboard View -->
        <?php if($action == 'dashboard'): ?>
        <div class="stats">
            <div class="stat-card">
                <h4><?php echo count($users); ?></h4>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h4><?php echo count($services); ?></h4>
                <p>Services</p>
            </div>
            <div class="stat-card">
                <h4><?php echo count($quotes); ?></h4>
                <p>Quotations</p>
            </div>
            <div class="stat-card">
                <h4><?php echo count($opinions); ?></h4>
                <p>Messages</p>
            </div>
        </div>
        
        <div class="admin-section">
            <h3>Recent Quotations</h3>
            <table>
                <thead>
                    <tr><th>Date</th><th>Subtotal</th><th>Tax</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach(array_slice($quotes, 0, 5) as $quote): ?>
                    <tr>
                        <td><?php echo date('Y-m-d', strtotime($quote['created_at'])); ?></td>
                        <td>$<?php echo $quote['subtotal']; ?></td>
                        <td>$<?php echo $quote['tax']; ?></td>
                        <td>$<?php echo $quote['total']; ?></td>
                        <td><?php echo ucfirst($quote['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Services Management -->
        <?php if($action == 'services'): ?>
        <div class="admin-section">
            <h3>Manage Service Charges</h3>
            <table>
                <thead>
                    <tr><th>Category</th><th>Service Name</th><th>Current Charge ($)</th><th>Update</th></tr>
                </thead>
                <tbody>
                    <?php foreach($services as $service): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($service['category']); ?></td>
                        <td><?php echo htmlspecialchars($service['service_name']); ?></td>
                        <td>$<?php echo number_format($service['charge'], 2); ?></td>
                        <td>
                            <form method="post" style="display: flex; gap: 5px;">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                <input type="number" name="charge" step="10" value="<?php echo $service['charge']; ?>" style="width: 100px;">
                                <button type="submit" name="update_charge" class="btn-small">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- News Management -->
        <?php if($action == 'news'): ?>
        <div class="admin-section">
            <h3>Add News Bulletin</h3>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group">
                    <input type="text" name="news_text" placeholder="Enter news announcement..." required style="width: 100%; padding: 10px;">
                </div>
                <button type="submit" name="add_news">Add News</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h3>Existing News</h3>
            <table>
                <thead><tr><th>News</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($news as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['news_text']); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($item['created_at'])); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="news_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="delete_news" class="btn-small" onclick="return confirm('Delete this news?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Projects Management -->
        <?php if($action == 'projects'): ?>
        <div class="admin-section">
            <h3>Add New Project</h3>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="grid-2">
                    <input type="text" name="project_name" placeholder="Project Name" required>
                    <input type="number" name="completion_percent" placeholder="Completion % (0-100)" min="0" max="100" required>
                </div>
                <button type="submit" name="add_project">Add Project</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h3>Update Project Progress</h3>
            <table>
                <thead><tr><th>Project</th><th>Current Progress</th><th>Update</th></tr></thead>
                <tbody>
                    <?php foreach($projects as $project): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                        <td>
                            <div class="gantt-bar" style="width: 200px;">
                                <div class="gantt-fill" style="width: <?php echo $project['completion_percent']; ?>%;"><?php echo $project['completion_percent']; ?>%</div>
                            </div>
                        </td>
                        <td>
                            <form method="post" style="display: flex; gap: 5px;">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                <input type="number" name="completion_percent" min="0" max="100" value="<?php echo $project['completion_percent']; ?>" style="width: 80px;">
                                <button type="submit" name="update_project" class="btn-small">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- I-Corner Management -->
        <?php if($action == 'icorner'): ?>
        <div class="admin-section">
            <h3>Add I-Corner Link</h3>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="grid-2">
                    <input type="text" name="title" placeholder="Link Title" required>
                    <input type="url" name="url" placeholder="URL" required>
                    <select name="category">
                        <option value="portal">Portal</option>
                        <option value="tool">Tool</option>
                        <option value="resource">Resource</option>
                        <option value="general">General</option>
                    </select>
                </div>
                <button type="submit" name="add_icorner_link">Add Link</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h3>Existing I-Corner Links</h3>
            <table>
                <thead><tr><th>Title</th><th>URL</th><th>Category</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($icorner_links as $link): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($link['title']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank"><?php echo htmlspecialchars($link['url']); ?></a></td>
                        <td><?php echo htmlspecialchars($link['category']); ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="link_id" value="<?php echo $link['id']; ?>">
                                <button type="submit" name="delete_icorner" class="btn-small" onclick="return confirm('Delete this link?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Users Management -->
        <?php if($action == 'users'): ?>
        <div class="admin-section">
            <h3>Manage Users</h3>
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Login ID</th><th>Mobile</th><th>Role</th><th>Registered</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['login_id']); ?></td>
                        <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="role" onchange="this.form.submit()">
                                    <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                <input type="hidden" name="update_user_role">
                            </form>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="reset_password" class="btn-small" onclick="return confirm('Reset password for this user?')">Reset Password</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Quotations View -->
        <?php if($action == 'quotes'): ?>
        <div class="admin-section">
            <h3>All Quotation Requests</h3>
            <table>
                <thead>
                    <tr><th>Date</th><th>Session ID</th><th>Services</th><th>Subtotal</th><th>Tax</th><th>Total</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach($quotes as $quote): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($quote['created_at'])); ?></td>
                        <td><?php echo substr($quote['session_id'], 0, 20); ?>...</td>
                        <td><?php 
                            $services_list = json_decode($quote['selected_services'], true);
                            echo count($services_list) . ' service(s)';
                        ?></td>
                        <td>$<?php echo $quote['subtotal']; ?></td>
                        <td>$<?php echo $quote['tax']; ?></td>
                        <td><strong>$<?php echo $quote['total']; ?></strong></td>
                        <td><?php echo ucfirst($quote['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Opinions View -->
        <?php if($action == 'opinions'): ?>
        <div class="admin-section">
            <h3>User Messages & Opinions</h3>
            <table>
                <thead>
                    <tr><th>Date</th><th>Name</th><th>Contact</th><th>Email</th><th>Message</th></tr>
                </thead>
                <tbody>
                    <?php foreach($opinions as $opinion): ?>
                    <tr>
                        <td><?php echo date('Y-m-d', strtotime($opinion['submitted_at'])); ?></td>
                        <td><?php echo htmlspecialchars($opinion['name']); ?></td>
                        <td><?php echo htmlspecialchars($opinion['contact']); ?></td>
                        <td><?php echo htmlspecialchars($opinion['email']); ?></td>
                        <td><?php echo htmlspecialchars(substr($opinion['message'], 0, 100)) . '...'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>