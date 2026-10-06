<?php

class ScoreController extends BaseController
{

    public function submit()
    {
        // Require authentication
        $this->requireAuth();

        // Get user role
        $role = $this->getUserRole();

        // Only pembimbing and penguji can submit scores
        if (
            !RoleHelper::isLecturerRole($role) &&
            $role !== 'superadmin' &&
            $role !== 'kombi' &&
            $role !== 'penguji_eksternal'
        ) {
            $this->redirect('/dashboard');
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        $stageLabels = $this->getStageLabels();
        $availableStages = $this->getAvailableStagesForRole($role, $stageLabels);
        if (!in_array($role, ['superadmin', 'kombi'], true)) {
            $availableStages = $this->extendStagesBasedOnAssignments($db, $availableStages, $stageLabels);
        }

        $angkatanFilter = trim($_GET['angkatan'] ?? '');
        $searchFilter = trim($_GET['search'] ?? '');
        $activeAngkatan = Settings::getActiveAngkatan();
        if ($angkatanFilter !== '' && !empty($activeAngkatan) && !in_array((int) $angkatanFilter, $activeAngkatan, true)) {
            $angkatanFilter = '';
        }

        $availableAngkatan = !empty($activeAngkatan) ? array_values($activeAngkatan) : $this->getAllAngkatan($db);
        sort($availableAngkatan);

        $students = $this->getStudentsForRole($db, $role, $angkatanFilter, $searchFilter, $activeAngkatan);
        $evaluationSummary = $this->prepareEvaluationSummary($db, $students, $availableStages, $role);

        $this->render('scores/submit', [
            'availableStages' => $availableStages,
            'students' => $evaluationSummary,
            'stageLabels' => $stageLabels,
            'role' => $role,
            'filters' => [
                'angkatan' => $angkatanFilter,
                'search' => $searchFilter
            ],
            'availableAngkatan' => $availableAngkatan,
            'usingActiveAngkatan' => !empty($activeAngkatan),
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error')
        ]);
    }

    public function exportStage($studentId, $stageCode)
    {
        $this->requireAuth();

        $role = $this->getUserRole();
        $stageCode = strtolower(urldecode($stageCode));
        $studentId = (int) $studentId;

        // For lecturer roles, check assignments instead of base role
        if (RoleHelper::isLecturerRole($role) && $role !== 'dosen') {
            $database = new Database();
            $db = $database->getConnection();
            $assignmentStmt = $db->prepare("SELECT GROUP_CONCAT(role) AS roles
                                             FROM assignments
                                             WHERE student_id = :student_id AND lecturer_id = :lecturer_id");
            $assignmentStmt->execute([
                ':student_id' => $studentId,
                ':lecturer_id' => $_SESSION['user_id'] ?? 0
            ]);
            $assignmentRow = $assignmentStmt->fetch(PDO::FETCH_ASSOC);
            $roleList = [];
            if ($assignmentRow && !empty($assignmentRow['roles'])) {
                foreach (explode(',', $assignmentRow['roles']) as $rawRole) {
                    $trimmed = trim($rawRole);
                    if ($trimmed !== '') {
                        $roleList[] = $trimmed;
                    }
                }
            }

            $stageRolePrefixes = [
                'sempro' => ['pembimbing', 'penguji'],
                'semhas' => ['pembimbing', 'penguji'],
                'pra-ujian' => ['pembimbing'],
                'ujian' => ['penguji']
            ];

            $hasAllowedRole = false;
            foreach ($roleList as $assignedRole) {
                foreach ($stageRolePrefixes[$stageCode] ?? [] as $prefix) {
                    if (strpos($assignedRole, $prefix) === 0) {
                        $hasAllowedRole = true;
                        break 2;
                    }
                }
            }

            if (!$hasAllowedRole) {
                $this->setFlash('error', 'Anda tidak diizinkan mencetak nilai pada tahap tersebut.');
                $this->redirect('/scores/submit');
                return;
            }
        } elseif (!in_array($role, ['kombi', 'superadmin', 'dosen', 'mahasiswa'], true)) {
            $this->setFlash('error', 'Anda tidak diizinkan mencetak nilai pada tahap tersebut.');
            $this->redirect('/scores/submit');
            return;
        }

        if ($studentId <= 0) {
            $this->setFlash('error', 'Mahasiswa tidak ditemukan.');
            $this->redirect('/scores/submit');
            return;
        }

        $targetEvaluatorId = null;

        if ($role === 'mahasiswa') {
            if (!Settings::isScoreVisibleForStage($stageCode)) {
                $this->setFlash('error', 'Nilai tahap tersebut belum dibuka untuk mahasiswa.');
                $this->redirect('/timeline');
                return;
            }
            $database = new Database();
            $db = $database->getConnection();
            $studentIdForUser = $this->resolveStudentId($db, (int) ($_SESSION['user_id'] ?? 0));
            if (!$studentIdForUser || $studentIdForUser !== $studentId) {
                $this->setFlash('error', 'Anda tidak diizinkan mengakses nilai mahasiswa lain.');
                $this->redirect('/timeline');
                return;
            }
            $targetEvaluatorId = null;
        } elseif (RoleHelper::isLecturerRole($role)) {
            // Assignment already checked above, just set evaluator ID
            $targetEvaluatorId = $_SESSION['user_id'] ?? null;
        } else {
            // kombi, superadmin, dosen - can specify evaluator or view all
            if (isset($_GET['evaluator']) && $_GET['evaluator'] !== '') {
                $candidate = (int) $_GET['evaluator'];
                if ($candidate > 0) {
                    $targetEvaluatorId = $candidate;
                }
            }
        }

        try {
            $data = EvaluationExportService::prepare($studentId, $stageCode, $targetEvaluatorId);
            PdfExporter::outputStageEvaluation($data['student'], $stageCode, $data['evaluations'], $data['components'], $data['schedule']);
            return;
        } catch (Exception $e) {
            $this->setFlash('error', 'Gagal membuat PDF: ' . $e->getMessage());
            $this->redirect('/scores/submit');
        }
    }

    public function exportFinalThesisScore($params)
    {
        // Debug log
        file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " - exportFinalThesisScore called with params: " . json_encode($params) . "\n", FILE_APPEND | LOCK_EX);
        
        $this->requireAuth();
        $role = $this->getUserRole();
        
        // Extract studentId from params array
        $studentId = is_array($params) ? (int) ($params['id'] ?? $params[0] ?? 0) : (int) $params;
        
        file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " - After requireAuth, studentId: $studentId\n", FILE_APPEND | LOCK_EX);

        // Check permissions
        if (!in_array($role, ['kombi', 'superadmin', 'dosen'], true) && !RoleHelper::isLecturerRole($role)) {
            $this->setFlash('error', 'Anda tidak diizinkan mengekspor nilai akhir skripsi.');
            $this->redirect('/scores/submit');
            return;
        }

        if ($studentId <= 0) {
            $this->setFlash('error', 'Mahasiswa tidak ditemukan.');
            $this->redirect('/scores/submit');
            return;
        }

        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Get student data
            $stmt = $db->prepare("SELECT s.* FROM students s WHERE s.id = :student_id");
            $stmt->bindParam(':student_id', $studentId);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $this->setFlash('error', 'Mahasiswa tidak ditemukan.');
                $this->redirect('/scores/submit');
                return;
            }
            
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            $student['title'] = $this->getAcceptedTitle($db, $studentId);
            
