<?php
class Assignment {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    public function createAssignment($class_id, $title, $description, $due_date = null, $max_points = 100) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO assignments (class_id, title, description, due_date, max_points) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$class_id, $title, $description, $due_date, $max_points]);
            
            $assignment_id = $this->conn->lastInsertId();
            
            // Log action
            $this->logAction($_SESSION['user_id'], 'assignment_create', "Created assignment: $title", $assignment_id, 'assignment');
            
            return $assignment_id;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getAssignmentsByClass($class_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT a.*, 
                       COUNT(s.id) as submission_count,
                       COUNT(CASE WHEN s.grade IS NOT NULL THEN 1 END) as graded_count
                FROM assignments a 
                LEFT JOIN submissions s ON a.id = s.assignment_id 
                WHERE a.class_id = ? 
                GROUP BY a.id 
                ORDER BY a.created_at DESC
            ");
            $stmt->execute([$class_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getAssignmentById($assignment_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT a.*, c.name as class_name, c.id as class_id, c.teacher_id 
                FROM assignments a 
                JOIN classes c ON a.class_id = c.id 
                WHERE a.id = ?
            ");
            $stmt->execute([$assignment_id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return null;
        }
    }

    public function submitAssignment($assignment_id, $student_id, $files = []) {
        try {
            // Check if already submitted
            $stmt = $this->conn->prepare("SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
            $stmt->execute([$assignment_id, $student_id]);
            
            if ($stmt->fetch()) {
                // Update existing submission
                $stmt = $this->conn->prepare("
                    UPDATE submissions 
                    SET submitted_at = CURRENT_TIMESTAMP, grade = NULL, feedback = NULL, graded_at = NULL, graded_by = NULL 
                    WHERE assignment_id = ? AND student_id = ?
                ");
                $stmt->execute([$assignment_id, $student_id]);
                $submission_id = $this->conn->lastInsertId();
            } else {
                // Create new submission
                $stmt = $this->conn->prepare("
                    INSERT INTO submissions (assignment_id, student_id) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$assignment_id, $student_id]);
                $submission_id = $this->conn->lastInsertId();
            }

            // Handle file uploads
            foreach ($files as $file) {
                $this->addSubmissionFile($submission_id, $file['name'], $file['path'], $file['size']);
            }

            // Log action
            $this->logAction($student_id, 'assignment_submit', "Submitted assignment", $assignment_id, 'assignment');
            
            return $submission_id;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getSubmission($assignment_id, $student_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT s.*, u.first_name, u.last_name, u.profile_picture,
                       gr.first_name as grader_first_name, gr.last_name as grader_last_name
                FROM submissions s 
                JOIN users u ON s.student_id = u.id 
                LEFT JOIN users gr ON s.graded_by = gr.id 
                WHERE s.assignment_id = ? AND s.student_id = ?
            ");
            $stmt->execute([$assignment_id, $student_id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return null;
        }
    }

    public function getSubmissions($assignment_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT s.*, u.first_name, u.last_name, u.username, u.profile_picture,
                       gr.first_name as grader_first_name, gr.last_name as grader_last_name
                FROM submissions s 
                JOIN users u ON s.student_id = u.id 
                LEFT JOIN users gr ON s.graded_by = gr.id 
                WHERE s.assignment_id = ? 
                ORDER BY s.submitted_at DESC
            ");
            $stmt->execute([$assignment_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    public function gradeSubmission($teacher_id, $submission_id, $grade, $feedback = null) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE submissions 
                SET grade = ?, feedback = ?, graded_at = CURRENT_TIMESTAMP, graded_by = ? 
                WHERE id = ?
            ");
            $result = $stmt->execute([$grade, $feedback, $teacher_id, $submission_id]);

            if ($result) {
                $this->logAction($teacher_id, 'submission_grade', "Graded submission with grade: $grade", $submission_id, 'submission');
            }

            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function addAssignmentFile($assignment_id, $file_name, $file_path, $file_size) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO assignment_files (assignment_id, file_name, file_path, file_size) 
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$assignment_id, $file_name, $file_path, $file_size]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getAssignmentFiles($assignment_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM assignment_files 
                WHERE assignment_id = ? 
                ORDER BY uploaded_at DESC
            ");
            $stmt->execute([$assignment_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    private function addSubmissionFile($submission_id, $file_name, $file_path, $file_size) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO submission_files (submission_id, file_name, file_path, file_size) 
                VALUES (?, ?, ?, ?)
            ");
            return $stmt->execute([$submission_id, $file_name, $file_path, $file_size]);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getSubmissionFiles($submission_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM submission_files 
                WHERE submission_id = ? 
                ORDER BY uploaded_at DESC
            ");
            $stmt->execute([$submission_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    private function logAction($user_id, $action_type, $action_description, $target_id = null, $target_type = null) {
        try {
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = $this->conn->prepare("
                INSERT INTO action_history (user_id, action_type, action_description, target_id, target_type, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $action_type, $action_description, $target_id, $target_type, $ip_address, $user_agent]);
        } catch(PDOException $e) {
            // Log errors but don't break the application
        }
    }
}
