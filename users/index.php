<?php
require_once '../config/database.php';
require_once '../config/auth.php';

// Only admins can access user management
requireAdmin();

// Handle user actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    $action = $_GET['action'];
    
    try {
        if ($action === 'toggle_status') {
            $sql = "UPDATE users SET is_active = NOT is_active WHERE id = ?";
            executeQuery($pdo, $sql, [$userId]);
            $success = "User status updated successfully!";
        } elseif ($action === 'delete' && $userId != $_SESSION['user_id']) {
            deleteRecord($pdo, 'users', 'id', $userId);
            $success = "User deleted successfully!";
        }
    } catch(Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get all users
try {
    $sql = "SELECT id, username, email, role, first_name, last_name, created_at, last_login, is_active 
            FROM users ORDER BY created_at DESC";
    $stmt = executeQuery($pdo, $sql);
    $users = $stmt->fetchAll();
} catch(Exception $e) {
    $error = "Error loading users: " . $e->getMessage();
}

$currentUser = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="theme-color" content="#667eea">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Course Manager">
    <meta name="format-detection" content="telephone=no">
    <title>User Management - Course Management System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <h1><i class="fas fa-graduation-cap"></i> Course Management System</h1>
                <ul class="nav-menu">
                    <li><a href="../index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="../students/index.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                    <li><a href="../teachers/index.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                    <li><a href="../courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="../facilities/index.php"><i class="fas fa-building"></i> Facilities</a></li>
                    <li class="user-menu">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="color: white;">
                                <i class="fas fa-user"></i> 
                                <?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?>
                                <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; margin-left: 0.5rem;">ADMIN</span>
                            </span>
                            <a href="../auth/logout.php" style="color: white; text-decoration: none;">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-header">
                <h2>User Management</h2>
                <p>Manage system users and their access</p>
            </div>

            <div class="quick-actions">
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New User
                </a>
            </div>

            <div class="table">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($users) && count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr style="<?= !$user['is_active'] ? 'opacity: 0.6;' : '' ?>">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <i class="fas <?= $user['role'] === 'admin' ? 'fa-user-shield' : 'fa-user' ?>" 
                                               style="color: <?= $user['role'] === 'admin' ? '#dc3545' : '#667eea' ?>;"></i>
                                            <div>
                                                <div style="font-weight: 600;">
                                                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span style="padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;
                                                     background: <?= $user['role'] === 'admin' ? '#dc3545' : '#667eea' ?>; 
                                                     color: white;">
                                            <?= strtoupper($user['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;
                                                     background: <?= $user['is_active'] ? '#28a745' : '#6c757d' ?>; 
                                                     color: white;">
                                            <?= $user['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($user['last_login']): ?>
                                            <?= date('M j, Y g:i A', strtotime($user['last_login'])) ?>
                                        <?php else: ?>
                                            <span style="color: #999;">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['id'] != $currentUser['id']): ?>
                                            <a href="?action=toggle_status&id=<?= $user['id'] ?>" 
                                               class="btn <?= $user['is_active'] ? 'btn-warning' : 'btn-success' ?>" 
                                               title="<?= $user['is_active'] ? 'Deactivate' : 'Activate' ?> User">
                                                <i class="fas <?= $user['is_active'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                                            </a>
                                            <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-primary" title="Edit User">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?action=delete&id=<?= $user['id'] ?>" 
                                               class="btn btn-danger" 
                                               title="Delete User"
                                               onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 0.8rem;">Current User</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem;">
                                    <i class="fas fa-users" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>No users found.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Management System. All rights reserved.</p>
        </div>
    </footer>
    <script src="../js/mobile.js"></script>
</body>
</html>