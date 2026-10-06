<?php

class StudentController extends BaseController {
    
    private $student;
    private $db;
    
    public function __construct() {
        // Connect to database
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Initialize student model
        $this->student = new Student($this->db);
    }
    
    public function index() {
        // Require authentication
        $this->requireAuth();
        
        // Check role permissions (only kombi and superadmin can access)
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get search, angkatan, and status parameters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $angkatan = isset($_GET['angkatan']) ? trim($_GET['angkatan']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : 'AKTIF'; // Default filter to 'AKTIF'
        $useActiveAngkatan = isset($_GET['active_angkatan']) ? (bool)$_GET['active_angkatan'] : true; // Default to using active angkatan

        // Distinct angkatan list for the filter dropdown (server-side list)
        $angkatanList = [];
        try {
            $angkatanStmt = $this->db->query("SELECT DISTINCT angkatan FROM students ORDER BY angkatan DESC");
            $angkatanList = array_map('strval', $angkatanStmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (Exception $e) {
            $angkatanList = [];
        }

        // Render view (rows are loaded via server-side DataTables at /students/data)
        $this->render('students/index', [
            'students' => [],
            'role' => $role,
            'angkatanList' => $angkatanList,
            'search' => $search,
            'angkatan' => $angkatan,
            'status' => $status,
            'useActiveAngkatan' => $useActiveAngkatan,
            'success' => $this->getFlash('success')
        ]);
    }

    public function listData() {
        // Require authentication
        $this->requireAuth();

        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->jsonResponse(['draw' => 0, 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []], 403);
            return;
        }

        $draw = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
        $start = isset($_GET['start']) ? max(0, (int)$_GET['start']) : 0;
        $length = isset($_GET['length']) ? (int)$_GET['length'] : 25;
        if ($length < 0) {
            $length = 100000;
        }

        $search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
        $angkatan = isset($_GET['angkatan']) ? trim((string)$_GET['angkatan']) : '';
        $status = isset($_GET['status']) ? trim((string)$_GET['status']) : 'AKTIF';
        $useActiveAngkatan = !isset($_GET['active_angkatan']) || ((string)$_GET['active_angkatan'] !== '0');

        // Build WHERE clause faithfully mirroring the original filter branches
        $where = [];
        $params = [];
        $emptyResult = false;
        $applyActive = $useActiveAngkatan && $angkatan === '';

        if ($applyActive) {
            if ($search === '' && $status === '') {
                // "Semua Status" + no search => all students (no filter)
            } else {
                $active = Settings::getActiveAngkatan();
                if (empty($active)) {
                    $emptyResult = true;
                } else {
                    $ph = implode(',', array_fill(0, count($active), '?'));
                    $where[] = "angkatan IN ($ph)";
                    foreach ($active as $a) {
                        $params[] = (int)$a;
                    }
                    if ($status !== '') {
                        $where[] = "status = ?";
                        $params[] = $status;
                    } else {
                        // search-only with active angkatan also excludes LULUS
                        $where[] = "status != 'LULUS'";
                    }
                    if ($search !== '') {
                        $where[] = "(nim LIKE ? OR name LIKE ?)";
                        $kw = "%{$search}%";
                        $params[] = $kw;
                        $params[] = $kw;
                    }
                }
            }
        } else {
            if ($angkatan !== '') {
                $where[] = "angkatan = ?";
                $params[] = $angkatan;
            }
            if ($status !== '') {
                $where[] = "status = ?";
                $params[] = $status;
            }
            if ($search !== '') {
                $where[] = "(nim LIKE ? OR name LIKE ?)";
                $kw = "%{$search}%";
                $params[] = $kw;
                $params[] = $kw;
            }
        }

        $whereSql = (!empty($where) ? 'WHERE ' . implode(' AND ', $where) : '');

        // Counts
        $recordsTotal = (int)$this->db->query("SELECT COUNT(*) FROM students")->fetchColumn();
        if ($emptyResult) {
            $recordsFiltered = 0;
        } else {
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM students $whereSql");
            $countStmt->execute($params);
            $recordsFiltered = (int)$countStmt->fetchColumn();
        }

        // Ordering
        $orderable = [1 => 'nim', 2 => 'name', 3 => 'angkatan', 4 => 'semester_masuk', 5 => 'status'];
        $orderCol = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 1;
        $orderDir = isset($_GET['order'][0]['dir']) ? strtoupper((string)$_GET['order'][0]['dir']) : 'ASC';
        if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
            $orderDir = 'ASC';
        }
        $orderField = $orderable[$orderCol] ?? 'nim';
        $orderSql = "ORDER BY {$orderField} {$orderDir}, id ASC";

        // Data
        $data = [];
        if (!$emptyResult) {
            $query = "SELECT id, nim, name, angkatan, semester_masuk, status, user_id
                      FROM students $whereSql $orderSql
                      LIMIT $length OFFSET $start";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                if (empty($row['user_id'])) {
                    try {
                        $userId = $this->ensureStudentUser($row['nim'], $row['name'], null);
                        $update = "UPDATE students SET user_id = :user_id WHERE id = :id";
                        $stmtUpdate = $this->db->prepare($update);
                        $stmtUpdate->bindParam(':user_id', $userId, PDO::PARAM_INT);
                        $stmtUpdate->bindParam(':id', $row['id'], PDO::PARAM_INT);
                        $stmtUpdate->execute();
                        $row['user_id'] = $userId;
                    } catch (Exception $e) {
                        // Ignore to avoid breaking list rendering
                    }
                }
                $data[] = $row;
            }
        }

