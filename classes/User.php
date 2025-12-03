<?php
class User {
    private $db;
    public $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

public function exists(string $column, string $value): bool {
        try {
            // Pārbauda, lai $column būtu drošs lauks
            if (!in_array($column, ['email', 'username'])) {
                return false;
            }

            // Izpilda vaicājumu
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE {$column} = ?");
            $stmt->execute([$value]);
            
            // Atgriež TRUE, ja skaits ir lielāks par 0
            return $stmt->fetchColumn() > 0;

        } catch (PDOException $e) {
            // Kļūdas apstrāde
            return false;
        }
    }

    public function register($username, $email, $password, $first_name, $last_name, $role = 'student') {
        try {
            // Password is stored as plain text for debugging
            $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password, $first_name, $last_name, $role]);
            
            $user_id = $this->conn->lastInsertId();
            
            // Create default settings
            $stmt = $this->conn->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
            $stmt->execute([$user_id]);
            
            // Log action
            $this->logAction($user_id, 'register', "User registered with role: $role");
            
            return $user_id;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function login($username, $password) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && $password === $user['password']) {
                // Generate session token
                $session_token = bin2hex(random_bytes(32));
                $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                $stmt = $this->conn->prepare("INSERT INTO user_sessions (user_id, session_token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $session_token, $expires_at]);
                
                // Log action
                $this->logAction($user['id'], 'login', 'User logged in');
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['session_token'] = $session_token;
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['username'] = $user['username'];
                
                return $user;
            }
            return false;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function logout() {
        if (isset($_SESSION['session_token'])) {
            try {
                $stmt = $this->conn->prepare("DELETE FROM user_sessions WHERE session_token = ?");
                $stmt->execute([$_SESSION['session_token']]);
                
                // Log action
                $this->logAction($_SESSION['user_id'], 'logout', 'User logged out');
            } catch(PDOException $e) {
                // Continue with session destruction even if database logging fails
            }
        }
        
        session_destroy();
    }

    public function isAuthenticated() {
        if (!isset($_SESSION['session_token']) || !isset($_SESSION['user_id'])) {
            return false;
        }

        try {
            $stmt = $this->conn->prepare("SELECT * FROM user_sessions WHERE session_token = ? AND expires_at > NOW()");
            $stmt->execute([$_SESSION['session_token']]);
            return $stmt->fetch() !== false;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }

        try {
            $stmt = $this->conn->prepare("SELECT id, username, email, first_name, last_name, role, profile_picture FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return null;
        }
    }

    public function updateProfile($user_id, $first_name, $last_name, $email) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$first_name, $last_name, $email, $user_id]);
            
            if ($result) {
                $this->logAction($user_id, 'profile_update', 'Profile information updated');
            }
            
            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function updateProfilePicture($user_id, $file_path) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $result = $stmt->execute([$file_path, $user_id]);
            
            if ($result) {
                $this->logAction($user_id, 'profile_picture_update', 'Profile picture updated');
            }
            
            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getAllUsers() {
        try {
            $stmt = $this->conn->prepare("SELECT id, username, email, first_name, last_name, role, created_at FROM users ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }

    public function updateUserRole($admin_id, $user_id, $new_role) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $result = $stmt->execute([$new_role, $user_id]);
            
            if ($result) {
                $this->logAction($admin_id, 'role_update', "Updated user ID $user_id role to $new_role", $user_id, 'user');
            }
            
            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getActionHistory($limit = 50) {
        try {
            $stmt = $this->conn->prepare("
                SELECT ah.*, u.username, u.first_name, u.last_name 
                FROM action_history ah 
                JOIN users u ON ah.user_id = u.id 
                ORDER BY ah.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
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

    public function adminUpdateUser(int $admin_id, int $user_id, string $first_name, string $last_name, $password = null): bool {
        $sql = "UPDATE users SET first_name = ?, last_name = ?";
        $params = [$first_name, $last_name];
        
        if (!empty($password)) {
            // Jūsu esošā klase glabā paroles kā plain text. Drošības labad vajadzētu izmantot password_hash, bet šajā piemērā mēs pieturamies pie Jūsu esošās loģikas.
            $sql .= ", password = ?";
            $params[] = $password;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $user_id;
        
        try {
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                $this->logAction($admin_id, 'user_data_updated', "Admin atjaunināja lietotāja ID $user_id datus.", $user_id, 'user');
            }
            
            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }
}
