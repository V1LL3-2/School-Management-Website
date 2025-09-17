<?php
require_once '../config/database.php';

// Get filter parameters
$teacher_id = $_GET['teacher_id'] ?? null;
$student_id = $_GET['student_id'] ?? null;
$facility_id = $_GET['facility_id'] ?? null;
$week_offset = (int)($_GET['week'] ?? 0);

// Calculate current week dates
$current_date = new DateTime();
$current_date->modify(($week_offset * 7) . ' days');
$monday = clone $current_date;
$monday->modify('monday this week');

// Create week dates array
$week_dates = [];
for ($i = 0; $i < 5; $i++) {
    $date = clone $monday;
    $date->modify("+$i days");
    $week_dates[] = $date;
}

// Get schedule data
try {
    $sql = "SELECT cs.*, c.name as course_name, c.emblem as course_code,
                   CONCAT(t.first_name, ' ', t.surname) as teacher_name,
                   f.name as facility_name, f.capacity,
                   COUNT(cl.student_id) as enrollment_count
            FROM course_sessions cs
            JOIN courses c ON cs.course_id = c.emblem
            LEFT JOIN teachers t ON c.teacher_id = t.identification_number
            LEFT JOIN facilities f ON c.facility_id = f.emblem
            LEFT JOIN course_logins cl ON c.emblem = cl.course_id
            WHERE 1=1";
    
    $params = [];
    
    if ($teacher_id) {
        $sql .= " AND c.teacher_id = ?";
        $params[] = $teacher_id;
    }
    
    if ($facility_id) {
        $sql .= " AND c.facility_id = ?";
        $params[] = $facility_id;
    }
    
    if ($student_id) {
        $sql .= " AND cl.student_id = ?";
        $params[] = $student_id;
    }
    
    $sql .= " GROUP BY cs.id ORDER BY cs.day_of_week, cs.start_time";
    
    $stmt = executeQuery($pdo, $sql, $params);
    $sessions = $stmt->fetchAll();
    
    // Get filter options
    $teachers = getAllRecords($pdo, 'teachers');
    $facilities = getAllRecords($pdo, 'facilities');
    $students = getAllRecords($pdo, 'students');
    
} catch(Exception $e) {
    $error = "Error loading schedule: " . $e->getMessage();
    $sessions = [];
    $teachers = [];
    $facilities = [];
    $students = [];
}

// Organize sessions by day and time
$schedule = [];
foreach ($sessions as $session) {
    $day = $session['day_of_week'];
    $start_time = $session['start_time'];
    
    if (!isset($schedule[$day])) {
        $schedule[$day] = [];
    }
    
    $schedule[$day][] = $session;
}

// Generate hourly time slots from 8:00 to 18:00
$hourly_slots = [];
for ($hour = 8; $hour <= 18; $hour++) {
    $hourly_slots[] = sprintf('%02d:00:00', $hour);
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Schedule - Course Management System</title>
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
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="filters">
                <h3><i class="fas fa-filter"></i> Filters</h3>
                <form method="GET" class="filter-row">
                    <input type="hidden" name="week" value="<?= $week_offset ?>">
                    
                    <div class="filter-group">
                        <label for="teacher_id">Teacher</label>
                        <select name="teacher_id" id="teacher_id">
                            <option value="">All Teachers</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?= $teacher['identification_number'] ?>" 
                                        <?= $teacher_id == $teacher['identification_number'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['surname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="student_id">Student</label>
                        <select name="student_id" id="student_id">
                            <option value="">All Students</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['student_number'] ?>" 
                                        <?= $student_id == $student['student_number'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['surname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="facility_id">Facility</label>
                        <select name="facility_id" id="facility_id">
                            <option value="">All Facilities</option>
                            <?php foreach ($facilities as $facility): ?>
                                <option value="<?= $facility['emblem'] ?>" 
                                        <?= $facility_id == $facility['emblem'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($facility['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Schedule -->
            <div class="schedule-container">
                <div class="schedule-header">
                    <div class="week-navigation">
                        <button onclick="navigateWeek(-1)">
                            <i class="fas fa-chevron-left"></i> Previous Week
                        </button>
                        <h2>
                            Week <?= $monday->format('W') ?>, <?= $monday->format('Y') ?>
                            <br>
                            <small style="font-weight: normal; opacity: 0.9;">
                                <?= $monday->format('M j') ?> - <?= $week_dates[4]->format('M j, Y') ?>
                            </small>
                        </h2>
                        <button onclick="navigateWeek(1)">
                            Next Week <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                
                <div style="overflow-x: auto;">
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th class="time-slot">Time</th>
                                <?php foreach ($days as $index => $day): ?>
                                    <th>
                                        <?= $day ?>
                                        <br>
                                        <small style="font-weight: normal; opacity: 0.7;">
                                            <?= $week_dates[$index]->format('M j') ?>
                                        </small>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hourly_slots as $time_slot): ?>
                                <tr>
                                    <td class="time-slot">
                                        <?= date('H:i', strtotime($time_slot)) ?>
                                    </td>
                                    <?php for ($day = 1; $day <= 5; $day++): ?>
                                        <td>
                                            <?php if (isset($schedule[$day])): ?>
                                                <?php foreach ($schedule[$day] as $session): ?>
                                                    <?php if ($session['start_time'] === $time_slot): ?>
                                                        <div class="session-block" onclick="showSessionDetails(<?= htmlspecialchars(json_encode($session)) ?>)">
                                                            <div class="session-title">
                                                                <?= htmlspecialchars($session['course_code']) ?>
                                                            </div>
                                                            <div class="session-info">
                                                                <?= htmlspecialchars($session['course_name']) ?><br>
                                                                <?= htmlspecialchars($session['teacher_name'] ?: 'No teacher') ?><br>
                                                                <?= htmlspecialchars($session['facility_name'] ?: 'No room') ?><br>
                                                                <?= date('H:i', strtotime($session['start_time'])) ?> - <?= date('H:i', strtotime($session['end_time'])) ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
        function navigateWeek(offset) {
            const currentWeek = <?= $week_offset ?>;
            const newWeek = currentWeek + offset;
            const url = new URL(window.location);
            url.searchParams.set('week', newWeek);
            window.location.href = url.toString();
        }
        
        function showSessionDetails(session) {
            alert(`Course: ${session.course_name} (${session.course_code})
Teacher: ${session.teacher_name || 'No teacher assigned'}
Room: ${session.facility_name || 'No room assigned'}
Time: ${session.start_time.slice(0,5)} - ${session.end_time.slice(0,5)}
Enrolled Students: ${session.enrollment_count}`);
        }
        
        // Auto-submit form when filters change
        document.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    </script>
</body>
</html>