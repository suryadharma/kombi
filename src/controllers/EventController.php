<?php

class EventController extends BaseController
{
    private $stageOptions = [
        'sempro' => 'Seminar Proposal (Sempro)',
        'semhas' => 'Seminar Hasil (Semhas)',
        'ujian' => 'Ujian Skripsi'
    ];

    private $statusOptions = [
        'MENUNGGU' => 'Menunggu Pelaksanaan',
        'SELESAI' => 'Selesai',
        'BATAL' => 'Dibatalkan'
    ];

    public function index()
    {
        $this->requireAuth();
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        $stageFilter = $_GET['stage'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $angkatanFilter = $_GET['angkatan'] ?? '';
        $searchFilter = trim($_GET['search'] ?? '');
        $hideCompleted = $_GET['hide_completed'] ?? '1';
        $hideCompleted = ($hideCompleted === '0') ? false : true;

        // Sync pending events so finished stages automatically marked as completed
        $syncStage = $stageFilter !== '' ? $stageFilter : null;
        EventService::syncPendingEvents($db, null, $syncStage);

        $activeAngkatan = Settings::getActiveAngkatan();
        if ($angkatanFilter !== '' && !empty($activeAngkatan) && !in_array((int) $angkatanFilter, $activeAngkatan, true)) {
            $angkatanFilter = '';
        }

        $query = "SELECT e.*, s.nim, s.name AS student_name, s.angkatan,
                          (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = s.id AND a.role = 'pembimbing_1' LIMIT 1) as pembimbing_1,
                          (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = s.id AND a.role = 'pembimbing_2' LIMIT 1) as pembimbing_2,
                          (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = s.id AND a.role = 'penguji_1' LIMIT 1) as penguji_1,
                          (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = s.id AND a.role = 'penguji_2' LIMIT 1) as penguji_2,
                          (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = s.id AND a.role = 'penguji_3' LIMIT 1) as penguji_3
                   FROM events e
                   JOIN students s ON e.student_id = s.id
                   WHERE 1=1";
        $params = [];

        if ($stageFilter !== '' && isset($this->stageOptions[$stageFilter])) {
            $eventType = EventService::toEventType($stageFilter);
            $query .= " AND e.type = :type";
            $params[':type'] = $eventType;
        }

        if ($statusFilter !== '' && isset($this->statusOptions[$statusFilter])) {
            $query .= " AND e.status = :status";
            $params[':status'] = $statusFilter;
        }

        if ($angkatanFilter !== '') {
            $query .= " AND s.angkatan = :angkatan";
            $params[':angkatan'] = $angkatanFilter;
        }

        if ($searchFilter !== '') {
            $query .= " AND (s.nim LIKE :search OR s.name LIKE :search)";
            $params[':search'] = '%' . $searchFilter . '%';
        }

        $query .= " ORDER BY e.scheduled_date DESC, e.scheduled_time DESC";
        $stmt = $db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $externalTokensByEvent = [];
        if (!empty($events)) {
            $eventIds = array_column($events, 'id');
            $placeholders = implode(',', array_fill(0, count($eventIds), '?'));
            $tokenQuery = "SELECT event_id, nip, token, expires_at
                           FROM external_tokens
                           WHERE event_id IN ($placeholders)
                           ORDER BY created_at DESC";
            $tokenStmt = $db->prepare($tokenQuery);
            $tokenStmt->execute($eventIds);
            while ($tokenRow = $tokenStmt->fetch(PDO::FETCH_ASSOC)) {
                $eventId = (int) $tokenRow['event_id'];
                if (!isset($externalTokensByEvent[$eventId])) {
                    $externalTokensByEvent[$eventId] = [
                        'nip' => $tokenRow['nip'],
                        'token' => $tokenRow['token'],
                        'expires_at' => $tokenRow['expires_at']
                    ];
                }
            }

            if (!empty($externalTokensByEvent)) {
                $nipList = [];
                foreach ($externalTokensByEvent as $external) {
                    $nipList[] = $external['nip'];
                }
                $nipList = array_values(array_unique($nipList));

                if (!empty($nipList)) {
                    $nipPlaceholders = implode(',', array_fill(0, count($nipList), '?'));
                    $userQuery = "SELECT username, nip FROM users WHERE role = 'penguji_eksternal' AND nip IN ($nipPlaceholders)";
                    $userStmt = $db->prepare($userQuery);
                    $userStmt->execute($nipList);
                    $nipToUsername = [];
                    while ($userRow = $userStmt->fetch(PDO::FETCH_ASSOC)) {
                        $nipToUsername[$userRow['nip']] = $userRow['username'];
                    }

                    foreach ($externalTokensByEvent as $eventId => $external) {
                        $nip = $external['nip'];
                        $externalTokensByEvent[$eventId]['username'] = $nipToUsername[$nip] ?? $nip;
                    }
                }
            }
        }

        $formattedEvents = array_map(function ($event) use ($externalTokensByEvent) {
            $stage = EventService::toStage($event['type']) ?? '';
            $event['stage'] = $stage;
            $event['stage_label'] = EventService::stageLabel($stage);
            $event['external_token'] = $externalTokensByEvent[(int) $event['id']] ?? null;
            return $event;
        }, $events);

        $studentSchedules = [];
        foreach ($formattedEvents as $event) {
            $studentId = (int) $event['student_id'];
            if (!isset($studentSchedules[$studentId])) {
                $studentSchedules[$studentId] = [
                    'student_id' => $studentId,
                    'nim' => $event['nim'],
                    'student_name' => $event['student_name'],
                    'angkatan' => $event['angkatan'],
                    'pembimbing_1' => $event['pembimbing_1'] ?? null,
                    'pembimbing_2' => $event['pembimbing_2'] ?? null,
                    'penguji_1' => $event['penguji_1'] ?? null,
                    'penguji_2' => $event['penguji_2'] ?? null,
                    'penguji_3' => $event['penguji_3'] ?? null,
                    'stages' => [],
                    'pending_count' => 0,
                    'completed_count' => 0,
                    'next_stage' => null,
                    'next_schedule' => null,
                ];
            }

            $studentSchedules[$studentId]['stages'][] = $event;
            if ($event['status'] === 'SELESAI') {
                $studentSchedules[$studentId]['completed_count']++;
            } elseif ($event['status'] === 'MENUNGGU') {
                $studentSchedules[$studentId]['pending_count']++;
                $eventDateTime = $event['scheduled_date'] . ' ' . $event['scheduled_time'];
                if ($studentSchedules[$studentId]['next_schedule'] === null || $eventDateTime < $studentSchedules[$studentId]['next_schedule']) {
                    $studentSchedules[$studentId]['next_schedule'] = $eventDateTime;
                    $studentSchedules[$studentId]['next_stage'] = $event['stage_label'];
                }
            }
        }

        if ($hideCompleted) {
            $studentSchedules = array_values(array_filter($studentSchedules, function ($student) {
                if (empty($student['stages'])) {
                    return true;
                }
                return $student['pending_count'] > 0;
            }));
        } else {
            $studentSchedules = array_values($studentSchedules);
        }

        if (!empty($activeAngkatan)) {
            $placeholders = implode(',', array_fill(0, count($activeAngkatan), '?'));
            $angkatanStmt = $db->prepare("SELECT DISTINCT angkatan FROM students WHERE angkatan IN ($placeholders) ORDER BY angkatan DESC");
            $angkatanStmt->execute($activeAngkatan);
            $angkatanList = $angkatanStmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $angkatanStmt = $db->prepare("SELECT DISTINCT angkatan FROM students WHERE angkatan IS NOT NULL AND angkatan != '' ORDER BY angkatan DESC");
            $angkatanStmt->execute();
            $angkatanList = $angkatanStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $this->render('events/index', [
            'events' => $formattedEvents,
            'studentSchedules' => $studentSchedules,
            'stageOptions' => $this->stageOptions,
            'statusOptions' => $this->statusOptions,
            'angkatanList' => $angkatanList,
            'activeAngkatan' => $activeAngkatan,
            'filters' => [
                'stage' => $stageFilter,
                'status' => $statusFilter,
                'angkatan' => $angkatanFilter,
                'search' => $searchFilter,
                'hide_completed' => $hideCompleted
            ],
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error')
        ]);
    }

    public function create()
    {
        $this->requireAuth();
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        $this->render('events/form', [
            'mode' => 'create',
            'event' => [
                'scheduled_date' => date('Y-m-d'),
                'scheduled_time' => '09:00',
                'status' => 'MENUNGGU'
            ],
            'stageOptions' => $this->stageOptions,
            'statusOptions' => $this->statusOptions,
            'students' => $this->getStudents($db),
            'externalTokens' => [],
            'externalLecturers' => $this->getExternalLecturers($db),
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success')
        ]);
    }

    public function store()
    {
        $this->requireAuth();
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/events/create');
            return;
        }

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $stage = $_POST['stage'] ?? '';
        $scheduledDate = $_POST['scheduled_date'] ?? '';
        $scheduledTime = $_POST['scheduled_time'] ?? '';
        $room = trim($_POST['room'] ?? '');
        $status = $_POST['status'] ?? 'MENUNGGU';
        $externalName = trim($_POST['external_name'] ?? '');
        $externalNip = trim($_POST['external_nip'] ?? '');

        $formData = [
            'student_id' => $studentId,
            'stage' => $stage,
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => $scheduledTime,
            'room' => $room,
            'status' => $status,
            'external_name' => $externalName,
            'external_nip' => $externalNip
        ];

        $database = new Database();
        $db = $database->getConnection();

        $errors = $this->validateForm($studentId, $stage, $scheduledDate, $scheduledTime, $room, $status);
        if (empty($errors)) {
            $errors = $this->validateSchedulingPrerequisites($db, $studentId);
        }
        if (!empty($errors)) {
            $this->renderFormWithErrors($formData, $errors);
            return;
        }

        try {
            $eventInfo = EventService::upsertEvent($db, $studentId, $stage, $scheduledDate, $scheduledTime, $room, $status);

            $tokenMessage = '';
            if ($externalName !== '' && $externalNip !== '') {
                $invite = ExternalInviteService::createExternalToken(
                    $db,
                    $eventInfo['id'],
                    $externalNip,
                    $externalName,
                    $scheduledDate,
                    $scheduledTime
                );
                $tokenMessage = " Token eksternal: {$invite['token']}.";
            }

            AuditLogger::log(
                $_SESSION['user_id'],
                $eventInfo['is_new'] ? 'create_event' : 'update_event',
                'events',
                (int) $eventInfo['id'],
                sprintf(
                    'Penjadwalan %s untuk mahasiswa ID %d pada %s %s di %s. Status: %s.',
                    EventService::stageLabel($eventInfo['stage']),
                    $studentId,
                    $scheduledDate,
                    $scheduledTime,
                    $room,
                    $status
                )
            );

            $this->setFlash('success', 'Penjadwalan berhasil disimpan.' . $tokenMessage);
            $this->redirect('/events');
        } catch (Exception $e) {
            $this->renderFormWithErrors($formData, ['Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function edit($params)
    {
        $this->requireAuth();
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $id = $params['id'] ?? null;
        if (!$id) {
            $this->redirect('/events');
            return;
        }

        $database = new Database();
        $db = $database->getConnection();

        $event = $this->findEvent($db, (int) $id);
        if (!$event) {
            $this->redirect('/events');
            return;
        }

        $stage = EventService::toStage($event['type']) ?? '';
        $event['stage'] = $stage;

        $tokens = $this->findExternalTokens($db, (int) $id);

        $this->render('events/form', [
            'mode' => 'edit',
            'event' => $event,
            'stageOptions' => $this->stageOptions,
            'statusOptions' => $this->statusOptions,
            'students' => $this->getStudents($db),
            'externalTokens' => $tokens,
            'externalLecturers' => $this->getExternalLecturers($db),
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success')
        ]);
    }

    public function update($params)
    {
        $this->requireAuth();
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $id = $params['id'] ?? null;
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/events');
            return;
        }

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $stage = $_POST['stage'] ?? '';
        $scheduledDate = $_POST['scheduled_date'] ?? '';
        $scheduledTime = $_POST['scheduled_time'] ?? '';
        $room = trim($_POST['room'] ?? '');
        $status = $_POST['status'] ?? 'MENUNGGU';
        $externalName = trim($_POST['external_name'] ?? '');
        $externalNip = trim($_POST['external_nip'] ?? '');

        $formData = [
            'id' => $id,
            'student_id' => $studentId,
            'stage' => $stage,
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => $scheduledTime,
            'room' => $room,
            'status' => $status,
            'external_name' => $externalName,
            'external_nip' => $externalNip
        ];

        $database = new Database();
        $db = $database->getConnection();

        $errors = $this->validateForm($studentId, $stage, $scheduledDate, $scheduledTime, $room, $status);
        if (empty($errors)) {
            $errors = $this->validateSchedulingPrerequisites($db, $studentId);
        }
        if (!empty($errors)) {
            $this->renderFormWithErrors($formData, $errors, true);
            return;
        }

        try {
            $existing = $this->findEvent($db, (int) $id);
            if (!$existing) {
                $this->setFlash('error', 'Penjadwalan tidak ditemukan.');
                $this->redirect('/events');
                return;
            }

            $eventType = EventService::toEventType($stage);
            if (!$eventType) {
                $this->renderFormWithErrors($formData, ['Tahap ujian tidak valid.'], true);
                return;
            }

            // Prevent duplicate schedules for the same student and stage (only check active/pending events)
            $conflictQuery = "SELECT id FROM events WHERE student_id = :student_id AND type = :type AND id != :id AND status = 'MENUNGGU' LIMIT 1";
            $conflictStmt = $db->prepare($conflictQuery);
            $conflictStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $conflictStmt->bindParam(':type', $eventType);
            $conflictStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $conflictStmt->execute();
            if ($conflictStmt->fetch(PDO::FETCH_ASSOC)) {
                $this->renderFormWithErrors($formData, ['Mahasiswa sudah memiliki jadwal pada tahap tersebut.'], true);
                return;
            }

            $update = "UPDATE events
                       SET student_id = :student_id,
                           type = :type,
                           scheduled_date = :date,
                           scheduled_time = :time,
                           room = :room,
                           status = :status
                       WHERE id = :id";
            $stmt = $db->prepare($update);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':type', $eventType);
            $stmt->bindParam(':date', $scheduledDate);
            $stmt->bindParam(':time', $scheduledTime);
            $stmt->bindParam(':room', $room);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $tokenMessage = '';
            if ($externalName !== '' && $externalNip !== '') {
                $invite = ExternalInviteService::createExternalToken(
                    $db,
                    (int) $id,
                    $externalNip,
                    $externalName,
                    $scheduledDate,
                    $scheduledTime
                );
                $tokenMessage = " Token eksternal: {$invite['token']}.";
            }

            AuditLogger::log(
                $_SESSION['user_id'],
                'update_event',
                'events',
                (int) $id,
                sprintf(
                    'Penjadwalan %s diperbarui untuk mahasiswa ID %d pada %s %s di %s. Status: %s.',
                    EventService::stageLabel($stage),
                    $studentId,
                    $scheduledDate,
                    $scheduledTime,
                    $room,
                    $status
                )
            );

            $this->setFlash('success', 'Penjadwalan berhasil diperbarui.' . $tokenMessage);
            $this->redirect('/events');
        } catch (Exception $e) {
            $this->renderFormWithErrors($formData, ['Terjadi kesalahan: ' . $e->getMessage()], true);
        }
    }

    public function delete($params)
    {
        $this->requireAuth();
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }

        $id = $params['id'] ?? null;
        if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/events');
            return;
        }

        try {
            $database = new Database();
            $db = $database->getConnection();

            $stmt = $db->prepare("DELETE FROM events WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                AuditLogger::log(
                    $_SESSION['user_id'],
                    'delete_event',
                    'events',
                    (int) $id,
                    'Penjadwalan dihapus oleh Kombi.'
                );
                $this->setFlash('success', 'Penjadwalan berhasil dihapus.');
            } else {
                $this->setFlash('error', 'Penjadwalan tidak ditemukan atau sudah dihapus.');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Gagal menghapus penjadwalan: ' . $e->getMessage());
        }

        $this->redirect('/events');
    }

    public function checkProgress()
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $studentId = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
        if ($studentId <= 0) {
            echo json_encode(['completed' => []]);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        $completedStages = [];
        $stages = array_keys($this->stageOptions);

        $debugInfo = [];
        foreach ($stages as $stage) {
            $debug = []; // Initialize debug for each stage
            $isComplete = $this->isStageCompleted($db, $studentId, $stage, $debug);
            if ($isComplete) {
                $completedStages[] = $stage;
            }
            $debugInfo[$stage] = $debug;
        }

        echo json_encode([
            'completed' => $completedStages,
            'debug' => $debugInfo
        ]);
        exit;
    }

    /**
     * Get lecturer assignments for a student
     * Returns pembimbing and penguji assignments
     */
    public function getLecturerAssignments()
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $studentId = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
        if ($studentId <= 0) {
            echo json_encode(['success' => false, 'lecturers' => []]);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT
                    a.role,
                    u.name as lecturer_name,
                    u.nip as lecturer_nip
                  FROM assignments a
                  JOIN users u ON u.id = a.lecturer_id
                  WHERE a.student_id = :student_id
                  ORDER BY
                    CASE a.role
                        WHEN 'pembimbing_1' THEN 1
                        WHEN 'pembimbing_2' THEN 2
                        WHEN 'penguji_1' THEN 3
                        WHEN 'penguji_2' THEN 4
                        WHEN 'penguji_3' THEN 5
                        ELSE 6
                    END";

        $stmt = $db->prepare($query);
        $stmt->execute([':student_id' => $studentId]);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $roleLabels = [
            'pembimbing_1' => 'Pembimbing 1',
            'pembimbing_2' => 'Pembimbing 2',
            'penguji_1' => 'Penguji Ketua',
            'penguji_2' => 'Penguji Anggota 1',
            'penguji_3' => 'Penguji Anggota 2'
        ];

        foreach ($assignments as &$assignment) {
            $assignment['role_label'] = $roleLabels[$assignment['role']] ?? ucfirst(str_replace('_', ' ', $assignment['role']));
        }

        echo json_encode([
            'success' => true,
            'lecturers' => $assignments
        ]);
        exit;
    }

    /**
     * Check for schedule conflicts
     * Returns conflicting schedules for the given date/time and lecturers
     */
    public function checkScheduleConflict()
    {
        $this->requireAuth();
        header('Content-Type: application/json');

        $studentId = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
        $scheduledDate = $_GET['scheduled_date'] ?? '';
        $scheduledTime = $_GET['scheduled_time'] ?? '';
        $excludeEventId = isset($_GET['exclude_event_id']) ? (int) $_GET['exclude_event_id'] : 0;

        if ($studentId <= 0 || $scheduledDate === '' || $scheduledTime === '') {
            echo json_encode(['success' => false, 'conflicts' => [], 'message' => 'Parameter tidak lengkap']);
            exit;
        }

        $database = new Database();
        $db = $database->getConnection();

        // Get lecturer assignments for this student
        $query = "SELECT lecturer_id FROM assignments WHERE student_id = :student_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':student_id' => $studentId]);
        $lecturerIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($lecturerIds)) {
            echo json_encode(['success' => true, 'conflicts' => [], 'message' => 'Tidak ada dosen yang ditetapkan']);
            exit;
        }

        // Check for conflicts with these lecturers
        $conflicts = [];
        $dateTime = $scheduledDate . ' ' . $scheduledTime;
        
        // Get events on the same date AND time, then check lecturer overlap
        // Use the same approach as the events index page
        $conflictQuery = "SELECT e.id, e.scheduled_date, e.scheduled_time, e.room,
                             s.nim, s.name as student_name,
                             (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = e.student_id AND a.role = 'pembimbing_1' LIMIT 1) as pembimbing_1,
                             (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = e.student_id AND a.role = 'pembimbing_2' LIMIT 1) as pembimbing_2,
                             (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = e.student_id AND a.role = 'penguji_1' LIMIT 1) as penguji_1,
                             (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = e.student_id AND a.role = 'penguji_2' LIMIT 1) as penguji_2,
                             (SELECT u.name FROM assignments a JOIN users u ON u.id = a.lecturer_id WHERE a.student_id = e.student_id AND a.role = 'penguji_3' LIMIT 1) as penguji_3,
                             e.type as event_type
                          FROM events e
                          JOIN students s ON e.student_id = s.id
                          WHERE e.scheduled_date = ?
                            AND e.scheduled_time = ?
                            AND e.status NOT IN ('BATAL', 'SELESAI')";
        
        if ($excludeEventId > 0) {
            $conflictQuery .= " AND e.id != ?";
        }

        $conflictStmt = $db->prepare($conflictQuery);
        
        // Build parameters array in order
        $params = [];
        $params[] = $scheduledDate; // First ? for scheduled_date
        $params[] = $scheduledTime; // Second ? for scheduled_time
        
        // Add exclude_event_id if needed
        if ($excludeEventId > 0) {
            $params[] = $excludeEventId;
        }
        
        $conflictStmt->execute($params);
        $conflictingEvents = $conflictStmt->fetchAll(PDO::FETCH_ASSOC);

        // Get current student's lecturers for comparison
        $currentStudentLecturersQuery = "SELECT u.name, a.role
                                           FROM assignments a
                                           JOIN users u ON u.id = a.lecturer_id
                                           WHERE a.student_id = :student_id";
        $currentStmt = $db->prepare($currentStudentLecturersQuery);
        $currentStmt->execute([':student_id' => $studentId]);
        $currentLecturers = $currentStmt->fetchAll(PDO::FETCH_ASSOC);
        $currentLecturerNames = array_column($currentLecturers, 'name');

        // Group conflicts by lecturer - only show overlapping lecturers
        foreach ($conflictingEvents as $event) {
            $roles = ['pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2', 'penguji_3'];
            foreach ($roles as $role) {
                $lecturerName = $event[$role] ?? null;
                if ($lecturerName && in_array($lecturerName, $currentLecturerNames)) {
                    if (!isset($conflicts[$lecturerName])) {
                        $conflicts[$lecturerName] = [];
                    }
                    // Avoid duplicate entries for the same event
                    $alreadyAdded = false;
                    foreach ($conflicts[$lecturerName] as $existing) {
                        if ($existing['event_id'] === $event['id']) {
                            $alreadyAdded = true;
                            break;
                        }
                    }
                    if (!$alreadyAdded) {
                        $conflicts[$lecturerName][] = [
                            'event_id' => $event['id'],
                            'time' => $event['scheduled_time'],
                            'room' => $event['room'],
                            'student' => $event['nim'] . ' - ' . $event['student_name'],
                            'event_type' => $event['event_type']
                        ];
                    }
                }
            }
        }
        
        // Also check for room conflicts (same room at same time, regardless of lecturers)
        $roomConflictQuery = "SELECT e.id, e.scheduled_date, e.scheduled_time, e.room,
                                 s.nim, s.name as student_name,
                                 e.type as event_type
                              FROM events e
                              JOIN students s ON e.student_id = s.id
                              WHERE e.scheduled_date = ?
                                AND e.scheduled_time = ?
                                AND e.room = ?
                                AND e.status NOT IN ('BATAL', 'SELESAI')";
        
        if ($excludeEventId > 0) {
            $roomConflictQuery .= " AND e.id != ?";
        }
        
        $roomConflictStmt = $db->prepare($roomConflictQuery);
        $roomParams = [$scheduledDate, $scheduledTime, $_GET['room'] ?? ''];
        if ($excludeEventId > 0) {
            $roomParams[] = $excludeEventId;
        }
        $roomConflictStmt->execute($roomParams);
        $roomConflicts = $roomConflictStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add room conflicts to the result
        if (!empty($roomConflicts)) {
            $conflicts['[RUANGAN]'] = [];
            foreach ($roomConflicts as $event) {
                $conflicts['[RUANGAN]'][] = [
                    'event_id' => $event['id'],
                    'time' => $event['scheduled_time'],
                    'room' => $event['room'],
                    'student' => $event['nim'] . ' - ' . $event['student_name'],
                    'event_type' => $event['event_type']
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'has_conflicts' => !empty($conflicts),
            'conflicts' => $conflicts,
            'message' => !empty($conflicts)
                ? 'Ditemukan ' . count($conflicts) . ' dosen memiliki jadwal bentrok'
                : 'Tidak ada jadwal bentrok'
        ]);
        exit;
    }

    private function isStageCompleted(PDO $db, int $studentId, string $stage, &$debug = null): bool
    {
        $debug = [];
        // 1. Check if ANY event of this stage is marked SELESAI
        $eventType = EventService::toEventType($stage);
        $stmt = $db->prepare("SELECT COUNT(*) FROM events WHERE student_id = :sid AND type = :type AND status = 'SELESAI'");
        $stmt->execute([':sid' => $studentId, ':type' => $eventType]);
        if ($stmt->fetchColumn() > 0) {
            return true;
        }

        // 2. Check if all evaluators have submitted scores
        // Logic: Get necessary roles for stage -> Count assignments -> Count submitted evaluations

        $roles = [];
        if (in_array($stage, ['sempro', 'semhas', 'pra-ujian', 'ujian'], true)) {
            // For simplicity, check all assignments (Pembimbing & Penguji)
            // Or filter by specific roles if you want stricter check per stage
            // But sticking to "complete" definition.
        } else {
            return false;
        }

        // Get count of assigned evaluators (pembimbing + penguji)
        $assignStmt = $db->prepare("SELECT COUNT(*) FROM assignments WHERE student_id = :sid AND (role LIKE 'pembimbing%' OR role LIKE 'penguji%')");
        $assignStmt->execute([':sid' => $studentId]);
        $assignedCount = (int) $assignStmt->fetchColumn();

        if ($assignedCount === 0) {
            $debug['reason'] = 'no_assignments';
            return false; // No evaluators -> not complete
        }

        // Get count of evaluators who submitted a final score for this stage
        $evalStmt = $db->prepare("
            SELECT COUNT(DISTINCT evaluator_id) 
            FROM evaluations 
            WHERE student_id = :sid 
              AND stage = :stage 
              AND final_score IS NOT NULL
        ");
        $evalStmt->execute([':sid' => $studentId, ':stage' => $stage]);
        $submittedCount = (int) $evalStmt->fetchColumn();

        $debug['assigned'] = $assignedCount;
        $debug['submitted'] = $submittedCount;

        // If everyone submitted
        return $submittedCount >= $assignedCount;
    }

    private function getStudents(PDO $db): array
    {
        $activeAngkatan = Settings::getActiveAngkatan();

        $query = "
            SELECT s.id, s.nim, s.name
            FROM students s
            WHERE s.status != 'LULUS'
              AND EXISTS (
                  SELECT 1 FROM titles t
                  WHERE t.student_id = s.id
                    AND t.status = 'DITERIMA'
              )
              AND EXISTS (
                  SELECT 1 FROM assignments a
                  WHERE a.student_id = s.id
                    AND a.role = 'pembimbing_1'
              )
              AND EXISTS (
                  SELECT 1 FROM assignments a
                  WHERE a.student_id = s.id
                    AND a.role = 'penguji_1'
              )
        ";

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

    private function validateSchedulingPrerequisites(PDO $db, int $studentId): array
    {
        if ($studentId <= 0) {
            return [];
        }

        $errors = [];

        $titleStmt = $db->prepare("
            SELECT COUNT(*) FROM titles
            WHERE student_id = :student_id
              AND status = 'DITERIMA'
        ");
        $titleStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $titleStmt->execute();
        if ((int) $titleStmt->fetchColumn() === 0) {
            $errors[] = 'Mahasiswa belum memiliki judul yang disetujui Kombi.';
        }

        $pembimbingStmt = $db->prepare("
            SELECT COUNT(*) FROM assignments
            WHERE student_id = :student_id
              AND role = 'pembimbing_1'
        ");
        $pembimbingStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $pembimbingStmt->execute();
        if ((int) $pembimbingStmt->fetchColumn() === 0) {
            $errors[] = 'Tetapkan pembimbing terlebih dahulu sebelum menjadwalkan tahap.';
        }

        $pengujiKetuaStmt = $db->prepare("
            SELECT COUNT(*) FROM assignments
            WHERE student_id = :student_id
              AND role = 'penguji_1'
        ");
        $pengujiKetuaStmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $pengujiKetuaStmt->execute();
        if ((int) $pengujiKetuaStmt->fetchColumn() === 0) {
            $errors[] = 'Tetapkan ketua penguji sebelum menjadwalkan tahap.';
        }

        return $errors;
    }

    private function findEvent(PDO $db, int $id): ?array
    {
        $query = "SELECT e.*, s.nim, s.name AS student_name
                  FROM events e
                  JOIN students s ON e.student_id = s.id
                  WHERE e.id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        return $event ?: null;
    }

    private function findExternalTokens(PDO $db, int $eventId): array
    {
        $query = "SELECT et.nip, et.token, et.expires_at, et.used_at, et.nda_agreed, et.created_at, l.name AS examiner_name
                  FROM external_tokens et
                  LEFT JOIN lecturers l ON l.nip = et.nip
                  WHERE et.event_id = :event_id
                  ORDER BY et.created_at DESC";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function validateForm(
        int $studentId,
        string $stage,
        string $date,
        string $time,
        string $room,
        string $status
    ): array {
        $errors = [];
        if ($studentId <= 0) {
            $errors[] = 'Mahasiswa wajib dipilih.';
        }
        if ($stage === '' || !isset($this->stageOptions[$stage])) {
            $errors[] = 'Tahap ujian tidak valid.';
        }
        if ($date === '') {
            $errors[] = 'Tanggal wajib diisi.';
        }
        if ($time === '') {
            $errors[] = 'Waktu wajib diisi.';
        }
        if ($room === '') {
            $errors[] = 'Ruang pelaksanaan wajib diisi.';
        }
        if ($status === '' || !isset($this->statusOptions[$status])) {
            $errors[] = 'Status penjadwalan tidak valid.';
        }
        return $errors;
    }

    private function renderFormWithErrors(array $formData, array $errors, bool $isEdit = false): void
    {
        $database = new Database();
        $db = $database->getConnection();

        $tokens = [];
        if ($isEdit && !empty($formData['id'])) {
            $tokens = $this->findExternalTokens($db, (int) $formData['id']);
        }

        $this->render('events/form', [
            'mode' => $isEdit ? 'edit' : 'create',
            'event' => $formData,
            'stageOptions' => $this->stageOptions,
            'statusOptions' => $this->statusOptions,
            'students' => $this->getStudents($db),
            'externalTokens' => $tokens,
            'externalLecturers' => $this->getExternalLecturers($db),
            'error' => implode(' ', $errors),
            'success' => null
        ]);
    }

    private function getExternalLecturers(PDO $db): array
    {
        $query = "
            SELECT COALESCE(u.name, l.name) AS name, l.nip
            FROM lecturers l
            LEFT JOIN users u ON l.user_id = u.id
            WHERE l.is_external = 1
            ORDER BY COALESCE(u.name, l.name)
        ";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
