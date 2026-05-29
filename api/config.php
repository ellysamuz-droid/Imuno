<?php
/**
 * CONFIG.PHP - Database Connection & Authentication
 * Semua helper functions untuk auth, validation, session
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
    
    error_log('✅ Database connection successful');
    
} catch (Exception $e) {
    error_log('❌ Database connection failed: ' . $e->getMessage());
    
    if (strpos($_SERVER['REQUEST_URI'], '.php') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Gagal terhubung ke database'
        ]);
        exit();
    }
    
    die('❌ Koneksi Database Gagal: ' . $e->getMessage());
}

// ============================================
// 3. SESSION CONFIGURATION
// ============================================

// Start session jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 days
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// ============================================
// 4. HELPER FUNCTIONS - INPUT & VALIDATION
// ============================================

/**
 * Sanitize input untuk prevent XSS
 */
function sanitize_input($input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function validate_email($email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password menggunakan bcrypt
 */
function hash_password($password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password terhadap hash
 */
function verify_password($password, $hash): bool
{
    return password_verify($password, $hash);
}

// ============================================
// 5. HELPER FUNCTIONS - AUTHENTICATION
// ============================================

/**
 * Set authentication cookie/session setelah login berhasil
 */
function set_auth_cookie($user_id, $user_email, $user_role = 'user'): void
{
    // Set session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_email'] = $user_email;
    $_SESSION['user_role'] = $user_role;
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Set cookie juga (backup)
    $cookie_value = json_encode([
        'user_id' => $user_id,
        'user_email' => $user_email,
        'user_role' => $user_role
    ]);
    setcookie('auth_token', base64_encode($cookie_value), time() + (86400 * 7), '/');
}

/**
 * Check apakah user sudah login
 */
function is_logged_in(): bool
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user ID
 */
function get_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user email
 */
function get_user_email(): ?string
{
    return $_SESSION['user_email'] ?? null;
}

/**
 * Get current user role
 */
function get_user_role(): ?string
{
    return $_SESSION['user_role'] ?? 'user';
}

/**
 * Require user harus login, jika tidak redirect ke login
 */
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Require user harus admin, jika tidak redirect ke dashboard
 */
function require_admin(): void
{
    require_login();
    
    if (get_user_role() !== 'admin') {
        header('Location: dashboard.php');
        exit();
    }
}

/**
 * Logout user - clear session dan cookie
 */
function logout(): void
{
    // Clear session
    $_SESSION = array();
    session_destroy();
    
    // Clear cookie
    setcookie('auth_token', '', time() - 3600, '/');
    
    // Redirect ke login
    header('Location: login.php');
    exit();
}

/**
 * Get user data dari database
 */
function get_user_data($user_id)
{
    global $conn;
    
    $query = $conn->prepare("SELECT id, username, email, tanggal_lahir, role, created_at FROM users WHERE id = ?");
    if (!$query) {
        return null;
    }
    
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

// ============================================
// 6. HELPER FUNCTIONS - ERROR HANDLING
// ============================================

/**
 * Return JSON error response
 */
function json_error($message, $errors = null, $status_code = 400): void
{
    http_response_code($status_code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'errors' => $errors
    ]);
    exit();
}

/**
 * Return JSON success response
 */
function json_success($message, $data = null, $status_code = 200): void
{
    http_response_code($status_code);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// ============================================
// 7. LOGGING HELPER
// ============================================

/**
 * Log activity ke file
 */
function log_activity($user_id, $action, $details = ''): void
{
    $log_file = __DIR__ . '/logs/activity.log';
    $log_dir = dirname($log_file);
    
    // Create logs directory jika tidak ada
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $message = "[{$timestamp}] User ID: {$user_id} | Action: {$action} | Details: {$details}\n";
    
    error_log($message, 3, $log_file);
}

?>