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
} catch(Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $surname = trim($_POST['surname']);
    $substance = trim($_POST['substance']);
    
    // Validation
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    
    if (empty($surname)) {
        $errors[] = "Surname is required";
    }
    
    if (empty($substance)) {
        $errors[] = "Subject/Substance is required";
    }
    
    // If no errors, update the teacher
    if (empty($errors)) {
        try {
            $sql = "UPDATE teachers SET first_name = ?, surname = ?, substance = ? WHERE identification_number = ?";
            executeQuery($pdo, $sql, [$first_name, $surname, $substance, $teacher_id]);
            
            header('Location: view.php?id=' . $teacher_id . '&success=Teacher updated successfully');
            exit;
        } catch(Exception $e) {
            $errors[] = "Error updating teacher: " . $e->getMessage();
        }
    }
} else {
    // Pre-populate form with existing data
    $_POST['first_name'] = $teacher['first_name'];
    $_POST['surname'] = $teacher['surname'];
    $_POST['substance'] = $teacher['substance'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher - Course Management System</title>
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
                <h2>Edit Teacher</h2>
                <p>Update teacher information</p>
            </div>

            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="identification_number">Teacher ID</label>
                        <input type="text" id="identification_number" name="identification_number" class="form-control" 
                               value="<?= htmlspecialchars($teacher['identification_number']) ?>" 
                               readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                        <small style="color: #666;">Teacher ID cannot be changed after creation</small>
                    </div>

                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" class="form-control" 
                               value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="surname">Surname *</label>
                        <input type="text" id="surname" name="surname" class="form-control" 
                               value="<?= htmlspecialchars($_POST['surname'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="substance">Subject/Specialty *</label>
                        <input type="text" id="substance" name="substance" class="form-control" 
                               value="<?= htmlspecialchars($_POST['substance'] ?? '') ?>" 
                               placeholder="e.g., Mathematics, English Literature, Computer Science" required>
                        <small style="color: #666;">The subject area this teacher specializes in</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Update Teacher
                        </button>
                        <a href="view.php?id=<?= $teacher_id ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Details
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Back to List
                        </a>
                    </div>
                </form>
            </div>

            <!-- Show current assignments -->
            <div class="detail-card" style="margin-top: 2rem;">
                <div class="detail-header">
                    <h3><i class="fas fa-info-circle"></i> Current Course Assignments</h3>
                </div>
                <div style="padding: 1rem 0;">
                    <?php
                    try {
                        // Get current course assignments
                        $sql = "SELECT c.emblem, c.name, COUNT(cl.student_id) as enrollment_count
                                FROM courses c
                                LEFT JOIN course_logins cl ON c.emblem = cl.course_id
                                WHERE c.teacher_id = ?
                                GROUP BY c.emblem";
                        
                        $stmt = executeQuery($pdo, $sql, [$teacher_id]);
                        $currentCourses = $stmt->fetchAll();
                        $totalStudents = array_sum(array_column($currentCourses, 'enrollment_count'));
                    ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: #667eea;"><?= count($currentCourses) ?></div>
                            <div style="color: #666; font-size: 0.9rem;">Assigned Courses</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: #28a745;"><?= $totalStudents ?></div>
                            <div style="color: #666; font-size: 0.9rem;">Total Students</div>
                        </div>
                    </div>
                    
                    <?php if (count($currentCourses) > 0): ?>
                        <div style="margin-top: 1rem;">
                            <strong>Currently teaching:</strong>
                            <ul style="margin-left: 2rem; color: #666;">
                                <?php foreach ($currentCourses as $course): ?>
                                    <li><?= htmlspecialchars($course['name']) ?> (<?= $course['enrollment_count'] ?> students)</li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p style="color: #666; font-style: italic;">This teacher is not currently assigned to any courses.</p>
                    <?php endif; ?>
                    
                    <?php } catch(Exception $e) { ?>
                        <p style="color: #666;">Unable to load current assignments.</p>
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
</body>
</html>