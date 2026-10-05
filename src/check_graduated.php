<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check for graduated students
    $stmt = $db->query("SELECT COUNT(*) as count FROM students WHERE status = 'LULUS'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Number of graduated students: " . $result['count'] . "\n";
    
    // Show all student statuses
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM students GROUP BY status");
    echo "\nStudent status breakdown:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['status']}: {$row['count']}\n";
    }
    
    // Show some graduated students if any
    if ($result['count'] > 0) {
        $stmt = $db->query("SELECT id, nim, name, angkatan, status FROM students WHERE status = 'LULUS' LIMIT 5");
        echo "\nSample graduated students:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  ID: {$row['id']}, NIM: {$row['nim']}, Name: {$row['name']}, Angkatan: {$row['angkatan']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
