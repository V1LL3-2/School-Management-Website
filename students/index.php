<?php
require_once '../config/database.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $student_id = $_GET['delete'];
    try {
        deleteRecord($pdo, 'students', 'student_number', $student_id);
        $success = "Student deleted successfully!";
    } catch(Exception $e) {
        $error = "Error deleting student: " . $e->getMessage();
    }
}

// Handle creating student record from user account
if (isset($_POST['create_student_from_user'])) {
    $user_id = (int)$_POST['user_id'];
    $grade = (int)$_POST['grade'];
    $birthday = $_POST['birthday'];
    
    try {
        // Get user details
        $sql = "SELECT * FROM users WHERE id = ? AND role = 'student'";
        $stmt = executeQuery($pdo, $sql, [$user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Create student record
            $sql = "INSERT INTO students (first_name, surname, birthday, grade) VALUES (?, ?, ?, ?)";
            $stmt = executeQuery($pdo, $sql, [$user['first_name'], $user['last_name'], $birthday, $grade]);
            
            // Get the new student ID
            $new_student_id = $pdo->lastInsertId();
            
            // Link the user account to the student record
            $sql = "UPDATE users SET student_id = ? WHERE id = ?";
            executeQuery($pdo, $sql, [$new_student_id, $user_id]);
            
            $success = "Student record created successfully and linked to user account!";
        } else {
            $error = "User not found or not a student role.";
        }
    } catch(Exception $e) {
        $error = "Error creating student record: " . $e->getMessage();
    }
}

// Get all students from students table
try {
    $students = getAllRecords($pdo, 'students');
} catch(Exception $e) {
    $error = "Error loading students: " . $e->getMessage();
}

// Get student users who don't have a corresponding student record
try {
    $sql = "SELECT u.* FROM users u 
            LEFT JOIN students s ON u.student_id = s.student_number 
            WHERE u.role = 'student' AND u.student_id IS NULL";
    $stmt = executeQuery($pdo, $sql);
    $unlinkedStudentUsers = $stmt->fetchAll();
} catch(Exception $e) {
    $unlinkedStudentUsers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Course Management System</title>
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
                <h2>Students Management</h2>
                <p>Manage all student records</p>
            </div>

            <div class="quick-actions">
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Student
                </a>
            </div>

            <!-- Unlinked Student User Accounts -->
            <?php if (count($unlinkedStudentUsers) > 0): ?>
                <div class="detail-card" style="margin-bottom: 2rem;">
                    <div class="detail-header">
                        <h3><i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i> Student Accounts Needing Student Records</h3>
                        <p>These user accounts have student role but no corresponding student record for course management</p>
                    </div>

                    <div class="table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($unlinkedStudentUsers as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                        <td>
                                            <button class="btn btn-success" onclick="showCreateStudentModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['first_name']) ?>', '<?= htmlspecialchars($user['last_name']) ?>')">
                                                <i class="fas fa-user-plus"></i> Create Student Record
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Regular Students Table -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3><i class="fas fa-users"></i> Active Students</h3>
                    <p>Students with complete records who can enroll in courses</p>
                </div>

                <div class="table">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Number</th>
                                <th>Name</th>
                                <th>Birthday</th>
                                <th>Grade</th>
                                <th>User Account</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($students) && count($students) > 0): ?>
                                <?php foreach ($students as $student): ?>
                                    <?php
                                    // Check if this student has a linked user account
                                    try {
                                        $sql = "SELECT username, email FROM users WHERE student_id = ?";
                                        $stmt = executeQuery($pdo, $sql, [$student['student_number']]);
                                        $linkedUser = $stmt->fetch();
                                    } catch(Exception $e) {
                                        $linkedUser = null;
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($student['student_number']) ?></td>
                                        <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['surname']) ?></td>
                                        <td><?= htmlspecialchars(date('M j, Y', strtotime($student['birthday']))) ?></td>
                                        <td>Grade <?= htmlspecialchars($student['grade']) ?></td>
                                        <td>
                                            <?php if ($linkedUser): ?>
                                                <i class="fas fa-link" style="color: #28a745;" title="Linked to user account"></i>
                                                <?= htmlspecialchars($linkedUser['username']) ?>
                                            <?php else: ?>
                                                <span style="color: #666; font-style: italic;">No user account</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="view.php?id=<?= $student['student_number'] ?>" class="btn btn-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?= $student['student_number'] ?>" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="enroll.php?id=<?= $student['student_number'] ?>" class="btn btn-success" title="Enroll in Course">
                                                <i class="fas fa-user-plus"></i>
                                            </a>
                                            <a href="?delete=<?= $student['student_number'] ?>" 
                                               class="btn btn-danger" 
                                               title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this student?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 2rem;">
                                        <i class="fas fa-users" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                                        <p>No students found. <a href="add.php">Add the first student</a></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for creating student record from user account -->
    <div id="createStudentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 10px; max-width: 500px; width: 90%;">
            <h3><i class="fas fa-user-plus"></i> Create Student Record</h3>
            <p>Create a student record for <strong id="modalUserName"></strong></p>
            
            <form method="POST" id="createStudentForm">
                <input type="hidden" id="modalUserId" name="user_id">
                
                <div class="form-group">
                    <label for="modalBirthday">Birthday *</label>
                    <input type="date" id="modalBirthday" name="birthday" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="modalGrade">Grade *</label>
                    <select id="modalGrade" name="grade" class="form-control" required>
                        <option value="">Select Grade</option>
                        <option value="1">Grade 1</option>
                        <option value="2">Grade 2</option>
                        <option value="3">Grade 3</option>
                    </select>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="hideCreateStudentModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" name="create_student_from_user" class="btn btn-success">
                        <i class="fas fa-save"></i> Create Student Record
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Management System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function showCreateStudentModal(userId, firstName, lastName) {
            document.getElementById('modalUserId').value = userId;
            document.getElementById('modalUserName').textContent = firstName + ' ' + lastName;
            document.getElementById('createStudentModal').style.display = 'block';
        }

        function hideCreateStudentModal() {
            document.getElementById('createStudentModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('createStudentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideCreateStudentModal();
            }
        });
    </script>
</body>
</html>