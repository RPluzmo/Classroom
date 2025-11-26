<?php
class Settings {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    public function getUserSettings($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return null;
        }
    }

    public function toggleDarkTheme($user_id) {
        try {
            // Get current setting
            $stmt = $this->conn->prepare("SELECT dark_theme FROM user_settings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $settings = $stmt->fetch();

            if (!$settings) {
                // Create default settings
                $stmt = $this->conn->prepare("INSERT INTO user_settings (user_id, dark_theme) VALUES (?, ?)");
                $stmt->execute([$user_id, 1]);
                $new_theme = true;
            } else {
                // Toggle existing setting
                $new_theme = !$settings['dark_theme'];
                $stmt = $this->conn->prepare("UPDATE user_settings SET dark_theme = ? WHERE user_id = ?");
                $stmt->execute([$new_theme ? 1 : 0, $user_id]);
            }

            // Update session
            $_SESSION['dark_theme'] = $new_theme;

            return $new_theme;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function updateSettings($user_id, $dark_theme = null, $language = null, $notifications = null) {
        try {
            $updates = [];
            $params = [];

            if ($dark_theme !== null) {
                $updates[] = "dark_theme = ?";
                $params[] = $dark_theme ? 1 : 0;
            }

            if ($language !== null) {
                $updates[] = "language = ?";
                $params[] = $language;
            }

            if ($notifications !== null) {
                $updates[] = "notifications = ?";
                $params[] = $notifications ? 1 : 0;
            }

            if (empty($updates)) {
                return true; // No updates needed
            }

            $params[] = $user_id;
            $sql = "UPDATE user_settings SET " . implode(', ', $updates) . " WHERE user_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute($params);

            if ($result && $dark_theme !== null) {
                $_SESSION['dark_theme'] = $dark_theme;
            }

            return $result;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function isDarkTheme($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT dark_theme FROM user_settings WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $settings = $stmt->fetch();
            
            return $settings ? (bool)$settings['dark_theme'] : false;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function initializeUserSettings($user_id) {
        try {
            $stmt = $this->conn->prepare("
                INSERT IGNORE INTO user_settings (user_id, dark_theme, language, notifications) 
                VALUES (?, 0, 'en', 1)
            ");
            return $stmt->execute([$user_id]);
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>