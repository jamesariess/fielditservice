<?php
/**
 * Compatibility adapter for AI operations.
 * AI tables now live in the main Hub database; keeping this class avoids
 * rewriting the established AI API surface.
 */
class AIDatabase {
    private static ?PDO $pdo = null;
    private static bool $connected = false;
    private static bool $schemaReady = false;
    
    public static function connect() {
        if (self::$connected) return true;
        try {
            if (class_exists('Database')) {
                self::$pdo = Database::getInstance();
            } else {
                $dsn = "mysql:host=" . AI_DB_HOST . ";port=" . AI_DB_PORT . ";dbname=" . AI_DB_NAME . ";charset=" . AI_DB_CHARSET;
                self::$pdo = new PDO($dsn, AI_DB_USER, AI_DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            }
            self::ensureSchema();
            self::$connected = true;
            return true;
        } catch (PDOException $e) {
            error_log("AI Database Error: " . $e->getMessage());
            self::$connected = false;
            return false;
        }
    }

    private static function ensureSchema(): void {
        if (self::$schemaReady || !self::$pdo) return;
        $statements = [
            "CREATE TABLE IF NOT EXISTS ai_conversation_logs (
                id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(150) NOT NULL,
                user_id INT NOT NULL,
                message TEXT NOT NULL,
                response LONGTEXT NOT NULL,
                sources_used VARCHAR(1000) DEFAULT NULL,
                confidence VARCHAR(20) DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ai_logs_session (session_id),
                INDEX idx_ai_logs_user (user_id),
                INDEX idx_ai_logs_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS ai_personality (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                bot_name VARCHAR(100) NOT NULL DEFAULT 'IT Bot',
                greeting TEXT DEFAULT NULL,
                personality VARCHAR(50) DEFAULT 'professional',
                system_prompt LONGTEXT DEFAULT NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_ai_personality_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS ai_training_files (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                file_type VARCHAR(30) DEFAULT 'text',
                content LONGTEXT NOT NULL,
                category VARCHAR(100) DEFAULT 'general',
                tags VARCHAR(1000) DEFAULT NULL,
                uploaded_by INT DEFAULT NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_ai_training_active (is_active),
                INDEX idx_ai_training_category (category)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS ai_conversation_ratings (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(150) NOT NULL,
                user_id INT NOT NULL,
                rating TINYINT NOT NULL,
                comment TEXT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_ai_rating_session (session_id),
                INDEX idx_ai_rating_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            "CREATE TABLE IF NOT EXISTS ai_response_feedback (
                id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                legacy_message_id BIGINT DEFAULT NULL,
                session_id VARCHAR(150) DEFAULT NULL,
                user_id INT NOT NULL,
                rating VARCHAR(20) NOT NULL,
                solved VARCHAR(20) DEFAULT NULL,
                feedback TEXT DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_ai_response_feedback_user (user_id),
                INDEX idx_ai_response_feedback_session (session_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        ];
        foreach ($statements as $sql) self::$pdo->exec($sql);
        self::$schemaReady = true;
    }
    
    public static function isConnected() {
        return self::$connected && self::$pdo !== null;
    }
    
    public static function fetch($sql, $params = []) {
        if (!self::connect()) return null;
        try {
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("AI DB Query Error: " . $e->getMessage());
            return null;
        }
    }
    
    public static function fetchAll($sql, $params = []) {
        if (!self::connect()) return [];
        try {
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("AI DB Query Error: " . $e->getMessage());
            return [];
        }
    }
    
    public static function insert($table, $data) {
        if (!self::connect()) return null;
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute(array_values($data));
            return self::$pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("AI DB Insert Error: " . $e->getMessage());
            return null;
        }
    }
    
    public static function update($table, $data, $where) {
        if (!self::connect()) return false;
        try {
            $setParts = [];
            $values = [];
            foreach ($data as $col => $val) {
                $setParts[] = "{$col} = ?";
                $values[] = $val;
            }
            $whereParts = [];
            foreach ($where as $col => $val) {
                $whereParts[] = "{$col} = ?";
                $values[] = $val;
            }
            $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE " . implode(' AND ', $whereParts);
            $stmt = self::$pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("AI DB Update Error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function delete($table, $where) {
        if (!self::connect()) return false;
        try {
            $whereParts = [];
            $values = [];
            foreach ($where as $col => $val) {
                $whereParts[] = "{$col} = ?";
                $values[] = $val;
            }
            $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereParts);
            $stmt = self::$pdo->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("AI DB Delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function execute($sql, $params = []) {
        if (!self::connect()) return false;
        try {
            $stmt = self::$pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("AI DB Execute Error: " . $e->getMessage());
            return false;
        }
    }
    
    public static function escapeId($id) {
        return '`' . str_replace('`', '``', $id) . '`';
    }
}