            // Get ujian date from events (use evaluation created_at as fallback)
            $dateStmt = $db->prepare("SELECT scheduled_date FROM events
                                      WHERE student_id = :student_id
                                      AND type = 'UJIAN_SKRIPSI'
                                      AND status = 'SELESAI'
                                      ORDER BY scheduled_date DESC
                                      LIMIT 1");
            $dateStmt->bindParam(':student_id', $studentId);
            $dateStmt->execute();
            $dateRow = $dateStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($dateRow && $dateRow['scheduled_date']) {
                $student['ujian_date'] = $dateRow['scheduled_date'];
            } else {
                // Fallback: use the earliest evaluation date for 'ujian' stage
                $evalDateStmt = $db->prepare("SELECT created_at FROM evaluations
                                             WHERE student_id = :student_id
                                             AND stage = 'ujian'
                                             ORDER BY created_at ASC
                                             LIMIT 1");
                $evalDateStmt->bindParam(':student_id', $studentId);
                $evalDateStmt->execute();
                $evalDateRow = $evalDateStmt->fetch(PDO::FETCH_ASSOC);
                $student['ujian_date'] = $evalDateRow ? $evalDateRow['created_at'] : null;
            }
            
            // Get final score data using the calculateFinalThesisScore method
            $finalScoreData = $this->calculateFinalThesisScore($db, $studentId, []);
            
            // Get chairperson data
            $chairperson = null;
            $chairStmt = $db->prepare("SELECT u.name, u.nip FROM users u
                                       JOIN assignments a ON a.lecturer_id = u.id
                                       WHERE a.student_id = :student_id AND a.role LIKE 'penguji_1'
                                       LIMIT 1");
            $chairStmt->bindParam(':student_id', $studentId);
            $chairStmt->execute();
            if ($chairStmt->rowCount() > 0) {
                $chairperson = $chairStmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Output PDF
            PdfExporter::outputFinalThesisScore($student, $finalScoreData, $chairperson);
            return;
        } catch (Exception $e) {
            $this->setFlash('error', 'Gagal membuat PDF: ' . $e->getMessage());
            $this->redirect('/scores/submit');
        }
    }

    private function getAcceptedTitle($db, $studentId)
    {
        $stmt = $db->prepare("SELECT title FROM titles
                              WHERE student_id = :student_id AND status = 'DITERIMA'
                              ORDER BY verified_at DESC LIMIT 1");
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['title'] ?? null;
    }

    public function finalize()
    {
        // Require authentication
        $this->requireAuth();

        // Only kombi and superadmin can finalize panel
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        // Process panel finalization
        // This would contain the logic to finalize scores
        $this->render('scores/finalize');
    }

    /**
     * Map of stage codes to labels.
     */
    private function getStageLabels()
    {
        return [
            'sempro' => 'Seminar Proposal (Sempro)',
            'semhas' => 'Seminar Hasil (Semhas)',
            'pra-ujian' => 'Pra-Ujian Skripsi',
            'ujian' => 'Ujian Skripsi'
        ];
    }

    /**
     * Determine which evaluation stages are available for a given role
     * Implements project specification: 
     * - pra-ujian: only for dosen_pembimbing
     * - ujian: only for dosen_penguji
     * - sempro/semhas: for both dosen_pembimbing and dosen_penguji
     * 
     * Note: We include all stages here, but the view will hide buttons based on assignment role
     */
    private function getAvailableStagesForRole(string $role, array $stageLabels): array
    {
        // Define which stages are available for each role
        $availableStages = [];

        if ($role === 'dosen_pembimbing' || $role === 'dosen') {
            $availableStages = [
                'sempro' => $stageLabels['sempro'] ?? 'Seminar Proposal',
                'semhas' => $stageLabels['semhas'] ?? 'Seminar Hasil',
                'pra-ujian' => $stageLabels['pra-ujian'] ?? 'Pra-Ujian Skripsi'
            ];
        } elseif ($role === 'dosen_penguji') {
            // Penguji tetap dapat melihat kolom pra-ujian untuk monitoring,
            // tetapi hanya pembimbing yang diberi akses edit per mahasiswa.
            $availableStages = [
                'sempro' => $stageLabels['sempro'] ?? 'Seminar Proposal',
                'semhas' => $stageLabels['semhas'] ?? 'Seminar Hasil',
                'pra-ujian' => $stageLabels['pra-ujian'] ?? 'Pra-Ujian Skripsi',
                'ujian' => $stageLabels['ujian'] ?? 'Ujian Skripsi'
            ];
        } elseif ($role === 'penguji_eksternal') {
            $availableStages = [
                'sempro' => $stageLabels['sempro'] ?? 'Seminar Proposal',
                'semhas' => $stageLabels['semhas'] ?? 'Seminar Hasil',
                'ujian' => $stageLabels['ujian'] ?? 'Ujian Skripsi'
            ];
        } elseif ($role === 'superadmin' || $role === 'kombi') {
            $availableStages = $stageLabels;
        }

        return $availableStages;
    }

    private function getStudentsForRole(PDO $db, string $role, string $angkatanFilter = '', string $searchFilter = '', ?array $activeAngkatan = null): array
    {
        if ($role === 'superadmin' || $role === 'kombi') {
            $query = "SELECT DISTINCT s.id, s.nim, s.name, s.status, s.angkatan
                      FROM students s";

            $conditions = [];
            $params = [];

            if (!empty($activeAngkatan)) {
                $placeholders = [];
                foreach ($activeAngkatan as $index => $angkatan) {
                    $placeholder = ':active_' . $index;
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = (int) $angkatan;
                }
                $conditions[] = 's.angkatan IN (' . implode(',', $placeholders) . ')';
            }

            if ($angkatanFilter !== '') {
                $conditions[] = 's.angkatan = :filter_angkatan';
                $params[':filter_angkatan'] = (int) $angkatanFilter;
            }

            if ($searchFilter !== '') {
                $conditions[] = '(s.nim LIKE :search OR s.name LIKE :search)';
                $params[':search'] = '%' . $searchFilter . '%';
            }

            if (!empty($conditions)) {
                $query .= ' WHERE ' . implode(' AND ', $conditions);
            }

            $query .= ' ORDER BY s.name';

            $stmt = $db->prepare($query);
            foreach ($params as $placeholder => $value) {
                if ($placeholder === ':search') {
                    $stmt->bindValue($placeholder, $value, PDO::PARAM_STR);
                } else {
                    $stmt->bindValue($placeholder, $value, PDO::PARAM_INT);
                }
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $query = "SELECT DISTINCT s.id, s.nim, s.name, s.status, s.angkatan
                  FROM students s
                  JOIN assignments a ON s.id = a.student_id";

        $conditions = ['a.lecturer_id = :lecturer_id'];
        $params = [];

        if (!empty($activeAngkatan)) {
            $placeholders = [];
            foreach ($activeAngkatan as $index => $angkatan) {
                $placeholder = ':active_' . $index;
                $placeholders[] = $placeholder;
                $params[$placeholder] = (int) $angkatan;
            }
            $conditions[] = 's.angkatan IN (' . implode(',', $placeholders) . ')';
        }

        if ($angkatanFilter !== '') {
            $conditions[] = 's.angkatan = :filter_angkatan';
            $params[':filter_angkatan'] = (int) $angkatanFilter;
        }

        if ($searchFilter !== '') {
            $conditions[] = '(s.nim LIKE :search OR s.name LIKE :search)';
            $params[':search'] = '%' . $searchFilter . '%';
        }

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY s.name';

        $stmt = $db->prepare($query);
        $stmt->bindValue(':lecturer_id', $_SESSION['user_id'], PDO::PARAM_INT);
        foreach ($params as $placeholder => $value) {
            if ($placeholder === ':search') {
                $stmt->bindValue($placeholder, $value, PDO::PARAM_STR);
            } else {
                $stmt->bindValue($placeholder, $value, PDO::PARAM_INT);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllAngkatan(PDO $db): array
    {
        $stmt = $db->prepare("SELECT DISTINCT angkatan FROM students WHERE angkatan IS NOT NULL AND angkatan != '' ORDER BY angkatan");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function calculateFinalThesisScore(PDO $db, int $studentId, array $stagesData)
    {
        // Debug log
        @file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " - calculateFinalThesisScore called for student $studentId\n", FILE_APPEND | LOCK_EX);
        
        // Check for bypass in Ujian stage (which we interpret as 'Final' bypass based on user request)
        if (!empty($stagesData['ujian']['evaluations'])) {
            foreach ($stagesData['ujian']['evaluations'] as $eval) {
                if (($eval['mode'] ?? '') === 'bypass') {
                    $score = (float) $eval['final_score'];
                    @file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " - Bypass found, returning score: $score\n", FILE_APPEND | LOCK_EX);
                    return [
                        'value' => $score,
                        'letter' => ScoreHelper::letterGrade($score),
                        'is_bypass' => true,
                        'formula' => [
                            'parts' => [
                                [
                                    'label' => 'Nilai Bypass',
                                    'value' => $score,
                                    'weight_percent' => '100',
                                    'weight_fraction' => 1.0
                                ]
                            ]
                        ]
                    ];
                }
            }
        }

        // Calculate Pra-Ujian Average (Raw Total Score)
        $praUjianTotal = 0;
        $praUjianCount = 0;
        
        // Fetch raw evaluations to get total_score (raw weighted sum)
        // verify current stage data or re-fetch if needed.
        // usage of 'getEvaluationStatus' does return some info but maybe not raw total_score for all.
        // Let's refetch deeply to be safe and accurate

        $query = "SELECT stage, total_score, final_score, mode, evaluator_role FROM evaluations WHERE student_id = :student_id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':student_id', $studentId);
        $stmt->execute();
        $evals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        @file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " - Found " . count($evals) . " evaluations for student $studentId\n", FILE_APPEND | LOCK_EX);
        
        $praUjianSum = 0;
        $praUjianN = 0;
        $ujianSum = 0;
        $ujianN = 0;

        foreach ($evals as $eval) {
            $score = (float) ($eval['total_score'] ?? 0); // Use Raw Score (Σ weight * score)
            @file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " - Stage: {$eval['stage']}, Score: $score\n", FILE_APPEND | LOCK_EX);

            if ($eval['stage'] === 'pra-ujian') {
                $praUjianSum += $score;
                $praUjianN++;
            } elseif ($eval['stage'] === 'ujian') {
                $ujianSum += $score;
                $ujianN++;
            }
        }

        $praUjianAvg = $praUjianN > 0 ? $praUjianSum / $praUjianN : 0;
        $ujianAvg = $ujianN > 0 ? $ujianSum / $ujianN : 0;
        
        @file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " - Pra-ujian: $praUjianSum / $praUjianN = $praUjianAvg, Ujian: $ujianSum / $ujianN = $ujianAvg\n", FILE_APPEND | LOCK_EX);

        $finalScore = $praUjianAvg + $ujianAvg;
        
        @file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " - Final score: $finalScore\n", FILE_APPEND | LOCK_EX);

        return [
            'value' => $finalScore,
            'letter' => ScoreHelper::letterGrade($finalScore),
            'is_bypass' => false,
            'formula' => [
                'parts' => [
                    [
                        'label' => 'Pra-Ujian',
                        'value' => $praUjianAvg,
                        'weight_percent' => '100',
                        'weight_fraction' => 1.0
                    ],
                    [
                        'label' => 'Ujian Skripsi',
                        'value' => $ujianAvg,
                        'weight_percent' => '100',
                        'weight_fraction' => 1.0
                    ]
                ]
            ]
        ];
    }

    private function prepareEvaluationSummary(PDO $db, array $students, array $availableStages, string $role): array
    {
        $results = [];
        $studentIds = array_column($students, 'id');
        $letterLinksMap = $this->fetchLetterLinksForStudents($db, $studentIds);
        foreach ($students as $student) {
            $studentResult = [
                'id' => $student['id'],
                'nim' => $student['nim'],
                'name' => $student['name'],
                'status' => $student['status'] ?? null,
                'angkatan' => $student['angkatan'] ?? null,
                'stages' => [],
                'letter_links' => ['pembimbing' => [], 'penguji' => []]
            ];

            $assignmentContext = $this->getAssignmentContext($db, (int) $student['id']);
            $assignmentRoles = AssignmentRoleHelper::fetchRoles($db, (int) $student['id'], (int) $_SESSION['user_id']);
            if (empty($assignmentRoles)) {
                $assignmentRoles = $this->fetchHistoricalAssignmentRoles($db, (int) $student['id'], (int) $_SESSION['user_id']);
            }

            $assignmentRoleString = !empty($assignmentRoles) ? implode(',', $assignmentRoles) : null;

            $studentResult['assignment_role'] = $assignmentRoleString;
            $studentResult['letter_links'] = $this->groupLetterLinksByRole(
                $letterLinksMap[$student['id']] ?? [],
                $assignmentRoles,
                in_array($role, ['kombi', 'superadmin'], true)
            );
            $studentResult['assignment_roles_array'] = $assignmentRoles;

            foreach ($availableStages as $stageCode => $stageLabel) {
                $summary = $this->getEvaluationStatus($db, (int) $student['id'], $stageCode, $role, $assignmentContext);
                
                // Check stage access (schedule, prerequisites, etc.)
                [$allowed, $lockReason] = EvaluationPolicy::checkStageAccess($db, (int) $student['id'], $stageCode, $role, (int) $_SESSION['user_id']);
                
                // Check edit window lock for existing evaluations
                $evaluationsLocked = false;
                $evaluationsLockReason = null;
                
                if (!empty($summary['evaluations'])) {
                    foreach ($summary['evaluations'] as $evaluation) {
                        $evalId = $evaluation['id'] ?? null;
                        if ($evalId) {
                            [$canEditEval, $lockReasonEval] = EvaluationPolicy::canEditEvaluation($db, (int) $evalId, $role);
                            if (!$canEditEval) {
                                $evaluationsLocked = true;
                                $evaluationsLockReason = $lockReasonEval;
                                break;
                            }
                        }
                    }
                }
                
                // Set locked status based on both stage access and edit window
                $summary['locked'] = !$allowed || $evaluationsLocked;
                $summary['lock_reason'] = $evaluationsLocked ? $evaluationsLockReason : $lockReason;

                // Check if this specific user can edit this stage for this specific student
                $canEditForThisStudent = $this->canRoleEditStageForStudent($stageCode, $role, $assignmentRoles);
                $summary['can_edit'] = $canEditForThisStudent && !$summary['locked'];

                if ($stageCode === 'ujian') {
                    $summary['exam_outcome'] = $studentResult['status'] ?? null;
                }
                $studentResult['stages'][$stageCode] = $summary;
            }

            // Calculate Final Thesis Score
            $studentResult['final_thesis_score'] = $this->calculateFinalThesisScore($db, $student['id'], $studentResult['stages']);

            $results[] = $studentResult;
        }

        return $results;
    }

    private function fetchLetterLinksForStudents(PDO $db, array $studentIds): array
    {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds))));
        if (empty($studentIds)) {
            return [];
        }

