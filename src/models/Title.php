<?php

class Title {
    private $conn;
    private $table_name = "titles";

    public $id;
    public $student_id;
    public $title;
    public $abstract;
    public $keywords;
    public $advisor_letter_link;
    public $examiner_letter_link;
    public $proposed_pembimbing_1_id;
    public $proposed_pembimbing_2_id;
    public $proposed_penguji_1_id;
    public $proposed_penguji_2_id;
    public $proposed_penguji_3_id;
    public $document_path;
    public $status;
    public $notes;
    public $submitted_at;
    public $verified_at;
    public $verified_by;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT t.*, s.nim, s.name as student_name 
                  FROM " . $this->table_name . " t
                  JOIN students s ON t.student_id = s.id
                  ORDER BY t.submitted_at DESC";
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
            $this->student_id = $row['student_id'];
            $this->title = $row['title'];
            $this->abstract = $row['abstract'];
            $this->keywords = $row['keywords'];
            $this->advisor_letter_link = $row['advisor_letter_link'] ?? null;
            $this->examiner_letter_link = $row['examiner_letter_link'] ?? null;
            $this->proposed_pembimbing_1_id = $row['proposed_pembimbing_1_id'] ?? null;
            $this->proposed_pembimbing_2_id = $row['proposed_pembimbing_2_id'] ?? null;
            $this->proposed_penguji_1_id = $row['proposed_penguji_1_id'] ?? null;
            $this->proposed_penguji_2_id = $row['proposed_penguji_2_id'] ?? null;
            $this->proposed_penguji_3_id = $row['proposed_penguji_3_id'] ?? null;
            $this->document_path = $row['document_path'];
            $this->status = $row['status'];
            $this->notes = $row['notes'];
            $this->submitted_at = $row['submitted_at'];
            $this->verified_at = $row['verified_at'];
            $this->verified_by = $row['verified_by'];
            return true;
        }
        return false;
    }

    public function getByStudentId($student_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE student_id = ? ORDER BY submitted_at DESC LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->student_id = $row['student_id'];
            $this->title = $row['title'];
            $this->abstract = $row['abstract'];
            $this->keywords = $row['keywords'];
            $this->advisor_letter_link = $row['advisor_letter_link'] ?? null;
            $this->examiner_letter_link = $row['examiner_letter_link'] ?? null;
            $this->proposed_pembimbing_1_id = $row['proposed_pembimbing_1_id'] ?? null;
            $this->proposed_pembimbing_2_id = $row['proposed_pembimbing_2_id'] ?? null;
            $this->proposed_penguji_1_id = $row['proposed_penguji_1_id'] ?? null;
            $this->proposed_penguji_2_id = $row['proposed_penguji_2_id'] ?? null;
            $this->proposed_penguji_3_id = $row['proposed_penguji_3_id'] ?? null;
            $this->document_path = $row['document_path'];
            $this->status = $row['status'];
            $this->notes = $row['notes'];
            $this->submitted_at = $row['submitted_at'];
            $this->verified_at = $row['verified_at'];
            $this->verified_by = $row['verified_by'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET student_id=:student_id, title=:title, abstract=:abstract, 
                      keywords=:keywords, advisor_letter_link=:advisor_letter_link,
                      examiner_letter_link=:examiner_letter_link,
                      proposed_pembimbing_1_id=:proposed_pembimbing_1_id,
                      proposed_pembimbing_2_id=:proposed_pembimbing_2_id,
                      proposed_penguji_1_id=:proposed_penguji_1_id,
                      proposed_penguji_2_id=:proposed_penguji_2_id,
                      proposed_penguji_3_id=:proposed_penguji_3_id,
                      document_path=:document_path, status=:status";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->student_id = htmlspecialchars(strip_tags($this->student_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->abstract = htmlspecialchars(strip_tags($this->abstract));
        $this->keywords = htmlspecialchars(strip_tags($this->keywords));
        $this->advisor_letter_link = htmlspecialchars(strip_tags((string)$this->advisor_letter_link));
        $this->examiner_letter_link = htmlspecialchars(strip_tags((string)$this->examiner_letter_link));
        $this->proposed_pembimbing_1_id = htmlspecialchars(strip_tags((string)$this->proposed_pembimbing_1_id));
        $this->proposed_pembimbing_2_id = htmlspecialchars(strip_tags((string)$this->proposed_pembimbing_2_id));
        $this->proposed_penguji_1_id = htmlspecialchars(strip_tags((string)$this->proposed_penguji_1_id));
        $this->proposed_penguji_2_id = htmlspecialchars(strip_tags((string)$this->proposed_penguji_2_id));
        $this->proposed_penguji_3_id = htmlspecialchars(strip_tags((string)$this->proposed_penguji_3_id));
        $this->document_path = htmlspecialchars(strip_tags($this->document_path));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Bind values
        $stmt->bindParam(":student_id", $this->student_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":abstract", $this->abstract);
        $stmt->bindParam(":keywords", $this->keywords);
        $stmt->bindParam(":advisor_letter_link", $this->advisor_letter_link);
        $stmt->bindParam(":examiner_letter_link", $this->examiner_letter_link);
        $stmt->bindParam(":proposed_pembimbing_1_id", $this->proposed_pembimbing_1_id);
        $stmt->bindParam(":proposed_pembimbing_2_id", $this->proposed_pembimbing_2_id);
        $stmt->bindParam(":proposed_penguji_1_id", $this->proposed_penguji_1_id);
        $stmt->bindParam(":proposed_penguji_2_id", $this->proposed_penguji_2_id);
        $stmt->bindParam(":proposed_penguji_3_id", $this->proposed_penguji_3_id);
        $stmt->bindParam(":document_path", $this->document_path);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET title = :title,
                      abstract = :abstract,
                      keywords = :keywords,
                      advisor_letter_link = :advisor_letter_link,
                      examiner_letter_link = :examiner_letter_link,
                      proposed_pembimbing_1_id = :proposed_pembimbing_1_id,
                      proposed_pembimbing_2_id = :proposed_pembimbing_2_id,
                      proposed_penguji_1_id = :proposed_penguji_1_id,
                      proposed_penguji_2_id = :proposed_penguji_2_id,
                      proposed_penguji_3_id = :proposed_penguji_3_id,
                      document_path = :document_path,
                      status = :status,
                      notes = :notes,
                      verified_at = :verified_at,
                      verified_by = :verified_by
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->abstract = htmlspecialchars(strip_tags($this->abstract));
        $this->keywords = htmlspecialchars(strip_tags($this->keywords));
        $this->advisor_letter_link = htmlspecialchars(strip_tags((string)$this->advisor_letter_link));
        $this->examiner_letter_link = htmlspecialchars(strip_tags((string)$this->examiner_letter_link));
        $this->proposed_pembimbing_1_id = htmlspecialchars(strip_tags((string)$this->proposed_pembimbing_1_id));
        $this->proposed_pembimbing_2_id = htmlspecialchars(strip_tags((string)$this->proposed_pembimbing_2_id));
        $this->proposed_penguji_1_id = htmlspecialchars(strip_tags((string)$this->proposed_penguji_1_id));
        $this->proposed_penguji_2_id = htmlspecialchars(strip_tags((string)$this->proposed_penguji_2_id));
        $this->proposed_penguji_3_id = htmlspecialchars(strip_tags((string)$this->proposed_penguji_3_id));
        $this->document_path = htmlspecialchars(strip_tags($this->document_path));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->verified_at = htmlspecialchars(strip_tags($this->verified_at));
        $this->verified_by = htmlspecialchars(strip_tags($this->verified_by));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":abstract", $this->abstract);
        $stmt->bindParam(":keywords", $this->keywords);
        $stmt->bindParam(":advisor_letter_link", $this->advisor_letter_link);
        $stmt->bindParam(":examiner_letter_link", $this->examiner_letter_link);
        $stmt->bindParam(":proposed_pembimbing_1_id", $this->proposed_pembimbing_1_id);
        $stmt->bindParam(":proposed_pembimbing_2_id", $this->proposed_pembimbing_2_id);
        $stmt->bindParam(":proposed_penguji_1_id", $this->proposed_penguji_1_id);
        $stmt->bindParam(":proposed_penguji_2_id", $this->proposed_penguji_2_id);
        $stmt->bindParam(":proposed_penguji_3_id", $this->proposed_penguji_3_id);
        $stmt->bindParam(":document_path", $this->document_path);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":verified_at", $this->verified_at);
        $stmt->bindParam(":verified_by", $this->verified_by);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        return $stmt->execute();
    }

    public function getPendingTitles() {
        $query = "SELECT t.*, s.nim, s.name as student_name 
                  FROM " . $this->table_name . " t
                  JOIN students s ON t.student_id = s.id
                  WHERE t.status = 'MENUNGGU'
                  ORDER BY t.submitted_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
