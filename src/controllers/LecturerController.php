<?php

class LecturerController extends BaseController {
    
    public function index() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can manage lecturers
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get search parameter
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Build query based on search
            if (!empty($search)) {
                $query = "SELECT * FROM lecturers WHERE nip LIKE :search OR name LIKE :search ORDER BY name";
                $stmt = $db->prepare($query);
                $stmt->bindValue(':search', '%' . $search . '%');
            } else {
                $query = "SELECT * FROM lecturers ORDER BY name";
                $stmt = $db->prepare($query);
            }
            
            $stmt->execute();
            $lecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ensure internal lecturers have user accounts
            foreach ($lecturers as &$lecturer) {
                $isExternalFlag = isset($lecturer['is_external']) ? (int)$lecturer['is_external'] : 0;
                if ($isExternalFlag === 0) {
                    if (empty($lecturer['user_id'])) {
                        $userId = $this->ensureLecturerUser($db, $lecturer['nip'], $lecturer['name']);
                        $update = "UPDATE lecturers SET user_id = :user_id WHERE id = :id";
                        $updateStmt = $db->prepare($update);
                        $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                        $updateStmt->bindParam(':id', $lecturer['id'], PDO::PARAM_INT);
                        $updateStmt->execute();
                        $lecturer['user_id'] = $userId;
                    } else {
                        UserRoleService::ensureUserRole($db, (int)$lecturer['user_id'], 'dosen_pembimbing');
                    }
                }
            }
            unset($lecturer);

            $assignmentsByLecturer = [];
            if (!empty($lecturers)) {
                $userToLecturerMap = [];
                foreach ($lecturers as $lecturerRow) {
                    $lecturerId = (int)($lecturerRow['id'] ?? 0);
                    $assignmentsByLecturer[$lecturerId] = [
                        'pembimbing' => [],
                        'penguji' => []
                    ];
                    $userId = (int)($lecturerRow['user_id'] ?? 0);
                    if ($userId > 0) {
                        $userToLecturerMap[$userId] = $lecturerId;
                    }
                }

                if (!empty($userToLecturerMap)) {
                    $placeholders = implode(',', array_fill(0, count($userToLecturerMap), '?'));
                    $assignmentQuery = "SELECT a.lecturer_id,
                                               a.role,
                                               s.id AS student_id,
                                               s.nim,
                                               s.name AS student_name,
                                               s.angkatan
                                        FROM assignments a
                                        JOIN students s ON s.id = a.student_id
                                        WHERE a.lecturer_id IN ($placeholders)
                                        ORDER BY s.name";
                    $assignmentStmt = $db->prepare($assignmentQuery);
                    $bindIndex = 1;
                    foreach (array_keys($userToLecturerMap) as $userId) {
                        $assignmentStmt->bindValue($bindIndex, $userId, PDO::PARAM_INT);
                        $bindIndex++;
                    }
                    $assignmentStmt->execute();
                    $assignmentRows = $assignmentStmt->fetchAll(PDO::FETCH_ASSOC);

                    $studentIds = array_column($assignmentRows, 'student_id');
                    $studentLetterLinks = $this->fetchStudentLetterLinks($db, $studentIds);
                    $roleLabels = [
                        'pembimbing_1' => 'Pembimbing 1',
                        'pembimbing_2' => 'Pembimbing 2',
                        'penguji_1' => 'Penguji Ketua',
                        'penguji_2' => 'Penguji Anggota 1',
                        'penguji_3' => 'Penguji Anggota 2',
                    ];

                    foreach ($assignmentRows as $row) {
                        $userId = (int)($row['lecturer_id'] ?? 0);
                        if (!isset($userToLecturerMap[$userId])) {
                            continue;
                        }
                        $lecturerId = $userToLecturerMap[$userId];
                        $roleCode = $row['role'] ?? '';
                        $type = (strpos($roleCode, 'pembimbing') === 0) ? 'pembimbing' : 'penguji';
                        $roleLabel = $roleLabels[$roleCode] ?? ucwords(str_replace('_', ' ', $roleCode));
                        $letters = $studentLetterLinks[(int)$row['student_id']] ?? ['advisor' => [], 'examiner' => []];
                        $assignmentsByLecturer[$lecturerId][$type][] = [
                            'role' => $roleLabel,
                            'student_name' => $row['student_name'] ?? '-',
                            'nim' => $row['nim'] ?? '',
                            'angkatan' => $row['angkatan'] ?? '',
                            'letter_link' => $this->resolveLetterLinkForRole($letters, $roleCode)
                        ];
                    }
                }
            }

