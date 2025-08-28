<?php
require_once '../config/database.php';

// Get course ID from URL
$course_id = $_GET['id'] ?? null;

if (!$course_id) {
    header('Location: index.php');
    exit;
}

try {
    // Get course details with teacher and facility information
    $sql = "SELECT c.*, 
                   t.identification_number as teacher_id,
                   CONCAT(t.first_name, ' ', t.surname) as teacher_name,
                   t.substance as teacher_subject,
                   f.emblem as facility_id,
                   f.name as facility_name,
                   f.capacity as facility_capacity
            FROM courses c
            LEFT JOIN teachers t ON c.teacher_id = t.identification_number
            LEFT JOIN facilities f ON c.facility_id = f.emblem
            WHERE c.emblem = ?";
    
    $stmt = executeQuery($pdo, $sql, [$course_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        header('Location: index.php?error=Course not found');
        exit;
    }
    
    // Get enrolled students
    $sql = "SELECT s.student_number, 
                   CONCAT(s.first_name, ' ', s.surname) as student_name,
                   s.grade,
                   cl.login_date_time,
                   YEAR(NOW()) - YEAR(s.birthday) as age
            FROM course_logins cl
            JOIN students s ON cl.student_id = s.student_number
            WHERE cl.course_id = ?
            ORDER BY s.first_name, s.surname";
    
    $stmt = executeQuery($pdo, $sql, [$course_id]);
    $enrolledStudents = $stmt->fetchAll();
    
} catch(Exception $e) {
    $error = "Error loading course details: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Details - Course Management System</title>
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
                    <li><a href="index.php" class="active"><i class="fas fa-book"></i> Courses</a></li>
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

            <?php if (isset($course)): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h2><i class="fas fa-book"></i> <?= htmlspecialchars($course['name']) ?></h2>
                        <p><?= htmlspecialchars($course['emblem']) ?> - Course Details</p>
                    </div>

                    <div class="detail-info">
                        <div class="info-item">
                            <span class="info-label">Course Code</span>
                            <span class="info-value"><?= htmlspecialchars($course['emblem']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Course Name</span>
                            <span class="info-value"><?= htmlspecialchars($course['name']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Description</span>
                            <span class="info-value"><?= htmlspecialchars($course['description'] ?: 'No description available') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Start Date</span>
                            <span class="info-value"><?= date('F j, Y', strtotime($course['start_day'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">End Date</span>
                            <span class="info-value"><?= date('F j, Y', strtotime($course['rest_of_day'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Duration</span>
                            <span class="info-value">
                                <?php
                                $start = new DateTime($course['start_day']);
                                $end = new DateTime($course['rest_of_day']);
                                $duration = $start->diff($end)->days + 1;
                                echo $duration . ' days';
                                ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Teacher</span>
                            <span class="info-value">
                                <?php if ($course['teacher_name']): ?>
                                    <a href="../teachers/view.php?id=<?= $course['teacher_id'] ?>">
                                        <?= htmlspecialchars($course['teacher_name']) ?>
                                    </a>
                                    <br><small><?= htmlspecialchars($course['teacher_subject']) ?></small>
                                <?php else: ?>
                                    No teacher assigned
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Facility</span>
                            <span class="info-value">
                                <?php if ($course['facility_name']): ?>
                                    <a href="../facilities/view.php?id=<?= urlencode($course['facility_id']) ?>">
                                        <?= htmlspecialchars($course['facility_name']) ?>
                                    </a>
                                    <br><small>Capacity: <?= $course['facility_capacity'] ?> students</small>
                                    <?php if (count($enrolledStudents) > $course['facility_capacity']): ?>
                                        <i class="fas fa-exclamation-triangle warning-icon" title="Over capacity!"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    No facility assigned
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Enrollment</span>
                            <span class="info-value">
                                <?= count($enrolledStudents) ?> students enrolled
                                <?php if ($course['facility_capacity']): ?>
                                    (<?= count($enrolledStudents) ?>/<?= $course['facility_capacity'] ?>)
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="edit.php?id=<?= urlencode($course['emblem']) ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Course
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-header">
                        <h3><i class="fas fa-users"></i> Enrolled Students</h3>
                        <p>Students registered for this course (showing names and years)</p>
                    </div>

                    <?php if (count($enrolledStudents) > 0): ?>
                        <div class="table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Student Number</th>
                                        <th>Student Name</th>
                                        <th>Grade</th>
                                        <th>Age</th>
                                        <th>Enrollment Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrolledStudents as $student): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($student['student_number']) ?></td>
                                            <td>
                                                <a href="../students/view.php?id=<?= $student['student_number'] ?>">
                                                    <?= htmlspecialchars($student['student_name']) ?>
                                                </a>
                                            </td>
                                            <td>Grade <?= htmlspecialchars($student['grade']) ?></td>
                                            <td><?= htmlspecialchars($student['age']) ?> years</td>
                                            <td><?= date('M j, Y g:i A', strtotime($student['login_date_time'])) ?></td>
                                            <td>
                                                <a href="../students/view.php?id=<?= $student['student_number'] ?>" 
                                                   class="btn btn-primary" title="View Student">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem;">
                            <i class="fas fa-users" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                            <p>No students enrolled in this course yet.</p>
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