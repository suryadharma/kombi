<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Settings.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../controllers/Controller.php';

class EmailQueueController extends Controller
{
    public function process()
    {
        // Require authentication for security
        $this->requireAuth();
        
        // Only allow admin/kombi/superadmin to process queue
        $role = $this->getUserRole();
        if ($role !== 'superadmin' && $role !== 'kombi' && $role !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }
        
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $emailService = new EmailService($db);
            
            // Check if email is enabled
            if (!$emailService->isEnabled()) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email feature is disabled',
                    'processed' => 0,
                    'sent' => 0,
                    'failed' => 0
                ]);
                return;
            }
            
            // Process up to 5 emails
            $result = $emailService->processQueue(5);
            
            echo json_encode([
                'success' => true,
                'message' => 'Email queue processed',
                'processed' => $result['processed'],
                'sent' => $result['sent'],
                'failed' => $result['failed'],
                'errors' => $result['errors'] ?? []
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function status()
    {
        // Require authentication
        $this->requireAuth();
        
        // Only allow admin/kombi/superadmin
        $role = $this->getUserRole();
        if ($role !== 'superadmin' && $role !== 'kombi' && $role !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }
        
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            $emailService = new EmailService($db);
            $stats = $emailService->getQueueStats();
            
            // Get recent emails
            $stmt = $db->query("SELECT id, recipient_email, subject, status, created_at, sent_at 
                                FROM email_queue 
                                ORDER BY created_at DESC 
                                LIMIT 10");
            $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'stats' => $stats,
                'recent' => $recent
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
