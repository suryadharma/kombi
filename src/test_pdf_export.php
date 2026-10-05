<?php
// Test script for PDF export - bypass auth for testing
require_once __DIR__ . '/config/init.php';

// Mock session
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';

// Import required classes
use App\Controllers\ScoreController;
use App\Helpers\PdfExporter;

// Test data
$studentId = 1217;

echo "Testing PDF export for student $studentId...\n";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get student data
    $stmt = $db->prepare("SELECT s.* FROM students s WHERE s.id = :student_id");
    $stmt->bindParam(':student_id', $studentId);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        die("Student not found\n");
    }
    
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get title
    $titleStmt = $db->prepare("SELECT title FROM titles WHERE student_id = :student_id AND status = 'DITERIMA' LIMIT 1");
    $titleStmt->bindParam(':student_id', $studentId);
    $titleStmt->execute();
    $titleRow = $titleStmt->fetch(PDO::FETCH_ASSOC);
    $student['title'] = $titleRow ? $titleRow['title'] : '-';
    
    // Get ujian date
    $dateStmt = $db->prepare("SELECT MIN(evaluation_date) as ujian_date FROM evaluations WHERE student_id = :student_id AND stage = 'ujian'");
    $dateStmt->bindParam(':student_id', $studentId);
    $dateStmt->execute();
    $dateRow = $dateStmt->fetch(PDO::FETCH_ASSOC);
    $student['ujian_date'] = $dateRow ? $dateRow['ujian_date'] : null;
    
    echo "Student: {$student['name']} ({$student['nim']})\n";
    echo "Title: {$student['title']}\n";
    
    // Create ScoreController instance
    $controller = new ScoreController();
    
    // Calculate final score using reflection to access private method
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('calculateFinalThesisScore');
    $method->setAccessible(true);
    
    $scoreData = $method->invoke($controller, $db, $studentId, []);
    
    echo "\nScore Data Structure:\n";
    echo json_encode($scoreData, JSON_PRETTY_PRINT) . "\n";
    
    echo "\nFinal Score: " . ($scoreData['value'] ?? 'NOT SET') . "\n";
    echo "Letter Grade: " . ($scoreData['letter'] ?? 'NOT SET') . "\n";
    echo "Is Bypass: " . (($scoreData['is_bypass'] ?? false) ? 'Yes' : 'No') . "\n";
    
    if (!empty($scoreData['formula']['parts'])) {
        echo "\nFormula Parts:\n";
        foreach ($scoreData['formula']['parts'] as $i => $part) {
            echo "  " . ($i+1) . ". {$part['label']}: {$part['value']} (weight: {$part['weight_percent']}%, fraction: {$part['weight_fraction']})\n";
        }
    }
    
    // Get chairperson
    $chairperson = null;
    $chairStmt = $db->prepare("SELECT u.name, u.nip FROM users u
                               JOIN assignments a ON a.lecturer_id = u.id
                               WHERE a.student_id = :student_id AND a.role LIKE 'penguji_1'
                               LIMIT 1");
    $chairStmt->bindParam(':student_id', $studentId);
    $chairStmt->execute();
    if ($chairStmt->rowCount() > 0) {
        $chairperson = $chairStmt->fetch(PDO::FETCH_ASSOC);
        echo "\nChairperson: {$chairperson['name']}\n";
    }
    
    // Output PDF
    echo "\nGenerating PDF...\n";
    $pdfPath = __DIR__ . '/../storage/test_final_score.pdf';
    
    // Capture PDF output
    ob_start();
    PdfExporter::outputFinalThesisScore($student, $scoreData, $chairperson);
    $pdfContent = ob_get_clean();
    
    // Save to file
    file_put_contents($pdfPath, $pdfContent);
    
    echo "PDF saved to: $pdfPath\n";
    echo "PDF size: " . strlen($pdfContent) . " bytes\n";
    
    if (strlen($pdfContent) > 1000) {
        echo "\n✓ PDF generation appears successful!\n";
        
        // Check if PDF contains the score
        if (strpos($pdfContent, (string) $scoreData['value']) !== false) {
            echo "✓ PDF contains the score value: {$scoreData['value']}\n";
        } else {
            echo "✗ WARNING: PDF may not contain the expected score value\n";
        }
    } else {
        echo "\n✗ PDF generation failed - content too small\n";
    }
    
} catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
