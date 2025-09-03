<?php
require_once '../config/database.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $facility_id = $_GET['delete'];
    try {
        deleteRecord($pdo, 'facilities', 'emblem', $facility_id);
        $success = "Facility deleted successfully!";
    } catch(Exception $e) {
        $error = "Error deleting facility: " . $e->getMessage();
    }
}

// Get all facilities with course information
try {
    $sql = "SELECT f.*, 
                   COUNT(c.emblem) as course_count,
                   COALESCE(SUM(enrollment_counts.student_count), 0) as total_students
            FROM facilities f
            LEFT JOIN courses c ON f.emblem = c.facility_id
            LEFT JOIN (
                SELECT course_id, COUNT(student_id) as student_count
                FROM course_logins
                GROUP BY course_id
            ) enrollment_counts ON c.emblem = enrollment_counts.course_id
            GROUP BY f.emblem";
    
    $stmt = executeQuery($pdo, $sql);
    $facilities = $stmt->fetchAll();
} catch(Exception $e) {
    $error = "Error loading facilities: " . $e->getMessage();
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
    <title>Facilities - Course Management System</title>
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
                    <li><a href="../courses/index.php"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="index.php" class="active"><i class="fas fa-building"></i> Facilities</a></li>
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
                <h2>Facilities Management</h2>
                <p>Manage all facility records</p>
            </div>

            <div class="quick-actions">
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Facility
                </a>
            </div>

            <div class="table">
                <table>
                    <thead>
                        <tr>
                            <th>Facility Code</th>
                            <th>Facility Name</th>
                            <th>Capacity</th>
                            <th>Courses</th>
                            <th>Total Students</th>
                            <th>Utilization</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($facilities) && count($facilities) > 0): ?>
                            <?php foreach ($facilities as $facility): ?>
                                <tr>
                                    <td><?= htmlspecialchars($facility['emblem']) ?></td>
                                    <td><?= htmlspecialchars($facility['name']) ?></td>
                                    <td><?= htmlspecialchars($facility['capacity']) ?></td>
                                    <td><?= htmlspecialchars($facility['course_count']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($facility['total_students']) ?>
                                        <?php if ($facility['total_students'] > $facility['capacity']): ?>
                                            <i class="fas fa-exclamation-triangle warning-icon" title="Over capacity!"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $utilization = $facility['capacity'] > 0 ? 
                                            round(($facility['total_students'] / $facility['capacity']) * 100, 1) : 0;
                                        ?>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="flex: 1; background: #e9ecef; height: 8px; border-radius: 4px; overflow: hidden;">
                                                <div style="width: <?= min($utilization, 100) ?>%; height: 100%; 
                                                           background: <?= $utilization > 100 ? '#dc3545' : 
                                                                         ($utilization > 80 ? '#ffc107' : '#28a745') ?>;"></div>
                                            </div>
                                            <span style="font-size: 0.8rem; font-weight: 600; 
                                                        color: <?= $utilization > 100 ? '#dc3545' : 
                                                                  ($utilization > 80 ? '#856404' : '#155724') ?>;">
                                                <?= $utilization ?>%
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="view.php?id=<?= urlencode($facility['emblem']) ?>" class="btn btn-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit.php?id=<?= urlencode($facility['emblem']) ?>" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?= urlencode($facility['emblem']) ?>" 
                                           class="btn btn-danger" 
                                           title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this facility?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem;">
                                    <i class="fas fa-building" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                    <p>No facilities found. <a href="add.php">Add the first facility</a></p>
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
    <script src="../js/mobile.js"></script>
</body>
</html>