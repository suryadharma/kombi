<?php

class Student {
    private $conn;
    private $table_name = "students";

    public $id;
    public $nim;
    public $name;
    public $angkatan;
    public $semester_masuk;
    public $semester_lulus;
    public $status;
    public $user_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nim";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->nim = $row['nim'];
            $this->name = $row['name'];
            $this->angkatan = $row['angkatan'];
            $this->semester_masuk = $row['semester_masuk'];
            $this->semester_lulus = $row['semester_lulus'];
            $this->status = $row['status'];
            $this->user_id = $row['user_id'];
            return true;
        }
        return false;
    }

    public function getByNim($nim) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE nim = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $nim);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->nim = $row['nim'];
            $this->name = $row['name'];
            $this->angkatan = $row['angkatan'];
            $this->semester_masuk = $row['semester_masuk'];
            $this->semester_lulus = $row['semester_lulus'];
            $this->status = $row['status'];
            $this->user_id = $row['user_id'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nim=:nim, name=:name, angkatan=:angkatan, semester_masuk=:semester_masuk, status=:status";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->nim = htmlspecialchars(strip_tags((string)$this->nim));
        $this->name = htmlspecialchars(strip_tags((string)$this->name));
        $this->angkatan = htmlspecialchars(strip_tags((string)$this->angkatan));
        $this->semester_masuk = htmlspecialchars(strip_tags((string)$this->semester_masuk));
        $this->status = htmlspecialchars(strip_tags((string)$this->status));

        // Bind values
        $stmt->bindParam(":nim", $this->nim);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":angkatan", $this->angkatan);
        $stmt->bindParam(":semester_masuk", $this->semester_masuk);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET nim = :nim, name = :name, angkatan = :angkatan, semester_masuk = :semester_masuk, 
                      semester_lulus = :semester_lulus, status = :status
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->nim = htmlspecialchars(strip_tags((string)$this->nim));
        $this->name = htmlspecialchars(strip_tags((string)$this->name));
        $this->angkatan = htmlspecialchars(strip_tags((string)$this->angkatan));
        $this->semester_masuk = htmlspecialchars(strip_tags((string)$this->semester_masuk));
        $this->status = htmlspecialchars(strip_tags((string)$this->status));
        $this->id = htmlspecialchars(strip_tags((string)$this->id));

        if ($this->semester_lulus !== null && $this->semester_lulus !== '') {
            $this->semester_lulus = htmlspecialchars(strip_tags((string)$this->semester_lulus));
        } else {
            $this->semester_lulus = null;
        }

        // Bind values
        $stmt->bindParam(":nim", $this->nim);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":angkatan", $this->angkatan);
        $stmt->bindParam(":semester_masuk", $this->semester_masuk);
        $stmt->bindParam(":semester_lulus", $this->semester_lulus);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        return $stmt->execute();
    }

    public function searchByKeyword($keyword) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE nim LIKE ? OR name LIKE ? 
                  ORDER BY nim";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->execute();

        return $stmt;
    }
    
    public function getByAngkatan($angkatan) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE angkatan = ? 
                  ORDER BY nim";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $angkatan);
        $stmt->execute();

        return $stmt;
    }
    
    public function searchByKeywordAndAngkatan($keyword, $angkatan) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (nim LIKE ? OR name LIKE ?) AND angkatan = ?
                  ORDER BY nim";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->bindParam(3, $angkatan);
        $stmt->execute();

        return $stmt;
    }
    
    public function getByStatus($status) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE status = ? 
                  ORDER BY nim";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->execute();

        return $stmt;
    }
    
    public function getByAngkatanAndStatus($angkatan, $status) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE angkatan = ? AND status = ?
                  ORDER BY nim";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $angkatan);
        $stmt->bindParam(2, $status);
        $stmt->execute();

        return $stmt;
    }
    
    public function searchByKeywordAndStatus($keyword, $status) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (nim LIKE ? OR name LIKE ?) AND status = ?
                  ORDER BY nim";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->bindParam(3, $status);
        $stmt->execute();

        return $stmt;
    }
    
    public function searchByAllFilters($keyword, $angkatan, $status) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (nim LIKE ? OR name LIKE ?) AND angkatan = ? AND status = ?
                  ORDER BY nim";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->bindParam(3, $angkatan);
        $stmt->bindParam(4, $status);
        $stmt->execute();

        return $stmt;
    }
    
    public function getByActiveAngkatan() {
        $activeAngkatan = Settings::getActiveAngkatan();
        
        if (empty($activeAngkatan)) {
            // Jika tidak ada angkatan aktif, kembalikan query kosong
            $query = "SELECT * FROM " . $this->table_name . " WHERE 1=0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        }
        
        $placeholders = implode(',', array_fill(0, count($activeAngkatan), '?'));
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE angkatan IN ($placeholders) AND status != 'LULUS'
                  ORDER BY nim";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($activeAngkatan);
        return $stmt;
    }
    
    public function searchByKeywordAndActiveAngkatan($keyword) {
        $activeAngkatan = Settings::getActiveAngkatan();
        
        if (empty($activeAngkatan)) {
            // Jika tidak ada angkatan aktif, kembalikan query kosong
            $query = "SELECT * FROM " . $this->table_name . " WHERE 1=0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        }
        
        $placeholders = implode(',', array_fill(0, count($activeAngkatan), '?'));
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (nim LIKE ? OR name LIKE ?) AND angkatan IN ($placeholders) AND status != 'LULUS'
                  ORDER BY nim";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $params = array_merge([$keyword, $keyword], $activeAngkatan);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function getByActiveAngkatanAndStatus($status) {
        $activeAngkatan = Settings::getActiveAngkatan();
        
        if (empty($activeAngkatan)) {
            // Jika tidak ada angkatan aktif, kembalikan query kosong
            $query = "SELECT * FROM " . $this->table_name . " WHERE 1=0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        }
        
        // Sanitize input
        $status = htmlspecialchars(strip_tags($status));
        $this->status = $status;
        
        $placeholders = implode(',', array_fill(0, count($activeAngkatan), '?'));
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE angkatan IN ($placeholders) AND status = ?
                  ORDER BY nim";
        
        $stmt = $this->conn->prepare($query);
        
        // Buat array parameter: angkatan pertama, lalu status
        $params = [];
        foreach ($activeAngkatan as $angkatan) {
            $params[] = htmlspecialchars(strip_tags($angkatan));
        }
        $params[] = $status;
        
        $stmt->execute($params);
        return $stmt;
    }
    
    public function searchByAllFiltersAndActiveAngkatan($keyword, $status) {
        $activeAngkatan = Settings::getActiveAngkatan();
        
        if (empty($activeAngkatan)) {
            // Jika tidak ada angkatan aktif, kembalikan query kosong
            $query = "SELECT * FROM " . $this->table_name . " WHERE 1=0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        }
        
        $placeholders = implode(',', array_fill(0, count($activeAngkatan), '?'));
        // Memperbaiki query dengan menghapus kondisi duplikat "status != 'LULUS'"
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE (nim LIKE ? OR name LIKE ?) AND angkatan IN ($placeholders) AND status = ?
                  ORDER BY nim";
        
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $params = array_merge([$keyword, $keyword], $activeAngkatan, [$status]);
        $stmt->execute($params);
        return $stmt;
    }
}
