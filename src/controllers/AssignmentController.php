<?php

class AssignmentController extends BaseController
{

    private $assignmentModel;
    private $db;
    private $assignmentHistory;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        require_once MODEL_PATH . '/Assignment.php';
        require_once MODEL_PATH . '/AssignmentHistory.php';
        $this->assignmentModel = new Assignment($this->db);
        $this->assignmentHistory = new AssignmentHistory($this->db);
    }

    /**
     * Set assignments for a student
     */
    public function set()
    {
        $this->requireAuth();

        // Only kombi and superadmin can set assignments
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $errors = [];
        $success = null;
        $roleLabels = [
            'pembimbing_1' => 'Pembimbing 1',
            'pembimbing_2' => 'Pembimbing 2',
            'penguji_1' => 'Penguji Ketua',
            'penguji_2' => 'Penguji Anggota 1',
            'penguji_3' => 'Penguji Anggota 2'
        ];
        $studentId = null;
        $students = $this->getStudents();
        $lecturers = $this->getLecturers();
        $lecturerMap = [];
        foreach ($lecturers as $lecturer) {
            $lecturerMap[(int)$lecturer['id']] = $lecturer['name'];
        }
        $currentAssignments = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
            $pembimbing1 = isset($_POST['pembimbing_1']) ? intval($_POST['pembimbing_1']) : 0;
            $pembimbing2 = isset($_POST['pembimbing_2']) ? intval($_POST['pembimbing_2']) : 0;
            $pengujiKetua = isset($_POST['penguji_ketua']) ? intval($_POST['penguji_ketua']) : 0;
            $pengujiAnggota1 = isset($_POST['penguji_anggota_1']) ? intval($_POST['penguji_anggota_1']) : 0;
            $pengujiAnggota2 = isset($_POST['penguji_anggota_2']) ? intval($_POST['penguji_anggota_2']) : 0;
            $notes = trim($_POST['notes'] ?? '');
            $notesParam = ($notes === '') ? null : $notes;
            $assignedBy = $_SESSION['user_id'];
            $effectiveDate = date('Y-m-d');

            if ($studentId <= 0) {
                $errors[] = 'Mahasiswa harus dipilih.';
            }

            if ($pembimbing1 <= 0) {
                $errors[] = 'Pembimbing 1 harus dipilih.';
            }

            if ($pengujiKetua <= 0) {
                $errors[] = 'Ketua Penguji harus dipilih.';
            }

            if (empty($errors)) {
                $roleSelections = [
                    'Pembimbing 1' => $pembimbing1,
                    'Pembimbing 2' => $pembimbing2,
                    'Ketua Penguji' => $pengujiKetua,
                    'Penguji Anggota 1' => $pengujiAnggota1,
                    'Penguji Anggota 2' => $pengujiAnggota2
                ];
                $usedLecturers = [];
                foreach ($roleSelections as $roleLabel => $lecturerId) {
                    $lecturerId = (int) $lecturerId;
                    if ($lecturerId <= 0) {
                        continue;
                    }
                    if (isset($usedLecturers[$lecturerId])) {
                        $lecturerName = $lecturerMap[$lecturerId] ?? ('ID #' . $lecturerId);
                        $errors[] = sprintf(
                            'Dosen %s tidak boleh dirangkap sebagai %s dan %s pada mahasiswa yang sama.',
                            $lecturerName,
                            $usedLecturers[$lecturerId],
                            $roleLabel
                        );
                        break;
                    }
                    $usedLecturers[$lecturerId] = $roleLabel;
                }
            }

            if (empty($errors)) {
                $existingAssignments = $this->assignmentModel->getAssignmentsByStudent($studentId);
                if (!empty($existingAssignments)) {
                    $errors[] = 'Mahasiswa ini sudah memiliki penetapan aktif. Gunakan menu "Ganti Dosen" untuk melakukan perubahan.';
                    $currentAssignments = $existingAssignments;
                }
            }

            if (empty($errors)) {
                try {
                    $this->db->beginTransaction();

                    $this->assignmentModel->setAssignment($studentId, 'pembimbing_1', $pembimbing1, $notesParam, $assignedBy, $effectiveDate);

                    if ($pembimbing2 > 0) {
                        $this->assignmentModel->setAssignment($studentId, 'pembimbing_2', $pembimbing2, $notesParam, $assignedBy, $effectiveDate);
                    } else {
                        $this->assignmentModel->removeAssignment($studentId, 'pembimbing_2');
                    }

                    $this->assignmentModel->setAssignment($studentId, 'penguji_1', $pengujiKetua, $notesParam, $assignedBy, $effectiveDate);

                    if ($pengujiAnggota1 > 0) {
                        $this->assignmentModel->setAssignment($studentId, 'penguji_2', $pengujiAnggota1, $notesParam, $assignedBy, $effectiveDate);
                    } else {
                        $this->assignmentModel->removeAssignment($studentId, 'penguji_2');
                    }

                    if ($pengujiAnggota2 > 0) {
                        $this->assignmentModel->setAssignment($studentId, 'penguji_3', $pengujiAnggota2, $notesParam, $assignedBy, $effectiveDate);
                    } else {
                        $this->assignmentModel->removeAssignment($studentId, 'penguji_3');
                    }

                    $this->db->commit();

                    AuditLogger::log(
                        $assignedBy,
                        'update_assignment',
                        'students',
                        $studentId,
                        'Penetapan pembimbing dan penguji diperbarui'
                    );

                    $this->setFlash('success', 'Penetapan berhasil disimpan.');
                    $this->redirect('/assignments/set?student=' . $studentId . '&status=success');
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $errorMessage = 'Gagal menyimpan penetapan.';
                    if ($e instanceof PDOException && $e->getCode() === '23000') {
                        $errorMessage = 'Gagal menyimpan penetapan: data dosen tidak valid. Pastikan memilih dosen yang terdaftar.';
                    }
                    $errors[] = $errorMessage;
                }
            }
        } else {
            $studentId = isset($_GET['student']) ? intval($_GET['student']) : null;
        }

        if (!$success && isset($_GET['status']) && $_GET['status'] === 'success') {
            $success = 'Penetapan berhasil disimpan.';
        }

        // Fetch existing assignments for selected student (if any)
        if ($studentId && empty($currentAssignments)) {
            $currentAssignments = $this->assignmentModel->getAssignmentsByStudent($studentId);
        }

        // Get active angkatan for filter
        $activeAngkatan = Settings::getActiveAngkatan();
        $useActiveAngkatan = isset($_GET['active_angkatan']) ? (bool) $_GET['active_angkatan'] : true;

        $this->render('assignments/set', [
            'students' => $students,
            'lecturers' => $lecturers,
            'selectedStudentId' => $studentId,
            'currentAssignments' => $currentAssignments,
            'success' => $success,
            'errors' => $errors,
            'activeAngkatan' => $activeAngkatan,
            'useActiveAngkatan' => $useActiveAngkatan
        ]);
    }

    /**
     * Cancel assignments for a student
     */
    public function cancel()
    {
        $this->requireAuth();

        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $errors = [];
        $success = $this->getFlash('success');
        $selectedStudentId = isset($_GET['student']) ? intval($_GET['student']) : null;
        $reason = '';
        $selectedRolesInput = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $selectedStudentId = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
            $selectedRoles = isset($_POST['roles']) ? (array) $_POST['roles'] : [];
            $reason = trim($_POST['reason'] ?? '');

            if ($selectedStudentId <= 0) {
                $errors[] = 'Mahasiswa harus dipilih.';
            }

            if (empty($selectedRoles)) {
                $errors[] = 'Pilih minimal satu penetapan yang ingin dibatalkan.';
            }

            if (empty($errors)) {
                try {
                    $this->db->beginTransaction();

                    foreach ($selectedRoles as $role) {
                        $this->assignmentModel->removeAssignment($selectedStudentId, $role);
                    }

                    $this->db->commit();

                    AuditLogger::log(
                        $_SESSION['user_id'],
                        'cancel_assignment',
                        'students',
                        $selectedStudentId,
                        'Penetapan dibatalkan untuk: ' . implode(', ', $selectedRoles) . ($reason !== '' ? ' dengan alasan: ' . $reason : '')
                    );

                    $this->setFlash('success', 'Penetapan berhasil dibatalkan.');
                    $redirectUrl = '/assignments/cancel';
                    if ($selectedStudentId > 0) {
                        $redirectUrl .= '?student=' . $selectedStudentId;
                    }
                    $this->redirect($redirectUrl);
                    return;
                } catch (Exception $e) {
                    $this->db->rollBack();
                    $errors[] = 'Gagal membatalkan penetapan: ' . $e->getMessage();
                }
            }
        } else {
            $currentAssignments = [];
            if ($selectedStudentId) {
                $currentAssignments = $this->assignmentModel->getAssignmentsByStudent($selectedStudentId);
            }
        }

        $roleLabels = [
            'pembimbing_1' => 'Pembimbing 1',
            'pembimbing_2' => 'Pembimbing 2',
            'penguji_1' => 'Ketua Penguji',
            'penguji_2' => 'Penguji Anggota 1',
            'penguji_3' => 'Penguji Anggota 2'
        ];

        $this->render('assignments/cancel', [
            'students' => $this->getStudents(),
            'selectedStudentId' => $selectedStudentId,
            'currentAssignments' => $currentAssignments,
            'roleLabels' => $roleLabels,
            'errors' => $errors,
            'success' => $success,
            'reason' => $reason,
            'selectedRoles' => $selectedRolesInput
        ]);
    }

    /**
     * Change assignment for a student
     */
    public function change($params)
    {
        $this->requireAuth();

        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $studentId = $params['id'] ?? null;
        if (!$studentId) {
            $this->redirect('/students');
            return;
        }

        $errors = [];
        $success = null;

        // Get student info
        $studentQuery = "SELECT id, nim, name FROM students WHERE id = :id";
        $stmt = $this->db->prepare($studentQuery);
        $stmt->bindParam(':id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            $this->redirect('/students');
            return;
        }

        // Get current assignments
        $currentAssignments = $this->assignmentModel->getAssignmentsByStudent($studentId);

        // Get lecturers
        $lecturers = $this->getLecturers();

        $roleLabels = [
            'pembimbing_1' => 'Pembimbing 1',
            'pembimbing_2' => 'Pembimbing 2',
            'penguji_1' => 'Ketua Penguji',
            'penguji_2' => 'Penguji Anggota 1',
            'penguji_3' => 'Penguji Anggota 2'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $roleToChange = $_POST['role'] ?? '';
            $newLecturerId = isset($_POST['new_lecturer_id']) ? intval($_POST['new_lecturer_id']) : 0;
            $reason = trim($_POST['reason'] ?? '');
            $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d');
            $assignedBy = $_SESSION['user_id'];

            // Validate
            if (empty($roleToChange)) {
                $errors[] = 'Role harus dipilih.';
            }

            if ($newLecturerId <= 0) {
                $errors[] = 'Dosen baru harus dipilih.';
            }

            if (empty($errors) && $newLecturerId > 0) {
                $conflictQuery = "SELECT role FROM assignments WHERE student_id = :student_id AND lecturer_id = :lecturer_id AND role != :role LIMIT 1";
                $conflictStmt = $this->db->prepare($conflictQuery);
                $conflictStmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
                $conflictStmt->bindValue(':lecturer_id', $newLecturerId, PDO::PARAM_INT);
                $conflictStmt->bindValue(':role', $roleToChange, PDO::PARAM_STR);
                $conflictStmt->execute();
                $conflictRole = $conflictStmt->fetchColumn();
                if ($conflictRole) {
                    $conflictLabel = $roleLabels[$conflictRole] ?? ucfirst(str_replace('_', ' ', $conflictRole));
                    $errors[] = 'Dosen tersebut sudah ditugaskan sebagai ' . $conflictLabel . ' untuk mahasiswa ini. Pilih dosen lain.';
                }
            }

            if (empty($errors)) {
                try {
                    $this->db->beginTransaction();

                    // Get current lecturer ID for this role
                    $currentLecturerId = 0;
                    foreach ($currentAssignments as $assignment) {
                        if ($assignment['role'] === $roleToChange) {
                            $currentLecturerId = $assignment['lecturer_id'];
                            break;
                        }
                    }

                    if ($currentLecturerId <= 0) {
                        $errors[] = 'Tidak ada dosen yang ditetapkan untuk role ini.';
                        $this->db->rollBack();
                    } else {
                        // Update assignment
                        $updateQuery = "UPDATE assignments SET lecturer_id = :new_lecturer_id WHERE student_id = :student_id AND role = :role";
                        $updateStmt = $this->db->prepare($updateQuery);
                        $updateStmt->bindParam(':new_lecturer_id', $newLecturerId, PDO::PARAM_INT);
                        $updateStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                        $updateStmt->bindParam(':role', $roleToChange);
                        $updateStmt->execute();

                        // Record history
                        $historyQuery = "INSERT INTO assignment_history
                                        (student_id, lecturer_id_old, lecturer_id_new, role, reason, assigned_by, effective_date)
                                        VALUES (:student_id, :lecturer_id_old, :lecturer_id_new, :role, :reason, :assigned_by, :effective_date)";
                        $historyStmt = $this->db->prepare($historyQuery);
                        $historyStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                        $historyStmt->bindParam(':lecturer_id_old', $currentLecturerId, PDO::PARAM_INT);
                        $historyStmt->bindParam(':lecturer_id_new', $newLecturerId, PDO::PARAM_INT);
                        $historyStmt->bindParam(':role', $roleToChange);
                        $historyStmt->bindParam(':reason', $reason);
                        $historyStmt->bindParam(':assigned_by', $assignedBy, PDO::PARAM_INT);
                        $historyStmt->bindParam(':effective_date', $effectiveDate);
                        $historyStmt->execute();

                        $this->db->commit();

                        AuditLogger::log(
                            $assignedBy,
                            'change_assignment',
                            'students',
                            $studentId,
                            "Pergantian dosen untuk role $roleToChange dari $currentLecturerId ke $newLecturerId"
                        );

                        $this->setFlash('success', 'Pergantian dosen berhasil disimpan.');
                        $this->redirect('/students/' . $studentId);
                        return;
                    }
                } catch (Exception $e) {
                    if ($this->db->inTransaction()) {
                        $this->db->rollBack();
                    }
                    $errors[] = 'Gagal menyimpan pergantian: ' . $e->getMessage();
                }
            }
        }

        $flashError = $this->getFlash('error');
        if ($flashError) {
            $errors[] = $flashError;
        }

        $this->render('assignments/change', [
            'student' => $student,
            'currentAssignments' => $currentAssignments,
            'lecturers' => $lecturers,
            'roleLabels' => $roleLabels,
            'errors' => $errors,
            'success' => $success
        ]);
    }

    /**
     * View assignment history
     */
    public function history($params = null)
    {
        $this->requireAuth();

        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $assignmentHistory = new AssignmentHistory($this->db);

        // Support both GET and params-based student_id
        $studentId = null;
        if ($params && isset($params['id'])) {
            $studentId = (int) $params['id'];
        } elseif (isset($_GET['student'])) {
            $studentId = (int) $_GET['student'];
        }

        $filters = [
            'student_id' => $studentId,
            'date_from' => isset($_GET['date_from']) && $_GET['date_from'] !== '' ? $_GET['date_from'] : null,
            'date_to' => isset($_GET['date_to']) && $_GET['date_to'] !== '' ? $_GET['date_to'] : null
        ];

        $history = $this->assignmentHistory->getHistory($filters);
        $letterCache = [];
        foreach ($history as &$row) {
            $studentKey = (int)($row['student_id'] ?? 0);
            $roleKey = $row['role'] ?? '';
            $oldId = (int)($row['lecturer_id_old'] ?? 0);
            $newId = (int)($row['lecturer_id_new'] ?? 0);

            $row['letter_url_old'] = $this->resolveLetterLinkFromCache($studentKey, $oldId, $roleKey, $letterCache);
            $row['letter_url_new'] = $this->resolveLetterLinkFromCache($studentKey, $newId, $roleKey, $letterCache);
        }
        unset($row);

        // Get student info if filtering by student
        $student = null;
        if ($studentId) {
            $studentQuery = "SELECT id, nim, name FROM students WHERE id = :id";
            $stmt = $this->db->prepare($studentQuery);
            $stmt->bindParam(':id', $studentId, PDO::PARAM_INT);
            $stmt->execute();
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Get student list for filter dropdown
        $students = $this->getStudents();

        $this->render('assignments/history', [
            'history' => $history,
            'filters' => $filters,
            'student' => $student,
            'students' => $students
        ]);
    }

    /**
     * Get students for dropdown
     */
    private function getStudents()
    {
        // Get active angkatan from settings
        $activeAngkatan = Settings::getActiveAngkatan();
        $useActiveAngkatan = isset($_GET['active_angkatan']) ? (bool) $_GET['active_angkatan'] : true;
        
        $query = "SELECT s.id,
                         s.nim,
                         s.name,
                         (CASE WHEN t.id IS NOT NULL THEN 1 ELSE 0 END) AS has_approved_title,
                         t.title AS title_label,
                         COALESCE(a.assignment_count, 0) AS assignment_count
                  FROM students s
                  LEFT JOIN titles t ON s.id = t.student_id AND t.status = 'DITERIMA'
                  LEFT JOIN (
                        SELECT student_id, COUNT(*) AS assignment_count
                        FROM assignments
                        GROUP BY student_id
                  ) a ON a.student_id = s.id
                  WHERE s.status != 'LULUS' ";
        
        // Add active angkatan filter if enabled
        if ($useActiveAngkatan && !empty($activeAngkatan)) {
            $placeholders = implode(',', array_fill(0, count($activeAngkatan), '?'));
            $query .= " AND s.angkatan IN ($placeholders)";
        }
        
        $query .= " ORDER BY s.name";
        
        $stmt = $this->db->prepare($query);
        
        // Bind parameters if filtering by active angkatan
        if ($useActiveAngkatan && !empty($activeAngkatan)) {
            foreach ($activeAngkatan as $index => $angkatan) {
                $stmt->bindValue($index + 1, $angkatan);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get lecturers for dropdown
     */
    private function getLecturers()
    {
        $roles = RoleHelper::lecturerRoles();
        $placeholders = implode(',', array_fill(0, count($roles), '?'));
        $query = "
            SELECT u.id, u.name
            FROM users u
            JOIN lecturers l ON u.id = l.user_id
            WHERE u.role IN ($placeholders)
            ORDER BY u.name
        ";
        $stmt = $this->db->prepare($query);
        foreach ($roles as $index => $role) {
            $stmt->bindValue($index + 1, $role);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function resolveLetterLinkFromCache(int $studentId, int $lecturerId, string $role, array &$cache): ?string
    {
        if ($studentId <= 0 || $lecturerId <= 0 || $role === '') {
            return null;
        }
        if (!isset($cache[$studentId])) {
            $cache[$studentId] = [];
        }
        if (!isset($cache[$studentId][$lecturerId])) {
            $cache[$studentId][$lecturerId] = [];
        }
        if (!array_key_exists($role, $cache[$studentId][$lecturerId])) {
            $cache[$studentId][$lecturerId][$role] = AssignmentLetterLinkService::getLink($this->db, $studentId, $lecturerId, $role);
        }
        return $cache[$studentId][$lecturerId][$role];
    }
}
