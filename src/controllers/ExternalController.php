<?php

class ExternalController extends BaseController {
    
    public function invite() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can invite external examiners
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        $database = new Database();
        $db = $database->getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['examiner_name'] ?? '');
            $nip = trim($_POST['examiner_nip'] ?? '');
            $studentId = (int) ($_POST['student_id'] ?? 0);
            $stage = $_POST['stage'] ?? '';
            $eventDate = $_POST['event_date'] ?? '';
            $eventTime = $_POST['event_time'] ?? '';
            $room = trim($_POST['room'] ?? '');

            if ($name === '' || $nip === '' || $studentId <= 0 || $stage === '' || $eventDate === '' || $eventTime === '' || $room === '') {
                $this->setFlash('error', 'Semua field wajib diisi.');
                $this->redirect('/external/invite');
                return;
            }

            $stageOptions = EventService::getSchedulableStageOptions();
            if (!isset($stageOptions[$stage])) {
                $this->setFlash('error', 'Tahap ujian tidak valid.');
                $this->redirect('/external/invite');
                return;
            }

            $stageStatus = EventService::getStageStatus($db, $studentId, $stage);
            if ($stageStatus['completed']) {
                $this->setFlash('error', 'Tahap ini sudah selesai (semua dosen sudah menilai). Jika ingin menjadwalkan ulang, ubah status jadwal sebelumnya terlebih dahulu.');
                $this->redirect('/external/invite');
                return;
            }

            try {
                $eventInfo = EventService::upsertEvent($db, $studentId, $stage, $eventDate, $eventTime, $room);
                $invite = ExternalInviteService::createExternalToken(
                    $db,
                    $eventInfo['id'],
                    $nip,
                    $name,
                    $eventDate,
                    $eventTime
                );

                AuditLogger::log(
                    $_SESSION['user_id'],
                    'invite_external_examiner',
                    'external_tokens',
                    (int)$db->lastInsertId(),
                    "Undangan penguji eksternal atas nama {$name}"
                );

                $this->setFlash('success', "Token berhasil dibuat: {$invite['token']}");
            } catch (Exception $e) {
                $this->setFlash('error', 'Gagal membuat token: ' . $e->getMessage());
            }

            $this->redirect('/external/invite');
            return;
        }

        $students = $this->getStudents($db);
        $this->render('external/invite', [
            'students' => $students,
            'stageOptions' => EventService::getSchedulableStageOptions(),
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success')
        ]);
    }
    
    public function login() {
        $database = new Database();
        $db = $database->getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nip = trim($_POST['nip'] ?? '');
            $token = trim($_POST['token'] ?? '');
            $nda = isset($_POST['nda']);

            if ($nip === '' || $token === '' || !$nda) {
                $this->setFlash('error', 'Lengkapi NIP, token, dan persetujuan NDA.');
                $this->redirect('/external/login');
                return;
            }

            $query = "SELECT et.*, e.type, e.student_id, e.scheduled_date, e.scheduled_time, e.room,
                              s.name AS student_name, s.nim, t.title, l.name AS examiner_name
                      FROM external_tokens et
                      JOIN events e ON et.event_id = e.id
                      JOIN students s ON e.student_id = s.id
                      LEFT JOIN titles t ON t.student_id = s.id AND t.status = 'DITERIMA'
                      LEFT JOIN lecturers l ON l.nip = et.nip
                      WHERE et.nip = :nip AND et.token = :token
                      ORDER BY et.created_at DESC LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nip', $nip);
            $stmt->bindParam(':token', $token);
            $stmt->execute();
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $this->setFlash('error', 'Token tidak ditemukan atau tidak valid.');
                $this->redirect('/external/login');
                return;
            }

            if (strtotime($record['expires_at']) < time()) {
                $this->setFlash('error', 'Token sudah kedaluwarsa. Hubungi Kombi untuk token baru.');
                $this->redirect('/external/login');
                return;
            }

            $externalUserId = ExternalInviteService::ensureExternalUser(
                $db,
                $record['nip'],
                $record['examiner_name'] ?? ($record['nip'] ?? 'Penguji Eksternal')
            );

            $mapStage = array_flip([
                'sempro' => 'SEMPRO',
                'semhas' => 'SEMHAS',
                'pra-ujian' => 'PRA_UJIAN',
                'ujian' => 'UJIAN_SKRIPSI'
            ]);

            $stage = $mapStage[$record['type']] ?? 'ujian';

            $_SESSION['external_examiner'] = [
                'token_id' => (int)$record['id'],
                'event_id' => (int)$record['event_id'],
                'stage' => $stage,
                'student_id' => (int)$record['student_id'],
                'student_name' => $record['student_name'],
                'student_nim' => $record['nim'],
                'title' => $record['title'],
                'scheduled_at' => $record['scheduled_date'] . ' ' . $record['scheduled_time'],
                'room' => $record['room'],
                'nip' => $record['nip'],
                'examiner_name' => $record['examiner_name'] ?? 'Penguji Eksternal',
                'user_id' => $externalUserId
            ];

            $update = "UPDATE external_tokens SET nda_agreed = 1, used_at = NOW() WHERE id = :id";
            $updateStmt = $db->prepare($update);
            $updateStmt->bindParam(':id', $record['id'], PDO::PARAM_INT);
            $updateStmt->execute();

            $this->redirect('/external/score/submit');
            return;
        }

        $this->render('external/login', [
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success')
        ]);
    }
    
    public function submitScore() {
        if (!isset($_SESSION['external_examiner'])) {
            $this->redirect('/external/login');
            return;
        }

        $context = $_SESSION['external_examiner'];
        $database = new Database();
        $db = $database->getConnection();
        $externalUserId = $context['user_id'] ?? ExternalInviteService::ensureExternalUser(
            $db,
            $context['nip'],
            $context['examiner_name'] ?? ($context['nip'] ?? 'Penguji Eksternal')
        );
        $_SESSION['external_examiner']['user_id'] = $externalUserId;
        $context['user_id'] = $externalUserId;

        $componentsStmt = $db->prepare("SELECT * FROM evaluation_components WHERE stage = :stage ORDER BY sort_order");
        $componentsStmt->bindParam(':stage', $context['stage']);
        $componentsStmt->execute();
        $components = $componentsStmt->fetchAll(PDO::FETCH_ASSOC);

        $evaluationQuery = "SELECT * FROM evaluations 
                             WHERE student_id = :student_id 
                               AND evaluator_id = :evaluator_id 
                               AND stage = :stage
                               AND evaluator_role = 'penguji_eksternal'
                             LIMIT 1";
        $evaluationStmt = $db->prepare($evaluationQuery);
        $evaluationStmt->bindParam(':student_id', $context['student_id'], PDO::PARAM_INT);
        $evaluationStmt->bindParam(':evaluator_id', $externalUserId, PDO::PARAM_INT);
        $evaluationStmt->bindParam(':stage', $context['stage']);
        $evaluationStmt->execute();
        $evaluation = $evaluationStmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($evaluation) {
            $evaluation['final_score'] = ScoreHelper::normalize($evaluation['final_score'] ?? null);
            $evaluation['total_score'] = isset($evaluation['total_score']) ? (float) $evaluation['total_score'] : null;
        }

        $evaluationComponents = [];
        if ($evaluation) {
            $evalComponentsQuery = "SELECT ec.*, es.score 
                                    FROM evaluation_components ec
                                    LEFT JOIN evaluation_scores es 
                                           ON ec.id = es.component_id 
                                          AND es.evaluation_id = :evaluation_id
                                    WHERE ec.stage = :stage
                                    ORDER BY ec.sort_order";
            $evalComponentsStmt = $db->prepare($evalComponentsQuery);
            $evalComponentsStmt->bindParam(':evaluation_id', $evaluation['id'], PDO::PARAM_INT);
            $evalComponentsStmt->bindParam(':stage', $context['stage']);
            $evalComponentsStmt->execute();
            $evaluationComponents = $evalComponentsStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $mode = 'components';
        $existingNotes = $evaluation['notes'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mode = 'components';
            $notes = trim($_POST['notes'] ?? '');
            $finalScoreInput = $_POST['final_score'] ?? null;

            try {
                $db->beginTransaction();

                $checkQuery = "SELECT id FROM evaluations 
                                WHERE student_id = :student_id 
                                  AND evaluator_id = :evaluator_id 
                                  AND stage = :stage
                                  AND evaluator_role = 'penguji_eksternal'
                                LIMIT 1";
                $checkStmt = $db->prepare($checkQuery);
                $checkStmt->bindParam(':student_id', $context['student_id'], PDO::PARAM_INT);
                $checkStmt->bindParam(':evaluator_id', $externalUserId, PDO::PARAM_INT);
                $checkStmt->bindParam(':stage', $context['stage']);
                $checkStmt->execute();

                $totalScore = null;
                $totalScoreRaw = null;
                $totalWeight = 0.0;
                $runningScore = 0.0;
                $componentScores = [];

                if (empty($components)) {
                    throw new RuntimeException('Komponen penilaian belum dikonfigurasi. Hubungi Kombi.');
                }

                foreach ($components as $component) {
                    $field = 'score_' . $component['id'];
                    if (!isset($_POST[$field]) || $_POST[$field] === '') {
                        throw new RuntimeException('Semua nilai komponen wajib diisi.');
                    }
                    $scoreValue = floatval($_POST[$field]);
                    if ($scoreValue < 0 || $scoreValue > 100) {
                        throw new RuntimeException('Nilai komponen harus berada di antara 0 dan 100.');
                    }
                    $componentScores[$component['id']] = $scoreValue;
                    $runningScore += $scoreValue * $component['weight'];
                    $totalWeight += $component['weight'];
                }

                if ($totalWeight <= 0) {
                    throw new RuntimeException('Total bobot komponen tidak valid.');
                }

                $totalScoreRaw = $runningScore;
                $totalScore = $runningScore / $totalWeight;

                $normalizedScore = round($totalScore, 2);
                $rawScoreRounded = round($totalScoreRaw, 2);
                if ($checkStmt->rowCount() > 0) {
                    $existingEvalId = (int) $checkStmt->fetchColumn();
                    $updateQuery = "UPDATE evaluations SET 
                                        mode = :mode,
                                        final_score = :final_score,
                                        total_score = :total_score,
                                        notes = :notes,
                                        source = 'manual',
                                        updated_at = CURRENT_TIMESTAMP
                                    WHERE id = :id";
                    $updateStmt = $db->prepare($updateQuery);
                    $updateStmt->bindParam(':mode', $mode);
                    $updateStmt->bindParam(':final_score', $normalizedScore);
                    $updateStmt->bindParam(':total_score', $rawScoreRounded);
                    $updateStmt->bindParam(':notes', $notes);
                    $updateStmt->bindParam(':id', $existingEvalId, PDO::PARAM_INT);
                    $updateStmt->execute();
                    $evaluationId = $existingEvalId;
                } else {
                    $insertQuery = "INSERT INTO evaluations (
                                        student_id, evaluator_id, evaluator_role, stage,
                                        mode, final_score, total_score, notes, source
                                    ) VALUES (
                                        :student_id, :evaluator_id, 'penguji_eksternal', :stage,
                                        :mode, :final_score, :total_score, :notes, 'manual'
                                    )";
                    $insertStmt = $db->prepare($insertQuery);
                    $insertStmt->bindParam(':student_id', $context['student_id'], PDO::PARAM_INT);
                    $insertStmt->bindParam(':evaluator_id', $externalUserId, PDO::PARAM_INT);
                    $insertStmt->bindParam(':stage', $context['stage']);
                    $insertStmt->bindParam(':mode', $mode);
                    $insertStmt->bindParam(':final_score', $normalizedScore);
                    $insertStmt->bindParam(':total_score', $rawScoreRounded);
                    $insertStmt->bindParam(':notes', $notes);
                    $insertStmt->execute();
                    $evaluationId = (int) $db->lastInsertId();
                }

                $deleteScoresQuery = "DELETE FROM evaluation_scores WHERE evaluation_id = :evaluation_id";
                $deleteScoresStmt = $db->prepare($deleteScoresQuery);
                $deleteScoresStmt->bindParam(':evaluation_id', $evaluationId, PDO::PARAM_INT);
                $deleteScoresStmt->execute();

                foreach ($componentScores as $componentId => $scoreValue) {
                    $insertScoreQuery = "INSERT INTO evaluation_scores (evaluation_id, component_id, score)
                                         VALUES (:evaluation_id, :component_id, :score)";
                    $insertScoreStmt = $db->prepare($insertScoreQuery);
                    $insertScoreStmt->bindParam(':evaluation_id', $evaluationId, PDO::PARAM_INT);
                    $insertScoreStmt->bindParam(':component_id', $componentId, PDO::PARAM_INT);
                    $insertScoreStmt->bindParam(':score', $scoreValue);
                    $insertScoreStmt->execute();
                }

                $db->commit();
                $this->setFlash('success', 'Penilaian berhasil disimpan.');
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $this->setFlash('error', $e->getMessage() ?: 'Terjadi kesalahan saat menyimpan penilaian.');
            }

            $this->redirect('/external/score/submit');
            return;
        }

        $this->render('external/score/submit', [
            'context' => $context,
            'components' => $components,
            'evaluation' => $evaluation,
            'evaluationComponents' => $evaluationComponents,
            'mode' => $mode,
            'existingNotes' => $existingNotes,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error'),
            'pembimbingSummary' => EvaluationService::getPembimbingSummary($context['student_id'], $context['stage'])
        ]);
    }

    private function getStudents(PDO $db): array {
        $activeAngkatan = Settings::getActiveAngkatan();

        $query = "SELECT s.id, s.nim, s.name
                  FROM students s
                  WHERE s.status != 'LULUS'
                    AND EXISTS (
                        SELECT 1 FROM titles t
                        WHERE t.student_id = s.id AND t.status = 'DITERIMA'
                    )
                    AND EXISTS (
                        SELECT 1 FROM assignments a
                        WHERE a.student_id = s.id AND a.role = 'pembimbing_1'
                    )
                    AND EXISTS (
                        SELECT 1 FROM assignments a
                        WHERE a.student_id = s.id AND a.role = 'penguji_1'
                    )";
        $params = [];

        if (!empty($activeAngkatan)) {
            $placeholders = implode(',', array_fill(0, count($activeAngkatan), '?'));
            $query .= " AND s.angkatan IN ($placeholders)";
            $params = $activeAngkatan;
        }

        $query .= " ORDER BY s.name";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function logout() {
        unset($_SESSION['external_examiner']);
        $this->setFlash('success', 'Anda telah logout.');
        $this->redirect('/external/login');
    }
}
