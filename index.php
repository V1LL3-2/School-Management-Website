<?php
require_once 'config/database.php';

// Get summary statistics
try {
    $studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $teacherCount = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $courseCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    $facilityCount = $pdo->query("SELECT COUNT(*) FROM facilities")->fetchColumn();
    $enrollmentCount = $pdo->query("SELECT COUNT(*) FROM course_logins")->fetchColumn();
} catch(Exception $e) {
    $error = "Error loading dashboard data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <h1><i class="fas fa-graduation-cap"></i> Course Management System</h1>
                <ul class="nav-menu">
                    <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="students/index.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                    <li><a href="teachers/index.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                    <li><a href="courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="facilities/index.php"><i class="fas fa-building"></i> Facilities</a></li>
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
                <h2>Dashboard Overview</h2>
                <p>Welcome to the Course Management System</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $studentCount ?? 0 ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $teacherCount ?? 0 ?></h3>
                        <p>Total Teachers</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $courseCount ?? 0 ?></h3>
                        <p>Total Courses</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $facilityCount ?? 0 ?></h3>
                        <p>Total Facilities</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $enrollmentCount ?? 0 ?></h3>
                        <p>Total Enrollments</p>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                    <a href="students/add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Student
                    </a>
                    <a href="teachers/add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Teacher
                    </a>
                    <a href="courses/add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Course
                    </a>
                    <a href="facilities/add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Facility
                    </a>
                </div>
            </div>

            <div class="recent-activity">
                <h3>Recent Enrollments</h3>
                <div class="activity-list">
                    <?php
                    try {
                        $recentEnrollments = $pdo->query("
                            SELECT cl.login_date_time, 
                                   CONCAT(s.first_name, ' ', s.surname) as student_name,
                                   c.name as course_name
                            FROM course_logins cl
                            JOIN students s ON cl.student_id = s.student_number
                            JOIN courses c ON cl.course_id = c.emblem
                            ORDER BY cl.login_date_time DESC
                            LIMIT 5
                        ")->fetchAll();
                        
                        foreach ($recentEnrollments as $enrollment):
                    ?>
                        <div class="activity-item">
                            <i class="fas fa-sign-in-alt"></i>
                            <div class="activity-content">
                                <p><strong><?= htmlspecialchars($enrollment['student_name']) ?></strong> enrolled in <strong><?= htmlspecialchars($enrollment['course_name']) ?></strong></p>
                                <span class="activity-time"><?= date('M j, Y g:i A', strtotime($enrollment['login_date_time'])) ?></span>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    } catch(Exception $e) {
                        echo '<p>No recent activity available</p>';
                    }
                    ?>
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