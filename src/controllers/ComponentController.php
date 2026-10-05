<?php

class ComponentController extends BaseController {
    
    public function index() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can manage components
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Get stage filter from GET parameter
            $stageFilter = isset($_GET['stage']) && !empty($_GET['stage']) ? $_GET['stage'] : null;
            
            // Build query based on filter
            if ($stageFilter) {
                $query = "SELECT * FROM evaluation_components WHERE stage = :stage ORDER BY stage, sort_order";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':stage', $stageFilter);
            } else {
                $query = "SELECT * FROM evaluation_components ORDER BY stage, sort_order";
                $stmt = $db->prepare($query);
            }
            
            $stmt->execute();
            $components = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Show components list
            $this->render('components/index', ['components' => $components]);
        } catch (Exception $e) {
            $this->render('components/index', ['error' => 'Gagal memuat data komponen: ' . $e->getMessage()]);
        }
    }
    
    public function create() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can manage components
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Show create form
        $this->render('components/create');
    }
    
    public function store() {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can manage components
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get POST data
        $name = $_POST['name'] ?? '';
        $stage = $_POST['stage'] ?? '';
        $weight = $_POST['weight'] ?? 0;
        $description = $_POST['description'] ?? '';
        $sortOrder = $_POST['sort_order'] ?? 0;
        
        // Validate input
        if (empty($name) || empty($stage)) {
            $this->render('components/create', ['error' => 'Nama dan tahap harus diisi']);
            return;
        }
        
        // Validate weight
        $weight = floatval($weight);
        if ($weight < 0 || $weight > 1) {
            $this->render('components/create', ['error' => 'Bobot harus antara 0 dan 1']);
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        $this->ensureWeightPrecision($db);
        
        try {
            // Insert component data
            $query = "INSERT INTO evaluation_components (name, stage, weight, description, sort_order) 
                      VALUES (:name, :stage, :weight, :description, :sort_order)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':stage', $stage);
            $stmt->bindParam(':weight', $weight);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':sort_order', $sortOrder);
            
            if ($stmt->execute()) {
                $this->redirect('/components');
            } else {
                $this->render('components/create', ['error' => 'Gagal menyimpan data komponen']);
            }
        } catch (Exception $e) {
            $this->render('components/create', ['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    
    public function edit($params) {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can manage components
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get component ID from params
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/components');
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Get component data
            $query = "SELECT * FROM evaluation_components WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $this->redirect('/components');
                return;
            }
            
            $component = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Show edit form
            $this->render('components/edit', ['component' => $component]);
        } catch (Exception $e) {
            $this->redirect('/components');
        }
    }
    
    public function update($params) {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can manage components
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get component ID from params
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/components');
            return;
        }
        
        // Get POST data
        $name = $_POST['name'] ?? '';
        $stage = $_POST['stage'] ?? '';
        $weight = $_POST['weight'] ?? 0;
        $description = $_POST['description'] ?? '';
        $sortOrder = $_POST['sort_order'] ?? 0;
        
        // Validate input
        if (empty($name) || empty($stage)) {
            $this->render('components/edit', ['error' => 'Nama dan tahap harus diisi', 'component' => ['id' => $id]]);
            return;
        }
        
        // Validate weight
        $weight = floatval($weight);
        if ($weight < 0 || $weight > 1) {
            $this->render('components/edit', ['error' => 'Bobot harus antara 0 dan 1', 'component' => ['id' => $id]]);
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        $this->ensureWeightPrecision($db);
        
        try {
            // Update component data
            $query = "UPDATE evaluation_components SET name = :name, stage = :stage, weight = :weight, 
                      description = :description, sort_order = :sort_order WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':stage', $stage);
            $stmt->bindParam(':weight', $weight);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':sort_order', $sortOrder);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $this->redirect('/components');
            } else {
                $this->render('components/edit', ['error' => 'Gagal memperbarui data komponen', 'component' => ['id' => $id]]);
            }
        } catch (Exception $e) {
            $this->render('components/edit', ['error' => 'Terjadi kesalahan: ' . $e->getMessage(), 'component' => ['id' => $id]]);
        }
    }
    
    public function delete($params) {
        // Require authentication
        $this->requireAuth();
        
        // Only kombi and superadmin can manage components
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        // Get component ID from params
        $id = $params['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/components');
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // Delete component
            $query = "DELETE FROM evaluation_components WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            $stmt->execute();
            
            $this->redirect('/components');
        } catch (Exception $e) {
            $this->redirect('/components');
        }
    }

    /**
     * Ensure the weight column can store 4 decimal places so bobot seperti 0.075 tidak dibulatkan.
     */
    private function ensureWeightPrecision(PDO $db): void
    {
        try {
            $checkQuery = "SELECT NUMERIC_SCALE, NUMERIC_PRECISION 
                           FROM INFORMATION_SCHEMA.COLUMNS 
                           WHERE TABLE_SCHEMA = DATABASE() 
                             AND TABLE_NAME = 'evaluation_components' 
                             AND COLUMN_NAME = 'weight'
                           LIMIT 1";
            $stmt = $db->query($checkQuery);
            $column = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            if ($column) {
                $scale = isset($column['NUMERIC_SCALE']) ? (int)$column['NUMERIC_SCALE'] : 0;
                $precision = isset($column['NUMERIC_PRECISION']) ? (int)$column['NUMERIC_PRECISION'] : 0;
                if ($scale < 4 || $precision < 6) {
                    $db->exec("ALTER TABLE evaluation_components 
                               MODIFY COLUMN weight DECIMAL(6,4) NOT NULL DEFAULT 0.0000");
                }
            }
        } catch (Exception $e) {
            // Jika alter gagal (misal hak akses), kita biarkan saja agar operasi utama tetap berjalan.
        }
    }
}
