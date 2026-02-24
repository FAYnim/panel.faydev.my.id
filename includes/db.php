<?php
class Database {
    private $db_host = "localhost";
    private $db_user = "root";
    private $db_pass = "";
    private $db_name = "fayd7716_panel";

    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=".$this->db_host.";dbname=".$this->db_name.";charset=utf8mb4;";

            $option = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            $this->conn = new PDO($dsn, $this->db_user, $this->db_pass, $option);

        } catch (PDOException $e) {
            error_log("Connection Failed: ".$e->getMessage());
        }

        return $this->conn;
    }

    /**
     * Insert data into table using query builder
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int|false Last insert ID on success, false on failure
     */
    public function insert($table, $data) {
        try {
            // Build column names and placeholders
            $columns = array_keys($data);
            $columnString = implode(', ', $columns);
            $placeholders = ':' . implode(', :', $columns);

            // Build INSERT query
            $sql = "INSERT INTO {$table} ({$columnString}) VALUES ({$placeholders})";

            // Prepare statement
            $stmt = $this->conn->prepare($sql);

            // Bind values
            foreach ($data as $key => $value) {
                $stmt->bindValue(":{$key}", $value);
            }

            // Execute
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Insert Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Select data from table using query builder
     * @param string $table Table name
     * @param array|string $columns Columns to select (default: '*')
     * @param array $where WHERE conditions as associative array
     * @param string $orderBy ORDER BY clause (e.g., 'id DESC')
     * @param string|int $limit LIMIT clause
     * @return array|false Array of results on success, false on failure
     */
    public function select($table, $columns = '*', $where = [], $orderBy = '', $limit = '') {
        try {
            // Build column string
            if (is_array($columns)) {
                $columnString = implode(', ', $columns);
            } else {
                $columnString = $columns;
            }

            // Build base query
            $sql = "SELECT {$columnString} FROM {$table}";

            // Build WHERE clause
            $whereClause = '';
            if (!empty($where)) {
                $conditions = [];
                foreach ($where as $key => $value) {
                    $conditions[] = "{$key} = :{$key}";
                }
                $whereClause = ' WHERE ' . implode(' AND ', $conditions);
                $sql .= $whereClause;
            }

            // Add ORDER BY
            if (!empty($orderBy)) {
                $sql .= " ORDER BY {$orderBy}";
            }

            // Add LIMIT
            if (!empty($limit)) {
                $sql .= " LIMIT {$limit}";
            }

            // Prepare statement
            $stmt = $this->conn->prepare($sql);

            // Bind WHERE values
            if (!empty($where)) {
                foreach ($where as $key => $value) {
                    $stmt->bindValue(":{$key}", $value);
                }
            }

            // Execute
            $stmt->execute();
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Select Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update data in table using query builder
     * @param string $table Table name
     * @param array $data Associative array of column => value to update
     * @param array $where WHERE conditions as associative array
     * @return int|false Number of affected rows on success, false on failure
     */
    public function update($table, $data, $where = []) {
        try {
            // Build SET clause
            $setClause = [];
            foreach ($data as $key => $value) {
                $setClause[] = "{$key} = :set_{$key}";
            }
            $setString = implode(', ', $setClause);

            // Build UPDATE query
            $sql = "UPDATE {$table} SET {$setString}";

            // Build WHERE clause
            if (!empty($where)) {
                $conditions = [];
                foreach ($where as $key => $value) {
                    $conditions[] = "{$key} = :where_{$key}";
                }
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
            }

            // Prepare statement
            $stmt = $this->conn->prepare($sql);

            // Bind SET values
            foreach ($data as $key => $value) {
                $stmt->bindValue(":set_{$key}", $value);
            }

            // Bind WHERE values
            if (!empty($where)) {
                foreach ($where as $key => $value) {
                    $stmt->bindValue(":where_{$key}", $value);
                }
            }

            // Execute and return affected rows
            if ($stmt->execute()) {
                return $stmt->rowCount();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete data from table using query builder
     * @param string $table Table name
     * @param array $where WHERE conditions as associative array
     * @return int|false Number of affected rows on success, false on failure
     */
    public function delete($table, $where = []) {
        try {
            // Build DELETE query
            $sql = "DELETE FROM {$table}";

            // Build WHERE clause
            if (!empty($where)) {
                $conditions = [];
                foreach ($where as $key => $value) {
                    $conditions[] = "{$key} = :{$key}";
                }
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
            }

            // Prepare statement
            $stmt = $this->conn->prepare($sql);

            // Bind WHERE values
            if (!empty($where)) {
                foreach ($where as $key => $value) {
                    $stmt->bindValue(":{$key}", $value);
                }
            }

            // Execute and return affected rows
            if ($stmt->execute()) {
                return $stmt->rowCount();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Delete Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count records in table using query builder
     * @param string $table Table name
     * @param array $where WHERE conditions as associative array
     * @return int|false Number of records on success, false on failure
     */
    public function count($table, $where = []) {
        try {
            // Build COUNT query
            $sql = "SELECT COUNT(*) as total FROM {$table}";

            // Build WHERE clause
            if (!empty($where)) {
                $conditions = [];
                foreach ($where as $key => $value) {
                    $conditions[] = "{$key} = :{$key}";
                }
                $sql .= ' WHERE ' . implode(' AND ', $conditions);
            }

            // Prepare statement
            $stmt = $this->conn->prepare($sql);

            // Bind WHERE values
            if (!empty($where)) {
                foreach ($where as $key => $value) {
                    $stmt->bindValue(":{$key}", $value);
                }
            }

            // Execute and return count
            $stmt->execute();
            $result = $stmt->fetch();
            return (int) $result['total'];

        } catch (PDOException $e) {
            error_log("Count Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute raw SQL query
     * @param string $sql Raw SQL query with ? placeholders
     * @param array $params Optional parameters for prepared statement
     * @return array|int|false Results on SELECT, affected rows on INSERT/UPDATE/DELETE, false on failure
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);

            if (stripos($sql, 'SELECT') === 0) {
                return $stmt->fetchAll();
            }

            return $stmt->rowCount();

        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            return false;
        }
    }
}
?>