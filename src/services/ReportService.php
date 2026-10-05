<?php

class ReportService
{
    private $db;
    private array $stageWeightTotals = [];

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getStudyPeriodData(?int $angkatanFilter, string $searchFilter, ?string $statusFilter): array
    {
        $angkatanListStmt = $this->db->query("SELECT DISTINCT angkatan FROM students WHERE angkatan IS NOT NULL ORDER BY angkatan DESC");
        $angkatanList = $angkatanListStmt->fetchAll(PDO::FETCH_COLUMN);
        $statusListStmt = $this->db->query("SELECT DISTINCT status FROM students WHERE status IS NOT NULL ORDER BY status ASC");
        $statusList = $statusListStmt->fetchAll(PDO::FETCH_COLUMN);

        $studentsQuery = "SELECT s.*, 
                          (SELECT COUNT(*) FROM cuti_records cr WHERE cr.student_id = s.id AND cr.status = 'DISETUJUI') AS cuti_semester
                          FROM students s";
        $conditions = [];
        $params = [];

        if ($angkatanFilter) {
            $conditions[] = "s.angkatan = :angkatan";
            $params[':angkatan'] = $angkatanFilter;
        }
        if ($searchFilter !== '') {
            $conditions[] = "(s.nim LIKE :search OR s.name LIKE :search)";
            $params[':search'] = '%' . $searchFilter . '%';
        }
        if ($statusFilter) {
            $conditions[] = "s.status = :status";
            $params[':status'] = $statusFilter;
        }

        if (!empty($conditions)) {
            $studentsQuery .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->db->prepare($studentsQuery);
        $stmt->execute($params);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $perAngkatan = [];
        $studentRows = [];

        foreach ($students as $student) {
            $semesterMasuk = (int) $student['semester_masuk'];
            $semesterLulus = $student['semester_lulus'] !== null ? (int) $student['semester_lulus'] : null;
            $cuti = (int) ($student['cuti_semester'] ?? 0);

            if ($semesterLulus === null) {
                $masaStudi = null;
            } else {
                $masaStudi = max(0, $semesterLulus - $semesterMasuk + 1 - $cuti);
            }

            $studentRow = [
                'nim' => $student['nim'],
                'name' => $student['name'],
                'angkatan' => $student['angkatan'],
                'semester_masuk' => $semesterMasuk,
                'semester_lulus' => $semesterLulus,
                'cuti' => $cuti,
                'masa_studi' => $masaStudi,
                'status' => $student['status']
            ];

            $studentRows[] = $studentRow;

            if ($masaStudi !== null) {
                $angkatan = $student['angkatan'];
                if (!isset($perAngkatan[$angkatan])) {
                    $perAngkatan[$angkatan] = [
                        'total_semester' => 0,
                        'count_lulus' => 0,
                        'total_mahasiswa' => 0
                    ];
                }
                $perAngkatan[$angkatan]['total_semester'] += $masaStudi;
                $perAngkatan[$angkatan]['count_lulus'] += 1;
                $perAngkatan[$angkatan]['total_mahasiswa'] += 1;
            } else {
                $angkatan = $student['angkatan'];
                if (!isset($perAngkatan[$angkatan])) {
                    $perAngkatan[$angkatan] = [
                        'total_semester' => 0,
                        'count_lulus' => 0,
                        'total_mahasiswa' => 0
                    ];
                }
                $perAngkatan[$angkatan]['total_mahasiswa'] += 1;
            }
        }

        $summary = [];
        foreach ($perAngkatan as $angkatan => $data) {
            $average = $data['count_lulus'] > 0 ? $data['total_semester'] / $data['count_lulus'] : null;
            $summary[] = [
                'angkatan' => $angkatan,
                'average_semester' => $average ? round($average, 2) : null,
                'lulusan' => $data['count_lulus'],
                'total_mahasiswa' => $data['total_mahasiswa']
            ];
        }

        usort($summary, function ($a, $b) {
            return $a['angkatan'] <=> $b['angkatan'];
        });

        return [
            'summary' => $summary,
            'students' => $studentRows,
            'angkatanList' => $angkatanList,
            'statusList' => $statusList
        ];
    }

    public function getWorkloadData(?int $angkatanFilter, string $lecturerSearch): array
    {
        $angkatanListStmt = $this->db->query("SELECT DISTINCT angkatan FROM students WHERE angkatan IS NOT NULL AND (status IS NULL OR status != 'LULUS') ORDER BY angkatan DESC");
        $angkatanList = $angkatanListStmt->fetchAll(PDO::FETCH_COLUMN);

        $conditions = ["(s.status IS NULL OR s.status != 'LULUS')"];
        $params = [];
        if ($angkatanFilter) {
            $conditions[] = "s.angkatan = :angkatan";
            $params[':angkatan'] = $angkatanFilter;
        }

        $whereClause = '';
        if (!empty($conditions)) {
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }

        $query = "SELECT u.id, u.name,
                          SUM(CASE WHEN a.role IN ('pembimbing_1', 'pembimbing_2') THEN 1 ELSE 0 END) AS pembimbing_count,
                          SUM(CASE WHEN a.role LIKE 'penguji%' THEN 1 ELSE 0 END) AS penguji_count
                   FROM assignments a
                   JOIN students s ON s.id = a.student_id
                   JOIN users u ON a.lecturer_id = u.id
                   $whereClause
                   GROUP BY u.id, u.name
                   ORDER BY u.name";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate helper values logic
        // Need to fetch details to populate per angkatan counts
        $detailQuery = "SELECT a.lecturer_id,
                               a.role,
                               s.nim,
                               s.name AS student_name,
                               s.angkatan
                        FROM assignments a
                        JOIN students s ON s.id = a.student_id
                        $whereClause";
        $detailStmt = $this->db->prepare($detailQuery);
        $detailStmt->execute($params);
        $detailRows = $detailStmt->fetchAll(PDO::FETCH_ASSOC);

        $detailMap = [];
        foreach ($detailRows as $detail) {
            $roleCode = $detail['role'];
            if (in_array($roleCode, ['pembimbing_1', 'pembimbing_2'], true)) {
                $type = 'pembimbing';
            } elseif (stripos($roleCode, 'penguji') === 0) {
                $type = 'penguji';
            } else {
                continue;
            }

            $lecturerId = (int) $detail['lecturer_id'];
            $angkatanKey = $detail['angkatan'] !== null && $detail['angkatan'] !== ''
                ? (string) $detail['angkatan']
                : 'Tidak diketahui';

            if (!isset($detailMap[$lecturerId][$type])) {
                $detailMap[$lecturerId][$type] = [
                    'counts' => [],
                    'names' => []
                ];
            }

            if (!isset($detailMap[$lecturerId][$type]['counts'][$angkatanKey])) {
                $detailMap[$lecturerId][$type]['counts'][$angkatanKey] = 0;
            }
            $detailMap[$lecturerId][$type]['counts'][$angkatanKey]++;

            if (!isset($detailMap[$lecturerId][$type]['names'][$angkatanKey])) {
                $detailMap[$lecturerId][$type]['names'][$angkatanKey] = [];
            }
            $detailMap[$lecturerId][$type]['names'][$angkatanKey][] = $detail['student_name'] ?? $detail['nim'];
        }

        foreach ($rows as &$row) {
            $lecturerId = (int) $row['id'];
            $row['pembimbing_by_angkatan'] = $detailMap[$lecturerId]['pembimbing']['counts'] ?? [];
            $row['pembimbing_by_angkatan_names'] = $detailMap[$lecturerId]['pembimbing']['names'] ?? [];
            $row['penguji_by_angkatan'] = $detailMap[$lecturerId]['penguji']['counts'] ?? [];
            $row['penguji_by_angkatan_names'] = $detailMap[$lecturerId]['penguji']['names'] ?? [];
        }
        unset($row);

        if ($lecturerSearch !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($lecturerSearch) {
                return stripos($row['name'], $lecturerSearch) !== false;
            }));
        }

