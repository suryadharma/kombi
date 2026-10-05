<?php

class StudentImportController extends BaseController {
    
    public function importForm() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can import students
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Show import form
        $this->render('students/import');
    }
    
    public function importProcess() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can import students
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['student_file']) || $_FILES['student_file']['error'] !== UPLOAD_ERR_OK) {
            $this->render('students/import', ['error' => 'File tidak ditemukan atau terjadi kesalahan saat upload.']);
            return;
        }
        
        // Check file type
        $fileType = mime_content_type($_FILES['student_file']['tmp_name']);
        if ($fileType !== 'text/csv' && $fileType !== 'text/plain') {
            $this->render('students/import', ['error' => 'Format file tidak didukung. Harap unggah file CSV.']);
            return;
        }
        
        // Process CSV file
        $handle = fopen($_FILES['student_file']['tmp_name'], 'r');
        if (!$handle) {
            $this->render('students/import', ['error' => 'Gagal membaca file.']);
            return;
        }
        
        // Skip header row
        $header = fgetcsv($handle);
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        // Prepare statements
        $checkStudentStmt = $db->prepare("SELECT id, user_id FROM students WHERE nim = ?");
        $checkUserStmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $insertStudentStmt = $db->prepare("INSERT INTO students (nim, name, angkatan, semester_masuk, status) VALUES (?, ?, ?, ?, ?)");
        $updateStudentStmt = $db->prepare("UPDATE students SET name = ?, angkatan = ?, semester_masuk = ?, status = ? WHERE nim = ?");
        $insertUserStmt = $db->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
        $updateUserStmt = $db->prepare("UPDATE users SET name = ? WHERE username = ?");
        $updateStudentUserIdStmt = $db->prepare("UPDATE students SET user_id = ? WHERE id = ?");
        
        $importedCount = 0;
        $errors = [];
        
        // Process each row
        while (($data = fgetcsv($handle)) !== false) {
            // Extract data (assuming CSV format: nim, name, angkatan, semester_masuk)
            if (count($data) < 4) {
                $errors[] = "Baris data tidak lengkap: " . implode(', ', $data);
                continue;
            }
            
            $nim = trim($data[0] ?? '');
            $name = trim($data[1] ?? '');
            $angkatan = (int)($data[2] ?? 0); // Convert to int, empty string becomes 0
            $semester_masuk = (int)($data[3] ?? 0); // Convert to int, empty string becomes 0
            $status = trim($data[4] ?? 'AKTIF');

            // Validasi NIM tidak boleh kosong
            if (empty($nim)) {
                $errors[] = "NIM tidak boleh kosong untuk data: " . implode(', ', $data);
                continue;
            }

            // Validasi nama tidak boleh kosong
            if (empty($name)) {
                $errors[] = "Nama tidak boleh kosong untuk NIM {$nim}";
                continue;
            }

            // Validasi angkatan harus angka valid
            if ($angkatan <= 0) {
                $errors[] = "Angkatan tidak valid untuk NIM {$nim}";
                continue;
            }

            // Validasi semester_masuk harus angka valid
            if ($semester_masuk <= 0) {
                $errors[] = "Semester masuk tidak valid untuk NIM {$nim}";
                continue;
            }

            // Validate status against ENUM values
            $validStatuses = ['AKTIF', 'CUTI', 'NON-AKTIF', 'LULUS'];
            if (!in_array($status, $validStatuses)) {
                $status = 'AKTIF'; // Default to AKTIF if invalid
            }
            
            try {
                // Begin transaction
                $db->beginTransaction();
                
                // Check if student already exists
                $checkStudentStmt->execute([$nim]);
                $existingStudent = $checkStudentStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingStudent) {
                    // Update existing student data
                    $updateStudentStmt->execute([$name, $angkatan, $semester_masuk, $status, $nim]);
                    $studentId = $existingStudent['id'];
                    $userId = $existingStudent['user_id'];
                    
                    // Check if user account exists
                    $checkUserStmt->execute([$nim]);
                    $existingUser = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existingUser) {
                        // Update user data if exists
                        $updateUserStmt->execute([$name, $nim]);
                    } else {
                        // Create user account if not exists
                        $defaultPassword = password_hash($nim, PASSWORD_DEFAULT);
                        $insertUserStmt->execute([$nim, $defaultPassword, $name, 'mahasiswa']);
                        $userId = $db->lastInsertId();
                        
                        // Update student with user_id
                        $updateStudentUserIdStmt->execute([$userId, $studentId]);
                    }
                } else {
                    // Check if user account exists
                    $checkUserStmt->execute([$nim]);
                    $existingUser = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Insert new student data
                    $insertStudentStmt->execute([$nim, $name, $angkatan, $semester_masuk, $status]);
                    $studentId = $db->lastInsertId();
                    
                    if ($existingUser) {
                        // Use existing user account
                        $userId = $existingUser['id'];
                        // Update user data
                        $updateUserStmt->execute([$name, $nim]);
                    } else {
                        // Generate default password (NIM as default password)
                        $defaultPassword = password_hash($nim, PASSWORD_DEFAULT);
                        
                        // Insert user data
                        $insertUserStmt->execute([$nim, $defaultPassword, $name, 'mahasiswa']);
                        $userId = $db->lastInsertId();
                    }
                    
                    // Update student with user_id
                    $updateStudentUserIdStmt->execute([$userId, $studentId]);
                }
                
                // Commit transaction
                $db->commit();
                
                $importedCount++;
            } catch (Exception $e) {
                $db->rollback();
                $errors[] = "Gagal mengimpor data untuk NIM {$nim}: " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        // Show result
        $this->render('students/import_result', [
            'importedCount' => $importedCount,
            'errors' => $errors
        ]);
    }
}