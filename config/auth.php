<?php
// config/auth.php - Updated authentication functions with professional roles
require_once 'database.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user is admin (full access)
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

// Check if user is teacher (can manage courses, view students in their courses)
function isTeacher() {
    return isLoggedIn() && $_SESSION['user_role'] === 'teacher';
}

// Check if user is student (can only view their own data)
function isStudent() {
    return isLoggedIn() && $_SESSION['user_role'] === 'student';
}

// Check if user is staff (limited administrative access)
function isStaff() {
    return isLoggedIn() && $_SESSION['user_role'] === 'staff';
}

// Check if user has teacher-level access or higher
function canManageCourses() {
    return isAdmin() || isTeacher() || isStaff();
}

// Check if user can view all student data
function canViewAllStudents() {
    return isAdmin() || isStaff();
}

// Check if user can manage facilities
function canManageFacilities() {
    return isAdmin() || isStaff();
}

// Check if user can manage teachers
function canManageTeachers() {
    return isAdmin();
}

// Check if user can manage system users
function canManageUsers() {
    return isAdmin();
}

// Get current user info
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['user_role'],
        'first_name' => $_SESSION['first_name'],
        'last_name' => $_SESSION['last_name'],
        'student_id' => $_SESSION['student_id'] ?? null,
        'teacher_id' => $_SESSION['teacher_id'] ?? null
    ];
}

// Login user
function loginUser($pdo, $username, $password) {
    try {
        $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1";
        $stmt = executeQuery($pdo, $sql, [$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['teacher_id'] = $user['teacher_id'];
            
            // Update last login
            $updateSql = "UPDATE users SET last_login = NOW() WHERE id = ?";
            executeQuery($pdo, $updateSql, [$user['id']]);
            
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// Register new user
function registerUser($pdo, $username, $email, $password, $firstName, $lastName, $role = 'student', $studentId = null, $teacherId = null) {
    try {
        // Check if username or email already exists
        $checkSql = "SELECT COUNT(*) FROM users WHERE username = ? OR email = ?";
        $stmt = executeQuery($pdo, $checkSql, [$username, $email]);
        
        if ($stmt->fetchColumn() > 0) {
            return "Username or email already exists";
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO users (username, email, password_hash, role, first_name, last_name, student_id, teacher_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        executeQuery($pdo, $sql, [$username, $email, $passwordHash, $role, $firstName, $lastName, $studentId, $teacherId]);
        
        return true;
    } catch (Exception $e) {
        return "Error creating user: " . $e->getMessage();
    }
}

// Logout user
function logoutUser() {
    session_destroy();
    session_start();
}

// Require login - redirect to login page if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /SchoolManagement/projekti/auth/login.php');
        exit;
    }
}

// Require admin - redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /SchoolManagement/projekti/index.php?error=Access denied - Administrator privileges required');
        exit;
    }
}

// Require teacher level or higher access
function requireTeacherAccess() {
    requireLogin();
    if (!canManageCourses()) {
        header('Location: /SchoolManagement/projekti/index.php?error=Access denied - Teacher privileges required');
        exit;
    }
}

// Require staff level or higher access
function requireStaffAccess() {
    requireLogin();
    if (!isStaff() && !isAdmin()) {
        header('Location: /SchoolManagement/projekti/index.php?error=Access denied - Staff privileges required');
        exit;
    }
}

// Get user role display name
function getRoleDisplayName($role) {
    switch ($role) {
        case 'admin': return 'Administrator';
        case 'teacher': return 'Teacher';
        case 'student': return 'Student';
        case 'staff': return 'Staff';
        default: return 'User';
    }
}

// Get user role color
function getRoleColor($role) {
    switch ($role) {
        case 'admin': return '#dc3545';
        case 'teacher': return '#28a745';
        case 'student': return '#667eea';
        case 'staff': return '#ffc107';
        default: return '#6c757d';
    }
}

// Check password strength
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if (!preg_match('/[A-Za-z]/', $password)) {
        $errors[] = "Password must contain at least one letter";
    }
    
    if (!preg_match('/\d/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return $errors;
}

// Get courses that a teacher is assigned to
function getTeacherCourses($pdo, $teacherId) {
    try {
        $sql = "SELECT * FROM courses WHERE teacher_id = ?";
        $stmt = executeQuery($pdo, $sql, [$teacherId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// Get courses that a student is enrolled in
function getStudentCourses($pdo, $studentId) {
    try {
        $sql = "SELECT c.*, t.first_name as teacher_first_name, t.surname as teacher_surname, f.name as facility_name
                FROM courses c
                LEFT JOIN teachers t ON c.teacher_id = t.identification_number
                LEFT JOIN facilities f ON c.facility_id = f.emblem
                INNER JOIN course_logins cl ON c.emblem = cl.course_id
                WHERE cl.student_id = ?";
        $stmt = executeQuery($pdo, $sql, [$studentId]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}
?>