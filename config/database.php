<?php
/**
 * Database Configuration
 */

// Database connection settings
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'doctorna_db');
// Support both DB_USERNAME/DB_PASSWORD and DB_USER/DB_PASS from installer
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? ($_ENV['DB_USER'] ?? 'root'));
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? ($_ENV['DB_PASS'] ?? ''));
define('DB_CHARSET', 'utf8mb4');

// PDO options
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Enable emulated prepares to allow binding LIMIT/OFFSET and improve compatibility on some hosts
    PDO::ATTR_EMULATE_PREPARES => true,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
]);

/**
 * Database connection class
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD, PDO_OPTIONS);
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            // Bind params with proper types (ensure LIMIT/OFFSET are integers)
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $paramName = is_int($key)
                        ? $key + 1 // positional params are 1-based
                        : (strpos($key, ':') === 0 ? $key : ':' . $key);
                    $isIntParam = is_int($value) || (is_string($value) && ctype_digit($value)) || in_array($key, ['limit', 'offset']);
                    if ($isIntParam) {
                        $stmt->bindValue($paramName, (int)$value, PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue($paramName, $value);
                    }
                }
                $stmt->execute();
            } else {
                $stmt->execute();
            }
            return $stmt;
        } catch (PDOException $e) {
            if (APP_DEBUG) {
                throw new Exception("Query failed: " . $e->getMessage() . " SQL: " . $sql);
            } else {
                throw new Exception("Database query failed. Please try again later.");
            }
        }
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        return $this->connection->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        return $this->query($sql, $params);
    }
    
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params);
    }
    
    public function count($table, $where = '1=1', $params = []) {
        // If WHERE clause references alias 'a.', wrap table with alias
        $useAlias = strpos($where, 'a.') !== false;
        $from = $useAlias ? "{$table} a" : $table;
        $sql = "SELECT COUNT(*) as count FROM {$from} WHERE {$where}";
        $result = $this->fetch($sql, $params);
        return (int)($result['count'] ?? 0);
    }

    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Global database helper function
function db() {
    return Database::getInstance();
}
