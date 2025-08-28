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

try {
    // Get student's enrolled courses
    $studentCourses = getStudentCourses($pdo, $currentUser['student_id']);
    
    // Get student details from students table
    $studentDetails = null;
    if ($currentUser['student_id']) {
        $studentDetails = getRecordById($pdo, 'students', 'student_number', $currentUser['student_id']);
    }
    
} catch(Exception $e) {
    $error = "Error loading courses: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Course Management System</title>
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
                    <li><a href="my_courses.php" class="active"><i class="fas fa-book"></i> My Courses</a></li>
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
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-header">
                <h2>My Courses</h2>
                <p>View your enrolled courses and schedules</p>
            </div>

            <?php if ($studentDetails): ?>
                <div class="detail-card" style="margin-bottom: 2rem;">
                    <div class="detail-header">
                        <h3><i class="fas fa-user-graduate"></i> Student Information</h3>
                    </div>
                    <div class="detail-info">
                        <div class="info-item">
                            <span class="info-label">Student Number</span>
                            <span class="info-value"><?= htmlspecialchars($studentDetails['student_number']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?= htmlspecialchars($studentDetails['first_name'] . ' ' . $studentDetails['surname']) ?></span>
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

            <?php if (!empty($studentCourses)): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h3><i class="fas fa-book"></i> Enrolled Courses (<?= count($studentCourses) ?>)</h3>
                    </div>
                    
                    <div class="table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Description</th>
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
                                            <div>
                                                <strong style="color: #667eea;"><?= htmlspecialchars($course['name']) ?></strong><br>
                                                <small style="color: #666;"><?= htmlspecialchars($course['emblem']) ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($course['description'])): ?>
                                                <small style="color: #666;"><?= htmlspecialchars($course['description']) ?></small>
                                            <?php else: ?>
                                                <span style="color: #999; font-style: italic;">No description</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($course['teacher_first_name']): ?>
                                                <div>
                                                    <strong><?= htmlspecialchars($course['teacher_first_name'] . ' ' . $course['teacher_surname']) ?></strong>
                                                </div>
                                            <?php else: ?>
                                                <span style="color: #999; font-style: italic;">No teacher assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($course['facility_name']): ?>
                                                <div>
                                                    <strong><?= htmlspecialchars($course['facility_name']) ?></strong>
                                                </div>
                                            <?php else: ?>
                                                <span style="color: #999; font-style: italic;">TBA</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="font-size: 0.9rem;">
                                                <strong>Start:</strong> <?= date('M j, Y', strtotime($course['start_day'])) ?><br>
                                                <strong>End:</strong> <?= date('M j, Y', strtotime($course['rest_of_day'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $today = time();
                                            $startDate = strtotime($course['start_day']);
                                            $endDate = strtotime($course['rest_of_day']);
                                            
                                            if ($today < $startDate): ?>
                                                <span style="color: #ffc107; font-weight: 600; padding: 3px 8px; background: #fff3cd; border-radius: 12px; font-size: 0.8rem;">
                                                    <i class="fas fa-clock"></i> Upcoming
                                                </span>
                                            <?php elseif ($today >= $startDate && $today <= $endDate): ?>
                                                <span style="color: #28a745; font-weight: 600; padding: 3px 8px; background: #d4edda; border-radius: 12px; font-size: 0.8rem;">
                                                    <i class="fas fa-play"></i> Active
                                                </span>
                                            <?php else: ?>
                                                <span style="color: #6c757d; font-weight: 600; padding: 3px 8px; background: #f8f9fa; border-radius: 12px; font-size: 0.8rem;">
                                                    <i class="fas fa-check"></i> Completed
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Course Statistics -->
                <div class="stats-grid" style="margin-top: 2rem;">
                    <?php
                    $totalCourses = count($studentCourses);
                    $activeCourses = 0;
                    $completedCourses = 0;
                    $upcomingCourses = 0;
                    
                    foreach ($studentCourses as $course) {
                        $today = time();
                        $startDate = strtotime($course['start_day']);
                        $endDate = strtotime($course['rest_of_day']);
                        
                        if ($today < $startDate) {
                            $upcomingCourses++;
                        } elseif ($today >= $startDate && $today <= $endDate) {
                            $activeCourses++;
                        } else {
                            $completedCourses++;
                        }
                    }
                    ?>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $totalCourses ?></h3>
                            <p>Total Enrolled</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #28a745;">
                            <i class="fas fa-play"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $activeCourses ?></h3>
                            <p>Currently Active</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #ffc107;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $upcomingCourses ?></h3>
                            <p>Upcoming</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #6c757d;">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?= $completedCourses ?></h3>
                            <p>Completed</p>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="detail-card">
                    <div style="text-align: center; padding: 3rem;">
                        <i class="fas fa-book" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                        <h3>No Courses Enrolled</h3>
                        <p style="color: #666; margin: 1rem 0;">You are not currently enrolled in any courses.</p>
                        <p style="color: #666;">Contact your academic advisor or school administration to enroll in courses.</p>
                        
                        <div style="margin-top: 2rem;">
                            <a href="../index.php" class="btn btn-primary">
                                <i class="fas fa-home"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Help Section -->
            <div class="detail-card" style="margin-top: 2rem;">
                <div class="detail-header">
                    <h3><i class="fas fa-question-circle"></i> Need Help?</h3>
                </div>
                <div style="padding: 1rem 0; color: #666;">
                    <p><strong>Course Enrollment:</strong> To enroll in new courses, contact your academic advisor or school administration office.</p>
                    <p><strong>Schedule Changes:</strong> If you need to drop or change courses, speak with your advisor during office hours.</p>
                    <p><strong>Technical Issues:</strong> If you're having trouble accessing course materials, contact the IT help desk.</p>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>