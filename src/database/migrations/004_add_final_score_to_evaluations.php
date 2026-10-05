<?php

/**
 * Migration: Add final_score column to evaluations table
 * 
 * This migration adds the final_score column to store the final score
 * for bypass evaluations. The final_score is calculated from component scores
 * stored in the evaluation_scores table.
 */

class AddFinalScoreToEvaluations {
    public function up(PDO $db) {
        // Check if column already exists
        $checkColumnSql = "SELECT COUNT(*) FROM information_schema.COLUMNS 
                             WHERE TABLE_SCHEMA = DATABASE() 
                             AND TABLE_NAME = 'evaluations' 
                             AND COLUMN_NAME = 'final_score'";
        $stmt = $db->prepare($checkColumnSql);
        $stmt->execute();
        $columnExists = (bool)$stmt->fetchColumn();
        
        if (!$columnExists) {
            // Add final_score column
            $alterSql = "ALTER TABLE evaluations ADD COLUMN final_score DECIMAL(5,2) NULL 
                           COMMENT 'Nilai akhir skripsi (untuk bypass)' AFTER mode";
            
            try {
                $db->exec($alterSql);
                echo "Column final_score added successfully to evaluations table.\n";
            } catch (PDOException $e) {
                echo "Error adding final_score column: " . $e->getMessage() . "\n";
            }
        }
    }
    
    public function down(PDO $db) {
        // Check if column exists
        $checkColumnSql = "SELECT COUNT(*) FROM information_schema.COLUMNS 
                             WHERE TABLE_SCHEMA = DATABASE() 
                             AND TABLE_NAME = 'evaluations' 
                             AND COLUMN_NAME = 'final_score'";
        $stmt = $db->prepare($checkColumnSql);
        $stmt->execute();
        $columnExists = (bool)$stmt->fetchColumn();
        
        if ($columnExists) {
            // Drop final_score column
            $alterSql = "ALTER TABLE evaluations DROP COLUMN final_score";
            
            try {
                $db->exec($alterSql);
                echo "Column final_score dropped from evaluations table.\n";
            } catch (PDOException $e) {
                echo "Error dropping final_score column: " . $e->getMessage() . "\n";
            }
        }
    }
}
