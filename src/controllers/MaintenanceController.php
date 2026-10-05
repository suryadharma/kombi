<?php

/**
 * MaintenanceController
 *
 * Controller for maintenance tasks like cleaning up test data
 */
class MaintenanceController extends BaseController
{
    public function __construct()
    {
        // Ensure user is logged in
        $this->requireAuth();
        
        // Only allow superadmin to access maintenance functions
        if ($this->getUserRole() !== 'superadmin') {
            http_response_code(403);
            die("Access Denied. Maintenance functions are restricted to superadmin users only.");
        }
    }

    /**
     * Cleanup test data (students with NIM containing '2222' or 'test')
     */
    public function cleanupTestData()
    {
        $db = $this->getDb();
        $deletedCount = 0;
        $errors = [];

        echo "<!DOCTYPE html><html><head><title>Pembersihan Data Test</title>";
        echo "<meta charset='UTF-8'>";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
            .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h1 { color: #d9534f; border-bottom: 2px solid #d9534f; padding-bottom: 10px; }
            h2 { color: #333; margin-top: 30px; }
            .warning { background-color: #fff3cd; border: 1px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 5px; }
            .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 20px; margin: 20px 0; border-radius: 5px; color: #155724; }
            .error { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; margin: 20px 0; border-radius: 5px; color: #721c24; }
            table { border-collapse: collapse; width: 100%; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
            th { background-color: #d9534f; color: white; font-weight: bold; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            .btn { padding: 12px 24px; margin-right: 10px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; }
            .btn-danger { background-color: #dc3545; color: white; }
            .btn-danger:hover { background-color: #c82333; }
            .btn-secondary { background-color: #6c757d; color: white; }
            .btn-secondary:hover { background-color: #5a6268; }
            .summary { background-color: #e7f3ff; padding: 20px; border-radius: 5px; margin: 20px 0; }
            .summary h3 { margin-top: 0; color: #004085; }
            ul { margin: 10px 0; padding-left: 20px; }
            li { margin: 5px 0; }
        </style>";
        echo "</head><body><div class='container'>";

        try {
            // Tampilkan konfirmasi
            if (!isset($_POST['confirmed'])) {
                echo "<h1>🧹 Pembersihan Data Test</h1>";
                echo "<div class='warning'>";
                echo "<h2>⚠️ PERINGATAN!</h2>";
                echo "<p>Script ini akan <strong>MENGHAPUS</strong> data test berikut secara permanen:</p>";
                echo "<ul>";
                echo "<li>Mahasiswa dengan NIM yang mengandung '2222' atau 'test'</li>";
                echo "<li>Semua events yang terkait dengan mahasiswa test tersebut</li>";
                echo "<li>Assignments untuk mahasiswa test tersebut (jika ada)</li>";
                echo "<li>Users yang terkait dengan mahasiswa test (jika ada)</li>";
                echo "</ul>";
                echo "<p><strong>Pastikan Anda yakin!</strong> Data yang dihapus tidak dapat dikembalikan.</p>";
                
                // Tampilkan data yang akan dihapus
                echo "<h2>Data yang Akan Dihapus:</h2>";
                
                // Cari mahasiswa test
                $query = "SELECT id, nim, name, angkatan, status FROM students 
                          WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%'";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $testStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($testStudents)) {
                    echo "<h3>Mahasiswa Test (" . count($testStudents) . " data):</h3>";
                    echo "<table>";
                    echo "<tr><th>ID</th><th>NIM</th><th>Nama</th><th>Angkatan</th><th>Status</th></tr>";
                    foreach ($testStudents as $student) {
                        echo "<tr>";
                        echo "<td>{$student['id']}</td>";
                        echo "<td><strong>{$student['nim']}</strong></td>";
                        echo "<td>{$student['name']}</td>";
                        echo "<td>{$student['angkatan']}</td>";
                        echo "<td>{$student['status']}</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    
                    // Cari events untuk mahasiswa test
                    $studentIds = array_column($testStudents, 'id');
                    $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
                    $eventQuery = "SELECT id, student_id, event_type, event_date, venue 
                                  FROM events WHERE student_id IN ($placeholders)";
                    $eventStmt = $db->prepare($eventQuery);
                    $eventStmt->execute($studentIds);
                    $testEvents = $eventStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($testEvents)) {
                        echo "<h3>Events untuk Mahasiswa Test (" . count($testEvents) . " data):</h3>";
                        echo "<table>";
                        echo "<tr><th>ID</th><th>Student ID</th><th>Tipe Event</th><th>Tanggal</th><th>Venue</th></tr>";
                        foreach ($testEvents as $event) {
                            echo "<tr>";
                            echo "<td>{$event['id']}</td>";
                            echo "<td>{$event['student_id']}</td>";
                            echo "<td>{$event['event_type']}</td>";
                            echo "<td>{$event['event_date']}</td>";
                            echo "<td>{$event['venue']}</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    }
                    
                    // Cari assignments untuk mahasiswa test
                    $assignQuery = "SELECT a.id, a.student_id, a.lecturer_id, a.role, u.name as lecturer_name
                                    FROM assignments a
                                    LEFT JOIN users u ON u.id = a.lecturer_id
                                    WHERE a.student_id IN ($placeholders)";
                    $assignStmt = $db->prepare($assignQuery);
                    $assignStmt->execute($studentIds);
                    $testAssignments = $assignStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (!empty($testAssignments)) {
                        echo "<h3>Assignments untuk Mahasiswa Test (" . count($testAssignments) . " data):</h3>";
                        echo "<table>";
                        echo "<tr><th>ID</th><th>Student ID</th><th>Dosen</th><th>Role</th></tr>";
                        foreach ($testAssignments as $assign) {
                            echo "<tr>";
                            echo "<td>{$assign['id']}</td>";
                            echo "<td>{$assign['student_id']}</td>";
                            echo "<td>" . ($assign['lecturer_name'] ?? 'N/A') . "</td>";
                            echo "<td>{$assign['role']}</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    }
                } else {
                    echo "<p class='success'>✓ Tidak ada data test ditemukan!</p>";
                }
                
                echo "<form method='POST' style='margin-top: 30px;'>";
                echo "<input type='hidden' name='confirmed' value='yes'>";
                echo "<button type='submit' class='btn btn-danger'>🗑️ Ya, Hapus Semua Data Test</button> ";
                echo "<a href='/dashboard' class='btn btn-secondary'>❌ Batal</a>";
                echo "</form>";
                echo "</div>";
                echo "</div></body></html>";
                exit;
            }
            
            echo "<h1>🧹 Pembersihan Data Test</h1>";
            echo "<h2>Mulai Membersihkan Data Test...</h2>";
            
            // 1. Cari semua mahasiswa test
            echo "<h3>1. Mencari Mahasiswa Test</h3>";
            $query = "SELECT id, nim, name, angkatan, status FROM students 
                      WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%'";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $testStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($testStudents)) {
                echo "<div class='success'>✓ Tidak ada mahasiswa test ditemukan!</div>";
            } else {
                echo "<p>Ditemukan " . count($testStudents) . " mahasiswa test.</p>";
                $studentIds = array_column($testStudents, 'id');
                
                // 2. Hapus events untuk mahasiswa test
                echo "<h3>2. Menghapus Events untuk Mahasiswa Test</h3>";
                $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
                $eventQuery = "SELECT COUNT(*) as count FROM events WHERE student_id IN ($placeholders)";
                $eventStmt = $db->prepare($eventQuery);
                $eventStmt->execute($studentIds);
                $eventCount = $eventStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($eventCount > 0) {
                    $deleteEventQuery = "DELETE FROM events WHERE student_id IN ($placeholders)";
                    $deleteEventStmt = $db->prepare($deleteEventQuery);
                    $deleteEventStmt->execute($studentIds);
                    $deletedEvents = $deleteEventStmt->rowCount();
                    echo "<p class='success'>✓ Events berhasil dihapus: " . $deletedEvents . " data</p>";
                } else {
                    echo "<p>Tidak ada events untuk mahasiswa test.</p>";
                }
                
                // 3. Hapus assignments untuk mahasiswa test
                echo "<h3>3. Menghapus Assignments untuk Mahasiswa Test</h3>";
                $assignQuery = "SELECT COUNT(*) as count FROM assignments WHERE student_id IN ($placeholders)";
                $assignStmt = $db->prepare($assignQuery);
                $assignStmt->execute($studentIds);
                $assignCount = $assignStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($assignCount > 0) {
                    $deleteAssignQuery = "DELETE FROM assignments WHERE student_id IN ($placeholders)";
                    $deleteAssignStmt = $db->prepare($deleteAssignQuery);
                    $deleteAssignStmt->execute($studentIds);
                    $deletedAssignments = $deleteAssignStmt->rowCount();
                    echo "<p class='success'>✓ Assignments berhasil dihapus: " . $deletedAssignments . " data</p>";
                } else {
                    echo "<p>Tidak ada assignments untuk mahasiswa test.</p>";
                }
                
                // 4. Hapus users yang terkait dengan mahasiswa test
                echo "<h3>4. Menghapus Users yang Terkait dengan Mahasiswa Test</h3>";
                $userQuery = "SELECT COUNT(*) as count FROM users WHERE username IN (SELECT nim FROM students WHERE id IN ($placeholders))";
                $userStmt = $db->prepare($userQuery);
                $userStmt->execute($studentIds);
                $userCount = $userStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                if ($userCount > 0) {
                    $deleteUserQuery = "DELETE FROM users WHERE username IN (SELECT nim FROM students WHERE id IN ($placeholders))";
                    $deleteUserStmt = $db->prepare($deleteUserQuery);
                    $deleteUserStmt->execute($studentIds);
                    $deletedUsers = $deleteUserStmt->rowCount();
                    echo "<p class='success'>✓ Users berhasil dihapus: " . $deletedUsers . " data</p>";
                } else {
                    echo "<p>Tidak ada users untuk mahasiswa test.</p>";
                }
                
                // 5. Hapus mahasiswa test
                echo "<h3>5. Menghapus Data Mahasiswa Test</h3>";
                $deleteStudentQuery = "DELETE FROM students WHERE id IN ($placeholders)";
                $deleteStudentStmt = $db->prepare($deleteStudentQuery);
                $deleteStudentStmt->execute($studentIds);
                $deletedStudents = $deleteStudentStmt->rowCount();
                echo "<p class='success'>✓ Mahasiswa test berhasil dihapus: " . $deletedStudents . " data</p>";
                
                $deletedCount = $deletedStudents + $deletedEvents + $deletedAssignments + $deletedUsers;
            }
            
            // Verifikasi - cek apakah masih ada data test
            echo "<h3>6. Verifikasi Pembersihan</h3>";
            $verifyQuery = "SELECT COUNT(*) as count FROM students 
                           WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%'";
            $verifyStmt = $db->prepare($verifyQuery);
            $verifyStmt->execute();
            $remainingCount = $verifyStmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($remainingCount == 0) {
                echo "<div class='success'>";
                echo "<h3>✅ Pembersihan Selesai!</h3>";
                echo "<p>Semua data test telah berhasil dihapus.</p>";
                echo "</div>";
            } else {
                echo "<div class='error'>";
                echo "<h3>⚠️ Masih Ada Data Tersisa!</h3>";
                echo "<p>Masih terdapat " . $remainingCount . " data mahasiswa test yang belum dihapus.</p>";
                echo "</div>";
            }
            
            // Summary
            echo "<div class='summary'>";
            echo "<h3>📊 Ringkasan Pembersihan</h3>";
            echo "<ul>";
            echo "<li>Total data yang dihapus: <strong>" . $deletedCount . "</strong></li>";
            if (!empty($testStudents)) {
                echo "<li>Mahasiswa test: <strong>" . $deletedStudents . "</strong></li>";
                echo "<li>Events: <strong>" . ($deletedEvents ?? 0) . "</strong></li>";
                echo "<li>Assignments: <strong>" . ($deletedAssignments ?? 0) . "</strong></li>";
                echo "<li>Users: <strong>" . ($deletedUsers ?? 0) . "</strong></li>";
            }
            echo "</ul>";
            echo "</div>";
            
            echo "<p><a href='/dashboard' class='btn btn-secondary'>🏠 Kembali ke Dashboard</a></p>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Error Database!</h3>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Error!</h3>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        
        echo "</div></body></html>";
        exit;
    }

    /**
     * Get database connection
     */
    private function getDb(): PDO
    {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        return $database->getConnection();
    }
}