        $this->jsonResponse([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function show($params) {
        // Require authentication
        $this->requireAuth();
        
        // Get student ID from params
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/students');
            return;
        }
        
        // Get student by ID
        if (!$this->student->getById($id)) {
            // Student not found
            $this->redirect('/students');
            return;
        }
        
        // Get current user role
        $currentUserRole = $this->getUserRole();
        
        // Get assignments for this student
        $assignments = [];
        $roles = ['pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2', 'penguji_3'];
        foreach ($roles as $role) {
            $query = "SELECT u.name FROM assignments a
                      JOIN users u ON a.lecturer_id = u.id
                      WHERE a.student_id = :student_id AND a.role = :role
                      LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':student_id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':role', $role);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $assignments[$role] = $result ? $result['name'] : null;
        }
        
        // Render view
        $this->render('students/show', [
            'student' => $this->student,
            'assignments' => $assignments,
            'currentUserRole' => $currentUserRole
        ]);
    }
    
    public function create() {
        // Require authentication
        $this->requireAuth();
        
        // Check role permissions (only kombi and superadmin can access)
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Render view
        $this->render('students/create');
    }
    
    public function store() {
        // Require authentication
        $this->requireAuth();
        
        // Check role permissions (only kombi and superadmin can access)
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/students/create');
            return;
        }

        $nim = trim($_POST['nim'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $angkatan = trim($_POST['angkatan'] ?? '');
        $semesterMasuk = trim($_POST['semester_masuk'] ?? '');
        $status = $_POST['status'] ?? 'AKTIF';

        if ($nim === '' || $name === '' || $angkatan === '' || $semesterMasuk === '') {
            $this->render('students/create', [
                'error' => 'NIM, Nama, Angkatan, dan Semester Masuk wajib diisi',
                'student' => $this->student
            ]);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Check duplicate NIM
            $checkQuery = "SELECT id FROM students WHERE nim = :nim";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':nim', $nim);
            $checkStmt->execute();
            if ($checkStmt->rowCount() > 0) {
                $this->db->rollBack();
                $this->render('students/create', [
                    'error' => 'NIM sudah terdaftar',
                    'student' => $this->student
                ]);
                return;
            }

            $userId = $this->ensureStudentUser($nim, $name, null);

            $this->student->nim = $nim;
            $this->student->name = $name;
            $this->student->angkatan = $angkatan;
            $this->student->semester_masuk = $semesterMasuk;
            $this->student->status = $status;
            $this->student->user_id = $userId;

            if ($this->student->create()) {
                $this->db->commit();
                $this->setFlash('success', 'Mahasiswa baru berhasil ditambahkan.');
                $this->redirect('/students');
            } else {
                $this->db->rollBack();
                $this->render('students/create', [
                    'error' => 'Gagal menambahkan mahasiswa',
                    'student' => $this->student
                ]);
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->render('students/create', [
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'student' => $this->student
            ]);
        }
    }
    
    public function edit($params) {
        // Require authentication
        $this->requireAuth();
        
        // Check role permissions (only kombi and superadmin can access)
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get student ID from params
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/students');
            return;
        }
        
        // Get student by ID
        if (!$this->student->getById($id)) {
            // Student not found
            $this->redirect('/students');
            return;
        }
        
        // Check if student data is valid
        if (!isset($this->student->id)) {
            // Invalid student data
            $this->redirect('/students');
            return;
        }
        
        // Render view with student data
        $this->render('students/edit', [
            'student' => [
                'id' => $this->student->id,
                'nim' => $this->student->nim ?? '',
                'name' => $this->student->name ?? '',
                'angkatan' => $this->student->angkatan ?? '',
                'semester_masuk' => $this->student->semester_masuk ?? '',
                'status' => $this->student->status ?? 'AKTIF'
            ]
        ]);
    }
    
    public function update($params) {
        // Require authentication
        $this->requireAuth();
        
        // Check role permissions (only kombi and superadmin can access)
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get student ID from params
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/students');
            return;
        }
        
        // Get student by ID
        if (!$this->student->getById($id)) {
            // Student not found
            $this->redirect('/students');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/students/' . $id . '/edit');
            return;
        }

        $nim = trim($_POST['nim'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $angkatan = trim($_POST['angkatan'] ?? '');
        $semesterMasuk = trim($_POST['semester_masuk'] ?? '');
        $semesterLulus = trim($_POST['semester_lulus'] ?? '');
        $status = $_POST['status'] ?? 'AKTIF';

        if ($nim === '' || $name === '' || $angkatan === '' || $semesterMasuk === '') {
            $this->render('students/edit', [
                'error' => 'NIM, Nama, Angkatan, dan Semester Masuk wajib diisi',
                'student' => $this->student
            ]);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Check duplicate NIM for other students
            $checkQuery = "SELECT id FROM students WHERE nim = :nim AND id != :id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':nim', $nim);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            if ($checkStmt->rowCount() > 0) {
                $this->db->rollBack();
                $this->render('students/edit', [
                    'error' => 'NIM sudah terdaftar pada mahasiswa lain',
                    'student' => $this->student
                ]);
                return;
            }

            $existingUserId = $this->student->user_id ? (int)$this->student->user_id : null;
            $userId = $this->ensureStudentUser($nim, $name, $existingUserId);

            // Update properties
            $this->student->nim = $nim;
            $this->student->name = $name;
            $this->student->angkatan = $angkatan;
            $this->student->semester_masuk = $semesterMasuk;
            $this->student->semester_lulus = $semesterLulus !== '' ? $semesterLulus : null;
            $this->student->status = $status;
            $this->student->user_id = $userId;

            if ($this->student->update()) {
                $this->db->commit();
                $this->redirect('/students');
            } else {
                $this->db->rollBack();
                $this->render('students/edit', [
                    'error' => 'Gagal memperbarui mahasiswa',
                    'student' => $this->student
                ]);
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->render('students/edit', [
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'student' => $this->student
            ]);
        }
    }
    
    public function delete($params) {
        // Require authentication
        $this->requireAuth();
        
        // Check role permissions (only kombi and superadmin can access)
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get student ID from params
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/students');
            return;
        }
        
        // Get student by ID
        if (!$this->student->getById($id)) {
            // Student not found
            $this->redirect('/students');
            return;
        }
        
        // Process deletion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete student
            if ($this->student->delete()) {
                // Success
                $this->redirect('/students');
            } else {
                // Error
                $this->render('students/show', [
                    'student' => $this->student,
                    'error' => 'Gagal menghapus mahasiswa'
                ]);
            }
        } else {
            // Show confirmation page
            $this->render('students/delete', [
                'student' => $this->student
            ]);
        }
    }