        AssignmentLetterLinkService::ensureTable($db);
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

        $map = [];
        $linkQuery = "
            SELECT allinks.student_id,
                   allinks.role,
                   allinks.lecturer_id,
                   allinks.letter_url,
                   allinks.source,
                   allinks.updated_at,
                   u.name AS lecturer_name
            FROM assignment_letter_links allinks
            JOIN users u ON u.id = allinks.lecturer_id
            WHERE allinks.student_id IN ($placeholders)
            ORDER BY allinks.updated_at DESC
        ";
        $linkStmt = $db->prepare($linkQuery);
        foreach ($studentIds as $index => $studentId) {
            $linkStmt->bindValue($index + 1, $studentId, PDO::PARAM_INT);
        }
        $linkStmt->execute();

        while ($row = $linkStmt->fetch(PDO::FETCH_ASSOC)) {
            $studentId = (int) ($row['student_id'] ?? 0);
            $roleKey = $row['role'] ?? '';
            $lecturerId = isset($row['lecturer_id']) ? (int) $row['lecturer_id'] : 0;
            if ($studentId <= 0 || $roleKey === '' || $lecturerId <= 0) {
                continue;
            }

            $context = $this->getAssignmentContext($db, $studentId);
            $currentLecturerId = $context['current'][$roleKey] ?? null;
            $isCurrent = $currentLecturerId !== null && (int) $currentLecturerId === $lecturerId;
            $status = 'history';
            if ($isCurrent) {
                $status = $this->isReplacementLecturer($context, $roleKey, $lecturerId) ? 'replacement' : 'current';
            } elseif ($this->isFormerLecturer($context, $roleKey, $lecturerId)) {
                $status = 'former';
            }

            if (!isset($map[$studentId])) {
                $map[$studentId] = [];
            }
            if (!isset($map[$studentId][$roleKey])) {
                $map[$studentId][$roleKey] = [];
            }
            $map[$studentId][$roleKey][] = [
                'url' => $row['letter_url'] ?? null,
                'lecturer_id' => $lecturerId,
                'lecturer_name' => $row['lecturer_name'] ?? null,
                'status' => $status,
                'source' => $row['source'] ?? 'assignment',
                'updated_at' => $row['updated_at'] ?? null
            ];
        }

