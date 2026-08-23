<?php
/**
 * AIDatabase — Separate database connection for AI operations
 * Uses fieldit_ai database (independent from main app database)
 * If main server goes down, AI still works
 */
class AIDatabase {
    private static ?PDO $pdo = null;
    private static bool $connected = false;
    
    public static function connect() {
        if (self::$connected) return true;
        try {
            $dsn = "mysql:host=" . AI_DB_HOST . ";port=" . AI_DB_PORT . ";dbname=" . AI_DB_NAME . ";charset=" . AI_DB_CHARSET;
            self::$pdo = new PDO($dsn, AI_DB_USER, AI_DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::$connected = true;
            return true;
        } catch (PDOException $e) {
            error_log("AI Database Error: " . $e->getMessage());
            self::$connected = false;
            return false;
        }
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
