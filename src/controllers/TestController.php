<?php

class TestController extends BaseController
{
    public function index()
    {
        $this->requireAuth();

        echo "Test OK.";
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

        try {
            EventService::queueEmailForLecturer($db, (int) $studentId, $stage, (int) $lecturerId);
            echo "Email queue test completed.";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
