<?php

class TestController extends BaseController
{
    public function index()
    {
        $this->requireAuth();
        
        $debugFile = __DIR__ . '/../storage/logs/email_debug.log';
        $debugDir = dirname($debugFile);
        if (!is_dir($debugDir)) {
            @mkdir($debugDir, 0755, true);
        }
        
        $log = date('Y-m-d H:i:s') . " - TestController::index called\n";
        @file_put_contents($debugFile, $log, FILE_APPEND);
        
        echo "Test OK. Check debug log.";
    }
    
    public function testEmail()
    {
        $this->requireAuth();
        
        require_once __DIR__ . '/../helpers/EventService.php';
        require_once __DIR__ . '/../helpers/Settings.php';
        
        $database = new Database();
        $db = $database->getConnection();
        
        // Get a test student ID
        $stmt = $db->query("SELECT id FROM students LIMIT 1");
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            echo "No students found";
            return;
        }
        
        $studentId = $student['id'];
        $stage = 'sempro';
        $lecturerId = $_SESSION['user_id'];
        
        $debugFile = __DIR__ . '/../storage/logs/email_debug.log';
        @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - About to call queueEmailForLecturer\n", FILE_APPEND);
        
        try {
            EventService::queueEmailForLecturer($db, (int) $studentId, $stage, (int) $lecturerId);
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - queueEmailForLecturer returned\n", FILE_APPEND);
            echo "Email queue test completed. Check debug log and email.";
        } catch (Exception $e) {
            @file_put_contents($debugFile, date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            echo "Error: " . $e->getMessage();
        }
    }
}
