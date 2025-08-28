<?php
require_once '../config/database.php';

// Get course ID from URL
$course_id = $_GET['id'] ?? null;

if (!$course_id) {
    header('Location: index.php');
    exit;
}

try {
    // Get course details
    $course = getRecordById($pdo, 'courses', 'emblem', $course_id);
    
    if (!$course) {
        header('Location: index.php?error=Course not found');
        exit;
    }
    
    // Get teachers and facilities for dropdown
    $teachers = getAllRecords($pdo, 'teachers');
    $facilities = getAllRecords($pdo, 'facilities');
    
} catch(Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $start_day = $_POST['start_day'];
    $rest_of_day = $_POST['rest_of_day'];
    $teacher_id = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;
    $facility_id = !empty($_POST['facility_id']) ? $_POST['facility_id'] : null;
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Course name is required";
    }
    
    if (empty($start_day)) {
        $errors[] = "Start date is required";
    }
    
    if (empty($rest_of_day)) {
        $errors[] = "End date is required";
    }
    
    if (!empty($start_day) && !empty($rest_of_day) && $start_day >= $rest_of_day) {
        $errors[] = "End date must be after start date";
    }
    
    // If no errors, update the course
    if (empty($errors)) {
        try {
            $sql = "UPDATE courses SET name = ?, description = ?, start_day = ?, rest_of_day = ?, teacher_id = ?, facility_id = ? WHERE emblem = ?";
            executeQuery($pdo, $sql, [$name, $description, $start_day, $rest_of_day, $teacher_id, $facility_id, $course_id]);
            
            header('Location: view.php?id=' . urlencode($course_id) . '&success=Course updated successfully');
            exit;
        } catch(Exception $e) {
            $errors[] = "Error updating course: " . $e->getMessage();
        }
    }
} else {
    // Pre-populate form with existing data
    $_POST['name'] = $course['name'];
    $_POST['description'] = $course['description'];
    $_POST['start_day'] = $course['start_day'];
    $_POST['rest_of_day'] = $course['rest_of_day'];
    $_POST['teacher_id'] = $course['teacher_id'];
    $_POST['facility_id'] = $course['facility_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - Course Management System</title>
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
                <h2>Edit Course</h2>
                <p>Update course information for <?= htmlspecialchars($course['emblem']) ?></p>
            </div>

            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="emblem">Course Code</label>
                        <input type="text" id="emblem" name="emblem" class="form-control" 
                               value="<?= htmlspecialchars($course['emblem']) ?>" 
                               readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                        <small style="color: #666;">Course code cannot be changed after creation</small>
                    </div>

                    <div class="form-group">
                        <label for="name">Course Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3" 
                                  placeholder="Brief description of the course content"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="start_day">Start Date *</label>
                            <input type="date" id="start_day" name="start_day" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['start_day'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="rest_of_day">End Date *</label>
                            <input type="date" id="rest_of_day" name="rest_of_day" class="form-control" 
                                   value="<?= htmlspecialchars($_POST['rest_of_day'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="teacher_id">Assign Teacher</label>
                        <select id="teacher_id" name="teacher_id" class="form-control">
                            <option value="">Select Teacher (Optional)</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= $teacher['identification_number'] ?>" 
                                        <?= ($_POST['teacher_id'] ?? '') == $teacher['identification_number'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['surname'] . ' - ' . $teacher['substance']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #666;">Choose the teacher responsible for this course</small>
                    </div>

                    <div class="form-group">
                        <label for="facility_id">Assign Facility</label>
                        <select id="facility_id" name="facility_id" class="form-control">
                            <option value="">Select Facility (Optional)</option>
                            <?php foreach ($facilities as $facility): ?>
                                <option value="<?= htmlspecialchars($facility['emblem']) ?>" 
                                        <?= ($_POST['facility_id'] ?? '') == $facility['emblem'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($facility['name'] . ' (Capacity: ' . $facility['capacity'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #666;">Choose where the course will be held</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Update Course
                        </button>
                        <a href="view.php?id=<?= urlencode($course_id) ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Details
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Back to List
                        </a>
                    </div>
                </form>
            </div>

            <!-- Show current enrollment information -->
            <div class="detail-card" style="margin-top: 2rem;">
                <div class="detail-header">
                    <h3><i class="fas fa-info-circle"></i> Current Enrollment Information</h3>
                </div>
                <div style="padding: 1rem 0;">
                    <?php
                    try {
                        // Get current enrollment count
                        $sql = "SELECT COUNT(cl.student_id) as enrollment_count
                                FROM course_logins cl
                                WHERE cl.course_id = ?";
                        
                        $stmt = executeQuery($pdo, $sql, [$course_id]);
                        $enrollmentData = $stmt->fetch();
                        $currentEnrollment = $enrollmentData['enrollment_count'] ?? 0;
                        
                        // Get facility capacity if assigned
                        $facilityCapacity = null;
                        if ($course['facility_id']) {
                            $facilityInfo = getRecordById($pdo, 'facilities', 'emblem', $course['facility_id']);
                            $facilityCapacity = $facilityInfo['capacity'] ?? null;
                        }
                    ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: #667eea;"><?= $currentEnrollment ?></div>
                            <div style="color: #666; font-size: 0.9rem;">Current Enrollment</div>
                        </div>
                        <?php if ($facilityCapacity): ?>
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: #333;"><?= $facilityCapacity ?></div>
                            <div style="color: #666; font-size: 0.9rem;">Facility Capacity</div>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: <?= $currentEnrollment > $facilityCapacity ? '#dc3545' : '#28a745' ?>;">
                                <?= $facilityCapacity - $currentEnrollment ?>
                            </div>
                            <div style="color: #666; font-size: 0.9rem;">Available Spots</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($facilityCapacity && $currentEnrollment > $facilityCapacity): ?>
                        <div class="alert alert-warning" style="margin-top: 1rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This course is currently over the facility capacity! 
                            Consider moving to a larger facility or limiting enrollment.
                        </div>
                    <?php endif; ?>
                    
                    <?php } catch(Exception $e) { ?>
                        <p style="color: #666;">Unable to load enrollment information.</p>
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

    <script>
        // Update end date minimum when start date changes
        document.getElementById('start_day').addEventListener('change', function(e) {
            const startDate = e.target.value;
            document.getElementById('rest_of_day').setAttribute('min', startDate);
        });
    </script>
</body>
</html>