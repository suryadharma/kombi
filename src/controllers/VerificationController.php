<?php

class VerificationController extends BaseController
{
    public function finalScore()
    {
        $studentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $token = $_GET['token'] ?? '';

        $status = 'invalid';
        $message = '';
        $data = null;

        if ($studentId <= 0 || $token === '') {
            $message = 'Parameter verifikasi tidak lengkap.';
            $this->render('verification/evaluation', [
                'title' => 'Verifikasi Nilai Akhir',
                'status' => $status,
                'message' => $message,
                'evaluation' => null
            ]);
            return;
        }

        try {
            $database = new Database();
            $db = $database->getConnection();

            $query = "SELECT s.id, s.nim, s.name, s.angkatan, s.status, t.title,
                             (SELECT e.scheduled_date FROM events e 
                              WHERE e.student_id = s.id AND e.type = 'UJIAN_SKRIPSI' AND e.status = 'SELESAI' 
                              ORDER BY e.scheduled_date DESC LIMIT 1) AS ujian_date,
                             (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = s.id AND a.role = 'pembimbing_1' LIMIT 1) as pembimbing_1,
                             (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = s.id AND a.role = 'pembimbing_2' LIMIT 1) as pembimbing_2,
                             (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = s.id AND a.role = 'penguji_1' LIMIT 1) as penguji_1,
                             (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = s.id AND a.role = 'penguji_2' LIMIT 1) as penguji_2,
                             (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = s.id AND a.role = 'penguji_3' LIMIT 1) as penguji_3
                      FROM students s
                      LEFT JOIN titles t ON t.student_id = s.id AND t.status = 'DITERIMA'
                      WHERE s.id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                $message = 'Data mahasiswa tidak ditemukan.';
            } else {
                $isValid = VerificationHelper::verifyEvaluationToken(
                    $studentId,
                    null,
                    $token
                );
                if ($isValid) {
                    $status = 'valid';
                    $message = 'Dokumen ini valid dan sesuai dengan data pada ' . AppSettings::getFullName() . '.';
                    $data = $student;
                } else {
                    $message = 'Token tidak sesuai atau sudah tidak berlaku.';
                }
            }
        } catch (Exception $e) {
            $message = 'Terjadi kesalahan saat memverifikasi: ' . $e->getMessage();
        }

        $this->render('verification/final', [
            'title' => 'Verifikasi Nilai Akhir',
            'status' => $status,
            'message' => $message,
            'evaluation' => $data
        ]);
    }

    public function evaluation()
    {
        $evaluationId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $token = $_GET['token'] ?? '';

        $status = 'invalid';
        $message = '';
        $data = null;

        if ($evaluationId <= 0 || $token === '') {
            $message = 'Parameter verifikasi tidak lengkap.';
            $this->render('verification/evaluation', [
                'title' => 'Verifikasi Nilai',
                'status' => $status,
                'message' => $message,
                'evaluation' => null
            ]);
            return;
        }

        try {
            $database = new Database();
            $db = $database->getConnection();

            $query = "SELECT e.id, e.stage, e.final_score, e.total_score, e.mode, e.updated_at,
                             (
                                SELECT ROUND(SUM(es.score *
                                    CASE WHEN ec.weight > 1 THEN ec.weight / 100 ELSE ec.weight END
                                ), 2)
                                FROM evaluation_scores es
                                JOIN evaluation_components ec ON ec.id = es.component_id
                                WHERE es.evaluation_id = e.id
                             ) AS weighted_total,
                             e.notes, e.evaluator_role, e.evaluator_id,
                             s.nim, s.name AS student_name, s.angkatan,
                             u.name AS evaluator_name, u.nip,
                             a.role AS assignment_role
                      FROM evaluations e
                      JOIN students s ON s.id = e.student_id
                      JOIN users u ON u.id = e.evaluator_id
                      LEFT JOIN assignments a ON a.student_id = e.student_id
                                             AND a.lecturer_id = e.evaluator_id
                      WHERE e.id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $evaluationId]);
            $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$evaluation) {
                $message = 'Data penilaian tidak ditemukan.';
            } else {
                $isValid = VerificationHelper::verifyEvaluationToken(
                    $evaluationId,
                    $evaluation['updated_at'] ?? null,
                    $token
                );
                if ($isValid) {
                    $status = 'valid';
                    $message = 'Dokumen ini valid dan sesuai dengan data pada ' . AppSettings::getFullName() . '.';
                    $evaluation['role_label'] = $this->formatRoleLabel(
                        $evaluation['assignment_role'] ?? null,
                        $evaluation['evaluator_role'] ?? null
                    );
                    $data = $evaluation;
                } else {
                    $message = 'Token tidak sesuai atau sudah tidak berlaku.';
                }
            }
        } catch (Exception $e) {
            $message = 'Terjadi kesalahan saat memverifikasi: ' . $e->getMessage();
        }

        $this->render('verification/evaluation', [
            'title' => 'Verifikasi Nilai',
            'status' => $status,
            'message' => $message,
            'evaluation' => $data
        ]);
    }

    private function formatRoleLabel(?string $assignmentRole, ?string $fallbackRole): string
    {
        $assignmentLabels = [
            'pembimbing_1' => 'Pembimbing 1',
            'pembimbing_2' => 'Pembimbing 2',
            'penguji_1' => 'Penguji Ketua',
            'penguji_2' => 'Penguji Anggota 1',
            'penguji_3' => 'Penguji Anggota 2',
        ];

        if ($assignmentRole && isset($assignmentLabels[$assignmentRole])) {
            return $assignmentLabels[$assignmentRole];
        }

        switch ($fallbackRole) {
            case 'dosen_pembimbing':
                return 'Pembimbing';
            case 'dosen_penguji':
                return 'Penguji';
            case 'kombi':
                return 'Kombi';
            case 'superadmin':
                return 'Superadmin';
            case 'penguji_eksternal':
                return 'Penguji Eksternal';
            default:
                return $fallbackRole ?? '-';
        }
    }
}
