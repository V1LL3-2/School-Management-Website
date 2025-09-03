<?php
require_once '../config/database.php';

// Get facility ID from URL
$facility_id = $_GET['id'] ?? null;

if (!$facility_id) {
    header('Location: index.php');
    exit;
}

try {
    // Get facility details
    $facility = getRecordById($pdo, 'facilities', 'emblem', $facility_id);
    
    if (!$facility) {
        header('Location: index.php?error=Facility not found');
        exit;
    }
} catch(Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $capacity = (int)$_POST['capacity'];
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Facility name is required";
    }
    
    if ($capacity <= 0) {
        $errors[] = "Capacity must be greater than 0";
    }
    
    // If no errors, update the facility
    if (empty($errors)) {
        try {
            $sql = "UPDATE facilities SET name = ?, capacity = ? WHERE emblem = ?";
            executeQuery($pdo, $sql, [$name, $capacity, $facility_id]);
            
            header('Location: view.php?id=' . urlencode($facility_id) . '&success=Facility updated successfully');
            exit;
        } catch(Exception $e) {
            $errors[] = "Error updating facility: " . $e->getMessage();
        }
    }
} else {
    // Pre-populate form with existing data
    $_POST['name'] = $facility['name'];
    $_POST['capacity'] = $facility['capacity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Facility - Course Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="theme-color" content="#667eea">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Course Manager">
    <meta name="format-detection" content="telephone=no">
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
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <ul style="margin: 0; padding-left: 1rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="dashboard-header">
                <h2>Edit Facility</h2>
                <p>Update facility information for <?= htmlspecialchars($facility['emblem']) ?></p>
            </div>

            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="emblem">Facility Code</label>
                        <input type="text" id="emblem" name="emblem" class="form-control" 
                               value="<?= htmlspecialchars($facility['emblem']) ?>" 
                               readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                        <small style="color: #666;">Facility code cannot be changed after creation</small>
                    </div>

                    <div class="form-group">
                        <label for="name">Facility Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="capacity">Capacity *</label>
                        <input type="number" id="capacity" name="capacity" class="form-control" 
                               value="<?= htmlspecialchars($_POST['capacity'] ?? '') ?>" 
                               min="1" max="1000" required>
                        <small style="color: #666;">Maximum number of students this facility can accommodate</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Update Facility
                        </button>
                        <a href="view.php?id=<?= urlencode($facility_id) ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Details
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Back to List
                        </a>
                    </div>
                </form>
            </div>

            <!-- Show current usage information -->
            <div class="detail-card" style="margin-top: 2rem;">
                <div class="detail-header">
                    <h3><i class="fas fa-info-circle"></i> Current Usage Information</h3>
                </div>
                <div style="padding: 1rem 0;">
                    <?php
                    try {
                        // Get current course assignments and enrollments
                        $sql = "SELECT c.emblem, c.name, COUNT(cl.student_id) as enrollment_count
                                FROM courses c
                                LEFT JOIN course_logins cl ON c.emblem = cl.course_id
                                WHERE c.facility_id = ?
                                GROUP BY c.emblem";
                        
                        $stmt = executeQuery($pdo, $sql, [$facility_id]);
                        $currentCourses = $stmt->fetchAll();
                        $totalEnrollments = array_sum(array_column($currentCourses, 'enrollment_count'));
                    ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: #333;"><?= $facility['capacity'] ?></div>
                            <div style="color: #666; font-size: 0.9rem;">Current Capacity</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: #667eea;"><?= count($currentCourses) ?></div>
                            <div style="color: #666; font-size: 0.9rem;">Active Courses</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: <?= $totalEnrollments > $facility['capacity'] ? '#dc3545' : '#28a745' ?>;">
                                <?= $totalEnrollments ?>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">Total Students</div>
                        </div>
                    </div>
                    
                    <?php if ($totalEnrollments > $facility['capacity']): ?>
                        <div class="alert alert-warning" style="margin-top: 1rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This facility is currently over capacity! 
                            Consider increasing the capacity or redistributing some courses.
                        </div>
                    <?php endif; ?>
                    
                    <?php } catch(Exception $e) { ?>
                        <p style="color: #666;">Unable to load current usage information.</p>
                    <?php } ?>
                </div>
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