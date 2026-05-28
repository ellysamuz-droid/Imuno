<?php

class Database
{
    private static ?Database $instance = null;
    private mysqli $connection;

    private string $host;
    private int    $port;
    private string $user;
    private string $pass;
    private string $db;

    private function __construct()
    {
        // Ambil dari Environment Variables (Vercel), jika kosong gunakan fallback
        $this->host = $_ENV['TIDB_HOST'] ?? getenv('TIDB_HOST') ?: 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com';
        $this->port = (int)($_ENV['TIDB_PORT'] ?? getenv('TIDB_PORT') ?: 4000);
        $this->user = $_ENV['TIDB_USER'] ?? getenv('TIDB_USER') ?: '4E4R7ePMi5xj2AM.root';
        $this->pass = $_ENV['TIDB_PASS'] ?? getenv('TIDB_PASS') ?: 'YJWuAMvg2BFEYIzj';
        $this->db   = $_ENV['TIDB_DB']   ?? getenv('TIDB_DB')   ?: 'imuno';

        $this->connect();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect(): void
    {
        $mysqli = new mysqli();

        // Menggunakan SSL karena TiDB Cloud mewajibkannya
        $mysqli->real_connect(
            $this->host,
            $this->user,
            $this->pass,
            $this->db,
            $this->port,
            null,
            MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
        );

        if ($mysqli->connect_errno) {
            throw new RuntimeException(
                'Koneksi ke TiDB Cloud gagal: ' . $mysqli->connect_error . 
                ' (Host: ' . $this->host . ':' . $this->port . ')'
            );
        }

        $mysqli->set_charset('utf8mb4');
        $this->connection = $mysqli;
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    public function query(string $sql, string $types = '', array $params = []): array
    {
        try {
            $stmt   = $this->prepare($sql, $types, $params);
            $result = $stmt->get_result();

            if ($result === false) {
                throw new RuntimeException('Gagal mengambil result: ' . $stmt->error);
            }

            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }

            $stmt->close();
            return $rows;
        } catch (Exception $e) {
            error_log('Database Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }

    public function execute(string $sql, string $types = '', array $params = []): int
    {
        try {
            $stmt         = $this->prepare($sql, $types, $params);
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows;
        } catch (Exception $e) {
            error_log('Database Execute Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }

    public function lastInsertId(): int
    {
        return (int) $this->connection->insert_id;
    }

    public function beginTransaction(): void
    {
        $this->connection->begin_transaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollback(): void
    {
        $this->connection->rollback();
    }

    private function prepare(string $sql, string $types, array $params): mysqli_stmt
    {
        $stmt = $this->connection->prepare($sql);

        if ($stmt === false) {
            throw new RuntimeException('Prepare gagal: ' . $this->connection->error . ' | SQL: ' . $sql);
        }

        if ($types !== '' && count($params) > 0) {
            if (!$stmt->bind_param($types, ...$params)) {
                throw new RuntimeException('Bind param gagal: ' . $stmt->error);
            }
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('Execute gagal: ' . $stmt->error);
        }

        return $stmt;
    }

    public function close(): void
    {
        if ($this->connection) {
            $this->connection->close();
        }
        self::$instance = null;
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new RuntimeException('Singleton tidak bisa di-unserialize.');
    }
}

// ============================================
// INISIALISASI KONEKSI DATABASE
// ============================================
try {
    $dbInstance = Database::getInstance();
    $conn = $dbInstance->getConnection(); 
    
    // Log success connection
    error_log('✅ Database connection successful');
    
} catch (Exception $e) {
    // Log error
    error_log('❌ Database connection failed: ' . $e->getMessage());
    
    // Jika ini dari API request, kirim JSON error
    if (strpos($_SERVER['REQUEST_URI'], '.php') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal terhubung ke database: ' . $e->getMessage()
        ]);
        exit();
    }
    
    // Jika ini halaman view, tampilkan error
    die('❌ Koneksi Database Gagal: ' . $e->getMessage());
}
?>