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
    
    // Get courses scheduled in this facility
    $sql = "SELECT c.emblem, c.name, c.start_day, c.rest_of_day,
                   CONCAT(t.first_name, ' ', t.surname) as teacher_name,
                   COUNT(cl.student_id) as participant_count
            FROM courses c
            LEFT JOIN teachers t ON c.teacher_id = t.identification_number
            LEFT JOIN course_logins cl ON c.emblem = cl.course_id
            WHERE c.facility_id = ?
            GROUP BY c.emblem
            ORDER BY c.start_day";
    
    $stmt = executeQuery($pdo, $sql, [$facility_id]);
    $scheduledCourses = $stmt->fetchAll();
    
} catch(Exception $e) {
    $error = "Error loading facility details: " . $e->getMessage();
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
    <title>Facility Details - Course Management System</title>
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
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($facility)): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h2><i class="fas fa-building"></i> <?= htmlspecialchars($facility['name']) ?></h2>
                        <p><?= htmlspecialchars($facility['emblem']) ?> - Facility Details</p>
                    </div>

                    <div class="detail-info">
                        <div class="info-item">
                            <span class="info-label">Facility Code</span>
                            <span class="info-value"><?= htmlspecialchars($facility['emblem']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Facility Name</span>
                            <span class="info-value"><?= htmlspecialchars($facility['name']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Capacity</span>
                            <span class="info-value"><?= htmlspecialchars($facility['capacity']) ?> students</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Total Courses</span>
                            <span class="info-value"><?= count($scheduledCourses) ?> courses scheduled</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Current Utilization</span>
                            <span class="info-value">
                                <?php
                                $totalParticipants = array_sum(array_column($scheduledCourses, 'participant_count'));
                                $utilization = $facility['capacity'] > 0 ? 
                                    round(($totalParticipants / $facility['capacity']) * 100, 1) : 0;
                                ?>
                                <?= $totalParticipants ?> / <?= $facility['capacity'] ?> 
                                (<?= $utilization ?>%)
                                <?php if ($totalParticipants > $facility['capacity']): ?>
                                    <i class="fas fa-exclamation-triangle warning-icon" title="Over capacity!"></i>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status</span>
                            <span class="info-value">
                                <?php if ($totalParticipants > $facility['capacity']): ?>
                                    <span style="color: #dc3545; font-weight: 600;">⚠️ Over Capacity</span>
                                <?php elseif ($utilization > 80): ?>
                                    <span style="color: #ffc107; font-weight: 600;">⚡ High Usage</span>
                                <?php else: ?>
                                    <span style="color: #28a745; font-weight: 600;">✅ Available</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="edit.php?id=<?= urlencode($facility['emblem']) ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Facility
                        </a>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-header">
                        <h3><i class="fas fa-calendar-alt"></i> Scheduled Courses</h3>
                        <p>All courses that will be held in this facility</p>
                    </div>

                    <?php if (count($scheduledCourses) > 0): ?>
                        <div class="table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Name</th>
                                        <th>Teacher in Charge</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Participants</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($scheduledCourses as $course): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($course['emblem']) ?></td>
                                            <td>
                                                <a href="../courses/view.php?id=<?= urlencode($course['emblem']) ?>">
                                                    <?= htmlspecialchars($course['name']) ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($course['teacher_name'] ?: 'No teacher assigned') ?></td>
                                            <td><?= date('M j, Y', strtotime($course['start_day'])) ?></td>
                                            <td><?= date('M j, Y', strtotime($course['rest_of_day'])) ?></td>
                                            <td><?= htmlspecialchars($course['participant_count']) ?></td>
                                            <td>
                                                <?php if ($course['participant_count'] > $facility['capacity']): ?>
                                                    <span style="color: #dc3545; font-weight: 600;">
                                                        <i class="fas fa-exclamation-triangle warning-icon"></i> Over Capacity
                                                    </span>
                                                <?php elseif ($course['participant_count'] > ($facility['capacity'] * 0.8)): ?>
                                                    <span style="color: #ffc107; font-weight: 600;">
                                                        <i class="fas fa-exclamation-circle"></i> Nearly Full
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color: #28a745; font-weight: 600;">
                                                        <i class="fas fa-check-circle"></i> OK
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="../courses/view.php?id=<?= urlencode($course['emblem']) ?>" 
                                                   class="btn btn-primary" title="View Course">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem;">
                            <i class="fas fa-calendar-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                            <p>No courses scheduled for this facility yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (count($scheduledCourses) > 0): ?>
                <div class="detail-card">
                    <div class="detail-header">
                        <h3><i class="fas fa-chart-bar"></i> Capacity Analysis</h3>
                        <p>Visual representation of facility utilization</p>
                    </div>

                    <div style="padding: 1rem 0;">
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                            <span style="font-weight: 600;">Overall Utilization:</span>
                            <div style="flex: 1; background: #e9ecef; height: 20px; border-radius: 10px; overflow: hidden;">
                                <div style="width: <?= min($utilization, 100) ?>%; height: 100%; 
                                           background: <?= $utilization > 100 ? '#dc3545' : 
                                                         ($utilization > 80 ? '#ffc107' : '#28a745') ?>; 
                                           transition: width 0.3s ease;"></div>
                            </div>
                            <span style="font-weight: 600; min-width: 60px;
                                        color: <?= $utilization > 100 ? '#dc3545' : 
                                                  ($utilization > 80 ? '#856404' : '#155724') ?>;">
                                <?= $utilization ?>%
                            </span>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-top: 1.5rem;">
                            <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                                <div style="font-size: 1.5rem; font-weight: bold; color: #333;"><?= $facility['capacity'] ?></div>
                                <div style="color: #666; font-size: 0.9rem;">Total Capacity</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                                <div style="font-size: 1.5rem; font-weight: bold; color: #667eea;"><?= $totalParticipants ?></div>
                                <div style="color: #666; font-size: 0.9rem;">Total Enrolled</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                                <div style="font-size: 1.5rem; font-weight: bold; color: <?= $facility['capacity'] - $totalParticipants >= 0 ? '#28a745' : '#dc3545' ?>;">
                                    <?= $facility['capacity'] - $totalParticipants ?>
                                </div>
                                <div style="color: #666; font-size: 0.9rem;">Available Spots</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
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