<?php

class HomeController extends BaseController
{

    public function index()
    {
        // Require authentication
        $this->requireAuth();

        $role = $this->getUserRole();
        $database = new Database();
        $db = $database->getConnection();
        $userId = $_SESSION['user_id'];

        switch ($role) {
            case 'superadmin':
            case 'kombi':
                $data = $this->getKombiDashboardData($db);
                $this->render('dashboard/kombi', $data);
                break;
            case 'dosen':
            case 'dosen_pembimbing':
                $data = $this->getLecturerDashboardData($db, $userId, $role);
                $this->render('dashboard/pembimbing', $data);
                break;
            case 'dosen_penguji':
                $data = $this->getLecturerDashboardData($db, $userId, $role);
                $this->render('dashboard/penguji', $data);
                break;
            case 'mahasiswa':
                $data = $this->getStudentDashboardData($db, $userId);
                $this->render('dashboard/mahasiswa', $data);
                break;
            default:
                $this->render('dashboard/default');
        }
    }

    private function getKombiDashboardData(PDO $db): array
    {
        // Get active angkatan
        $activeAngkatan = Settings::getActiveAngkatan();
        $angkatanCondition = '';
        $angkatanParams = [];

        if (!empty($activeAngkatan)) {
            $placeholders = implode(',', array_fill(0, count($activeAngkatan), '?'));
            $angkatanCondition = " AND s.angkatan IN ($placeholders)";
            $angkatanParams = $activeAngkatan;
        }

        $summaryCards = [];

        // Total students query
        $totalStudentsQuery = "SELECT COUNT(*) FROM students s WHERE s.status != 'LULUS'$angkatanCondition";
        $totalStudentsStmt = $db->prepare($totalStudentsQuery);
        $totalStudentsStmt->execute($angkatanParams);
        $totalStudents = (int) $totalStudentsStmt->fetchColumn();

        // No title count query
        $noTitleCountQuery = "SELECT COUNT(*) FROM students s WHERE NOT EXISTS (SELECT 1 FROM titles t WHERE t.student_id = s.id) AND s.status != 'LULUS'$angkatanCondition";
        $noTitleCountStmt = $db->prepare($noTitleCountQuery);
        $noTitleCountStmt->execute($angkatanParams);
        $noTitleCount = (int) $noTitleCountStmt->fetchColumn();

        // Pending title count query
        $pendingTitleCountQuery = "SELECT COUNT(*) FROM titles t JOIN students s ON t.student_id = s.id WHERE t.status = 'MENUNGGU' AND s.status != 'LULUS'$angkatanCondition";
        $pendingTitleCountStmt = $db->prepare($pendingTitleCountQuery);
        $pendingTitleCountStmt->execute($angkatanParams);
        $pendingTitleCount = (int) $pendingTitleCountStmt->fetchColumn();

        // Missing pembimbing query
        $missingPembimbingQuery = "SELECT COUNT(*) FROM students s WHERE NOT EXISTS (SELECT 1 FROM assignments a WHERE a.student_id = s.id AND a.role = 'pembimbing_1') AND s.status != 'LULUS'$angkatanCondition";
        $missingPembimbingStmt = $db->prepare($missingPembimbingQuery);
        $missingPembimbingStmt->execute($angkatanParams);
        $missingPembimbing = (int) $missingPembimbingStmt->fetchColumn();

        // Missing penguji query
        $missingPengujiQuery = "SELECT COUNT(*) FROM students s WHERE NOT EXISTS (SELECT 1 FROM assignments a WHERE a.student_id = s.id AND a.role = 'penguji_1') AND s.status != 'LULUS'$angkatanCondition";
        $missingPengujiStmt = $db->prepare($missingPengujiQuery);
        $missingPengujiStmt->execute($angkatanParams);
        $missingPenguji = (int) $missingPengujiStmt->fetchColumn();

        $summaryCards[] = [
            'label' => 'Total Mahasiswa',
            'value' => $totalStudents,
            'icon' => 'fa-users',
            'variant' => 'primary',
            'iconClass' => 'bg-primary text-white'
        ];
        $summaryCards[] = [
            'label' => 'Belum Ajukan Judul',
            'value' => $noTitleCount,
            'icon' => 'fa-file-circle-xmark',
            'variant' => $noTitleCount > 0 ? 'danger' : 'secondary',
            'iconClass' => $noTitleCount > 0 ? 'bg-danger text-white' : 'bg-secondary text-white'
        ];
        $summaryCards[] = [
            'label' => 'Judul Menunggu Verifikasi',
            'value' => $pendingTitleCount,
            'icon' => 'fa-hourglass-half',
            'variant' => $pendingTitleCount > 0 ? 'warning' : 'secondary',
            'iconClass' => $pendingTitleCount > 0 ? 'bg-warning text-dark' : 'bg-secondary text-white'
        ];
        $summaryCards[] = [
            'label' => 'Belum Ada Pembimbing',
            'value' => $missingPembimbing,
            'icon' => 'fa-user-tie',
            'variant' => $missingPembimbing > 0 ? 'danger' : 'success',
            'iconClass' => $missingPembimbing > 0 ? 'bg-danger text-white' : 'bg-success text-white'
        ];
        $summaryCards[] = [
            'label' => 'Belum Ada Penguji',
            'value' => $missingPenguji,
            'icon' => 'fa-users-viewfinder',
            'variant' => $missingPenguji > 0 ? 'danger' : 'success',
            'iconClass' => $missingPenguji > 0 ? 'bg-danger text-white' : 'bg-success text-white'
        ];

        // Pending titles query
        $pendingTitlesQuery = "SELECT s.nim, s.name, t.title, t.submitted_at FROM titles t JOIN students s ON s.id = t.student_id WHERE t.status = 'MENUNGGU' AND s.status != 'LULUS'$angkatanCondition ORDER BY t.submitted_at ASC LIMIT 6";
        $pendingTitlesStmt = $db->prepare($pendingTitlesQuery);
        $pendingTitlesStmt->execute($angkatanParams);
        $pendingTitles = $pendingTitlesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Upcoming events query
        $upcomingEventsQuery = "SELECT s.nim, s.name, e.type, e.scheduled_date, e.scheduled_time FROM events e JOIN students s ON s.id = e.student_id WHERE e.status = 'MENUNGGU' AND e.scheduled_date >= CURDATE() AND s.status != 'LULUS'$angkatanCondition ORDER BY e.scheduled_date, e.scheduled_time LIMIT 6";
        $upcomingEventsStmt = $db->prepare($upcomingEventsQuery);
        $upcomingEventsStmt->execute($angkatanParams);
        $upcomingEvents = $upcomingEventsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Build per-angkatan statistics
        if (!empty($activeAngkatan)) {
            $angkatanList = $activeAngkatan;
            rsort($angkatanList);
        } else {
            $angkatanStmt = $db->prepare("SELECT DISTINCT angkatan FROM students ORDER BY angkatan DESC");
            $angkatanStmt->execute();
            $angkatanList = $angkatanStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $angkatanStats = [];

        if (!empty($angkatanList)) {
            $angkatanPlaceholders = implode(',', array_fill(0, count($angkatanList), '?'));
            $angkatanParams = array_map('intval', $angkatanList);

            $studentAggQuery = "
                SELECT
                    s.angkatan,
                    COUNT(*) AS total,
                    SUM(CASE WHEN s.status != 'LULUS' AND tt.student_id IS NULL THEN 1 ELSE 0 END) AS no_title,
                    SUM(CASE WHEN s.status = 'LULUS' THEN 1 ELSE 0 END) AS lulus,
                    SUM(CASE WHEN s.status = 'MENGULANG' THEN 1 ELSE 0 END) AS mengulang
                FROM students s
                LEFT JOIN (
                    SELECT DISTINCT student_id
                    FROM titles
                ) tt ON tt.student_id = s.id
                WHERE s.angkatan IN ($angkatanPlaceholders)
                GROUP BY s.angkatan
            ";
            $studentAggStmt = $db->prepare($studentAggQuery);
            $studentAggStmt->execute($angkatanParams);
            $studentAggRows = $studentAggStmt->fetchAll(PDO::FETCH_ASSOC);
            $studentAggMap = [];
            foreach ($studentAggRows as $row) {
                $angkatan = (int) $row['angkatan'];
                $studentAggMap[$angkatan] = [
                    'total' => (int) $row['total'],
                    'no_title' => (int) $row['no_title'],
                    'lulus' => (int) $row['lulus'],
                    'mengulang' => (int) $row['mengulang'],
                ];
            }

            $eventAggQuery = "
                SELECT
                    s.angkatan,
                    SUM(CASE WHEN e.type = 'SEMPRO' THEN 1 ELSE 0 END) AS sempro,
                    SUM(CASE WHEN e.type = 'SEMHAS' THEN 1 ELSE 0 END) AS semhas
                FROM (
                    SELECT DISTINCT student_id, type
                    FROM events
                    WHERE status != 'BATAL'
                      AND type IN ('SEMPRO', 'SEMHAS')
                ) e
                JOIN students s ON s.id = e.student_id
                WHERE s.angkatan IN ($angkatanPlaceholders)
                GROUP BY s.angkatan
            ";
            $eventAggStmt = $db->prepare($eventAggQuery);
            $eventAggStmt->execute($angkatanParams);
            $eventAggRows = $eventAggStmt->fetchAll(PDO::FETCH_ASSOC);
            $eventAggMap = [];
            foreach ($eventAggRows as $row) {
                $angkatan = (int) $row['angkatan'];
                $eventAggMap[$angkatan] = [
                    'sempro' => (int) $row['sempro'],
                    'semhas' => (int) $row['semhas'],
                ];
            }

            foreach ($angkatanList as $angkatan) {
                $angkatanInt = (int) $angkatan;
                $studentStats = $studentAggMap[$angkatanInt] ?? ['total' => 0, 'no_title' => 0, 'lulus' => 0, 'mengulang' => 0];
                $eventStats = $eventAggMap[$angkatanInt] ?? ['sempro' => 0, 'semhas' => 0];

                $angkatanStats[] = [
                    'angkatan' => $angkatanInt,
                    'total' => $studentStats['total'],
                    'no_title' => $studentStats['no_title'],
                    'sempro' => $eventStats['sempro'],
                    'semhas' => $eventStats['semhas'],
                    'lulus' => $studentStats['lulus'],
                    'mengulang' => $studentStats['mengulang'],
                ];
            }
        }

        $shortcuts = [
            ['title' => 'Kelola Mahasiswa', 'icon' => 'fa-user-graduate', 'description' => 'Tambah, ubah, atau impor data mahasiswa', 'url' => '/students', 'variant' => 'primary'],
            ['title' => 'Penetapan Dosen', 'icon' => 'fa-people-arrows', 'description' => 'Atur pembimbing dan penguji secara cepat', 'url' => '/assignments/set', 'variant' => 'success'],
            ['title' => 'Monitoring Tahap', 'icon' => 'fa-route', 'description' => 'Pantau progres seminar & ujian setiap mahasiswa', 'url' => '/reports/tracking', 'variant' => 'info'],
            ['title' => 'Laporan Cepat', 'icon' => 'fa-chart-pie', 'description' => 'Akses laporan masa studi, SLA, dan beban dosen', 'url' => '/reports', 'variant' => 'warning'],
            ['title' => 'Pengaturan Angkatan', 'icon' => 'fa-cog', 'description' => 'Atur angkatan yang sedang aktif', 'url' => '/settings', 'variant' => 'secondary']
        ];

        return [
            'summaryCards' => $summaryCards,
            'pendingTitles' => $pendingTitles,
            'upcomingEvents' => $upcomingEvents,
            'angkatanStats' => $angkatanStats,
            'shortcuts' => $shortcuts
        ];
    }

    private function getLecturerDashboardData(PDO $db, int $userId, string $role): array
    {
        // Get active angkatan
        $activeAngkatan = Settings::getActiveAngkatan();
        $angkatanCondition = '';
        $angkatanParams = [$userId];

        if (!empty($activeAngkatan)) {
            $placeholders = implode(',', array_fill(0, count($activeAngkatan), '?'));
            $angkatanCondition = " AND s.angkatan IN ($placeholders)";
            $angkatanParams = array_merge([$userId], $activeAngkatan);
        }

        $assignmentCountsQuery = "SELECT role, COUNT(*) AS total FROM assignments a JOIN students s ON a.student_id = s.id WHERE a.lecturer_id = ? AND s.status != 'LULUS'$angkatanCondition GROUP BY role";
        $assignmentCountsStmt = $db->prepare($assignmentCountsQuery);
        $assignmentCountsStmt->execute($angkatanParams);
        $assignmentCounts = $assignmentCountsStmt->fetchAll(PDO::FETCH_ASSOC);

        $totalStudentsQuery = "SELECT COUNT(DISTINCT a.student_id) FROM assignments a JOIN students s ON a.student_id = s.id WHERE a.lecturer_id = ? AND s.status != 'LULUS'$angkatanCondition";
        $totalStudentsStmt = $db->prepare($totalStudentsQuery);
        $totalStudentsStmt->execute($angkatanParams);
        $totalStudents = (int) $totalStudentsStmt->fetchColumn();

        $summaryCards = [];
        $summaryCards[] = [
            'label' => 'Mahasiswa Aktif',
            'value' => $totalStudents,
            'icon' => 'fa-user-graduate',
            'variant' => 'primary',
            'iconClass' => 'bg-primary text-white'
        ];

        $roleNames = [
            'pembimbing_1' => 'Pembimbing 1',
            'pembimbing_2' => 'Pembimbing 2',
            'penguji_1' => 'Ketua Penguji',
            'penguji_2' => 'Penguji Anggota 1',
            'penguji_3' => 'Penguji Anggota 2'
        ];
        foreach ($assignmentCounts as $count) {
            $summaryCards[] = [
                'label' => $roleNames[$count['role']] ?? $count['role'],
                'value' => (int) $count['total'],
                'icon' => 'fa-users',
                'variant' => 'secondary',
                'iconClass' => 'bg-secondary text-white'
            ];
        }

        $upcomingEventsQuery = "SELECT s.nim, s.name, e.type, e.scheduled_date, e.scheduled_time FROM events e JOIN assignments a ON a.student_id = e.student_id JOIN students s ON s.id = e.student_id WHERE a.lecturer_id = ? AND e.status = 'MENUNGGU' AND e.scheduled_date >= CURDATE() AND s.status != 'LULUS'$angkatanCondition ORDER BY e.scheduled_date, e.scheduled_time LIMIT 5";
        $upcomingEventsStmt = $db->prepare($upcomingEventsQuery);
        $upcomingEventsStmt->execute($angkatanParams);
        $upcomingEvents = $upcomingEventsStmt->fetchAll(PDO::FETCH_ASSOC);

        $studentsQuery = "SELECT DISTINCT s.id, s.nim, s.name
                           FROM assignments a
                           JOIN students s ON a.student_id = s.id
                           WHERE a.lecturer_id = ? AND s.status != 'LULUS'$angkatanCondition
                           ORDER BY s.name";
        $studentsStmt = $db->prepare($studentsQuery);
        $studentsStmt->execute($angkatanParams);
        $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

        $stageMap = [
            'dosen_pembimbing' => ['sempro', 'semhas', 'pra-ujian'],
            'dosen_penguji' => ['sempro', 'semhas', 'ujian'],
            'dosen' => ['sempro', 'semhas', 'pra-ujian', 'ujian']
        ];
        $stageToEventType = [
            'sempro' => 'SEMPRO',
            'semhas' => 'SEMHAS',
            'pra-ujian' => 'PRA_UJIAN',
            'ujian' => 'UJIAN_SKRIPSI'
        ];

        // Optimized: Fetch all evaluations in single query instead of N+1
        $pendingEvaluations = [];
        if (!empty($stageMap[$role]) && !empty($students)) {
            $stages = $stageMap[$role];
            $studentIds = array_column($students, 'id');
            
            if (!empty($studentIds)) {
                // Build placeholders for IN clause
                $studentPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));
                $stagePlaceholders = implode(',', array_fill(0, count($stages), '?'));
                
                // Single query to get all existing evaluations
                $existingEvalsQuery = "SELECT student_id, stage
                                        FROM evaluations
                                        WHERE student_id IN ($studentPlaceholders)
                                          AND stage IN ($stagePlaceholders)
                                          AND evaluator_id = ?";
                $existingEvalsStmt = $db->prepare($existingEvalsQuery);
                $existingEvalsStmt->execute(array_merge($studentIds, $stages, [$userId]));
                $existingEvals = $existingEvalsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Build lookup map: "student_id:stage" => true
                $evalMap = [];
                foreach ($existingEvals as $eval) {
                    $key = $eval['student_id'] . ':' . $eval['stage'];
                    $evalMap[$key] = true;
                }
                
                // Batch-fetch latest event per (student, type) instead of N+1 queries
                $latestEventMap = [];
                $eventStages = array_filter($stages, static function ($stage) {
                    return $stage !== 'pra-ujian';
                });
                $eventTypes = [];
                foreach ($eventStages as $stage) {
                    $eventTypes[] = $stageToEventType[$stage] ?? strtoupper($stage);
                }
                if (!empty($eventTypes)) {
                    $eventTypePlaceholders = implode(',', array_fill(0, count($eventTypes), '?'));
                    $eventsQuery = "SELECT student_id, type, scheduled_date, scheduled_time
                                    FROM events
                                    WHERE student_id IN ($studentPlaceholders)
                                      AND type IN ($eventTypePlaceholders)
                                    ORDER BY scheduled_date DESC, scheduled_time DESC";
                    $eventsStmt = $db->prepare($eventsQuery);
                    $eventsStmt->execute(array_merge($studentIds, $eventTypes));
                    foreach ($eventsStmt->fetchAll(PDO::FETCH_ASSOC) as $ev) {
                        $key = $ev['student_id'] . ':' . $ev['type'];
                        if (!isset($latestEventMap[$key])) {
                            $latestEventMap[$key] = $ev;
                        }
                    }
                }

                // Batch-fetch pra-ujian prerequisites (pembimbing role + semhas score)
                $pembimbingMap = [];
                $semhasScoreSet = [];
                if (in_array('pra-ujian', $stages, true)) {
                    $assignmentQuery = "SELECT student_id, role FROM assignments
                                        WHERE lecturer_id = ?
                                          AND student_id IN ($studentPlaceholders)";
                    $assignmentStmt = $db->prepare($assignmentQuery);
                    $assignmentStmt->execute(array_merge([$userId], $studentIds));
                    foreach ($assignmentStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        if (strpos((string)$row['role'], 'pembimbing') !== false) {
                            $pembimbingMap[(int)$row['student_id']] = true;
                        }
                    }

                    $semhasQuery = "SELECT DISTINCT student_id FROM evaluations
                                    WHERE student_id IN ($studentPlaceholders)
                                      AND stage = 'semhas'
                                      AND evaluator_role = 'dosen_pembimbing'
                                      AND final_score IS NOT NULL";
                    $semhasStmt = $db->prepare($semhasQuery);
                    $semhasStmt->execute($studentIds);
                    foreach ($semhasStmt->fetchAll(PDO::FETCH_COLUMN) as $sid) {
                        $semhasScoreSet[(int)$sid] = true;
                    }
                }

                $now = new DateTimeImmutable('now');

                // Check pending evaluations using the pre-fetched maps
                foreach ($students as $student) {
                    $sid = (int) $student['id'];
                    foreach ($stages as $stage) {
                        $allowed = true;

                        if ($stage === 'pra-ujian') {
                            $allowed = isset($pembimbingMap[$sid]) && isset($semhasScoreSet[$sid]);
                        } else {
                            $eventType = $stageToEventType[$stage] ?? strtoupper($stage);
                            $event = $latestEventMap[$sid . ':' . $eventType] ?? null;
                            if ($event === null) {
                                $allowed = false;
                            } else {
                                $date = $event['scheduled_date'] ?? null;
                                $time = $event['scheduled_time'] ?? '00:00:00';
                                $dateTimeString = trim($date . ' ' . $time);
                                $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateTimeString) ?:
                                            DateTimeImmutable::createFromFormat('Y-m-d H:i', $dateTimeString) ?:
                                            DateTimeImmutable::createFromFormat('Y-m-d', $date);
                                $allowed = ($dateTime !== false) && ($dateTime <= $now);
                            }
                        }

                        if (!$allowed) {
                            continue;
                        }

                        $key = $sid . ':' . $stage;
                        if (!isset($evalMap[$key])) {
                            $pendingEvaluations[] = [
                                'student_id' => $sid,
                                'nim' => $student['nim'],
                                'name' => $student['name'],
                                'stage' => $stage,
                                'type' => $stageToEventType[$stage] ?? strtoupper($stage)
                            ];
                        }
                    }
                }
            }
        }

        $quickLinks = [
            [
                'title' => 'Input Nilai Tahapan',
                'icon' => 'fa-pen-to-square',
                'description' => 'Isi atau perbarui nilai seminar dan sidang mahasiswa',
                'url' => '/scores/submit',
                'variant' => 'primary',
                'disabled' => empty($students)
            ]
        ];

        return [
            'summaryCards' => $summaryCards,
            'upcomingEvents' => $upcomingEvents,
            'pendingEvaluations' => $pendingEvaluations,
            'quickLinks' => $quickLinks
        ];
    }

    private function getStudentDashboardData(PDO $db, int $userId): array
    {
        $studentQuery = "SELECT * FROM students WHERE user_id = :user_id LIMIT 1";
        $stmt = $db->prepare($studentQuery);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return ['student' => null];
        }

        $studentId = $student['id'];

        $titleStmt = $db->prepare("SELECT * FROM titles WHERE student_id = :student_id ORDER BY submitted_at DESC LIMIT 1");
        $titleStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $titleStmt->execute();
        $title = $titleStmt->fetch(PDO::FETCH_ASSOC);

        $eventStmt = $db->prepare("SELECT * FROM events WHERE student_id = :student_id ORDER BY scheduled_date, scheduled_time");
        $eventStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $eventStmt->execute();
        $events = $eventStmt->fetchAll(PDO::FETCH_ASSOC);

        $evalQuery = "
            SELECT e.stage, e.updated_at, e.final_score
            FROM evaluations e
            JOIN (
                SELECT stage, MAX(updated_at) AS latest_updated
                FROM evaluations
                WHERE student_id = :student_id_latest
                GROUP BY stage
            ) latest ON latest.stage = e.stage AND latest.latest_updated = e.updated_at
            WHERE e.student_id = :student_id
        ";
        $evalStmt = $db->prepare($evalQuery);
        $evalStmt->bindParam(':student_id_latest', $studentId, PDO::PARAM_INT);
        $evalStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $evalStmt->execute();
        $evaluations = $evalStmt->fetchAll(PDO::FETCH_ASSOC);
        $evaluationMap = [];
        foreach ($evaluations as $evaluation) {
            $evaluationMap[$evaluation['stage']] = $evaluation;
        }

        $stageOrder = ['sempro', 'semhas', 'pra-ujian', 'ujian'];
        $stageLabels = [
            'sempro' => 'Seminar Proposal',
            'semhas' => 'Seminar Hasil',
            'pra-ujian' => 'Pra-Ujian Skripsi',
            'ujian' => 'Ujian Skripsi'
        ];

        // Get lecturers for this student
        $lecturersQuery = "SELECT u.name, a.role FROM assignments a JOIN users u ON a.lecturer_id = u.id WHERE a.student_id = :student_id ORDER BY a.role";
        $lecturersStmt = $db->prepare($lecturersQuery);
        $lecturersStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $lecturersStmt->execute();
        $assignments = $lecturersStmt->fetchAll(PDO::FETCH_ASSOC);

        $pembimbingList = [];
        $pengujiList = [];
        foreach ($assignments as $assignment) {
            if (strpos($assignment['role'], 'pembimbing') !== false) {
                $pembimbingList[] = $assignment['name'];
            } elseif (strpos($assignment['role'], 'penguji') !== false) {
                $pengujiList[] = $assignment['name'];
            }
        }

        $stageStatus = [];
        $nextAction = 'Menunggu pengajuan judul';
        $stageCompleted = false;

        foreach ($stageOrder as $stage) {
            // Pra-ujian hanya punya pembimbing, tidak ada penguji
            // Ujian hanya punya penguji, tidak ada pembimbing
            $stagePengujiList = ($stage === 'pra-ujian') ? [] : $pengujiList;
            $stagePembimbingList = ($stage === 'ujian') ? [] : $pembimbingList;
            $event = null;
            foreach ($events as $ev) {
                $mapStage = null;
                switch ($ev['type']) {
                    case 'SEMPRO':
                        $mapStage = 'sempro';
                        break;
                    case 'SEMHAS':
                        $mapStage = 'semhas';
                        break;
                    case 'PRA_UJIAN':
                        $mapStage = 'pra-ujian';
                        break;
                    case 'UJIAN_SKRIPSI':
                        $mapStage = 'ujian';
                        break;
                }
                if ($mapStage === $stage) {
                    $event = $ev;
                    break;
                }
            }

            $evaluation = $evaluationMap[$stage] ?? null;
            $status = [
                'label' => 'Belum Terjadwal',
                'badge' => 'bg-secondary',
                'detail' => 'Belum ada jadwal'
            ];

            $dateString = null;
            if ($event) {
                $dateString = date('d M Y', strtotime($event['scheduled_date'])) . ' ' . substr($event['scheduled_time'], 0, 5);
                if ($event['status'] === 'MENUNGGU') {
                    $status['label'] = 'Terjadwal';
                    $status['badge'] = 'bg-info';
                    $status['detail'] = 'Jadwal: ' . $dateString;
                } elseif ($event['status'] === 'SELESAI') {
                    $status['label'] = 'Selesai';
                    $status['badge'] = 'bg-primary';
                    $status['detail'] = 'Dilaksanakan: ' . $dateString;
                }
            }

            if ($evaluation && $evaluation['final_score'] !== null) {
                $status['label'] = 'Nilai Terisi';
                $status['badge'] = 'bg-success';
                $normalizedFinal = ScoreHelper::normalize($evaluation['final_score']);
                $status['detail'] = 'Nilai: ' . number_format((float) $normalizedFinal, 2);
            } elseif ($event && $event['status'] === 'SELESAI' && !$evaluation) {
                $status['label'] = 'Menunggu Nilai';
                $status['badge'] = 'bg-warning text-dark';
                $status['detail'] = 'Menunggu penilaian dosen';
            }

            if (!$stageCompleted && $status['badge'] !== 'bg-success') {
                $nextAction = $stageLabels[$stage];
                $stageCompleted = true;
            }

            $stageStatus[] = [
                'key' => $stage,
                'label' => $stageLabels[$stage],
                'status' => $status,
                'date' => $dateString,
                'pembimbing' => $stagePembimbingList,
                'penguji' => $stagePengujiList
            ];
        }

        if (!$stageCompleted) {
            $studentStatus = isset($student['status']) ? strtoupper(trim((string)$student['status'])) : '';
            if ($studentStatus === 'LULUS') {
                $nextAction = 'Sudah Lulus';
            } else {
                $nextAction = 'Tahapan selesai';
            }
        }

        $titleStatus = [
            'label' => 'Belum Mengajukan',
            'badge' => 'bg-secondary',
            'detail' => 'Silakan ajukan judul skripsi'
        ];
        if ($title) {
            $titleStatus['detail'] = 'Terakhir diperbarui: ' . date('d M Y', strtotime($title['submitted_at']));
            switch ($title['status']) {
                case 'MENUNGGU':
                    $titleStatus['label'] = 'Menunggu Verifikasi';
                    $titleStatus['badge'] = 'bg-info';
                    break;
                case 'DITERIMA':
                    $titleStatus['label'] = 'Judul Disetujui';
                    $titleStatus['badge'] = 'bg-success';
                    break;
                case 'PERLU_REVISI':
                    $titleStatus['label'] = 'Perlu Revisi';
                    $titleStatus['badge'] = 'bg-warning text-dark';
                    break;
                case 'DITOLAK':
                    $titleStatus['label'] = 'Judul Ditolak';
                    $titleStatus['badge'] = 'bg-danger';
                    break;
            }
        }

        $nextEventStmt = $db->prepare("SELECT type, scheduled_date, scheduled_time FROM events WHERE student_id = :student_id AND status = 'MENUNGGU' ORDER BY scheduled_date, scheduled_time LIMIT 1");
        $nextEventStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $nextEventStmt->execute();
        $nextEvent = $nextEventStmt->fetch(PDO::FETCH_ASSOC);

        return [
            'student' => $student,
            'titleStatus' => $titleStatus,
            'stageStatus' => $stageStatus,
            'nextAction' => $nextAction,
            'nextEvent' => $nextEvent
        ];
    }

    public function debugRoles()
    {
        $database = new Database();
        $db = $database->getConnection();

        echo "<pre>";
        echo "Distinct Roles:\n";
        $stmt = $db->query("SELECT DISTINCT role FROM assignments");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- '" . $row['role'] . "'\n";
        }

        echo "\nUser ID: " . ($_SESSION['user_id'] ?? 'Not logged in') . "\n";

        echo "\nSample Assignments:\n";
        $stmt = $db->query("SELECT * FROM assignments LIMIT 10");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";
    }
}
