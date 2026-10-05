<?php

class ProposalEvaluationController extends BaseController {
    
    public function showForm($studentId) {
        // Require authentication
        $this->requireAuth();
        
        // Only pembimbing and penguji can access evaluation form
        $role = $this->getUserRole();
        if (
            !RoleHelper::isLecturerRole($role) &&
            $role !== 'superadmin' &&
            $role !== 'kombi'
        ) {
            $this->redirect('/dashboard');
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // For superadmin and kombi, allow access to any student
            if ($role === 'superadmin' || $role === 'kombi') {
                // Get student data
                $studentQuery = "SELECT s.*, u.name as lecturer_name FROM students s 
                                LEFT JOIN assignments a ON s.id = a.student_id 
                                LEFT JOIN users u ON a.lecturer_id = u.id 
                                WHERE s.id = :student_id";
                $studentStmt = $db->prepare($studentQuery);
                $studentStmt->bindParam(':student_id', $studentId);
                $studentStmt->execute();
                
                if ($studentStmt->rowCount() == 0) {
                    $this->redirect('/students');
                    return;
                }
                
                $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // For regular lecturers, check if they are assigned to this student
                $assignmentQuery = "SELECT s.*, u.name as lecturer_name FROM students s 
                                   JOIN assignments a ON s.id = a.student_id 
                                   JOIN users u ON a.lecturer_id = u.id 
                                   WHERE s.id = :student_id AND a.lecturer_id = :lecturer_id";
                $assignmentStmt = $db->prepare($assignmentQuery);
                $assignmentStmt->bindParam(':student_id', $studentId);
                $assignmentStmt->bindParam(':lecturer_id', $_SESSION['user_id']);
                $assignmentStmt->execute();
                
                if ($assignmentStmt->rowCount() == 0) {
                    // Not assigned to this student
                    $this->redirect('/students');
                    return;
                }
                
                $student = $assignmentStmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Get existing evaluation if any
            $evaluationQuery = "SELECT * FROM proposal_evaluations WHERE student_id = :student_id AND evaluator_id = :evaluator_id";
            $evaluationStmt = $db->prepare($evaluationQuery);
            $evaluationStmt->bindParam(':student_id', $studentId);
            $evaluationStmt->bindParam(':evaluator_id', $_SESSION['user_id']);
            $evaluationStmt->execute();
            
            $evaluation = null;
            if ($evaluationStmt->rowCount() > 0) {
                $evaluation = $evaluationStmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // Show evaluation form
            $this->render('evaluations/proposal_form', [
                'student' => $student,
                'evaluation' => $evaluation
            ]);
        } catch (Exception $e) {
            $this->redirect('/students');
        }
    }
    
    public function saveEvaluation($studentId) {
        // Require authentication
        $this->requireAuth();
        
        // Only pembimbing and penguji can save evaluation
        $role = $this->getUserRole();
        if (
            !RoleHelper::isLecturerRole($role) &&
            $role !== 'superadmin' &&
            $role !== 'kombi'
        ) {
            $this->redirect('/dashboard');
            return;
        }
        
        // Database connection
        $database = new Database();
        $db = $database->getConnection();
        
        try {
            // For superadmin and kombi, allow access to any student
            if ($role !== 'superadmin' && $role !== 'kombi') {
                // For regular lecturers, check if they are assigned to this student
                $assignmentQuery = "SELECT id FROM assignments 
                                   WHERE student_id = :student_id AND lecturer_id = :lecturer_id";
                $assignmentStmt = $db->prepare($assignmentQuery);
                $assignmentStmt->bindParam(':student_id', $studentId);
                $assignmentStmt->bindParam(':lecturer_id', $_SESSION['user_id']);
                $assignmentStmt->execute();
                
                if ($assignmentStmt->rowCount() == 0) {
                    // Not assigned to this student
                    $this->redirect('/students');
                    return;
                }
            }
            
            // Check if evaluation already exists
            $checkQuery = "SELECT id FROM proposal_evaluations WHERE student_id = :student_id AND evaluator_id = :evaluator_id";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->bindParam(':student_id', $studentId);
            $checkStmt->bindParam(':evaluator_id', $_SESSION['user_id']);
            $checkStmt->execute();
            
            // Get form data
            $performance_score_1 = $_POST['performance_score_1'] ?? 0;
            $performance_score_2 = $_POST['performance_score_2'] ?? 0;
            $performance_score_3 = $_POST['performance_score_3'] ?? 0;
            $performance_score_4 = $_POST['performance_score_4'] ?? 0;
            $performance_score_5 = $_POST['performance_score_5'] ?? 0;
            
            $content_score_1 = $_POST['content_score_1'] ?? 0;
            $content_score_2 = $_POST['content_score_2'] ?? 0;
            $content_score_3 = $_POST['content_score_3'] ?? 0;
            
            // Calculate weighted scores
            $performance_total = ($performance_score_1 * 0.10) + 
                                ($performance_score_2 * 0.15) + 
                                ($performance_score_3 * 0.20) + 
                                ($performance_score_4 * 0.25) + 
                                ($performance_score_5 * 0.30);
            
            $content_total = ($content_score_1 * 0.30) + 
                            ($content_score_2 * 0.40) + 
                            ($content_score_3 * 0.30);
            
            $final_score = ($performance_total * 0.40) + ($content_total * 0.60);
            
            if ($checkStmt->rowCount() > 0) {
                // Update existing evaluation
                $evaluation = $checkStmt->fetch(PDO::FETCH_ASSOC);
                $updateQuery = "UPDATE proposal_evaluations SET 
                                performance_score_1 = :performance_score_1,
                                performance_score_2 = :performance_score_2,
                                performance_score_3 = :performance_score_3,
                                performance_score_4 = :performance_score_4,
                                performance_score_5 = :performance_score_5,
                                content_score_1 = :content_score_1,
                                content_score_2 = :content_score_2,
                                content_score_3 = :content_score_3,
                                performance_total = :performance_total,
                                content_total = :content_total,
                                final_score = :final_score,
                                updated_at = CURRENT_TIMESTAMP
                                WHERE id = :id";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindParam(':performance_score_1', $performance_score_1);
                $updateStmt->bindParam(':performance_score_2', $performance_score_2);
                $updateStmt->bindParam(':performance_score_3', $performance_score_3);
                $updateStmt->bindParam(':performance_score_4', $performance_score_4);
                $updateStmt->bindParam(':performance_score_5', $performance_score_5);
                $updateStmt->bindParam(':content_score_1', $content_score_1);
                $updateStmt->bindParam(':content_score_2', $content_score_2);
                $updateStmt->bindParam(':content_score_3', $content_score_3);
                $updateStmt->bindParam(':performance_total', $performance_total);
                $updateStmt->bindParam(':content_total', $content_total);
                $updateStmt->bindParam(':final_score', $final_score);
                $updateStmt->bindParam(':id', $evaluation['id']);
                $updateStmt->execute();
            } else {
                // Insert new evaluation
                $insertQuery = "INSERT INTO proposal_evaluations (
                                student_id, evaluator_id, evaluator_role,
                                performance_score_1, performance_score_2, performance_score_3, performance_score_4, performance_score_5,
                                content_score_1, content_score_2, content_score_3,
                                performance_total, content_total, final_score
                                ) VALUES (
                                :student_id, :evaluator_id, :evaluator_role,
                                :performance_score_1, :performance_score_2, :performance_score_3, :performance_score_4, :performance_score_5,
                                :content_score_1, :content_score_2, :content_score_3,
                                :performance_total, :content_total, :final_score
                                )";
                $insertStmt = $db->prepare($insertQuery);
                $insertStmt->bindParam(':student_id', $studentId);
                $insertStmt->bindParam(':evaluator_id', $_SESSION['user_id']);
                $insertStmt->bindParam(':evaluator_role', $role);
                $insertStmt->bindParam(':performance_score_1', $performance_score_1);
                $insertStmt->bindParam(':performance_score_2', $performance_score_2);
                $insertStmt->bindParam(':performance_score_3', $performance_score_3);
                $insertStmt->bindParam(':performance_score_4', $performance_score_4);
                $insertStmt->bindParam(':performance_score_5', $performance_score_5);
                $insertStmt->bindParam(':content_score_1', $content_score_1);
                $insertStmt->bindParam(':content_score_2', $content_score_2);
                $insertStmt->bindParam(':content_score_3', $content_score_3);
                $insertStmt->bindParam(':performance_total', $performance_total);
                $insertStmt->bindParam(':content_total', $content_total);
                $insertStmt->bindParam(':final_score', $final_score);
                $insertStmt->execute();
            }
            
            $this->redirect("/evaluations/proposal/{$studentId}");
        } catch (Exception $e) {
            $this->redirect("/evaluations/proposal/{$studentId}");
        }
    }
}
