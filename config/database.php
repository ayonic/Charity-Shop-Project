<?php
/**
 * Database Configuration
 */

function db_count($table, $where = '') {
    global $pdo;
    $sql = "SELECT COUNT(*) as count FROM " . $table;
    if ($where) {
        $sql .= " WHERE " . $where;
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    } catch (PDOException $e) {
        error_log("Error in db_count: " . $e->getMessage());
        return 0;
    }
}


// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'charity_shop');

// Database helper functions
function db_connect() {
    try {
        // Create PDO connection
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

function db_close($connection) {
    $connection = null;
}

function db_escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function db_query($query, $params = []) {
    $connection = db_connect();
    $stmt = $connection->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

function db_fetch_all($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function db_fetch_row($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch();
}

function db_insert_id() {
    global $connection;
    return $connection->insert_id;
}

function db_affected_rows() {
    global $connection;
    return $connection->affected_rows;
}

function db_error() {
    global $connection;
    return $connection->error;
}

function db_insert($table, $data) {
    $connection = db_connect();
    $fields = array_keys($data);
    $values = array_values($data);
    $placeholders = array_fill(0, count($fields), '?');
    
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(', ', $fields),
        implode(', ', $placeholders)
    );
    
    try {
        $stmt = $connection->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement');
        }
        
        if (!$stmt->execute($values)) {
            throw new Exception('Failed to execute statement');
        }
        
        return $connection->lastInsertId();
    } catch (PDOException $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}