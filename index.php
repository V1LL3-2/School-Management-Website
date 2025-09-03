<?php
require_once 'config/database.php';
require_once 'config/auth.php';


// Handle logout
if (isset($_GET['logout'])) {
    logoutUser();
    header('Location: index.php?message=You have been logged out successfully');
    exit;
}

// Check if user is logged in
$isLoggedIn = isLoggedIn();
$currentUser = null;

if ($isLoggedIn) {
    $currentUser = getCurrentUser();
    
    // Get statistics based on user role
    try {
        if (isAdmin()) {
            // Admin sees everything
            $studentCount = executeQuery($pdo, "SELECT COUNT(*) FROM students")->fetchColumn();
            $teacherCount = executeQuery($pdo, "SELECT COUNT(*) FROM teachers")->fetchColumn();
            $courseCount = executeQuery($pdo, "SELECT COUNT(*) FROM courses")->fetchColumn();
            $facilityCount = executeQuery($pdo, "SELECT COUNT(*) FROM facilities")->fetchColumn();
            $userCount = executeQuery($pdo, "SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
            
            // Get recent enrollments
            $recentEnrollments = executeQuery($pdo, 
                "SELECT cl.login_date_time, 
                        CONCAT(s.first_name, ' ', s.surname) as student_name,
                        c.name as course_name
                 FROM course_logins cl
                 JOIN students s ON cl.student_id = s.student_number
                 JOIN courses c ON cl.course_id = c.emblem
                 ORDER BY cl.login_date_time DESC
                 LIMIT 5"
            )->fetchAll();
            
        } elseif (isTeacher()) {
            // Teachers see their own courses and students
            $teacherCourses = getTeacherCourses($pdo, $currentUser['teacher_id']);
            $courseCount = count($teacherCourses);
            $facilityCount = executeQuery($pdo, "SELECT COUNT(*) FROM facilities")->fetchColumn();
            
            // Get students in teacher's courses
            $studentCount = 0;
            if ($currentUser['teacher_id']) {
                $studentCount = executeQuery($pdo, 
                    "SELECT COUNT(DISTINCT cl.student_id) 
                     FROM course_logins cl
                     JOIN courses c ON cl.course_id = c.emblem
                     WHERE c.teacher_id = ?", 
                    [$currentUser['teacher_id']]
                )->fetchColumn();
            }
            
            $recentEnrollments = [];
            
        } elseif (isStudent()) {
            // Students see only their own data
            $studentCourses = getStudentCourses($pdo, $currentUser['student_id']);
            $courseCount = count($studentCourses);
            $studentCount = 1; // Just themselves
            $teacherCount = 0;
            $facilityCount = 0;
            $recentEnrollments = [];
            
        } else {
            // Staff or other roles
            $courseCount = executeQuery($pdo, "SELECT COUNT(*) FROM courses")->fetchColumn();
            $facilityCount = executeQuery($pdo, "SELECT COUNT(*) FROM facilities")->fetchColumn();
            $studentCount = executeQuery($pdo, "SELECT COUNT(*) FROM students")->fetchColumn();
            $teacherCount = executeQuery($pdo, "SELECT COUNT(*) FROM teachers")->fetchColumn();
            $recentEnrollments = [];
        }
        
    } catch(Exception $e) {
        $error = "Error loading dashboard data: " . $e->getMessage();
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_submit'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $loginError = "Please fill in all fields";
    } else {
        if (loginUser($pdo, $username, $password)) {
            header('Location: index.php?success=Welcome back!');
            exit;
        } else {
            $loginError = "Invalid username/email or password";
        }
    }
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_submit'])) {
    $username = trim($_POST['reg_username']);
    $email = trim($_POST['reg_email']);
    $password = $_POST['reg_password'];
    $confirmPassword = $_POST['reg_confirm_password'];
    $firstName = trim($_POST['reg_first_name']);
    $lastName = trim($_POST['reg_last_name']);
    $role = $_POST['reg_role'] ?? 'student';
    
    $regErrors = [];
    
    if (empty($username) || empty($email) || empty($firstName) || empty($lastName)) {
        $regErrors[] = "All fields are required";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $regErrors[] = "Invalid email format";
    }
    
    if (strlen($username) < 3) {
        $regErrors[] = "Username must be at least 3 characters long";
    }
    
    if (!in_array($role, ['student', 'teacher'])) {
        $regErrors[] = "Invalid role selected";
    }
    
    if (empty($password)) {
        $regErrors[] = "Password is required";
    } else {
        $passwordErrors = validatePassword($password);
        $regErrors = array_merge($regErrors, $passwordErrors);
    }
    
    if ($password !== $confirmPassword) {
        $regErrors[] = "Passwords do not match";
    }
    
    if (empty($regErrors)) {
        $result = registerUser($pdo, $username, $email, $password, $firstName, $lastName, $role);
        
        if ($result === true) {
            $regSuccess = "Registration successful! You can now log in.";
        } else {
            $regErrors[] = $result;
        }
    }
}
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
    <title><?= $isLoggedIn ? 'Dashboard - ' . getRoleDisplayName($currentUser['role']) : 'Course Management System' ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }
        
        .close:hover {
            opacity: 0.7;
        }
        
        .auth-tabs {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }
        
        .auth-tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1rem;
            color: #666;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }
        
        .auth-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
            font-weight: 600;
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
        }
        
        .guest-header {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin-bottom: 3rem;
        }
        
        .guest-header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .guest-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .guest-actions {
            text-align: center;
            margin: 2rem 0;
        }
        
        .guest-actions .btn {
            margin: 0 1rem;
            font-size: 1.1rem;
            padding: 1rem 2rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        .role-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <?php if ($isLoggedIn): ?>
        <!-- LOGGED IN VIEW -->
        <header>
            <nav class="navbar">
                <div class="nav-container">
                    <h1><i class="fas fa-graduation-cap"></i> Course Management System</h1>
                    <ul class="nav-menu">
                        <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                        
                        <!-- Admin has access to everything -->
                        <?php if (isAdmin()): ?>
                            <li><a href="students/index.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                            <li><a href="teachers/index.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                            <li><a href="courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
                            <li><a href="facilities/index.php"><i class="fas fa-building"></i> Facilities</a></li>
                        
                        <!-- Teachers can manage courses and view facilities -->
                        <?php elseif (isTeacher()): ?>
                            <li><a href="courses/index.php"><i class="fas fa-book"></i> My Courses</a></li>
                            <li><a href="facilities/index.php"><i class="fas fa-building"></i> Facilities</a></li>
                        
                        <!-- Students can only view their courses -->
                        <?php elseif (isStudent()): ?>
                            <li><a href="students/my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
                        
                        <!-- Staff has limited admin access -->
                        <?php elseif (isStaff()): ?>
                            <li><a href="students/index.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                            <li><a href="courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
                            <li><a href="facilities/index.php"><i class="fas fa-building"></i> Facilities</a></li>
                        <?php endif; ?>
                        
                        <li class="user-menu">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="color: white;">
                                    <i class="fas fa-user"></i> 
                                    <?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?>
                                    <span class="role-badge" style="background-color: <?= getRoleColor($currentUser['role']) ?>; color: white;">
                                        <?= strtoupper($currentUser['role']) ?>
                                    </span>
                                </span>
                                <a href="?logout=1" style="color: white; text-decoration: none;" onclick="return confirm('Are you sure you want to logout?')">
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
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <div class="dashboard-header">
                    <h2>Welcome back, <?= htmlspecialchars($currentUser['first_name']) ?>!</h2>
                    <p><?= getRoleDisplayName($currentUser['role']) ?> Dashboard</p>
                </div>

                <div class="stats-grid">
                    <?php if (isAdmin()): ?>
                        <!-- Admin sees all statistics -->
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $studentCount ?></h3>
                                <p>Total Students</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $teacherCount ?></h3>
                                <p>Total Teachers</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $courseCount ?></h3>
                                <p>Active Courses</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $facilityCount ?></h3>
                                <p>Facilities</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $userCount ?></h3>
                                <p>System Users</p>
                            </div>
                        </div>
                        
                    <?php elseif (isTeacher()): ?>
                        <!-- Teachers see their course statistics -->
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $courseCount ?></h3>
                                <p>My Courses</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $studentCount ?></h3>
                                <p>My Students</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $facilityCount ?></h3>
                                <p>Available Facilities</p>
                            </div>
                        </div>
                        
                    <?php elseif (isStudent()): ?>
                        <!-- Students see only their own data -->
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $courseCount ?></h3>
                                <p>Enrolled Courses</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-content">
                                <h3>
                                    <?php 
                                    // Count active courses (not ended)
                                    $activeCourses = 0;
                                    if (isset($studentCourses)) {
                                        foreach ($studentCourses as $course) {
                                            if (strtotime($course['rest_of_day']) >= time()) {
                                                $activeCourses++;
                                            }
                                        }
                                    }
                                    echo $activeCourses;
                                    ?>
                                </h3>
                                <p>Active Courses</p>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- Staff sees limited statistics -->
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $studentCount ?></h3>
                                <p>Students</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $courseCount ?></h3>
                                <p>Courses</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $facilityCount ?></h3>
                                <p>Facilities</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions based on role -->
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <?php if (isAdmin()): ?>
                            <!-- Admin can do everything -->
                            <a href="students/add.php" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Add New Student
                            </a>
                            <a href="teachers/add.php" class="btn btn-primary">
                                <i class="fas fa-user-tie"></i> Add New Teacher
                            </a>
                            <a href="courses/add.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create New Course
                            </a>
                            <a href="facilities/add.php" class="btn btn-primary">
                                <i class="fas fa-building"></i> Add New Facility
                            </a>
                            <a href="users/index.php" class="btn btn-warning">
                                <i class="fas fa-users-cog"></i> Manage Users
                            </a>
                            
                        <?php elseif (isTeacher()): ?>
                            <!-- Teachers can create courses and view facilities -->
                            <a href="courses/add.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create New Course
                            </a>
                            <a href="courses/index.php" class="btn btn-success">
                                <i class="fas fa-chalkboard"></i> View My Courses
                            </a>
                            <a href="facilities/index.php" class="btn btn-secondary">
                                <i class="fas fa-building"></i> View Facilities
                            </a>
                            
                        <?php elseif (isStudent()): ?>
                            <!-- Students can only view their courses -->
                            <a href="students/my_courses.php" class="btn btn-primary">
                                <i class="fas fa-book"></i> View My Courses
                            </a>
                            <a href="students/my_profile.php" class="btn btn-secondary">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            
                        <?php elseif (isStaff()): ?>
                            <!-- Staff has limited admin access -->
                            <a href="students/index.php" class="btn btn-primary">
                                <i class="fas fa-users"></i> Manage Students
                            </a>
                            <a href="courses/index.php" class="btn btn-primary">
                                <i class="fas fa-book"></i> Manage Courses
                            </a>
                            <a href="facilities/add.php" class="btn btn-success">
                                <i class="fas fa-building"></i> Add Facility
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activity (Admin and Teachers only) -->
                <?php if ((isAdmin() || isTeacher()) && !empty($recentEnrollments)): ?>
                    <div class="recent-activity">
                        <h3>Recent Activity</h3>
                        <div class="activity-list">
                            <?php foreach ($recentEnrollments as $enrollment): ?>
                                <div class="activity-item">
                                    <i class="fas fa-user-plus"></i>
                                    <div class="activity-content">
                                        <p><?= htmlspecialchars($enrollment['student_name']) ?> enrolled in <?= htmlspecialchars($enrollment['course_name']) ?></p>
                                        <span class="activity-time"><?= date('M j, Y g:i A', strtotime($enrollment['login_date_time'])) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Student-specific course list -->
                <?php if (isStudent() && isset($studentCourses) && !empty($studentCourses)): ?>
                    <div class="recent-activity">
                        <h3>My Courses</h3>
                        <div class="table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Teacher</th>
                                        <th>Facility</th>
                                        <th>Schedule</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($studentCourses as $course): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($course['name']) ?></strong><br>
                                                <small style="color: #666;"><?= htmlspecialchars($course['emblem']) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($course['teacher_first_name']): ?>
                                                    <?= htmlspecialchars($course['teacher_first_name'] . ' ' . $course['teacher_surname']) ?>
                                                <?php else: ?>
                                                    <span style="color: #999;">No teacher assigned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($course['facility_name'] ?: 'TBA') ?></td>
                                            <td>
                                                <?= date('M j', strtotime($course['start_day'])) ?> - 
                                                <?= date('M j, Y', strtotime($course['rest_of_day'])) ?>
                                            </td>
                                            <td>
                                                <?php if (strtotime($course['rest_of_day']) >= time()): ?>
                                                    <span style="color: #28a745; font-weight: 600;">Active</span>
                                                <?php else: ?>
                                                    <span style="color: #6c757d; font-weight: 600;">Completed</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Role-specific notices -->
                <?php if (isStudent()): ?>
                    <div class="detail-card">
                        <div class="detail-header">
                            <h3><i class="fas fa-info-circle"></i> Student Information</h3>
                        </div>
                        <div style="padding: 1rem 0; color: #666;">
                            <p><strong>What you can do:</strong></p>
                            <ul style="margin-left: 2rem;">
                                <li>View your enrolled courses and schedules</li>
                                <li>Check course details and teacher information</li>
                                <li>Update your profile information</li>
                                <li>View facility information for your courses</li>
                            </ul>
                            <p style="margin-top: 1rem;"><em>Need to enroll in new courses? Contact your academic advisor or school administration.</em></p>
                        </div>
                    </div>
                    
                <?php elseif (isTeacher()): ?>
                    <div class="detail-card">
                        <div class="detail-header">
                            <h3><i class="fas fa-chalkboard-teacher"></i> Teacher Information</h3>
                        </div>
                        <div style="padding: 1rem 0; color: #666;">
                            <p><strong>What you can do:</strong></p>
                            <ul style="margin-left: 2rem;">
                                <li>Create and manage your courses</li>
                                <li>View students enrolled in your courses</li>
                                <li>Check facility availability and capacity</li>
                                <li>Update course information and schedules</li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

    <?php else: ?>
        <!-- GUEST VIEW -->
        <div class="guest-header">
            <div class="container">
                <div style="font-size: 4rem; margin-bottom: 1rem;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1>Course Management System</h1>
                <p>Professional school management for students, teachers and administrators</p>
                
                <div class="guest-actions">
                    <button onclick="openModal('login')" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                    <button onclick="openModal('register')" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </button>
                </div>
            </div>
        </div>

        <main class="main-content">
            <div class="container">
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['message']) ?>
                    </div>
                <?php endif; ?>

                <div style="text-align: center; padding: 3rem 0;">
                    <h2>Professional School Management</h2>
                    <div class="stats-grid" style="margin-top: 2rem;">
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #dc3545;">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Administrators</h3>
                                <p>Complete system management and oversight</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #28a745;">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Teachers</h3>
                                <p>Course creation and student management</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #667eea;">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Students</h3>
                                <p>Access to courses and academic information</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon" style="background: #ffc107;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3>Staff</h3>
                                <p>Administrative support and coordination</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Authentication Modal -->
        <div id="authModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-graduation-cap"></i> Course Management System</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="auth-tabs">
                        <button class="auth-tab active" onclick="switchTab('login')">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </button>
                        <button class="auth-tab" onclick="switchTab('register')">
                            <i class="fas fa-user-plus"></i> Sign Up
                        </button>
                    </div>

                    <!-- Login Form -->
                    <div id="loginForm" class="auth-form active">
                        <?php if (isset($loginError)): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($loginError) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-group">
                                <label for="username">Username or Email</label>
                                <input type="text" id="username" name="username" class="form-control" 
                                       placeholder="Enter your username or email" required>
                            </div>

                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" 
                                       placeholder="Enter your password" required>
                            </div>

                            <div class="form-group">
                                <button type="submit" name="login_submit" class="btn btn-primary" style="width: 100%;">
                                    <i class="fas fa-sign-in-alt"></i> Sign In
                                </button>
                            </div>
                        </form>

                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 1rem;">
                            <p style="margin: 0; font-size: 0.85rem; color: #666;"><strong>Demo Accounts:</strong></p>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.8rem; color: #666;">
                                Admin: admin / admin123<br>
                                Teacher: teacher1 / user123<br>
                                Student: (register as new user)
                            </p>
                        </div>
                    </div>

                    <!-- Register Form -->
                    <div id="registerForm" class="auth-form">
                        <?php if (!empty($regErrors)): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <ul style="margin: 0; padding-left: 1rem;">
                                    <?php foreach ($regErrors as $error): ?>
                                        <li><?= htmlspecialchars($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($regSuccess)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($regSuccess) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="reg_first_name">First Name *</label>
                                    <input type="text" id="reg_first_name" name="reg_first_name" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['reg_first_name'] ?? '') ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="reg_last_name">Last Name *</label>
                                    <input type="text" id="reg_last_name" name="reg_last_name" class="form-control" 
                                           value="<?= htmlspecialchars($_POST['reg_last_name'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="reg_username">Username *</label>
                                <input type="text" id="reg_username" name="reg_username" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['reg_username'] ?? '') ?>" 
                                       placeholder="Choose a unique username" required>
                            </div>

                            <div class="form-group">
                                <label for="reg_email">Email *</label>
                                <input type="email" id="reg_email" name="reg_email" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['reg_email'] ?? '') ?>" 
                                       placeholder="Enter your email address" required>
                            </div>

                            <div class="form-group">
                                <label for="reg_role">I am a *</label>
                                <select id="reg_role" name="reg_role" class="form-control" required>
                                    <option value="">Select your role</option>
                                    <option value="student" <?= ($_POST['reg_role'] ?? '') == 'student' ? 'selected' : '' ?>>
                                        Student
                                    </option>
                                    <option value="teacher" <?= ($_POST['reg_role'] ?? '') == 'teacher' ? 'selected' : '' ?>>
                                        Teacher
                                    </option>
                                </select>
                                <small style="color: #666;">Staff and Admin accounts are created by administrators</small>
                            </div>

                            <div class="form-group">
                                <label for="reg_password">Password *</label>
                                <input type="password" id="reg_password" name="reg_password" class="form-control" 
                                       placeholder="Create a strong password" required>
                                <small style="color: #666;">At least 6 characters with letters and numbers</small>
                            </div>

                            <div class="form-group">
                                <label for="reg_confirm_password">Confirm Password *</label>
                                <input type="password" id="reg_confirm_password" name="reg_confirm_password" class="form-control" 
                                       placeholder="Re-enter your password" required>
                            </div>

                            <div class="form-group">
                                <button type="submit" name="register_submit" class="btn btn-success" style="width: 100%;">
                                    <i class="fas fa-user-plus"></i> Create Account
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Management System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function openModal(tab = 'login') {
            document.getElementById('authModal').style.display = 'block';
            switchTab(tab);
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('authModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function switchTab(tab) {
            // Remove active class from all tabs and forms
            document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
            
            // Add active class to selected tab and form
            document.querySelector(`[onclick="switchTab('${tab}')"]`).classList.add('active');
            document.getElementById(tab + 'Form').classList.add('active');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('authModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Auto-open modal if there are form errors
        <?php if (isset($loginError)): ?>
            openModal('login');
        <?php elseif (!empty($regErrors) || isset($regSuccess)): ?>
            openModal('register');
        <?php endif; ?>
    </script>
    <script src="../js/mobile.js"></script>
</body>
</html>