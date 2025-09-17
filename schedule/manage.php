<?php
require_once '../config/database.php';

// Get course ID from URL
$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    header('Location: ../courses/index.php?error=Course ID required');
    exit;
}

// Get course details
try {
    $course = getRecordById($pdo, 'courses', 'emblem', $course_id);
    if (!$course) {
        header('Location: ../courses/index.php?error=Course not found');
        exit;
    }
    
    // Get existing sessions for this course
    $sql = "SELECT * FROM course_sessions WHERE course_id = ? ORDER BY day_of_week, start_time";
    $stmt = executeQuery($pdo, $sql, [$course_id]);
    $existing_sessions = $stmt->fetchAll();
    
} catch(Exception $e) {
    $error = "Error loading course: " . $e->getMessage();
}

// Handle form submission
if ($_POST) {
    try {
        // Delete existing sessions for this course
        $sql = "DELETE FROM course_sessions WHERE course_id = ?";
        executeQuery($pdo, $sql, [$course_id]);
        
        // Add new sessions
        if (isset($_POST['sessions'])) {
            foreach ($_POST['sessions'] as $session) {
                if (!empty($session['day_of_week']) && !empty($session['start_time']) && !empty($session['end_time'])) {
                    $sql = "INSERT INTO course_sessions (course_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)";
                    executeQuery($pdo, $sql, [
                        $course_id,
                        $session['day_of_week'],
                        $session['start_time'],
                        $session['end_time']
                    ]);
                }
            }
        }
        
        $success = "Course schedule updated successfully!";
        
        // Reload existing sessions
        $sql = "SELECT * FROM course_sessions WHERE course_id = ? ORDER BY day_of_week, start_time";
        $stmt = executeQuery($pdo, $sql, [$course_id]);
        $existing_sessions = $stmt->fetchAll();
        
    } catch(Exception $e) {
        $error = "Error updating schedule: " . $e->getMessage();
    }
}

$days = [
    1 => 'Monday',
    2 => 'Tuesday', 
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course Schedule - Course Management System</title>
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
                    <li><a href="../facilities/index.php"><i class="fas fa-building"></i> Facilities</a></li>
                    <li><a href="index.php" class="active"><i class="fas fa-calendar"></i> Schedule</a></li>
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
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <div class="form-header">
                    <h2><i class="fas fa-calendar-plus"></i> Manage Course Schedule</h2>
                    <p>Configure weekly schedule for: <strong><?= htmlspecialchars($course['name']) ?> (<?= htmlspecialchars($course['emblem']) ?>)</strong></p>
                </div>

                <form method="POST" id="scheduleForm">
                    <div id="sessions-container">
                        <?php if (!empty($existing_sessions)): ?>
                            <?php foreach ($existing_sessions as $index => $session): ?>
                                <div class="session-row">
                                    <select name="sessions[<?= $index ?>][day_of_week]" required>
                                        <option value="">Select Day</option>
                                        <?php foreach ($days as $day_num => $day_name): ?>
                                            <option value="<?= $day_num ?>" <?= $session['day_of_week'] == $day_num ? 'selected' : '' ?>>
                                                <?= $day_name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    
                                    <input type="time" name="sessions[<?= $index ?>][start_time]" 
                                           value="<?= htmlspecialchars($session['start_time']) ?>" required>
                                    
                                    <input type="time" name="sessions[<?= $index ?>][end_time]" 
                                           value="<?= htmlspecialchars($session['end_time']) ?>" required>
                                    
                                    <button type="button" class="remove-session" onclick="removeSession(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="session-row">
                                <select name="sessions[0][day_of_week]" required>
                                    <option value="">Select Day</option>
                                    <?php foreach ($days as $day_num => $day_name): ?>
                                        <option value="<?= $day_num ?>"><?= $day_name ?></option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <input type="time" name="sessions[0][start_time]" required>
                                <input type="time" name="sessions[0][end_time]" required>
                                
                                <button type="button" class="remove-session" onclick="removeSession(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="button" class="add-session" onclick="addSession()">
                        <i class="fas fa-plus"></i> Add Session
                    </button>

                    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Schedule
                        </button>
                        <a href="../courses/view.php?id=<?= urlencode($course_id) ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Course
                        </a>
                    </div>
                </form>
            </div>

            <!-- Current Schedule Preview -->
            <?php if (!empty($existing_sessions)): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h3><i class="fas fa-eye"></i> Current Schedule</h3>
                    </div>
                    
                    <div class="detail-content">
                        <div class="table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Day</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($existing_sessions as $session): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($days[$session['day_of_week']]) ?></td>
                                            <td><?= date('H:i', strtotime($session['start_time'])) ?></td>
                                            <td><?= date('H:i', strtotime($session['end_time'])) ?></td>
                                            <td>
                                                <?php
                                                $start = new DateTime($session['start_time']);
                                                $end = new DateTime($session['end_time']);
                                                $duration = $start->diff($end);
                                                echo $duration->format('%h hours %i minutes');
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Course Management System. All rights reserved.</p>
        </div>
    </footer>

    <script>
        let sessionCount = <?= count($existing_sessions) ?: 1 ?>;

        function addSession() {
            const container = document.getElementById('sessions-container');
            const sessionRow = document.createElement('div');
            sessionRow.className = 'session-row';
            
            sessionRow.innerHTML = `
                <select name="sessions[${sessionCount}][day_of_week]" required>
                    <option value="">Select Day</option>
                    <?php foreach ($days as $day_num => $day_name): ?>
                        <option value="<?= $day_num ?>"><?= $day_name ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="time" name="sessions[${sessionCount}][start_time]" required>
                <input type="time" name="sessions[${sessionCount}][end_time]" required>
                
                <button type="button" class="remove-session" onclick="removeSession(this)">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            container.appendChild(sessionRow);
            sessionCount++;
        }

        function removeSession(button) {
            const sessionRow = button.parentElement;
            const container = document.getElementById('sessions-container');
            
            // Don't allow removing the last session
            if (container.children.length > 1) {
                sessionRow.remove();
            } else {
                alert('At least one session is required.');
            }
        }

        // Validate time inputs
        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            const sessions = document.querySelectorAll('.session-row');
            let valid = true;
            
            sessions.forEach(function(session) {
                const startTime = session.querySelector('input[name*="start_time"]').value;
                const endTime = session.querySelector('input[name*="end_time"]').value;
                
                if (startTime && endTime && startTime >= endTime) {
                    alert('End time must be after start time for all sessions.');
                    valid = false;
                    return false;
                }
            });
            
            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>