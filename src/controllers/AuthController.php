<?php

class AuthController extends BaseController {
    
    public function login() {
        // If user is already logged in, redirect to dashboard
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        
        // Fetch upcoming events for display on login page
        $upcomingEvents = $this->getUpcomingEvents();
        
        // Show login form
        $this->render('auth/login', [
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success'),
            'upcomingEvents' => $upcomingEvents
        ]);
    }
    
    public function loginProcess() {
        // Get POST data
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        // Validate input
        if (empty($username) || empty($password)) {
            $this->render('auth/login', ['error' => 'Username dan password harus diisi']);
            return;
        }
        
        // Connect to database
        $database = new Database();
        $db = $database->getConnection();
        
        // Prepare query to get user
        $query = "SELECT id, username, password, name, role FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        // Check if user exists
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role_default'] = $user['role'];

                // Fetch available roles from helper; always include default role
                $availableRoles = UserRoleService::getUserRoles($db, (int)$user['id']);
                if (!in_array($user['role'], $availableRoles, true)) {
                    $availableRoles[] = $user['role'];
                }
                sort($availableRoles);

                $_SESSION['available_roles'] = $availableRoles;
                $activeRole = $user['role'];
                $_SESSION['active_role'] = $activeRole;
                $_SESSION['role'] = $activeRole;
                
                // Check if this is the first login (password same as username/NIM)
                if (password_verify($username, $user['password'])) {
                    // Set flag that user can change password but not required
                    $_SESSION['should_change_password'] = true;
                }
                
                // Redirect to dashboard
                $this->redirect('/dashboard');
            }
        }
        
        // If we get here, login failed
        $this->render('auth/login', ['error' => 'Username atau password salah']);
    }
    
    public function changePassword() {
        // Require authentication
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        // Show change password form
        $this->render('auth/change_password');
    }
    
    public function changePasswordProcess() {
        // Require authentication
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        // Get POST data
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->render('auth/change_password', ['error' => 'Semua field harus diisi']);
            return;
        }
        
        // Check if new password and confirm password match
        if ($newPassword !== $confirmPassword) {
            $this->render('auth/change_password', ['error' => 'Password baru dan konfirmasi password tidak cocok']);
            return;
        }
        
        // Check password length
        if (strlen($newPassword) < 6) {
            $this->render('auth/change_password', ['error' => 'Password minimal 6 karakter']);
            return;
        }
        
        // Connect to database
        $database = new Database();
        $db = $database->getConnection();
        
        // Get current user data
        $userId = $_SESSION['user_id'];
        $query = "SELECT username, password FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify current password
            if (password_verify($currentPassword, $user['password'])) {
                // Hash new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password in database
                $updateQuery = "UPDATE users SET password = :password WHERE id = :id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':password', $hashedPassword);
                $updateStmt->bindParam(':id', $userId);
                
                if ($updateStmt->execute()) {
                    // Remove should_change_password flag
                    unset($_SESSION['should_change_password']);
                    
                    // Redirect to dashboard with success message
                    $this->render('auth/change_password', ['success' => 'Password berhasil diubah']);
                    return;
                } else {
                    $this->render('auth/change_password', ['error' => 'Gagal mengubah password']);
                    return;
                }
            }
        }
        
        // If we get here, current password is wrong
        $this->render('auth/change_password', ['error' => 'Password saat ini salah']);
    }
    
    public function skipChangePassword() {
        // Require authentication
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        // Remove should_change_password flag
        unset($_SESSION['should_change_password']);
        
        // Redirect to dashboard
        $this->redirect('/dashboard');
    }
    
    public function logout() {
        // Destroy session
        unset($_SESSION['role_default'], $_SESSION['available_roles'], $_SESSION['active_role']);
        session_destroy();
        
        // Redirect to login page
        $this->redirect('/login');
    }

    public function switchRole() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
            return;
        }

        $requestedRole = $_POST['role'] ?? '';
        $fallback = isset($_POST['redirect_to']) ? trim($_POST['redirect_to']) : '/dashboard';
        if ($fallback === '' || strpos($fallback, '/') !== 0) {
            $fallback = '/dashboard';
        }

        $availableRoles = $_SESSION['available_roles'] ?? [];
        if (!in_array($requestedRole, $availableRoles, true)) {
            $this->setFlash('error', 'Role tidak valid atau tidak tersedia.');
            $this->redirect($fallback ?: '/dashboard');
            return;
        }

        $_SESSION['active_role'] = $requestedRole;
        $_SESSION['role'] = $requestedRole;

            $roleLabels = [
                'superadmin' => 'Superadmin',
                'kombi' => 'Kombi',
                'dosen' => 'Dosen',
                'dosen_pembimbing' => 'Dosen Pembimbing',
                'dosen_penguji' => 'Dosen Penguji',
                'mahasiswa' => 'Mahasiswa',
            'penguji_eksternal' => 'Penguji Eksternal'
        ];
        $label = $roleLabels[$requestedRole] ?? ucwords(str_replace('_', ' ', $requestedRole));

        $this->setFlash('success', 'Peran aktif berhasil diubah menjadi ' . $label . '.');
        $this->redirect($fallback ?: '/dashboard');
    }

    private function getUpcomingEvents(): array {
        $database = new Database();
        $db = $database->getConnection();
        
        $currentDate = date('Y-m-d');
        
        // Optimized query with JOIN to get lecturers in single query
        // Using GROUP_CONCAT to avoid N+1 query problem
        $query = "SELECT e.*, s.nim, s.name AS student_name, s.angkatan,
                         GROUP_CONCAT(
                             CONCAT(a.role, ':', u.name)
                             ORDER BY a.role
                             SEPARATOR '|'
                         ) as lecturers_data
                  FROM events e
                  JOIN students s ON e.student_id = s.id
                  LEFT JOIN assignments a ON s.id = a.student_id
                  LEFT JOIN users u ON a.lecturer_id = u.id
                  WHERE e.status = 'MENUNGGU' AND e.scheduled_date >= :currentDate
                  GROUP BY e.id, s.nim, s.name, s.angkatan, e.type, e.scheduled_date, e.scheduled_time, e.room, e.status
                  ORDER BY e.scheduled_date ASC, e.scheduled_time ASC
                  LIMIT 10";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':currentDate', $currentDate);
        $stmt->execute();
        
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Stage labels
        $stageLabels = [
            'SEMPRO' => 'Seminar Proposal',
            'SEMHAS' => 'Seminar Hasil',
            'PRA_UJIAN' => 'Pra Ujian',
            'UJIAN_SKRIPSI' => 'Ujian Skripsi'
        ];
        
        // Format events for display
        $formattedEvents = [];
        foreach ($events as $event) {
            // Parse lecturers data from GROUP_CONCAT
            $lecturersInfo = ['pembimbing' => [], 'penguji' => []];
            if (!empty($event['lecturers_data'])) {
                $lecturerPairs = explode('|', $event['lecturers_data']);
                foreach ($lecturerPairs as $pair) {
                    $parts = explode(':', $pair, 2);
                    if (count($parts) === 2) {
                        $role = $parts[0];
                        $name = $parts[1];
                        if (strpos($role, 'pembimbing') !== false) {
                            $lecturersInfo['pembimbing'][] = $name;
                        } elseif (strpos($role, 'penguji') !== false) {
                            $lecturersInfo['penguji'][] = $name;
                        }
                    }
                }
            }
            
            $formattedEvents[] = [
                'id' => $event['id'],
                'nim' => $event['nim'],
                'student_name' => $event['student_name'],
                'angkatan' => $event['angkatan'],
                'type' => $event['type'],
                'type_label' => $stageLabels[$event['type']] ?? $event['type'],
                'scheduled_date' => $event['scheduled_date'],
                'scheduled_time' => $event['scheduled_time'],
                'room' => $event['room'],
                'status' => $event['status'],
                'date_formatted' => date('d M Y', strtotime($event['scheduled_date'])),
                'time_formatted' => substr($event['scheduled_time'], 0, 5) . ' WIB',
                'days_until' => $this->getDaysUntil($event['scheduled_date'], $event['scheduled_time']),
                'lecturers' => $lecturersInfo
            ];
        }
        
        return $formattedEvents;
    }
    
    private function getEventLecturers($db, $studentId, $eventType) {
        // Query to get all lecturers assigned to this student
        $lectQuery = "SELECT u.name, a.role
                      FROM assignments a
                      JOIN users u ON a.lecturer_id = u.id
                      WHERE a.student_id = :student_id
                      ORDER BY a.role";
        
        $lectStmt = $db->prepare($lectQuery);
        $lectStmt->bindParam(':student_id', $studentId);
        $lectStmt->execute();
        
        $assignments = $lectStmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [
            'pembimbing' => [],
            'penguji' => []
        ];
        
        foreach ($assignments as $assignment) {
            $role = $assignment['role'];
            $lecturerName = $assignment['name'];
            
            if (strpos($role, 'pembimbing') !== false) {
                $result['pembimbing'][] = $lecturerName;
            } elseif (strpos($role, 'penguji') !== false) {
                $result['penguji'][] = $lecturerName;
            }
        }
        
        return $result;
    }
    
    private function getDaysUntil(string $eventDate, string $eventTime = null): string {
        $timezone = new DateTimeZone('Asia/Jakarta');
        
        // Create the event datetime - combine date and time if provided
        if ($eventTime) {
            $eventDateTime = new DateTime($eventDate . ' ' . $eventTime, $timezone);
        } else {
            $eventDateTime = new DateTime($eventDate, $timezone);
        }
        
        $currentDateTime = new DateTime('now', $timezone);
        
        // Compare the full datetime values
        $interval = $currentDateTime->diff($eventDateTime);
        
        // Determine the difference in days
        $daysDiff = $currentDateTime > $eventDateTime ? -$interval->days : $interval->days;
        
        // Check if the event is today by comparing just the dates
        $currentDate = $currentDateTime->format('Y-m-d');
        $eventDateOnly = $eventDateTime->format('Y-m-d');
        
        if ($currentDate === $eventDateOnly) {
            return 'Hari ini';
        } elseif ($daysDiff === 1) {  // Tomorrow
            return 'Besok';
        } else {
            return $interval->days . ' hari lagi';
        }
    }
}
