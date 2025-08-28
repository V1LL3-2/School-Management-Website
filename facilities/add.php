<?php
require_once '../config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emblem = strtoupper(trim($_POST['emblem']));
    $name = trim($_POST['name']);
    $capacity = (int)$_POST['capacity'];
    
    // Validation
    $errors = [];
    
    if (empty($emblem)) {
        $errors[] = "Facility code is required";
    }
    
    if (empty($name)) {
        $errors[] = "Facility name is required";
    }
    
    if ($capacity <= 0) {
        $errors[] = "Capacity must be greater than 0";
    }
    
    // Check if facility code already exists
    if (empty($errors)) {
        try {
            $existing = getRecordById($pdo, 'facilities', 'emblem', $emblem);
            if ($existing) {
                $errors[] = "Facility code already exists";
            }
        } catch(Exception $e) {
            $errors[] = "Error checking facility code: " . $e->getMessage();
        }
    }
    
    // If no errors, insert the facility
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO facilities (emblem, name, capacity) VALUES (?, ?, ?)";
            executeQuery($pdo, $sql, [$emblem, $name, $capacity]);
            
            header('Location: index.php?success=Facility added successfully');
            exit;
        } catch(Exception $e) {
            $errors[] = "Error adding facility: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Facility - Course Management System</title>
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
                <h2>Add New Facility</h2>
                <p>Enter facility information</p>
            </div>

            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="emblem">Facility Code *</label>
                        <input type="text" id="emblem" name="emblem" class="form-control" 
                               value="<?= htmlspecialchars($_POST['emblem'] ?? '') ?>" 
                               placeholder="e.g., ROOM105, LAB001, HALL001" 
                               style="text-transform: uppercase;" required>
                        <small style="color: #666;">Use a unique code like ROOM101, LAB001, etc.</small>
                    </div>

                    <div class="form-group">
                        <label for="name">Facility Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                               placeholder="e.g., Computer Lab A, Main Auditorium" required>
                    </div>

                    <div class="form-group">
                        <label for="capacity">Capacity *</label>
                        <input type="number" id="capacity" name="capacity" class="form-control" 
                               value="<?= htmlspecialchars($_POST['capacity'] ?? '') ?>" 
                               min="1" max="1000" placeholder="e.g., 30" required>
                        <small style="color: #666;">Maximum number of students this facility can accommodate</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Add Facility
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </form>
            </div>

            <div class="detail-card" style="margin-top: 2rem;">
                <div class="detail-header">
                    <h3><i class="fas fa-info-circle"></i> Facility Code Guidelines</h3>
                </div>
                <div style="padding: 1rem 0;">
                    <p><strong>Suggested naming conventions:</strong></p>
                    <ul style="margin-left: 2rem; color: #666;">
                        <li><strong>ROOM###</strong> - Regular classrooms (e.g., ROOM101, ROOM102)</li>
                        <li><strong>LAB###</strong> - Laboratory spaces (e.g., LAB001, LAB002)</li>
                        <li><strong>HALL###</strong> - Large halls/auditoriums (e.g., HALL001)</li>
                        <li><strong>GYM###</strong> - Gymnasium spaces (e.g., GYM001)</li>
                        <li><strong>LIB###</strong> - Library spaces (e.g., LIB001)</li>
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
        // Auto-uppercase facility code as user types
        document.getElementById('emblem').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });
    </script>
</body>
</html>