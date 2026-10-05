<?php
/**
 * Script untuk membersihkan data yatim piatu (orphaned data)
 * Data yatim piatu adalah data evaluations/events yang tidak memiliki title terkait
 */

require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/helpers/Csrf.php';

// Load environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

$database = new Database();
$db = $database->getConnection();

echo "=== Membersihkan Data Yatim Piatu ===\n\n";

// 1. Cari students yang tidak memiliki title
echo "1. Mencari mahasiswa yang tidak memiliki title...\n";
$stmt = $db->query("
    SELECT s.id, s.nim, s.name 
    FROM students s 
    LEFT JOIN titles t ON s.id = t.student_id 
    WHERE t.id IS NULL
    ORDER BY s.nim
");
$studentsWithoutTitle = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "   Ditemukan " . count($studentsWithoutTitle) . " mahasiswa tanpa title:\n";
foreach ($studentsWithoutTitle as $student) {
    echo "   - {$student['nim']}: {$student['name']}\n";
}

if (empty($studentsWithoutTitle)) {
    echo "\nTidak ada data yatim piatu yang perlu dibersihkan.\n";
    exit(0);
}

echo "\n2. Menampilkan data yang akan dihapus...\n\n";

$totalEvaluations = 0;
$totalEvents = 0;
$totalAssignments = 0;

foreach ($studentsWithoutTitle as $student) {
    $studentId = $student['id'];
    
    // Cek evaluations
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM evaluations WHERE student_id = :student_id");
    $stmt->execute([':student_id' => $studentId]);
    $evalCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Cek events
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM events WHERE student_id = :student_id");
    $stmt->execute([':student_id' => $studentId]);
    $eventCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Cek assignments
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM assignments WHERE student_id = :student_id");
    $stmt->execute([':student_id' => $studentId]);
    $assignCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($evalCount > 0 || $eventCount > 0 || $assignCount > 0) {
        echo "Mahasiswa: {$student['nim']} - {$student['name']}\n";
        echo "  Evaluations: $evalCount\n";
        echo "  Events: $eventCount\n";
        echo "  Assignments: $assignCount\n\n";
        
        $totalEvaluations += $evalCount;
        $totalEvents += $eventCount;
        $totalAssignments += $assignCount;
    }
}

echo "\nTotal yang akan dihapus:\n";
echo "  Evaluations: $totalEvaluations\n";
echo "  Events: $totalEvents\n";
echo "  Assignments: $totalAssignments\n\n";

// Konfirmasi
echo "Apakah Anda yakin ingin menghapus data ini? (ketik 'YA' untuk melanjutkan): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) !== 'YA') {
    echo "\nDibatalkan.\n";
    exit(0);
}

echo "\n3. Menghapus data...\n";

try {
    $db->beginTransaction();
    
    foreach ($studentsWithoutTitle as $student) {
        $studentId = $student['id'];
        
        // Hapus evaluations
        $stmt = $db->prepare("DELETE FROM evaluations WHERE student_id = :student_id");
        $stmt->execute([':student_id' => $studentId]);
        $deletedEval = $stmt->rowCount();
        
        // Hapus events
        $stmt = $db->prepare("DELETE FROM events WHERE student_id = :student_id");
        $stmt->execute([':student_id' => $studentId]);
        $deletedEvent = $stmt->rowCount();
        
        // Hapus assignments
        $stmt = $db->prepare("DELETE FROM assignments WHERE student_id = :student_id");
        $stmt->execute([':student_id' => $studentId]);
        $deletedAssign = $stmt->rowCount();
        
        // Hapus assignment_history
        $stmt = $db->prepare("DELETE FROM assignment_history WHERE student_id = :student_id");
        $stmt->execute([':student_id' => $studentId]);
        
        if ($deletedEval > 0 || $deletedEvent > 0 || $deletedAssign > 0) {
            echo "  {$student['nim']}: $deletedEval evaluations, $deletedEvent events, $deletedAssign assignments dihapus\n";
        }
    }
    
    $db->commit();
    echo "\n✓ Selesai! Data yatim piatu berhasil dibersihkan.\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "\n✗ Gagal: " . $e->getMessage() . "\n";
    exit(1);
}
