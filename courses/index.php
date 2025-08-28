<?php
require_once '../config/database.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $course_id = $_GET['delete'];
    try {
        deleteRecord($pdo, 'courses', 'emblem', $course_id);
        $success = "Course deleted successfully!";
    } catch(Exception $e) {
        $error = "Error deleting course: " . $e->getMessage();
    }
}

// Get all courses with teacher and facility information
try {
    $sql = "SELECT c.*, 
                   CONCAT(t.first_name, ' ', t.surname) as teacher_name,
                   f.name as facility_name,
                   f.capacity,
                   COUNT(cl.student_id) as enrollment_count
            FROM courses c
            LEFT JOIN teachers t ON c.teacher_id = t.identification_number
            LEFT JOIN facilities f ON c.facility_id = f.emblem
            LEFT JOIN course_logins cl ON c.emblem = cl.course_id
            GROUP BY c.emblem";
    
    $stmt = executeQuery($pdo, $sql);
    $courses = $stmt->fetchAll();
} catch(Exception $e) {
    $error = "Error loading courses: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Course Management System</title>
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
            <?php if (isset($success)): ?>
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
                <h2>Courses Management</h2>
                <p>Manage all course records</p>
            </div>

            <div class="quick-actions">
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Course
                </a>
            </div>

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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($courses) && count($courses) > 0): ?>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= htmlspecialchars($course['emblem']) ?></td>
                                    <td><?= htmlspecialchars($course['name']) ?></td>
                                    <td><?= htmlspecialchars($course['teacher_name'] ?: 'No teacher assigned') ?></td>
                                    <td>
                                        <?= htmlspecialchars($course['facility_name'] ?: 'No facility assigned') ?>
                                        <?php if ($course['capacity'] && $course['enrollment_count'] > $course['capacity']): ?>
                                            <i class="fas fa-exclamation-triangle warning-icon" title="Over capacity!"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars(date('M j, Y', strtotime($course['start_day']))) ?></td>
                                    <td><?= htmlspecialchars(date('M j, Y', strtotime($course['rest_of_day']))) ?></td>
                                    <td>
                                        <?= $course['enrollment_count'] ?>
                                        <?php if ($course['capacity']): ?>
                                            / <?= $course['capacity'] ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?= urlencode($course['emblem']) ?>" class="btn btn-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= urlencode($course['emblem']) ?>" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?= urlencode($course['emblem']) ?>" 
                                           class="btn btn-danger" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this course?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 2rem;">
                                    <i class="fas fa-book" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>No courses found. <a href="add.php">Add the first course</a></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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