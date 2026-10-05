<?php
// Simple test endpoint for PDF export
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define('BASE_PATH', __DIR__);

// Use the init file which loads all dependencies
require_once __DIR__ . '/config/init.php';

// Load controller manually since init doesn't load it
require_once __DIR__ . '/controllers/ScoreController.php';

session_start();

// Mock session
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'superadmin';

echo "<!DOCTYPE html><html><head><title>PDF Export Test</head><body>";
echo "<h1>PDF Export Test</h1>";

try {
    $studentId = 1217;
    echo "<p>Student ID: $studentId</p>";
    
    $database = new Database();
    $db = $database->getConnection();
    echo "<p>Database connected</p>";
    
    // Get student
    $stmt = $db->prepare("SELECT s.* FROM students s WHERE s.id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        die("<p style='color:red'>Student not found</p>");
    }
    
    echo "<p>Student: {$student['name']}</p>";
    
    // Get title
    $titleStmt = $db->prepare("SELECT title FROM titles WHERE student_id = ? AND status = 'DITERIMA' LIMIT 1");
    $titleStmt->execute([$studentId]);
    $titleRow = $titleStmt->fetch(PDO::FETCH_ASSOC);
    $student['title'] = $titleRow ? $titleRow['title'] : '-';
    
    echo "<p>Title: {$student['title']}</p>";
    
    // Create controller and get score data
    $controller = new ScoreController();
    
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('calculateFinalThesisScore');
    $method->setAccessible(true);
    
    echo "<p>Calculating score...</p>";
    $scoreData = $method->invoke($controller, $db, $studentId, []);
    
    echo "<h2>Score Data:</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($scoreData, JSON_PRETTY_PRINT)) . "</pre>";
    
    echo "<h2>Summary:</h2>";
    echo "<ul>";
    echo "<li>Value: " . ($scoreData['value'] ?? 'NOT SET') . "</li>";
    echo "<li>Letter: " . ($scoreData['letter'] ?? 'NOT SET') . "</li>";
    echo "</ul>";
    
    if (isset($_GET['generate'])) {
        echo "<h2>Generating PDF...</h2>";
        
        ob_start();
        try {
            PdfExporter::outputFinalThesisScore($student, $scoreData, null);
            $pdfContent = ob_get_clean();
            
            $size = strlen($pdfContent);
            echo "<p>PDF Size: $size bytes</p>";
            
            if ($size > 1000) {
                echo "<p style='color:green'>PDF Generated Successfully!</p>";
                
                // Save
                file_put_contents(__DIR__ . '/../storage/test_final.pdf', $pdfContent);
                echo "<p>Saved to: storage/test_final.pdf</p>";
                
                // Check for score
                if (strpos($pdfContent, (string) $scoreData['value']) !== false) {
                    echo "<p style='color:green'>Score found in PDF!</p>";
                } else {
                    echo "<p style='color:orange'>Score NOT found in PDF</p>";
                }
            } else {
                echo "<p style='color:red'>PDF too small: $size bytes</p>";
                echo "<pre>" . htmlspecialchars(bin2hex($pdfContent)) . "</pre>";
            }
        } catch (Exception $e) {
            ob_end_clean();
            echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
    } else {
        echo "<p><a href='?generate=1'>Generate PDF</a></p>";
    }
    
} catch (Throwable $e) {
    echo "<h2 style='color:red'>Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "</body></html>";
