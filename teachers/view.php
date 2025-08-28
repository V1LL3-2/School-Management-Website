<?php
require_once '../config/database.php';

// Get teacher ID from URL
$teacher_id = $_GET['id'] ?? null;

if (!$teacher_id) {
    header('Location: index.php');
    exit;
}

try {
    // Get teacher details
    $teacher = getRecordById($pdo, 'teachers', 'identification_number', $teacher_id);
    
    if (!$teacher) {
        header('Location: index.php?error=Teacher not found');
        exit;
    }
    
    // Get teacher's assigned courses
    $sql = "SELECT c.emblem, c.name, c.start_day, c.rest_of_day, 
                   f.name as facility_name,
                   COUNT(cl.student_id) as enrollment_count
            FROM courses c
            LEFT JOIN facilities f ON c.facility_id = f.emblem
            LEFT JOIN course_logins cl ON c.emblem = cl.course_id
            WHERE c.teacher_id = ?
            GROUP BY c.emblem
            ORDER BY c.start_day";
    
    $stmt = executeQuery($pdo, $sql, [$teacher_id]);
    $assignedCourses = $stmt->fetchAll();
    
} catch(Exception $e) {
    $error = "Error loading teacher details: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Details - Course Management System</title>
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
                    <li><a href="index.php" class="active"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
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

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($teacher)): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h2><i class="fas fa-chalkboard-teacher"></i> <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['surname']) ?></h2>
                        <p>Teacher Details</p>
                    </div>

                    <div class="detail-info">
                        <div class="info-item">
                            <span class="info-label">Teacher ID</span>
                            <span class="info-value"><?= htmlspecialchars($teacher['identification_number']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">First Name</span>
                            <span class="info-value"><?= htmlspecialchars($teacher['first_name']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Surname</span>
                            <span class="info-value"><?= htmlspecialchars($teacher['surname']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Subject Specialty</span>
                            <span class="info-value"><?= htmlspecialchars($teacher['substance']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Courses Assigned</span>
                            <span class="info-value"><?= count($assignedCourses) ?> courses</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Total Students</span>
                            <span class="info-value">
                                <?= array_sum(array_column($assignedCourses, 'enrollment_count')) ?> students
                            </span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="edit.php?id=<?= $teacher['identification_number'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Teacher
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-header">
                        <h3><i class="fas fa-book"></i> Assigned Courses</h3>
                        <p>All courses for which this teacher is responsible</p>
                    </div>

                    <?php if (count($assignedCourses) > 0): ?>
                        <div class="table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Facility</th>
                                        <th>Students</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignedCourses as $course): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($course['emblem']) ?></td>
                                            <td>
                                                <a href="../courses/view.php?id=<?= urlencode($course['emblem']) ?>">
                                                    <?= htmlspecialchars($course['name']) ?>
                                                </a>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($course['start_day'])) ?></td>
                                            <td><?= date('M j, Y', strtotime($course['rest_of_day'])) ?></td>
                                            <td>
                                                <?php if ($course['facility_name']): ?>
                                                    <?= htmlspecialchars($course['facility_name']) ?>
                                                <?php else: ?>
                                                    <span style="color: #666; font-style: italic;">No facility assigned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($course['enrollment_count']) ?> enrolled</td>
                                            <td>
                                                <a href="../courses/view.php?id=<?= urlencode($course['emblem']) ?>" 
                                                   class="btn btn-primary" title="View Course">
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
                            <i class="fas fa-book" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                            <p>This teacher is not assigned to any courses yet.</p>
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