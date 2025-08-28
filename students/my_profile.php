<?php
require_once '../config/database.php';
require_once '../config/auth.php';

// Only students can access this page
requireLogin();

if (!isStudent()) {
    header('Location: ../index.php?error=Access denied - Students only');
    exit;
}

$currentUser = getCurrentUser();
$errors = [];
$success = '';

try {
    // Get student details from students table
    $studentDetails = null;
    if ($currentUser['student_id']) {
        $studentDetails = getRecordById($pdo, 'students', 'student_number', $currentUser['student_id']);
    }
    
    // Get user account details
    $userDetails = getRecordById($pdo, 'users', 'id', $currentUser['id']);
    
} catch(Exception $e) {
    $error = "Error loading profile: " . $e->getMessage();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);
    
    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists (excluding current user)
    if (empty($errors)) {
        try {
            $checkSql = "SELECT COUNT(*) FROM users WHERE email = ? AND id != ?";
            $stmt = executeQuery($pdo, $checkSql, [$email, $currentUser['id']]);
            
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Email already exists";
            }
        } catch (Exception $e) {
            $errors[] = "Error checking email: " . $e->getMessage();
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
    
    // If no validation errors, update the profile
    if (empty($errors)) {
        try {
            if (!empty($newPassword)) {
                // Update with new password
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET email = ?, password_hash = ? WHERE id = ?";
                executeQuery($pdo, $sql, [$email, $passwordHash, $currentUser['id']]);
            } else {
                // Update without changing password
                $sql = "UPDATE users SET email = ? WHERE id = ?";
                executeQuery($pdo, $sql, [$email, $currentUser['id']]);
            }
            
            // Update session email
            $_SESSION['email'] = $email;
            $success = "Profile updated successfully!";
            
            // Refresh user details
            $userDetails = getRecordById($pdo, 'users', 'id', $currentUser['id']);
            
        } catch (Exception $e) {
            $errors[] = "Error updating profile: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Course Management System</title>
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
                    <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
                    <li class="user-menu">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <span style="color: white;">
                                <i class="fas fa-user"></i> 
                                <?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?>
                                <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; margin-left: 0.5rem;">STUDENT</span>
                            </span>
                            <a href="../index.php?logout=1" style="color: white; text-decoration: none;" onclick="return confirm('Are you sure you want to logout?')">
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

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-header">
                <h2>My Profile</h2>
                <p>View and update your account information</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <!-- Student Information (Read-only) -->
                <?php if ($studentDetails): ?>
                    <div class="detail-card">
                        <div class="detail-header">
                            <h3><i class="fas fa-user-graduate"></i> Student Information</h3>
                            <p style="color: #666; font-size: 0.9rem;">This information is managed by school administration</p>
                        </div>
                        <div class="detail-info">
                            <div class="info-item">
                                <span class="info-label">Student Number</span>
                                <span class="info-value"><?= htmlspecialchars($studentDetails['student_number']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">First Name</span>
                                <span class="info-value"><?= htmlspecialchars($studentDetails['first_name']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Surname</span>
                                <span class="info-value"><?= htmlspecialchars($studentDetails['surname']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Birthday</span>
                                <span class="info-value"><?= date('F j, Y', strtotime($studentDetails['birthday'])) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Grade</span>
                                <span class="info-value">Grade <?= htmlspecialchars($studentDetails['grade']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Age</span>
                                <span class="info-value">
                                    <?php
                                    $birthDate = new DateTime($studentDetails['birthday']);
                                    $today = new DateTime();
                                    $age = $today->diff($birthDate)->y;
                                    echo $age . ' years old';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Account Settings (Editable) -->
                <div class="detail-card">
                    <div class="detail-header">
                        <h3><i class="fas fa-cog"></i> Account Settings</h3>
                        <p style="color: #666; font-size: 0.9rem;">Update your login credentials</p>
                    </div>
                    
                    <div style="padding: 1rem 0;">
                        <form method="POST">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       value="<?= htmlspecialchars($userDetails['username']) ?>" 
                                       readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                                <small style="color: #666;">Username cannot be changed</small>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?= htmlspecialchars($userDetails['email']) ?>" required>
                                <small style="color: #666;">Used for important notifications and password recovery</small>
                            </div>

                            <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 5px; margin: 1.5rem 0;">
                                <h4 style="margin-bottom: 1rem; color: #333;">
                                    <i class="fas fa-lock"></i> Change Password (Optional)
                                </h4>
                                <p style="color: #666; margin-bottom: 1rem; font-size: 0.9rem;">
                                    Leave blank to keep current password unchanged.
                                </p>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" 
                                           placeholder="Enter new password">
                                    <small style="color: #666;">At least 6 characters with letters and numbers</small>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                           placeholder="Confirm new password">
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                                <a href="../index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Account Activity -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3><i class="fas fa-history"></i> Account Activity</h3>
                </div>
                <div style="padding: 1rem 0;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1rem; font-weight: bold; color: #333;">
                                <?= htmlspecialchars($userDetails['username']) ?>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">Username</div>
                        </div>
                        
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1rem; font-weight: bold; color: #667eea;">
                                STUDENT
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">Account Type</div>
                        </div>
                        
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1rem; font-weight: bold; color: #28a745;">
                                ACTIVE
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">Account Status</div>
                        </div>
                        
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 0.9rem; font-weight: bold; color: #333;">
                                <?php if ($userDetails['last_login']): ?>
                                    <?= date('M j, Y g:i A', strtotime($userDetails['last_login'])) ?>
                                <?php else: ?>
                                    Never
                                <?php endif; ?>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">Last Login</div>
                        </div>
                        
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 0.9rem; font-weight: bold; color: #333;">
                                <?= date('M j, Y', strtotime($userDetails['created_at'])) ?>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">Member Since</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help & Support -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3><i class="fas fa-question-circle"></i> Need Help?</h3>
                </div>
                <div style="padding: 1rem 0; color: #666;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                        <div>
                            <h4 style="color: #333; margin-bottom: 0.5rem;">
                                <i class="fas fa-user-edit"></i> Profile Changes
                            </h4>
                            <p>You can update your email and password anytime. Your student information (name, grade, etc.) can only be changed by school administration.</p>
                        </div>
                        
                        <div>
                            <h4 style="color: #333; margin-bottom: 0.5rem;">
                                <i class="fas fa-book"></i> Course Enrollment
                            </h4>
                            <p>To enroll in new courses or make schedule changes, contact your academic advisor during office hours.</p>
                        </div>
                        
                        <div>
                            <h4 style="color: #333; margin-bottom: 0.5rem;">
                                <i class="fas fa-headset"></i> Technical Support
                            </h4>
                            <p>Having trouble with your account or the system? Contact the IT help desk for assistance.</p>
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