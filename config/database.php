<?php
// Database configuration
$host = 'localhost';
$dbname = 'course_management';
$username = 'sql-user'; // Change this to your database username
$password = 'sql-user'; // Change this to your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to safely execute queries
function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        throw new Exception("Query failed: " . $e->getMessage());
    }
}

// Function to get all records
function getAllRecords($pdo, $table) {
    $stmt = executeQuery($pdo, "SELECT * FROM $table");
    return $stmt->fetchAll();
}

// Function to get record by ID
function getRecordById($pdo, $table, $idColumn, $id) {
    $stmt = executeQuery($pdo, "SELECT * FROM $table WHERE $idColumn = ?", [$id]);
    return $stmt->fetch();
}

// Function to delete record
function deleteRecord($pdo, $table, $idColumn, $id) {
    return executeQuery($pdo, "DELETE FROM $table WHERE $idColumn = ?", [$id]);
}
?>