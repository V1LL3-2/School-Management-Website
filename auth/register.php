<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    // Basic validation only - no restrictions
    if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Check if username already exists
            $sql = "SELECT id FROM users WHERE username = ?";
            $stmt = executeQuery($pdo, $sql, [$username]);
            if ($stmt->fetch()) {
                $error = 'Username already exists. Please choose a different username.';
            } else {
                // Check if email already exists
                $sql = "SELECT id FROM users WHERE email = ?";
                $stmt = executeQuery($pdo, $sql, [$email]);
                if ($stmt->fetch()) {
                    $error = 'Email already exists. Please use a different email address.';
                } else {
                    // Hash password
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Try multiple approaches to insert the user
                    $insertSuccess = false;
                    $attempts = 0;
                    
                    while (!$insertSuccess && $attempts < 3) {
                        try {
                            $attempts++;
                            
                            if ($attempts > 1) {
                                // Fix AUTO_INCREMENT before retry
                                $sql = "SELECT MAX(id) as max_id FROM users";
                                $stmt = $pdo->query($sql);
                                $result = $stmt->fetch();
                                $maxId = ($result['max_id'] ?? 0) + 1;
                                
                                $sql = "ALTER TABLE users AUTO_INCREMENT = $maxId";
                                $pdo->exec($sql);
                            }
                            
                            // Insert new user
                            $sql = "INSERT INTO users (username, email, password_hash, role, first_name, last_name, created_at, is_active) 
                                    VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)";
                            
                            executeQuery($pdo, $sql, [$username, $email, $hashedPassword, $role, $firstName, $lastName]);
                            
                            $insertSuccess = true;
                            $success = 'Account created successfully! You can now sign in.';
                            
                            // Clear form data on success
                            $firstName = $lastName = $username = $email = '';
                            
                        } catch (PDOException $e) {
                            if ($attempts >= 3) {
                                // If still failing after 3 attempts, try with explicit ID
                                try {
                                    $sql = "SELECT MAX(id) as max_id FROM users";
                                    $stmt = $pdo->query($sql);
                                    $result = $stmt->fetch();
                                    $nextId = ($result['max_id'] ?? 0) + 1;
                                    
                                    $sql = "INSERT INTO users (id, username, email, password_hash, role, first_name, last_name, created_at, is_active) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 1)";
                                    
                                    executeQuery($pdo, $sql, [$nextId, $username, $email, $hashedPassword, $role, $firstName, $lastName]);
                                    
                                    $insertSuccess = true;
                                    $success = 'Account created successfully! You can now sign in.';
                                    $firstName = $lastName = $username = $email = '';
                                    
                                } catch (Exception $finalError) {
                                    $error = 'Unable to create account. Please contact system administrator.';
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Course Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 1rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .signup-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }
        
        .signup-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .signup-header h1 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .signup-header p {
            color: #666;
            margin: 0;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            border-color: #667eea;
            outline: none;
        }
        
        .role-selection {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .role-option input[type="radio"] {
            display: none;
        }
        
        .role-option label {
            display: block;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .role-option input:checked + label {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
        
        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }
        
        .auth-links a {
            color: #667eea;
            text-decoration: none;
        }
        
        .auth-links a:hover {
            text-decoration: underline;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5a67d8;
        }
        
        @media (max-width: 480px) {
            .signup-container {
                padding: 1.5rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .role-selection {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <h1><i class="fas fa-graduation-cap"></i> Sign Up</h1>
            <p>Create your account - No restrictions!</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                <br><br>
                <a href="signin.php" class="btn">Go to Sign In</a>
            </div>
        <?php else: ?>

        <form method="POST" id="signupForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" 
                           value="<?= htmlspecialchars($firstName ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" 
                           value="<?= htmlspecialchars($lastName ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" class="form-control" 
                       value="<?= htmlspecialchars($username ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= htmlspecialchars($email ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>I am a *</label>
                <div class="role-selection">
                    <div class="role-option">
                        <input type="radio" id="role_student" name="role" value="student" 
                               <?= (!isset($role) || $role === 'student') ? 'checked' : '' ?>>
                        <label for="role_student">
                            <i class="fas fa-user-graduate"></i><br>
                            Student
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" id="role_teacher" name="role" value="teacher" 
                               <?= (isset($role) && $role === 'teacher') ? 'checked' : '' ?>>
                        <label for="role_teacher">
                            <i class="fas fa-chalkboard-teacher"></i><br>
                            Teacher
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <?php endif; ?>

        <div class="auth-links">
            Already have an account? <a href="signin.php">Sign In</a><br>
            <a href="index.php">Back to Home</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('signupForm');
            const submitBtn = document.getElementById('submitBtn');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            function validatePasswords() {
                if (confirmPassword.value && password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }

            if (password && confirmPassword) {
                password.addEventListener('input', validatePasswords);
                confirmPassword.addEventListener('input', validatePasswords);
            }

            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    if (password.value !== confirmPassword.value) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
                });
            }
        });
    </script>
</body>
</html>