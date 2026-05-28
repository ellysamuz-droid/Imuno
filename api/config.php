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
        // ✅ Pakai $_ENV dulu, fallback ke getenv(), fallback ke hardcode
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
                'Koneksi ke TiDB Cloud gagal: ' . $mysqli->connect_error
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
    }

    public function execute(string $sql, string $types = '', array $params = []): int
    {
        $stmt         = $this->prepare($sql, $types, $params);
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        return $affectedRows;
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
            throw new RuntimeException('Prepare gagal: ' . $this->connection->error);
        }

        if ($types !== '' && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('Execute gagal: ' . $stmt->error);
        }

        return $stmt;
    }

    public function close(): void
    {
        $this->connection->close();
        self::$instance = null;
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new RuntimeException('Singleton tidak bisa di-unserialize.');
    }
}