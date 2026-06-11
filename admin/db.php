<?php
// ============================================================
// CSSI Portal v4.0 — Conexiune MySQL
// ============================================================
// IMPORTANT: Actualizeaza credentialele dupa creare in cPanel
// cPanel → MySQL Databases → Create Database + User
// ============================================================

// Încarcă secretele din fișier extern (gitignored) sau din variabile de mediu
$secretsFile = __DIR__ . '/../secrets.php';
$SECRETS = file_exists($secretsFile) ? require $secretsFile : [];

define('DB_HOST', 'localhost');
define('DB_NAME', 'r101042brea_cssi');  // cPanel prefix + db name
define('DB_USER', 'r101042brea_cssi');  // cPanel prefix + user
// Prioritate: variabilă de mediu > fișier secrets > fallback (DOAR pentru compat tranziție)
define('DB_PASS', getenv('CSSI_DB_PASS') ?: ($SECRETS['DB_PASS'] ?? 'cssi-install-2026'));
define('DB_CHARSET', 'utf8mb4');

// API keys externe
define('ZERNIO_KEY', getenv('CSSI_ZERNIO_KEY') ?: ($SECRETS['ZERNIO_KEY'] ?? ''));

// Claude / Anthropic API — generare automată text postări marketing
define('ANTHROPIC_KEY', getenv('CSSI_ANTHROPIC_KEY') ?: ($SECRETS['ANTHROPIC_KEY'] ?? ''));
if (!defined('CLAUDE_MODEL') && !empty($SECRETS['CLAUDE_MODEL'])) define('CLAUDE_MODEL', $SECRETS['CLAUDE_MODEL']);

// Recovery token — pentru endpoint-uri de recuperare admin (ex: deblocare cont)
define('RECOVERY_TOKEN', getenv('CSSI_RECOVERY_TOKEN') ?: ($SECRETS['RECOVERY_TOKEN'] ?? ''));

// SMSLink.ro — trimitere SMS automat (cereri recenzii, notificări)
define('SMSLINK_KEY', getenv('CSSI_SMSLINK_KEY') ?: ($SECRETS['SMSLINK_KEY'] ?? ''));
define('SMSLINK_SENDER', getenv('CSSI_SMSLINK_SENDER') ?: ($SECRETS['SMSLINK_SENDER'] ?? 'CSSI'));

// Upload paths
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', '/admin/uploads/');
define('PDF_DIR', UPLOAD_DIR . 'oferte/');
define('PROIECTE_DIR', UPLOAD_DIR . 'proiecte/');

// Conectare PDO
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'error' => 'DB connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// Generare ID unic secvential
function nextId($cheie, $prefix = '', $pad = 4) {
    $db = getDB();
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT valoare FROM secvente WHERE cheie = ? FOR UPDATE");
        $stmt->execute([$cheie]);
        $row = $stmt->fetch();
        $next = ($row ? $row['valoare'] : 0) + 1;
        
        $db->prepare("UPDATE secvente SET valoare = ? WHERE cheie = ?")
           ->execute([$next, $cheie]);
        $db->commit();
        
        if ($prefix) {
            return $prefix . str_pad($next, $pad, '0', STR_PAD_LEFT);
        }
        return (string) $next;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

// Helper: raspuns JSON
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Helper: get POST body as array
function getPostData() {
    $contentType = (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '');
    if (strpos($contentType, 'application/json') !== false) {
        return json_decode(file_get_contents('php://input'), true) ?: [];
    }
    return $_POST;
}

// Creare directoare uploads daca nu exista
foreach ([UPLOAD_DIR, PDF_DIR, PROIECTE_DIR] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
