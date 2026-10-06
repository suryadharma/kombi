<?php

require_once __DIR__ . '/../services/ReportService.php';
require_once __DIR__ . '/../helpers/DataTablesHelper.php';

class ReportController extends BaseController
{
    private $reportService;

    public function __construct()
    {
        try {
            // Connect to database
            $database = new Database();
            $db = $database->getConnection();
            $this->reportService = new ReportService($db);
        } catch (Exception $e) {
            // Log error but don't fail - will be handled in individual methods
            error_log("ReportController constructor error: " . $e->getMessage());
            $this->reportService = null;
        }
    }

    private function ensureAccess()
    {
        $this->requireAuth();
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            // Check if this is a data endpoint (for DataTables AJAX requests)
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($uri, '/data') !== false || strpos($uri, 'data=') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Akses ditolak', 'message' => 'Anda tidak memiliki akses ke halaman ini']);
                exit;
            }
            $this->redirect('/dashboard');
            exit;
        }
    }

    private function ensureSuperadminAccess()
    {
        $this->requireAuth();
        $role = $this->getUserRole();
        if ($role !== 'superadmin') {
            // Check if this is a data endpoint (for DataTables AJAX requests)
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            if (strpos($uri, '/data') !== false || strpos($uri, 'data=') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Akses ditolak', 'message' => 'Halaman ini hanya dapat diakses oleh superadmin']);
                exit;
            }
            $this->redirect('/dashboard');
            exit;
        }
    }

    public function studyPeriod()
    {
        $this->ensureAccess();

        $angkatanFilter = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
        $searchFilter = trim($_GET['student_search'] ?? '');
        $statusFilter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;

        $data = $this->reportService->getStudyPeriodData($angkatanFilter, $searchFilter, $statusFilter);

        $this->render('reports/study_period', array_merge($data, [
            'activeTab' => 'study',
            'selectedAngkatan' => $angkatanFilter,
            'currentSearch' => $searchFilter,
            'statusFilter' => $statusFilter
        ]));
    }

    public function workload()
    {
        $this->ensureAccess();

        $angkatanFilter = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
        $lecturerSearch = trim($_GET['lecturer_search'] ?? '');

        $data = $this->reportService->getWorkloadData($angkatanFilter, $lecturerSearch);

        // Additional rendering logic that was in controller (Settings)
        $maxPembimbing = (int) (Settings::get('max_pembimbing', 8));
        $maxPenguji = (int) (Settings::get('max_penguji', 10));

        $totals = [
            'dosen' => count($data['workloads']),
            'total_bimbingan' => array_sum(array_column($data['workloads'], 'pembimbing_count')),
            'total_penguji' => array_sum(array_column($data['workloads'], 'penguji_count'))
        ];

        $this->render('reports/workload', array_merge($data, [
            'maxPembimbing' => $maxPembimbing,
            'maxPenguji' => $maxPenguji,
            'totals' => $totals,
            'activeTab' => 'workload',
            'selectedAngkatan' => $angkatanFilter,
            'currentSearch' => $lecturerSearch
        ]));
    }

    public function sla()
    {
        $this->ensureSuperadminAccess();

        $stageLabels = [
            'sempro' => 'Seminar Proposal',
            'semhas' => 'Seminar Hasil',
            'pra-ujian' => 'Pra-Ujian',
            'ujian' => 'Ujian Skripsi'
        ];

        $selectedStage = isset($_GET['stage']) && $_GET['stage'] !== '' ? $_GET['stage'] : null;
        $selectedAngkatan = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
        $statusFilter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
        $searchFilter = trim($_GET['student_search'] ?? '');

        $data = $this->reportService->getSlaData($selectedStage, $selectedAngkatan, $searchFilter, $statusFilter);

        $this->render('reports/sla', array_merge($data, [
            'activeTab' => 'sla',
            'stageLabels' => $stageLabels,
            'selectedStage' => $selectedStage,
            'selectedAngkatan' => $selectedAngkatan,
            'statusFilter' => $statusFilter,
            'currentSearch' => $searchFilter
        ]));
    }

    public function bypassReport()
    {
        $this->ensureAccess();

        $stageLabels = [
            'sempro' => 'Seminar Proposal',
            'semhas' => 'Seminar Hasil',
            'pra-ujian' => 'Pra-Ujian',
            'ujian' => 'Ujian Skripsi'
        ];

        $selectedStage = isset($_GET['stage']) && $_GET['stage'] !== '' ? $_GET['stage'] : null;
        $selectedAngkatan = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
        $searchFilter = trim($_GET['student_search'] ?? '');

        $data = $this->reportService->getBypassData($selectedStage, $selectedAngkatan, $searchFilter);

        $this->render('reports/bypass', array_merge($data, [
            'activeTab' => 'bypass',
            'stageLabels' => $stageLabels,
            'stageFilter' => $selectedStage,
            'selectedAngkatan' => $selectedAngkatan,
            'currentSearch' => $searchFilter
        ]));
    }

    public function tracking()
    {
        $this->ensureAccess();

        $angkatanFilter = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
        $searchFilter = trim($_GET['student_search'] ?? '');

        try {
            $data = $this->reportService->getTrackingData($angkatanFilter, $searchFilter);

            $this->render('reports/tracking', array_merge($data, [
                'selectedAngkatan' => $angkatanFilter,
                'currentSearch' => $searchFilter,
                'activeTab' => 'tracking'
            ]));
        } catch (Exception $e) {
            $this->render('reports/tracking', [
                'students' => [],
                'angkatanList' => [],
                'selectedAngkatan' => $angkatanFilter,
                'currentSearch' => $searchFilter,
                'summary' => [],
                'activeTab' => 'tracking',
                'error' => 'Gagal memuat data pelacakan: ' . $e->getMessage()
            ]);
        }
    }

    public function graduated()
    {
        $this->ensureAccess();

        $angkatanFilter = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
        $searchFilter = trim($_GET['student_search'] ?? '');

        try {
            $data = $this->reportService->getGraduatedData($angkatanFilter, $searchFilter);

            $this->render('reports/graduated', array_merge($data, [
                'selectedAngkatan' => $angkatanFilter,
                'currentSearch' => $searchFilter,
                'activeTab' => 'graduated'
            ]));
        } catch (Exception $e) {
            $this->render('reports/graduated', [
                'students' => [],
                'angkatanList' => [],
                'selectedAngkatan' => $angkatanFilter,
                'currentSearch' => $searchFilter,
                'summary' => [],
                'activeTab' => 'graduated',
                'error' => 'Gagal memuat data lulusan: ' . $e->getMessage()
            ]);
        }
    }

    public function exportGraduatedStage($studentId, $stageCode)
    {
        $this->requireAuth();
        $role = $this->getUserRole();
        
        // Allow students to export their own final score PDF
        $isStudentAccessingOwnFinal = ($role === 'mahasiswa' && strtolower(urldecode($stageCode)) === 'final');
        
        // If not a student accessing their own final score, require admin access
        if (!$isStudentAccessingOwnFinal) {
            $this->ensureAccess();
        }

        $allowedStages = ['sempro', 'semhas', 'pra-ujian', 'ujian', 'final'];
        $stageCode = strtolower(urldecode($stageCode));
        if (!in_array($stageCode, $allowedStages, true)) {
            $this->setFlash('error', 'Tahap tidak dikenali untuk ekspor PDF.');
            $this->redirect('/reports/graduated');
            return;
        }

        $studentId = (int) $studentId;
        if ($studentId <= 0) {
            $this->setFlash('error', 'Mahasiswa tidak ditemukan.');
            $this->redirect('/reports/graduated');
            return;
        }

        // If student is accessing their own final score, verify ownership
        if ($isStudentAccessingOwnFinal) {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $this->setFlash('error', 'Sesi tidak valid.');
                $this->redirect('/login');
                return;
            }
            
            $database = new Database();
            $db = $database->getConnection();
            $stmt = $db->prepare("SELECT id FROM students WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$student || (int) $student['id'] !== $studentId) {
                $this->setFlash('error', 'Anda tidak memiliki akses ke data ini.');
                $this->redirect('/timeline');
                return;
            }
        }

        $evaluatorId = null;
        if (isset($_GET['evaluator']) && $_GET['evaluator'] !== '') {
            $candidate = (int) $_GET['evaluator'];
            if ($candidate > 0) {
                $evaluatorId = $candidate;
            }
        }

        try {
            if ($stageCode === 'final') {
                $data = $this->reportService->getFinalScoreData($studentId);
                PdfExporter::outputFinalThesisScore($data['student'], $data['finalData'], $data['chairperson'] ?? null);
                return;
            }

            // Note: EvaluationExportService is still used for individual stage exports
            // as it might contain specific logic for single evaluation export shared with other controllers.
            // If we wanted to move strict report logic, we could use ReportService, but keeping existing service for now.
            $data = EvaluationExportService::prepare($studentId, $stageCode, $evaluatorId);
            PdfExporter::outputStageEvaluation($data['student'], $stageCode, $data['evaluations'], $data['components'], $data['schedule']);
            return;
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Gagal menyiapkan PDF: ' . $e->getMessage());
            $this->redirect('/reports/graduated');
        }
    }

    /**
     * Display historical workload report for lecturers
     * Includes both active and graduated students within a specified period
     */
    public function historicalWorkload()
    {
        $this->ensureAccess();

        // Get filter parameters
        $tahunMulai = isset($_GET['tahun_mulai']) && $_GET['tahun_mulai'] !== '' ? (int) $_GET['tahun_mulai'] : null;
        $tahunSelesai = isset($_GET['tahun_selesai']) && $_GET['tahun_selesai'] !== '' ? (int) $_GET['tahun_selesai'] : null;
        $filterBy = $_GET['filter_by'] ?? 'angkatan';
        $roleType = $_GET['role_type'] ?? 'all';
        $lecturerSearch = trim($_GET['lecturer_search'] ?? '');

        // Default: 5 years period
        if (!$tahunMulai || !$tahunSelesai) {
            $currentYear = (int) date('Y');
            $tahunMulai = $currentYear - 5;
            $tahunSelesai = $currentYear;
        }

        // Validate filter by option
        if (!in_array($filterBy, ['tahun_lulus', 'angkatan'])) {
            $filterBy = 'angkatan';
        }

        // Validate role type option
        if (!in_array($roleType, ['all', 'pembimbing', 'penguji'])) {
            $roleType = 'all';
        }

        $data = $this->reportService->getHistoricalWorkloadData(
            $tahunMulai,
            $tahunSelesai,
            $filterBy,
            $roleType,
            $lecturerSearch
        );

        $this->render('reports/historical_workload', array_merge($data, [
            'activeTab' => 'historical_workload',
            'selectedTahunMulai' => $tahunMulai,
            'selectedTahunSelesai' => $tahunSelesai,
            'selectedFilterBy' => $filterBy,
            'selectedRoleType' => $roleType,
            'currentSearch' => $lecturerSearch
        ]));
    }

    /**
     * AJAX endpoint to fetch student details for a specific lecturer and role
     */
    public function historicalWorkloadDetails()
    {
        $this->ensureAccess();

        header('Content-Type: application/json');

        $lecturerId = isset($_GET['lecturer_id']) ? (int) $_GET['lecturer_id'] : 0;
        $roleType = $_GET['role_type'] ?? '';
        $filterBy = $_GET['filter_by'] ?? 'angkatan';
        $tahunMulai = isset($_GET['tahun_mulai']) ? (int) $_GET['tahun_mulai'] : 0;
        $tahunSelesai = isset($_GET['tahun_selesai']) ? (int) $_GET['tahun_selesai'] : 0;

        // Validate inputs
        if (!$lecturerId || !in_array($filterBy, ['tahun_lulus', 'angkatan']) ||
            !$tahunMulai || !$tahunSelesai) {
            echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
            return;
        }

        // Validate role_type - allow 'all', 'pembimbing', or 'penguji'
        if ($roleType !== 'all' && !in_array($roleType, ['pembimbing', 'penguji'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid role_type parameter']);
            return;
        }

        try {
            $students = [];
            
            // If role_type is 'all', fetch both pembimbing and penguji
            if ($roleType === 'all') {
                $pembimbingStudents = $this->reportService->getHistoricalWorkloadStudentDetails(
                    $lecturerId,
                    'pembimbing',
                    $filterBy,
                    $tahunMulai,
                    $tahunSelesai
                );
                
                $pengujiStudents = $this->reportService->getHistoricalWorkloadStudentDetails(
                    $lecturerId,
                    'penguji',
                    $filterBy,
                    $tahunMulai,
                    $tahunSelesai
                );
                
                // Merge both arrays
                $students = array_merge($pembimbingStudents, $pengujiStudents);
            } else {
                // Single role type
                $students = $this->reportService->getHistoricalWorkloadStudentDetails(
                    $lecturerId,
                    $roleType,
                    $filterBy,
                    $tahunMulai,
                    $tahunSelesai
                );
            }

            echo json_encode(['success' => true, 'students' => $students]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * AJAX endpoint for server-side DataTables processing - Tracking report
     */
    public function trackingData()
    {
        ob_start();
        try {
            $this->ensureAccess();
            $request = $_GET;
            $angkatanFilter = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
            // Use student_search parameter from form (not DataTables' built-in search)
            $searchFilter = isset($_GET['student_search']) ? trim($_GET['student_search']) : '';
            $result = $this->reportService->getTrackingDataServerSide($request, $angkatanFilter, $searchFilter);
            ob_end_clean();
            header('Content-Type: application/json');
            echo $result;
        } catch (Exception $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'draw' => isset($request['draw']) ? (int)$request['draw'] : 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        exit;
    }

    /**
     * AJAX endpoint for server-side DataTables processing - Graduated report
     */
    public function graduatedData()
    {
        ob_start();
        try {
            $this->ensureAccess();
            $request = $_GET;
            $angkatanFilter = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
            // Use student_search parameter from form (not DataTables' built-in search)
            $searchFilter = isset($_GET['student_search']) ? trim($_GET['student_search']) : '';
            $result = $this->reportService->getGraduatedDataServerSide($request, $angkatanFilter, $searchFilter);
            ob_end_clean();
            header('Content-Type: application/json');
            echo $result;
        } catch (Exception $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'draw' => isset($request['draw']) ? (int)$request['draw'] : 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        exit;
    }

    /**
     * AJAX endpoint for server-side DataTables processing - Bypass report
     */
    public function bypassData()
    {
        ob_start();
        try {
            $this->ensureAccess();
            $request = $_GET;
            $selectedStage = isset($_GET['stage']) && $_GET['stage'] !== '' ? $_GET['stage'] : null;
            $selectedAngkatan = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
            // Use student_search parameter from form (not DataTables' built-in search)
            $searchFilter = isset($_GET['student_search']) ? trim($_GET['student_search']) : '';
            $result = $this->reportService->getBypassDataServerSide($request, $selectedStage, $selectedAngkatan, $searchFilter);
            ob_end_clean();
            header('Content-Type: application/json');
            echo $result;
        } catch (Exception $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'draw' => isset($request['draw']) ? (int)$request['draw'] : 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        exit;
    }

    /**
     * AJAX endpoint for server-side DataTables processing - Workload report
     */
    public function workloadData()
    {
        ob_start();
        try {
            $this->ensureAccess();
            $request = $_GET;
            $angkatanFilter = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
            // Use lecturer_search parameter from form (not DataTables' built-in search)
            $lecturerSearch = isset($_GET['lecturer_search']) ? trim($_GET['lecturer_search']) : '';
            $result = $this->reportService->getWorkloadDataServerSide($request, $angkatanFilter, $lecturerSearch);
            ob_end_clean();
            header('Content-Type: application/json');
            echo $result;
        } catch (Exception $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'draw' => isset($request['draw']) ? (int)$request['draw'] : 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        exit;
    }

    /**
     * AJAX endpoint for server-side DataTables processing - SLA report
     */
    public function slaData()
    {
        ob_start();
        try {
            $this->ensureSuperadminAccess();
            $request = $_GET;
            $selectedStage = isset($_GET['stage']) && $_GET['stage'] !== '' ? $_GET['stage'] : null;
            $selectedAngkatan = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
            $statusFilter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
            // Use student_search parameter from form (not DataTables' built-in search)
            $searchFilter = isset($_GET['student_search']) ? trim($_GET['student_search']) : '';
            $result = $this->reportService->getSlaDataServerSide($request, $selectedStage, $selectedAngkatan, $searchFilter, $statusFilter);
            ob_end_clean();
            header('Content-Type: application/json');
            echo $result;
        } catch (Exception $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'draw' => isset($request['draw']) ? (int)$request['draw'] : 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        exit;
    }

    /**
     * AJAX endpoint for server-side DataTables processing - Study Period report
     */
    public function studyPeriodData()
    {
        // Define log file path
        $logFile = __DIR__ . '/../../storage/debug_log.txt';
        
        // Helper function for logging
        $log = function($message) use ($logFile) {
            $timestamp = date('Y-m-d H:i:s');
            @file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
        };
        
        // Register shutdown function to catch fatal errors
        register_shutdown_function(function() use ($log) {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                $log("FATAL ERROR: {$error['message']} in {$error['file']}:{$error['line']}");
            }
        });
        
        // Enable error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        ini_set('error_log', __DIR__ . '/../../storage/php_errors.log');
        
        // Log that we're here
        $log("studyPeriodData called");
        $log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
        $log("GET params: " . json_encode($_GET));
        
        try {
            $log("Before ensureAccess");
            $this->ensureAccess();
            $log("After ensureAccess");
            
            // Check if reportService exists
            $log("Checking reportService...");
            if ($this->reportService === null) {
                $log("ERROR: reportService is NULL!");
                throw new Exception('reportService is not initialized');
            }
            $log("reportService exists");
            
            $request = $_GET;
            $log("After \$request = \$_GET");
            
            $angkatanFilter = isset($_GET['angkatan']) && $_GET['angkatan'] !== '' ? (int) $_GET['angkatan'] : null;
            $log("angkatanFilter: " . var_export($angkatanFilter, true));
            
            // Use student_search parameter from form (not DataTables' built-in search)
            $searchFilter = isset($_GET['student_search']) ? trim($_GET['student_search']) : '';
            $log("searchFilter: " . var_export($searchFilter, true));
            
            $statusFilter = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null;
            $log("statusFilter: " . var_export($statusFilter, true));

            $log("Before calling getStudyPeriodDataServerSide");
            
            $result = $this->reportService->getStudyPeriodDataServerSide($request, $angkatanFilter, $searchFilter, $statusFilter);
            
            $log("After getStudyPeriodDataServerSide - result length: " . strlen($result));
            
            header('Content-Type: application/json');
            echo $result;
            $log("Response sent successfully");
        } catch (Throwable $e) {
            $log("Throwable caught: " . $e->getMessage());
            $log("File: " . $e->getFile() . " Line: " . $e->getLine());
            $log("Stack trace:\n" . $e->getTraceAsString());
            
            header('Content-Type: application/json');
            echo json_encode([
                'draw' => isset($request['draw']) ? (int)$request['draw'] : 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'debug' => 'Check debug_log.txt for details'
            ]);
        }
        exit;
    }

    /**
     * AJAX endpoint for server-side DataTables processing - Historical Workload report
     */
    public function historicalWorkloadData()
    {
        ob_start();
        try {
            $this->ensureAccess();
            $request = $_GET;
            $tahunMulai = isset($_GET['tahun_mulai']) && $_GET['tahun_mulai'] !== '' ? (int) $_GET['tahun_mulai'] : null;
            $tahunSelesai = isset($_GET['tahun_selesai']) && $_GET['tahun_selesai'] !== '' ? (int) $_GET['tahun_selesai'] : null;
            $filterBy = $_GET['filter_by'] ?? 'angkatan';
            $roleType = $_GET['role_type'] ?? 'all';
            // Use lecturer_search parameter from form (not DataTables' built-in search)
            $lecturerSearch = isset($_GET['lecturer_search']) ? trim($_GET['lecturer_search']) : '';
            $result = $this->reportService->getHistoricalWorkloadDataServerSide($request, $tahunMulai, $tahunSelesai, $filterBy, $roleType, $lecturerSearch);
            ob_end_clean();
            header('Content-Type: application/json');
            echo $result;
        } catch (Exception $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'draw' => isset($request['draw']) ? (int)$request['draw'] : 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
        exit;
    }
}
