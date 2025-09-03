<?php
require_once '../config/database.php';

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
    
    // If no errors, insert the teacher
    if (empty($errors)) {
        try {
            $sql = "INSERT INTO teachers (first_name, surname, substance) VALUES (?, ?, ?)";
            executeQuery($pdo, $sql, [$first_name, $surname, $substance]);
            
            header('Location: index.php?success=Teacher added successfully');
            exit;
        } catch(Exception $e) {
            $errors[] = "Error adding teacher: " . $e->getMessage();
        }
    }
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
    <title>Add Teacher - Course Management System</title>
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
                <h2>Add New Teacher</h2>
                <p>Enter teacher information</p>
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
                        <label for="substance">Subject/Specialty *</label>
                        <input type="text" id="substance" name="substance" class="form-control" 
                               value="<?= htmlspecialchars($_POST['substance'] ?? '') ?>" 
                               placeholder="e.g., Mathematics, English Literature, Computer Science" required>
                        <small style="color: #666;">The subject area this teacher specializes in</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Add Teacher
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </form>
            </div>

            <div class="detail-card" style="margin-top: 2rem;">
                <div class="detail-header">
                    <h3><i class="fas fa-info-circle"></i> Subject Examples</h3>
                </div>
                <div style="padding: 1rem 0;">
                    <p><strong>Common subject areas:</strong></p>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem; margin-top: 1rem; color: #666;">
                        <div>• Mathematics</div>
                        <div>• English Literature</div>
                        <div>• Computer Science</div>
                        <div>• Physics</div>
                        <div>• Chemistry</div>
                        <div>• Biology</div>
                        <div>• History</div>
                        <div>• Geography</div>
                        <div>• Art & Design</div>
                        <div>• Physical Education</div>
                        <div>• Music</div>
                        <div>• Foreign Languages</div>
                    </div>
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