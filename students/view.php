<?php
require_once '../config/database.php';

// Get student ID from URL
$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    header('Location: index.php');
    exit;
}

try {
    // Get student details
    $student = getRecordById($pdo, 'students', 'student_number', $student_id);
    
    if (!$student) {
        header('Location: index.php?error=Student not found');
        exit;
    }
    
    // Get student's enrolled courses
    $sql = "SELECT c.emblem, c.name, c.start_day, c.rest_of_day, 
                   CONCAT(t.first_name, ' ', t.surname) as teacher_name,
                   f.name as facility_name,
                   cl.login_date_time
            FROM course_logins cl
            JOIN courses c ON cl.course_id = c.emblem
            LEFT JOIN teachers t ON c.teacher_id = t.identification_number
            LEFT JOIN facilities f ON c.facility_id = f.emblem
            WHERE cl.student_id = ?
            ORDER BY c.start_day";
    
    $stmt = executeQuery($pdo, $sql, [$student_id]);
    $enrolledCourses = $stmt->fetchAll();
    
} catch(Exception $e) {
    $error = "Error loading student details: " . $e->getMessage();
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
    <title>Student Details - Course Management System</title>
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
                    <li><a href="index.php" class="active"><i class="fas fa-user-graduate"></i> Students</a></li>
                    <li><a href="../teachers/index.php"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                    <li><a href="../courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="../facilities/index.php"><i class="fas fa-building"></i> Facilities</a></li>
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

            <?php if (isset($student)): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h2><i class="fas fa-user-graduate"></i> <?= htmlspecialchars($student['first_name'] . ' ' . $student['surname']) ?></h2>
                        <p>Student Details</p>
                    </div>

                    <div class="detail-info">
                        <div class="info-item">
                            <span class="info-label">Student Number</span>
                            <span class="info-value"><?= htmlspecialchars($student['student_number']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">First Name</span>
                            <span class="info-value"><?= htmlspecialchars($student['first_name']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Surname</span>
                            <span class="info-value"><?= htmlspecialchars($student['surname']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Birthday</span>
                            <span class="info-value"><?= date('F j, Y', strtotime($student['birthday'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Grade</span>
                            <span class="info-value">Grade <?= htmlspecialchars($student['grade']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Age</span>
                            <span class="info-value">
                                <?php
                                $birthDate = new DateTime($student['birthday']);
                                $today = new DateTime();
                                $age = $today->diff($birthDate)->y;
                                echo $age . ' years old';
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="edit.php?id=<?= $student['student_number'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Student
                        </a>
                        <a href="enroll.php?id=<?= $student['student_number'] ?>" class="btn btn-success">
                            <i class="fas fa-plus-circle"></i> Enroll in Course
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-header">
                        <h3><i class="fas fa-book"></i> Enrolled Courses</h3>
                        <p>Courses this student is enrolled in</p>
                    </div>

                    <?php if (count($enrolledCourses) > 0): ?>
                        <div class="table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Teacher</th>
                                        <th>Facility</th>
                                        <th>Enrolled Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrolledCourses as $course): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($course['emblem']) ?></td>
                                            <td>
                                                <a href="../courses/view.php?id=<?= $course['emblem'] ?>">
                                                    <?= htmlspecialchars($course['name']) ?>
                                                </a>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($course['start_day'])) ?></td>
                                            <td><?= date('M j, Y', strtotime($course['rest_of_day'])) ?></td>
                                            <td><?= htmlspecialchars($course['teacher_name'] ?: 'No teacher assigned') ?></td>
                                            <td><?= htmlspecialchars($course['facility_name'] ?: 'No facility assigned') ?></td>
                                            <td><?= date('M j, Y g:i A', strtotime($course['login_date_time'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem;">
                            <i class="fas fa-book" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                            <p>This student is not enrolled in any courses yet.</p>
                            <a href="enroll.php?id=<?= $student['student_number'] ?>" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Enroll in First Course
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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