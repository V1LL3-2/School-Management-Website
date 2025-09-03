<?php
require_once '../config/database.php';
require_once '../config/auth.php';

// Only admins can edit users
requireAdmin();

// Get user ID from URL
$userId = $_GET['id'] ?? null;

if (!$userId) {
    header('Location: index.php?error=User ID is required');
    exit;
}

try {
    // Get user details
    $user = getRecordById($pdo, 'users', 'id', $userId);
    
    if (!$user) {
        header('Location: index.php?error=User not found');
        exit;
    }
} catch(Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit;
}

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $role = $_POST['role'];
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($firstName)) {
        $errors[] = "First name is required";
    }
    
    if (empty($lastName)) {
        $errors[] = "Last name is required";
    }
    
    if (!in_array($role, ['admin', 'user'])) {
        $errors[] = "Invalid role selected";
    }
    
    // Check if username or email already exists (excluding current user)
    if (empty($errors)) {
        try {
            $checkSql = "SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?";
            $stmt = executeQuery($pdo, $checkSql, [$username, $email, $userId]);
            
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Username or email already exists";
            }
        } catch (Exception $e) {
            $errors[] = "Error checking username/email: " . $e->getMessage();
        }
    }
    
    // Password validation (only if new password is provided)
    if (!empty($newPassword)) {
        $passwordErrors = validatePassword($newPassword);
        $errors = array_merge($errors, $passwordErrors);
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = "Passwords do not match";
        }
    }
    
    // If no validation errors, update the user
    if (empty($errors)) {
        try {
            if (!empty($newPassword)) {
                // Update with new password
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, is_active = ?, password_hash = ? WHERE id = ?";
                executeQuery($pdo, $sql, [$username, $email, $firstName, $lastName, $role, $isActive, $passwordHash, $userId]);
            } else {
                // Update without changing password
                $sql = "UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, is_active = ? WHERE id = ?";
                executeQuery($pdo, $sql, [$username, $email, $firstName, $lastName, $role, $isActive, $userId]);
            }
            
            header('Location: index.php?success=User updated successfully');
            exit;
        } catch (Exception $e) {
            $errors[] = "Error updating user: " . $e->getMessage();
        }
    }
} else {
    // Pre-populate form with existing data
    $_POST['username'] = $user['username'];
    $_POST['email'] = $user['email'];
    $_POST['first_name'] = $user['first_name'];
    $_POST['last_name'] = $user['last_name'];
    $_POST['role'] = $user['role'];
    $_POST['is_active'] = $user['is_active'];
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
    <title>Edit User - Course Management System</title>
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
                            <a href="../index.php?logout=1" style="color: white; text-decoration: none;">
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
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul style="margin: 0; padding-left: 1rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-header">
                <h2>Edit User</h2>
                <p>Update user information for <?= htmlspecialchars($user['username']) ?></p>
            </div>

            <div class="form-container">
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" id="first_name" name="first_name" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" id="last_name" name="last_name" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="role">User Role *</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="user" <?= ($_POST['role'] ?? '') == 'user' ? 'selected' : '' ?>>
                                    Regular User
                                </option>
                                <option value="admin" <?= ($_POST['role'] ?? '') == 'admin' ? 'selected' : '' ?>>
                                    Administrator
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="is_active">Account Status</label>
                            <div style="margin-top: 0.5rem;">
                                <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal;">
                                    <input type="checkbox" id="is_active" name="is_active" 
                                           <?= ($_POST['is_active'] ?? 0) ? 'checked' : '' ?>>
                                    Active Account
                                </label>
                                <small style="color: #666;">Uncheck to deactivate the user</small>
                            </div>
                        </div>
                    </div>

                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 5px; margin: 1.5rem 0;">
                        <h4 style="margin-bottom: 1rem; color: #333;">
                            <i class="fas fa-lock"></i> Change Password (Optional)
                        </h4>
                        <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">
                            Leave blank to keep current password unchanged.
                        </p>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" 
                                       placeholder="Enter new password">
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                       placeholder="Confirm new password">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Update User
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <?php if ($userId != $currentUser['id']): ?>
                            <a href="index.php?action=delete&id=<?= $userId ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Are you sure you want to delete this user?')">
                                <i class="fas fa-trash"></i> Delete User
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- User Information Summary -->
            <div class="detail-card" style="margin-top: 2rem;">
                <div class="detail-header">
                    <h3><i class="fas fa-info-circle"></i> Current User Information</h3>
                </div>
                <div style="padding: 1rem 0;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: #333;">
                                <?= htmlspecialchars($user['username']) ?>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">Current Username</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: <?= $user['role'] === 'admin' ? '#dc3545' : '#667eea' ?>;">
                                <?= strtoupper($user['role']) ?>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">Current Role</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: <?= $user['is_active'] ? '#28a745' : '#6c757d' ?>;">
                                <?= $user['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">Current Status</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1rem; font-weight: bold; color: #333;">
                                <?php if ($user['last_login']): ?>
                                    <?= date('M j, Y', strtotime($user['last_login'])) ?>
                                <?php else: ?>
                                    Never
                                <?php endif; ?>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">Last Login</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Management System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Show/hide password confirmation based on new password input
        document.getElementById('new_password').addEventListener('input', function() {
            const confirmField = document.getElementById('confirm_password');
            if (this.value) {
                confirmField.required = true;
                confirmField.style.borderColor = '#667eea';
            } else {
                confirmField.required = false;
                confirmField.value = '';
                confirmField.style.borderColor = '';
            }
        });
    </script>
    <script src="../js/mobile.js"></script>
</body>
</html>