        $titleQuery = "
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
        $titleStmt = $db->prepare($titleQuery);
        foreach ($studentIds as $index => $studentId) {
            $titleStmt->bindValue($index + 1, $studentId, PDO::PARAM_INT);
        }
        $titleStmt->execute();
        while ($row = $titleStmt->fetch(PDO::FETCH_ASSOC)) {
            $studentId = (int) $row['student_id'];
            $decoded = array_merge(
                $this->decodeLetterLinkJson($row['advisor_letter_link'] ?? null, 'advisor'),
                $this->decodeLetterLinkJson($row['examiner_letter_link'] ?? null, 'examiner')
            );
            if (!isset($map[$studentId])) {
                $map[$studentId] = [];
            }
            foreach ($decoded as $roleKey => $linkValue) {
                $roleLinks = $map[$studentId][$roleKey] ?? [];
                if (!empty($roleLinks)) {
                    continue;
                }
                $map[$studentId][$roleKey][] = [
                    'url' => $linkValue,
                    'lecturer_id' => null,
                    'lecturer_name' => null,
                    'status' => 'proposal',
                    'source' => 'title',
                    'updated_at' => null
                ];
            }
        }
        return $map;
    }

    private function decodeLetterLinkJson($value, string $group): array
    {
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
            $defaults = $this->getRoleGroupDefaults();
            $defaultRole = $defaults[$group] ?? null;
            if ($defaultRole) {
                $result[$defaultRole] = trim($value);
            }
        }
        return $result;
    }

    private function groupLetterLinksByRole(array $rawLinks, ?array $allowedRoles = null, bool $allowAll = false): array
    {
        if (empty($rawLinks)) {
            return ['pembimbing' => [], 'penguji' => []];
        }
        $allowedLookup = [];
        if (!$allowAll && !empty($allowedRoles)) {
            foreach ($allowedRoles as $roleCode) {
                $roleCode = trim((string) $roleCode);
                if ($roleCode !== '') {
                    $allowedLookup[$roleCode] = true;
                }
            }
        }

        $labels = $this->getAssignmentRoleLabels();
        $grouped = ['pembimbing' => [], 'penguji' => []];
        foreach ($rawLinks as $roleKey => $entries) {
            if (!is_array($entries)) {
                continue;
            }
            if (!$allowAll && (empty($allowedLookup) || !isset($allowedLookup[$roleKey]))) {
                continue;
            }
            $roleLabel = $labels[$roleKey] ?? ucwords(str_replace('_', ' ', $roleKey));
            $groupKey = strpos($roleKey, 'pembimbing') === 0 ? 'pembimbing' : (strpos($roleKey, 'penguji') === 0 ? 'penguji' : null);
            if ($groupKey === null) {
                continue;
            }
            foreach ($entries as $entry) {
                $url = is_array($entry) ? ($entry['url'] ?? null) : null;
                if (!is_string($url) || trim($url) === '') {
                    continue;
                }
                $detailParts = [];
                $lecturerName = is_array($entry) ? ($entry['lecturer_name'] ?? null) : null;
                if ($lecturerName) {
                    $detailParts[] = $lecturerName;
                }
                $statusCode = $entry['status'] ?? null;
                $statusLabel = $this->formatAssignmentStatusLabel($statusCode);
                if ($statusLabel && strtolower($statusLabel) !== 'aktif') {
                    $detailParts[] = $statusLabel;
                }
                $label = $roleLabel;
                if (!empty($detailParts)) {
                    $label .= ' • ' . implode(' • ', $detailParts);
                }
                $grouped[$groupKey][] = [
                    'label' => $label,
                    'url' => $url,
                    'status' => $statusCode,
                    'status_label' => $statusLabel,
                    'lecturer_name' => $lecturerName
                ];
            }
        }
        return $grouped;
    }

    private function getAssignmentContext(PDO $db, int $studentId): array
    {
        static $cache = [];
        if (isset($cache[$studentId])) {
            return $cache[$studentId];
        }

        $context = [
            'current' => [],
            'current_lookup' => [],
            'history_old' => [],
            'history_new' => []
        ];

        $assignStmt = $db->prepare("SELECT role, lecturer_id FROM assignments WHERE student_id = :student_id");
        $assignStmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $assignStmt->execute();
        while ($row = $assignStmt->fetch(PDO::FETCH_ASSOC)) {
            $roleKey = $row['role'] ?? '';
            $lecturerId = isset($row['lecturer_id']) ? (int) $row['lecturer_id'] : 0;
            if ($roleKey === '' || $lecturerId <= 0) {
                continue;
            }
            $context['current'][$roleKey] = $lecturerId;
            $context['current_lookup'][$lecturerId] = $roleKey;
        }

        $historyStmt = $db->prepare("
            SELECT lecturer_id_old, lecturer_id_new, role
            FROM assignment_history
            WHERE student_id = :student_id
        ");
        $historyStmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $historyStmt->execute();
        while ($row = $historyStmt->fetch(PDO::FETCH_ASSOC)) {
            $roleKey = $row['role'] ?? '';
            if ($roleKey === '') {
                continue;
            }
            $oldId = isset($row['lecturer_id_old']) ? (int) $row['lecturer_id_old'] : 0;
            $newId = isset($row['lecturer_id_new']) ? (int) $row['lecturer_id_new'] : 0;
            if ($oldId > 0) {
                if (!isset($context['history_old'][$roleKey])) {
                    $context['history_old'][$roleKey] = [];
                }
                $context['history_old'][$roleKey][$oldId] = true;
            }
            if ($newId > 0) {
                if (!isset($context['history_new'][$roleKey])) {
                    $context['history_new'][$roleKey] = [];
                }
                $context['history_new'][$roleKey][$newId] = true;
            }
        }

        $cache[$studentId] = $context;
        return $context;
    }

    private function isReplacementLecturer(array $context, ?string $roleKey, int $lecturerId): bool
    {
        if ($lecturerId <= 0 || !$roleKey) {
            return false;
        }
        return !empty($context['history_new'][$roleKey][$lecturerId]);
    }

    private function isFormerLecturer(array $context, ?string $roleKey, int $lecturerId): bool
    {
        if ($lecturerId <= 0 || !$roleKey) {
            return false;
        }
        return !empty($context['history_old'][$roleKey][$lecturerId]);
    }

    private function getAssignmentRoleLabels(): array
    {
        return [
            'pembimbing_1' => 'Pembimbing 1',
            'pembimbing_2' => 'Pembimbing 2',
            'penguji_1' => 'Penguji Ketua',
            'penguji_2' => 'Penguji Anggota 1',
            'penguji_3' => 'Penguji Anggota 2',
        ];
    }

    private function getRoleGroupDefaults(): array
    {
        return [
            'advisor' => 'pembimbing_1',
            'examiner' => 'penguji_1'
        ];
    }

    private function extendStagesBasedOnAssignments(PDO $db, array $availableStages, array $stageLabels): array
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $availableStages;
        }

        if (!isset($availableStages['ujian']) && $this->lecturerHasAssignmentRole($db, $userId, 'penguji')) {
            $availableStages['ujian'] = $stageLabels['ujian'] ?? 'Ujian Skripsi';
        }

        if (!isset($availableStages['pra-ujian']) && $this->lecturerHasAssignmentRole($db, $userId, 'pembimbing')) {
            $availableStages['pra-ujian'] = $stageLabels['pra-ujian'] ?? 'Pra-Ujian Skripsi';
        }

        return $availableStages;
    }

    private function lecturerHasAssignmentRole(PDO $db, int $lecturerId, string $rolePrefix): bool
    {
        $query = "SELECT 1 FROM assignments WHERE lecturer_id = :lecturer_id AND role LIKE :pattern LIMIT 1";
        $stmt = $db->prepare($query);
        $pattern = $rolePrefix . '%';
        $stmt->bindValue(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->bindValue(':pattern', $pattern, PDO::PARAM_STR);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Determines if a specific role can edit a specific stage for a specific student
     * Implements project specification: 
     * - pra-ujian: only dosen_pembimbing can edit
     * - ujian: only dosen_penguji can edit
     * - sempro/semhas: both dosen_pembimbing and dosen_penguji can edit
     */
    private function canRoleEditStageForStudent(string $stageCode, string $role, $assignmentRoles): bool
    {
        if ($role === 'superadmin' || $role === 'kombi') {
            return true;
        }

        $rolesArray = is_array($assignmentRoles)
            ? $assignmentRoles
            : AssignmentRoleHelper::parseConcatenated($assignmentRoles);

        if ($stageCode === 'pra-ujian') {
            return AssignmentRoleHelper::hasRolePrefix($rolesArray, 'pembimbing');
        }

        if ($stageCode === 'ujian') {
            return AssignmentRoleHelper::hasRolePrefix($rolesArray, 'penguji');
        }

        if (in_array($stageCode, ['sempro', 'semhas'], true)) {
            return !empty($rolesArray);
        }

        return false;
    }

    private function fetchHistoricalAssignmentRoles(PDO $db, int $studentId, int $lecturerId): array
    {
        if ($studentId <= 0 || $lecturerId <= 0) {
            return [];
        }
        $historyQuery = "
            SELECT role
            FROM assignment_history
            WHERE student_id = :student_id
              AND (lecturer_id_old = :lecturer_id OR lecturer_id_new = :lecturer_id)
            ORDER BY effective_date DESC, id DESC
        ";
        $stmt = $db->prepare($historyQuery);
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindValue(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->execute();
        $roles = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $roleValue = $row['role'] ?? null;
            if ($roleValue && !in_array($roleValue, $roles, true)) {
                $roles[] = $roleValue;
            }
        }
        return $roles;
    }

    private function getEvaluationStatus(PDO $db, int $studentId, string $stage, string $role, ?array $assignmentContext = null): array
    {
        $params = [
            ':student_id' => $studentId,
            ':stage' => $stage,
        ];

        $assignmentOrder = "'pembimbing_1','pembimbing_2','penguji_1','penguji_2','penguji_3','sekretaris'";
        $query = "SELECT e.id,
                         e.total_score,
                         e.final_score,
                         (
                            SELECT ROUND(SUM(es.score * 
                                CASE WHEN ec.weight > 1 THEN ec.weight / 100 ELSE ec.weight END
                            ), 2)
                            FROM evaluation_scores es
                            JOIN evaluation_components ec ON ec.id = es.component_id
                            WHERE es.evaluation_id = e.id
                         ) AS weighted_total,
                         e.mode,
                         e.updated_at,
                         e.evaluator_id,
                         e.evaluator_role,
                         u.name AS evaluator_name,
                         la.assignment_roles
                  FROM evaluations e
                  JOIN users u ON e.evaluator_id = u.id
                  LEFT JOIN (
                        SELECT student_id,
                               lecturer_id,
                               GROUP_CONCAT(role ORDER BY FIELD(role, {$assignmentOrder})) AS assignment_roles
                        FROM assignments
                        GROUP BY student_id, lecturer_id
                  ) la ON la.student_id = e.student_id AND la.lecturer_id = e.evaluator_id
                  WHERE e.student_id = :student_id
                    AND e.stage = :stage";

        if ($role !== 'superadmin' && $role !== 'kombi') {
            $query .= " AND e.evaluator_id = :evaluator_id";
            $params[':evaluator_id'] = $_SESSION['user_id'];
        }

        $query .= " ORDER BY e.updated_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute($params);

        $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [
            'evaluations' => [],
            'updatedAt' => null
        ];

        $latestUpdate = null;
        $assignmentContext = $assignmentContext ?? $this->getAssignmentContext($db, $studentId);
        foreach ($evaluations as $evaluation) {
            $assignmentRoles = AssignmentRoleHelper::parseConcatenated($evaluation['assignment_roles'] ?? null);
            $assignmentStatus = 'unassigned';
            $usedHistory = false;
            if (empty($assignmentRoles) && !empty($evaluation['evaluator_id'])) {
                $assignmentRoles = $this->fetchHistoricalAssignmentRoles($db, $studentId, (int) $evaluation['evaluator_id']);
                if (!empty($assignmentRoles)) {
                    $usedHistory = true;
                }
            }
            $stageAssignmentRole = AssignmentRoleHelper::pickRoleForStage($assignmentRoles, $stage);
            $normalizedRole = AssignmentRoleHelper::inferLecturerRole($assignmentRoles, $stage, $evaluation['evaluator_role']);

            if (!empty($evaluation['assignment_roles'])) {
                $assignmentStatus = $this->isReplacementLecturer($assignmentContext, (string) $stageAssignmentRole, (int) $evaluation['evaluator_id'])
                    ? 'replacement'
                    : 'current';
            } elseif ($usedHistory) {
                $assignmentStatus = $this->isFormerLecturer($assignmentContext, (string) $stageAssignmentRole, (int) $evaluation['evaluator_id'])
                    ? 'former'
                    : 'history';
            }

            $evaluation['assignment_role'] = $stageAssignmentRole;
            $evaluation['evaluator_role'] = $normalizedRole ?? $evaluation['evaluator_role'];
            $evaluation['assignment_status'] = $assignmentStatus;
            $evaluation['assignment_status_label'] = $this->formatAssignmentStatusLabel($assignmentStatus);

            $label = $this->formatEvaluatorLabel($evaluation['evaluator_role'], $stageAssignmentRole, 1, false);
            if ($evaluation['assignment_status_label'] && $assignmentStatus !== 'current') {
                $label .= ' (' . $evaluation['assignment_status_label'] . ')';
            }
            $evaluation['label'] = $label;

            $result['evaluations'][] = $evaluation;

            // Track the latest update timestamp
            if (!$latestUpdate || $evaluation['updated_at'] > $latestUpdate) {
                $latestUpdate = $evaluation['updated_at'];
            }
        }
        $result['evaluations'] = $this->sortEvaluationBadges($stage, $result['evaluations']);

        $result['updatedAt'] = $latestUpdate;

        // Additional check: ensure penguji cannot edit pra-ujian even if somehow listed
        if ($stage === 'pra-ujian' && $role === 'dosen_penguji') {
            // Find the assignment role for this specific student and logged-in lecturer
            $assignmentCheck = "SELECT role FROM assignments 
                               WHERE student_id = :student_id 
                               AND lecturer_id = :lecturer_id 
                               LIMIT 1";
            $assignStmt = $db->prepare($assignmentCheck);
            $assignStmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
            $assignStmt->bindValue(':lecturer_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $assignStmt->execute();
            $assignmentRole = $assignStmt->fetchColumn();

            // If this lecturer is not a pembimbing for this student, they cannot edit pra-ujian
            if ($assignmentRole && strpos($assignmentRole, 'pembimbing') === false) {
                $result['can_edit'] = false;
            }
        }

        return $result;
    }

    private function getStageEvaluatorConfig(string $stage): array
    {
        $stage = strtolower($stage);

        $default = [
            'allowed_roles' => ['dosen_pembimbing', 'dosen_penguji', 'penguji_eksternal'],
            'assignment_priority' => [
                'pembimbing_1' => 10,
                'pembimbing_2' => 11,
                'penguji_1' => 20,
                'penguji_2' => 21,
                'penguji_3' => 22,
                'sekretaris' => 23,
                'penguji_eksternal' => 24
            ],
            'role_priority' => [
                'dosen_pembimbing' => 30,
                'dosen_penguji' => 40,
                'penguji_eksternal' => 41,
                'kombi' => 90,
                'superadmin' => 91
            ]
        ];

        if ($stage === 'pra-ujian') {
            return [
                'allowed_roles' => ['dosen_pembimbing'],
                'assignment_priority' => [
                    'pembimbing_1' => 10,
                    'pembimbing_2' => 11
                ],
                'role_priority' => [
                    'dosen_pembimbing' => 30
                ]
            ];
        }

        if ($stage === 'ujian') {
            return [
                'allowed_roles' => ['dosen_penguji', 'penguji_eksternal'],
                'assignment_priority' => [
                    'penguji_1' => 10,
                    'penguji_2' => 11,
                    'penguji_3' => 12,
                    'sekretaris' => 13,
                    'penguji_eksternal' => 14
                ],
                'role_priority' => [
                    'dosen_penguji' => 30,
                    'penguji_eksternal' => 31
                ]
            ];
        }

        return $default;
    }

    /**
     * Provide a compact label for evaluator badges on the score overview table.
     */
    private function formatEvaluatorLabel(string $role, ?string $assignmentRole, int $sequence, bool $isExternal): string
    {
        $assignmentLabels = [
            'pembimbing_1' => 'Pembimbing 1',
            'pembimbing_2' => 'Pembimbing 2',
            'penguji_1' => 'Ketua Penguji',
            'penguji_2' => 'Penguji Anggota 1',
            'penguji_3' => 'Penguji Anggota 2',
            'sekretaris' => 'Sekretaris'
        ];

        $baseLabels = [
            'dosen_pembimbing' => 'Pembimbing',
            'dosen_penguji' => 'Penguji',
            'penguji_eksternal' => 'Penguji Eksternal',
            'kombi' => 'Kombi',
            'superadmin' => 'Admin'
        ];

        if ($assignmentRole && isset($assignmentLabels[$assignmentRole])) {
            $label = $assignmentLabels[$assignmentRole];
        } else {
            switch ($role) {
                case 'dosen_pembimbing':
                    $label = 'Pembimbing ' . max(1, $sequence);
                    break;
                case 'dosen':
                    $label = 'Dosen ' . max(1, $sequence);
                    break;
                case 'dosen_penguji':
                    $fallback = [
                        1 => 'Ketua Penguji',
                        2 => 'Penguji Anggota 1',
                        3 => 'Penguji Anggota 2'
                    ];
                    $label = $fallback[$sequence] ?? ('Penguji ' . $sequence);
                    break;
                case 'penguji_eksternal':
                    $label = 'Penguji Eksternal';
                    break;
                default:
                    $label = $baseLabels[$role] ?? ucfirst(str_replace('_', ' ', strtolower($role)));
                    if ($sequence > 1) {
                        $label .= ' ' . $sequence;
                    }
                    break;
            }
        }

        if ($isExternal && stripos($label, 'eksternal') === false) {
            $label .= ' (Eksternal)';
        }

        return $label;
    }

    private function formatAssignmentStatusLabel(?string $status): ?string
    {
        switch ($status) {
            case 'replacement':
                return 'Pengganti';
            case 'former':
            case 'history':
                return 'Riwayat';
            case 'current':
                return 'Aktif';
            case 'proposal':
                return 'Usulan Mahasiswa';
            case 'unassigned':
                return 'Tanpa Penetapan';
            default:
                return null;
        }
    }

    /**
     * Ensure evaluator badges stay in a predictable order (pembimbing first, lalu penguji).
     */
    private function sortEvaluationBadges(string $stage, array $evaluations): array
    {
        $config = $this->getStageEvaluatorConfig($stage);
        $assignmentPriority = $config['assignment_priority'];
        $rolePriority = $config['role_priority'];

        usort($evaluations, function (array $a, array $b) use ($assignmentPriority, $rolePriority) {
            $priorityA = $this->getEvaluationPriority($a, $assignmentPriority, $rolePriority);
            $priorityB = $this->getEvaluationPriority($b, $assignmentPriority, $rolePriority);

            if ($priorityA === $priorityB) {
                return strcmp($a['label'], $b['label']);
            }

            return $priorityA <=> $priorityB;
        });

        return $evaluations;
    }

    private function getEvaluationPriority(array $evaluation, array $assignmentPriority, array $rolePriority): int
    {
        $assignmentRole = $evaluation['assignment_role'] ?? null;
        if ($assignmentRole && isset($assignmentPriority[$assignmentRole])) {
            return $assignmentPriority[$assignmentRole];
        }

        $role = $evaluation['evaluator_role'] ?? '';
        return $rolePriority[$role] ?? 100;
    }

    private function resolveStudentId(PDO $db, int $userId): ?int
    {
        if ($userId <= 0) {
            return null;
        }
        $studentQuery = "SELECT id FROM students WHERE user_id = :user_id";
        $studentStmt = $db->prepare($studentQuery);
        $studentStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $studentStmt->execute();
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
        return $student ? (int) $student['id'] : null;
    }

    /**
     * Redirect old score recap to the merged "Data Lulusan" report
     */
    public function recap()
    {
        $this->requireAuth();
        $this->redirect('/reports/graduated');
    }

    /**
     * Display personalized score recap for lecturers
     * Shows only students the lecturer has evaluated
     */
    public function lecturerRecap()
    {
        $this->requireAuth();

        $role = $this->getUserRole();
        // Only lecturers can access their personalized recap
        if (!in_array($role, ['dosen_pembimbing', 'dosen_penguji', 'dosen'], true)) {
            $this->setFlash('error', 'Akses ditolak.');
            $this->redirect('/dashboard');
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        // Get available angkatan for filter
        $stmt = $db->prepare("SELECT DISTINCT s.angkatan
                              FROM students s
                              JOIN evaluations e ON s.id = e.student_id
                              WHERE e.evaluator_id = :lecturer_id
                              AND s.angkatan IS NOT NULL AND s.angkatan != ''
                              ORDER BY s.angkatan DESC");
        $stmt->execute([':lecturer_id' => $_SESSION['user_id']]);
        $angkatanList = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $selectedAngkatan = trim($_GET['angkatan'] ?? '');
        $currentSearch = trim($_GET['search'] ?? '');

        $this->render('scores/lecturer_recap', [
            'angkatanList' => $angkatanList,
            'selectedAngkatan' => $selectedAngkatan ?: null,
            'currentSearch' => $currentSearch,
            'role' => $role
        ]);
    }

    /**
     * AJAX data provider for lecturer's personalized score recap DataTable
     */
    public function lecturerRecapData()
    {
        // Debug: log method entry
        file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " LECTURER_RECAP: Method called\n", FILE_APPEND | LOCK_EX);
        
        $this->requireAuth();

        $role = $this->getUserRole();
        if (!in_array($role, ['dosen_pembimbing', 'dosen_penguji', 'dosen'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        $lecturerId = $_SESSION['user_id'];

        // Get filter parameters
        $selectedAngkatan = isset($_GET['angkatan']) ? trim((string)$_GET['angkatan']) : '';
        $currentSearch = isset($_GET['student_search']) ? trim((string)$_GET['student_search']) : '';

        // Build base query for students that this lecturer has evaluated
        $baseQuery = "
            SELECT DISTINCT
                s.id,
                s.nim,
                s.name,
                s.angkatan,
                s.semester_masuk,
                s.semester_lulus,
                s.status,
                (SELECT title FROM titles WHERE student_id = s.id AND status = 'DITERIMA' ORDER BY submitted_at DESC LIMIT 1) as skripsi_title
            FROM students s
            JOIN evaluations e ON s.id = e.student_id
            WHERE e.evaluator_id = :lecturer_id
        ";

        $bindings = [':lecturer_id' => $lecturerId];

        // Apply angkatan filter
        if ($selectedAngkatan !== '') {
            $baseQuery .= " AND s.angkatan = :angkatan";
            $bindings[':angkatan'] = $selectedAngkatan;
        }

        // Apply search filter
        if ($currentSearch !== '') {
            $baseQuery .= " AND (s.nim LIKE :search OR s.name LIKE :search)";
            $bindings[':search'] = "%{$currentSearch}%";
        }

        // DataTable column definitions
        // Note: searchable is set to false for all columns because we handle search in the controller
        $columns = [
            DataTablesHelper::column('nim', 0, false, true),
            DataTablesHelper::column('name', 1, false, true),
            DataTablesHelper::column('angkatan', 2, false, true),
            DataTablesHelper::column('status', 3, false, false, function($val, $row) {
                // Format status with badge
                $status = htmlspecialchars($val ?? '-');
                $badgeClass = 'bg-secondary';
                if ($status === 'LULUS') {
                    $badgeClass = 'bg-success';
                } elseif ($status === 'AKTIF') {
                    $badgeClass = 'bg-primary';
                } elseif ($status === 'CUTI') {
                    $badgeClass = 'bg-warning text-dark';
                } elseif ($status === 'KELUAR' || $status === 'DO') {
                    $badgeClass = 'bg-danger';
                }
                return '<span class="badge ' . $badgeClass . ' fs-6">' . $status . '</span>';
            }),
            DataTablesHelper::column('semester_lulus', 4, false, false),
            DataTablesHelper::column('id', 5, false, false),
            DataTablesHelper::column('skripsi_title', 6, false, false),
            DataTablesHelper::column('id', 7, false, false, function($val, $row) {
                return "<button class='btn btn-sm btn-outline-primary detail-btn' data-bs-target='#detail-{$val}'><i class='fas fa-chevron-down'></i> Detail</button>";
            }),
            DataTablesHelper::column('id', 8, false, false, function($val, $row) {
                // This will be populated with scoresDetail JSON in the enrichment step
                return '';
            }),
        ];

        // Debug: log query and bindings
        file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " LECTURER_RECAP: Query: " . $baseQuery . "\n", FILE_APPEND | LOCK_EX);
        file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " LECTURER_RECAP: Bindings: " . print_r($bindings, true) . "\n", FILE_APPEND | LOCK_EX);
        
        $result = DataTablesHelper::process($_GET, $db, $baseQuery, $columns, $bindings);

        // Debug: log result
        file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " LECTURER_RECAP: Result count: " . count($result['data'] ?? []) . "\n", FILE_APPEND | LOCK_EX);

        // Enrich data with scores
        if (!empty($result['data'])) {
            $studentIds = array_column($result['data'], 5);
            
            if (!empty($studentIds)) {
                $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

                // Get evaluations for these students (all evaluators, not just current lecturer)
                // This is needed for calculating the correct final thesis score
                $evalQuery = "
                    SELECT
                        e.student_id,
                        e.stage,
                        e.final_score,
                        e.total_score,
                        e.evaluator_role,
                        e.mode,
                        e.evaluator_id,
                        u.name as evaluator_name,
                        a.role as assignment_role
                    FROM evaluations e
                    JOIN users u ON e.evaluator_id = u.id
                    LEFT JOIN assignments a ON a.student_id = e.student_id AND a.lecturer_id = e.evaluator_id
                    WHERE e.student_id IN ({$placeholders})
                    AND e.final_score IS NOT NULL
                    ORDER BY e.student_id, e.stage, e.evaluator_id
                ";
                $evalStmt = $db->prepare($evalQuery);
                
                // Convert positional parameters to named parameters
                $namedParams = [];
                $paramKeys = [];
                foreach ($studentIds as $index => $studentId) {
                    $paramKey = ':student_id_' . $index;
                    $namedParams[$paramKey] = $studentId;
                    $paramKeys[] = $paramKey;
                }
                
                // Replace placeholders in query with named parameters
                $evalQueryWithNamed = str_replace('?', implode(',', $paramKeys), $evalQuery);
                
                $evalStmt = $db->prepare($evalQueryWithNamed);
                $evalStmt->execute($namedParams);
                $evaluations = $evalStmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $evaluations = [];
            }

            // Group evaluations by student and stage
            $scoresByStudent = [];
            foreach ($evaluations as $eval) {
                $studentId = $eval['student_id'];
                $stage = $eval['stage'];
                if (!isset($scoresByStudent[$studentId])) {
                    $scoresByStudent[$studentId] = [];
                }
                if (!isset($scoresByStudent[$studentId][$stage])) {
                    $scoresByStudent[$studentId][$stage] = [];
                }
                $scoresByStudent[$studentId][$stage][] = $eval;
            }

            // Process each student
            foreach ($result['data'] as &$row) {
                $studentId = $row[5];
                $scores = $scoresByStudent[$studentId] ?? [];

                // Calculate stage averages
                $semproScores = $scores['sempro'] ?? [];
                $semhasScores = $scores['semhas'] ?? [];
                $praUjianScores = $scores['pra-ujian'] ?? [];
                $ujianScores = $scores['ujian'] ?? [];

                // Calculate averages for ALL evaluators (pra-ujian)
                // Use total_score (raw weighted sum) with fallback to final_score
                $praUjianAvg = 0;
                if (!empty($praUjianScores)) {
                    $sum = 0;
                    $count = 0;
                    foreach ($praUjianScores as $score) {
                        $sum += (float)($score['total_score'] ?? $score['final_score']);
                        $count++;
                    }
                    $praUjianAvg = $count > 0 ? $sum / $count : 0;
                }

                // Calculate averages for ALL evaluators (ujian)
                // Use total_score (raw weighted sum) with fallback to final_score
                $ujianAvg = 0;
                if (!empty($ujianScores)) {
                    $sum = 0;
                    $count = 0;
                    foreach ($ujianScores as $score) {
                        $sum += (float)($score['total_score'] ?? $score['final_score']);
                        $count++;
                    }
                    $ujianAvg = $count > 0 ? $sum / $count : 0;
                }

                // Calculate final score
                $finalScore = $praUjianAvg + $ujianAvg;
                $finalLetter = ScoreHelper::letterGrade($finalScore);

                // Check for bypass
                $hasBypass = false;
                foreach ($ujianScores as $score) {
                    if (($score['mode'] ?? '') === 'bypass') {
                        $hasBypass = true;
                        $finalScore = (float)($score['final_score']);
                        $finalLetter = ScoreHelper::letterGrade($finalScore);
                        break;
                    }
                }

                // Build scores detail for JSON (only the current lecturer's own scores)
                $ownOnly = function (array $scores) use ($lecturerId) {
                    return array_values(array_filter($scores, function ($s) use ($lecturerId) {
                        return (int)($s['evaluator_id'] ?? 0) === (int)$lecturerId;
                    }));
                };

                $toDetail = function (array $scores, bool $includeBypass = false) {
                    return array_map(function ($s) use ($includeBypass) {
                        $detail = [
                            'evaluator_name' => $s['evaluator_name'],
                            'evaluator_role' => $s['evaluator_role'] ?? null,
                            'assignment_role' => $s['assignment_role'] ?? null,
                            'score' => ScoreHelper::normalize($s['final_score'])
                        ];
                        if ($includeBypass) {
                            $detail['is_bypass'] = ($s['mode'] ?? '') === 'bypass';
                        }
                        return $detail;
                    }, $scores);
                };

                $scoresDetail = [
                    'sempro' => $toDetail($ownOnly($semproScores)),
                    'semhas' => $toDetail($ownOnly($semhasScores)),
                    'pra-ujian' => $toDetail($ownOnly($praUjianScores)),
                    'ujian' => $toDetail($ownOnly($ujianScores), true),
                ];

                // Replace row data with formatted output
                // DataTablesHelper returns data with numeric indices (0-8)
                $row[0] = htmlspecialchars($row[0]); // nim
                $row[1] = htmlspecialchars($row[1]); // name
                $row[2] = htmlspecialchars($row[2] ?? '-'); // angkatan
                // row[3] is status (already formatted by DataTablesHelper)
                $row[4] = htmlspecialchars($row[4] ?? '-'); // semester_lulus
                $hasFinalScore = !empty($praUjianScores) || !empty($ujianScores);
                if ($hasFinalScore) {
                    $row[5] = '<span class="badge bg-primary fs-6">' . number_format($finalScore, 2) . '</span> ' .
                              '<span class="badge bg-success fs-6">' . htmlspecialchars($finalLetter) . '</span>' .
                              ($hasBypass ? ' <span class="badge bg-warning text-dark"><i class="fas fa-forward"></i> Bypass</span>' : '');
                } else {
                    $row[5] = '<span class="badge bg-secondary fs-6">Belum tersedia</span>';
                }
                $row[6] = htmlspecialchars($row[6] ?? '-'); // skripsi_title
                // row[7] is the detail button (already set by DataTablesHelper)
                // row[8] is the scoresDetail JSON for the expandable row
                $row[8] = json_encode($scoresDetail);
            }
        }

        file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " LECTURER_RECAP: Final result: " . json_encode($result) . "\n", FILE_APPEND | LOCK_EX);
        echo json_encode($result);
    }

}
