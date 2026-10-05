"<?php

class BypassController extends BaseController
{
    /**
     * Form bypass komprehensif untuk semua tahap
     */
    public function comprehensiveForm(\$studentId, \$stage = null)
    {
        // Require authentication
        \$this->requireAuth();

        // Only kombi and superadmin can access
        \$role = \$this->getUserRole();
        if (\$role !== 'kombi' && \$role !== 'superadmin') {
            \$this->redirect('/dashboard');
            return;
        }

        \$database = new Database();
        \$db = \$database->getConnection();

        try {
            // Get student data
            \$studentQuery = \"SELECT s.* FROM students s WHERE s.id = :student_id\";
            \$studentStmt = \$db->prepare(\$studentQuery);
            \$studentStmt->bindParam(':student_id', \$studentId);
            \$studentStmt->execute();

            if (\$studentStmt->rowCount() == 0) {
                \$this->setFlash('error', 'Mahasiswa tidak ditemukan.');
                \$this->redirect('/scores/submit');
                return;
            }

            \$student = \$studentStmt->fetch(PDO::FETCH_ASSOC);

            \$this->render('bypass/comprehensive', [
                'student' => \$student,
                'error' => \$this->getFlash('error'),
                'success' => \$this->getFlash('success')
            ]);
        } catch (Exception \$e) {
            \$this->setFlash('error', 'Terjadi kesalahan: ' . \$e->getMessage());
            \$this->redirect('/scores/submit');
        }
    }

    /**
     * Save comprehensive bypass data
     */
    public function saveComprehensive()
    {
        // Require authentication
        \$this->requireAuth();

        // Only kombi and superadmin can save
        \$role = \$this->getUserRole();
        if (\$role !== 'kombi' && \$role !== 'superadmin') {
            \$this->redirect('/dashboard');
            return;
        }

        \$studentId = \$_POST['student_id'] ?? null;
        if (!\$studentId) {
            \$this->setFlash('error', 'Mahasiswa tidak ditemukan.');
            \$this->redirect('/scores/submit');
            return;
        }

        \$this->setFlash('success', 'Data bypass berhasil disimpan (fitur dalam pengembangan).');
        \$this->redirect(\"/bypass/comprehensive/\$studentId\");
    }
}
?>"