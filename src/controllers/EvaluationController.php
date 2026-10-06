<?php

class EvaluationController extends BaseController
{

    public function showForm($studentId, $stage)
    {
        // Require authentication
        $this->requireAuth();

        // Only pembimbing, penguji, kombi and superadmin can access evaluation form
        $role = $this->getUserRole();
        if (
            !RoleHelper::isLecturerRole($role) &&
            $role !== 'superadmin' &&
            $role !== 'kombi'
        ) {
            $this->redirect('/dashboard');
            return;
        }

        // Enforce role-stage mapping (except for kombi and superadmin)
        if (!$this->isStageAllowedForRole($stage, $role)) {
            $this->redirect('/dashboard');
            return;
        }

        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        $canSwitchEvaluator = in_array($role, ['superadmin', 'kombi'], true);
        // Check if role is allowed to edit using controller logic (which includes assignment checks)
        $roleCanEdit = $this->isStageAllowedForRole($stage, $role);

        $examOutcomeEditable = false;
        $currentExamOutcome = null;
        $userAssignmentRole = null;
        $userAssignmentRoles = [];
        $availableEvaluations = [];
        if ($role === 'superadmin' || $role === 'kombi') {
            $selectedEvaluatorId = null;
            $activeEvaluator = [
                'id' => null,
                'name' => 'Belum dipilih',
                'role' => null
            ];
        } else {
            $selectedEvaluatorId = $_SESSION['user_id'] ?? null;
            $activeEvaluator = [
                'id' => $_SESSION['user_id'] ?? null,
                'name' => $_SESSION['user_name'] ?? '',
                'role' => $_SESSION['role'] ?? ''
            ];
        }

        try {
            // For superadmin and kombi, allow access to any student
            if ($role === 'superadmin' || $role === 'kombi') {
                // Get student data
                $studentQuery = "SELECT s.*, u.name as lecturer_name FROM students s 
                                LEFT JOIN assignments a ON s.id = a.student_id 
                                LEFT JOIN users u ON a.lecturer_id = u.id
                                WHERE s.id = :student_id";
                $studentStmt = $db->prepare($studentQuery);
                $studentStmt->bindParam(':student_id', $studentId);
                $studentStmt->execute();

                if ($studentStmt->rowCount() == 0) {
                    $this->redirect('/students');
                    return;
                }

                $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
                $student['thesis_title'] = $this->getAcceptedTitle($db, $studentId);
            } else {
                // For regular lecturers, check if they are assigned to this student
                $assignmentQuery = "SELECT s.*, u.name as lecturer_name FROM students s 
                                   JOIN assignments a ON s.id = a.student_id 
                                   JOIN users u ON a.lecturer_id = u.id 
                                   WHERE s.id = :student_id AND a.lecturer_id = :lecturer_id
                                   LIMIT 1";
                $assignmentStmt = $db->prepare($assignmentQuery);
                $assignmentStmt->bindParam(':student_id', $studentId);
                $assignmentStmt->bindParam(':lecturer_id', $_SESSION['user_id']);
                $assignmentStmt->execute();

                if ($assignmentStmt->rowCount() == 0) {
                    // Not assigned to this student
                    $this->redirect('/students');
                    return;
                }

                $student = $assignmentStmt->fetch(PDO::FETCH_ASSOC);
                $student['thesis_title'] = $this->getAcceptedTitle($db, $studentId);
                $userAssignmentRoles = AssignmentRoleHelper::fetchRoles($db, $studentId, (int) $_SESSION['user_id']);
                $userAssignmentRole = AssignmentRoleHelper::pickRoleForStage($userAssignmentRoles, $stage);

                if ($stage === 'pra-ujian' && !AssignmentRoleHelper::hasRolePrefix($userAssignmentRoles, 'pembimbing')) {
                    $this->setFlash('error', 'Akses ditolak: Tahap pra-ujian hanya untuk dosen pembimbing mahasiswa ini.');
                    $this->redirect('/scores/submit');
                    return;
                }

                if (!empty($userAssignmentRoles)) {
                    $activeEvaluator['role'] = AssignmentRoleHelper::inferLecturerRole($userAssignmentRoles, $stage, $activeEvaluator['role']);
                }
            }

            // Load available evaluations for stage
            $evaluationListStmt = $db->prepare("
                SELECT e.id,
                       e.evaluator_id,
                       e.mode,
                       e.updated_at,
                       u.name AS evaluator_name,
                       u.role AS evaluator_role
                FROM evaluations e
                JOIN users u ON u.id = e.evaluator_id
                WHERE e.student_id = :student_id
                  AND e.stage = :stage
                ORDER BY e.updated_at DESC
            ");
            $evaluationListStmt->bindParam(':student_id', $student['id'], PDO::PARAM_INT);
            $evaluationListStmt->bindParam(':stage', $stage);
            $evaluationListStmt->execute();
            $rawEvaluations = $evaluationListStmt->fetchAll(PDO::FETCH_ASSOC);
            $availableEvaluations = [];
            $seenEvaluators = [];
            foreach ($rawEvaluations as $evalMeta) {
                $evaluatorId = (int) $evalMeta['evaluator_id'];
                if (isset($seenEvaluators[$evaluatorId])) {
                    continue;
                }
                $seenEvaluators[$evaluatorId] = true;
                $availableEvaluations[] = $evalMeta;
            }

            if ($canSwitchEvaluator && empty($availableEvaluations)) {
                $selectedEvaluatorId = null;
            } elseif ($canSwitchEvaluator) {
                $requestedEvaluatorId = isset($_GET['evaluator_id']) ? trim($_GET['evaluator_id']) : null;
                if ($requestedEvaluatorId !== null && ctype_digit($requestedEvaluatorId)) {
                    $requestedEvaluatorId = (int) $requestedEvaluatorId;
                    foreach ($availableEvaluations as $evalMeta) {
                        if ((int) $evalMeta['evaluator_id'] === $requestedEvaluatorId) {
                            $selectedEvaluatorId = $requestedEvaluatorId;
                            break;
                        }
                    }
                }
                if ($selectedEvaluatorId === null && !empty($availableEvaluations)) {
                    $selectedEvaluatorId = (int) $availableEvaluations[0]['evaluator_id'];
                }
                if ($selectedEvaluatorId === null) {
                    $selectedEvaluatorId = $_SESSION['user_id'] ?? null;
                }
            } else {
                $selectedEvaluatorId = $_SESSION['user_id'] ?? null;
            }

            if ($selectedEvaluatorId !== null) {
                $activeUser = $this->getUserById($db, (int) $selectedEvaluatorId);
                if ($activeUser) {
                    // Get assignment roles for the selected evaluator
                    $selectedEvaluatorRoles = AssignmentRoleHelper::fetchRoles($db, $studentId, (int) $selectedEvaluatorId);
                    $selectedEvaluatorAssignmentRole = AssignmentRoleHelper::pickRoleForStage($selectedEvaluatorRoles, $stage);
                    
                    $activeEvaluator = [
                        'id' => (int) $selectedEvaluatorId,
                        'name' => $activeUser['name'],
                        'role' => $activeUser['role'],
                        'assignment_role' => $selectedEvaluatorAssignmentRole
                    ];
                }
            } elseif ($canSwitchEvaluator) {
                $activeEvaluator = [
                    'id' => null,
                    'name' => 'Belum dipilih',
                    'role' => null,
                    'assignment_role' => null
                ];
            }

            $currentExamOutcome = $student['status'] ?? null;

            if ($stage === 'ujian') {
                // Check if user is Ketua Penguji (penguji_1) for this student
                // This works even if user's session role is dosen_pembimbing but they're assigned as penguji_1
                if ($userAssignmentRole === 'penguji_1' || $role === 'superadmin' || $role === 'kombi') {
                    $examOutcomeEditable = true;
                } else {
                    $examOutcomeEditable = false;
                }

                // DEBUG: Log values for troubleshooting
                $debugInfo = sprintf(
                    "DEBUG [Exam Outcome]: Role=%s, SessionUserID=%s, StudentID=%s, userAssignmentRole=%s, examOutcomeEditable=%s",
                    $role,
                    $_SESSION['user_id'] ?? 'null',
                    $studentId,
                    $userAssignmentRole ?? 'null',
                    $examOutcomeEditable ? 'true' : 'false'
                );
                error_log($debugInfo);

                // Also write to debug file for easier access
                file_put_contents(
                    __DIR__ . '/../storage/logs/exam_debug.log',
                    date('Y-m-d H:i:s') . ' - ' . $debugInfo . "\n",
                    FILE_APPEND
                );
            }

            [$allowed, $lockReason] = EvaluationPolicy::checkStageAccess($db, (int) $student['id'], $stage, $role, (int) $_SESSION['user_id']);
            if (!$allowed) {
                $this->setFlash('error', $lockReason);
                $this->redirect('/scores/submit');
                return;
            }

            // Get evaluation components for this stage
            $componentsQuery = "SELECT * FROM evaluation_components WHERE stage = :stage ORDER BY sort_order";
            $componentsStmt = $db->prepare($componentsQuery);
            $componentsStmt->bindParam(':stage', $stage);
            $componentsStmt->execute();
            $components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get existing evaluation if any
            $evaluation = null;
            $evaluationComponents = [];
            if ($selectedEvaluatorId !== null) {
                $evaluationQuery = "SELECT * FROM evaluations WHERE student_id = :student_id AND evaluator_id = :evaluator_id AND stage = :stage";
                $evaluationStmt = $db->prepare($evaluationQuery);
                $evaluationStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $evaluationStmt->bindParam(':evaluator_id', $selectedEvaluatorId, PDO::PARAM_INT);
                $evaluationStmt->bindParam(':stage', $stage);
                $evaluationStmt->execute();

                if ($evaluationStmt->rowCount() > 0) {
                    $evaluation = $evaluationStmt->fetch(PDO::FETCH_ASSOC);
                    if ($evaluation) {
                        $evaluation['final_score'] = ScoreHelper::normalize($evaluation['final_score'] ?? null);
                        $evaluation['total_score'] = isset($evaluation['total_score']) ? (float) $evaluation['total_score'] : null;
                    }

                    // Get component scores for this evaluation
                    $evalComponentsQuery = "SELECT ec.*, es.score FROM evaluation_components ec 
                                           LEFT JOIN evaluation_scores es ON ec.id = es.component_id AND es.evaluation_id = :evaluation_id
                                           WHERE ec.stage = :stage 
                                           ORDER BY ec.sort_order";
                    $evalComponentsStmt = $db->prepare($evalComponentsQuery);
                    $evalComponentsStmt->bindParam(':evaluation_id', $evaluation['id'], PDO::PARAM_INT);
                    $evalComponentsStmt->bindParam(':stage', $stage);
                    $evalComponentsStmt->execute();
                    $evaluationComponents = $evalComponentsStmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            $autoScoreStages = ['pra-ujian', 'ujian'];
            if (in_array($stage, $autoScoreStages, true)) {
                $previousScores = $this->getPreviousEvaluatorScores($db, $studentId, $selectedEvaluatorId);
                
                // Debug log
                @file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " - Auto-score for student $studentId, stage $stage, evaluator $selectedEvaluatorId: " . json_encode($previousScores) . "\n", FILE_APPEND | LOCK_EX);

                foreach ($components as &$component) {
                    $autoScore = null;
                    if (strpos($component['name'], 'Seminar Proposal') !== false && isset($previousScores['sempro'])) {
                        $autoScore = $previousScores['sempro'];
                        $component['description'] = 'Nilai otomatis dari Seminar Proposal: ' . $previousScores['sempro'];
                    } elseif (strpos($component['name'], 'Seminar Hasil') !== false && isset($previousScores['semhas'])) {
                        $autoScore = $previousScores['semhas'];
                        $component['description'] = 'Nilai otomatis dari Seminar Hasil: ' . $previousScores['semhas'];
                    }

                    if ($autoScore !== null) {
                        $component['readonly'] = true;
                        $component['auto_score'] = $autoScore;
                        
                        // Debug log for each component that gets auto-score
                        @file_put_contents(__DIR__ . '/../../storage/debug_log.txt',
                            date('Y-m-d H:i:s') . " - Controller: Set auto_score for {$component['name']}: {$autoScore}, readonly=true\n",
                            FILE_APPEND | LOCK_EX);
                    }
                }
                unset($component);
                
                // Debug log: check if components have readonly set
                @file_put_contents(__DIR__ . '/../../storage/debug_log.txt',
                    date('Y-m-d H:i:s') . " - Controller: Components after processing, count=" . count($components) . "\n",
                    FILE_APPEND | LOCK_EX);
            }

            // Show evaluation form
            $this->render('evaluations/form', [
                'student' => $student,
                'stage' => $stage,
                'components' => $components,
                'evaluation' => $evaluation,
                'evaluationComponents' => $evaluationComponents,
                'examOutcomeEditable' => $examOutcomeEditable,
                'currentExamOutcome' => $currentExamOutcome,
                'userAssignmentRole' => $userAssignmentRole,
                'availableEvaluations' => $availableEvaluations,
                'selectedEvaluatorId' => $selectedEvaluatorId,
                'canSwitchEvaluator' => $canSwitchEvaluator,
                'roleCanEdit' => $roleCanEdit,
                'activeEvaluator' => $activeEvaluator,
                'success' => $this->getFlash('success'),
                'error' => $this->getFlash('error')
            ]);
        } catch (Exception $e) {
            $this->redirect('/students');
        }
    }

    // Helper function to get current evaluator's scores from previous stages
    private function getPreviousEvaluatorScores($db, $studentId, $evaluatorId)
    {
        $scores = [];

        if ($evaluatorId === null) {
            return $scores;
        }

        // Get evaluator's score from seminar proposal
        $semproQuery = "SELECT e.final_score FROM evaluations e
                       WHERE e.student_id = :student_id AND e.stage = 'sempro' AND e.evaluator_id = :evaluator_id AND e.final_score IS NOT NULL";
        $semproStmt = $db->prepare($semproQuery);
        $semproStmt->bindParam(':student_id', $studentId);
        $semproStmt->bindParam(':evaluator_id', $evaluatorId);
        $semproStmt->execute();

        if ($semproStmt->rowCount() > 0) {
            $semproScore = $semproStmt->fetch(PDO::FETCH_ASSOC);
            $scores['sempro'] = ScoreHelper::normalize($semproScore['final_score'] ?? null);
        }

        // Get evaluator's score from seminar hasil
        $semhasQuery = "SELECT e.final_score FROM evaluations e
                       WHERE e.student_id = :student_id AND e.stage = 'semhas' AND e.evaluator_id = :evaluator_id AND e.final_score IS NOT NULL";
        $semhasStmt = $db->prepare($semhasQuery);
        $semhasStmt->bindParam(':student_id', $studentId);
        $semhasStmt->bindParam(':evaluator_id', $evaluatorId);
        $semhasStmt->execute();

        if ($semhasStmt->rowCount() > 0) {
            $semhasScore = $semhasStmt->fetch(PDO::FETCH_ASSOC);
            $scores['semhas'] = ScoreHelper::normalize($semhasScore['final_score'] ?? null);
        }

        // Debug log
        @file_put_contents(__DIR__ . '/../../storage/debug_log.txt', date('Y-m-d H:i:s') . " - getPreviousEvaluatorScores for student $studentId, evaluator $evaluatorId: " . json_encode($scores) . "\n", FILE_APPEND | LOCK_EX);

        return $scores;
    }

    public function saveEvaluation($studentId, $stage)
    {
        // EMERGENCY DEBUG - Die immediately to prove this code is executed
        $dieMsg = "=== EMERGENCY: saveEvaluation CALLED at " . date('Y-m-d H:i:s') . " ===";
        file_put_contents(__DIR__ . '/../../storage/emergency_log.txt', $dieMsg . "\n", FILE_APPEND | LOCK_EX);
        // die($dieMsg); // Commented out for now - just log it
        
        // SUPER AGGRESSIVE DEBUGGING - Write to multiple locations including die()
        $timestamp = date('Y-m-d H:i:s');
        $debugMsg = "[$timestamp] saveEvaluation CALLED: studentId=$studentId, stage=$stage\n";
        
        // Write to system error log (always works)
        error_log("=== saveEvaluation START: studentId=$studentId, stage=$stage ===");
        
        // Write to debug_log.txt with absolute path
        $debugLogPath = __DIR__ . '/../../storage/debug_log.txt';
        @file_put_contents($debugLogPath, "[$timestamp] saveEvaluation: studentId=$studentId, stage=$stage\n", FILE_APPEND | LOCK_EX);
        
        // Write to email_debug.log with absolute path
        $logFile = __DIR__ . '/../../storage/logs/email_debug.log';
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        @file_put_contents($logFile, $debugMsg, FILE_APPEND | LOCK_EX);
        
        // Write to a separate file that we can check
        $saveLogPath = __DIR__ . '/../../storage/save_evaluation_log.txt';
        @file_put_contents($saveLogPath, "[$timestamp] saveEvaluation CALLED and executed\n", FILE_APPEND | LOCK_EX);
        
        // Require authentication
        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Before requireAuth\n", FILE_APPEND | LOCK_EX);
        $this->requireAuth();
        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - After requireAuth\n", FILE_APPEND | LOCK_EX);

        // Only pembimbing, penguji, kombi and superadmin can save evaluation
        $role = $this->getUserRole();
        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Role: $role\n", FILE_APPEND | LOCK_EX);
        if (
            !RoleHelper::isLecturerRole($role) &&
            $role !== 'superadmin' &&
            $role !== 'kombi'
        ) {
            @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Redirect: Role not allowed\n", FILE_APPEND | LOCK_EX);
            $this->redirect('/dashboard');
            return;
        }
        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Role check passed\n", FILE_APPEND | LOCK_EX);

        // Enforce role-stage mapping (except for kombi and superadmin)
        if (!$this->isStageAllowedForRole($stage, $role)) {
            @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Redirect: Stage not allowed for role\n", FILE_APPEND | LOCK_EX);
            $this->redirect('/dashboard');
            return;
        }
        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Stage allowed for role\n", FILE_APPEND | LOCK_EX);

        // Database connection
        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Before Database connection\n", FILE_APPEND | LOCK_EX);
        $database = new Database();
        $db = $database->getConnection();
        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - After Database connection\n", FILE_APPEND | LOCK_EX);

        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Before EvaluationPolicy check\n", FILE_APPEND | LOCK_EX);
        [$allowed, $lockReason] = EvaluationPolicy::checkStageAccess($db, (int) $studentId, $stage, $role, (int) $_SESSION['user_id']);
        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - After EvaluationPolicy check: allowed=" . ($allowed ? 'true' : 'false') . ", reason=$lockReason\n", FILE_APPEND | LOCK_EX);
        if (!$allowed) {
            @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Early return: Not allowed ({$lockReason})\n", FILE_APPEND | LOCK_EX);
            $this->setFlash('error', $lockReason);
            $this->redirect('/scores/submit');
            return;
        }
        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - After EvaluationPolicy check - PASSED\n", FILE_APPEND | LOCK_EX);

        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Getting targetEvaluatorId\n", FILE_APPEND | LOCK_EX);
        
        $userAssignmentRole = null;
        $targetEvaluatorId = $_SESSION['user_id'] ?? null;
        $targetEvaluatorRole = $role;

        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - targetEvaluatorId=$targetEvaluatorId, role=$role\n", FILE_APPEND | LOCK_EX);
        
        if (($role === 'superadmin' || $role === 'kombi') && isset($_POST['evaluator_id']) && ctype_digit((string) $_POST['evaluator_id'])) {
            $targetEvaluatorId = (int) $_POST['evaluator_id'];
            $targetUser = $this->getUserById($db, $targetEvaluatorId);
            if ($targetUser) {
                $targetEvaluatorRole = RoleHelper::normalizeLecturerRole($targetUser['role']);
            }
            @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Kombi/superadmin selected evaluator: $targetEvaluatorId\n", FILE_APPEND | LOCK_EX);
        } elseif ($role === 'superadmin' || $role === 'kombi') {
            @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Early return: No evaluator selected for kombi/superadmin\n", FILE_APPEND | LOCK_EX);
            $this->setFlash('error', 'Pilih penilai terlebih dahulu sebelum menyimpan data.');
            $this->redirect("/evaluations/form/{$studentId}/{$stage}");
            return;
        } else {
            @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Lecturer branch, getting current user\n", FILE_APPEND | LOCK_EX);
            $currentUser = $this->getUserById($db, $targetEvaluatorId ?? 0);
            if ($currentUser) {
                $targetEvaluatorRole = RoleHelper::normalizeLecturerRole($currentUser['role']);
            }
        }
        
        @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Before try block\n", FILE_APPEND | LOCK_EX);
        try {
            $db->beginTransaction();
            @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Transaction started\n", FILE_APPEND | LOCK_EX);

            // For superadmin and kombi, allow access to any student
            if ($role !== 'superadmin' && $role !== 'kombi') {
                @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Fetching assignment roles\n", FILE_APPEND | LOCK_EX);
                $userAssignmentRoles = AssignmentRoleHelper::fetchRoles($db, (int) $studentId, (int) $_SESSION['user_id']);
                $userAssignmentRole = AssignmentRoleHelper::pickRoleForStage($userAssignmentRoles, $stage);
                @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - userAssignmentRole=$userAssignmentRole\n", FILE_APPEND | LOCK_EX);

                if ($userAssignmentRole === null) {
                    @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Redirect: userAssignmentRole is null\n", FILE_APPEND | LOCK_EX);
                    $this->redirect('/students');
                    return;
                }

                if ($stage === 'pra-ujian' && !AssignmentRoleHelper::hasRolePrefix($userAssignmentRoles, 'pembimbing')) {
                    @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Redirect: pra-ujian requires pembimbing\n", FILE_APPEND | LOCK_EX);
                    $this->setFlash('error', 'Akses ditolak: Tahap pra-ujian hanya untuk dosen pembimbing mahasiswa ini.');
                    $this->redirect('/scores/submit');
                    return;
                }

                $targetEvaluatorRole = AssignmentRoleHelper::inferLecturerRole($userAssignmentRoles, $stage, $targetEvaluatorRole);
                @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - targetEvaluatorRole=$targetEvaluatorRole\n", FILE_APPEND | LOCK_EX);
            }

            if (($role === 'superadmin' || $role === 'kombi') && $targetEvaluatorId) {
                $targetAssignmentRoles = AssignmentRoleHelper::fetchRoles($db, (int) $studentId, (int) $targetEvaluatorId);
                if (!empty($targetAssignmentRoles)) {
                    $targetEvaluatorRole = AssignmentRoleHelper::inferLecturerRole($targetAssignmentRoles, $stage, $targetEvaluatorRole);
                }
            }

            $targetEvaluatorRole = RoleHelper::normalizeLecturerRole($targetEvaluatorRole ?? $role);

            $examOutcomeEditable = false;
            if ($stage === 'ujian') {
                // Check if user is Ketua Penguji (penguji_1) for this student
                // This works even if user's session role is dosen_pembimbing but they're assigned as penguji_1
                if ($userAssignmentRole === 'penguji_1' || $role === 'superadmin' || $role === 'kombi') {
                    $examOutcomeEditable = true;
                } else {
                    $examOutcomeEditable = false;
                }
            }
            $examOutcome = null;
            if ($examOutcomeEditable) {
                $examOutcome = isset($_POST['exam_outcome']) ? strtoupper(trim($_POST['exam_outcome'])) : '';
                if (!in_array($examOutcome, ['LULUS', 'MENGULANG'], true)) {
                    @file_put_contents(__DIR__ . '/../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Early return: Invalid exam outcome\n", FILE_APPEND);
                    $db->rollBack();
                    $this->setFlash('error', 'Status sidang skripsi wajib dipilih (Lulus atau Mengulang).');
                    $this->redirect("/evaluations/form/{$studentId}/{$stage}");
                    return;
                }
            }

            // Check if evaluation already exists
            $checkQuery = "SELECT id FROM evaluations WHERE student_id = :student_id AND evaluator_id = :evaluator_id AND stage = :stage";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':student_id', $studentId);
            $checkStmt->bindParam(':evaluator_id', $targetEvaluatorId, PDO::PARAM_INT);
            $checkStmt->bindParam(':stage', $stage);
            $checkStmt->execute();

            // Get form data
            $mode = $_POST['mode'] ?? 'components'; // components or final
            $finalScoreInput = $_POST['final_score'] ?? null;
            $notes = $_POST['notes'] ?? '';

            $autoScoreStages = ['pra-ujian', 'ujian'];
            $previousScores = [];
            if (in_array($stage, $autoScoreStages, true)) {
                $evaluatorId = $_POST['evaluator_id'] ?? $_SESSION['user_id'] ?? null;
                $previousScores = $this->getPreviousEvaluatorScores($db, $studentId, $evaluatorId);
            }

            // Calculate total score if in components mode
            $totalScore = null; // normalized (0-100)
            $totalScoreRaw = null; // jumlah Σ (nilai × bobot)
            if ($mode === 'components') {
                // Get evaluation components for this stage
                $componentsQuery = "SELECT * FROM evaluation_components WHERE stage = :stage ORDER BY sort_order";
                $componentsStmt = $db->prepare($componentsQuery);
                $componentsStmt->bindParam(':stage', $stage);
                $componentsStmt->execute();
                $components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);

                $totalWeight = 0;
                $runningScore = 0.0;
                foreach ($components as $component) {
                    $isAutoComponent = in_array($stage, $autoScoreStages, true) &&
                        (strpos($component['name'], 'Seminar Proposal') !== false ||
                            strpos($component['name'], 'Seminar Hasil') !== false);

                    if ($isAutoComponent) {
                        $autoScore = null;
                        if (strpos($component['name'], 'Seminar Proposal') !== false) {
                            $autoScore = $previousScores['sempro'] ?? null;
                        } elseif (strpos($component['name'], 'Seminar Hasil') !== false) {
                            $autoScore = $previousScores['semhas'] ?? null;
                        }
                        if ($autoScore !== null) {
                            $runningScore += $autoScore * $component['weight'];
                            $totalWeight += $component['weight'];
                        }
                        continue;
                    }

                    $score = isset($_POST['score_' . $component['id']]) ? floatval($_POST['score_' . $component['id']]) : 0;
                    $runningScore += $score * $component['weight'];
                    $totalWeight += $component['weight'];
                }

                if ($totalWeight <= 0) {
                    $db->rollBack();
                    $this->setFlash('error', 'Total bobot komponen tidak valid.');
                    $this->redirect("/evaluations/form/{$studentId}/{$stage}");
                    return;
                }

                $totalScoreRaw = $runningScore;
                $totalScore = $runningScore / $totalWeight;
            } else if ($mode === 'final') {
                $inputScore = $finalScoreInput !== null ? floatval($finalScoreInput) : null;
                $totalScore = $inputScore;
                $totalScoreRaw = $inputScore;
            }

            if ($totalScore === null || $totalScoreRaw === null) {
                $db->rollBack();
                $this->redirect("/evaluations/form/{$studentId}/{$stage}");
                return;
            }

            if ($checkStmt->rowCount() > 0) {
                // Update existing evaluation
                $evaluation = $checkStmt->fetch(PDO::FETCH_ASSOC);
                $evaluationId = $evaluation['id'];
                
                // Check if evaluation can be edited (edit window + lock status)
                @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Before edit window check\n", FILE_APPEND | LOCK_EX);
                [$canEdit, $lockReason] = EvaluationPolicy::canEditEvaluation($db, (int) $evaluationId, $role);
                @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - After edit window check: canEdit=" . ($canEdit ? 'true' : 'false') . ", reason=$lockReason\n", FILE_APPEND | LOCK_EX);
                
                if (!$canEdit) {
                    $db->rollBack();
                    $this->setFlash('error', $lockReason ?? 'Nilai tidak dapat diedit.');
                    $this->redirect("/evaluations/form/{$studentId}/{$stage}");
                    return;
                }
                
                // Get old values for audit log
                $oldValuesQuery = "SELECT final_score, total_score, notes FROM evaluations WHERE id = :id";
                $oldValuesStmt = $db->prepare($oldValuesQuery);
                $oldValuesStmt->bindParam(':id', $evaluationId);
                $oldValuesStmt->execute();
                $oldValues = $oldValuesStmt->fetch(PDO::FETCH_ASSOC);
                
                // Get edit reason from POST (required for edits)
                $editReason = $_POST['edit_reason'] ?? '';
                if (empty($editReason)) {
                    $db->rollBack();
                    $this->setFlash('error', 'Alasan perubahan nilai wajib diisi.');
                    $this->redirect("/evaluations/form/{$studentId}/{$stage}");
                    return;
                }
                
                $updateQuery = "UPDATE evaluations SET
                                mode = :mode,
                                final_score = :final_score,
                                total_score = :total_score,
                                notes = :notes,
                                updated_at = CURRENT_TIMESTAMP
                                WHERE id = :id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':mode', $mode);
                $updateStmt->bindParam(':final_score', $totalScore);
                $updateStmt->bindParam(':total_score', $totalScoreRaw);
                $updateStmt->bindParam(':notes', $notes);
                $updateStmt->bindParam(':id', $evaluationId);
                $updateStmt->execute();

                // Add audit log entry
                $auditQuery = "INSERT INTO evaluation_edit_history (
                                evaluation_id,
                                edited_by,
                                edited_by_role,
                                old_final_score,
                                new_final_score,
                                old_total_score,
                                new_total_score,
                                edit_reason
                                ) VALUES (
                                :evaluation_id,
                                :edited_by,
                                :edited_by_role,
                                :old_final_score,
                                :new_final_score,
                                :old_total_score,
                                :new_total_score,
                                :edit_reason
                                )";
                $auditStmt = $db->prepare($auditQuery);
                $auditStmt->bindParam(':evaluation_id', $evaluationId);
                $auditStmt->bindParam(':edited_by', $targetEvaluatorId, PDO::PARAM_INT);
                $auditStmt->bindParam(':edited_by_role', $targetEvaluatorRole);
                $auditStmt->bindParam(':old_final_score', $oldValues['final_score']);
                $auditStmt->bindParam(':new_final_score', $totalScore);
                $auditStmt->bindParam(':old_total_score', $oldValues['total_score']);
                $auditStmt->bindParam(':new_total_score', $totalScoreRaw);
                $auditStmt->bindParam(':edit_reason', $editReason);
                $auditStmt->execute();

                // Email notification to student on score edit has been disabled
                // Score changes are lecturer privacy and should not be sent to students
            } else {
                // Insert new evaluation
                $insertQuery = "INSERT INTO evaluations (
                                student_id, evaluator_id, evaluator_role, stage,
                                mode, final_score, total_score, notes, submitted_at
                                ) VALUES (
                                :student_id, :evaluator_id, :evaluator_role, :stage,
                                :mode, :final_score, :total_score, :notes, NOW()
                                )";
                $insertStmt = $db->prepare($insertQuery);
                $insertStmt->bindParam(':student_id', $studentId);
                $insertStmt->bindParam(':evaluator_id', $targetEvaluatorId, PDO::PARAM_INT);
                $insertStmt->bindParam(':evaluator_role', $targetEvaluatorRole);
                $insertStmt->bindParam(':stage', $stage);
                $insertStmt->bindParam(':mode', $mode);
                $insertStmt->bindParam(':final_score', $totalScore);
                $insertStmt->bindParam(':total_score', $totalScoreRaw);
                $insertStmt->bindParam(':notes', $notes);
                $insertStmt->execute();

                $evaluationId = $db->lastInsertId();
            }