            // Show lecturer list
            $this->render('lecturers/index', [
                'lecturers' => $lecturers,
                'search' => $search,
                'assignmentsByLecturer' => $assignmentsByLecturer
            ]);
        } catch (Exception $e) {
            $this->render('lecturers/index', ['error' => 'Gagal memuat data dosen: ' . $e->getMessage()]);
        }
    }

    private function fetchStudentLetterLinks(PDO $db, array $studentIds): array {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds))));
        if (empty($studentIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $query = "
            SELECT t.student_id,
                   t.advisor_letter_link,
                   t.examiner_letter_link
            FROM titles t
            JOIN (
                SELECT student_id, MAX(submitted_at) AS latest_submitted
                FROM titles
                WHERE student_id IN ($placeholders)
                GROUP BY student_id
            ) latest ON latest.student_id = t.student_id AND latest.latest_submitted = t.submitted_at
        ";
        $stmt = $db->prepare($query);
        foreach ($studentIds as $index => $studentId) {
            $stmt->bindValue($index + 1, $studentId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $roleGroupMap = $this->getRoleGroupMap();
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $studentId = (int)$row['student_id'];
            $results[$studentId] = [
                'advisor' => $this->decodeLetterLinkJson($row['advisor_letter_link'] ?? null, 'advisor'),
                'examiner' => $this->decodeLetterLinkJson($row['examiner_letter_link'] ?? null, 'examiner')
            ];
        }
        return $results;
    }

    private function decodeLetterLinkJson($value, string $group): array {
        $result = [];
        if (!is_string($value) || trim($value) === '') {
            return $result;
        }
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            foreach ($decoded as $roleKey => $link) {
                if (!is_string($link) || trim($link) === '') {
                    continue;
                }
                $result[$roleKey] = trim($link);
            }
            return $result;
        }
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $defaultRole = $group === 'advisor' ? 'pembimbing_1' : 'penguji_1';
            $result[$defaultRole] = trim($value);
        }
        return $result;
    }

    private function getRoleGroupMap(): array {
        return [
            'pembimbing_1' => 'advisor',
            'pembimbing_2' => 'advisor',
            'penguji_1' => 'examiner',
            'penguji_2' => 'examiner',
            'penguji_3' => 'examiner',
        ];
    }

    private function resolveLetterLinkForRole(array $letters, string $roleCode): ?string {
        $groupMap = $this->getRoleGroupMap();
        $group = $groupMap[$roleCode] ?? null;
        if (!$group) {
            return null;
        }
        $groupLetters = $letters[$group] ?? [];
        if (isset($groupLetters[$roleCode]) && trim($groupLetters[$roleCode]) !== '') {
            return $groupLetters[$roleCode];
        }
        return null;
    }
    
    public function create() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can manage lecturers
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Show create form
        $this->render('lecturers/create');
    }
    
    public function store() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can manage lecturers
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get POST data
        $nip = $_POST['nip'] ?? '';
        $name = $_POST['name'] ?? '';
        $prodi = $_POST['prodi'] ?? '';
        $email = $_POST['email'] ?? '';
        $isExternal = isset($_POST['is_external']) ? 1 : 0;

        // Validate input
        if (empty($nip) || empty($name)) {
            $this->render('lecturers/create', ['error' => 'NIP dan Nama harus diisi']);
            return;
        }

        // Validate email format if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('lecturers/create', ['error' => 'Format email tidak valid']);
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $db->beginTransaction();

            // Check if NIP already exists
            $checkQuery = "SELECT id FROM lecturers WHERE nip = :nip";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':nip', $nip);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $db->rollBack();
                $this->render('lecturers/create', ['error' => 'NIP sudah terdaftar']);
                return;
            }
            
            $userId = null;
            if (!$isExternal) {
                $userId = $this->ensureLecturerUser($db, $nip, $name, null, $email);
            }
            
            // Insert lecturer data
            $query = "INSERT INTO lecturers (user_id, nip, name, prodi, email, is_external) VALUES (:user_id, :nip, :name, :prodi, :email, :is_external)";
            $stmt = $db->prepare($query);
            if ($userId) {
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':user_id', null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(':nip', $nip);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':prodi', $prodi);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':is_external', $isExternal, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $db->commit();
                $this->redirect('/lecturers');
            } else {
                $db->rollBack();
                $this->render('lecturers/create', ['error' => 'Gagal menyimpan data dosen']);
            }
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->render('lecturers/create', ['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function edit($params) {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can manage lecturers
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        if (!$id) {
            $this->redirect('/lecturers');
            return;
        }

        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Get lecturer data
            $query = "SELECT * FROM lecturers WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $this->redirect('/lecturers');
                return;
            }
            
            $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Show edit form
            $this->render('lecturers/edit', ['lecturer' => $lecturer]);
        } catch (Exception $e) {
            $this->redirect('/lecturers');
        }
    }

    public function update($params) {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can manage lecturers
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        if (!$id) {
            $this->redirect('/lecturers');
            return;
        }

        // Database connection
        $database = new Database();
        $db = $database->getConnection();

        $currentLecturer = $this->getLecturerById($db, $id);
        if (!$currentLecturer) {
            $this->redirect('/lecturers');
            return;
        }

        // Get POST data
        $nip = $_POST['nip'] ?? '';
        $name = $_POST['name'] ?? '';
        $prodi = $_POST['prodi'] ?? '';
        $email = $_POST['email'] ?? '';
        $isExternal = isset($_POST['is_external']) ? 1 : 0;
        
        // Validate input
        if (empty($nip) || empty($name)) {
            $this->render('lecturers/edit', ['error' => 'NIP dan Nama harus diisi', 'lecturer' => ['id' => $id]]);
            return;
        }

        // Validate email format if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('lecturers/edit', ['error' => 'Format email tidak valid', 'lecturer' => $currentLecturer]);
            return;
        }
        
        try {
            $db->beginTransaction();
            // Check if NIP already exists for other lecturer
            $checkQuery = "SELECT id FROM lecturers WHERE nip = :nip AND id != :id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':nip', $nip);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $db->rollBack();
                $this->render('lecturers/edit', ['error' => 'NIP sudah terdaftar pada dosen lain', 'lecturer' => $currentLecturer]);
                return;
            }
            
            $userId = $currentLecturer['user_id'] ?? null;
            if (!$isExternal) {
                $userId = $this->ensureLecturerUser($db, $nip, $name, $userId, $email);
            } else {
                $userId = null;
            }

            // Update lecturer data
            $query = "UPDATE lecturers SET user_id = :user_id, nip = :nip, name = :name, prodi = :prodi, email = :email, is_external = :is_external WHERE id = :id";
            $stmt = $db->prepare($query);
            if ($userId) {
                $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':user_id', null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(':nip', $nip);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':prodi', $prodi);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':is_external', $isExternal, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $db->commit();
                $this->redirect('/lecturers');
            } else {
                $db->rollBack();
                $this->render('lecturers/edit', ['error' => 'Gagal memperbarui data dosen', 'lecturer' => $currentLecturer]);
            }
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->render('lecturers/edit', ['error' => 'Terjadi kesalahan: ' . $e->getMessage(), 'lecturer' => $currentLecturer]);
        }
    }
    
    public function delete($params) {
        // Require authentication
        $this->requireAuth();

        // Only kombi and superadmin can manage lecturers
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        if (!$id) {
            $this->redirect('/lecturers');
            return;
        }

        // Database connection
        $database = new Database();
        $db = $database->getConnection();

        try {
            // Delete lecturer
            $query = "DELETE FROM lecturers WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            $this->redirect('/lecturers');
        } catch (Exception $e) {
            $this->redirect('/lecturers');
        }
    }

    private function getLecturerById(PDO $db, int $id): ?array {
        $query = "SELECT * FROM lecturers WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);
        return $lecturer ?: null;
    }

    private function ensureLecturerUser(PDO $db, string $nip, string $name, ?int $existingUserId = null, ?string $email = null): int {
        if ($existingUserId) {
            $userQuery = "SELECT id, role, username FROM users WHERE id = :id";
            $userStmt = $db->prepare($userQuery);
            $userStmt->bindParam(':id', $existingUserId, PDO::PARAM_INT);
            $userStmt->execute();
            $userRow = $userStmt->fetch(PDO::FETCH_ASSOC);

            if (!$userRow) {
                throw new RuntimeException('Pengguna tidak ditemukan.');
            }

            $protectedRoles = ['superadmin', 'kombi'];
            $shouldOverridePrimaryRole = !in_array($userRow['role'], $protectedRoles, true);

            $setParts = ['name = :name', 'nip = :nip', 'email = :email'];
            if ($shouldOverridePrimaryRole) {
                $setParts[] = 'username = :username';
                $setParts[] = 'role = :role';
            }

            $update = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = :id";
            $stmt = $db->prepare($update);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':nip', $nip);
            $stmt->bindValue(':email', $email);
            if ($shouldOverridePrimaryRole) {
                $stmt->bindParam(':username', $nip);
                $stmt->bindValue(':role', 'dosen_pembimbing');
            }
            $stmt->bindParam(':id', $existingUserId, PDO::PARAM_INT);
            $stmt->execute();

            UserRoleService::ensureUserRole($db, $existingUserId, 'dosen_pembimbing');
            return $existingUserId;
        }

        $userQuery = "SELECT id, role FROM users WHERE username = :username LIMIT 1";
        $userStmt = $db->prepare($userQuery);
        $userStmt->bindParam(':username', $nip);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $protectedRoles = ['superadmin', 'kombi'];
            $shouldOverridePrimaryRole = !in_array($user['role'], $protectedRoles, true);

            $setParts = ['name = :name', 'nip = :nip', 'email = :email'];
            if ($shouldOverridePrimaryRole) {
                $setParts[] = 'role = :role';
            }

            $update = "UPDATE users SET " . implode(', ', $setParts) . " WHERE id = :id";
            $stmt = $db->prepare($update);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':nip', $nip);
            $stmt->bindValue(':email', $email);
            if ($shouldOverridePrimaryRole) {
                $stmt->bindValue(':role', 'dosen_pembimbing');
            }
            $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
            $stmt->execute();
            $userId = (int)$user['id'];
            UserRoleService::ensureUserRole($db, $userId, 'dosen_pembimbing');
            return $userId;
        }

        $insert = "INSERT INTO users (username, password, name, role, nip, email) VALUES (:username, :password, :name, :role, :nip, :email)";
        $stmt = $db->prepare($insert);
        $password = password_hash($nip, PASSWORD_DEFAULT);
        $stmt->bindParam(':username', $nip);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':name', $name);
        $stmt->bindValue(':role', 'dosen_pembimbing');
        $stmt->bindParam(':nip', $nip);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $userId = (int)$db->lastInsertId();
        UserRoleService::ensureUserRole($db, $userId, 'dosen_pembimbing');
        return $userId;
    }
    
    public function importForm() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can import lecturers
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Show import form
        $this->render('lecturers/import');
    }
    
    public function importProcess() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can import lecturers
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->render('lecturers/import', ['error' => 'File tidak ditemukan atau terjadi kesalahan saat upload.']);
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Process CSV file
            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
            if (!$file) {
                throw new Exception('Gagal membuka file CSV.');
            }
            
            // Skip header row
            $header = fgetcsv($file);
            
            $importedCount = 0;
            $updatedCount = 0;
            $errors = [];
            
            while (($row = fgetcsv($file)) !== false) {
                // Validate row data
                if (count($row) < 3) {
                    $errors[] = 'Baris data tidak lengkap: ' . implode(', ', $row);
                    continue;
                }
                
                $nip = trim($row[0]);
                $name = trim($row[1]);
                $prodi = trim($row[2]);
                $isExternal = isset($row[3]) ? (strtolower(trim($row[3])) === 'ya' || strtolower(trim($row[3])) === 'true' || trim($row[3]) === '1') : 0;
                
                if (empty($nip) || empty($name)) {
                    $errors[] = 'NIP dan Nama harus diisi: ' . implode(', ', $row);
                    continue;
                }
                
                try {
                    // Check if lecturer already exists
                    $checkQuery = "SELECT id, user_id FROM lecturers WHERE nip = :nip";
                    $checkStmt = $db->prepare($checkQuery);
                    $checkStmt->bindParam(':nip', $nip);
                    $checkStmt->execute();
                    
                    if ($checkStmt->rowCount() > 0) {
                        // Update existing lecturer
                        $lecturer = $checkStmt->fetch(PDO::FETCH_ASSOC);
                        $userId = $lecturer['user_id'];
                        
                        if (!$isExternal) {
                            $userId = $this->ensureLecturerUser($db, $nip, $name, $userId, null);
                        } else {
                            $userId = null;
                        }
                        
                        $updateQuery = "UPDATE lecturers SET user_id = :user_id, name = :name, prodi = :prodi, is_external = :is_external WHERE nip = :nip";
                        $updateStmt = $db->prepare($updateQuery);
                        if ($userId) {
                            $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                        } else {
                            $updateStmt->bindValue(':user_id', null, PDO::PARAM_NULL);
                        }
                        $updateStmt->bindParam(':name', $name);
                        $updateStmt->bindParam(':prodi', $prodi);
                        $updateStmt->bindParam(':is_external', $isExternal, PDO::PARAM_INT);
                        $updateStmt->bindParam(':nip', $nip);
                        $updateStmt->execute();
                        
                        $updatedCount++;
                    } else {
                        // Insert new lecturer
                        $userId = null;
                        if (!$isExternal) {
                            $userId = $this->ensureLecturerUser($db, $nip, $name, null, null);
                        }
                        
                        $insertQuery = "INSERT INTO lecturers (user_id, nip, name, prodi, is_external) VALUES (:user_id, :nip, :name, :prodi, :is_external)";
                        $insertStmt = $db->prepare($insertQuery);
                        if ($userId) {
                            $insertStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                        } else {
                            $insertStmt->bindValue(':user_id', null, PDO::PARAM_NULL);
                        }
                        $insertStmt->bindParam(':nip', $nip);
                        $insertStmt->bindParam(':name', $name);
                        $insertStmt->bindParam(':prodi', $prodi);
                        $insertStmt->bindParam(':is_external', $isExternal, PDO::PARAM_INT);
                        $insertStmt->execute();
                        
                        $importedCount++;
                    }
                } catch (Exception $e) {
                    $errors[] = 'Gagal memproses data (' . $nip . ' - ' . $name . '): ' . $e->getMessage();
                }
            }
            
            fclose($file);
            $db->commit();
            
            $this->render('lecturers/import', [
                'success' => "Impor berhasil: $importedCount data baru, $updatedCount data diperbarui.",
                'errors' => $errors
            ]);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            
            if (isset($file) && is_resource($file)) {
                fclose($file);
            }
            
            $this->render('lecturers/import', ['error' => 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()]);
        }
    }
}
