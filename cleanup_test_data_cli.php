#!/usr/bin/env php
<?php
/**
 * Command-line script to clean up test data (NIM 222222222222)
 * 
 * Usage: php cleanup_test_data_cli.php
 * 
 * WARNING: This will DELETE data permanently!
 */

// Load database configuration
require_once __DIR__ . '/src/config/database.php';

echo "=== Test Data Cleanup Script ===\n";
echo "This script will remove test data (NIM containing '2222' or 'test')\n\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        die("Error: Could not connect to database\n");
    }
    
    echo "Connected to database successfully\n\n";
    
    // Show what will be deleted
    echo "=== Data to be deleted ===\n";
    
    $query = "SELECT id, nim, name, angkatan, status FROM students 
              WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $testStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($testStudents)) {
        echo "No test students found!\n";
        exit(0);
    }
    
    echo "Found " . count($testStudents) . " test students:\n";
    foreach ($testStudents as $student) {
        echo "  - ID: {$student['id']}, NIM: {$student['nim']}, Name: {$student['name']}\n";
    }
    
    // Count events
    $studentIds = array_column($testStudents, 'id');
    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
    $eventQuery = "SELECT COUNT(*) as count FROM events WHERE student_id IN ($placeholders)";
    $eventStmt = $db->prepare($eventQuery);
    $eventStmt->execute($studentIds);
    $eventCount = $eventStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Events to delete: $eventCount\n";
    
    // Count assignments
    $assignQuery = "SELECT COUNT(*) as count FROM assignments WHERE student_id IN ($placeholders)";
    $assignStmt = $db->prepare($assignQuery);
    $assignStmt->execute($studentIds);
    $assignCount = $assignStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Assignments to delete: $assignCount\n";
    
    // Count users
    $userQuery = "SELECT COUNT(*) as count FROM users WHERE username IN (SELECT nim FROM students WHERE id IN ($placeholders))";
    $userStmt = $db->prepare($userQuery);
    $userStmt->execute($studentIds);
    $userCount = $userStmt->fetch(PDO::FETCH_ASSOC)['count'];
    echo "Users to delete: $userCount\n";
    
    echo "\n";
    echo "Type 'yes' to continue with deletion, or anything else to cancel: ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) !== 'yes') {
        echo "Cleanup cancelled.\n";
        exit(0);
    }
    
    echo "\n=== Starting cleanup ===\n";
    
    // Delete events
    echo "Deleting events... ";
    $deleteEventQuery = "DELETE FROM events WHERE student_id IN ($placeholders)";
    $deleteEventStmt = $db->prepare($deleteEventQuery);
    $deleteEventStmt->execute($studentIds);
    $deletedEvents = $deleteEventStmt->rowCount();
    echo "Deleted $deletedEvents events\n";
    
    // Delete assignments
    echo "Deleting assignments... ";
    $deleteAssignQuery = "DELETE FROM assignments WHERE student_id IN ($placeholders)";
    $deleteAssignStmt = $db->prepare($deleteAssignQuery);
    $deleteAssignStmt->execute($studentIds);
    $deletedAssignments = $deleteAssignStmt->rowCount();
    echo "Deleted $deletedAssignments assignments\n";
    
    // Delete users
    echo "Deleting users... ";
    $deleteUserQuery = "DELETE FROM users WHERE username IN (SELECT nim FROM students WHERE id IN ($placeholders))";
    $deleteUserStmt = $db->prepare($deleteUserQuery);
    $deleteUserStmt->execute($studentIds);
    $deletedUsers = $deleteUserStmt->rowCount();
    echo "Deleted $deletedUsers users\n";
    
    // Delete students
    echo "Deleting students... ";
    $deleteStudentQuery = "DELETE FROM students WHERE id IN ($placeholders)";
    $deleteStudentStmt = $db->prepare($deleteStudentQuery);
    $deleteStudentStmt->execute($studentIds);
    $deletedStudents = $deleteStudentStmt->rowCount();
    echo "Deleted $deletedStudents students\n";
    
    echo "\n=== Cleanup completed ===\n";
    echo "Total deleted: " . ($deletedEvents + $deletedAssignments + $deletedUsers + $deletedStudents) . " records\n";
    
    // Verify
    $verifyQuery = "SELECT COUNT(*) as count FROM students 
                    WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%'";
    $verifyStmt = $db->prepare($verifyQuery);
    $verifyStmt->execute();
    $remainingCount = $verifyStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($remainingCount == 0) {
        echo "✓ All test data successfully removed!\n";
    } else {
        echo "⚠ Warning: $remainingCount test students still remain\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
