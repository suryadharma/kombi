<?php

class ProfileController extends BaseController {

    public function index() {
        $this->requireAuth();

        $database = new Database();
        $db = $database->getConnection();

        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        $profile = [
            'user' => $this->getUser($db, $userId),
            'role' => $role,
            'details' => null
        ];

        switch ($role) {
            case 'mahasiswa':
                $profile['details'] = $this->getStudentProfile($db, $userId);
                break;
            case 'dosen':
            case 'dosen_pembimbing':
            case 'dosen_penguji':
                $profile['details'] = $this->getLecturerProfile($db, $userId);
                break;
            default:
                // Kombi, superadmin, external, etc. fallback to user info only
                break;
        }

        $this->render('profile/index', $profile);
    }

    private function getUser(PDO $db, int $userId): array {
        $query = "SELECT id, username, name, role, nip, nim, email, created_at FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: [];
    }
    
    /**
     * Update user profile (email)
     */
    public function update()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile');
            return;
        }
        
        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::validate($csrfToken)) {
            $this->render('profile/index', [
                'error' => 'Token keamanan tidak valid. Silakan coba lagi.'
            ]);
            return;
        }
        
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $userId = $_SESSION['user_id'];
            $email = trim($_POST['email'] ?? '');
            
            // Validate email
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format email tidak valid');
            }
            
            // Update email in users table
            $updateQuery = "UPDATE users SET email = :email WHERE id = :id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([
                ':email' => $email ?: null,
                ':id' => $userId
            ]);
            
            // Also update email in lecturers table if user is a lecturer
            $lecturerQuery = "SELECT id FROM lecturers WHERE user_id = :user_id";
            $lecturerStmt = $db->prepare($lecturerQuery);
            $lecturerStmt->execute([':user_id' => $userId]);
            $lecturer = $lecturerStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($lecturer) {
                $updateLecturerQuery = "UPDATE lecturers SET email = :email WHERE user_id = :user_id";
                $updateLecturerStmt = $db->prepare($updateLecturerQuery);
                $updateLecturerStmt->execute([
                    ':email' => $email ?: null,
                    ':user_id' => $userId
                ]);
            }
            
            // Set success message
            $this->setFlash('success', 'Email berhasil diperbarui');
            $this->redirect('/profile');
            
        } catch (Exception $e) {
            $this->render('profile/index', [
                'error' => 'Gagal memperbarui email: ' . $e->getMessage()
            ]);
        }
    }

    private function getStudentProfile(PDO $db, int $userId): ?array {
        $query = "SELECT s.*, t.title, t.status AS title_status, t.submitted_at
                  FROM students s
                  LEFT JOIN titles t ON s.id = t.student_id
                  WHERE s.user_id = :user_id
                  ORDER BY t.submitted_at DESC
                  LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
        return $details ?: null;
    }

    private function getLecturerProfile(PDO $db, int $userId): ?array {
        $query = "SELECT l.*, u.username
                  FROM lecturers l
                  JOIN users u ON l.user_id = u.id
                  WHERE l.user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $details = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($details) {
            // Fetch assignment counts for lecturers
            $assignmentQuery = "SELECT role, COUNT(*) AS total
                                FROM assignments
                                WHERE lecturer_id = :user_id
                                GROUP BY role";
            $assignmentStmt = $db->prepare($assignmentQuery);
            $assignmentStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $assignmentStmt->execute();
            $assignments = $assignmentStmt->fetchAll(PDO::FETCH_ASSOC);
            $details['assignments'] = $assignments;
        }

        return $details ?: null;
    }
}