    public function bulkDelete() {
        // Require authentication
        $this->requireAuth();
        
        // Check role permissions (only kombi and superadmin can access)
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Redirect to students list if not POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/students');
            return;
        }
        
        // CSRF validation
        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            $this->render('students/index', [
                'students' => $this->student->getAll()->fetchAll(PDO::FETCH_ASSOC),
                'error' => 'Token CSRF tidak valid.'
            ]);
            return;
        }
        
        // Get selected student IDs
        $selectedStudentIds = $_POST['selected_students'] ?? [];
        
        // Check if any students are selected
        if (empty($selectedStudentIds)) {
            $this->render('students/index', [
                'students' => $this->student->getAll()->fetchAll(PDO::FETCH_ASSOC),
                'error' => 'Tidak ada mahasiswa yang dipilih untuk dihapus.'
            ]);
            return;
        }
        
        // Delete selected students
        $deletedCount = 0;
        $errors = [];
        
        foreach ($selectedStudentIds as $studentId) {
            try {
                $this->db->beginTransaction();
                if ($this->student->getById($studentId)) {
                    if ($this->student->delete()) {
                        $deletedCount++;
                        $this->db->commit();
                    } else {
                        $this->db->rollBack();
                        $errors[] = "Gagal menghapus mahasiswa dengan ID: " . htmlspecialchars($studentId);
                    }
                } else {
                    $this->db->rollBack();
                    $errors[] = "Mahasiswa dengan ID: " . htmlspecialchars($studentId) . " tidak ditemukan.";
                }
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $errors[] = "Terjadi kesalahan saat menghapus mahasiswa ID " . htmlspecialchars($studentId) . ": " . $e->getMessage();
            }
        }
        
        // Prepare message
        $message = $deletedCount . " mahasiswa berhasil dihapus.";
        if (!empty($errors)) {
            $message .= " Beberapa kesalahan terjadi: " . implode(", ", $errors);
            $this->render('students/index', [
                'students' => $this->student->getAll()->fetchAll(PDO::FETCH_ASSOC),
                'error' => $message
            ]);
        } else {
            $this->redirect('/students', ['success' => $message]);
        }
    }

    public function bulkDeleteProcess() {
        $this->requireAuth();

        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/students');
            return;
        }

        if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
            $this->render('students/index', [
                'students' => $this->student->getAll()->fetchAll(PDO::FETCH_ASSOC),
                'error' => 'Token CSRF tidak valid.'
            ]);
            return;
        }

        $selectedStudentIds = $_POST['selected_students'] ?? [];

        if (empty($selectedStudentIds)) {
            $this->render('students/index', [
                'students' => $this->student->getAll()->fetchAll(PDO::FETCH_ASSOC),
                'error' => 'Tidak ada mahasiswa yang dipilih untuk dihapus.'
            ]);
            return;
        }

        $deletedCount = 0;
        $errors = [];

        foreach ($selectedStudentIds as $studentId) {
            try {
                $this->db->beginTransaction();
                if ($this->student->getById($studentId)) {
                    if ($this->student->delete()) {
                        $deletedCount++;
                        $this->db->commit();
                    } else {
                        $this->db->rollBack();
                        $errors[] = "Gagal menghapus mahasiswa dengan ID: " . htmlspecialchars($studentId);
                    }
                } else {
                    $this->db->rollBack();
                    $errors[] = "Mahasiswa dengan ID: " . htmlspecialchars($studentId) . " tidak ditemukan.";
                }
            } catch (Exception $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                $errors[] = "Terjadi kesalahan saat menghapus mahasiswa ID " . htmlspecialchars($studentId) . ": " . $e->getMessage();
            }
        }

        $message = $deletedCount . " mahasiswa berhasil dihapus.";
        if (!empty($errors)) {
            $message .= " Beberapa kesalahan terjadi: " . implode(", ", $errors);
            $this->render('students/index', [
                'students' => $this->student->getAll()->fetchAll(PDO::FETCH_ASSOC),
                'error' => $message
            ]);
        } else {
            $this->redirect('/students', ['success' => $message]);
        }
    }

    public function assignmentStatus() {
        $this->requireAuth();

        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $angkatanFilter = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int)$_GET['angkatan'] : null;
        $searchFilter = trim($_GET['search'] ?? '');
        $includeLulus = isset($_GET['include_lulus']) && (int)$_GET['include_lulus'] === 1;

        $database = new Database();
        $db = $database->getConnection();

        try {
            // Get list of angkatan for filter
            $angkatanQuery = $includeLulus
                ? "SELECT DISTINCT angkatan FROM students ORDER BY angkatan DESC"
                : "SELECT DISTINCT angkatan FROM students WHERE status != 'LULUS' ORDER BY angkatan DESC";
            $angkatanStmt = $db->prepare($angkatanQuery);
            $angkatanStmt->execute();
            $angkatanList = $angkatanStmt->fetchAll(PDO::FETCH_COLUMN);

            // Fetch students based on filter
            $studentQuery = "SELECT id, nim, name, angkatan, status FROM students";
            $conditions = [];
            $params = [];

            if (!$includeLulus) {
                $conditions[] = "status != 'LULUS'";
            }

            if ($angkatanFilter) {
                $conditions[] = "angkatan = :angkatan";
                $params[':angkatan'] = $angkatanFilter;
            }

            if ($searchFilter) {
                $conditions[] = "(nim LIKE :search OR name LIKE :search)";
                $params[':search'] = '%' . $searchFilter . '%';
            }

            if (!empty($conditions)) {
                $studentQuery .= " WHERE " . implode(' AND ', $conditions);
            }

            $studentQuery .= " ORDER BY angkatan DESC, name";
            $studentStmt = $db->prepare($studentQuery);
            $studentStmt->execute($params);
            $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($students)) {
                $this->render('students/assignment_status', [
                    'students' => [],
                    'angkatanList' => $angkatanList,
                    'selectedAngkatan' => $angkatanFilter,
                    'currentSearch' => $searchFilter,
                    'summary' => [],
                    'roleLabels' => [
                        'pembimbing_1' => 'Pembimbing 1',
                        'pembimbing_2' => 'Pembimbing 2',
                        'penguji_1' => 'Penguji Ketua',
                        'penguji_2' => 'Penguji Anggota 1',
                        'penguji_3' => 'Penguji Anggota 2'
                    ],
                    'includeLulus' => $includeLulus
                ]);
                return;
            }

            $studentIds = array_column($students, 'id');
            $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

            // Latest title info per student
            $titleQuery = "
                SELECT t1.*
                FROM titles t1
                JOIN (
                    SELECT student_id, MAX(submitted_at) AS latest_submitted
                    FROM titles
                    WHERE student_id IN ($placeholders)
                    GROUP BY student_id
                ) t2 ON t1.student_id = t2.student_id AND t1.submitted_at = t2.latest_submitted
            ";
            $titleStmt = $db->prepare($titleQuery);
            $titleStmt->execute($studentIds);
            $titles = $titleStmt->fetchAll(PDO::FETCH_ASSOC);
            $titleMap = [];
            foreach ($titles as $title) {
                $titleMap[$title['student_id']] = $title;
            }

            // Assignments per student with lecturer names
            $assignmentQuery = "
                SELECT a.student_id, a.role, u.name AS lecturer_name
                FROM assignments a
                JOIN users u ON a.lecturer_id = u.id
                WHERE a.student_id IN ($placeholders)
            ";
            $assignmentStmt = $db->prepare($assignmentQuery);
            $assignmentStmt->execute($studentIds);
            $assignments = $assignmentStmt->fetchAll(PDO::FETCH_ASSOC);
            $assignmentMap = [];
            foreach ($assignments as $assignment) {
                $assignmentMap[$assignment['student_id']][$assignment['role']] = $assignment['lecturer_name'];
            }

            $roleLabels = [
                'pembimbing_1' => 'Pembimbing 1',
                'pembimbing_2' => 'Pembimbing 2',
                'penguji_1' => 'Penguji Ketua',
                'penguji_2' => 'Penguji Anggota 1',
                'penguji_3' => 'Penguji Anggota 2'
            ];

            $rows = [];
            $summary = [];
            foreach ($students as $student) {
                $studentId = $student['id'];
                $title = $titleMap[$studentId] ?? null;
                $assignment = $assignmentMap[$studentId] ?? [];

                $angkatan = (int)$student['angkatan'];
                if (!isset($summary[$angkatan])) {
                    $summary[$angkatan] = [
                        'angkatan' => $angkatan,
                        'total' => 0,
                        'no_title' => 0,
                        'title_pending' => 0,
                        'title_approved' => 0,
                        'missing_pembimbing' => 0,
                        'missing_penguji' => 0
                    ];
                }
                $summary[$angkatan]['total']++;

                $titleStatus = [
                    'label' => 'Belum Mengajukan',
                    'badge' => 'bg-secondary',
                    'detail' => 'Mahasiswa belum mengajukan judul'
                ];
                $titleState = 'none';
                if ($title) {
                    $titleStatus['detail'] = 'Pengajuan: ' . date('d M Y', strtotime($title['submitted_at']));
                    switch ($title['status']) {
                        case 'MENUNGGU':
                            $titleStatus['label'] = 'Menunggu Verifikasi';
                            $titleStatus['badge'] = 'bg-info';
                            $titleState = 'pending';
                            break;
                        case 'DITERIMA':
                            $titleStatus['label'] = 'Judul Disetujui';
                            $titleStatus['badge'] = 'bg-success';
                            $titleState = 'approved';
                            break;
                        case 'PERLU_REVISI':
                            $titleStatus['label'] = 'Perlu Revisi';
                            $titleStatus['badge'] = 'bg-warning text-dark';
                            $titleState = 'pending';
                            break;
                        case 'DITOLAK':
                            $titleStatus['label'] = 'Judul Ditolak';
                            $titleStatus['badge'] = 'bg-danger';
                            $titleState = 'rejected';
                            break;
                        default:
                            $titleStatus['label'] = $title['status'];
                            $titleStatus['badge'] = 'bg-secondary';
                            $titleState = 'other';
                    }
                } else {
                    $summary[$angkatan]['no_title']++;
                }

                if ($titleState === 'pending') {
                    $summary[$angkatan]['title_pending']++;
                } elseif ($titleState === 'approved') {
                    $summary[$angkatan]['title_approved']++;
                }

                $assignmentStatus = [];
                foreach ($roleLabels as $roleKey => $label) {
                    if (!empty($assignment[$roleKey])) {
                        $assignmentStatus[$roleKey] = [
                            'label' => $assignment[$roleKey],
                            'badge' => 'bg-success'
                        ];
                    } else {
                        $assignmentStatus[$roleKey] = [
                            'label' => 'Belum Ditentukan',
                            'badge' => 'bg-danger'
                        ];
                    }
                }

                $rows[] = [
                    'student' => $student,
                    'title' => $titleStatus,
                    'assignment' => $assignmentStatus
                ];

                if (empty($assignment['pembimbing_1'])) {
                    $summary[$angkatan]['missing_pembimbing']++;
                }
                if (empty($assignment['penguji_1']) || empty($assignment['penguji_2'])) {
                    $summary[$angkatan]['missing_penguji']++;
                }
            }

            krsort($summary);

            $this->render('students/assignment_status', [
                'students' => $rows,
                'roleLabels' => $roleLabels,
                'angkatanList' => $angkatanList,
                'selectedAngkatan' => $angkatanFilter,
                'currentSearch' => $searchFilter,
                'summary' => $summary,
                'includeLulus' => $includeLulus
            ]);
        } catch (Exception $e) {
            $this->render('students/assignment_status', [
                'students' => [],
                'roleLabels' => [],
                'angkatanList' => [],
                'selectedAngkatan' => $angkatanFilter,
                'currentSearch' => $searchFilter,
                'summary' => [],
                'includeLulus' => $includeLulus,
                'error' => 'Gagal memuat data penetapan: ' . $e->getMessage()
            ]);
        }
    }

    private function ensureStudentUser(string $nim, string $name, ?int $existingUserId = null): int {
        if ($existingUserId) {
            $update = "UPDATE users SET username = :username, name = :name, role = :role, nim = :nim WHERE id = :id";
            $stmt = $this->db->prepare($update);
            $stmt->bindParam(':username', $nim);
            $stmt->bindParam(':name', $name);
            $stmt->bindValue(':role', 'mahasiswa');
            $stmt->bindParam(':nim', $nim);
            $stmt->bindParam(':id', $existingUserId, PDO::PARAM_INT);
            $stmt->execute();
            return $existingUserId;
        }

        $userQuery = "SELECT id FROM users WHERE username = :username LIMIT 1";
        $userStmt = $this->db->prepare($userQuery);
        $userStmt->bindParam(':username', $nim);
        $userStmt->execute();
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $update = "UPDATE users SET name = :name, role = :role, nim = :nim WHERE id = :id";
            $stmt = $this->db->prepare($update);
            $stmt->bindParam(':name', $name);
            $stmt->bindValue(':role', 'mahasiswa');
            $stmt->bindParam(':nim', $nim);
            $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
            $stmt->execute();
            return (int)$user['id'];
        }

        $insert = "INSERT INTO users (username, password, name, role, nim) VALUES (:username, :password, :name, :role, :nim)";
        $stmt = $this->db->prepare($insert);
        $password = password_hash($nim, PASSWORD_DEFAULT);
        $stmt->bindParam(':username', $nim);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':name', $name);
        $stmt->bindValue(':role', 'mahasiswa');
        $stmt->bindParam(':nim', $nim);
        $stmt->execute();
        return (int)$this->db->lastInsertId();
    }
}
