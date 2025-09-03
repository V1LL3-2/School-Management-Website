<?php
// Database configuration
$host = 'localhost';
$dbname = 'course_management';
$username = 'root'; // Default XAMPP username
$password = '';     // Default XAMPP password (empty)

// New password for all users
$new_password = 'admin123';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Prepare the update statement
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE 1");
    
    // Execute the update
    $stmt->execute([$hashed_password]);
    
    // Get the number of affected rows
    $affected_rows = $stmt->rowCount();
    
    echo "Success! Password reset completed.\n";
    echo "Number of users updated: $affected_rows\n";
    echo "All users' passwords have been reset to: $new_password\n";
    echo "Password hash used: $hashed_password\n\n";
    
    // Optional: Display all users for verification
    $stmt = $pdo->query("SELECT id, username, email, role, first_name, last_name FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Updated users:\n";
    echo str_repeat("-", 80) . "\n";
    printf("%-4s %-15s %-30s %-10s %-20s\n", "ID", "Username", "Email", "Role", "Name");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($users as $user) {
        printf("%-4d %-15s %-30s %-10s %-20s\n", 
               $user['id'], 
               $user['username'], 
               $user['email'], 
               $user['role'], 
               $user['first_name'] . ' ' . $user['last_name']
        );
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Please check your database connection settings.\n";
} catch (Exception $e) {
    echo "An unexpected error occurred: " . $e->getMessage() . "\n";
}

// Close the connection
$pdo = null;

echo "\n--- Script completed ---\n";
?>