            // Save component scores if in components mode
            if ($mode === 'components') {
                // Delete existing component scores
                $deleteScoresQuery = "DELETE FROM evaluation_scores WHERE evaluation_id = :evaluation_id";
                $deleteScoresStmt = $db->prepare($deleteScoresQuery);
                $deleteScoresStmt->bindParam(':evaluation_id', $evaluationId);
                $deleteScoresStmt->execute();

                // Get evaluation components for this stage
                $componentsQuery = "SELECT * FROM evaluation_components WHERE stage = :stage ORDER BY sort_order";
                $componentsStmt = $db->prepare($componentsQuery);
                $componentsStmt->bindParam(':stage', $stage);
                $componentsStmt->execute();
                $components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);

                // Insert new component scores
                foreach ($components as $component) {
                    // Skip automatic components from previous stages
                    if (
                        in_array($stage, $autoScoreStages, true) &&
                        (strpos($component['name'], 'Seminar Proposal') !== false ||
                            strpos($component['name'], 'Seminar Hasil') !== false)
                    ) {
                        continue;
                    }

                    $score = isset($_POST['score_' . $component['id']]) ? floatval($_POST['score_' . $component['id']]) : 0;

                    $insertScoreQuery = "INSERT INTO evaluation_scores (evaluation_id, component_id, score) 
                                        VALUES (:evaluation_id, :component_id, :score)";
                    $insertScoreStmt = $db->prepare($insertScoreQuery);
                    $insertScoreStmt->bindParam(':evaluation_id', $evaluationId);
                    $insertScoreStmt->bindParam(':component_id', $component['id']);
                    $insertScoreStmt->bindParam(':score', $score);
                    $insertScoreStmt->execute();
                }

