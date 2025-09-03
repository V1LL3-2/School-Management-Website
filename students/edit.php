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
} catch(Exception $e) {
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $surname = trim($_POST['surname']);
    $birthday = $_POST['birthday'];
    $grade = (int)$_POST['grade'];
    
    // Validation
    $errors = [];
    
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    
    if (empty($surname)) {
        $errors[] = "Surname is required";
    }
    
    if (empty($birthday)) {
        $errors[] = "Birthday is required";
    }
    
    if (!in_array($grade, [1, 2, 3])) {
        $errors[] = "Grade must be 1, 2, or 3";
    }
    
    // If no errors, update the student
    if (empty($errors)) {
        try {
            $sql = "UPDATE students SET first_name = ?, surname = ?, birthday = ?, grade = ? WHERE student_number = ?";
            executeQuery($pdo, $sql, [$first_name, $surname, $birthday, $grade, $student_id]);
            
            header('Location: view.php?id=' . $student_id . '&success=Student updated successfully');
            exit;
        } catch(Exception $e) {
            $errors[] = "Error updating student: " . $e->getMessage();
        }
    }
} else {
    // Pre-populate form with existing data
    $_POST['first_name'] = $student['first_name'];
    $_POST['surname'] = $student['surname'];
    $_POST['birthday'] = $student['birthday'];
    $_POST['grade'] = $student['grade'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Course Management System</title>
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
                <h2>Edit Student</h2>
                <p>Update student information</p>
            </div>

            <div class="form-container">
                <form method="POST">
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
                        <label for="birthday">Birthday *</label>
                        <input type="date" id="birthday" name="birthday" class="form-control" 
                               value="<?= htmlspecialchars($_POST['birthday'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="grade">Grade *</label>
                        <select id="grade" name="grade" class="form-control" required>
                            <option value="">Select Grade</option>
                            <option value="1" <?= ($_POST['grade'] ?? '') == '1' ? 'selected' : '' ?>>Grade 1</option>
                            <option value="2" <?= ($_POST['grade'] ?? '') == '2' ? 'selected' : '' ?>>Grade 2</option>
                            <option value="3" <?= ($_POST['grade'] ?? '') == '3' ? 'selected' : '' ?>>Grade 3</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Update Student
                        </button>
                        <a href="view.php?id=<?= $student_id ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Details
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-list"></i> Back to List
                        </a>
                    </div>
                </form>
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