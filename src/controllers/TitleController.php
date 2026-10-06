<?php

class TitleController extends BaseController {
    
    private $title;
    private $db;
    
    public function __construct() {
        // Connect to database
        $database = new Database();
        $this->db = $database->getConnection();
        
        // Initialize title model
        $this->title = new Title($this->db);
        AssignmentLetterLinkService::ensureTable($this->db);
    }
    
    public function submit() {
        // Require authentication
        $this->requireAuth();
        
        // Only students, kombi and superadmin can submit titles
        $role = $this->getUserRole();
        if ($role !== 'mahasiswa' && $role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        $this->ensureTitleColumns($db);
        $this->ensureTitleColumns($db);
        AssignmentLetterLinkService::ensureTable($db);
        $lecturerOptions = $this->getLecturerOptions($db);
        $lecturerMap = [];
        foreach ($lecturerOptions as $opt) {
            $lecturerMap[(int)$opt['id']] = $opt;
        }

        $roleDefinitions = $this->getRoleDefinitions();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formMode = $_POST['form_mode'] ?? 'create';
            if ($role === 'mahasiswa' && $formMode === 'update_letter_links') {
                $this->processStudentLetterLinkUpdate($db, $roleDefinitions, $lecturerOptions);
                return;
            }
            try {
                // Get POST data
                $title = trim($_POST['title'] ?? '');
                $abstract = '';
                $keywords = '';
                $proposedLecturersInput = [
                    'pembimbing_1' => $this->normalizeLecturerSelection($_POST['pembimbing_1_id'] ?? null, $lecturerMap),
                    'pembimbing_2' => $this->normalizeLecturerSelection($_POST['pembimbing_2_id'] ?? null, $lecturerMap),
                    'penguji_1' => $this->normalizeLecturerSelection($_POST['penguji_1_id'] ?? null, $lecturerMap),
                    'penguji_2' => $this->normalizeLecturerSelection($_POST['penguji_2_id'] ?? null, $lecturerMap),
                    'penguji_3' => $this->normalizeLecturerSelection($_POST['penguji_3_id'] ?? null, $lecturerMap),
                ];
                $letterLinkInputs = $this->collectLetterLinkInputs($roleDefinitions);
                $letterLinkValidationError = $this->validateLetterLinkInputs($letterLinkInputs, $proposedLecturersInput, $roleDefinitions);
                $lecturerUniquenessError = $this->validateLecturerUniqueness($proposedLecturersInput, $roleDefinitions);
                
                // Validate input
                if ($title === '' || empty($proposedLecturersInput['pembimbing_1']) || empty($proposedLecturersInput['penguji_1']) || $letterLinkValidationError || $lecturerUniquenessError) {
                    $errorMessage = $letterLinkValidationError ?: ($lecturerUniquenessError ?: 'Judul, Pembimbing 1, Penguji 1, dan link surat tugas sesuai pilihan wajib diisi.');
                    $studentAssignmentsData = [];
                    if ($role === 'mahasiswa') {
                        $studentAssignmentsData = $this->getStudentAssignmentsData($db, $this->resolveStudentId($db, $_SESSION['user_id']));
                    } elseif (!empty($_POST['student_id'])) {
                        $studentAssignmentsData = $this->getStudentAssignmentsData($db, (int)$_POST['student_id']);
                    }
                    $this->render('titles/submit', [
                        'error' => $errorMessage,
                        'formData' => [
                            'title' => $title,
                            'student_id' => $role === 'mahasiswa' ? null : ($_POST['student_id'] ?? null),
                            'pembimbing_1_id' => $proposedLecturersInput['pembimbing_1'],
                            'pembimbing_2_id' => $proposedLecturersInput['pembimbing_2'],
                            'penguji_1_id' => $proposedLecturersInput['penguji_1'],
                            'penguji_2_id' => $proposedLecturersInput['penguji_2'],
                            'penguji_3_id' => $proposedLecturersInput['penguji_3'],
                        ],
                        'letterLinks' => $letterLinkInputs,
                        'students' => ($role === 'mahasiswa') ? null : $this->getStudentsList($db),
                        'studentAssignments' => $studentAssignmentsData,
                        'lecturerOptions' => $lecturerOptions,
                        'roleMeta' => $roleDefinitions
                    ]);
                    return;
                }
                
                // For students, get their own student ID
                if ($role === 'mahasiswa') {
                    $studentId = $this->resolveStudentId($db, $_SESSION['user_id']);
                    if (!$studentId) {
                        $this->render('titles/submit', [
                            'error' => 'Data mahasiswa tidak ditemukan',
                            'studentAssignments' => [],
                            'lecturerOptions' => $lecturerOptions,
                            'letterLinks' => $letterLinkInputs,
                            'roleMeta' => $roleDefinitions
                        ]);
                        return;
                    }
                } else {
                    // For admin roles, get student ID from form
                    $studentId = $_POST['student_id'] ?? null;
                    
                    if (!$studentId) {
                        $this->render('titles/submit', [
                            'error' => 'Mahasiswa harus dipilih',
                            'formData' => [
                                'title' => $title,
                                'student_id' => null,
                                'pembimbing_1_id' => $proposedLecturersInput['pembimbing_1'],
                                'pembimbing_2_id' => $proposedLecturersInput['pembimbing_2'],
                                'penguji_1_id' => $proposedLecturersInput['penguji_1'],
                                'penguji_2_id' => $proposedLecturersInput['penguji_2'],
                                'penguji_3_id' => $proposedLecturersInput['penguji_3'],
                            ],
                            'students' => $this->getStudentsList($db, true),
                            'lecturerOptions' => $lecturerOptions,
                            'letterLinks' => $letterLinkInputs,
                            'roleMeta' => $roleDefinitions
                        ]);
                        return;
                    }
                }

                $studentAssignmentsData = $this->getStudentAssignmentsData($db, (int)$studentId);
                $groupedLetterLinks = $this->groupLetterLinksByType($letterLinkInputs, $roleDefinitions);
                $advisorLinkPayload = $this->encodeLetterLinkGroup($groupedLetterLinks['advisor'] ?? []);
                $examinerLinkPayload = $this->encodeLetterLinkGroup($groupedLetterLinks['examiner'] ?? []);

                $existingTitle = $this->findLatestTitleByStudent($db, (int)$studentId);
                if ($existingTitle) {
                    $this->render('titles/submit', [
                        'error' => 'Mahasiswa tersebut sudah memiliki pengajuan judul. Gunakan menu edit untuk melakukan perubahan.',
                        'formData' => [
                            'title' => $title,
                            'student_id' => $studentId,
                            'pembimbing_1_id' => $proposedLecturersInput['pembimbing_1'],
                            'pembimbing_2_id' => $proposedLecturersInput['pembimbing_2'],
                            'penguji_1_id' => $proposedLecturersInput['penguji_1'],
                            'penguji_2_id' => $proposedLecturersInput['penguji_2'],
                            'penguji_3_id' => $proposedLecturersInput['penguji_3'],
                        ],
                        'students' => ($role === 'mahasiswa') ? null : $this->getStudentsList($db, true),
                        'studentAssignments' => $studentAssignmentsData,
                        'existingTitle' => $existingTitle,
                        'showAdminEditLink' => ($role !== 'mahasiswa') || ($role === 'mahasiswa' && isset($existingTitle['status']) && in_array($existingTitle['status'], ['PERLU_REVISI', 'DITOLAK'])),
                        'lecturerOptions' => $lecturerOptions,
                        'letterLinks' => $letterLinkInputs,
                        'roleMeta' => $roleDefinitions
                    ]);
                    return;
                }

                $this->storeLetterLinksForLecturers($db, (int)$studentId, $proposedLecturersInput, $letterLinkInputs, 'proposal');

                // Insert title data
                $query = "INSERT INTO titles (
                            student_id,
                            title,
                            abstract,
                            keywords,
                            advisor_letter_link,
                            examiner_letter_link,
                            proposed_pembimbing_1_id,
                            proposed_pembimbing_2_id,
                            proposed_penguji_1_id,
                            proposed_penguji_2_id,
                            proposed_penguji_3_id,
                            status
                          ) 
                          VALUES (
                            :student_id,
                            :title,
                            :abstract,
                            :keywords,
                            :advisor_link,
                            :examiner_link,
                            :pembimbing1,
                            :pembimbing2,
                            :penguji1,
                            :penguji2,
                            :penguji3,
                            'MENUNGGU'
                          )";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':student_id', $studentId);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':abstract', $abstract);
                $stmt->bindParam(':keywords', $keywords);
                if ($advisorLinkPayload === null) {
                    $stmt->bindValue(':advisor_link', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':advisor_link', $advisorLinkPayload, PDO::PARAM_STR);
                }
                if ($examinerLinkPayload === null) {
                    $stmt->bindValue(':examiner_link', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':examiner_link', $examinerLinkPayload, PDO::PARAM_STR);
                }
                $stmt->bindParam(':pembimbing1', $proposedLecturersInput['pembimbing_1'], PDO::PARAM_INT);
                $stmt->bindParam(':pembimbing2', $proposedLecturersInput['pembimbing_2'], PDO::PARAM_INT);
                $stmt->bindParam(':penguji1', $proposedLecturersInput['penguji_1'], PDO::PARAM_INT);
                $stmt->bindParam(':penguji2', $proposedLecturersInput['penguji_2'], PDO::PARAM_INT);
                $stmt->bindParam(':penguji3', $proposedLecturersInput['penguji_3'], PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    AuditLogger::log($_SESSION['user_id'], 'submit_title', 'titles', (int)$db->lastInsertId(), 'Pengajuan judul baru');
                    $this->render('titles/submission_success');
                } else {
                    $this->render('titles/submit', [
                        'error' => 'Gagal menyimpan data judul',
                        'formData' => [
                            'title' => $title,
                            'student_id' => $studentId,
                            'pembimbing_1_id' => $proposedLecturersInput['pembimbing_1'],
                            'pembimbing_2_id' => $proposedLecturersInput['pembimbing_2'],
                            'penguji_1_id' => $proposedLecturersInput['penguji_1'],
                            'penguji_2_id' => $proposedLecturersInput['penguji_2'],
                            'penguji_3_id' => $proposedLecturersInput['penguji_3'],
                        ],
                        'students' => ($role === 'mahasiswa') ? null : $this->getStudentsList($db, true),
                        'studentAssignments' => $studentAssignmentsData,
                        'lecturerOptions' => $lecturerOptions,
                        'letterLinks' => $letterLinkInputs,
                        'roleMeta' => $roleDefinitions
                    ]);
                }
            } catch (Exception $e) {
                $this->render('titles/submit', [
                    'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                    'formData' => [
                        'title' => $title,
                        'student_id' => $studentId ?? null,
                        'pembimbing_1_id' => $proposedLecturersInput['pembimbing_1'] ?? null,
                        'pembimbing_2_id' => $proposedLecturersInput['pembimbing_2'] ?? null,
                        'penguji_1_id' => $proposedLecturersInput['penguji_1'] ?? null,
                        'penguji_2_id' => $proposedLecturersInput['penguji_2'] ?? null,
                        'penguji_3_id' => $proposedLecturersInput['penguji_3'] ?? null,
                    ],
                    'students' => ($role === 'mahasiswa') ? null : $this->getStudentsList($db, true),
                    'studentAssignments' => isset($studentId) ? $this->getStudentAssignmentsData($db, (int)$studentId) : [],
                    'lecturerOptions' => $lecturerOptions,
                    'letterLinks' => $letterLinkInputs,
                    'roleMeta' => $roleDefinitions
                ]);
            }
        } else {
            // For GET request, show form
            try {
                if ($role === 'mahasiswa') {
                    // Check if student already has a title
                    $studentQuery = "SELECT id FROM students WHERE user_id = :user_id";
                    $studentStmt = $db->prepare($studentQuery);
                    $studentStmt->bindParam(':user_id', $_SESSION['user_id']);
                    $studentStmt->execute();
                    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($student) {
                        $existingTitle = $this->findLatestTitleByStudent($db, (int)$student['id']);
                        if ($existingTitle) {
                            $assignmentsData = $this->getStudentAssignmentsData($db, (int)$student['id']);
                            $letterUpdateLecturers = $this->buildLetterUpdateLecturerData($db, $existingTitle, $roleDefinitions, $assignmentsData);
                            $letterHints = $this->buildLecturerHintsFromArray($letterUpdateLecturers);
                            $this->render('titles/submit', [
                                'hasTitle' => true,
                                'existingTitle' => $existingTitle,
                                'allowLetterLinkUpdate' => true,
                                'showAdminEditLink' => in_array($existingTitle['status'] ?? '', ['PERLU_REVISI', 'DITOLAK']),
                                'letterUpdateLecturers' => $letterUpdateLecturers,
                                'studentAssignments' => $assignmentsData,
                                'lecturerOptions' => $lecturerOptions,
                                'letterLinks' => $this->prepareLetterLinkFormValues($existingTitle, $roleDefinitions, $letterHints),
                                'roleMeta' => $roleDefinitions
                            ]);
                            return;
                        }

                        $assignmentsData = $this->getStudentAssignmentsData($db, (int)$student['id']);
                        $this->render('titles/submit', [
                            'studentAssignments' => $assignmentsData,
                            'lecturerOptions' => $lecturerOptions,
                            'letterLinks' => [],
                            'roleMeta' => $roleDefinitions
                        ]);
                        return;
                    }
                    
                    $this->render('titles/submit', [
                        'studentAssignments' => [],
                        'lecturerOptions' => $lecturerOptions,
                        'letterLinks' => [],
                        'roleMeta' => $roleDefinitions
                    ]);
                } else {
                    // For admin roles, get list of students
                    $this->render('titles/submit', [
                        'students' => $this->getStudentsList($db, true),
                        'lecturerOptions' => $lecturerOptions,
                        'letterLinks' => [],
                        'roleMeta' => $roleDefinitions
                    ]);
                }
            } catch (Exception $e) {
                $this->render('titles/submit', [
                    'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                    'lecturerOptions' => $lecturerOptions,
                    'letterLinks' => [],
                    'roleMeta' => $roleDefinitions
                ]);
            }
        }
    }
    
    public function verify() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can verify titles
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Get filter parameters
            $search = trim($_GET['search'] ?? '');
            $angkatan = trim($_GET['angkatan'] ?? '');
            $activeAngkatan = Settings::getActiveAngkatan();
            if ($angkatan !== '' && !empty($activeAngkatan) && !in_array((int)$angkatan, $activeAngkatan, true)) {
                $angkatan = '';
            }

            if (!empty($activeAngkatan)) {
                $angkatanList = array_values($activeAngkatan);
                sort($angkatanList);
            } else {
                $angkatanQuery = "SELECT DISTINCT angkatan FROM students WHERE angkatan IS NOT NULL AND angkatan != '' ORDER BY angkatan";
                $angkatanStmt = $db->prepare($angkatanQuery);
                $angkatanStmt->execute();
                $angkatanList = $angkatanStmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Show verification list (table loaded server-side)
            $this->render('titles/verify_list', [
                'titles' => [],
                'angkatanList' => $angkatanList,
                'currentSearch' => $search,
                'currentAngkatan' => $angkatan
            ]);
        } catch (Exception $e) {
            $this->render('titles/verify_list', ['error' => 'Gagal memuat data judul: ' . $e->getMessage()]);
        }
    }
    
    public function verifyData() {
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

        $search = trim((string)($_GET['search'] ?? ''));
        $angkatan = trim((string)($_GET['angkatan'] ?? ''));
        $activeAngkatan = Settings::getActiveAngkatan();

        $base = "t.status = 'MENUNGGU'";
        $baseParams = [];
        if (!empty($activeAngkatan)) {
            $ph = implode(',', array_fill(0, count($activeAngkatan), '?'));
            $base .= " AND s.angkatan IN ($ph)";
            foreach ($activeAngkatan as $a) {
                $baseParams[] = (int)$a;
            }
        }

        $extra = '';
        $extraParams = [];
        if ($angkatan !== '' && (empty($activeAngkatan) || in_array((int)$angkatan, $activeAngkatan, true))) {
            $extra .= " AND s.angkatan = ?";
            $extraParams[] = (int)$angkatan;
        }
        if ($search !== '') {
            $extra .= " AND (s.nim LIKE ? OR s.name LIKE ?)";
            $kw = "%{$search}%";
            $extraParams[] = $kw;
            $extraParams[] = $kw;
        }

        $from = "FROM titles t JOIN students s ON t.student_id = s.id LEFT JOIN users u ON t.verified_by = u.id";

        $totalStmt = $this->db->prepare("SELECT COUNT(*) $from WHERE $base");
        $totalStmt->execute($baseParams);
        $recordsTotal = (int)$totalStmt->fetchColumn();

        $allParams = array_merge($baseParams, $extraParams);
        $filteredStmt = $this->db->prepare("SELECT COUNT(*) $from WHERE $base$extra");
        $filteredStmt->execute($allParams);
        $recordsFiltered = (int)$filteredStmt->fetchColumn();

        $orderable = [0 => 's.nim', 1 => 's.name', 2 => 's.angkatan', 3 => 't.title', 4 => 't.submitted_at'];
        $orderCol = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : 4;
        $orderDir = strtoupper((string)($_GET['order'][0]['dir'] ?? 'DESC'));
        if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
            $orderDir = 'DESC';
        }
        $orderField = $orderable[$orderCol] ?? 't.submitted_at';
        $orderSql = "ORDER BY $orderField $orderDir, t.id ASC";

        $stmt = $this->db->prepare("SELECT t.id, t.title, t.status, t.submitted_at, s.nim, s.name AS student_name, s.angkatan, s.status AS student_status, u.name AS verified_by_name $from WHERE $base$extra $orderSql LIMIT $length OFFSET $start");
        $stmt->execute($allParams);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->jsonResponse([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function verifyDetail($params) {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can verify titles
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get title ID from params
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/titles/verify');
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Get title details
            $query = "SELECT t.*, s.nim, s.name as student_name 
                      FROM titles t
                      JOIN students s ON t.student_id = s.id
                      WHERE t.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $this->redirect('/titles/verify');
                return;
            }
            
            $title = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $roleDefinitions = $this->getRoleDefinitions();
            // Show verification detail
            $this->render('titles/verify_detail', [
                'title' => $title,
                'proposedLecturers' => $this->buildProposedLecturerDisplay($db, $title),
                'roleMeta' => $roleDefinitions,
                'letterLinks' => $this->decodeLetterLinksForTitle($title, $roleDefinitions)
            ]);
        } catch (Exception $e) {
            $this->redirect('/titles/verify');
        }
    }
    
    public function processVerification($params) {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can verify titles
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get title ID from params
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/titles/verify');
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Get form data
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';
            $currentTitle = $this->findTitleById($db, (int)$id);
            if (!$currentTitle) {
                $this->redirect('/titles/verify');
                return;
            }
            
            // Validate status
            if (!in_array($status, ['DITERIMA', 'DITOLAK', 'PERLU_REVISI'])) {
                $this->redirect("/titles/verify/{$id}");
                return;
            }
            
            // Update title status
            $query = "UPDATE titles SET 
                      status = :status,
                      notes = :notes,
                      verified_by = :verified_by,
                      verified_at = CURRENT_TIMESTAMP
                      WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':verified_by', $_SESSION['user_id']);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                if ($status === 'DITERIMA') {
                    $this->applyProposedAssignments($db, $currentTitle, (int)$_SESSION['user_id']);
                }
                AuditLogger::log(
                    $_SESSION['user_id'],
                    'verify_title',
                    'titles',
                    (int)$id,
                    'Status: ' . $status . '; Catatan: ' . $notes
                );
                $this->redirect('/titles/verify');
            } else {
                $this->redirect("/titles/verify/{$id}");
            }
        } catch (Exception $e) {
            $this->redirect("/titles/verify/{$id}");
        }
    }
    
    public function view() {
        // Require authentication
        $this->requireAuth();

        // Only kombi and superadmin can access this page
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Get filter parameters (only for non-waiting titles)
            $search = trim($_GET['search'] ?? '');
            $angkatan = trim($_GET['angkatan'] ?? '');
            $activeAngkatan = Settings::getActiveAngkatan();
            if ($angkatan !== '' && !empty($activeAngkatan) && !in_array((int)$angkatan, $activeAngkatan, true)) {
                $angkatan = '';
            }

            $from = "FROM titles t JOIN students s ON t.student_id = s.id LEFT JOIN users u ON t.verified_by = u.id";

            // Count waiting titles
            $waitingBase = "t.status = 'MENUNGGU'";
            $waitingParams = [];
            if (!empty($activeAngkatan)) {
                $ph = implode(',', array_fill(0, count($activeAngkatan), '?'));
                $waitingBase .= " AND s.angkatan IN ($ph)";
                foreach ($activeAngkatan as $a) {
                    $waitingParams[] = (int)$a;
                }
            }
            $wCountStmt = $db->prepare("SELECT COUNT(*) $from WHERE $waitingBase");
            $wCountStmt->execute($waitingParams);
            $waitingCount = (int)$wCountStmt->fetchColumn();

            // Count other titles
            $otherBase = "t.status != 'MENUNGGU'";
            $otherParams = [];
            if (!empty($activeAngkatan)) {
                $ph = implode(',', array_fill(0, count($activeAngkatan), '?'));
                $otherBase .= " AND s.angkatan IN ($ph)";
                foreach ($activeAngkatan as $a) {
                    $otherParams[] = (int)$a;
                }
            }
            if ($search !== '') {
                $otherBase .= " AND (s.nim LIKE ? OR s.name LIKE ?)";
                $kw = "%{$search}%";
                $otherParams[] = $kw;
                $otherParams[] = $kw;
            }
            if ($angkatan !== '') {
                $otherBase .= " AND s.angkatan = ?";
                $otherParams[] = (int)$angkatan;
            }
            $oCountStmt = $db->prepare("SELECT COUNT(*) $from WHERE $otherBase");
            $oCountStmt->execute($otherParams);
            $otherCount = (int)$oCountStmt->fetchColumn();

            if (!empty($activeAngkatan)) {
                $angkatanList = array_values($activeAngkatan);
                sort($angkatanList);
            } else {
                $angkatanQuery = "SELECT DISTINCT angkatan FROM students WHERE angkatan IS NOT NULL AND angkatan != '' ORDER BY angkatan";
                $angkatanStmt = $db->prepare($angkatanQuery);
                $angkatanStmt->execute();
                $angkatanList = $angkatanStmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Show titles list with split sections (tables loaded server-side)
            $this->render('titles/view', [
                'waitingCount' => $waitingCount,
                'otherCount' => $otherCount,
                'angkatanList' => $angkatanList,
                'currentSearch' => $search,
                'currentAngkatan' => $angkatan,
                'success' => $this->getFlash('success'),
                'error' => $this->getFlash('error')
            ]);
        } catch (Exception $e) {
            $this->render('titles/view', ['error' => 'Gagal memuat data judul: ' . $e->getMessage()]);
        }
    }

    public function viewData() {
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

        $type = (string)($_GET['type'] ?? 'other');
        $search = trim((string)($_GET['search'] ?? ''));
        $angkatan = trim((string)($_GET['angkatan'] ?? ''));
        $activeAngkatan = Settings::getActiveAngkatan();

        $base = ($type === 'waiting') ? "t.status = 'MENUNGGU'" : "t.status != 'MENUNGGU'";
        $baseParams = [];
        if (!empty($activeAngkatan)) {
            $ph = implode(',', array_fill(0, count($activeAngkatan), '?'));
            $base .= " AND s.angkatan IN ($ph)";
            foreach ($activeAngkatan as $a) {
                $baseParams[] = (int)$a;
            }
        }

        $extra = '';
        $extraParams = [];
        if ($type !== 'waiting') {
            if ($angkatan !== '' && (empty($activeAngkatan) || in_array((int)$angkatan, $activeAngkatan, true))) {
                $extra .= " AND s.angkatan = ?";
                $extraParams[] = (int)$angkatan;
            }
            if ($search !== '') {
                $extra .= " AND (s.nim LIKE ? OR s.name LIKE ?)";
                $kw = "%{$search}%";
                $extraParams[] = $kw;
                $extraParams[] = $kw;
            }
        }

        $from = "FROM titles t JOIN students s ON t.student_id = s.id LEFT JOIN users u ON t.verified_by = u.id";

        $totalStmt = $this->db->prepare("SELECT COUNT(*) $from WHERE $base");
        $totalStmt->execute($baseParams);
        $recordsTotal = (int)$totalStmt->fetchColumn();

        $allParams = array_merge($baseParams, $extraParams);
        $filteredStmt = $this->db->prepare("SELECT COUNT(*) $from WHERE $base$extra");
        $filteredStmt->execute($allParams);
        $recordsFiltered = (int)$filteredStmt->fetchColumn();

        if ($type === 'waiting') {
            $orderable = [0 => 's.nim', 1 => 's.name', 2 => 's.angkatan', 3 => 't.title', 4 => 't.submitted_at'];
            $defaultCol = 4;
            $defaultDir = 'ASC';
        } else {
            $orderable = [0 => 's.nim', 1 => 's.name', 2 => 's.angkatan', 3 => 't.title', 4 => 't.status', 5 => 't.submitted_at', 6 => 'u.name'];
            $defaultCol = 5;
            $defaultDir = 'DESC';
        }
        $orderCol = isset($_GET['order'][0]['column']) ? (int)$_GET['order'][0]['column'] : $defaultCol;
        $orderDir = strtoupper((string)($_GET['order'][0]['dir'] ?? $defaultDir));
        if (!in_array($orderDir, ['ASC', 'DESC'], true)) {
            $orderDir = $defaultDir;
        }
        $orderField = $orderable[$orderCol] ?? 't.submitted_at';
        $orderSql = "ORDER BY $orderField $orderDir, t.id ASC";

        $stmt = $this->db->prepare("SELECT t.id, t.title, t.status, t.submitted_at, s.nim, s.name AS student_name, s.angkatan, s.status AS student_status, u.name AS verified_by_name $from WHERE $base$extra $orderSql LIMIT $length OFFSET $start");
        $stmt->execute($allParams);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->jsonResponse([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function viewDetail($params) {
        // Require authentication
        $this->requireAuth();
        
        // Get title ID from params
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/titles/view');
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Get title details
            $query = "SELECT t.*, s.nim, s.name as student_name, 
                             u.name as verified_by_name
                      FROM titles t
                      JOIN students s ON t.student_id = s.id
                      LEFT JOIN users u ON t.verified_by = u.id
                      WHERE t.id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $this->redirect('/titles/view');
                return;
            }
            
            $title = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $roleDefinitions = $this->getRoleDefinitions();
            // Show title detail
            $this->render('titles/view_detail', [
                'title' => $title,
                'proposedLecturers' => $this->buildProposedLecturerDisplay($db, $title),
                'roleMeta' => $roleDefinitions,
                'letterLinks' => $this->decodeLetterLinksForTitle($title, $roleDefinitions)
            ]);
        } catch (Exception $e) {
            $this->redirect('/titles/view');
        }
    }

    public function edit($params) {
        $this->requireAuth();

        $role = $this->getUserRole();
        $id = $params['id'] ?? null;
        if (!$id) {
            $this->redirect('/titles/view');
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        try {
            $title = $this->findTitleById($db, (int)$id);
            if (!$title) {
                $this->redirect('/titles/view');
                return;
            }

            // Check if user can edit this title
            $canEdit = false;
            if ($role === 'kombi' || $role === 'superadmin') {
                // Admins can edit any title
                $canEdit = true;
            } elseif ($role === 'mahasiswa') {
                // Students can only edit their own titles, and only if status is PERLU_REVISI or DITOLAK
                $studentId = $this->resolveStudentId($db, $_SESSION['user_id']);
                if ((int)$title['student_id'] === $studentId && in_array($title['status'], ['PERLU_REVISI', 'DITOLAK'])) {
                    $canEdit = true;
                }
            }

            if (!$canEdit) {
                $this->setFlash('error', 'Anda tidak memiliki akses untuk mengedit judul ini.');
                $this->redirect('/titles/view');
                return;
            }
            $roleDefinitions = $this->getRoleDefinitions();

            $currentAssignments = $this->getStudentAssignmentsData($db, (int)$title['student_id']);
            $assignmentHints = $this->buildLecturerHintsFromArray($currentAssignments);
            $this->render('titles/edit', [
                'title' => $title,
                'error' => $this->getFlash('error'),
                'success' => $this->getFlash('success'),
                'lecturerOptions' => $this->getLecturerOptions($db),
                'currentAssignments' => $currentAssignments,
                'roleMeta' => $roleDefinitions,
                'letterLinks' => $this->prepareLetterLinkFormValues($title, $roleDefinitions, $assignmentHints)
            ]);
        } catch (Exception $e) {
            $this->redirect('/titles/view');
        }
    }

    public function update($params) {
        $this->requireAuth();

        $role = $this->getUserRole();
        $id = $params['id'] ?? null;
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/titles/view');
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        // Check if user can update this title
        $existingTitle = $this->findTitleById($db, (int)$id);
        if (!$existingTitle) {
            $this->setFlash('error', 'Judul tidak ditemukan.');
            $this->redirect('/titles/view');
            return;
        }

        $canUpdate = false;
        if ($role === 'kombi' || $role === 'superadmin') {
            // Admins can update any title
            $canUpdate = true;
        } elseif ($role === 'mahasiswa') {
            // Students can only update their own titles, and only if status is PERLU_REVISI or DITOLAK
            $studentId = $this->resolveStudentId($db, $_SESSION['user_id']);
            if ((int)$existingTitle['student_id'] === $studentId && in_array($existingTitle['status'], ['PERLU_REVISI', 'DITOLAK'])) {
                $canUpdate = true;
            }
        }

        if (!$canUpdate) {
            $this->setFlash('error', 'Anda tidak memiliki akses untuk mengupdate judul ini.');
            $this->redirect('/titles/view');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $lecturerOptions = $this->getLecturerOptions($db);
        $lecturerMap = [];
        foreach ($lecturerOptions as $opt) {
            $lecturerMap[(int)$opt['id']] = $opt;
        }
        $roleDefinitions = $this->getRoleDefinitions();
        $proposedLecturersInput = [
            'pembimbing_1' => $this->normalizeLecturerSelection($_POST['pembimbing_1_id'] ?? null, $lecturerMap),
            'pembimbing_2' => $this->normalizeLecturerSelection($_POST['pembimbing_2_id'] ?? null, $lecturerMap),
            'penguji_1' => $this->normalizeLecturerSelection($_POST['penguji_1_id'] ?? null, $lecturerMap),
            'penguji_2' => $this->normalizeLecturerSelection($_POST['penguji_2_id'] ?? null, $lecturerMap),
            'penguji_3' => $this->normalizeLecturerSelection($_POST['penguji_3_id'] ?? null, $lecturerMap),
        ];
        $letterLinkInputs = $this->collectLetterLinkInputs($roleDefinitions);
        $letterLinkValidationError = $this->validateLetterLinkInputs($letterLinkInputs, $proposedLecturersInput, $roleDefinitions);
        $lecturerUniquenessError = $this->validateLecturerUniqueness($proposedLecturersInput, $roleDefinitions);

        if ($title === '' || empty($proposedLecturersInput['pembimbing_1']) || empty($proposedLecturersInput['penguji_1']) || $letterLinkValidationError || $lecturerUniquenessError) {
            $errorMessage = $letterLinkValidationError ?: ($lecturerUniquenessError ?: 'Judul, Pembimbing 1, Penguji 1, dan link surat tugas sesuai pilihan wajib diisi.');
            $this->setFlash('error', $errorMessage);
            $this->redirect("/titles/{$id}/edit");
            return;
        }

        try {
            $current = $this->findTitleById($db, (int)$id);
            if (!$current) {
                $this->setFlash('error', 'Data judul tidak ditemukan.');
                $this->redirect('/titles/view');
                return;
            }
            $existingAbstract = $current['abstract'] ?? '';
            $existingKeywords = $current['keywords'] ?? '';
            $groupedLetterLinks = $this->groupLetterLinksByType($letterLinkInputs, $roleDefinitions);
            $advisorLinkPayload = $this->encodeLetterLinkGroup($groupedLetterLinks['advisor'] ?? []);
            $examinerLinkPayload = $this->encodeLetterLinkGroup($groupedLetterLinks['examiner'] ?? []);
            $this->storeLetterLinksForLecturers($db, (int)$current['student_id'], $proposedLecturersInput, $letterLinkInputs, 'manual');

            $updateQuery = "UPDATE titles SET 
                            title = :title,
                            abstract = :abstract,
                            keywords = :keywords,
                            advisor_letter_link = :advisor_link,
                            examiner_letter_link = :examiner_link,
                            proposed_pembimbing_1_id = :pembimbing1,
                            proposed_pembimbing_2_id = :pembimbing2,
                            proposed_penguji_1_id = :penguji1,
                            proposed_penguji_2_id = :penguji2,
                            proposed_penguji_3_id = :penguji3,
                            status = 'MENUNGGU',
                            notes = NULL,
                            verified_by = NULL,
                            verified_at = NULL
                            WHERE id = :id";
            $stmt = $db->prepare($updateQuery);
            $stmt->bindParam(':title', $title);
            $stmt->bindValue(':abstract', $existingAbstract);
            $stmt->bindValue(':keywords', $existingKeywords);
            if ($advisorLinkPayload === null) {
                $stmt->bindValue(':advisor_link', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':advisor_link', $advisorLinkPayload, PDO::PARAM_STR);
            }
            if ($examinerLinkPayload === null) {
                $stmt->bindValue(':examiner_link', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':examiner_link', $examinerLinkPayload, PDO::PARAM_STR);
            }
            $stmt->bindParam(':pembimbing1', $proposedLecturersInput['pembimbing_1'], PDO::PARAM_INT);
            $stmt->bindParam(':pembimbing2', $proposedLecturersInput['pembimbing_2'], PDO::PARAM_INT);
            $stmt->bindParam(':penguji1', $proposedLecturersInput['penguji_1'], PDO::PARAM_INT);
            $stmt->bindParam(':penguji2', $proposedLecturersInput['penguji_2'], PDO::PARAM_INT);
            $stmt->bindParam(':penguji3', $proposedLecturersInput['penguji_3'], PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            AuditLogger::log(
                $_SESSION['user_id'],
                'update_title',
                'titles',
                (int)$id,
                'Judul diperbarui dan status dikembalikan ke MENUNGGU'
            );

            $this->setFlash('success', 'Judul berhasil diperbarui dan dikembalikan ke status menunggu verifikasi.');

            // Redirect based on user role
            $userRole = $this->getUserRole();
            if ($userRole === 'mahasiswa') {
                $this->redirect('/dashboard');
            } else {
                $this->redirect('/titles/view');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Gagal memperbarui judul: ' . $e->getMessage());
            $this->redirect("/titles/{$id}/edit");
        }
    }

    public function delete($params) {
        $this->requireAuth();

        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $id = $params['id'] ?? null;
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/titles/view');
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        try {
            $db->beginTransaction();

            $titleStmt = $db->prepare("SELECT student_id FROM titles WHERE id = :id FOR UPDATE");
            $titleStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $titleStmt->execute();
            $titleRow = $titleStmt->fetch(PDO::FETCH_ASSOC);
            if (!$titleRow) {
                $db->rollBack();
                $this->setFlash('error', 'Data judul tidak ditemukan atau sudah dihapus.');
                $this->redirect('/titles/view');
                return;
            }

            $studentId = (int)($titleRow['student_id'] ?? 0);
            if ($studentId > 0) {
                // Delete assignment letter links for this student
                $deleteLetterLinks = $db->prepare("DELETE FROM assignment_letter_links WHERE student_id = :student_id");
                $deleteLetterLinks->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $deleteLetterLinks->execute();

                // Delete evaluations (scores) for this student
                $deleteEvaluations = $db->prepare("DELETE FROM evaluations WHERE student_id = :student_id");
                $deleteEvaluations->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $deleteEvaluations->execute();

                // Delete events (schedules) for this student
                $deleteEvents = $db->prepare("DELETE FROM events WHERE student_id = :student_id");
                $deleteEvents->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $deleteEvents->execute();

                // Delete assignments for this student
                $deleteAssignments = $db->prepare("DELETE FROM assignments WHERE student_id = :student_id");
                $deleteAssignments->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $deleteAssignments->execute();

                // Delete assignment history for this student
                $deleteHistory = $db->prepare("DELETE FROM assignment_history WHERE student_id = :student_id");
                $deleteHistory->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $deleteHistory->execute();
            }

            $stmt = $db->prepare("DELETE FROM titles WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() <= 0) {
                $db->rollBack();
                $this->setFlash('error', 'Data judul tidak ditemukan atau sudah dihapus.');
                $this->redirect('/titles/view');
                return;
            }

            $db->commit();

            AuditLogger::log(
                $_SESSION['user_id'],
                'delete_title',
                'titles',
                (int)$id,
                'Pengajuan judul serta penetapan dosen terkait dihapus'
            );
            $this->setFlash('success', 'Pengajuan judul dan penetapan dosen terkait berhasil dihapus.');

            $this->redirect('/titles/view');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->setFlash('error', 'Gagal menghapus judul: ' . $e->getMessage());
            $this->redirect('/titles/view');
        }
    }

    private function storeLetterLinksForLecturers(PDO $db, int $studentId, array $lecturerSelections, array $letterLinkInputs, string $source = 'proposal'): void {
        if ($studentId <= 0) {
            return;
        }
        foreach ($lecturerSelections as $roleKey => $lecturerId) {
            $lecturerId = (int)($lecturerId ?? 0);
            if ($lecturerId <= 0) {
                continue;
            }
            $linkValue = trim($letterLinkInputs[$roleKey] ?? '');
            if ($linkValue === '') {
                continue;
            }
            AssignmentLetterLinkService::upsert($db, $studentId, $lecturerId, $roleKey, $linkValue, $source);
        }
    }

    private function processStudentLetterLinkUpdate(PDO $db, array $roleDefinitions, array $lecturerOptions): void {
        $studentId = $this->resolveStudentId($db, (int)$_SESSION['user_id']);
        if (!$studentId) {
            $this->render('titles/submit', [
                'error' => 'Data mahasiswa tidak ditemukan',
                'studentAssignments' => [],
                'lecturerOptions' => $lecturerOptions,
                'letterLinks' => [],
                'roleMeta' => $roleDefinitions
            ]);
            return;
        }

        $existingTitle = $this->findLatestTitleByStudent($db, (int)$studentId);
        if (!$existingTitle) {
            $this->render('titles/submit', [
                'error' => 'Pengajuan judul tidak ditemukan untuk akun Anda.',
                'studentAssignments' => [],
                'lecturerOptions' => $lecturerOptions,
                'letterLinks' => [],
                'roleMeta' => $roleDefinitions
            ]);
            return;
        }

        $assignmentsData = $this->getStudentAssignmentsData($db, (int)$studentId);
        $letterLinkInputs = $this->collectLetterLinkInputs($roleDefinitions);
        $lecturerSelections = $this->buildLecturerSelectionsForTitle($existingTitle, $assignmentsData);
        $letterLinkValidationError = $this->validateLetterLinkInputs($letterLinkInputs, $lecturerSelections, $roleDefinitions);

        if ($letterLinkValidationError) {
            $this->render('titles/submit', [
                'hasTitle' => true,
                'error' => $letterLinkValidationError,
                'existingTitle' => $existingTitle,
                'allowLetterLinkUpdate' => true,
                'showAdminEditLink' => in_array($existingTitle['status'] ?? '', ['PERLU_REVISI', 'DITOLAK']),
                'letterUpdateLecturers' => $this->buildLetterUpdateLecturerData($db, $existingTitle, $roleDefinitions, $assignmentsData),
                'studentAssignments' => $assignmentsData,
                'lecturerOptions' => $lecturerOptions,
                'letterLinks' => $letterLinkInputs,
                'roleMeta' => $roleDefinitions
            ]);
            return;
        }

        $groupedLetterLinks = $this->groupLetterLinksByType($letterLinkInputs, $roleDefinitions);
        $advisorLinkPayload = $this->encodeLetterLinkGroup($groupedLetterLinks['advisor'] ?? []);
        $examinerLinkPayload = $this->encodeLetterLinkGroup($groupedLetterLinks['examiner'] ?? []);
        $this->storeLetterLinksForLecturers($db, (int)$studentId, $lecturerSelections, $letterLinkInputs, 'manual');

        try {
            $updateQuery = "UPDATE titles SET 
                            advisor_letter_link = :advisor_link,
                            examiner_letter_link = :examiner_link
                            WHERE id = :id";
            $stmt = $db->prepare($updateQuery);
            if ($advisorLinkPayload === null) {
                $stmt->bindValue(':advisor_link', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':advisor_link', $advisorLinkPayload, PDO::PARAM_STR);
            }
            if ($examinerLinkPayload === null) {
                $stmt->bindValue(':examiner_link', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':examiner_link', $examinerLinkPayload, PDO::PARAM_STR);
            }
            $stmt->bindValue(':id', $existingTitle['id'], PDO::PARAM_INT);
            $stmt->execute();

            AuditLogger::log(
                $_SESSION['user_id'],
                'update_letter_links',
                'titles',
                (int)$existingTitle['id'],
                'Mahasiswa memperbarui link surat tugas'
            );

            $freshTitle = $this->findTitleById($db, (int)$existingTitle['id']);
            $updatedLecturers = $this->buildLetterUpdateLecturerData($db, $freshTitle, $roleDefinitions, $assignmentsData);
            $updatedHints = $this->buildLecturerHintsFromArray($updatedLecturers);
            $this->render('titles/submit', [
                'hasTitle' => true,
                'success' => 'Link surat tugas berhasil diperbarui.',
                'existingTitle' => $freshTitle,
                'allowLetterLinkUpdate' => true,
                'showAdminEditLink' => in_array($freshTitle['status'] ?? '', ['PERLU_REVISI', 'DITOLAK']),
                'letterUpdateLecturers' => $updatedLecturers,
                'studentAssignments' => $assignmentsData,
                'lecturerOptions' => $lecturerOptions,
                'letterLinks' => $this->prepareLetterLinkFormValues($freshTitle, $roleDefinitions, $updatedHints),
                'roleMeta' => $roleDefinitions
            ]);
        } catch (Exception $e) {
            $letterUpdateLecturers = $this->buildLetterUpdateLecturerData($db, $existingTitle, $roleDefinitions, $assignmentsData);
            $this->render('titles/submit', [
                'hasTitle' => true,
                'error' => 'Gagal memperbarui link surat tugas: ' . $e->getMessage(),
                'existingTitle' => $existingTitle,
                'allowLetterLinkUpdate' => true,
                'showAdminEditLink' => in_array($existingTitle['status'] ?? '', ['PERLU_REVISI', 'DITOLAK']),
                'letterUpdateLecturers' => $letterUpdateLecturers,
                'studentAssignments' => $assignmentsData,
                'lecturerOptions' => $lecturerOptions,
                'letterLinks' => $letterLinkInputs,
                'roleMeta' => $roleDefinitions
            ]);
        }
    }

    private function buildLecturerSelectionsForTitle(array $title, array $assignmentsData): array {
        $roleColumns = [
            'pembimbing_1' => 'proposed_pembimbing_1_id',
            'pembimbing_2' => 'proposed_pembimbing_2_id',
            'penguji_1' => 'proposed_penguji_1_id',
            'penguji_2' => 'proposed_penguji_2_id',
            'penguji_3' => 'proposed_penguji_3_id',
        ];
        $selections = [];
        foreach ($roleColumns as $role => $column) {
            if (!empty($assignmentsData[$role]['id'])) {
                $selections[$role] = (int)$assignmentsData[$role]['id'];
                continue;
            }
            if (isset($title[$column]) && $title[$column] !== null) {
                $selections[$role] = (int)$title[$column];
            } else {
                $selections[$role] = null;
            }
        }
        return $selections;
    }

    private function buildLetterUpdateLecturerData(PDO $db, array $title, array $roleDefinitions, array $assignmentsData): array {
        $proposedDisplay = $this->buildProposedLecturerDisplay($db, $title);
        $result = [];
        foreach ($roleDefinitions as $roleKey => $meta) {
            if (!empty($assignmentsData[$roleKey])) {
                $result[$roleKey] = [
                    'id' => (int)$assignmentsData[$roleKey]['id'],
                    'name' => $assignmentsData[$roleKey]['name'],
                    'label' => $meta['label'] ?? ($proposedDisplay[$roleKey]['label'] ?? ucfirst(str_replace('_', ' ', $roleKey))),
                    'source' => 'assignment'
                ];
                continue;
            }
            $result[$roleKey] = [
                'id' => $proposedDisplay[$roleKey]['id'] ?? null,
                'name' => $proposedDisplay[$roleKey]['name'] ?? null,
                'label' => $meta['label'] ?? ($proposedDisplay[$roleKey]['label'] ?? ucfirst(str_replace('_', ' ', $roleKey))),
                'source' => 'proposal'
            ];
        }
        return $result;
    }

    private function buildLecturerHintsFromArray(array $data): array {
        $hints = [];
        foreach ($data as $roleKey => $info) {
            if (is_array($info) && !empty($info['id'])) {
                $hints[$roleKey] = ['id' => (int)$info['id']];
            } elseif (is_int($info) && $info > 0) {
                $hints[$roleKey] = ['id' => $info];
            }
        }
        return $hints;
    }

    private function resolveStudentId(PDO $db, int $userId): ?int {
        $studentQuery = "SELECT id FROM students WHERE user_id = :user_id";
        $studentStmt = $db->prepare($studentQuery);
        $studentStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $studentStmt->execute();
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
        return $student ? (int)$student['id'] : null;
    }

    private function getStudentAssignmentsData(PDO $db, ?int $studentId): array {
        if (!$studentId) {
            return [];
        }

        $assignmentsQuery = "
            SELECT a.role, a.lecturer_id, u.name AS lecturer_name
            FROM assignments a
            JOIN users u ON u.id = a.lecturer_id
            WHERE a.student_id = :student_id
            ORDER BY FIELD(a.role, 'pembimbing_1','pembimbing_2','penguji_1','penguji_2','penguji_3')
        ";
        $stmt = $db->prepare($assignmentsQuery);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $row) {
            $map[$row['role']] = [
                'id' => (int)$row['lecturer_id'],
                'name' => $row['lecturer_name']
            ];
        }
        return $map;
    }

    private function getStudentsList(PDO $db, bool $onlyWithoutTitle = false) {
        if ($onlyWithoutTitle) {
            $studentsQuery = "SELECT s.id, s.nim, s.name
                              FROM students s
                              WHERE NOT EXISTS (
                                SELECT 1 FROM titles t WHERE t.student_id = s.id
                              )
                              ORDER BY s.name";
        } else {
            $studentsQuery = "SELECT id, nim, name FROM students ORDER BY name";
        }
        $studentsStmt = $db->prepare($studentsQuery);
        $studentsStmt->execute();
        return $studentsStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function ensureTitleColumns(PDO $db): void {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;
        $columns = [
            'advisor_letter_link' => "ALTER TABLE titles ADD COLUMN advisor_letter_link TEXT NULL AFTER keywords",
            'examiner_letter_link' => "ALTER TABLE titles ADD COLUMN examiner_letter_link TEXT NULL AFTER advisor_letter_link",
            'proposed_pembimbing_1_id' => "ALTER TABLE titles ADD COLUMN proposed_pembimbing_1_id INT NULL AFTER examiner_letter_link",
            'proposed_pembimbing_2_id' => "ALTER TABLE titles ADD COLUMN proposed_pembimbing_2_id INT NULL AFTER proposed_pembimbing_1_id",
            'proposed_penguji_1_id' => "ALTER TABLE titles ADD COLUMN proposed_penguji_1_id INT NULL AFTER proposed_pembimbing_2_id",
            'proposed_penguji_2_id' => "ALTER TABLE titles ADD COLUMN proposed_penguji_2_id INT NULL AFTER proposed_penguji_1_id",
            'proposed_penguji_3_id' => "ALTER TABLE titles ADD COLUMN proposed_penguji_3_id INT NULL AFTER proposed_penguji_2_id",
        ];
        foreach ($columns as $column => $alterSql) {
            if (!$this->columnExists($db, 'titles', $column)) {
                try {
                    $db->exec($alterSql);
                } catch (Exception $e) {
                    // ignore if concurrent request already added it
                }
            }
        }
    }

    private function columnExists(PDO $db, string $table, string $column): bool {
        $query = "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':table', $table);
        $stmt->bindParam(':column', $column);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }

    private function findLatestTitleByStudent(PDO $db, int $studentId): ?array {
        $query = "SELECT * FROM titles WHERE student_id = :student_id ORDER BY submitted_at DESC LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $title = $stmt->fetch(PDO::FETCH_ASSOC);
        return $title ?: null;
    }

    private function findTitleById(PDO $db, int $titleId): ?array {
        $query = "SELECT t.*, s.nim, s.name AS student_name
                  FROM titles t
                  JOIN students s ON t.student_id = s.id
                  WHERE t.id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $titleId, PDO::PARAM_INT);
        $stmt->execute();
        $title = $stmt->fetch(PDO::FETCH_ASSOC);
        return $title ?: null;
    }

    private function getLecturerOptions(PDO $db): array {
        $roles = RoleHelper::lecturerRoles();
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $query = "SELECT id, name, role FROM users WHERE role IN ($placeholders) ORDER BY name";
        $stmt = $db->prepare($query);
        foreach ($roles as $index => $role) {
            $stmt->bindValue($index + 1, $role);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function normalizeLecturerSelection($value, array $lecturerMap): ?int {
        if ($value === null || $value === '') {
            return null;
        }
        $id = (int)$value;
        return isset($lecturerMap[$id]) ? $id : null;
    }

    private function getAssignmentRoleLabels(): array {
        return [
            'pembimbing_1' => 'Pembimbing 1',
            'pembimbing_2' => 'Pembimbing 2',
            'penguji_1' => 'Penguji Ketua',
            'penguji_2' => 'Penguji Anggota 1',
            'penguji_3' => 'Penguji Anggota 2',
        ];
    }

    private function getRoleDefinitions(): array {
        $labels = $this->getAssignmentRoleLabels();
        return [
            'pembimbing_1' => [
                'label' => $labels['pembimbing_1'],
                'group' => 'advisor',
                'required' => true,
            ],
            'pembimbing_2' => [
                'label' => $labels['pembimbing_2'],
                'group' => 'advisor',
                'required' => false,
            ],
            'penguji_1' => [
                'label' => $labels['penguji_1'],
                'group' => 'examiner',
                'required' => true,
            ],
            'penguji_2' => [
                'label' => $labels['penguji_2'],
                'group' => 'examiner',
                'required' => false,
            ],
            'penguji_3' => [
                'label' => $labels['penguji_3'],
                'group' => 'examiner',
                'required' => false,
            ],
        ];
    }

    private function collectLetterLinkInputs(array $roleDefinitions): array {
        $values = [];
        foreach ($roleDefinitions as $roleKey => $_meta) {
            $fieldName = $this->getLetterLinkFieldName($roleKey);
            $values[$roleKey] = trim($_POST[$fieldName] ?? '');
        }
        return $values;
    }

    private function validateLetterLinkInputs(array $letterLinks, array $lecturerSelections, array $roleDefinitions): ?string {
        foreach ($roleDefinitions as $roleKey => $meta) {
            $linkValue = trim($letterLinks[$roleKey] ?? '');
            $lecturerSelected = !empty($lecturerSelections[$roleKey]);
            $mandatory = !empty($meta['required']) || $lecturerSelected;
            if ($mandatory && $linkValue === '') {
                $label = $meta['label'] ?? ucfirst(str_replace('_', ' ', $roleKey));
                return 'Link surat tugas ' . $label . ' wajib diisi.';
            }
        }
        return null;
    }

    private function validateLecturerUniqueness(array $lecturerSelections, array $roleDefinitions): ?string {
        $used = [];
        foreach ($lecturerSelections as $roleKey => $lecturerId) {
            $lecturerId = (int)($lecturerId ?? 0);
            if ($lecturerId <= 0) {
                continue;
            }
            if (isset($used[$lecturerId])) {
                $firstLabel = $roleDefinitions[$used[$lecturerId]]['label'] ?? ucfirst(str_replace('_', ' ', $used[$lecturerId]));
                $secondLabel = $roleDefinitions[$roleKey]['label'] ?? ucfirst(str_replace('_', ' ', $roleKey));
                return sprintf('Dosen yang sama tidak boleh dipilih sebagai %s dan %s.', $firstLabel, $secondLabel);
            }
            $used[$lecturerId] = $roleKey;
        }
        return null;
    }

    private function groupLetterLinksByType(array $letterLinks, array $roleDefinitions): array {
        $grouped = ['advisor' => [], 'examiner' => []];
        foreach ($letterLinks as $roleKey => $value) {
            if ($value === '') {
                continue;
            }
            $group = $roleDefinitions[$roleKey]['group'] ?? 'advisor';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][$roleKey] = $value;
        }
        return $grouped;
    }

    private function encodeLetterLinkGroup(array $links): ?string {
        if (empty($links)) {
            return null;
        }
        return json_encode($links, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function decodeLetterLinkGroup($storedValue, array $roleDefinitions, string $group): array {
        $result = [];
        if (!is_string($storedValue) || trim($storedValue) === '') {
            return $result;
        }
        $decoded = json_decode($storedValue, true);
        if (is_array($decoded)) {
            foreach ($decoded as $roleKey => $link) {
                if (!is_string($link) || $link === '') {
                    continue;
                }
                if (($roleDefinitions[$roleKey]['group'] ?? null) === $group) {
                    $result[$roleKey] = $link;
                }
            }
            return $result;
        }
        if (filter_var($storedValue, FILTER_VALIDATE_URL)) {
            $fallbackRole = $this->getDefaultRoleForGroup($roleDefinitions, $group);
            if ($fallbackRole) {
                $result[$fallbackRole] = $storedValue;
            }
        }
        return $result;
    }

    private function decodeLetterLinksForTitle(array $title, array $roleDefinitions): array {
        return [
            'advisor' => $this->decodeLetterLinkGroup($title['advisor_letter_link'] ?? null, $roleDefinitions, 'advisor'),
            'examiner' => $this->decodeLetterLinkGroup($title['examiner_letter_link'] ?? null, $roleDefinitions, 'examiner'),
        ];
    }

    private function prepareLetterLinkFormValues(array $title, array $roleDefinitions, array $lecturerHints = []): array {
        $decoded = $this->decodeLetterLinksForTitle($title, $roleDefinitions);
        $legacyLinks = ($decoded['advisor'] ?? []) + ($decoded['examiner'] ?? []);
        $studentId = isset($title['student_id']) ? (int)$title['student_id'] : 0;
        $storedLinks = $studentId > 0 ? AssignmentLetterLinkService::getLinksForStudent($this->db, $studentId) : [];
        $values = [];
        foreach ($roleDefinitions as $roleKey => $_meta) {
            $hintLecturerId = null;
            if (isset($lecturerHints[$roleKey])) {
                if (is_array($lecturerHints[$roleKey]) && isset($lecturerHints[$roleKey]['id'])) {
                    $hintLecturerId = (int)$lecturerHints[$roleKey]['id'];
                } elseif (is_int($lecturerHints[$roleKey])) {
                    $hintLecturerId = $lecturerHints[$roleKey];
                }
            }
            if ($hintLecturerId && isset($storedLinks[$roleKey][$hintLecturerId])) {
                $values[$roleKey] = $storedLinks[$roleKey][$hintLecturerId];
                continue;
            }
            if (!empty($storedLinks[$roleKey])) {
                $values[$roleKey] = reset($storedLinks[$roleKey]);
                continue;
            }
            $values[$roleKey] = $legacyLinks[$roleKey] ?? '';
        }
        return $values;
    }

    private function getDefaultRoleForGroup(array $roleDefinitions, string $group): ?string {
        foreach ($roleDefinitions as $roleKey => $meta) {
            if (($meta['group'] ?? null) === $group) {
                return $roleKey;
            }
        }
        return null;
    }

    private function getLetterLinkFieldName(string $roleKey): string {
        return 'letter_link_' . $roleKey;
    }

    private function buildProposedLecturerDisplay(PDO $db, array $title): array {
        $roleColumns = [
            'pembimbing_1' => 'proposed_pembimbing_1_id',
            'pembimbing_2' => 'proposed_pembimbing_2_id',
            'penguji_1' => 'proposed_penguji_1_id',
            'penguji_2' => 'proposed_penguji_2_id',
            'penguji_3' => 'proposed_penguji_3_id',
        ];
        $roleLabels = $this->getAssignmentRoleLabels();
        $ids = [];
        foreach ($roleColumns as $column) {
            if (!empty($title[$column])) {
                $ids[] = (int)$title[$column];
            }
        }
        $lecturerNames = $this->fetchLecturerNamesByIds($db, $ids);
        $output = [];
        foreach ($roleColumns as $role => $column) {
            $lecturerId = isset($title[$column]) ? (int)$title[$column] : null;
            $output[$role] = [
                'id' => $lecturerId,
                'name' => $lecturerId && isset($lecturerNames[$lecturerId]) ? $lecturerNames[$lecturerId] : null,
                'label' => $roleLabels[$role] ?? ucfirst(str_replace('_', ' ', $role)),
            ];
        }
        return $output;
    }

    private function fetchLecturerNamesByIds(PDO $db, array $ids): array {
        $ids = array_values(array_unique(array_filter($ids)));
        if (empty($ids)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "SELECT id, name FROM users WHERE id IN ($placeholders)";
        $stmt = $db->prepare($query);
        foreach ($ids as $index => $id) {
            $stmt->bindValue($index + 1, $id, PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['id']] = $row['name'];
        }
        return $map;
    }

    private function applyProposedAssignments(PDO $db, array $title, int $assignedBy): void {
        $roleColumns = [
            'pembimbing_1' => 'proposed_pembimbing_1_id',
            'pembimbing_2' => 'proposed_pembimbing_2_id',
            'penguji_1' => 'proposed_penguji_1_id',
            'penguji_2' => 'proposed_penguji_2_id',
            'penguji_3' => 'proposed_penguji_3_id',
        ];
        $studentId = (int)$title['student_id'];
        foreach ($roleColumns as $role => $column) {
            $lecturerId = isset($title[$column]) ? (int)$title[$column] : null;
            if ($lecturerId && !$this->assignmentExists($db, $studentId, $role)) {
                $this->createAssignment($db, $studentId, $lecturerId, $role, $assignedBy);
            }
        }
    }

    private function assignmentExists(PDO $db, int $studentId, string $role): bool {
        $query = "SELECT id FROM assignments WHERE student_id = :student_id AND role = :role LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function createAssignment(PDO $db, int $studentId, int $lecturerId, string $role, int $assignedBy): void {
        $query = "INSERT INTO assignments (student_id, lecturer_id, role, reason, assigned_by, effective_date)
                  VALUES (:student_id, :lecturer_id, :role, :reason, :assigned_by, :effective_date)";
        $stmt = $db->prepare($query);
        $reason = 'Penetapan otomatis dari pengajuan judul';
        $effectiveDate = date('Y-m-d');
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':assigned_by', $assignedBy, PDO::PARAM_INT);
        $stmt->bindParam(':effective_date', $effectiveDate);
        $stmt->execute();
    }
}
