<?php
class Classroom {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

public function getClassesByTeacher(int $teacher_id): array {
    try {
        $stmt = $this->conn->prepare("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM class_members cm WHERE cm.class_id = c.id) AS student_count
            FROM classes c
            WHERE c.teacher_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$teacher_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

public function deleteClass($teacher_id, $class_id) {
    try {
        // Pārbaude, vai skolotājs pieder šai klasei
        $stmt = $this->conn->prepare("SELECT name FROM classes WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$class_id, $teacher_id]);
        $class = $stmt->fetch();
        if (!$class) return false;

        // Dzēst visus uzdevumus klasē
        $stmt_assignments = $this->conn->prepare("SELECT id FROM assignments WHERE class_id = ?");
        $stmt_assignments->execute([$class_id]);
        $assignments = $stmt_assignments->fetchAll();

        // Izveido Assignment objektu
        $assignmentObj = new Assignment();
        foreach ($assignments as $a) {
            $assignmentObj->deleteAssignment($teacher_id, $a['id']); // teacher_id nodod metodei
        }

        // Dzēst studentus no klases
        $stmt_del_members = $this->conn->prepare("DELETE FROM class_members WHERE class_id = ?");
        $stmt_del_members->execute([$class_id]);

        // Dzēst pašu klasi
        $stmt_del_class = $this->conn->prepare("DELETE FROM classes WHERE id = ?");
        $stmt_del_class->execute([$class_id]);

        // Log
        $this->logAction($teacher_id, 'class_delete', "Deleted class: " . $class['name'], $class_id, 'class');

        return true;
    } catch(PDOException $e) {
        return false;
    }
}


    public function createClass($teacher_id, $name, $description) {
        try {
            $class_code = $this->generateClassCode();
            $qr_code = $this->generateQRCode($class_code);
            
            $stmt = $this->conn->prepare("INSERT INTO classes (name, description, teacher_id, class_code, qr_code) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $teacher_id, $class_code, $qr_code]);
            
            $class_id = $this->conn->lastInsertId();
            
            // Log action
            $this->logAction($teacher_id, 'class_create', "Created class: $name", $class_id, 'class');
            
            return $class_id;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getTeacherClasses($teacher_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, COUNT(cm.id) as student_count 
                FROM classes c 
                LEFT JOIN class_members cm ON c.id = cm.class_id 
                WHERE c.teacher_id = ? 
                GROUP BY c.id 
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$teacher_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getStudentClasses($student_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, u.first_name as teacher_first_name, u.last_name as teacher_last_name 
                FROM classes c 
                JOIN class_members cm ON c.id = cm.class_id 
                JOIN users u ON c.teacher_id = u.id 
                WHERE cm.student_id = ? 
                ORDER BY c.created_at DESC
            ");
            $stmt->execute([$student_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getClassById($class_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, u.first_name as teacher_first_name, u.last_name as teacher_last_name, u.username as teacher_username 
                FROM classes c 
                JOIN users u ON c.teacher_id = u.id 
                WHERE c.id = ?
            ");
            $stmt->execute([$class_id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return null;
        }
    }

    public function joinClass($student_id, $class_code) {
        try {
            $stmt = $this->conn->prepare("SELECT id FROM classes WHERE class_code = ?");
            $stmt->execute([$class_code]);
            $class = $stmt->fetch();

            if (!$class) {
                return false;
            }

            $class_id = $class['id'];

            // Check if already enrolled
            $stmt = $this->conn->prepare("SELECT id FROM class_members WHERE class_id = ? AND student_id = ?");
            $stmt->execute([$class_id, $student_id]);
            if ($stmt->fetch()) {
                return false; // Already enrolled
            }

            // Enroll student
            $stmt = $this->conn->prepare("INSERT INTO class_members (class_id, student_id) VALUES (?, ?)");
            $result = $stmt->execute([$class_id, $student_id]);

            if ($result) {
                $this->logAction($student_id, 'class_join', "Joined class with code: $class_code", $class_id, 'class');
            }

            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getClassMembers($class_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.id, u.username, u.first_name, u.last_name, u.profile_picture, cm.joined_at 
                FROM class_members cm 
                JOIN users u ON cm.student_id = u.id 
                WHERE cm.class_id = ? 
                ORDER BY u.first_name, u.last_name
            ");
            $stmt->execute([$class_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    public function removeStudent($teacher_id, $class_id, $student_id) {
        try {
            // Verify teacher owns the class
            $stmt = $this->conn->prepare("SELECT teacher_id FROM classes WHERE id = ? AND teacher_id = ?");
            $stmt->execute([$class_id, $teacher_id]);
            if (!$stmt->fetch()) {
                return false;
            }

            $stmt = $this->conn->prepare("DELETE FROM class_members WHERE class_id = ? AND student_id = ?");
            $result = $stmt->execute([$class_id, $student_id]);

            if ($result) {
                $this->logAction($teacher_id, 'student_remove', "Removed student from class", $class_id, 'class');
            }

            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    private function generateClassCode() {
        do {
            $code = substr(strtoupper(uniqid()), 0, 6);
            // Make sure it contains letters and numbers
            $code = preg_replace('/[^A-Z0-9]/', '', $code);
            if (strlen($code) < 6) {
                $code = str_pad($code, 6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
            }
            $code = substr($code, 0, 6);

            $stmt = $this->conn->prepare("SELECT id FROM classes WHERE class_code = ?");
            $stmt->execute([$code]);
        } while ($stmt->fetch());

        return $code;
    }

    private function generateQRCode($class_code) {
        // For this implementation, we'll use a simple approach
        // In production, you might want to use a QR code library
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        return $base_url . "/join_class.php?code=" . $class_code;
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
