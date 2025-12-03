<?php
class Comment {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    public function addComment($user_id, $comment_text, $assignment_id = null, $submission_id = null) {
    try {
        $stmt = $this->conn->prepare("
            INSERT INTO comments (user_id, assignment_id, submission_id, comment_text) 
            VALUES (?, ?, ?, ?)
        ");
        $result = $stmt->execute([$user_id, $assignment_id, $submission_id, $comment_text]);

        if ($result) {
            $comment_id = $this->conn->lastInsertId();
            $target_type = $assignment_id ? 'assignment' : 'submission';
            $target_id = $assignment_id ?? $submission_id;
            
            $this->logAction($user_id, 'comment_add', "Added comment to $target_type", $target_id, $target_type);
            
            return $comment_id;
        }

        return false;
    } catch(PDOException $e) {
        return false;
    }
}

    public function getAssignmentComments($assignment_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, u.first_name, u.last_name, u.username, u.profile_picture 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.assignment_id = ? 
                ORDER BY c.created_at ASC
            ");
            $stmt->execute([$assignment_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    public function getSubmissionComments($submission_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, u.first_name, u.last_name, u.username, u.profile_picture 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.submission_id = ? 
                ORDER BY c.created_at ASC
            ");
            $stmt->execute([$submission_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    public function updateComment($user_id, $comment_id, $comment_text) {
        try {
            // Verify user owns the comment
            $stmt = $this->conn->prepare("SELECT user_id FROM comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            $comment = $stmt->fetch();

            if (!$comment || $comment['user_id'] != $user_id) {
                return false;
            }

            $stmt = $this->conn->prepare("
                UPDATE comments 
                SET comment_text = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $result = $stmt->execute([$comment_text, $comment_id]);

            if ($result) {
                $this->logAction($user_id, 'comment_update', "Updated comment", $comment_id, 'comment');
            }

            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function deleteComment($user_id, $comment_id) {
        try {
            // Verify user owns the comment
            $stmt = $this->conn->prepare("SELECT user_id FROM comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            $comment = $stmt->fetch();

            if (!$comment || $comment['user_id'] != $user_id) {
                return false;
            }

            $stmt = $this->conn->prepare("DELETE FROM comments WHERE id = ?");
            $result = $stmt->execute([$comment_id]);

            if ($result) {
                $this->logAction($user_id, 'comment_delete', "Deleted comment", $comment_id, 'comment');
            }

            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getCommentById($comment_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT c.*, u.first_name, u.last_name, u.username, u.profile_picture 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.id = ?
            ");
            $stmt->execute([$comment_id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return null;
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
