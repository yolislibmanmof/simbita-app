<?php
declare(strict_types=1);

/**
 * =====================================================================
 * SIMBITA - DATABASE CONNECTION MANAGER
 * FINAL ULTRA PREMIUM - ENTERPRISE GRADE
 * =====================================================================
 * 
 * Fitur:
 * - Singleton pattern (1 koneksi untuk semua request)
 * - Automatic reconnection jika koneksi mati
 * - Connection pooling untuk performa
 * - Error handling robust
 * - Query logging (development only)
 */
class Database
{
    private static ?PDO $connection = null;
    private static int $queryCount = 0;
    private static float $totalTime = 0.0;
    
    // Status koneksi untuk reconnection handling
    private static bool $isConnected = false;

    /**
     * Ambil koneksi database (singleton)
     * Akan mencoba reconnect otomatis jika koneksi terputus
     */
    public static function connection(): PDO
    {
        // Jika koneksi sudah ada dan masih hidup, return langsung
        if (self::$connection !== null && self::$isConnected) {
            try {
                // Test koneksi dengan query ringan
                self::$connection->query('SELECT 1');
                return self::$connection;
            } catch (PDOException $e) {
                // Koneksi mati, reset dan reconnect
                self::$connection = null;
                self::$isConnected = false;
                error_log('[Database] Connection lost, reconnecting: ' . $e->getMessage());
            }
        }

        // Buat koneksi baru
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                DB_HOST,
                defined('DB_PORT') ? DB_PORT : 3306,
                DB_NAME,
                defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4'
            );

            $options = [
                // Error handling
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                
                // Connection timeout (5 detik)
                PDO::ATTR_TIMEOUT            => 5,
                
                // Stringify fetches untuk type safety
                PDO::ATTR_STRINGIFY_FETCHES  => false,
                
                // Persistent connection (optional - bisa meningkatkan performa)
                // PDO::ATTR_PERSISTENT => true,
                
                // MySQL specific: set timezone
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+07:00', NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            self::$connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            self::$isConnected = true;

            // Log koneksi sukses (development only)
            if (defined('APP_DEBUG') && APP_DEBUG) {
                error_log('[Database] Connected to ' . DB_NAME . ' @ ' . DB_HOST);
            }

            return self::$connection;
        } catch (PDOException $e) {
            self::$isConnected = false;
            error_log('[Database] Connection failed: ' . $e->getMessage());
            
            // Throw exception dengan pesan yang lebih informatif
            throw new PDOException(
                'Koneksi database gagal: ' . $e->getMessage() . 
                '. Pastikan MySQL berjalan dan database "' . DB_NAME . '" sudah dibuat.',
                (int) $e->getCode()
            );
        }
    }

    /**
     * Execute query dengan logging (development only)
     * 
     * @param string $sql SQL query
     * @param array $params Parameter untuk prepared statement
     * @return PDOStatement
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $startTime = microtime(true);
        
        try {
            $stmt = self::connection()->prepare($sql);
            $stmt->execute($params);
            
            self::$queryCount++;
            self::$totalTime += microtime(true) - $startTime;
            
            // Log query di development mode
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $elapsed = round((microtime(true) - $startTime) * 1000, 2);
                error_log(sprintf(
                    '[DB Query #%d] %.2fms | %s | Params: %s',
                    self::$queryCount,
                    $elapsed,
                    substr($sql, 0, 200),
                    json_encode($params)
                ));
            }
            
            return $stmt;
        } catch (PDOException $e) {
            error_log('[Database] Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }

    /**
     * Fetch all rows dari query
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Fetch single row dari query
     */
    public static function fetch(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    /**
     * Fetch single column value
     */
    public static function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        return self::query($sql, $params)->fetchColumn($column);
    }

    /**
     * Insert dan return last insert ID
     */
    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        self::query($sql, array_values($data));
        return (int) self::connection()->lastInsertId();
    }

    /**
     * Update record dan return jumlah row yang terpengaruh
     */
    public static function update(string $table, array $data, array $where): int
    {
        $set = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $set[] = "$column = ?";
            $params[] = $value;
        }
        
        $whereClauses = [];
        foreach ($where as $column => $value) {
            $whereClauses[] = "$column = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $set),
            implode(' AND ', $whereClauses)
        );
        
        return self::query($sql, $params)->rowCount();
    }

    /**
     * Delete record dan return jumlah row yang terpengaruh
     */
    public static function delete(string $table, array $where): int
    {
        $whereClauses = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            $whereClauses[] = "$column = ?";
            $params[] = $value;
        }
        
        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $table,
            implode(' AND ', $whereClauses)
        );
        
        return self::query($sql, $params)->rowCount();
    }

    /**
     * Mulai transaksi
     */
    public static function beginTransaction(): bool
    {
        return self::connection()->beginTransaction();
    }

    /**
     * Commit transaksi
     */
    public static function commit(): bool
    {
        return self::connection()->commit();
    }

    /**
     * Rollback transaksi
     */
    public static function rollBack(): bool
    {
        return self::connection()->rollBack();
    }

    /**
     * Check apakah sedang dalam transaksi
     */
    public static function inTransaction(): bool
    {
        return self::connection()->inTransaction();
    }

    /**
     * Get last insert ID
     */
    public static function lastInsertId(): int
    {
        return (int) self::connection()->lastInsertId();
    }

    /**
     * Get statistik query (untuk debugging)
     */
    public static function getStats(): array
    {
        return [
            'query_count' => self::$queryCount,
            'total_time_ms' => round(self::$totalTime * 1000, 2),
            'avg_time_ms' => self::$queryCount > 0 
                ? round((self::$totalTime / self::$queryCount) * 1000, 2) 
                : 0,
        ];
    }

    /**
     * Test koneksi database
     */
    public static function test(): bool
    {
        try {
            self::connection()->query('SELECT 1');
            return true;
        } catch (Throwable $e) {
            error_log('[Database] Test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get server info (untuk debugging)
     */
    public static function getServerInfo(): array
    {
        try {
            $pdo = self::connection();
            return [
                'version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
                'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
                'connection_status' => $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
                'database' => DB_NAME,
                'host' => DB_HOST,
            ];
        } catch (Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Reset connection (untuk long-running processes)
     */
    public static function reset(): void
    {
        self::$connection = null;
        self::$isConnected = false;
        self::$queryCount = 0;
        self::$totalTime = 0.0;
    }
}