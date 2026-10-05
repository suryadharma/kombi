<?php
/**
 * Diagnostic Script untuk Mengecek Data Assignments dan Event
 * 
 * Usage: Buka http://localhost:9201/debug_event_assignments.php
 */

require_once __DIR__ . '/config/database.php';

$db = null;
$database = null;

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h1>Diagnostic: Assignments & Event Data</h1>";
    echo "<style>
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        h2 { margin-top: 30px; border-bottom: 2px solid #333; }
    </style>";
    
    // 1. Cek semua assignments
    echo "<h2>1. Semua Data Assignments</h2>";
    $query = "SELECT a.id, a.student_id, s.nim, s.name as student_name, 
                     a.role, a.lecturer_id, l.name as lecturer_name, l.nip as lecturer_nip
              FROM assignments a
              JOIN students s ON a.student_id = s.id
              LEFT JOIN lecturers l ON a.lecturer_id = l.id
              ORDER BY s.nim, a.role";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($assignments)) {
        echo "<p class='error'>Tidak ada data assignments!</p>";
    } else {
        echo "<table>";
        echo "<tr><th>Student ID</th><th>NIM</th><th>Nama Mahasiswa</th><th>Role</th><th>Lecturer ID</th><th>Nama Dosen</th><th>NIP Dosen</th></tr>";
        foreach ($assignments as $row) {
            $lecturerClass = empty($row['lecturer_name']) ? 'error' : '';
            echo "<tr>";
            echo "<td>{$row['student_id']}</td>";
            echo "<td>{$row['nim']}</td>";
            echo "<td>{$row['student_name']}</td>";
            echo "<td>{$row['role']}</td>";
            echo "<td>{$row['lecturer_id']}</td>";
            echo "<td class='$lecturerClass'>" . (isset($row['lecturer_name']) ? $row['lecturer_name'] : 'NULL') . "</td>";
            echo "<td>" . (isset($row['lecturer_nip']) ? $row['lecturer_nip'] : 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p>Total: " . count($assignments) . " assignments</p>";
    }
    
    // 2. Cek data test (NIM 222222222222)
    echo "<h2>2. Data Test (NIM 222222222222)</h2>";
    $query = "SELECT s.id, s.nim, s.name, s.status, s.angkatan
              FROM students s
              WHERE s.nim LIKE '%2222%' OR s.nim LIKE '%test%' OR s.nim LIKE '%TEST%'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $testStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($testStudents)) {
        echo "<p class='success'>Tidak ada data test mahasiswa!</p>";
    } else {
        echo "<p class='error'>Ditemukan " . count($testStudents) . " mahasiswa test:</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>NIM</th><th>Nama</th><th>Status</th><th>Angkatan</th></tr>";
        foreach ($testStudents as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td class='error'>{$row['nim']}</td>";
            echo "<td>{$row['name']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "<td>{$row['angkatan']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Cek events untuk data test
    echo "<h2>3. Events untuk Data Test</h2>";
    if (!empty($testStudents)) {
        $testStudentIds = array_column($testStudents, 'id');
        $placeholders = implode(',', array_fill(0, count($testStudentIds), '?'));
        $query = "SELECT e.id, e.student_id, s.nim, s.name as student_name, 
                         e.type, e.scheduled_date, e.scheduled_time, e.room, e.status
                  FROM events e
                  JOIN students s ON e.student_id = s.id
                  WHERE e.student_id IN ($placeholders)
                  ORDER BY e.scheduled_date DESC, e.scheduled_time";
        $stmt = $db->prepare($query);
        $stmt->execute($testStudentIds);
        $testEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($testEvents)) {
            echo "<p class='success'>Tidak ada events untuk data test!</p>";
        } else {
            echo "<p class='error'>Ditemukan " . count($testEvents) . " events untuk data test:</p>";
            echo "<table>";
            echo "<tr><th>Event ID</th><th>Student ID</th><th>NIM</th><th>Nama</th><th>Tipe</th><th>Tanggal</th><th>Waktu</th><th>Ruangan</th><th>Status</th></tr>";
            foreach ($testEvents as $row) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['student_id']}</td>";
                echo "<td class='error'>{$row['nim']}</td>";
                echo "<td>{$row['student_name']}</td>";
                echo "<td>{$row['type']}</td>";
                echo "<td>{$row['scheduled_date']}</td>";
                echo "<td>{$row['scheduled_time']}</td>";
                echo "<td>{$row['room']}</td>";
                echo "<td>{$row['status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // 4. Cek lecturers tanpa NIP
    echo "<h2>4. Lecturers dengan Data Tidak Lengkap</h2>";
    $query = "SELECT l.id, l.name, l.nip, u.username, u.email
              FROM lecturers l
              JOIN users u ON l.user_id = u.id
              WHERE l.nip IS NULL OR l.nip = '' OR l.name IS NULL OR l.name = ''";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $incompleteLecturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($incompleteLecturers)) {
        echo "<p class='success'>Semua dosen memiliki data lengkap!</p>";
    } else {
        echo "<p class='warning'>Ditemukan " . count($incompleteLecturers) . " dosen dengan data tidak lengkap:</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nama</th><th>NIP</th><th>Username</th><th>Email</th></tr>";
        foreach ($incompleteLecturers as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>" . (isset($row['name']) ? $row['name'] : 'NULL') . "</td>";
            echo "<td>" . (isset($row['nip']) ? $row['nip'] : 'NULL') . "</td>";
            echo "<td>{$row['username']}</td>";
            echo "<td>{$row['email']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 5. Cek assignments dengan lecturer_id NULL atau tidak valid
    echo "<h2>5. Assignments dengan Lecturer ID Tidak Valid</h2>";
    $query = "SELECT a.id, a.student_id, s.nim, s.name as student_name, 
                     a.role, a.lecturer_id
              FROM assignments a
              JOIN students s ON a.student_id = s.id
              WHERE a.lecturer_id IS NULL 
                 OR a.lecturer_id = 0
                 OR NOT EXISTS (SELECT 1 FROM lecturers l WHERE l.id = a.lecturer_id)";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $invalidAssignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($invalidAssignments)) {
        echo "<p class='success'>Semua assignments memiliki lecturer_id yang valid!</p>";
    } else {
        echo "<p class='error'>Ditemukan " . count($invalidAssignments) . " assignments dengan lecturer_id tidak valid:</p>";
        echo "<table>";
        echo "<tr><th>Assignment ID</th><th>Student ID</th><th>NIM</th><th>Nama Mahasiswa</th><th>Role</th><th>Lecturer ID</th></tr>";
        foreach ($invalidAssignments as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['student_id']}</td>";
            echo "<td>{$row['nim']}</td>";
            echo "<td>{$row['student_name']}</td>";
            echo "<td>{$row['role']}</td>";
            echo "<td class='error'>" . (isset($row['lecturer_id']) ? $row['lecturer_id'] : 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 6. Cek events yang mungkin menyebabkan konflik
    echo "<h2>6. Semua Events (Terurut berdasarkan Tanggal & Waktu)</h2>";
    $query = "SELECT e.id, e.student_id, s.nim, s.name as student_name, s.angkatan,
                     e.type, e.scheduled_date, e.scheduled_time, e.room, e.status
              FROM events e
              JOIN students s ON e.student_id = s.id
              ORDER BY e.scheduled_date DESC, e.scheduled_time DESC
              LIMIT 50";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $allEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($allEvents)) {
        echo "<p>Tidak ada events!</p>";
    } else {
        echo "<p>Menampilkan 50 events terbaru:</p>";
        echo "<table>";
        echo "<tr><th>Event ID</th><th>Student ID</th><th>NIM</th><th>Nama</th><th>Angkatan</th><th>Tipe</th><th>Tanggal</th><th>Waktu</th><th>Ruangan</th><th>Status</th></tr>";
        foreach ($allEvents as $row) {
            $nimClass = (strpos($row['nim'], '2222') !== false || 
                       stripos($row['nim'], 'test') !== false) ? 'error' : '';
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['student_id']}</td>";
            echo "<td class='$nimClass'>{$row['nim']}</td>";
            echo "<td>{$row['student_name']}</td>";
            echo "<td>{$row['angkatan']}</td>";
            echo "<td>{$row['type']}</td>";
            echo "<td>{$row['scheduled_date']}</td>";
            echo "<td>{$row['scheduled_time']}</td>";
            echo "<td>{$row['room']}</td>";
            echo "<td>{$row['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<p><em>Selesai. Script ini hanya untuk diagnostic dan tidak akan mengubah data apapun.</em></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
