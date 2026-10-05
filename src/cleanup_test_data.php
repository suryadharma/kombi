<?php
/**
 * Script untuk Membersihkan Data Test (NIM 222222222222)
 * 
 * Usage: Buka http://localhost:9201/cleanup_test_data.php
 * 
 * WARNING: Script ini akan MENGHAPUS data secara permanen!
 * Pastikan Anda yakin sebelum menjalankan!
 */

require_once __DIR__ . '/config/database.php';

$db = null;
$database = null;
$deletedCount = 0;
$errors = [];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h1>Pembersihan Data Test</h1>";
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #d9534f; }
        .warning { background-color: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 20px 0; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #d9534f; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .btn { padding: 10px 20px; margin-right: 10px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-secondary { background-color: #6c757d; color: white; }
        .btn:hover { opacity: 0.8; }
    </style>";
    
    // Tampilkan konfirmasi
    if (!isset($_POST['confirmed'])) {
        echo "<div class='warning'>";
        echo "<h2>⚠️ PERINGATAN!</h2>";
        echo "<p>Script ini akan <strong>MENGHAPUS</strong> data test berikut secara permanen:</p>";
        echo "<ul>";
        echo "<li>Mahasiswa dengan NIM 222222222222 atau NIM yang mengandung '2222'</li>";
        echo "<li>Semua events yang terkait dengan mahasiswa test tersebut</li>";
        echo "<li>Assignments untuk mahasiswa test tersebut (jika ada)</li>";
        echo "</ul>";
        echo "<p><strong>Pastikan Anda yakin!</strong> Data yang dihapus tidak dapat dikembalikan.</p>";
        echo "<form method='POST' style='margin-top: 20px;'>";
        echo "<input type='hidden' name='confirmed' value='yes'>";
        echo "<button type='submit' class='btn btn-danger'>Ya, Hapus Semua Data Test</button> ";
        echo "<a href='/dashboard' class='btn btn-secondary'>Batal</a>";
        echo "</form>";
        echo "</div>";
        exit;
    }
    
    echo "<h2>Mulai Membersihkan Data Test...</h2>";
    
    // 1. Cari semua mahasiswa test
    echo "<h3>1. Mencari Mahasiswa Test</h3>";
    $query = "SELECT id, nim, name, angkatan, status FROM students 
              WHERE nim LIKE '%2222%' OR nim LIKE '%test%' OR nim LIKE '%TEST%'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $testStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($testStudents)) {
        echo "<div class='success'>Tidak ada mahasiswa test ditemukan!</div>";
    } else {
        echo "<p>Ditemukan " . count($testStudents) . " mahasiswa test:</p>";
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
        
        // Hapus mahasiswa test
        $studentIds = array_column($testStudents, 'id');
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $deleteQuery = "DELETE FROM students WHERE id IN ($placeholders)";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->execute($studentIds);
        $deletedCount += $deleteStmt->rowCount();
        echo "<p class='success'>✓ Mahasiswa test berhasil dihapus: " . $deleteStmt->rowCount() . " data</p>";
    }
    
    // 2. Hapus events untuk mahasiswa test
    if (!empty($testStudents)) {
        echo "<h3>2. Menghapus Events untuk Mahasiswa Test</h3>";
        $studentIds = array_column($testStudents, 'id');
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        
        // Cek events dulu
        $checkQuery = "SELECT id, student_id, type, scheduled_date, scheduled_time, room, status 
                       FROM events WHERE student_id IN ($placeholders)";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute($studentIds);
        $testEvents = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($testEvents)) {
            echo "<p>Tidak ada events untuk mahasiswa test.</p>";
        } else {
            echo "<p>Ditemukan " . count($testEvents) . " events untuk mahasiswa test:</p>";
            echo "<table>";
            echo "<tr><th>Event ID</th><th>Student ID</th><th>Tipe</th><th>Tanggal</th><th>Waktu</th><th>Ruangan</th><th>Status</th></tr>";
            foreach ($testEvents as $event) {
                echo "<tr>";
                echo "<td>{$event['id']}</td>";
                echo "<td>{$event['student_id']}</td>";
                echo "<td>{$event['type']}</td>";
                echo "<td>{$event['scheduled_date']}</td>";
                echo "<td>{$event['scheduled_time']}</td>";
                echo "<td>{$event['room']}</td>";
                echo "<td>{$event['status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Hapus events
            $deleteQuery = "DELETE FROM events WHERE student_id IN ($placeholders)";
            $deleteStmt = $db->prepare($deleteQuery);
            $deleteStmt->execute($studentIds);
            $deletedCount += $deleteStmt->rowCount();
            echo "<p class='success'>✓ Events berhasil dihapus: " . $deleteStmt->rowCount() . " data</p>";
        }
    }
    
    // 3. Hapus assignments untuk mahasiswa test
    if (!empty($testStudents)) {
        echo "<h3>3. Menghapus Assignments untuk Mahasiswa Test</h3>";
        $studentIds = array_column($testStudents, 'id');
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        
        // Cek assignments dulu
        $checkQuery = "SELECT id, student_id, role, lecturer_id 
                       FROM assignments WHERE student_id IN ($placeholders)";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute($studentIds);
        $testAssignments = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($testAssignments)) {
            echo "<p>Tidak ada assignments untuk mahasiswa test.</p>";
        } else {
            echo "<p>Ditemukan " . count($testAssignments) . " assignments untuk mahasiswa test:</p>";
            echo "<table>";
            echo "<tr><th>Assignment ID</th><th>Student ID</th><th>Role</th><th>Lecturer ID</th></tr>";
            foreach ($testAssignments as $assignment) {
                echo "<tr>";
                echo "<td>{$assignment['id']}</td>";
                echo "<td>{$assignment['student_id']}</td>";
                echo "<td>{$assignment['role']}</td>";
                echo "<td>{$assignment['lecturer_id']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Hapus assignments
            $deleteQuery = "DELETE FROM assignments WHERE student_id IN ($placeholders)";
            $deleteStmt = $db->prepare($deleteQuery);
            $deleteStmt->execute($studentIds);
            $deletedCount += $deleteStmt->rowCount();
            echo "<p class='success'>✓ Assignments berhasil dihapus: " . $deleteStmt->rowCount() . " data</p>";
        }
    }
    
    // 4. Cek apakah masih ada data test
    echo "<h3>4. Pengecekan Ulang</h3>";
    $query = "SELECT COUNT(*) as count FROM students WHERE nim LIKE '%2222%' OR nim LIKE '%test%'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $remainingCount = $stmt->fetchColumn();
    
    if ($remainingCount > 0) {
        echo "<p class='error'>Masih ada $remainingCount data test yang tersisa!</p>";
    } else {
        echo "<p class='success'>✓ Semua data test berhasil dibersihkan!</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>Total data yang dihapus: $deletedCount</strong></p>";
    echo "<p><a href='/events/create' class='btn btn-primary'>Kembali ke Buat Event</a> ";
    echo "<a href='/dashboard' class='btn btn-secondary'>Kembali ke Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>Error!</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
