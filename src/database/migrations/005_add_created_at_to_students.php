<?php

/**
 * Migration: Add created_at column to students table
 * 
 * This migration adds the created_at column to store the timestamp when a student was created.
 * This is used for consistent token generation in PDF QR codes.
 */

class AddCreatedAtToStudents {
    public function up(PDO $db) {
        // Check if column already exists
        $checkColumnSql = "SELECT COUNT(*) FROM information_schema.COLUMNS 
                             WHERE TABLE_SCHEMA = DATABASE() 
                             AND TABLE_NAME = 'students' 
                             AND COLUMN_NAME = 'created_at'";
        $stmt = $db->prepare($checkColumnSql);
        $stmt->execute();
        $columnExists = (bool)$stmt->fetchColumn();
        
        if (!$columnExists) {
            // Add created_at column
            $alterSql = "ALTER TABLE students ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
                           COMMENT 'Timestamp when student was created' AFTER status";
            
            try {
                $db->exec($alterSql);
                echo "Column created_at added successfully to students table.\n";
            } catch (PDOException $e) {
                echo "Error adding created_at column: " . $e->getMessage() . "\n";
            }
        }
    }
    
    public function down(PDO $db) {
        // Check if column exists
        $checkColumnSql = "SELECT COUNT(*) FROM information_schema.COLUMNS 
                             WHERE TABLE_SCHEMA = DATABASE() 
                             AND TABLE_NAME = 'students' 
                             AND COLUMN_NAME = 'created_at'";
        $stmt = $db->prepare($checkColumnSql);
        $stmt->execute();
        $columnExists = (bool)$stmt->fetchColumn();
        
        if ($columnExists) {
            // Drop created_at column
            $alterSql = "ALTER TABLE students DROP COLUMN created_at";
            
            try {
                $db->exec($alterSql);
                echo "Column created_at dropped from students table.\n";
            } catch (PDOException $e) {
                echo "Error dropping created_at column: " . $e->getMessage() . "\n";
            }
        }
    }
}