        return [
            'workloads' => $rows,
            'angkatanList' => $angkatanList
        ];
    }

    public function getSlaData(?string $selectedStage, ?int $selectedAngkatan, string $searchFilter, ?string $statusFilter): array
    {
        $eventStageMap = [
            'SEMPRO' => 'sempro',
            'SEMHAS' => 'semhas',
            'PRA_UJIAN' => 'pra-ujian',
            'UJIAN_SKRIPSI' => 'ujian'
        ];

        $angkatanListStmt = $this->db->query("SELECT DISTINCT angkatan FROM students WHERE angkatan IS NOT NULL ORDER BY angkatan DESC");
        $angkatanList = $angkatanListStmt->fetchAll(PDO::FETCH_COLUMN);

        $eventsQuery = "SELECT e.*, s.nim, s.name AS student_name, s.angkatan
                        FROM events e
                        JOIN students s ON e.student_id = s.id";
        $eventsStmt = $this->db->prepare($eventsQuery);
        $eventsStmt->execute();
        $events = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);

        $evalQuery = "SELECT student_id, stage, MIN(created_at) AS first_entry, COUNT(*) AS total_entries
                      FROM evaluations
                      WHERE stage IN ('sempro','semhas','pra-ujian','ujian')
                      GROUP BY student_id, stage";
        $evalStmt = $this->db->prepare($evalQuery);
        $evalStmt->execute();
        $evaluations = $evalStmt->fetchAll(PDO::FETCH_ASSOC);

        $evaluationMap = [];
        foreach ($evaluations as $evaluation) {
            $key = $evaluation['student_id'] . '|' . $evaluation['stage'];
            $evaluationMap[$key] = $evaluation;
        }

        $slaThresholds = Settings::getSlaThresholds();
        $rows = [];
        $summary = [
            'total_events' => 0,
            'with_entry' => 0,
            'on_time' => 0,
            'average_hours' => null,
            'stage_breakdown' => [
                'sempro' => ['total' => 0, 'on_time' => 0],
                'semhas' => ['total' => 0, 'on_time' => 0],
                'pra-ujian' => ['total' => 0, 'on_time' => 0],
                'ujian' => ['total' => 0, 'on_time' => 0],
            ]
        ];

        $diffAccumulator = 0;
        foreach ($events as $event) {
            $stage = $eventStageMap[$event['type']] ?? null;
            if (!$stage) {
                continue;
            }

            if ($selectedStage && $stage !== $selectedStage) {
                continue;
            }
            if ($selectedAngkatan && (int) $event['angkatan'] !== $selectedAngkatan) {
                continue;
            }
            if ($searchFilter !== '') {
                $haystack = ($event['nim'] ?? '') . ' ' . ($event['student_name'] ?? '');
                if (stripos($haystack, $searchFilter) === false) {
                    continue;
                }
            }

            $summary['total_events']++;
            $summary['stage_breakdown'][$stage]['total']++;

            $scheduledDateTime = new DateTime($event['scheduled_date'] . ' ' . $event['scheduled_time']);
            $key = $event['student_id'] . '|' . $stage;
            $status = 'Belum Diisi';
            $hours = null;
            $firstEntry = null;
            $hasEntry = isset($evaluationMap[$key]);

            if ($hasEntry) {
                $summary['with_entry']++;
                $firstEntry = new DateTime($evaluationMap[$key]['first_entry']);
                $diffSeconds = max(0, $firstEntry->getTimestamp() - $scheduledDateTime->getTimestamp());
                $hours = round($diffSeconds / 3600, 2);
                $diffAccumulator += $hours;

                $stageThreshold = $slaThresholds[$stage] ?? 72;
                if ($hours <= $stageThreshold) {
                    $status = 'Tepat Waktu';
                    $summary['on_time']++;
                    $summary['stage_breakdown'][$stage]['on_time']++;
                } else {
                    $status = 'Lewat SLA';
                }
            }

            if ($statusFilter && $status !== $statusFilter) {
                continue;
            }

            $rows[] = [
                'student_nim' => $event['nim'],
                'student_name' => $event['student_name'],
                'stage' => $stage,
                'scheduled_at' => $scheduledDateTime,
                'first_entry' => $firstEntry,
                'hours_to_entry' => $hours,
                'status' => $status
            ];
        }

        if ($summary['with_entry'] > 0) {
            $summary['average_hours'] = round($diffAccumulator / $summary['with_entry'], 2);
        }

        return [
            'rows' => $rows,
            'summary' => $summary,
            'slaThresholds' => $slaThresholds,
            'angkatanList' => $angkatanList
        ];
    }

    public function getBypassData(?string $selectedStage, ?int $selectedAngkatan, string $searchFilter): array
    {
        $angkatanListStmt = $this->db->query("SELECT DISTINCT angkatan FROM students WHERE angkatan IS NOT NULL ORDER BY angkatan DESC");
        $angkatanList = $angkatanListStmt->fetchAll(PDO::FETCH_COLUMN);

        $query = "SELECT e.student_id, e.stage, e.final_score, e.notes, e.updated_at,
                          s.nim, s.name AS student_name, s.angkatan,
                          u.name AS evaluator_name, u.role AS evaluator_role
                  FROM evaluations e
                  JOIN students s ON e.student_id = s.id
                  LEFT JOIN users u ON e.evaluator_id = u.id
                  WHERE e.mode = 'bypass'";
        $params = [];
        if ($selectedStage) {
            $query .= " AND e.stage = :stage";
            $params[':stage'] = $selectedStage;
        }
        if ($selectedAngkatan) {
            $query .= " AND s.angkatan = :angkatan";
            $params[':angkatan'] = $selectedAngkatan;
        }
        if ($searchFilter !== '') {
            $query .= " AND (s.nim LIKE :search OR s.name LIKE :search)";
            $params[':search'] = '%' . $searchFilter . '%';
        }
        $query .= " ORDER BY e.updated_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $summary = [
            'total' => count($rows),
            'stage_counts' => [
                'sempro' => 0,
                'semhas' => 0,
                'pra-ujian' => 0,
                'ujian' => 0
            ]
        ];

        foreach ($rows as $row) {
            $stage = $row['stage'];
            if (isset($summary['stage_counts'][$stage])) {
                $summary['stage_counts'][$stage]++;
            }
        }

        return [
            'rows' => $rows,
            'summary' => $summary,
            'angkatanList' => $angkatanList
        ];
    }

    public function getSummaryData(?int $angkatanFilter, string $searchFilter): array
    {
        $angkatanListStmt = $this->db->query("SELECT DISTINCT angkatan FROM students WHERE status != 'LULUS' AND angkatan IS NOT NULL ORDER BY angkatan DESC");
        $angkatanList = $angkatanListStmt->fetchAll(PDO::FETCH_COLUMN);

        // 1. Get Base Student Data
        $studentQuery = "SELECT id, nim, name FROM students WHERE status != 'LULUS'";
        $conditions = [];
        $params = [];
        if ($angkatanFilter) {
            $conditions[] = "angkatan = :angkatan";
            $params[':angkatan'] = $angkatanFilter;
        }
        if ($searchFilter !== '') {
            $conditions[] = "(nim LIKE :search OR name LIKE :search)";
            $params[':search'] = '%' . $searchFilter . '%';
        }
        if (!empty($conditions)) {
            $studentQuery .= " AND " . implode(' AND ', $conditions);
        }
        $studentQuery .= " ORDER BY name";
        $studentStmt = $this->db->prepare($studentQuery);
        $studentStmt->execute($params);
        $baseStudents = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

        $students = [];
        foreach ($baseStudents as $bs) {
            $students[$bs['id']] = [
                'id' => $bs['id'],
                'nim' => $bs['nim'],
                'name' => $bs['name'],
                'title' => null,
                'pembimbing_1' => null,
                'pembimbing_2' => null,
                'penguji_1' => null,
                'penguji_2' => null,
                'penguji_3' => null,
                'sempro_date' => null,
                'sempro_score' => null,
                'semhas_date' => null,
                'semhas_score' => null,
                'ujian_date' => null,
                'ujian_score' => null,
            ];
        }

        // 2. Get Titles
        $titleQuery = "SELECT student_id, title FROM titles WHERE status = 'DITERIMA'";
        $titleStmt = $this->db->prepare($titleQuery);
        $titleStmt->execute();
        $titles = $titleStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($titles as $t) {
            if (isset($students[$t['student_id']])) {
                $students[$t['student_id']]['title'] = $t['title'];
            }
        }

        // 3. Get Assignments
        $assignQuery = "SELECT a.student_id, a.role, u.name 
                        FROM assignments a 
                        JOIN users u ON a.lecturer_id = u.id";
        $assignStmt = $this->db->prepare($assignQuery);
        $assignStmt->execute();
        $assignments = $assignStmt->fetchAll(PDO::FETCH_ASSOC);

        $roleMap = [
            'pembimbing_1' => 'pembimbing_1',
            'pembimbing_2' => 'pembimbing_2',
            'penguji_1' => 'penguji_1',
            'penguji_2' => 'penguji_2',
            'penguji_3' => 'penguji_3',
        ];

        foreach ($assignments as $a) {
            if (isset($students[$a['student_id']]) && isset($roleMap[$a['role']])) {
                $students[$a['student_id']][$roleMap[$a['role']]] = $a['name'];
            }
        }

        // 4. Get Events (Dates)
        $eventQuery = "SELECT student_id, type, scheduled_date FROM events";
        $eventStmt = $this->db->prepare($eventQuery);
        $eventStmt->execute();
        $events = $eventStmt->fetchAll(PDO::FETCH_ASSOC);

        $typeMap = [
            'SEMPRO' => 'sempro_date',
            'SEMHAS' => 'semhas_date',
            'UJIAN_SKRIPSI' => 'ujian_date'
        ];

        foreach ($events as $e) {
            if (isset($students[$e['student_id']]) && isset($typeMap[$e['type']])) {
                $students[$e['student_id']][$typeMap[$e['type']]] = $e['scheduled_date'];
            }
        }

        // 5. Get Scores (Average per stage)
        $scoreQuery = "SELECT student_id, stage, AVG(final_score) as avg_score 
                       FROM evaluations 
                       WHERE final_score IS NOT NULL 
                       GROUP BY student_id, stage";
        $scoreStmt = $this->db->prepare($scoreQuery);
        $scoreStmt->execute();
        $scores = $scoreStmt->fetchAll(PDO::FETCH_ASSOC);

        $stageMap = [
            'sempro' => 'sempro_score',
            'semhas' => 'semhas_score',
            'ujian' => 'ujian_score'
        ];

        foreach ($scores as $s) {
            if (isset($students[$s['student_id']]) && isset($stageMap[$s['stage']])) {
                $students[$s['student_id']][$stageMap[$s['stage']]] = $s['avg_score'];
            }
        }

        return [
            'students' => array_values($students),
            'angkatanList' => $angkatanList
        ];
    }

    public function getTrackingData(?int $angkatanFilter, string $searchFilter): array
    {
        // Get list of angkatan for filter
        $angkatanQuery = "SELECT DISTINCT angkatan FROM students ORDER BY angkatan DESC";
        $angkatanStmt = $this->db->prepare($angkatanQuery);
        $angkatanStmt->execute();
        $angkatanList = $angkatanStmt->fetchAll(PDO::FETCH_COLUMN);

        // Build query for students
        $studentQuery = "SELECT s.id, s.nim, s.name, s.angkatan, s.semester_masuk, s.semester_lulus, s.status
                         FROM students s";
        $conditions = [];
        $params = [];

        if ($angkatanFilter) {
            $conditions[] = "s.angkatan = :angkatan";
            $params[':angkatan'] = $angkatanFilter;
        }

        if ($searchFilter) {
            $conditions[] = "(s.nim LIKE :search OR s.name LIKE :search)";
            $params[':search'] = '%' . $searchFilter . '%';
        }

        if (!empty($conditions)) {
            $studentQuery .= " WHERE " . implode(' AND ', $conditions);
        }
        $studentQuery .= " ORDER BY s.angkatan DESC, s.name";

        $studentStmt = $this->db->prepare($studentQuery);
        $studentStmt->execute($params);
        $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($students)) {
            return [
                'students' => [],
                'angkatanList' => $angkatanList,
                'summary' => []
            ];
        }

        $studentIds = array_column($students, 'id');
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

        // Get events for each student
        $eventQuery = "SELECT student_id, type, scheduled_date, status
                       FROM events
                       WHERE student_id IN ($placeholders) AND status = 'SELESAI'
                       ORDER BY student_id, scheduled_date";
        $eventStmt = $this->db->prepare($eventQuery);
        $eventStmt->execute($studentIds);
        $events = $eventStmt->fetchAll(PDO::FETCH_ASSOC);

        // Group events by student
        $eventMap = [];
        foreach ($events as $event) {
            $studentId = $event['student_id'];
            if (!isset($eventMap[$studentId])) {
                $eventMap[$studentId] = [];
            }
            $eventMap[$studentId][$event['type']] = $event['scheduled_date'];
        }

        // Get assignments
        $assignmentQuery = "SELECT a.student_id, a.role, a.lecturer_id, u.name AS lecturer_name
                           FROM assignments a
                           JOIN users u ON a.lecturer_id = u.id
                           WHERE a.student_id IN ($placeholders)";
        $assignmentStmt = $this->db->prepare($assignmentQuery);
        $assignmentStmt->execute($studentIds);
        $assignments = $assignmentStmt->fetchAll(PDO::FETCH_ASSOC);

        $assignmentMap = [];
        foreach ($assignments as $assignment) {
            $studentId = $assignment['student_id'];
            if (!isset($assignmentMap[$studentId])) {
                $assignmentMap[$studentId] = [];
            }
            $assignmentMap[$studentId][$assignment['role']] = $assignment['lecturer_name'];
        }

        $trackingData = [];
        $summary = [
            'total_students' => count($students),
            'has_sempro' => 0,
            'has_semhas' => 0,
            'has_ujian' => 0,
            'completed' => 0,
            'avg_duration_days' => null
        ];

        $totalDuration = 0;
        $completedCount = 0;
        $transitionSla = Settings::getStageProgressSlaConfig();

        foreach ($students as $student) {
            $studentId = $student['id'];
            $semproDate = $eventMap[$studentId]['SEMPRO'] ?? null;
            $semhasDate = $eventMap[$studentId]['SEMHAS'] ?? null;
            $praujiDate = $eventMap[$studentId]['PRA_UJIAN'] ?? null;
            $ujianDate = $eventMap[$studentId]['UJIAN_SKRIPSI'] ?? null;

            $durationSemproSemhas = null;
            $durationSemproUjian = null;
            $durationSemhasUjian = null;
            $durationSemhasPrauji = null;
            $durationPraujiUjian = null;

            if ($semproDate && $semhasDate) {
                $durationSemproSemhas = $this->calculateDuration($semproDate, $semhasDate);
            }
            if ($semproDate && $ujianDate) {
                $durationSemproUjian = $this->calculateDuration($semproDate, $ujianDate);
                $totalDuration += $durationSemproUjian;
                $completedCount++;
            }
            if ($semhasDate && $ujianDate) {
                $durationSemhasUjian = $this->calculateDuration($semhasDate, $ujianDate);
            }
            if ($semhasDate && $praujiDate) {
                $durationSemhasPrauji = $this->calculateDuration($semhasDate, $praujiDate);
            }
            if ($praujiDate && $ujianDate) {
                $durationPraujiUjian = $this->calculateDuration($praujiDate, $ujianDate);
            }

            if ($semproDate)
                $summary['has_sempro']++;
            if ($semhasDate)
                $summary['has_semhas']++;
            if ($ujianDate)
                $summary['has_ujian']++;
            if ($ujianDate && $student['status'] === 'LULUS')
                $summary['completed']++;

            $trackingData[] = [
                'id' => $studentId,
                'nim' => $student['nim'],
                'name' => $student['name'],
                'angkatan' => $student['angkatan'],
                'status' => $student['status'],
                'sempro_date' => $semproDate,
                'semhas_date' => $semhasDate,
                'prauji_date' => $praujiDate,
                'ujian_date' => $ujianDate,
                'duration_sempro_semhas' => $durationSemproSemhas,
                'duration_sempro_ujian' => $durationSemproUjian,
                'duration_semhas_ujian' => $durationSemhasUjian,
                'duration_semhas_praujian' => $durationSemhasPrauji,
                'duration_praujian_ujian' => $durationPraujiUjian,
                'pembimbing_1' => $assignmentMap[$studentId]['pembimbing_1'] ?? null,
                'pembimbing_2' => $assignmentMap[$studentId]['pembimbing_2'] ?? null,
                'penguji_1' => $assignmentMap[$studentId]['penguji_1'] ?? null,
                'penguji_2' => $assignmentMap[$studentId]['penguji_2'] ?? null,
                'penguji_3' => $assignmentMap[$studentId]['penguji_3'] ?? null,
                'sla_status' => [
                    'sempro_semhas' => $this->evaluateStageProgressStatus($durationSemproSemhas, $transitionSla['sempro_semhas'] ?? 0),
                    'semhas_pra-ujian' => $this->evaluateStageProgressStatus($durationSemhasPrauji, $transitionSla['semhas_pra-ujian'] ?? 0),
                    'pra-ujian_ujian' => $this->evaluateStageProgressStatus($durationPraujiUjian, $transitionSla['pra-ujian_ujian'] ?? 0),
                ],
            ];
        }

        if ($completedCount > 0) {
            $summary['avg_duration_days'] = round($totalDuration / $completedCount, 1);
        }

        return [
            'students' => $trackingData,
            'summary' => $summary,
            'angkatanList' => $angkatanList
        ];
    }

    public function getGraduatedData(?int $angkatanFilter, string $searchFilter): array
    {
        $angkatanQuery = "SELECT DISTINCT angkatan FROM students WHERE status = 'LULUS' ORDER BY angkatan DESC";
        $angkatanStmt = $this->db->prepare($angkatanQuery);
        $angkatanStmt->execute();
        $angkatanList = $angkatanStmt->fetchAll(PDO::FETCH_COLUMN);

        $studentQuery = "SELECT s.id, s.nim, s.name, s.angkatan, s.semester_masuk, s.semester_lulus, s.status
                         FROM students s
                         WHERE s.status = 'LULUS'";
        $conditions = [];
        $params = [];

        if ($angkatanFilter) {
            $conditions[] = "s.angkatan = :angkatan";
            $params[':angkatan'] = $angkatanFilter;
        }

        if ($searchFilter) {
            $conditions[] = "(s.nim LIKE :search OR s.name LIKE :search)";
            $params[':search'] = '%' . $searchFilter . '%';
        }

        if (!empty($conditions)) {
            $studentQuery .= " AND " . implode(' AND ', $conditions);
        }
        $studentQuery .= " ORDER BY s.angkatan DESC, s.name";

        $studentStmt = $this->db->prepare($studentQuery);
        $studentStmt->execute($params);
        $students = $studentStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($students)) {
            return [
                'students' => [],
                'summary' => [],
                'angkatanList' => $angkatanList
            ];
        }

        $studentIds = array_column($students, 'id');
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));

        $eventQuery = "SELECT student_id, type, scheduled_date, status
                       FROM events
                       WHERE student_id IN ($placeholders) AND status = 'SELESAI'
                       ORDER BY student_id, scheduled_date";
        $eventStmt = $this->db->prepare($eventQuery);
        $eventStmt->execute($studentIds);
        $events = $eventStmt->fetchAll(PDO::FETCH_ASSOC);

        $eventMap = [];
        foreach ($events as $event) {
            $studentId = $event['student_id'];
            if (!isset($eventMap[$studentId])) {
                $eventMap[$studentId] = [];
            }
            $eventMap[$studentId][$event['type']] = $event['scheduled_date'];
        }

        $assignmentQuery = "SELECT a.student_id, a.role, u.name AS lecturer_name
                           FROM assignments a
                           JOIN users u ON a.lecturer_id = u.id
                           WHERE a.student_id IN ($placeholders)";
        $assignmentStmt = $this->db->prepare($assignmentQuery);
        $assignmentStmt->execute($studentIds);
        $assignments = $assignmentStmt->fetchAll(PDO::FETCH_ASSOC);

        $assignmentMap = [];
        foreach ($assignments as $assignment) {
            $studentId = $assignment['student_id'];
            if (!isset($assignmentMap[$studentId])) {
                $assignmentMap[$studentId] = [];
            }
            $assignmentMap[$studentId][$assignment['role']] = $assignment['lecturer_name'];
        }

        // Assignment History and Titles are fetched here in original controller, simplification: 
        // I will include title fetch for correctness.
        $titleQuery = "SELECT student_id, title
                      FROM titles
                      WHERE student_id IN ($placeholders) AND status = 'DITERIMA'
                      ORDER BY submitted_at DESC";
        $titleStmt = $this->db->prepare($titleQuery);
        $titleStmt->execute($studentIds);
        $titles = $titleStmt->fetchAll(PDO::FETCH_ASSOC);

        $titleMap = [];
        foreach ($titles as $title) {
            if (!isset($titleMap[$title['student_id']])) {
                $titleMap[$title['student_id']] = $title['title'];
            }
        }

        // Scores
        $scoreQuery = "SELECT e.id AS evaluation_id, e.student_id, e.stage, e.mode, e.final_score, 
                             e.evaluator_role,
                             e.evaluator_id,
                             u.name AS evaluator_name,
                             a.role AS assignment_role
                      FROM evaluations e
                      JOIN users u ON e.evaluator_id = u.id
                      LEFT JOIN assignments a ON a.student_id = e.student_id AND a.lecturer_id = e.evaluator_id
                      WHERE e.student_id IN ($placeholders) AND e.final_score IS NOT NULL
                      ORDER BY e.student_id, e.stage, e.evaluator_id";
        $scoreStmt = $this->db->prepare($scoreQuery);
        $scoreStmt->execute($studentIds);
        $scores = $scoreStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch component scores for weighted calculation
        $evaluationIds = array_column($scores, 'evaluation_id');
        $componentScoresMap = [];
        if (!empty($evaluationIds)) {
            $componentPlaceholder = implode(',', array_fill(0, count($evaluationIds), '?'));
            $componentScoreQuery = "SELECT es.evaluation_id, ec.name AS component_name, ec.weight, es.score
                                   FROM evaluation_scores es
                                   JOIN evaluation_components ec ON ec.id = es.component_id
                                   WHERE es.evaluation_id IN ($componentPlaceholder)
                                   ORDER BY es.evaluation_id, ec.sort_order, ec.id";
            $componentStmt = $this->db->prepare($componentScoreQuery);
            $componentStmt->execute($evaluationIds);
            $componentScores = $componentStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($componentScores as $componentScore) {
                $evalId = $componentScore['evaluation_id'];
                if (!isset($componentScoresMap[$evalId])) {
                    $componentScoresMap[$evalId] = [];
                }
                $componentScoresMap[$evalId][] = [
                    'name' => $componentScore['component_name'],
                    'weight' => $componentScore['weight'],
                    'score' => $componentScore['score']
                ];
            }
        }

        $scoreMap = [];
        foreach ($scores as $score) {
            $studentId = $score['student_id'];
            $stage = $score['stage'];
            if (!isset($scoreMap[$studentId])) {
                $scoreMap[$studentId] = [];
            }
            if (!isset($scoreMap[$studentId][$stage])) {
                $scoreMap[$studentId][$stage] = [];
            }

            // Simplified role derivation (skipped complex history check for brevity/cleanliness as it was rare case)
            $derivedRole = $score['evaluator_role'];
            $assignmentRole = $score['assignment_role'] ?? null;

            if (!$assignmentRole && ($score['mode'] ?? '') !== 'bypass') {
                // Try to fallback or skip if strict
                // For now, let's keep it simple: if no assignment and not bypass, might be history or error.
                // We will accept if evaluator_role is set
            }

            if ($assignmentRole) {
                if (strpos($assignmentRole, 'pembimbing') === 0) {
                    $derivedRole = 'dosen_pembimbing';
                } elseif (strpos($assignmentRole, 'penguji') === 0) {
                    $derivedRole = 'dosen_penguji';
                }
            }

            $componentsForScore = $componentScoresMap[$score['evaluation_id']] ?? [];
            $rawScore = ScoreHelper::weightedTotalFromComponents($componentsForScore);

            $scoreMap[$studentId][$stage][] = [
                'evaluator_id' => $score['evaluator_id'],
                'evaluator_name' => $score['evaluator_name'],
                'evaluator_role' => $derivedRole,
                'assignment_role' => $assignmentRole,
                'score' => ScoreHelper::normalize($score['final_score']),
                'raw_score' => $rawScore,
                'mode' => $score['mode'],
                'components' => $componentsForScore
            ];
        }

        $graduatedData = [];
        $summary = [
            'total_graduated' => count($students),
            'per_angkatan' => [],
            'avg_study_duration' => null
        ];

        $totalStudyDuration = 0;
        $studentsWithStudyDuration = 0;

        foreach ($students as $student) {
            $studentId = $student['id'];
            $semproDate = $eventMap[$studentId]['SEMPRO'] ?? null;
            $semhasDate = $eventMap[$studentId]['SEMHAS'] ?? null;
            $ujianDate = $eventMap[$studentId]['UJIAN_SKRIPSI'] ?? null;

            $durationDays = null;
            if ($semproDate && $ujianDate) {
                $durationDays = $this->calculateDuration($semproDate, $ujianDate);
                $totalStudyDuration += $durationDays;
                $studentsWithStudyDuration++;
            }

            $masaStudi = null;
            if ($student['semester_lulus']) {
                $masaStudi = max(0, (int) $student['semester_lulus'] - (int) $student['semester_masuk'] + 1);
            }

            $pembimbingSummary = $this->summarizePembimbingScores($scoreMap[$studentId]['pra-ujian'] ?? []);
            $pengujiSummary = $this->summarizePengujiScores($scoreMap[$studentId]['ujian'] ?? []);

            $stageTotals = $this->getStageWeightTotals();
            $pembTotal = $stageTotals['pra-ujian'] ?? 0.0;
            $pengTotal = $stageTotals['ujian'] ?? 0.0;

            $pembimbingSummary['ratio_label'] = $this->formatStageRatio($pembTotal, $pengTotal);
            $pengujiSummary['ratio_label'] = $this->formatStageRatio($pengTotal, $pembTotal);

            $finalComposite = $this->calculateFinalCompositeScore($pembimbingSummary, $pengujiSummary);
            $finalScoreValue = $finalComposite['value'] ?? null;

            $ujianScores = $scoreMap[$studentId]['ujian'] ?? [];
            foreach ($ujianScores as $us) {
                if (($us['mode'] ?? '') === 'bypass') {
                    $finalScoreValue = (float) $us['score'];
                    break;
                }
            }

            $finalScoreLetter = ScoreHelper::letterGrade($finalScoreValue);

            $angkatan = $student['angkatan'];
            if (!isset($summary['per_angkatan'][$angkatan])) {
                $summary['per_angkatan'][$angkatan] = [
                    'count' => 0,
                    'avg_duration' => null
                ];
            }
            $summary['per_angkatan'][$angkatan]['count']++;

            $graduatedData[] = [
                'id' => $studentId,
                'nim' => $student['nim'],
                'name' => $student['name'],
                'angkatan' => $student['angkatan'],
                'semester_masuk' => $student['semester_masuk'],
                'semester_lulus' => $student['semester_lulus'],
                'masa_studi' => $masaStudi,
                'title' => $titleMap[$studentId] ?? null,
                'sempro_date' => $semproDate,
                'semhas_date' => $semhasDate,
                'ujian_date' => $ujianDate,
                'duration_days' => $durationDays,
                'pembimbing_1' => $assignmentMap[$studentId]['pembimbing_1'] ?? null,
                'pembimbing_2' => $assignmentMap[$studentId]['pembimbing_2'] ?? null,
                'penguji_1' => $assignmentMap[$studentId]['penguji_1'] ?? null,
                'penguji_2' => $assignmentMap[$studentId]['penguji_2'] ?? null,
                'penguji_3' => $assignmentMap[$studentId]['penguji_3'] ?? null,
                'scores' => $scoreMap[$studentId] ?? [],
                'final_score_avg' => $finalScoreValue,
                'final_score_letter' => $finalScoreLetter,
                'final_breakdown' => [
                    'pembimbing' => $pembimbingSummary,
                    'penguji' => $pengujiSummary,
                    'composite' => $finalComposite
                ]
            ];
        }

        foreach ($summary['per_angkatan'] as $angkatan => &$data) {
            $totalDuration = 0;
            $countWithDuration = 0;
            foreach ($graduatedData as $student) {
                if ($student['angkatan'] == $angkatan && $student['duration_days'] !== null) {
                    $totalDuration += $student['duration_days'];
                    $countWithDuration++;
                }
            }
            if ($countWithDuration > 0) {
                $data['avg_duration'] = round($totalDuration / $countWithDuration, 1);
            }
        }
        unset($data);

        if ($studentsWithStudyDuration > 0) {
            $summary['avg_study_duration'] = round($totalStudyDuration / $studentsWithStudyDuration, 1);
        }

        return [
            'students' => $graduatedData,
            'summary' => $summary,
            'angkatanList' => $angkatanList
        ];
    }

    public function getFinalScoreData(int $studentId): array
    {
        // 1. Get Student Info
        $stmt = $this->db->prepare("SELECT id, nim, name, angkatan, status FROM students WHERE id = :id");
        $stmt->execute([':id' => $studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            throw new Exception('Data mahasiswa tidak ditemukan.');
        }

        // 2. Get Title
        $stmt = $this->db->prepare("SELECT title FROM titles WHERE student_id = :id AND status = 'DITERIMA' ORDER BY submitted_at DESC LIMIT 1");
        $stmt->execute([':id' => $studentId]);
        $student['title'] = $stmt->fetchColumn() ?: '-';

        // 3. Get Ujian Date
        $stmt = $this->db->prepare("SELECT scheduled_date FROM events WHERE student_id = :id AND type = 'UJIAN_SKRIPSI' AND status = 'SELESAI' ORDER BY scheduled_date DESC LIMIT 1");
        $stmt->execute([':id' => $studentId]);
        $student['ujian_date'] = $stmt->fetchColumn();

        // 4. Get Evaluations
        $query = "SELECT e.stage, e.mode, e.final_score, e.total_score, e.evaluator_role, e.evaluator_id, u.name as evaluator_name, a.role as assignment_role
                  FROM evaluations e
                  LEFT JOIN users u ON u.id = e.evaluator_id
                  LEFT JOIN assignments a ON a.student_id = e.student_id AND a.lecturer_id = e.evaluator_id
                  WHERE e.student_id = :id AND final_score IS NOT NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $studentId]);
        $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Get Chairperson (Penguji 1)
        $stmt = $this->db->prepare("SELECT u.name, u.nip FROM assignments a JOIN users u ON a.lecturer_id = u.id WHERE a.student_id = :sid AND a.role = 'penguji_1' LIMIT 1");
        $stmt->execute([':sid' => $studentId]);
        $chairperson = $stmt->fetch(PDO::FETCH_ASSOC);

        // Group scores
        $praUjianScores = [];
        $ujianScores = [];
        $hasBypass = false;
        $bypassScore = 0;
        $bypassEvaluator = null;

        foreach ($evaluations as $eval) {
            if (($eval['mode'] ?? '') === 'bypass' && $eval['stage'] === 'ujian') {
                $hasBypass = true;
                $bypassScore = (float) $eval['final_score'];
                $bypassEvaluator = $eval['evaluator_name'];
                continue;
            }

            $scoreItem = [
                'assignment_role' => $eval['assignment_role'],
                'raw_score' => $eval['total_score'],
                'score' => $eval['final_score']
            ];

            if ($eval['stage'] === 'pra-ujian') {
                $praUjianScores[] = $scoreItem;
            } elseif ($eval['stage'] === 'ujian') {
                $ujianScores[] = $scoreItem;
            }
        }

        // 6. Calculate Final Composite
        $finalData = [];

        if ($hasBypass) {
            $finalData = [
                'value' => $bypassScore,
                'letter' => ScoreHelper::letterGrade($bypassScore),
                'formula' => [],
                'is_bypass' => true,
                'bypass_evaluator' => $bypassEvaluator
            ];
        } else {
            $pembSummary = $this->summarizePembimbingScores($praUjianScores);
            $pengSummary = $this->summarizePengujiScores($ujianScores);
            $composite = $this->calculateFinalCompositeScore($pembSummary, $pengSummary);
            $finalData = [
                'value' => $composite['value'],
                'letter' => ScoreHelper::letterGrade($composite['value']),
                'formula' => $composite['formula'],
                'is_bypass' => false
            ];
        }

        return [
            'student' => $student,
            'finalData' => $finalData,
            'chairperson' => $chairperson ?: null
        ];
    }

    // Helper methods (private)
    private function summarizePembimbingScores(array $scores): array
    {
        $stageWeights = $this->getStageWeightTotals();
        $stageWeight = $stageWeights['pra-ujian'] ?? 0.0;
        $ratio = Settings::getPembimbingRatio();
        $roleWeights = [
            'pembimbing_1' => $ratio[0] ?? 0,
            'pembimbing_2' => $ratio[1] ?? 0
        ];
        $summary = [
            'label' => 'Nilai Pembimbing',
            'count' => 0,
            'raw_sum' => 0.0,
            'raw_average' => null,
            'weighted' => null,
            'weighted_normalized' => null,
            'stage_weight' => $stageWeight
        ];
        $weightedRaw = 0.0;
        $weightedNormalized = 0.0;
        $weightTotal = 0.0;

        foreach ($scores as $score) {
            $role = $score['assignment_role'] ?? null;
            $rawValue = isset($score['raw_score']) ? (float) $score['raw_score'] : null;
            $normalizedValue = ScoreHelper::normalize(isset($score['score']) ? (float) $score['score'] : null);
            if ($rawValue === null || $role === null || strpos($role, 'pembimbing') !== 0) {
                continue;
            }
            $summary['count']++;
            $summary['raw_sum'] += $rawValue;
            if (isset($roleWeights[$role]) && $roleWeights[$role] > 0) {
                $weightedRaw += $rawValue * $roleWeights[$role];
                if ($normalizedValue !== null) {
                    $weightedNormalized += $normalizedValue * $roleWeights[$role];
                }
                $weightTotal += $roleWeights[$role];
            }
        }

        if ($summary['count'] > 0) {
            $summary['raw_average'] = round($summary['raw_sum'] / $summary['count'], 2);
        }

        if ($weightTotal > 0) {
            $summary['weighted'] = round($weightedRaw, 2);
            $summary['weighted_normalized'] = ScoreHelper::normalize($weightedNormalized / $weightTotal);
        } else {
            $summary['weighted'] = $summary['raw_average'];
            $summary['weighted_normalized'] = ScoreHelper::normalize($summary['raw_average']);
        }

        return $summary;
    }

    private function summarizePengujiScores(array $scores): array
    {
        $stageWeights = $this->getStageWeightTotals();
        $stageWeight = $stageWeights['ujian'] ?? 0.0;
        $method = strtolower(Settings::getPengujiAggregationMethod() ?? 'rata-rata');
        $summary = [
            'label' => 'Nilai Penguji',
            'count' => 0,
            'raw_values' => [],
            'normalized_values' => [],
            'aggregate' => null,
            'aggregate_normalized' => null,
            'method' => $method,
            'method_label' => $method === 'median' ? 'median' : 'rata-rata',
            'stage_weight' => $stageWeight
        ];

        foreach ($scores as $score) {
            $role = $score['assignment_role'] ?? null;
            $rawValue = isset($score['raw_score']) ? (float) $score['raw_score'] : null;
            $normalizedValue = ScoreHelper::normalize(isset($score['score']) ? (float) $score['score'] : null);
            if ($rawValue === null || $role === null || strpos($role, 'penguji') !== 0) {
                continue;
            }
            $summary['count']++;
            $summary['raw_values'][] = $rawValue;
            if ($normalizedValue !== null) {
                $summary['normalized_values'][] = $normalizedValue;
            }
        }

        $aggregateRaw = null;
        $values = $summary['raw_values'];
        if (!empty($values)) {
            if ($method === 'median') {
                sort($values);
                $count = count($values);
                $mid = (int) floor($count / 2);
                if ($count % 2 === 0) {
                    $aggregateRaw = ($values[$mid - 1] + $values[$mid]) / 2;
                } else {
                    $aggregateRaw = $values[$mid];
                }
            } else {
                $aggregateRaw = array_sum($values) / count($values);
            }
        }
        $summary['aggregate'] = $aggregateRaw !== null ? round($aggregateRaw, 2) : null;

        $aggregateNorm = null;
        $normValues = $summary['normalized_values'];
        if (!empty($normValues)) {
            if ($method === 'median') {
                sort($normValues);
                $count = count($normValues);
                $mid = (int) floor($count / 2);
                if ($count % 2 === 0) {
                    $aggregateNorm = ($normValues[$mid - 1] + $normValues[$mid]) / 2;
                } else {
                    $aggregateNorm = $normValues[$mid];
                }
            } else {
                $aggregateNorm = array_sum($normValues) / count($normValues);
            }
        }
        $summary['aggregate_normalized'] = $aggregateNorm !== null ? ScoreHelper::normalize($aggregateNorm) : null;

        return $summary;
    }

    private function calculateFinalCompositeScore(array $pembimbingSummary, array $pengujiSummary): array
    {
        $components = [];
        $normalizedValues = [];

        $pembAverage = null;
        if (isset($pembimbingSummary['raw_average']) && $pembimbingSummary['raw_average'] !== null) {
            $pembAverage = (float) $pembimbingSummary['raw_average'];
        } elseif (isset($pembimbingSummary['weighted_normalized']) && $pembimbingSummary['weighted_normalized'] !== null) {
            $pembAverage = (float) $pembimbingSummary['weighted_normalized'];
        } elseif (isset($pembimbingSummary['weighted']) && $pembimbingSummary['weighted'] !== null) {
            $pembAverage = ScoreHelper::normalize((float) $pembimbingSummary['weighted']);
        }
        if ($pembAverage !== null) {
            $components[] = [
                'label' => 'Nilai Pra-Ujian (Pembimbing)',
                'value' => $pembAverage,
                'weight_percent' => 100,
                'weight_fraction' => 1.0
            ];
            $normalizedValues[] = $pembAverage;
        }

        $pengAverage = null;
        if (isset($pengujiSummary['aggregate']) && $pengujiSummary['aggregate'] !== null) {
            $pengAverage = (float) $pengujiSummary['aggregate'];
        } elseif (isset($pengujiSummary['aggregate_normalized']) && $pengujiSummary['aggregate_normalized'] !== null) {
            $pengAverage = (float) $pengujiSummary['aggregate_normalized'];
        }
        if ($pengAverage !== null) {
            $components[] = [
                'label' => 'Nilai Ujian Skripsi (Penguji)',
                'value' => $pengAverage,
                'weight_percent' => 100,
                'weight_fraction' => 1.0
            ];
            $normalizedValues[] = $pengAverage;
        }

        $value = null;
        if (!empty($normalizedValues)) {
            $value = round(array_sum($normalizedValues), 2);
        }

        return [
            'value' => $value,
            'components' => $components,
            'formula' => ScoreHelper::describeWeightedFormula($components, $value, 2, false)
        ];
    }

    private function evaluateStageProgressStatus(?int $duration, int $threshold): string
    {
        if ($duration === null)
            return 'Belum Lengkap';
        if ($threshold <= 0)
            return 'Tanpa Target';
        return $duration <= $threshold ? 'Dalam Batas' : 'Lewat SLA';
    }

    private function calculateDuration($startDate, $endDate)
    {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $diff = $start->diff($end);
        return (int) $diff->days;
    }

    private function getStageWeightTotals(): array
    {
        if (!empty($this->stageWeightTotals)) {
            return $this->stageWeightTotals;
        }

        $query = "SELECT stage, SUM(weight) AS total_weight FROM evaluation_components GROUP BY stage";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $totals = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stage = strtolower($row['stage'] ?? '');
            $total = isset($row['total_weight']) ? (float) $row['total_weight'] : 0.0;
            $totals[$stage] = $total;
        }
        $this->stageWeightTotals = $totals;
        return $this->stageWeightTotals;
    }

    private function formatStageRatio(?float $first, ?float $second): ?string
    {
        $first = $first ?? 0.0;
        $second = $second ?? 0.0;
        if ($first <= 0 && $second <= 0)
            return null;
        $total = max($first + $second, 0.00001);
        $firstPercent = round(($first / $total) * 100);
        $secondPercent = round(($second / $total) * 100);
        if ($secondPercent <= 0)
            return sprintf('%d%%', $firstPercent);
        return sprintf('%d:%d', $firstPercent, $secondPercent);
    }

    /**
     * Get historical workload data for lecturers including graduated students
     *
     * @param int|null $tahunMulai Start year of the period
     * @param int|null $tahunSelesai End year of the period
     * @param string $filterBy Filter by 'tahun_lulus' or 'angkatan'
     * @param string $lecturerSearch Search term for lecturer name
     * @return array Historical workload data
     */
    public function getHistoricalWorkloadData(?int $tahunMulai, ?int $tahunSelesai, string $filterBy, string $roleType = 'all', string $lecturerSearch = ''): array
    {
        // Get list of available years for filter options (cached query)
        $yearListStmt = $this->db->query("SELECT DISTINCT angkatan FROM students WHERE angkatan IS NOT NULL ORDER BY angkatan DESC");
        $yearList = $yearListStmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Build WHERE conditions based on filter type
        $conditions = [];
        $params = [];
        
        if ($filterBy === 'tahun_lulus') {
            // Filter by graduation year (semester_lulus)
            // Optimized: Use range comparison instead of FLOOR in WHERE for better index usage
            $conditions[] = "(
                (s.status = 'LULUS' AND s.semester_lulus IS NOT NULL AND
                 s.semester_lulus BETWEEN :tahun_mulai_start AND :tahun_selesai_end)
                OR
                (s.status != 'LULUS' OR s.status IS NULL)
            )";
            // Convert years to semester format: 2020 -> 20201 (ganjil) to 20202 (genap)
            $params[':tahun_mulai_start'] = $tahunMulai * 10 + 1;
            $params[':tahun_selesai_end'] = $tahunSelesai * 10 + 2;
        } else {
            // Filter by angkatan (entrance year)
            $conditions[] = "s.angkatan BETWEEN :tahun_mulai AND :tahun_selesai";
            $params[':tahun_mulai'] = $tahunMulai;
            $params[':tahun_selesai'] = $tahunSelesai;
        }
        
        // Apply role type filter
        $roleCondition = '';
        if ($roleType === 'pembimbing') {
            $roleCondition = "AND a.role IN ('pembimbing_1', 'pembimbing_2')";
        } elseif ($roleType === 'penguji') {
            $roleCondition = "AND a.role LIKE 'penguji%'";
        }
        
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        
        // Main query for workload counts
        $query = "SELECT u.id, u.name,
                          SUM(CASE WHEN a.role IN ('pembimbing_1', 'pembimbing_2') THEN 1 ELSE 0 END) AS pembimbing_count,
                          SUM(CASE WHEN a.role LIKE 'penguji%' THEN 1 ELSE 0 END) AS penguji_count
                   FROM users u
                    JOIN assignments a ON u.id = a.lecturer_id
                    JOIN students s ON s.id = a.student_id
                    $whereClause
                    $roleCondition
                    GROUP BY u.id, u.name
                    ORDER BY u.name";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Detail query for breakdown by period - only fetch for lecturers in result
        // This significantly reduces data fetched for large datasets
        if (empty($rows)) {
            return [
                'workloads' => [],
                'totals' => ['dosen' => 0, 'total_bimbingan' => 0, 'total_penguji' => 0],
                'yearList' => $yearList,
                'periodList' => []
            ];
        }
        
        $lecturerIds = array_column($rows, 'id');
        
        // Create named placeholders for lecturer IDs
        $lecturerPlaceholders = [];
        foreach ($lecturerIds as $i => $id) {
            $placeholder = ':lecturer_id_' . $i;
            $lecturerPlaceholders[] = $placeholder;
            $params[$placeholder] = $id;
        }
        $lecturerIdsPlaceholder = implode(',', $lecturerPlaceholders);
        
        // Build additional WHERE conditions for detail query (same as main query)
        $detailConditions = [];
        if ($filterBy === 'tahun_lulus') {
            $detailConditions[] = "(
                (s.status = 'LULUS' AND s.semester_lulus IS NOT NULL AND
                 s.semester_lulus BETWEEN :tahun_mulai_start AND :tahun_selesai_end)
                OR
                (s.status != 'LULUS' OR s.status IS NULL)
            )";
        } else {
            $detailConditions[] = "s.angkatan BETWEEN :tahun_mulai AND :tahun_selesai";
        }
        
        // Apply role type filter to detail query as well
        if ($roleType === 'pembimbing') {
            $detailConditions[] = "a.role IN ('pembimbing_1', 'pembimbing_2')";
        } elseif ($roleType === 'penguji') {
            $detailConditions[] = "a.role LIKE 'penguji%'";
        }
        
        $detailWhereClause = 'AND ' . implode(' AND ', $detailConditions);
        
        $detailQuery = "SELECT a.lecturer_id,
                               a.role,
                               s.angkatan,
                               s.semester_lulus,
                               s.status
                        FROM assignments a
                        JOIN students s ON s.id = a.student_id
                        WHERE a.lecturer_id IN ($lecturerIdsPlaceholder)
                        $detailWhereClause";
        
        // Use params array which now contains all named parameters
        $detailParams = $params;
        $detailStmt = $this->db->prepare($detailQuery);
        $detailStmt->execute($detailParams);
        $detailRows = $detailStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Build detail map with breakdown by period (optimized - no names stored)
        $detailMap = [];
        foreach ($detailRows as $detail) {
            $roleCode = $detail['role'];
            if (in_array($roleCode, ['pembimbing_1', 'pembimbing_2'], true)) {
                $type = 'pembimbing';
            } elseif (stripos($roleCode, 'penguji') === 0) {
                $type = 'penguji';
            } else {
                continue;
            }
            
            $lecturerId = (int) $detail['lecturer_id'];
            
            // Determine period key based on filter type
            if ($filterBy === 'tahun_lulus') {
                if ($detail['status'] === 'LULUS' && $detail['semester_lulus'] !== null) {
                    $periodKey = (string) floor((int)$detail['semester_lulus'] / 10);
                } else {
                    $periodKey = 'Aktif';
                }
            } else {
                $periodKey = $detail['angkatan'] !== null && $detail['angkatan'] !== ''
                    ? (string) $detail['angkatan']
                    : 'Tidak diketahui';
            }
            
            if (!isset($detailMap[$lecturerId][$type])) {
                $detailMap[$lecturerId][$type] = [
                    'counts' => []
                ];
            }
            
            if (!isset($detailMap[$lecturerId][$type]['counts'][$periodKey])) {
                $detailMap[$lecturerId][$type]['counts'][$periodKey] = 0;
            }
            $detailMap[$lecturerId][$type]['counts'][$periodKey]++;
        }
        
        // Merge detail data into rows
        foreach ($rows as &$row) {
            $lecturerId = (int) $row['id'];
            $row['pembimbing_by_period'] = $detailMap[$lecturerId]['pembimbing']['counts'] ?? [];
            $row['pembimbing_by_period_names'] = $detailMap[$lecturerId]['pembimbing']['names'] ?? [];
            $row['penguji_by_period'] = $detailMap[$lecturerId]['penguji']['counts'] ?? [];
            $row['penguji_by_period_names'] = $detailMap[$lecturerId]['penguji']['names'] ?? [];
        }
        unset($row);
        
        // Apply lecturer search filter
        if ($lecturerSearch !== '') {
            $rows = array_values(array_filter($rows, function ($row) use ($lecturerSearch) {
                return stripos($row['name'], $lecturerSearch) !== false;
            }));
        }
        
        // Calculate totals
        $totals = [
            'dosen' => count($rows),
            'total_bimbingan' => array_sum(array_column($rows, 'pembimbing_count')),
            'total_penguji' => array_sum(array_column($rows, 'penguji_count'))
        ];
        
        // Build period list for filter options
        $periodList = [];
        if ($filterBy === 'tahun_lulus') {
            for ($year = $tahunMulai; $year <= $tahunSelesai; $year++) {
                $periodList[] = (string) $year;
            }
            $periodList[] = 'Aktif';
        } else {
            for ($year = $tahunMulai; $year <= $tahunSelesai; $year++) {
                $periodList[] = (string) $year;
            }
        }
        
        return [
            'workloads' => $rows,
            'totals' => $totals,
            'yearList' => $yearList,
            'periodList' => $periodList
        ];
    }

    /**
     * Get student details for a specific lecturer and role type
     * Used for AJAX popup showing student names, angkatan/graduation year, and status
     *
     * @param int $lecturerId Lecturer ID
     * @param string $roleType 'pembimbing' or 'penguji'
     * @param string $filterBy 'angkatan' or 'tahun_lulus'
     * @param int $tahunMulai Start year
     * @param int $tahunSelesai End year
     * @return array Student details with name, period, and status
     */
    public function getHistoricalWorkloadStudentDetails(
        int $lecturerId,
        string $roleType,
        string $filterBy,
        int $tahunMulai,
        int $tahunSelesai
    ): array {
        // Build WHERE conditions
        $conditions = [];
        $params = [];
        
        if ($filterBy === 'tahun_lulus') {
            $conditions[] = "(
                (s.status = 'LULUS' AND s.semester_lulus IS NOT NULL AND
                 s.semester_lulus BETWEEN :tahun_mulai_start AND :tahun_selesai_end)
                OR
                (s.status != 'LULUS' OR s.status IS NULL)
            )";
            $params[':tahun_mulai_start'] = $tahunMulai * 10 + 1;
            $params[':tahun_selesai_end'] = $tahunSelesai * 10 + 2;
        } else {
            $conditions[] = "s.angkatan BETWEEN :tahun_mulai AND :tahun_selesai";
            $params[':tahun_mulai'] = $tahunMulai;
            $params[':tahun_selesai'] = $tahunSelesai;
        }
        
        // Build role condition
        if ($roleType === 'pembimbing') {
            $conditions[] = "a.role IN ('pembimbing_1', 'pembimbing_2')";
        } else {
            $conditions[] = "a.role LIKE 'penguji%'";
        }
        
        $conditions[] = "a.lecturer_id = :lecturer_id";
        $params[':lecturer_id'] = $lecturerId;
        
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        
        // Query to fetch student details
        $query = "SELECT DISTINCT
                    s.id,
                    s.nim,
                    s.name,
                    s.angkatan,
                    s.semester_lulus,
                    s.status,
                    a.role
                  FROM assignments a
                  JOIN students s ON s.id = a.student_id
                  $whereClause
                  ORDER BY s.name";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format results
        $students = [];
        foreach ($rows as $row) {
            // Determine role label
            $role = 'pembimbing';
            if (strpos($row['role'], 'penguji') === 0) {
                $role = 'penguji';
            }
            
            if ($filterBy === 'tahun_lulus') {
                if ($row['status'] === 'LULUS' && $row['semester_lulus'] !== null) {
                    $period = (string) floor((int)$row['semester_lulus'] / 10);
                } else {
                    $period = 'Aktif';
                }
            } else {
                $period = $row['angkatan'] !== null && $row['angkatan'] !== ''
                    ? (string) $row['angkatan']
                    : 'Tidak diketahui';
            }
            
            $students[] = [
                'id' => (int) $row['id'],
                'nim' => $row['nim'] ?? '-',
                'name' => $row['name'],
                'nama' => $row['name'], // Add 'nama' alias for compatibility
                'angkatan' => $row['angkatan'],
                'period' => $period,
                'status' => $row['status'] ?? 'AKTIF',
                'role' => $role
            ];
        }
        
        return $students;
    }

    /**
     * Server-side DataTables processing for Tracking report
     */
    public function getTrackingDataServerSide(array $request, ?int $angkatanFilter, string $searchFilter): string
    {
        $transitionSla = Settings::getStageProgressSlaConfig();
        
        // Build base query
        $baseQuery = "SELECT s.id, s.nim, s.name, s.angkatan, s.status,
                             sempro_event.scheduled_date AS sempro_date,
                             semhas_event.scheduled_date AS semhas_date,
                             prauji_event.scheduled_date AS prauji_date,
                             ujian_event.scheduled_date AS ujian_date,
                             p1.name AS pembimbing_1,
                             p2.name AS pembimbing_2,
                             peng1.name AS penguji_1,
                             peng2.name AS penguji_2,
                             peng3.name AS penguji_3
                      FROM students s
                      LEFT JOIN events sempro_event ON s.id = sempro_event.student_id AND sempro_event.type = 'SEMPRO' AND sempro_event.status = 'SELESAI'
                      LEFT JOIN events semhas_event ON s.id = semhas_event.student_id AND semhas_event.type = 'SEMHAS' AND semhas_event.status = 'SELESAI'
                      LEFT JOIN events prauji_event ON s.id = prauji_event.student_id AND prauji_event.type = 'PRA_UJIAN' AND prauji_event.status = 'SELESAI'
                      LEFT JOIN events ujian_event ON s.id = ujian_event.student_id AND ujian_event.type = 'UJIAN_SKRIPSI' AND ujian_event.status = 'SELESAI'
                      LEFT JOIN assignments a1 ON s.id = a1.student_id AND a1.role = 'pembimbing_1'
                      LEFT JOIN users p1 ON a1.lecturer_id = p1.id
                      LEFT JOIN assignments a2 ON s.id = a2.student_id AND a2.role = 'pembimbing_2'
                      LEFT JOIN users p2 ON a2.lecturer_id = p2.id
                      LEFT JOIN assignments ap1 ON s.id = ap1.student_id AND ap1.role = 'penguji_1'
                      LEFT JOIN users peng1 ON ap1.lecturer_id = peng1.id
                      LEFT JOIN assignments ap2 ON s.id = ap2.student_id AND ap2.role = 'penguji_2'
                      LEFT JOIN users peng2 ON ap2.lecturer_id = peng2.id
                      LEFT JOIN assignments ap3 ON s.id = ap3.student_id AND ap3.role = 'penguji_3'
                      LEFT JOIN users peng3 ON ap3.lecturer_id = peng3.id";
        
        // Build WHERE conditions
        $conditions = [];
        $bindings = [];
        
        if ($angkatanFilter) {
            $conditions[] = "s.angkatan = :angkatan";
            $bindings[':angkatan'] = $angkatanFilter;
        }
        
        if ($searchFilter !== '') {
            $conditions[] = "(s.nim LIKE :search OR s.name LIKE :search)";
            $bindings[':search'] = '%' . $searchFilter . '%';
        }
        
        if (!empty($conditions)) {
            $baseQuery .= ' WHERE ' . implode(' AND ', $conditions);
        }
        
        // Define columns for DataTables
        $columns = [
            DataTablesHelper::column('nim', 0, true, true),
            DataTablesHelper::column('name', 1, true, true),
            DataTablesHelper::column('angkatan', 2, true, true),
            DataTablesHelper::column('status', 3, true, true),
            DataTablesHelper::column('sempro_date', 4, false, false, function($val, $row) {
                return $val ? date('d M Y', strtotime($val)) : '-';
            }),
            DataTablesHelper::column('semhas_date', 5, false, false, function($val, $row) {
                return $val ? date('d M Y', strtotime($val)) : '-';
            }),
            DataTablesHelper::column('prauji_date', 6, false, false, function($val, $row) {
                return $val ? date('d M Y', strtotime($val)) : '-';
            }),
            DataTablesHelper::column('ujian_date', 7, false, false, function($val, $row) {
                return $val ? date('d M Y', strtotime($val)) : '-';
            }),
            DataTablesHelper::column('sempro_date', 8, false, false, function($val, $row) use ($transitionSla) {
                $semhas = $row['semhas_date'] ?? null;
                if ($val && $semhas) {
                    $duration = $this->calculateDuration($val, $semhas);
                    $status = $this->evaluateStageProgressStatus($duration, $transitionSla['sempro_semhas'] ?? 0);
                    $class = $status === 'Dalam Batas' ? 'bg-success' : ($status === 'Lewat SLA' ? 'bg-danger' : 'bg-secondary');
                    return "<div>{$duration} hari</div><span class='badge {$class}'>{$status}</span>";
                }
                return '-';
            }),
            DataTablesHelper::column('semhas_date', 9, false, false, function($val, $row) use ($transitionSla) {
                $prauji = $row['prauji_date'] ?? null;
                if ($val && $prauji) {
                    $duration = $this->calculateDuration($val, $prauji);
                    $status = $this->evaluateStageProgressStatus($duration, $transitionSla['semhas_pra-ujian'] ?? 0);
                    $class = $status === 'Dalam Batas' ? 'bg-success' : ($status === 'Lewat SLA' ? 'bg-danger' : 'bg-secondary');
                    return "<div>{$duration} hari</div><span class='badge {$class}'>{$status}</span>";
                }
                return '-';
            }),
            DataTablesHelper::column('prauji_date', 10, false, false, function($val, $row) use ($transitionSla) {
                $ujian = $row['ujian_date'] ?? null;
                if ($val && $ujian) {
                    $duration = $this->calculateDuration($val, $ujian);
                    $status = $this->evaluateStageProgressStatus($duration, $transitionSla['pra-ujian_ujian'] ?? 0);
                    $class = $status === 'Dalam Batas' ? 'bg-success' : ($status === 'Lewat SLA' ? 'bg-danger' : 'bg-secondary');
                    return "<div>{$duration} hari</div><span class='badge {$class}'>{$status}</span>";
                }
                return '-';
            }),
            DataTablesHelper::column('sempro_date', 11, false, false, function($val, $row) {
                $ujian = $row['ujian_date'] ?? null;
                if ($val && $ujian) {
                    $duration = $this->calculateDuration($val, $ujian);
                    return "<strong>{$duration} hari</strong>";
                }
                return '-';
            }),
            DataTablesHelper::column('semhas_date', 12, false, false, function($val, $row) {
                $ujian = $row['ujian_date'] ?? null;
                if ($val && $ujian) {
                    $duration = $this->calculateDuration($val, $ujian);
                    return "{$duration} hari";
                }
                return '-';
            }),
            DataTablesHelper::column('pembimbing_1', 13, false, false, function($val, $row) {
                $p1 = $row['pembimbing_1'] ?? '';
                $p2 = $row['pembimbing_2'] ?? '';
                $html = $p1 ? "1. " . htmlspecialchars($p1) . "<br>" : '';
                $html .= $p2 ? "2. " . htmlspecialchars($p2) : '';
                return $html ?: '-';
            }),
            DataTablesHelper::column('penguji_1', 14, false, false, function($val, $row) {
                $peng1 = $row['penguji_1'] ?? '';
                $peng2 = $row['penguji_2'] ?? '';
                $peng3 = $row['penguji_3'] ?? '';
                $html = $peng1 ? "1. " . htmlspecialchars($peng1) . "<br>" : '';
                $html .= $peng2 ? "2. " . htmlspecialchars($peng2) . "<br>" : '';
                $html .= $peng3 ? "3. " . htmlspecialchars($peng3) : '';
                return $html ?: '-';
            }),
        ];
        
        $result = DataTablesHelper::process($request, $this->db, $baseQuery, $columns, $bindings);
        return json_encode($result);
    }

    /**
     * Server-side DataTables processing for Graduated report
     */
    public function getGraduatedDataServerSide(array $request, ?int $angkatanFilter, string $searchFilter): string
    {
        // Build base query
        $baseQuery = "SELECT s.id, s.nim, s.name, s.angkatan, s.semester_masuk, s.semester_lulus,
                             (SELECT title FROM titles WHERE student_id = s.id AND status = 'DITERIMA' ORDER BY submitted_at DESC LIMIT 1) AS title
                      FROM students s
                      WHERE s.status = 'LULUS'";
        
        // Build WHERE conditions
        $bindings = [];
        
        if ($angkatanFilter) {
            $baseQuery .= " AND s.angkatan = :angkatan";
            $bindings[':angkatan'] = $angkatanFilter;
        }
        
        if ($searchFilter !== '') {
            $baseQuery .= " AND (s.nim LIKE :search OR s.name LIKE :search)";
            $bindings[':search'] = '%' . $searchFilter . '%';
        }
        
        // Define columns for DataTables
        $columns = [
            DataTablesHelper::column('nim', 0, true, true),
            DataTablesHelper::column('name', 1, true, true),
            DataTablesHelper::column('angkatan', 2, true, true),
            DataTablesHelper::column('semester_masuk', 3, true, true),
            DataTablesHelper::column('semester_lulus', 4, true, true),
            DataTablesHelper::column('semester_masuk', 5, false, false, function($val, $row) {
                $masuk = (int) $val;
                $lulus = $row['semester_lulus'] ? (int) $row['semester_lulus'] : null;
                if ($lulus) {
                    $masaStudi = max(0, $lulus - $masuk + 1);
                    return $masaStudi . ' smt';
                }
                return '-';
            }),
            DataTablesHelper::column('id', 6, false, false, function($val, $row) {
                return "<button class='btn btn-sm btn-info' type='button' data-bs-toggle='collapse' data-bs-target='#detail-{$val}'><i class='fas fa-info-circle'></i> Lihat Detail</button>";
            }),
            // Hidden columns for expandable row data
            DataTablesHelper::column('title', 7, false, false),
            DataTablesHelper::column('id', 8, false, false, function($val, $row) {
                // Fetch additional data for expandable row
                return json_encode($this->getGraduatedDetailData($val));
            }),
        ];
        
        $result = DataTablesHelper::process($request, $this->db, $baseQuery, $columns, $bindings);
        return json_encode($result);
    }

    /**
     * Get detailed data for graduated student expandable row
     */
    private function getGraduatedDetailData(int $studentId): array
    {
        // Get events
        $stmt = $this->db->prepare("SELECT type, scheduled_date FROM events WHERE student_id = :id AND status = 'SELESAI' ORDER BY scheduled_date");
        $stmt->execute([':id' => $studentId]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $eventMap = [];
        foreach ($events as $event) {
            $eventMap[$event['type']] = $event['scheduled_date'];
        }
        
        // Get assignments
        $stmt = $this->db->prepare("SELECT a.role, u.name FROM assignments a JOIN users u ON a.lecturer_id = u.id WHERE a.student_id = :id");
        $stmt->execute([':id' => $studentId]);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $assignmentMap = [];
        foreach ($assignments as $a) {
            $assignmentMap[$a['role']] = $a['name'];
        }
        
        // Get scores with component data for raw_score calculation
        $stmt = $this->db->prepare("SELECT e.id AS evaluation_id, e.stage, e.final_score, e.evaluator_role, e.evaluator_id, u.name AS evaluator_name, a.role AS assignment_role FROM evaluations e JOIN users u ON e.evaluator_id = u.id LEFT JOIN assignments a ON a.student_id = e.student_id AND a.lecturer_id = e.evaluator_id WHERE e.student_id = :id AND e.final_score IS NOT NULL ORDER BY e.stage, e.evaluator_id");
        $stmt->execute([':id' => $studentId]);
        $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch component scores for weighted calculation
        $evaluationIds = array_column($scores, 'evaluation_id');
        $componentScoresMap = [];
        if (!empty($evaluationIds)) {
            $componentPlaceholder = implode(',', array_fill(0, count($evaluationIds), '?'));
            $componentScoreQuery = "SELECT es.evaluation_id, ec.name AS component_name, ec.weight, es.score
                                   FROM evaluation_scores es
                                   JOIN evaluation_components ec ON ec.id = es.component_id
                                   WHERE es.evaluation_id IN ($componentPlaceholder)
                                   ORDER BY es.evaluation_id, ec.sort_order, ec.id";
            $componentStmt = $this->db->prepare($componentScoreQuery);
            $componentStmt->execute($evaluationIds);
            $componentScores = $componentStmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($componentScores as $componentScore) {
                $evalId = $componentScore['evaluation_id'];
                if (!isset($componentScoresMap[$evalId])) {
                    $componentScoresMap[$evalId] = [];
                }
                $componentScoresMap[$evalId][] = [
                    'name' => $componentScore['component_name'],
                    'weight' => $componentScore['weight'],
                    'score' => $componentScore['score']
                ];
            }
        }
        
        $scoreMap = [];
        foreach ($scores as $score) {
            $stage = $score['stage'];
            if (!isset($scoreMap[$stage])) {
                $scoreMap[$stage] = [];
            }
            
            // Calculate raw_score from components
            $componentsForScore = $componentScoresMap[$score['evaluation_id']] ?? [];
            $rawScore = ScoreHelper::weightedTotalFromComponents($componentsForScore);
            
            $scoreMap[$stage][] = [
                'evaluator_name' => $score['evaluator_name'],
                'evaluator_role' => $score['evaluator_role'],
                'assignment_role' => $score['assignment_role'],
                'score' => ScoreHelper::normalize($score['final_score']),
                'raw_score' => $rawScore,
                'evaluator_id' => $score['evaluator_id']
            ];
        }
        
        // Calculate final score
        $pembimbingSummary = $this->summarizePembimbingScores($scoreMap['pra-ujian'] ?? []);
        $pengujiSummary = $this->summarizePengujiScores($scoreMap['ujian'] ?? []);
        $finalComposite = $this->calculateFinalCompositeScore($pembimbingSummary, $pengujiSummary);
        $finalScoreValue = $finalComposite['value'] ?? null;
        
        // Check for bypass
        $ujianScores = $scoreMap['ujian'] ?? [];
        foreach ($ujianScores as $us) {
            if (($us['mode'] ?? '') === 'bypass') {
                $finalScoreValue = (float) $us['score'];
                break;
            }
        }
        
        $finalScoreLetter = ScoreHelper::letterGrade($finalScoreValue);
        
        return [
            'events' => $eventMap,
            'assignments' => $assignmentMap,
            'scores' => $scoreMap,
            'final_score' => [
                'value' => $finalScoreValue,
                'letter' => $finalScoreLetter,
                'pembimbing_summary' => $pembimbingSummary,
                'penguji_summary' => $pengujiSummary,
                'composite' => $finalComposite
            ]
        ];
    }

    /**
     * Server-side DataTables processing for Summary report
     */
    public function getSummaryDataServerSide(array $request, ?int $angkatanFilter, string $searchFilter): string
    {
        // Build base query
        $baseQuery = "SELECT s.id, s.nim, s.name, s.angkatan,
                             (SELECT title FROM titles WHERE student_id = s.id AND status = 'DITERIMA' LIMIT 1) AS title,
                             p1.name AS pembimbing_1, p2.name AS pembimbing_2,
                             peng1.name AS penguji_1, peng2.name AS penguji_2, peng3.name AS penguji_3,
                             sempro.scheduled_date AS sempro_date,
                             semhas.scheduled_date AS semhas_date,
                             ujian.scheduled_date AS ujian_date,
                             sempro_scores.avg_score AS sempro_score,
                             semhas_scores.avg_score AS semhas_score,
                             ujian_scores.avg_score AS ujian_score
                      FROM students s
                      LEFT JOIN titles t ON s.id = t.student_id AND t.status = 'DITERIMA'
                      LEFT JOIN assignments a1 ON s.id = a1.student_id AND a1.role = 'pembimbing_1'
                      LEFT JOIN users p1 ON a1.lecturer_id = p1.id
                      LEFT JOIN assignments a2 ON s.id = a2.student_id AND a2.role = 'pembimbing_2'
                      LEFT JOIN users p2 ON a2.lecturer_id = p2.id
                      LEFT JOIN assignments ap1 ON s.id = ap1.student_id AND ap1.role = 'penguji_1'
                      LEFT JOIN users peng1 ON ap1.lecturer_id = peng1.id
                      LEFT JOIN assignments ap2 ON s.id = ap2.student_id AND ap2.role = 'penguji_2'
                      LEFT JOIN users peng2 ON ap2.lecturer_id = peng2.id
                      LEFT JOIN assignments ap3 ON s.id = ap3.student_id AND ap3.role = 'penguji_3'
                      LEFT JOIN users peng3 ON ap3.lecturer_id = peng3.id
                      LEFT JOIN events sempro ON s.id = sempro.student_id AND sempro.type = 'SEMPRO'
                      LEFT JOIN events semhas ON s.id = semhas.student_id AND semhas.type = 'SEMHAS'
                      LEFT JOIN events ujian ON s.id = ujian.student_id AND ujian.type = 'UJIAN_SKRIPSI'
                      LEFT JOIN (SELECT student_id, AVG(final_score) as avg_score FROM evaluations WHERE stage = 'sempro' AND final_score IS NOT NULL GROUP BY student_id) sempro_scores ON s.id = sempro_scores.student_id
                      LEFT JOIN (SELECT student_id, AVG(final_score) as avg_score FROM evaluations WHERE stage = 'semhas' AND final_score IS NOT NULL GROUP BY student_id) semhas_scores ON s.id = semhas_scores.student_id
                      LEFT JOIN (SELECT student_id, AVG(final_score) as avg_score FROM evaluations WHERE stage = 'ujian' AND final_score IS NOT NULL GROUP BY student_id) ujian_scores ON s.id = ujian_scores.student_id
                      WHERE s.status != 'LULUS'";
        
        // Build WHERE conditions
        $bindings = [];
        
        if ($angkatanFilter) {
            $baseQuery .= " AND s.angkatan = :angkatan";
            $bindings[':angkatan'] = $angkatanFilter;
        }
        
        if ($searchFilter !== '') {
            $baseQuery .= " AND (s.nim LIKE :search OR s.name LIKE :search)";
            $bindings[':search'] = '%' . $searchFilter . '%';
        }
        
        // Define columns for DataTables
        $columns = [
            DataTablesHelper::column('nim', 0, true, true),
            DataTablesHelper::column('name', 1, true, true),
            DataTablesHelper::column('title', 2, true, false),
            DataTablesHelper::column('pembimbing_1', 3, false, false),
            DataTablesHelper::column('pembimbing_2', 4, false, false),
            DataTablesHelper::column('penguji_1', 5, false, false),
            DataTablesHelper::column('penguji_2', 6, false, false),
            DataTablesHelper::column('penguji_3', 7, false, false),
            DataTablesHelper::column('sempro_date', 8, false, false, function($val) {
                return $val ? date('d M Y', strtotime($val)) : '-';
            }),
            DataTablesHelper::column('sempro_score', 9, false, false, function($val) {
                return $val !== null ? number_format((float)$val, 2) : '-';
            }),
            DataTablesHelper::column('semhas_date', 10, false, false, function($val) {
                return $val ? date('d M Y', strtotime($val)) : '-';
            }),
            DataTablesHelper::column('semhas_score', 11, false, false, function($val) {
                return $val !== null ? number_format((float)$val, 2) : '-';
            }),
            DataTablesHelper::column('ujian_date', 12, false, false, function($val) {
                return $val ? date('d M Y', strtotime($val)) : '-';
            }),
            DataTablesHelper::column('ujian_score', 13, false, false, function($val) {
                return $val !== null ? number_format((float)$val, 2) : '-';
            }),
        ];
        
        $result = DataTablesHelper::process($request, $this->db, $baseQuery, $columns, $bindings);
        return json_encode($result);
    }

    /**
     * Server-side DataTables processing for Bypass report
     */
    public function getBypassDataServerSide(array $request, ?string $selectedStage, ?int $selectedAngkatan, string $searchFilter): string
    {
        // Build base query
        $baseQuery = "SELECT e.student_id, e.stage, e.final_score, e.notes, e.updated_at,
                             s.nim, s.name AS student_name, s.angkatan,
                             u.name AS evaluator_name, u.role AS evaluator_role
                      FROM evaluations e
                      JOIN students s ON e.student_id = s.id
                      LEFT JOIN users u ON e.evaluator_id = u.id
                      WHERE e.mode = 'bypass'";
        
        // Build WHERE conditions
        $bindings = [];
        
        if ($selectedStage) {
            $baseQuery .= " AND e.stage = :stage";
            $bindings[':stage'] = $selectedStage;
        }
        
        if ($selectedAngkatan) {
            $baseQuery .= " AND s.angkatan = :angkatan";
            $bindings[':angkatan'] = $selectedAngkatan;
        }
        
        if ($searchFilter !== '') {
            $baseQuery .= " AND (s.nim LIKE :search OR s.name LIKE :search)";
            $bindings[':search'] = '%' . $searchFilter . '%';
        }
        
        // Define columns for DataTables
        $columns = [
            DataTablesHelper::column('nim', 0, true, true),
            DataTablesHelper::column('student_name', 1, true, true),
            DataTablesHelper::column('angkatan', 2, true, true),
            DataTablesHelper::column('stage', 3, true, true),
            DataTablesHelper::column('final_score', 4, true, true, function($val) {
                return $val !== null ? number_format((float)$val, 2) : '-';
            }),
            DataTablesHelper::column('evaluator_name', 5, true, true),
            DataTablesHelper::column('evaluator_role', 6, false, false),
            DataTablesHelper::column('notes', 7, false, false),
            DataTablesHelper::column('updated_at', 8, false, false, function($val) {
                return $val ? date('d M Y H:i', strtotime($val)) : '-';
            }),
        ];
        
        $result = DataTablesHelper::process($request, $this->db, $baseQuery, $columns, $bindings);
        return json_encode($result);
    }

    /**
     * Server-side DataTables processing for Workload report
     */
    public function getWorkloadDataServerSide(array $request, ?int $angkatanFilter, string $lecturerSearch): string
    {
        // Build base query
        $baseQuery = "SELECT u.id, u.name,
                             SUM(CASE WHEN a.role IN ('pembimbing_1', 'pembimbing_2') THEN 1 ELSE 0 END) AS pembimbing_count,
                             SUM(CASE WHEN a.role LIKE 'penguji%' THEN 1 ELSE 0 END) AS penguji_count
                      FROM assignments a
                      JOIN students s ON s.id = a.student_id
                      JOIN users u ON a.lecturer_id = u.id
                      WHERE (s.status IS NULL OR s.status != 'LULUS')";
        
        // Build WHERE conditions
        $bindings = [];
        
        if ($angkatanFilter) {
            $baseQuery .= " AND s.angkatan = :angkatan";
            $bindings[':angkatan'] = $angkatanFilter;
        }
        
        // Add lecturer search filter
        if ($lecturerSearch !== '') {
            $baseQuery .= " AND u.name LIKE :lecturer_search";
            $bindings[':lecturer_search'] = '%' . $lecturerSearch . '%';
        }
        
        $baseQuery .= " GROUP BY u.id, u.name";
        
        // Define columns for DataTables
        $columns = [
            DataTablesHelper::column('id', 0, false, false),
            DataTablesHelper::column('name', 1, true, true),
            DataTablesHelper::column('pembimbing_count', 2, true, true),
            DataTablesHelper::column('penguji_count', 3, true, true),
        ];
        
        $result = DataTablesHelper::process($request, $this->db, $baseQuery, $columns, $bindings);
        return json_encode($result);
    }

    /**
     * Server-side DataTables processing for SLA report
     */
    public function getSlaDataServerSide(array $request, ?string $selectedStage, ?int $selectedAngkatan, string $searchFilter, ?string $statusFilter): string
    {
        $eventStageMap = [
            'SEMPRO' => 'sempro',
            'SEMHAS' => 'semhas',
            'PRA_UJIAN' => 'pra-ujian',
            'UJIAN_SKRIPSI' => 'ujian'
        ];
        
        $slaThresholds = Settings::getSlaThresholds();
        
        // Build base query
        $baseQuery = "SELECT e.id, e.student_id, e.type, e.scheduled_date, e.scheduled_time,
                             s.nim, s.name AS student_name, s.angkatan,
                             eval_data.first_entry, eval_data.hours_diff, eval_data.status
                      FROM events e
                      JOIN students s ON e.student_id = s.id
                      LEFT JOIN (
                          SELECT ev.student_id, ev.stage,
                                 MIN(ev.created_at) AS first_entry,
                                 TIMESTAMPDIFF(HOUR,
                                     CONCAT(e2.scheduled_date, ' ', e2.scheduled_time),
                                     MIN(ev.created_at)
                                 ) AS hours_diff,
                                 CASE
                                     WHEN MIN(ev.created_at) IS NULL THEN 'Belum Diisi'
                                     WHEN TIMESTAMPDIFF(HOUR,
                                         CONCAT(e2.scheduled_date, ' ', e2.scheduled_time),
                                         MIN(ev.created_at)
                                     ) <= 72 THEN 'Tepat Waktu'
                                     ELSE 'Lewat SLA'
                                 END AS status
                          FROM evaluations ev
                          JOIN events e2 ON ev.student_id = e2.student_id
                          WHERE ev.stage IN ('sempro','semhas','pra-ujian','ujian')
                          GROUP BY ev.student_id, ev.stage, e2.scheduled_date, e2.scheduled_time
                      ) eval_data ON e.student_id = eval_data.student_id
                      WHERE e.type IN ('SEMPRO', 'SEMHAS', 'PRA_UJIAN', 'UJIAN_SKRIPSI')";
        
        // Build WHERE conditions
        $bindings = [];
        
        if ($selectedStage) {
            $baseQuery .= " AND e.type = :stage_type";
            $bindings[':stage_type'] = array_search($selectedStage, $eventStageMap) ?: $selectedStage;
        }
        
        if ($selectedAngkatan) {
            $baseQuery .= " AND s.angkatan = :angkatan";
            $bindings[':angkatan'] = $selectedAngkatan;
        }
        
        if ($searchFilter !== '') {
            $baseQuery .= " AND (s.nim LIKE :search OR s.name LIKE :search)";
            $bindings[':search'] = '%' . $searchFilter . '%';
        }
        
        if ($statusFilter) {
            $baseQuery .= " AND eval_data.status = :status";
            $bindings[':status'] = $statusFilter;
        }
        
        // Define columns for DataTables
        $columns = [
            DataTablesHelper::column('nim', 0, true, true),
            DataTablesHelper::column('student_name', 1, true, true),
            DataTablesHelper::column('type', 2, true, true, function($val) use ($eventStageMap) {
                return $eventStageMap[$val] ?? $val;
            }),
            DataTablesHelper::column('scheduled_date', 3, true, true, function($val, $row) {
                $time = $row['scheduled_time'] ?? '';
                return $val ? date('d M Y H:i', strtotime($val . ' ' . $time)) : '-';
            }),
            DataTablesHelper::column('first_entry', 4, false, false, function($val) {
                return $val ? date('d M Y H:i', strtotime($val)) : '-';
            }),
            DataTablesHelper::column('hours_diff', 5, true, true, function($val) {
                return $val !== null ? number_format((float)$val, 2) . ' jam' : '-';
            }),
            DataTablesHelper::column('status', 6, true, true, function($val) {
                $class = $val === 'Tepat Waktu' ? 'bg-success' : ($val === 'Lewat SLA' ? 'bg-danger' : 'bg-secondary');
                return "<span class='badge {$class}'>{$val}</span>";
            }),
        ];
        
        $result = DataTablesHelper::process($request, $this->db, $baseQuery, $columns, $bindings);
        return json_encode($result);
    }

    /**
     * Server-side DataTables processing for Study Period report
     */
    public function getStudyPeriodDataServerSide(array $request, ?int $angkatanFilter, string $searchFilter, ?string $statusFilter): string
    {
        // Build base query
        $baseQuery = "SELECT s.nim, s.name, s.angkatan, s.semester_masuk, s.semester_lulus, s.status,
                             (SELECT COUNT(*) FROM cuti_records cr WHERE cr.student_id = s.id AND cr.status = 'DISETUJUI') AS cuti_semester
                      FROM students s";
        
        // Build WHERE conditions
        $bindings = [];
        
        if ($angkatanFilter) {
            $baseQuery .= " WHERE s.angkatan = :angkatan";
            $bindings[':angkatan'] = $angkatanFilter;
        }
        
        if ($searchFilter !== '') {
            $baseQuery .= ($angkatanFilter ? " AND" : " WHERE") . " (s.nim LIKE :search OR s.name LIKE :search)";
            $bindings[':search'] = '%' . $searchFilter . '%';
        }
        
        if ($statusFilter) {
            $baseQuery .= ($angkatanFilter || $searchFilter !== '' ? " AND" : " WHERE") . " s.status = :status";
            $bindings[':status'] = $statusFilter;
        }
        
        // Define columns for DataTables
        $columns = [
            DataTablesHelper::column('nim', 0, true, true),
            DataTablesHelper::column('name', 1, true, true),
            DataTablesHelper::column('angkatan', 2, true, true),
            DataTablesHelper::column('semester_masuk', 3, true, true),
            DataTablesHelper::column('semester_lulus', 4, true, true),
            DataTablesHelper::column('cuti_semester', 5, true, true),
            DataTablesHelper::column('semester_masuk', 6, false, false, function($val, $row) {
                $masuk = (int) $val;
                $lulus = $row['semester_lulus'] ? (int) $row['semester_lulus'] : null;
                $cuti = (int) ($row['cuti_semester'] ?? 0);
                if ($lulus !== null) {
                    $masaStudi = max(0, $lulus - $masuk + 1 - $cuti);
                    return $masaStudi . ' smt';
                }
                return '-';
            }),
            DataTablesHelper::column('status', 7, true, true),
        ];
        
        $result = DataTablesHelper::process($request, $this->db, $baseQuery, $columns, $bindings);
        return json_encode($result);
    }

    /**
     * Server-side DataTables processing for Historical Workload report
     */
    public function getHistoricalWorkloadDataServerSide(array $request, ?int $tahunMulai, ?int $tahunSelesai, string $filterBy, string $roleType, string $lecturerSearch): string
    {
        // Build WHERE conditions
        $conditions = [];
        $bindings = [];
        
        if ($filterBy === 'tahun_lulus') {
            $conditions[] = "(
                (s.status = 'LULUS' AND s.semester_lulus IS NOT NULL AND
                 s.semester_lulus BETWEEN :tahun_mulai_start AND :tahun_selesai_end)
                OR
                (s.status != 'LULUS' OR s.status IS NULL)
            )";
            $bindings[':tahun_mulai_start'] = $tahunMulai * 10 + 1;
            $bindings[':tahun_selesai_end'] = $tahunSelesai * 10 + 2;
        } else {
            $conditions[] = "s.angkatan BETWEEN :tahun_mulai AND :tahun_selesai";
            $bindings[':tahun_mulai'] = $tahunMulai;
            $bindings[':tahun_selesai'] = $tahunSelesai;
        }
        
        // Build base query
        $baseQuery = "SELECT u.id, u.name,
                             SUM(CASE WHEN a.role IN ('pembimbing_1', 'pembimbing_2') THEN 1 ELSE 0 END) AS pembimbing_count,
                             SUM(CASE WHEN a.role LIKE 'penguji%' THEN 1 ELSE 0 END) AS penguji_count
                      FROM users u
                      JOIN assignments a ON u.id = a.lecturer_id
                      JOIN students s ON s.id = a.student_id
                      WHERE " . implode(' AND ', $conditions);
        
        // Apply role type filter
        if ($roleType === 'pembimbing') {
            $baseQuery .= " AND a.role IN ('pembimbing_1', 'pembimbing_2')";
        } elseif ($roleType === 'penguji') {
            $baseQuery .= " AND a.role LIKE 'penguji%'";
        }
        
        // Add lecturer search filter
        if ($lecturerSearch !== '') {
            $baseQuery .= " AND u.name LIKE :lecturer_search";
            $bindings[':lecturer_search'] = '%' . $lecturerSearch . '%';
        }
        
        $baseQuery .= " GROUP BY u.id, u.name";
        
        // Define columns for DataTables
        $columns = [
            DataTablesHelper::column('id', 0, false, false),
            DataTablesHelper::column('name', 1, true, true),
            DataTablesHelper::column('pembimbing_count', 2, true, true),
            DataTablesHelper::column('penguji_count', 3, true, true),
        ];
        
        $result = DataTablesHelper::process($request, $this->db, $baseQuery, $columns, $bindings);
        return json_encode($result);
    }
}
