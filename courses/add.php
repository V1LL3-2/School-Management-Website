<?php
require_once '../config/database.php';

// Get teachers and facilities for dropdown
try {
    $teachers = getAllRecords($pdo, 'teachers');
    $facilities = getAllRecords($pdo, 'facilities');
} catch(Exception $e) {
    $error = "Error loading data: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emblem = strtoupper(trim($_POST['emblem']));
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $start_day = $_POST['start_day'];
    $rest_of_day = $_POST['rest_of_day'];
    $teacher_id = !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null;
    $facility_id = !empty($_POST['facility_id']) ? $_POST['facility_id'] : null;
    
    // Validation
    $errors = [];
    
    if (empty($emblem)) {
        $errors[] = "Course code is required";
    }
    
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
    
    // Check if course code already exists
    if (empty($errors)) {
        try {
            $existing = getRecordById($pdo, 'courses', 'emblem', $emblem);
            if ($existing) {
                $errors[] = "Course code already exists";
            }
        } catch(Exception $e) {
            $errors[] = "Error checking course code: " . $e->getMessage();
        }
    }
    
    // If no errors, insert the course
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO courses (emblem, name, description, start_day, rest_of_day, teacher_id, facility_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            executeQuery($pdo, $sql, [$emblem, $name, $description, $start_day, $rest_of_day, $teacher_id, $facility_id]);
            
            header('Location: index.php?success=Course added successfully');
            exit;
        } catch(Exception $e) {
            $errors[] = "Error adding course: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course - Course Management System</title>
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

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-header">
                <h2>Add New Course</h2>
                <p>Enter course information</p>
            </div>

            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="emblem">Course Code *</label>
                        <input type="text" id="emblem" name="emblem" class="form-control" 
                               value="<?= htmlspecialchars($_POST['emblem'] ?? '') ?>" 
                               placeholder="e.g., MATH101, ENG201, CS301" 
                               style="text-transform: uppercase;" required>
                        <small style="color: #666;">Use a unique code like MATH101, ENG201, etc.</small>
                    </div>

                    <div class="form-group">
                        <label for="name">Course Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                               placeholder="e.g., Introduction to Mathematics" required>
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
                            <?php if (isset($teachers)): ?>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['identification_number'] ?>" 
                                            <?= ($_POST['teacher_id'] ?? '') == $teacher['identification_number'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['surname'] . ' - ' . $teacher['substance']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small style="color: #666;">You can assign a teacher now or later</small>
                    </div>

                    <div class="form-group">
                        <label for="facility_id">Assign Facility</label>
                        <select id="facility_id" name="facility_id" class="form-control">
                            <option value="">Select Facility (Optional)</option>
                            <?php if (isset($facilities)): ?>
                                <?php foreach ($facilities as $facility): ?>
                                    <option value="<?= htmlspecialchars($facility['emblem']) ?>" 
                                            <?= ($_POST['facility_id'] ?? '') == $facility['emblem'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($facility['name'] . ' (Capacity: ' . $facility['capacity'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <small style="color: #666;">Choose where the course will be held</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Add Course
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </form>
            </div>

            <div class="detail-card" style="margin-top: 2rem;">
                <div class="detail-header">
                    <h3><i class="fas fa-info-circle"></i> Course Code Guidelines</h3>
                </div>
                <div style="padding: 1rem 0;">
                    <p><strong>Suggested naming conventions:</strong></p>
                    <ul style="margin-left: 2rem; color: #666;">
                        <li><strong>SUBJ###</strong> - Subject code + level number</li>
                        <li><strong>MATH101</strong> - Basic Mathematics</li>
                        <li><strong>ENG201</strong> - Intermediate English</li>
                        <li><strong>CS301</strong> - Advanced Computer Science</li>
                        <li><strong>PHYS101</strong> - Introduction to Physics</li>
                        <li><strong>HIST201</strong> - World History</li>
                    </ul>
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
        // Auto-uppercase course code as user types
        document.getElementById('emblem').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_day').setAttribute('min', today);
        document.getElementById('rest_of_day').setAttribute('min', today);

        // Update end date minimum when start date changes
        document.getElementById('start_day').addEventListener('change', function(e) {
            const startDate = e.target.value;
            document.getElementById('rest_of_day').setAttribute('min', startDate);
        });
    </script>
</body>
</html>