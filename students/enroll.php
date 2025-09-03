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
    
    // Get all available courses (not already enrolled in)
    $sql = "SELECT c.emblem, c.name, c.description, c.start_day, c.rest_of_day,
                   CONCAT(t.first_name, ' ', t.surname) as teacher_name,
                   f.name as facility_name, f.capacity,
                   COUNT(cl.student_id) as current_enrollment
            FROM courses c
            LEFT JOIN teachers t ON c.teacher_id = t.identification_number
            LEFT JOIN facilities f ON c.facility_id = f.emblem
            LEFT JOIN course_logins cl ON c.emblem = cl.course_id
            WHERE c.emblem NOT IN (
                SELECT course_id FROM course_logins WHERE student_id = ?
            )
            GROUP BY c.emblem
            ORDER BY c.start_day, c.name";
    
    $stmt = executeQuery($pdo, $sql, [$student_id]);
    $availableCourses = $stmt->fetchAll();
    
    // Get currently enrolled courses
    $sql = "SELECT c.emblem, c.name, cl.login_date_time
            FROM course_logins cl
            JOIN courses c ON cl.course_id = c.emblem
            WHERE cl.student_id = ?
            ORDER BY c.name";
    
    $stmt = executeQuery($pdo, $sql, [$student_id]);
    $enrolledCourses = $stmt->fetchAll();
    
} catch(Exception $e) {
    $error = "Error loading data: " . $e->getMessage();
}

// Handle enrollment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    
    try {
        // Check if already enrolled
        $sql = "SELECT * FROM course_logins WHERE student_id = ? AND course_id = ?";
        $stmt = executeQuery($pdo, $sql, [$student_id, $course_id]);
        
        if ($stmt->fetch()) {
            $error = "Student is already enrolled in this course!";
        } else {
            // Enroll the student
            $sql = "INSERT INTO course_logins (student_id, course_id, login_date_time) VALUES (?, ?, NOW())";
            executeQuery($pdo, $sql, [$student_id, $course_id]);
            
            header('Location: view.php?id=' . $student_id . '&success=Student enrolled successfully');
            exit;
        }
    } catch(Exception $e) {
        $error = "Error enrolling student: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Student - Course Management System</title>
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
                        <h2><i class="fas fa-user-plus"></i> Enroll Student in Course</h2>
                        <p>Enrolling: <?= htmlspecialchars($student['first_name'] . ' ' . $student['surname']) ?> (<?= htmlspecialchars($student['student_number']) ?>)</p>
                    </div>

                    <div class="action-buttons">
                        <a href="view.php?id=<?= $student_id ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Student Details
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Back to Students List
                        </a>
                    </div>
                </div>

                <!-- Currently Enrolled Courses -->
                <div class="detail-card">
                    <div class="detail-header">
                        <h3><i class="fas fa-check-circle"></i> Currently Enrolled Courses</h3>
                        <p>Courses this student is already enrolled in</p>
                    </div>

                    <?php if (count($enrolledCourses) > 0): ?>
                        <div class="table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Enrollment Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrolledCourses as $course): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($course['emblem']) ?></td>
                                            <td>
                                                <a href="../courses/view.php?id=<?= urlencode($course['emblem']) ?>">
                                                    <?= htmlspecialchars($course['name']) ?>
                                                </a>
                                            </td>
                                            <td><?= date('M j, Y g:i A', strtotime($course['login_date_time'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem;">
                            <i class="fas fa-info-circle" style="font-size: 2rem; color: #ccc; margin-bottom: 1rem;"></i>
                            <p>This student is not enrolled in any courses yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Available Courses for Enrollment -->
                <div class="detail-card">
                    <div class="detail-header">
                        <h3><i class="fas fa-plus-circle"></i> Available Courses</h3>
                        <p>Click "Enroll" to add the student to a course</p>
                    </div>

                    <?php if (count($availableCourses) > 0): ?>
                        <div class="table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Teacher</th>
                                        <th>Facility</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Enrollment</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($availableCourses as $course): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($course['emblem']) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($course['name']) ?></strong>
                                                <?php if ($course['description']): ?>
                                                    <br><small style="color: #666;"><?= htmlspecialchars($course['description']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($course['teacher_name'] ?: 'No teacher assigned') ?></td>
                                            <td>
                                                <?= htmlspecialchars($course['facility_name'] ?: 'No facility assigned') ?>
                                                <?php if ($course['capacity'] && $course['current_enrollment'] >= $course['capacity']): ?>
                                                    <i class="fas fa-exclamation-triangle warning-icon" title="At capacity!"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($course['start_day'])) ?></td>
                                            <td><?= date('M j, Y', strtotime($course['rest_of_day'])) ?></td>
                                            <td>
                                                <?= $course['current_enrollment'] ?>
                                                <?php if ($course['capacity']): ?>
                                                    / <?= $course['capacity'] ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="course_id" value="<?= htmlspecialchars($course['emblem']) ?>">
                                                    <button type="submit" class="btn btn-success" 
                                                            onclick="return confirm('Are you sure you want to enroll <?= htmlspecialchars($student['first_name'] . ' ' . $student['surname']) ?> in <?= htmlspecialchars($course['name']) ?>?')"
                                                            <?= ($course['capacity'] && $course['current_enrollment'] >= $course['capacity']) ? 'title="Course is at capacity but enrollment is still allowed"' : '' ?>>
                                                        <i class="fas fa-user-plus"></i> Enroll
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php 
                        $overCapacityCourses = array_filter($availableCourses, function($course) {
                            return $course['capacity'] && $course['current_enrollment'] >= $course['capacity'];
                        });
                        ?>

                        <?php if (count($overCapacityCourses) > 0): ?>
                            <div class="alert alert-warning" style="margin-top: 1rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Note:</strong> Some courses are at or over capacity. You can still enroll students, 
                                but this may create overcrowding issues. Consider the facility limitations before enrolling.
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem;">
                            <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745; margin-bottom: 1rem;"></i>
                            <p><strong>All courses enrolled!</strong></p>
                            <p>This student is enrolled in all available courses.</p>
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
</body>
</html>