                if (in_array($stage, $autoScoreStages, true)) {
                    $componentsQuery = "SELECT * FROM evaluation_components WHERE stage = :stage ORDER BY sort_order";
                    $componentsStmt = $db->prepare($componentsQuery);
                    $componentsStmt->bindParam(':stage', $stage);
                    $componentsStmt->execute();
                    $autoComponents = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($autoComponents as $component) {
                        $autoScore = null;
                        if (isset($previousScores['sempro']) && strpos($component['name'], 'Seminar Proposal') !== false) {
                            $autoScore = $previousScores['sempro'];
                        } elseif (isset($previousScores['semhas']) && strpos($component['name'], 'Seminar Hasil') !== false) {
                            $autoScore = $previousScores['semhas'];
                        }

                        if ($autoScore !== null) {
                            $insertScoreQuery = "INSERT INTO evaluation_scores (evaluation_id, component_id, score) 
                                                VALUES (:evaluation_id, :component_id, :score)";
                            $insertScoreStmt = $db->prepare($insertScoreQuery);
                            $insertScoreStmt->bindParam(':evaluation_id', $evaluationId);
                            $insertScoreStmt->bindParam(':component_id', $component['id']);
                            $insertScoreStmt->bindParam(':score', $autoScore);
                            $insertScoreStmt->execute();
                        }
                    }
                }
            }

            // Update student status to 'LULUS' ONLY for 'ujian' stage when ALL evaluators have submitted
            // For sempro, semhas, and pra-ujian, status should NOT be changed to LULUS
            if ($stage === 'ujian') {
                $allSubmitted = $this->haveAllEvaluatorsSubmitted($db, (int)$studentId, $stage);
                
                if ($allSubmitted && $examOutcome !== null && $examOutcome === 'LULUS') {
                    // This is the last evaluator submitting for ujian stage and outcome is LULUS - update student status
                    $statusUpdate = $db->prepare("UPDATE students SET status = 'LULUS' WHERE id = :student_id");
                    $statusUpdate->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                    $statusUpdate->execute();
                }
            }

            @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - About to commit transaction\n", FILE_APPEND | LOCK_EX);
            $db->commit();
            
            $commitTime = date('Y-m-d H:i:s');
            error_log("[$commitTime] DB COMMIT SUCCESSFUL, about to queue email");
            @file_put_contents(__DIR__ . '/../../storage/debug_log.txt', "[$commitTime] DB COMMIT SUCCESSFUL\n", FILE_APPEND | LOCK_EX);
            @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - DB COMMIT SUCCESSFUL\n", FILE_APPEND | LOCK_EX);
            
            // Queue email untuk dosen yang baru saja menginput nilai
            // Email sekarang di-queue dan akan diproses di background
            @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Before email queuing block\n", FILE_APPEND | LOCK_EX);
            $emailQueued = false;
            $emailError = null;
            try {
                @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', date('Y-m-d H:i:s') . " - Inside email try block\n", FILE_APPEND | LOCK_EX);
                $emailDebugTime = date('Y-m-d H:i:s');
                error_log("[$emailDebugTime] EMAIL TRY BLOCK START: targetEvaluatorId=$targetEvaluatorId");
                @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', "[$emailDebugTime] EMAIL TRY BLOCK START: targetEvaluatorId=$targetEvaluatorId\n", FILE_APPEND | LOCK_EX);
                
                // Debug: Log sebelum call
                DebugLog::log("TRY BLOCK START: targetEvaluatorId=$targetEvaluatorId");
                
                EventService::markEventCompletedIfScoresReady($db, (int) $studentId, $stage);
                
                error_log("[$emailDebugTime] AFTER markEventCompleted");
                @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', "[$emailDebugTime] AFTER markEventCompleted\n", FILE_APPEND | LOCK_EX);
                DebugLog::log("AFTER markEventCompleted");
                
                EventService::queueEmailForLecturer($db, (int) $studentId, $stage, (int) $targetEvaluatorId);
                
                error_log("[$emailDebugTime] AFTER queueEmailForLecturer");
                @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', "[$emailDebugTime] AFTER queueEmailForLecturer\n", FILE_APPEND | LOCK_EX);
                DebugLog::log("AFTER queueEmailForLecturer");
                
                $emailQueued = true;
                
                error_log("[$emailDebugTime] emailQueued SET TO TRUE");
                @file_put_contents(__DIR__ . '/../../storage/logs/email_debug.log', "[$emailDebugTime] emailQueued SET TO TRUE\n", FILE_APPEND | LOCK_EX);
                DebugLog::log("emailQueued SET TO TRUE");
                
                // Process email queue directly (more reliable than HTTP trigger)
                try {
                    require_once __DIR__ . '/../services/EmailService.php';
                    $emailService = new EmailService($db);
                    $emailService->processQueue(5); // Process up to 5 pending emails
                    DebugLog::log("Email queue processed successfully");
                } catch (Exception $processEx) {
                    // Log but don't fail the whole transaction
                    $processError = $processEx->getMessage();
                    error_log("Failed to process email queue: $processError");
                    DebugLog::log("Email queue processing failed: $processError");
                }
                
            } catch (Throwable $emailEx) {
                // Log error tapi jangan gagalkan proses simpan nilai
                $emailError = $emailEx->getMessage();
                $emailDebugTime = date('Y-m-d H:i:s');
                error_log("[$emailDebugTime] EMAIL EXCEPTION: $emailError");
                error_log("[$emailDebugTime] EXCEPTION TRACE: " . $emailEx->getTraceAsString());
                @file_put_contents(__DIR__ . '/../storage/debug_log.txt', "[$emailDebugTime] EMAIL EXCEPTION: $emailError\n", FILE_APPEND | LOCK_EX);
                @file_put_contents(__DIR__ . '/../storage/debug_log.txt', "[$emailDebugTime] EXCEPTION FILE: " . $emailEx->getFile() . " LINE: " . $emailEx->getLine() . "\n", FILE_APPEND | LOCK_EX);
                DebugLog::log("EXCEPTION: $emailError");
                // Jangan throw exception, biar proses simpan nilai lanjut
            }

            // Audit log
            $auditDescription = "Stage={$stage}; Mode={$mode}; Score={$totalScore}";
            if ($examOutcome !== null) {
                $auditDescription .= "; Outcome={$examOutcome}";
            }
            AuditLogger::log(
                $_SESSION['user_id'],
                'save_evaluation',
                'evaluations',
                (int) $evaluationId,
                $auditDescription
            );

            // Set flash message
            if ($emailQueued) {
                $this->setFlash('success', 'Penilaian berhasil disimpan. Email notifikasi dikirim.');
            } else {
                $this->setFlash('success', 'Penilaian berhasil disimpan.');
            }
            
            $this->redirect("/evaluations/form/{$studentId}/{$stage}");
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->setFlash('error', 'Terjadi kesalahan: ' . $e->getMessage());
            $this->redirect("/evaluations/form/{$studentId}/{$stage}");
        }
    }

    public function bypassForm($studentId, $stage)
    {
        // Require authentication
        $this->requireAuth();

        if (strtolower($stage) !== 'ujian') {
            $this->redirect('/scores/submit');
            return;
        }

        // Only kombi and superadmin can bypass evaluation
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        // Database connection
        $database = new Database();
        $db = $database->getConnection();

        try {
            // Get student data
            $studentQuery = "SELECT s.* FROM students s WHERE s.id = :student_id";
            $studentStmt = $db->prepare($studentQuery);
            $studentStmt->bindParam(':student_id', $studentId);
            $studentStmt->execute();

            if ($studentStmt->rowCount() == 0) {
                $this->redirect('/students');
                return;
            }

            $student = $studentStmt->fetch(PDO::FETCH_ASSOC);

            // Get existing bypass evaluation if any
            $evaluationQuery = "SELECT * FROM evaluations WHERE student_id = :student_id AND stage = :stage AND mode = 'bypass'";
            $evaluationStmt = $db->prepare($evaluationQuery);
            $evaluationStmt->bindParam(':student_id', $studentId);
            $evaluationStmt->bindParam(':stage', $stage);
            $evaluationStmt->execute();

            $evaluation = null;
            if ($evaluationStmt->rowCount() > 0) {
                $evaluation = $evaluationStmt->fetch(PDO::FETCH_ASSOC);
            }

            // Get list of lecturers for dropdowns
            $lecturersQuery = "SELECT id, name FROM users WHERE role LIKE 'dosen_%' OR role = 'penguji_eksternal' ORDER BY name";
            $lecturersStmt = $db->prepare($lecturersQuery);
            $lecturersStmt->execute();
            $lecturers = $lecturersStmt->fetchAll(PDO::FETCH_ASSOC);

            // Get current assignments
            $assignments = [];
            $assignQuery = "SELECT role, lecturer_id FROM assignments WHERE student_id = :student_id";
            $assignStmt = $db->prepare($assignQuery);
            $assignStmt->bindParam(':student_id', $studentId);
            $assignStmt->execute();
            while ($row = $assignStmt->fetch(PDO::FETCH_ASSOC)) {
                $assignments[$row['role']] = $row['lecturer_id'];
            }

            // Get current title if any
            $titleQuery = "SELECT title FROM titles WHERE student_id = :student_id ORDER BY submitted_at DESC LIMIT 1";
            $titleStmt = $db->prepare($titleQuery);
            $titleStmt->bindParam(':student_id', $studentId);
            $titleStmt->execute();
            $currentTitle = $titleStmt->fetchColumn() ?: '';

            // Show bypass form
            $currentExamOutcome = isset($student['status']) ? strtoupper((string) $student['status']) : '';

            $this->render('evaluations/bypass', [
                'student' => $student,
                'stage' => $stage,
                'evaluation' => $evaluation,
                'lecturers' => $lecturers,
                'assignments' => $assignments,
                'currentTitle' => $currentTitle,
                'currentExamOutcome' => in_array($currentExamOutcome, ['LULUS', 'MENGULANG'], true) ? $currentExamOutcome : '',
                'error' => $this->getFlash('error'),
                'success' => $this->getFlash('success')
            ]);
        } catch (Exception $e) {
            $this->redirect('/students');
            return;
        }
    }

    private function getUserById(PDO $db, int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }
        $stmt = $db->prepare("SELECT id, name, role FROM users WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function saveBypass($studentId, $stage)
    {
        // Require authentication
        $this->requireAuth();

        if (strtolower($stage) !== 'ujian') {
            $this->redirect('/scores/submit');
            return;
        }

        // Only kombi and superadmin can bypass evaluation
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        // Database connection
        $database = new Database();
        $db = $database->getConnection();

        try {
            $db->beginTransaction();
            // Get form data
            $finalScoreInput = $_POST['final_score'] ?? null;
            if ($finalScoreInput === null || $finalScoreInput === '') {
                $this->setFlash('error', 'Nilai bypass wajib diisi.');
                $db->rollBack();
                $this->redirect("/evaluations/bypass/{$studentId}/{$stage}");
                return;
            }
            $finalScore = floatval($finalScoreInput);
            $notes = trim($_POST['notes'] ?? '');
            $examOutcome = isset($_POST['exam_outcome']) ? strtoupper(trim($_POST['exam_outcome'])) : '';

            if ($notes === '') {
                $this->setFlash('error', 'Catatan bypass wajib diisi.');
                $db->rollBack();
                $this->redirect("/evaluations/bypass/{$studentId}/{$stage}");
                return;
            }

            if (!in_array($examOutcome, ['LULUS', 'MENGULANG'], true)) {
                $this->setFlash('error', 'Status sidang skripsi wajib dipilih (Lulus atau Mengulang).');
                $db->rollBack();
                $this->redirect("/evaluations/bypass/{$studentId}/{$stage}");
                return;
            }

            // Save Title
            $title = trim($_POST['thesis_title'] ?? '');
            if ($title !== '') {
                // Check if title exists
                $checkTitle = $db->prepare("SELECT id FROM titles WHERE student_id = :student_id ORDER BY submitted_at DESC LIMIT 1");
                $checkTitle->bindValue(':student_id', $studentId);
                $checkTitle->execute();

                if ($checkTitle->rowCount() > 0) {
                    $updateTitle = $db->prepare("UPDATE titles SET title = :title, updated_at = NOW() WHERE student_id = :student_id ORDER BY submitted_at DESC LIMIT 1");
                    $updateTitle->bindValue(':title', $title);
                    $updateTitle->bindValue(':student_id', $studentId);
                    $updateTitle->execute();
                } else {
                    $insertTitle = $db->prepare("INSERT INTO titles (student_id, title, context, status, submitted_at) VALUES (:student_id, :title, 'Skripsi', 'DITERIMA', NOW())");
                    $insertTitle->bindValue(':student_id', $studentId);
                    $insertTitle->bindValue(':title', $title);
                    $insertTitle->execute();
                }
            }

            // Save Assignments
            $roles = ['pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2', 'penguji_3'];
            foreach ($roles as $roleKey) {
                $lecturerId = isset($_POST[$roleKey]) ? (int) $_POST[$roleKey] : 0;
                if ($lecturerId > 0) {
                    // Skip if lecturer already assigned to another role for this student
                    $conflictCheck = $db->prepare("SELECT role FROM assignments WHERE student_id = :student_id AND lecturer_id = :lecturer_id AND role != :role LIMIT 1");
                    $conflictCheck->bindValue(':student_id', $studentId);
                    $conflictCheck->bindValue(':lecturer_id', $lecturerId);
                    $conflictCheck->bindValue(':role', $roleKey);
                    $conflictCheck->execute();
                    if ($conflictCheck->fetchColumn()) {
                        continue;
                    }

                    // Check if assignment exists
                    $checkAssign = $db->prepare("SELECT id FROM assignments WHERE student_id = :student_id AND role = :role");
                    $checkAssign->bindValue(':student_id', $studentId);
                    $checkAssign->bindValue(':role', $roleKey);
                    $checkAssign->execute();

                    if ($checkAssign->rowCount() > 0) {
                        $updateAssign = $db->prepare("UPDATE assignments SET lecturer_id = :lecturer_id, assigned_by = :by, updated_at = NOW() WHERE student_id = :student_id AND role = :role");
                        $updateAssign->bindValue(':lecturer_id', $lecturerId);
                        $updateAssign->bindValue(':by', $_SESSION['user_id']);
                        $updateAssign->bindValue(':student_id', $studentId);
                        $updateAssign->bindValue(':role', $roleKey);
                        $updateAssign->execute();
                    } else {
                        $insertAssign = $db->prepare("INSERT INTO assignments (student_id, lecturer_id, role, assigned_by, effective_date) VALUES (:student_id, :lecturer_id, :role, :by, NOW())");
                        $insertAssign->bindValue(':student_id', $studentId);
                        $insertAssign->bindValue(':lecturer_id', $lecturerId);
                        $insertAssign->bindValue(':role', $roleKey);
                        $insertAssign->bindValue(':by', $_SESSION['user_id']);
                        $insertAssign->execute();
                    }
                }
            }

            // Check if bypass evaluation already exists
            $checkQuery = "SELECT id FROM evaluations WHERE student_id = :student_id AND stage = :stage AND mode = 'bypass'";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':student_id', $studentId);
            $checkStmt->bindParam(':stage', $stage);
            $checkStmt->execute();

            $evaluationId = null;
            if ($checkStmt->rowCount() > 0) {
                // Update existing bypass evaluation
                $evaluation = $checkStmt->fetch(PDO::FETCH_ASSOC);
                $updateQuery = "UPDATE evaluations SET 
                                final_score = :final_score,
                                total_score = :total_score,
                                reason = NULL,
                                notes = :notes,
                                updated_at = CURRENT_TIMESTAMP
                                WHERE id = :id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':final_score', $finalScore);
                $updateStmt->bindParam(':total_score', $finalScore);
                $updateStmt->bindParam(':notes', $notes);
                $updateStmt->bindParam(':id', $evaluation['id']);
                $updateStmt->execute();
                $evaluationId = $evaluation['id'];
            } else {
                // Insert new bypass evaluation
                $insertQuery = "INSERT INTO evaluations (
                                student_id, evaluator_id, evaluator_role, stage,
                                mode, final_score, total_score, reason, notes, source
                                ) VALUES (
                                :student_id, :evaluator_id, :evaluator_role, :stage,
                                'bypass', :final_score, :total_score, NULL, :notes, 'bypass'
                                )";
                $insertStmt = $db->prepare($insertQuery);
                $insertStmt->bindParam(':student_id', $studentId);
                $insertStmt->bindParam(':evaluator_id', $_SESSION['user_id']);
                $insertStmt->bindParam(':evaluator_role', $role);
                $insertStmt->bindParam(':stage', $stage);
                $insertStmt->bindParam(':final_score', $finalScore);
                $insertStmt->bindParam(':total_score', $finalScore);
                $insertStmt->bindParam(':notes', $notes);
                $insertStmt->execute();
                $evaluationId = $db->lastInsertId();
            }

            // Update student status to 'LULUS' ONLY for 'ujian' stage when ALL evaluators have submitted
            // For sempro, semhas, and pra-ujian, status should NOT be changed to LULUS
            if ($stage === 'ujian') {
                $allSubmitted = $this->haveAllEvaluatorsSubmitted($db, (int)$studentId, $stage);
                
                if ($allSubmitted && $examOutcome !== null && $examOutcome === 'LULUS') {
                    // This is the last evaluator submitting for ujian stage and outcome is LULUS - update student status
                    $statusUpdate = $db->prepare("UPDATE students SET status = 'LULUS' WHERE id = :student_id");
                    $statusUpdate->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                    $statusUpdate->execute();
                }
            }

            $db->commit();
            
            // Process email notifications (now queued instead of sent immediately)
            EventService::markEventCompletedIfScoresReady($db, (int) $studentId, $stage);
            
            // Process email queue directly (more reliable than HTTP trigger)
            try {
                require_once __DIR__ . '/../services/EmailService.php';
                $emailService = new EmailService($db);
                $emailService->processQueue(5); // Process up to 5 pending emails
            } catch (Exception $e) {
                // Log but don't fail the whole transaction
                error_log("Failed to process email queue: " . $e->getMessage());
            }
            
            // Audit log
            AuditLogger::log(
                $_SESSION['user_id'],
                'bypass_evaluation',
                'evaluations',
                (int) $evaluationId,
                "Stage={$stage}; Score={$finalScore}; Outcome={$examOutcome}; Catatan={$notes}"
            );

            $this->setFlash('success', 'Nilai bypass berhasil disimpan.');

            $this->redirect("/evaluations/bypass/{$studentId}/{$stage}");
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->setFlash('error', 'Terjadi kesalahan saat menyimpan bypass.');
            $this->redirect("/evaluations/bypass/{$studentId}/{$stage}");
        }
    }

    /**
     * Lock or unlock an evaluation (only for kombi and superadmin)
     */
    public function toggleLock($evaluationId)
    {
        $this->requireAuth();

        // Only kombi and superadmin can lock/unlock evaluations
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        try {
            // Get current lock status
            $query = "SELECT locked_at, locked_by FROM evaluations WHERE id = :evaluation_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':evaluation_id', $evaluationId, PDO::PARAM_INT);
            $stmt->execute();
            $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$evaluation) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Evaluasi tidak ditemukan.']);
                return;
            }

            $userId = $_SESSION['user_id'] ?? null;
            
            // Toggle lock status
            $isCurrentlyLocked = $evaluation['locked_at'] !== null;
            
            if ($isCurrentlyLocked) {
                // Unlock
                $updateQuery = "UPDATE evaluations SET locked_at = NULL, locked_by = NULL, lock_reason = NULL WHERE id = :evaluation_id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':evaluation_id', $evaluationId, PDO::PARAM_INT);
                $updateStmt->execute();
                
                $action = 'dibuka';
                $isLocked = 0;
            } else {
                // Lock
                $updateQuery = "UPDATE evaluations SET locked_at = NOW(), locked_by = :user_id, lock_reason = 'Manual lock by admin' WHERE id = :evaluation_id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':evaluation_id', $evaluationId, PDO::PARAM_INT);
                $updateStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $updateStmt->execute();
                
                $action = 'dikunci';
                $isLocked = 1;
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => "Nilai berhasil {$action}.",
                'is_locked' => $isLocked
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal mengubah status kunci: ' . $e->getMessage()]);
        }
    }

    /**
     * Determine if the current role is allowed to access a stage.
     */
    private function isStageAllowedForRole($stage, $role)
    {
        // Superadmin and Kombi can access all stages
        if ($role === 'superadmin' || $role === 'kombi') {
            return true;
        }

        $stageRoleMap = [
            'sempro' => ['dosen_pembimbing', 'dosen_penguji', 'dosen'],
            'semhas' => ['dosen_pembimbing', 'dosen_penguji', 'dosen'],
            'pra-ujian' => ['dosen_pembimbing'],
            'ujian' => ['dosen_penguji'],
        ];

        if (isset($stageRoleMap[$stage]) && in_array($role, $stageRoleMap[$stage], true)) {
            return true;
        }

        // Allow fallback access based on assignment role even if active role berbeda
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return false;
        }

        $database = new Database();
        $db = $database->getConnection();

        if ($stage === 'pra-ujian') {
            return $this->userHasAssignmentRole($db, $userId, 'pembimbing');
        }

        if ($stage === 'ujian') {
            return $this->userHasAssignmentRole($db, $userId, 'penguji');
        }

        return false;
    }

    private function getAcceptedTitle(PDO $db, int $studentId): ?string
    {
        $titleQuery = "SELECT title FROM titles WHERE student_id = :student_id AND status = 'DITERIMA' ORDER BY verified_at DESC LIMIT 1";
        $titleStmt = $db->prepare($titleQuery);
        $titleStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $titleStmt->execute();
        $row = $titleStmt->fetch(PDO::FETCH_ASSOC);
        return $row['title'] ?? null;
    }

    private function userHasAssignmentRole(PDO $db, int $lecturerId, string $rolePrefix): bool
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
     * Check if all required evaluators have submitted their scores for a given stage
     *
     * @param PDO $db Database connection
     * @param int $studentId Student ID
     * @param string $stage Evaluation stage (sempro, semhas, pra-ujian, ujian)
     * @return bool True if all required evaluators have submitted
     */
    private function haveAllEvaluatorsSubmitted(PDO $db, int $studentId, string $stage): bool
    {
        // Get required evaluators for this stage
        $requiredRoles = $this->getRequiredEvaluatorRoles($stage);
        
        // Count how many unique evaluators have submitted for this stage
        $query = "SELECT COUNT(DISTINCT evaluator_id)
                  FROM evaluations
                  WHERE student_id = :student_id
                  AND stage = :stage
                  AND evaluator_id IS NOT NULL";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindParam(':stage', $stage);
        $stmt->execute();
        $submittedCount = $stmt->fetchColumn();
        
        // Check if all required evaluators have submitted
        return $submittedCount >= $requiredRoles;
    }
    
    /**
     * Get the number of required evaluators for a given stage
     *
     * @param string $stage Evaluation stage
     * @return int Number of required evaluators
     */
    private function getRequiredEvaluatorRoles(string $stage): int
    {
        // Define required evaluator counts per stage
        $stageRequirements = [
            'sempro' => 2, // 2 pembimbing
            'semhas' => 2, // 2 pembimbing
            'pra-ujian' => 2, // 2 pembimbing
            'ujian' => 3, // 2 pembimbing + 1 penguji ketua (minimum)
        ];
        
        return $stageRequirements[$stage] ?? 2;
    }
}
