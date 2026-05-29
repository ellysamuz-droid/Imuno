<?php
/**
 * CONFIG.PHP - Database Connection & Authentication
 * Vercel-compatible: menggunakan cookie saja (tanpa session)
 */

// ============================================
// 1. DATABASE CONNECTION (TiDB)
// ============================================

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
// 2. INITIALIZE DATABASE CONNECTION
// ============================================

try {
    $dbInstance = Database::getInstance();
    $conn = $dbInstance->getConnection();
} catch (Exception $e) {
    error_log('❌ Database connection failed: ' . $e->getMessage());

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal terhubung ke database']);
        exit();
    }

    die('❌ Koneksi Database Gagal: ' . $e->getMessage());
}

// ============================================
// 3. COOKIE AUTH HELPERS
// ============================================

// Secret key untuk HMAC signature — ganti dengan string acak yang panjang
define('AUTH_SECRET', 'GANTI_DENGAN_RANDOM_STRING_PANJANG_DAN_RAHASIA_2024');
define('COOKIE_NAME', 'auth_data');
define('COOKIE_EXPIRE', 86400 * 7); // 7 hari

/**
 * Buat cookie auth yang ditandatangani (HMAC)
 */
function set_auth_cookie(int $user_id, string $user_email, string $user_role = 'user'): void
{
    $payload = json_encode([
        'user_id'    => $user_id,
        'user_email' => $user_email,
        'user_role'  => $user_role,
        'exp'        => time() + COOKIE_EXPIRE,
    ]);

    $encoded   = base64_encode($payload);
    $signature = hash_hmac('sha256', $encoded, AUTH_SECRET);
    $cookie    = $encoded . '.' . $signature;

    setcookie(COOKIE_NAME, $cookie, [
        'expires'  => time() + COOKIE_EXPIRE,
        'path'     => '/',
        'secure'   => true,   // Vercel selalu HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    // Langsung set ke $_COOKIE agar bisa dipakai di request yang sama
    $_COOKIE[COOKIE_NAME] = $cookie;
}

/**
 * Baca dan verifikasi cookie auth
 * Return array data user, atau null jika tidak valid / expired
 */
function get_auth_cookie(): ?array
{
    $cookie = $_COOKIE[COOKIE_NAME] ?? null;
    if (!$cookie) return null;

    $parts = explode('.', $cookie, 2);
    if (count($parts) !== 2) return null;

    [$encoded, $signature] = $parts;

    // Verifikasi signature
    $expected = hash_hmac('sha256', $encoded, AUTH_SECRET);
    if (!hash_equals($expected, $signature)) {
        error_log('Auth cookie: signature tidak valid');
        return null;
    }

    $payload = json_decode(base64_decode($encoded), true);
    if (!$payload) return null;

    // Cek expiry
    if (($payload['exp'] ?? 0) < time()) {
        error_log('Auth cookie: sudah expired');
        clear_auth_cookie();
        return null;
    }

    return $payload;
}

/**
 * Hapus cookie auth (logout)
 */
function clear_auth_cookie(): void
{
    setcookie(COOKIE_NAME, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($_COOKIE[COOKIE_NAME]);
}

// ============================================
// 4. HELPER FUNCTIONS - INPUT & VALIDATION
// ============================================

function sanitize_input($input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validate_email($email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function hash_password($password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verify_password($password, $hash): bool
{
    return password_verify($password, $hash);
}

// ============================================
// 5. HELPER FUNCTIONS - AUTHENTICATION
// ============================================

function is_logged_in(): bool
{
    return get_auth_cookie() !== null;
}

function get_user_id(): ?int
{
    $data = get_auth_cookie();
    return $data ? (int)$data['user_id'] : null;
}

function get_user_email(): ?string
{
    $data = get_auth_cookie();
    return $data ? $data['user_email'] : null;
}

function get_user_role(): ?string
{
    $data = get_auth_cookie();
    return $data ? $data['user_role'] : 'user';
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

function require_admin(): void
{
    require_login();

    if (get_user_role() !== 'admin') {
        header('Location: dashboard.php');
        exit();
    }
}

function logout(): void
{
    clear_auth_cookie();
    header('Location: login.php');
    exit();
}

function get_user_data($user_id)
{
    global $conn;

    $query = $conn->prepare(
        "SELECT id, username, email, tanggal_lahir, role, created_at FROM users WHERE id = ?"
    );
    if (!$query) return null;

    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();

    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

// ============================================
// 6. HELPER FUNCTIONS - RESPONSE
// ============================================

function json_error($message, $errors = null, $status_code = 400): void
{
    http_response_code($status_code);
    echo json_encode(['success' => false, 'message' => $message, 'errors' => $errors]);
    exit();
}

function json_success($message, $data = null, $status_code = 200): void
{
    http_response_code($status_code);
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit();
}

// ============================================
// 7. LOGGING HELPER
// ============================================

function log_activity($user_id, $action, $details = ''): void
{
    $timestamp = date('Y-m-d H:i:s');
    $message   = "[{$timestamp}] User ID: {$user_id} | Action: {$action} | Details: {$details}";
    error_log($message);
    // Tidak pakai file log karena Vercel filesystem tidak persistent
}