<?php
// ============================================================
// CSSI Portal v4.0 — REST API
// ============================================================
// Endpoint unic: /admin/api.php?action=...
// GET  = citire date
// POST = creare/modificare/stergere
// ============================================================

// CORS — restrânge la origin-ul portalului (Allow-Credentials cere origin specific)
$allowedOrigins = ['https://cssi.ro', 'https://www.cssi.ro'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
date_default_timezone_set('Europe/Bucharest');

$action = (isset($_GET['action']) ? $_GET['action'] : ((isset($_POST['action']) ? $_POST['action'] : '')));
$data = getPostData();

// ─── Helper: schemă oferte (idempotent migration) ─────────────
function ensureOferteColumns($db) {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        $cols = $db->query("SHOW COLUMNS FROM oferte")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('archived_at', $cols)) {
            $db->exec("ALTER TABLE oferte ADD COLUMN archived_at DATETIME NULL DEFAULT NULL");
            try { $db->exec("ALTER TABLE oferte ADD INDEX idx_archived (archived_at)"); } catch(Exception $e){}
        }
        if (!in_array('expires_at', $cols)) {
            $db->exec("ALTER TABLE oferte ADD COLUMN expires_at DATE NULL DEFAULT NULL");
            try { $db->exec("ALTER TABLE oferte ADD INDEX idx_expires (expires_at)"); } catch(Exception $e){}
        }
        if (!in_array('mentiuni', $cols)) {
            $db->exec("ALTER TABLE oferte ADD COLUMN mentiuni TEXT NULL DEFAULT NULL");
        }
    } catch (Exception $e) {
        // Loghez ca să pot debug (apare în error_log)
        error_log('ensureOferteColumns FAILED: ' . $e->getMessage());
    }
}
// Debug helper: returneaza listă coloane oferte (admin only — pentru troubleshoot)
function debugOferteColumns($db) {
    try { return $db->query("SHOW COLUMNS FROM oferte")->fetchAll(PDO::FETCH_COLUMN); }
    catch (Exception $e) { return ['ERR' => $e->getMessage()]; }
}

// Auto-expire oferte: Trimisa/In_discutie cu expires_at < azi → Expirata
function autoExpireOferte($db) {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $db->exec("UPDATE oferte SET status='Expirata' WHERE status IN ('Draft','Trimisa','In_discutie') AND expires_at IS NOT NULL AND expires_at < CURDATE() AND archived_at IS NULL");
    } catch (Exception $e) { /* silent */ }
}

// Schema tabel drafturi oferte (autosave server-side în calculator)
// Tabel separat — NU interferează cu /oferte.html, export CSV, stats, pipeline
function ensureOferteDrafturiSchema($db) {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS oferte_drafturi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_nume VARCHAR(255) NULL,
            obiectiv TEXT NULL,
            total_cu_tva DECIMAL(15,2) DEFAULT 0,
            payload LONGTEXT,
            created_by VARCHAR(100) NULL,
            created_by_name VARCHAR(255) NULL,
            updated_by VARCHAR(100) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_updated (updated_at),
            INDEX idx_creator (created_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Exception $e) {
        error_log('ensureOferteDrafturiSchema FAILED: ' . $e->getMessage());
    }
}

// Schema contracte: creeaza daca nu exista + ALTER pentru coloanele lipsa
// (idempotent — protejeaza pt cazul cand exista deja o tabela 'contracte' veche)
function ensureContracteSchema($db) {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS contracte (
            id INT PRIMARY KEY AUTO_INCREMENT,
            contract_nr VARCHAR(40),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        // Adaug coloane lipsa (idempotent — verific cu SHOW COLUMNS)
        $cols = array_column($db->query("SHOW COLUMNS FROM contracte")->fetchAll(PDO::FETCH_ASSOC), 'Field');
        $cols = array_flip($cols);
        $alters = [
            'contract_nr'           => "ADD COLUMN contract_nr VARCHAR(40)",
            'oferta_id'             => "ADD COLUMN oferta_id INT NULL",
            'proiect_id'            => "ADD COLUMN proiect_id INT NULL",
            'client_id'             => "ADD COLUMN client_id INT NULL",
            'token'                 => "ADD COLUMN token VARCHAR(64)",
            'tip_client'            => "ADD COLUMN tip_client VARCHAR(2) DEFAULT 'PF'",
            'status'                => "ADD COLUMN status VARCHAR(20) DEFAULT 'asteapta_date'",
            'date_completate'       => "ADD COLUMN date_completate TEXT NULL",
            'adresa_instalare'      => "ADD COLUMN adresa_instalare VARCHAR(500)",
            'avans_procent'         => "ADD COLUMN avans_procent DECIMAL(5,2) DEFAULT 35",
            'termen_plata_zile'     => "ADD COLUMN termen_plata_zile INT DEFAULT 15",
            'durata_executie_zile'  => "ADD COLUMN durata_executie_zile INT DEFAULT 20",
            'garantie_luni'         => "ADD COLUMN garantie_luni INT DEFAULT 24",
            'valoare_net'           => "ADD COLUMN valoare_net DECIMAL(12,2) DEFAULT 0",
            'valoare_tva'           => "ADD COLUMN valoare_tva DECIMAL(12,2) DEFAULT 0",
            'valoare_total'         => "ADD COLUMN valoare_total DECIMAL(12,2) DEFAULT 0",
            'note'                  => "ADD COLUMN note TEXT",
            'generat_doc_path'      => "ADD COLUMN generat_doc_path VARCHAR(255)",
            'generat_pdf_path'      => "ADD COLUMN generat_pdf_path VARCHAR(255)",
            'created_by'            => "ADD COLUMN created_by VARCHAR(60)",
            'completat_la'          => "ADD COLUMN completat_la DATETIME NULL",
            'generat_la'            => "ADD COLUMN generat_la DATETIME NULL",
            'updated_at'            => "ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
            'token_expires_at'      => "ADD COLUMN token_expires_at DATETIME NULL",
            'locked_resubmit'       => "ADD COLUMN locked_resubmit TINYINT(1) DEFAULT 0",
            'gdpr_consent_at'       => "ADD COLUMN gdpr_consent_at DATETIME NULL",
            'gdpr_consent_ip'       => "ADD COLUMN gdpr_consent_ip VARCHAR(45) NULL",
        ];
        foreach ($alters as $col => $sql) {
            if (!isset($cols[$col])) {
                try { $db->exec("ALTER TABLE contracte $sql"); } catch (Exception $e) { error_log("contracte ALTER $col: " . $e->getMessage()); }
            }
        }
        // status era ENUM cu valori vechi — extind la VARCHAR pentru a accepta noile valori
        try { $db->exec("ALTER TABLE contracte MODIFY status VARCHAR(20) DEFAULT 'asteapta_date'"); } catch (Exception $e) {}
        // Relax NOT NULL pe coloanele vechi care nu mai sunt obligatorii in noul flow
        try { $db->exec("ALTER TABLE contracte MODIFY proiect_id INT NULL"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE contracte MODIFY contract_id VARCHAR(40) NULL"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE contracte MODIFY data_semnare DATE NULL"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE contracte MODIFY valoare DECIMAL(12,2) NULL"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE contracte MODIFY conditii_plata TEXT NULL"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE contracte MODIFY pdf_path VARCHAR(255) NULL"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE contracte MODIFY clauze TEXT NULL"); } catch (Exception $e) {}
        // Adaug index-uri (best-effort)
        try { $db->exec("ALTER TABLE contracte ADD INDEX idx_status (status)"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE contracte ADD INDEX idx_token (token)"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE contracte ADD INDEX idx_oferta (oferta_id)"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE contracte ADD INDEX idx_client (client_id)"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE contracte ADD UNIQUE KEY uniq_contract_nr (contract_nr)"); } catch (Exception $e) {}
        try { $db->exec("ALTER TABLE contracte ADD UNIQUE KEY uniq_token (token)"); } catch (Exception $e) {}
    } catch (Exception $e) {
        error_log('ensureContracteSchema FAILED: ' . $e->getMessage());
    }
}

// ─── ENCRYPTION pt date sensibile (CNP, CI seria/nr) ─────────────
// Folosim AES-256-GCM cu cheie din secrets.php (CONTRACT_ENCRYPTION_KEY)
// Fallback graceful: daca cheia lipseste, storage in clar (cu log warning)
function encryptSensitive($plain) {
    if ($plain === '' || $plain === null) return '';
    if (!defined('CONTRACT_ENCRYPTION_KEY') || strlen(CONTRACT_ENCRYPTION_KEY) < 32) {
        error_log('CONTRACT_ENCRYPTION_KEY missing — date sensibile in clear text');
        return $plain;  // fallback graceful
    }
    $key = substr(hash('sha256', CONTRACT_ENCRYPTION_KEY, true), 0, 32);
    $iv  = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) return $plain;
    return 'enc:' . base64_encode($iv . $tag . $cipher);
}
function decryptSensitive($stored) {
    if ($stored === '' || $stored === null) return '';
    if (substr($stored, 0, 4) !== 'enc:') return $stored;  // plain fallback
    if (!defined('CONTRACT_ENCRYPTION_KEY') || strlen(CONTRACT_ENCRYPTION_KEY) < 32) return '[CHEIE_LIPSA]';
    $raw = base64_decode(substr($stored, 4));
    if (strlen($raw) < 12 + 16) return '[CORRUPT]';
    $iv  = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $cipher = substr($raw, 28);
    $key = substr(hash('sha256', CONTRACT_ENCRYPTION_KEY, true), 0, 32);
    $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return $plain === false ? '[DECRYPT_FAIL]' : $plain;
}

// ─── RATE LIMITING pt endpoint-uri publice contracte ─────────────
function ensureContractRateLimitTable($db) {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS contract_rate_limits (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            ip VARCHAR(45) NOT NULL,
            action VARCHAR(40) NOT NULL,
            ts DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY idx_ip_action_ts (ip, action, ts)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Exception $e) {}
}
// Verifica rate limit + inregistreaza cererea curenta. Daca depaseste, throw.
function checkContractRateLimit($db, $action, $maxPerMinute = 10) {
    ensureContractRateLimitTable($db);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (strlen($ip) > 45) $ip = substr($ip, 0, 45);
    try {
        // Cleanup vechi (> 1h) best-effort
        $db->exec("DELETE FROM contract_rate_limits WHERE ts < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $stmt = $db->prepare("SELECT COUNT(*) FROM contract_rate_limits WHERE ip = ? AND action = ? AND ts > DATE_SUB(NOW(), INTERVAL 1 MINUTE)");
        $stmt->execute([$ip, $action]);
        $count = intval($stmt->fetchColumn());
        if ($count >= $maxPerMinute) {
            jsonResponse(['success' => false, 'error' => 'Prea multe cereri. Reincearca in 1 minut.'], 429);
        }
        $db->prepare("INSERT INTO contract_rate_limits (ip, action) VALUES (?, ?)")->execute([$ip, $action]);
    } catch (Exception $e) { /* nu blocheaza pe eroare DB */ }
}

// ─── AUDIT LOG ─────────────────────────────────────────────────
function ensureContractAccessLog($db) {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS contract_access_log (
            id BIGINT PRIMARY KEY AUTO_INCREMENT,
            contract_id INT NOT NULL,
            action VARCHAR(40) NOT NULL,
            user_id VARCHAR(60),
            ip VARCHAR(45),
            user_agent VARCHAR(255),
            details VARCHAR(500),
            ts DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY idx_contract (contract_id),
            KEY idx_ts (ts)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Exception $e) {}
}
function logContractAccess($db, $contractId, $action, $details = '') {
    ensureContractAccessLog($db);
    $ip = substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45);
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $u  = currentUser();
    $userId = $u ? $u['username'] : 'public';
    try {
        $db->prepare("INSERT INTO contract_access_log (contract_id, action, user_id, ip, user_agent, details) VALUES (?,?,?,?,?,?)")
           ->execute([$contractId, $action, $userId, $ip, $ua, substr($details, 0, 500)]);
    } catch (Exception $e) {}
}

// Helper debug — returneaza coloanele actuale (cu Type pt diagnoza ENUM)
function debugContracteColumns($db) {
    try {
        $rows = $db->query("SHOW COLUMNS FROM contracte")->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function($r){ return ['col'=>$r['Field'],'type'=>$r['Type'],'null'=>$r['Null'],'default'=>$r['Default']]; }, $rows);
    } catch (Exception $e) { return ['ERR' => $e->getMessage()]; }
}

// Genereaza token securizat 32 chars URL-safe
function generateContractToken() {
    return bin2hex(random_bytes(16)); // 32 hex chars
}

// Date PRESTATOR (fixe — pot fi mutate in setari mai tarziu)
function prestatorData() {
    return [
        'denumire'   => 'BREAK SISTEMS SRL',
        'sediu'      => 'Brasov, str. Bisericii Romane Nr. 42 Ap.2, judetul Brasov',
        'reg_com'    => 'J200800843087',
        'cif'        => 'RO 23576950',
        'cont_iban'  => 'RO50 BTRL 0080 1202 U749 90XX',
        'banca'      => 'Banca Transilvania Brasov',
        'reprezentant' => 'Diaconu Mihai',
        'email'      => 'office@breaksistems.ro',
        'telefon'    => '',
    ];
}

// Schema proiecte: extinde ENUM status cu 'Interventie' (idempotent)
function ensureProiecteSchema($db) {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    try {
        // Vedem ENUM-ul curent al coloanei status
        $row = $db->query("SHOW COLUMNS FROM proiecte LIKE 'status'")->fetch();
        if ($row && stripos($row['Type'], "'Interventie'") === false) {
            // Construim noul ENUM = vechiul + Interventie inainte de Anulat
            $db->exec("ALTER TABLE proiecte MODIFY status ENUM('Lead','Oferta','Contract','Proiectare','Executie','Receptie','Facturat','Mentenanta','Interventie','Anulat') NOT NULL DEFAULT 'Lead'");
        }
    } catch (Exception $e) {
        error_log('ensureProiecteSchema FAILED: ' . $e->getMessage());
    }
}

// Calculează expires_at din data_oferta + valabilitate ("4 zile", "30 zile" etc)
function calcExpiresAt($dataOferta, $valab) {
    if (!$dataOferta) return null;
    $days = 30;
    if (preg_match('/(\d+)\s*zi/iu', $valab, $m)) $days = intval($m[1]);
    if ($days <= 0 || $days > 365) $days = 30;
    try { $d = new DateTime($dataOferta); $d->modify("+{$days} days"); return $d->format('Y-m-d'); }
    catch (Exception $e) { return null; }
}

try {
    $db = getDB();

    // ─── CSRF PROTECTION ─────────────────────────────────────────
    // Verifică pe toate POST-urile (login inclus — anti CSRF login)
    requireCsrfProtection();

    // ─── PROTECȚIE GLOBALĂ ───────────────────────────────────────
    // Toate endpoint-urile necesită autentificare, EXCEPT lista publică
    if (!in_array($action, publicActions(), true) && $action !== '_resetLock') {
        requireAuth();
    }

    switch ($action) {

        // ══════════════════════════════════════
        // RECOVERY — deblocare cont cu RECOVERY_TOKEN (din secrets.php)
        // Pentru cazuri de urgență când admin e blocat din rate-limit
        // ══════════════════════════════════════
        case '_resetLock':
            $token = isset($data['token']) ? trim($data['token']) : '';
            $expected = defined('RECOVERY_TOKEN') ? RECOVERY_TOKEN : '';
            if (!$expected || strlen($expected) < 16) {
                jsonResponse(['success' => false, 'error' => 'RECOVERY_TOKEN nesetat în secrets.php (min 16 chars)'], 503);
            }
            // Comparare timing-safe
            if (!hash_equals($expected, $token)) {
                usleep(800000);
                jsonResponse(['success' => false, 'error' => 'Token invalid'], 401);
            }
            $username = isset($data['username']) ? strtolower(trim($data['username'])) : '';
            if ($username) {
                $stmt = $db->prepare("UPDATE users SET failed_attempts=0, locked_until=NULL WHERE username=?");
                $stmt->execute([$username]);
                jsonResponse(['success' => true, 'unlocked' => $stmt->rowCount(), 'user' => $username]);
            } else {
                $stmt = $db->exec("UPDATE users SET failed_attempts=0, locked_until=NULL");
                jsonResponse(['success' => true, 'unlocked' => $stmt, 'user' => '*all*']);
            }
            break;

        // ══════════════════════════════════════
        // AUTH — login / logout / me / ping
        // ══════════════════════════════════════
        case 'login':
            $username = isset($data['username']) ? $data['username'] : '';
            $password = isset($data['password']) ? $data['password'] : '';
            $res = attemptLogin($db, $username, $password);
            $code = !$res['success'] ? (($res['code'] ?? '') === 'LOCKED' ? 429 : 401) : 200;
            jsonResponse($res, $code);
            break;

        case 'logout':
            doLogout();
            jsonResponse(['success' => true]);
            break;

        case 'me':
            $u = currentUser();
            jsonResponse(['success' => (bool)$u, 'user' => $u]);
            break;

        case 'ping':
            jsonResponse(['success' => true, 'time' => date('c'), 'authenticated' => (bool)currentUser()]);
            break;

        // ══════════════════════════════════════
        // MODULE ACCESS — verificare access user pe pagină
        // ══════════════════════════════════════
        case 'getMyModules':
            requireAuth();
            $u = currentUser();
            jsonResponse(['success' => true, 'modules' => getAllowedModules($db, $u), 'role' => $u['role'], 'is_tehnician' => $u['is_tehnician']]);
            break;

        case 'checkModuleAccess':
            requireAuth();
            $module = isset($_GET['module']) ? trim($_GET['module']) : '';
            if (!$module) jsonResponse(['success' => false, 'error' => 'module obligatoriu'], 400);
            $u = currentUser();
            $allowed = getAllowedModules($db, $u);
            if ($module === 'utilizatori' && !isAdmin()) {
                jsonResponse(['success' => false, 'allowed' => false, 'error' => 'Doar admin'], 403);
            }
            if (in_array($module, $allowed, true) || isAdmin()) {
                jsonResponse(['success' => true, 'allowed' => true]);
            }
            jsonResponse(['success' => false, 'allowed' => false, 'error' => 'Acces interzis la modulul "'.$module.'"'], 403);
            break;

        case 'adminGetUserModules':
            requireAdmin();
            $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
            if (!$userId) jsonResponse(['success' => false, 'error' => 'user_id obligatoriu'], 400);
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $u = $stmt->fetch();
            if (!$u) jsonResponse(['success' => false, 'error' => 'User inexistent'], 404);
            $u['is_tehnician'] = (bool)$u['is_tehnician'];
            $allowed  = getAllowedModules($db, $u);
            $defaults = defaultModulesForUser($u);
            $stmtC = $db->prepare("SELECT modules FROM user_config WHERE user_id = ?");
            $stmtC->execute([$u['username']]);
            $row = $stmtC->fetch();
            $hasOverride = $row && !empty($row['modules']) && $row['modules'] !== '[]';
            jsonResponse(['success' => true, 'data' => [
                'all_modules' => allModules(),
                'allowed'     => $allowed,
                'defaults'    => $defaults,
                'has_override'=> $hasOverride,
            ]]);
            break;

        case 'adminSetUserModules':
            requireAdmin();
            $userId = isset($data['user_id']) ? intval($data['user_id']) : 0;
            $modules = isset($data['modules']) && is_array($data['modules']) ? $data['modules'] : null;
            $useDefaults = !empty($data['use_defaults']);
            if (!$userId) jsonResponse(['success' => false, 'error' => 'user_id obligatoriu'], 400);
            $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $u = $stmt->fetch();
            if (!$u) jsonResponse(['success' => false, 'error' => 'User inexistent'], 404);
            $db->exec("CREATE TABLE IF NOT EXISTS user_config (
                user_id VARCHAR(60) PRIMARY KEY,
                modules TEXT, stages TEXT, primary_stages TEXT, focus VARCHAR(255),
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            if ($useDefaults) {
                $db->prepare("DELETE FROM user_config WHERE user_id = ?")->execute([$u['username']]);
                jsonResponse(['success' => true, 'reset' => true]);
            } else {
                $valid = array_values(array_filter($modules ?: [], function($m){ return in_array($m, allModules(), true) && $m !== 'utilizatori'; }));
                $db->prepare("INSERT INTO user_config (user_id, modules) VALUES (?, ?) ON DUPLICATE KEY UPDATE modules = VALUES(modules)")
                   ->execute([$u['username'], json_encode($valid)]);
                jsonResponse(['success' => true, 'modules' => $valid]);
            }
            break;

        // ══════════════════════════════════════
        // USERS — schimbare parolă proprie + admin user management
        // ══════════════════════════════════════
        case 'changeMyPassword':
            // Self-service — user-ul logat își schimbă parola
            $u = currentUser();
            $current = isset($data['current']) ? $data['current'] : '';
            $new     = isset($data['new']) ? $data['new'] : '';
            if (!$current || !$new) jsonResponse(['success' => false, 'error' => 'Parolă curentă + nouă obligatorii'], 400);
            if (strlen($new) < 6)   jsonResponse(['success' => false, 'error' => 'Parola nouă min. 6 caractere'], 400);
            if ($current === $new)  jsonResponse(['success' => false, 'error' => 'Parola nouă trebuie să fie diferită'], 400);

            $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ? AND active = 1");
            $stmt->execute([$u['id']]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($current, $row['password_hash'])) {
                usleep(800000);
                jsonResponse(['success' => false, 'error' => 'Parola curentă incorectă'], 401);
            }
            $newHash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 11]);
            $db->prepare("UPDATE users SET password_hash=?, failed_attempts=0, locked_until=NULL WHERE id=?")
               ->execute([$newHash, $u['id']]);
            jsonResponse(['success' => true, 'message' => 'Parolă schimbată']);
            break;

        case 'adminListUsers':
            requireAdmin();
            $rows = $db->query("SELECT id, username, display_name, role, is_tehnician, active, last_login, failed_attempts, locked_until, created_at FROM users ORDER BY id")->fetchAll();
            // Cast bool-uri pentru frontend
            foreach ($rows as &$r) {
                $r['is_tehnician'] = (bool)$r['is_tehnician'];
                $r['active'] = (bool)$r['active'];
                $r['locked'] = !empty($r['locked_until']) && strtotime($r['locked_until']) > time();
            }
            unset($r);
            jsonResponse(['success' => true, 'data' => $rows]);
            break;

        case 'adminSetPassword':
            requireAdmin();
            $userId = isset($data['user_id']) ? intval($data['user_id']) : 0;
            $newPw  = isset($data['new']) ? $data['new'] : '';
            if (!$userId || strlen($newPw) < 6) jsonResponse(['success' => false, 'error' => 'user_id + parolă (min 6 chars) obligatorii'], 400);
            $newHash = password_hash($newPw, PASSWORD_BCRYPT, ['cost' => 11]);
            $stmt = $db->prepare("UPDATE users SET password_hash=?, failed_attempts=0, locked_until=NULL WHERE id=?");
            $stmt->execute([$newHash, $userId]);
            jsonResponse(['success' => true, 'affected' => $stmt->rowCount()]);
            break;

        case 'adminUpdateUser':
            requireAdmin();
            $userId = isset($data['user_id']) ? intval($data['user_id']) : 0;
            if (!$userId) jsonResponse(['success' => false, 'error' => 'user_id obligatoriu'], 400);
            $allowedFields = ['display_name','role','is_tehnician','active'];
            $fields = []; $values = [];
            foreach ($allowedFields as $f) {
                if (array_key_exists($f, $data)) {
                    $fields[] = "$f = ?";
                    $values[] = in_array($f, ['is_tehnician','active'], true) ? (!empty($data[$f]) ? 1 : 0) : $data[$f];
                }
            }
            if (!$fields) jsonResponse(['success' => false, 'error' => 'Nimic de modificat'], 400);
            // Protecție: ultimul admin activ nu poate fi dezactivat sau scos din rol admin
            if ((isset($data['active']) && empty($data['active'])) || (isset($data['role']) && $data['role'] !== 'admin')) {
                $cur = $db->prepare("SELECT role, active FROM users WHERE id=?"); $cur->execute([$userId]); $crow = $cur->fetch();
                if ($crow && $crow['role'] === 'admin' && $crow['active']) {
                    $cnt = intval($db->query("SELECT COUNT(*) FROM users WHERE role='admin' AND active=1")->fetchColumn());
                    if ($cnt <= 1) jsonResponse(['success' => false, 'error' => 'Nu poți dezactiva sau scoate ultimul admin'], 400);
                }
            }
            $values[] = $userId;
            $db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id=?")->execute($values);
            jsonResponse(['success' => true]);
            break;

        case 'adminUnlockUser':
            requireAdmin();
            $userId = isset($data['user_id']) ? intval($data['user_id']) : 0;
            if (!$userId) jsonResponse(['success' => false, 'error' => 'user_id obligatoriu'], 400);
            $db->prepare("UPDATE users SET failed_attempts=0, locked_until=NULL WHERE id=?")->execute([$userId]);
            jsonResponse(['success' => true]);
            break;

        case 'adminCreateUser':
            requireAdmin();
            $username    = strtolower(trim($data['username'] ?? ''));
            $password    = isset($data['password']) ? $data['password'] : '';
            $displayName = trim($data['display_name'] ?? '');
            $role        = trim($data['role'] ?? 'tech');
            $isTeh       = !empty($data['is_tehnician']) ? 1 : 0;
            if (!preg_match('/^[a-z0-9_-]{3,30}$/', $username)) jsonResponse(['success' => false, 'error' => 'Username invalid (3-30 caractere, doar a-z, 0-9, _ -)'], 400);
            if (strlen($password) < 6) jsonResponse(['success' => false, 'error' => 'Parolă min. 6 caractere'], 400);
            $check = $db->prepare("SELECT id FROM users WHERE username=?");
            $check->execute([$username]);
            if ($check->fetch()) jsonResponse(['success' => false, 'error' => 'Username deja folosit'], 400);
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 11]);
            $stmt = $db->prepare("INSERT INTO users (username, password_hash, display_name, role, is_tehnician) VALUES (?,?,?,?,?)");
            $stmt->execute([$username, $hash, $displayName ?: ucfirst($username), $role, $isTeh]);
            jsonResponse(['success' => true, 'id' => intval($db->lastInsertId())]);
            break;
        // ══════════════════════════════════════
        // CLIENTI
        // ══════════════════════════════════════
        case 'getClienti':
            $search = (isset($_GET['search']) ? $_GET['search'] : '');
            $sql = "SELECT * FROM clienti";
            $params = [];
            if ($search) {
                $sql .= " WHERE nume LIKE ? OR telefon LIKE ? OR cui_cnp LIKE ? OR email LIKE ?";
                $s = "%$search%";
                $params = [$s, $s, $s, $s];
            }
            $sql .= " ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'getClient':
            $id = (isset($_GET['id']) ? $_GET['id'] : 0);
            $stmt = $db->prepare("SELECT * FROM clienti WHERE id = ? OR client_id = ?");
            $stmt->execute([$id, $id]);
            $row = $stmt->fetch();
            jsonResponse($row ? ['success' => true, 'data' => $row] : ['success' => false, 'error' => 'Client negasit']);
            break;

        case 'createClient':
            $nume = isset($data['nume']) ? trim($data['nume']) : '';
            $cui = isset($data['cui_cnp']) ? trim($data['cui_cnp']) : '';
            
            // Verifică dacă clientul există deja (după CUI sau nume exact)
            $existing = null;
            if ($cui) {
                $stmt = $db->prepare("SELECT id, client_id FROM clienti WHERE cui_cnp = ? LIMIT 1");
                $stmt->execute([$cui]);
                $existing = $stmt->fetch();
            }
            if (!$existing && $nume) {
                $stmt = $db->prepare("SELECT id, client_id FROM clienti WHERE LOWER(TRIM(nume)) = LOWER(?) LIMIT 1");
                $stmt->execute([$nume]);
                $existing = $stmt->fetch();
            }
            
            if ($existing) {
                // Client existent — actualizează datele dacă sunt noi
                $updates = [];
                $vals = [];
                foreach (['telefon','email','adresa','oras','persoana_contact'] as $f) {
                    if (!empty($data[$f])) {
                        $updates[] = "$f = CASE WHEN $f = '' OR $f IS NULL THEN ? ELSE $f END";
                        $vals[] = $data[$f];
                    }
                }
                if ($updates) {
                    $vals[] = $existing['id'];
                    $db->prepare("UPDATE clienti SET " . implode(', ', $updates) . " WHERE id = ?")->execute($vals);
                }
                jsonResponse(['success' => true, 'id' => $existing['id'], 'client_id' => $existing['client_id'], 'existing' => true]);
            } else {
                // Client nou
                $clientId = nextId('client_seq', 'CLI-', 4);
                $stmt = $db->prepare("INSERT INTO clienti (client_id, nume, cui_cnp, telefon, email, adresa, oras, judet, persoana_contact, tip, note) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([
                    $clientId, $nume, $cui,
                    (isset($data['telefon']) ? $data['telefon'] : ''),
                    (isset($data['email']) ? $data['email'] : ''),
                    (isset($data['adresa']) ? $data['adresa'] : ''),
                    (isset($data['oras']) ? $data['oras'] : 'Brașov'),
                    (isset($data['judet']) ? $data['judet'] : 'Brașov'),
                    (isset($data['persoana_contact']) ? $data['persoana_contact'] : ''),
                    (isset($data['tip']) ? $data['tip'] : 'Firma'),
                    (isset($data['note']) ? $data['note'] : '')]);
                $insertId = $db->lastInsertId();
                jsonResponse(['success' => true, 'id' => $insertId, 'client_id' => $clientId, 'existing' => false]);
            }
            break;

        case 'updateClient':
            $id = (isset($data['id']) ? $data['id'] : 0);
            $fields = [];
            $values = [];
            foreach (['nume','cui_cnp','telefon','email','adresa','oras','judet','persoana_contact','tip','note'] as $f) {
                if (isset($data[$f])) {
                    $fields[] = "$f = ?";
                    $values[] = $data[$f];
                }
            }
            if ($fields && $id) {
                $values[] = $id;
                $db->prepare("UPDATE clienti SET " . implode(', ', $fields) . " WHERE id = ?")->execute($values);
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Lipsesc date'], 400);
            }
            break;

        case 'deleteClient':
            // Doar admin poate șterge clienți (operațiune ireversibilă)
            requireAdmin();
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) jsonResponse(['success' => false, 'error' => 'id client obligatoriu'], 400);

            $cli = $db->prepare("SELECT id, nume FROM clienti WHERE id = ?");
            $cli->execute([$id]);
            $cliRow = $cli->fetch();
            if (!$cliRow) jsonResponse(['success' => false, 'error' => 'Client negăsit'], 404);

            // ─── GARDĂ DE SIGURANȚĂ ──────────────────────────────────
            // Ștergerea cascadă e permisă DOAR pentru clienți "morți":
            // fără oferte acceptate și fără proiecte care au depășit faza
            // de Lead/Ofertă (Anulat e ok). Asta protejează datele reale.
            $cntSafe = function($table, $where) use ($db, $id) {
                try {
                    $st = $db->prepare("SELECT COUNT(*) FROM $table WHERE $where");
                    $st->execute([$id]);
                    return intval($st->fetchColumn());
                } catch (Exception $e) { return 0; }
            };
            $nOferteAcceptate = $cntSafe('oferte', "client_id = ? AND status = 'Acceptata'");
            $nProiecteActive  = $cntSafe('proiecte', "client_id = ? AND status NOT IN ('Lead','Oferta','Anulat')");
            if ($nOferteAcceptate > 0 || $nProiecteActive > 0) {
                $motive = [];
                if ($nOferteAcceptate) $motive[] = "$nOferteAcceptate ofertă(e) acceptată(e)";
                if ($nProiecteActive)  $motive[] = "$nProiecteActive proiect(e) în lucru";
                jsonResponse([
                    'success' => false,
                    'error'   => 'Clientul "' . $cliRow['nume'] . '" are ' . implode(' și ', $motive) .
                                 '. Nu poate fi șters automat — gestionează manual aceste înregistrări.',
                    'code'    => 'HAS_ACTIVITY'
                ], 409);
            }

            // ─── CASCADĂ ─────────────────────────────────────────────
            // Best-effort per statement (unele tabele pot lipsi pe medii diferite),
            // în ordinea dependențelor: copii → părinți → client.
            $delIn = function($table, $col, array $ids) use ($db) {
                if (!$ids) return 0;
                try {
                    $ph = implode(',', array_fill(0, count($ids), '?'));
                    $st = $db->prepare("DELETE FROM $table WHERE $col IN ($ph)");
                    $st->execute(array_values($ids));
                    return $st->rowCount();
                } catch (Exception $e) { return 0; }
            };
            $delEq = function($table, $col, $val) use ($db) {
                try {
                    $st = $db->prepare("DELETE FROM $table WHERE $col = ?");
                    $st->execute([$val]);
                    return $st->rowCount();
                } catch (Exception $e) { return 0; }
            };

            // Colectează ID-urile proiectelor (numeric) + codurile (proiect_id text)
            $proiectIds = []; $proiectCods = [];
            try {
                $stP = $db->prepare("SELECT id, proiect_id FROM proiecte WHERE client_id = ?");
                $stP->execute([$id]);
                foreach ($stP->fetchAll() as $pr) {
                    $proiectIds[] = intval($pr['id']);
                    if (!empty($pr['proiect_id'])) $proiectCods[] = $pr['proiect_id'];
                }
            } catch (Exception $e) { /* fără proiecte */ }

            // Colectează ID-urile ofertelor
            $ofertaIds = [];
            try {
                $stO = $db->prepare("SELECT id FROM oferte WHERE client_id = ?");
                $stO->execute([$id]);
                foreach ($stO->fetchAll() as $of) { $ofertaIds[] = intval($of['id']); }
            } catch (Exception $e) { /* fără oferte */ }

            $sum = ['oferte' => 0, 'proiecte' => 0, 'contracte' => 0, 'altele' => 0];

            // Copii ai ofertelor
            $sum['altele'] += $delIn('oferta_linii',   'oferta_id', $ofertaIds);
            $sum['altele'] += $delIn('necesar_comenzi', 'oferta_id', $ofertaIds);

            // Copii ai proiectelor
            if ($proiectIds) {
                // executie_atribuiri se leagă prin programare_id
                try {
                    $phP = implode(',', array_fill(0, count($proiectIds), '?'));
                    $db->prepare("DELETE FROM executie_atribuiri WHERE programare_id IN (SELECT id FROM executie_programari WHERE proiect_id IN ($phP))")
                       ->execute(array_values($proiectIds));
                } catch (Exception $e) { /* tabel poate lipsi */ }
                foreach (['executie_programari','executie_jurnal','executie_files','executie_progres_material',
                          'proiectare','proiectare_checklist','proiectare_documente','jurnal_teren'] as $t) {
                    $sum['altele'] += $delIn($t, 'proiect_id', $proiectIds);
                }
                // notificari folosește codul text al proiectului
                $sum['altele'] += $delIn('notificari', 'proiect_id', $proiectCods);
            }

            // Mentenanță (legată prin client_id SAU proiect_id)
            $sum['altele'] += $delEq('mentenanta', 'client_id', $id);
            $sum['altele'] += $delIn('mentenanta', 'proiect_id', $proiectIds);

            // Contracte, oferte, proiecte (părinți), apoi clientul
            $sum['contracte'] = $delEq('contracte', 'client_id', $id);
            $sum['oferte']    = $delEq('oferte',    'client_id', $id);
            $sum['proiecte']  = $delEq('proiecte',  'client_id', $id);

            $stmt = $db->prepare("DELETE FROM clienti WHERE id = ?");
            $stmt->execute([$id]);

            jsonResponse([
                'success' => true,
                'nume'    => $cliRow['nume'],
                'deleted' => $stmt->rowCount(),
                'cascade' => $sum
            ]);
            break;

        // ══════════════════════════════════════
        // PROIECTE
        // ══════════════════════════════════════
        case 'getProiecte':
            $status = (isset($_GET['status']) ? $_GET['status'] : '');
            $search = (isset($_GET['search']) ? $_GET['search'] : '');
            $sql = "SELECT v.*, (SELECT client_id FROM proiecte WHERE id = v.id) AS client_db_id FROM v_proiecte_complete v WHERE 1=1";
            $params = [];
            if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
            if ($search) { $sql .= " AND (client_nume LIKE ? OR proiect_id LIKE ? OR obiectiv LIKE ?)"; $s = "%$search%"; $params = array_merge($params, [$s,$s,$s]); }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            // Imbogatim cu valori agregate din oferte (pentru proiectele fara valoare_contract setata)
            // Strategie: pentru fiecare proiect, calculam:
            //   - valoare_oferte_acceptate = SUM(total_cu_tva) WHERE proiect_id sau client_id match si status='Acceptata'
            //   - valoare_oferte_pipeline  = SUM(total_cu_tva) WHERE match si status IN ('Trimisa','In_discutie')
            //   - valoare_oferte_max       = max valoare unica (cea mai recenta) — pentru Lead/Oferta
            // Folosim 1 query agregat pe toate proiectele deodata pentru performanta
            if (!empty($rows)) {
                $clientIds = array_unique(array_filter(array_map(function($r){ return $r['client_db_id'] ?? null; }, $rows)));
                $proiectIds = array_unique(array_filter(array_map(function($r){ return $r['id'] ?? null; }, $rows)));
                $ofData = ['by_proiect' => [], 'by_client' => []];
                if ($proiectIds) {
                    $ph = implode(',', array_fill(0, count($proiectIds), '?'));
                    $stmtO = $db->prepare("SELECT proiect_id, status, total_cu_tva FROM oferte WHERE proiect_id IN ($ph)");
                    $stmtO->execute($proiectIds);
                    foreach ($stmtO->fetchAll() as $o) {
                        $pid = $o['proiect_id'];
                        if (!isset($ofData['by_proiect'][$pid])) $ofData['by_proiect'][$pid] = ['acc'=>0,'pip'=>0,'max'=>0];
                        $val = floatval($o['total_cu_tva']);
                        if ($o['status'] === 'Acceptata') $ofData['by_proiect'][$pid]['acc'] += $val;
                        elseif (in_array($o['status'], ['Trimisa','In_discutie'], true)) $ofData['by_proiect'][$pid]['pip'] += $val;
                        if ($val > $ofData['by_proiect'][$pid]['max']) $ofData['by_proiect'][$pid]['max'] = $val;
                    }
                }
                if ($clientIds) {
                    $ph = implode(',', array_fill(0, count($clientIds), '?'));
                    $stmtO = $db->prepare("SELECT client_id, status, total_cu_tva FROM oferte WHERE client_id IN ($ph) AND proiect_id IS NULL");
                    $stmtO->execute($clientIds);
                    foreach ($stmtO->fetchAll() as $o) {
                        $cid = $o['client_id'];
                        if (!isset($ofData['by_client'][$cid])) $ofData['by_client'][$cid] = ['acc'=>0,'pip'=>0,'max'=>0];
                        $val = floatval($o['total_cu_tva']);
                        if ($o['status'] === 'Acceptata') $ofData['by_client'][$cid]['acc'] += $val;
                        elseif (in_array($o['status'], ['Trimisa','In_discutie'], true)) $ofData['by_client'][$cid]['pip'] += $val;
                        if ($val > $ofData['by_client'][$cid]['max']) $ofData['by_client'][$cid]['max'] = $val;
                    }
                }
                // Atasam pe fiecare rand
                foreach ($rows as &$r) {
                    $bp = $ofData['by_proiect'][$r['id']] ?? ['acc'=>0,'pip'=>0,'max'=>0];
                    $bc = $ofData['by_client'][$r['client_db_id']] ?? ['acc'=>0,'pip'=>0,'max'=>0];
                    $r['valoare_oferte_acceptate'] = $bp['acc'] + $bc['acc'];
                    $r['valoare_oferte_pipeline']  = $bp['pip'] + $bc['pip'];
                    $r['valoare_oferta_max']       = max($bp['max'], $bc['max']);
                    // valoare_calc = "cea mai relevanta" — folosita de UI:
                    //   contract semnat? -> valoare_contract
                    //   altfel acceptate? -> oferte acceptate
                    //   altfel pipeline?  -> oferte pipeline
                    //   altfel max ofera (Lead/Draft) sau valoare_estimata
                    $vc = floatval($r['valoare_contract'] ?? 0);
                    $ve = floatval($r['valoare_estimata'] ?? 0);
                    if ($vc > 0)                          $r['valoare_calc'] = $vc;
                    elseif ($r['valoare_oferte_acceptate'] > 0) $r['valoare_calc'] = $r['valoare_oferte_acceptate'];
                    elseif ($r['valoare_oferte_pipeline'] > 0)  $r['valoare_calc'] = $r['valoare_oferte_pipeline'];
                    elseif ($r['valoare_oferta_max'] > 0)       $r['valoare_calc'] = $r['valoare_oferta_max'];
                    else                                        $r['valoare_calc'] = $ve;
                }
                unset($r);
            }
            jsonResponse(['success' => true, 'data' => $rows]);
            break;

        case 'getProiect':
            $id = (isset($_GET['id']) ? $_GET['id'] : 0);
            $stmt = $db->prepare("SELECT * FROM v_proiecte_complete WHERE id = ? OR proiect_id = ?");
            $stmt->execute([$id, $id]);
            jsonResponse(['success' => true, 'data' => $stmt->fetch()]);
            break;

        case 'createProiect':
            $clientId = (isset($data['client_id']) ? $data['client_id'] : 0);
            if (!$clientId) { jsonResponse(['success' => false, 'error' => 'client_id obligatoriu'], 400); break; }
            
            $year = date('Y');
            $proiectId = nextId('proiect_seq', "CSSI-$year-", 4);
            $istoric = json_encode([['status' => 'Lead', 'data' => date('Y-m-d H:i:s'), 'user' => (isset($data['responsabil']) ? $data['responsabil'] : 'Admin')]]);
            
            $stmt = $db->prepare("INSERT INTO proiecte (proiect_id, client_id, serviciu, obiectiv, status, valoare_estimata, responsabil, adresa_obiectiv, note, istoric_status) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $proiectId,
                $clientId,
                (isset($data['serviciu']) ? $data['serviciu'] : 'Supraveghere Video'),
                (isset($data['obiectiv']) ? $data['obiectiv'] : ''),
                (isset($data['status']) ? $data['status'] : 'Lead'),
                (isset($data['valoare_estimata']) ? $data['valoare_estimata'] : 0),
                (isset($data['responsabil']) ? $data['responsabil'] : ''),
                (isset($data['adresa_obiectiv']) ? $data['adresa_obiectiv'] : ''),
                (isset($data['note']) ? $data['note'] : ''),
                $istoric
            ]);
            
            // Creare directoare uploads pt proiect
            $projDir = PROIECTE_DIR . $proiectId . '/';
            foreach (['oferte', 'contract', 'proiectare', 'executie', 'receptie', 'facturi'] as $sub) {
                @mkdir($projDir . $sub, 0755, true);
            }
            
            jsonResponse(['success' => true, 'id' => $db->lastInsertId(), 'proiect_id' => $proiectId]);
            break;

        // Lucrare rapida / Interventie — creeaza atomic: client (daca e nou) +
        // proiect cu status='Interventie' + programare + atribuiri tehnicieni
        case 'createInterventie':
            ensureProiecteSchema($db);
            $clientId   = isset($data['client_id']) ? intval($data['client_id']) : 0;
            $clientNume = isset($data['client_nume']) ? trim($data['client_nume']) : '';
            $titlu      = isset($data['titlu']) ? trim($data['titlu']) : '';
            $adresa     = isset($data['adresa']) ? trim($data['adresa']) : '';
            $dataPrg    = isset($data['data']) ? $data['data'] : date('Y-m-d');
            $oraStart   = isset($data['ora_start']) ? $data['ora_start'] : '08:00';
            $durata     = isset($data['durata_ore']) ? floatval($data['durata_ore']) : 4;
            $tehnicieni = isset($data['tehnicieni']) && is_array($data['tehnicieni']) ? $data['tehnicieni'] : [];
            $note       = isset($data['note']) ? trim($data['note']) : '';
            $telefon    = isset($data['telefon']) ? trim($data['telefon']) : '';
            $serviciu   = isset($data['serviciu']) ? $data['serviciu'] : 'Supraveghere Video';
            $user       = isset($data['user']) ? $data['user'] : 'Admin';

            if (!$titlu)              jsonResponse(['success' => false, 'error' => 'Titlu obligatoriu'], 400);
            if (!$clientId && !$clientNume) jsonResponse(['success' => false, 'error' => 'Client obligatoriu (existent sau nume nou)'], 400);

            $db->beginTransaction();
            try {
                // 1. Creeaza client daca e ad-hoc
                if (!$clientId) {
                    $year = date('Y');
                    $newCid = nextId('client_seq', "CLI-", 4);
                    $stmtC = $db->prepare("INSERT INTO clienti (client_id, nume, telefon, oras, tip, note) VALUES (?,?,?,?,?,?)");
                    $stmtC->execute([$newCid, $clientNume, $telefon, '', 'Persoana fizica', 'Client creat din lucrare rapida']);
                    $clientId = $db->lastInsertId();
                }
                // 2. Creeaza proiect cu status Interventie
                $proiectIdCod = nextId('proiect_seq', "CSSI-" . date('Y') . "-", 4);
                $istoric = json_encode([['status' => 'Interventie', 'data' => date('Y-m-d H:i:s'), 'user' => $user, 'nota' => 'Lucrare rapida']]);
                $db->prepare("INSERT INTO proiecte (proiect_id, client_id, serviciu, obiectiv, status, valoare_estimata, responsabil, adresa_obiectiv, note, istoric_status, preluat_de) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
                   ->execute([$proiectIdCod, $clientId, $serviciu, $titlu, 'Interventie', 0, $user, $adresa, $note, $istoric, $user]);
                $proiectIdDb = $db->lastInsertId();
                // Creare directoare uploads
                $projDir = PROIECTE_DIR . $proiectIdCod . '/';
                foreach (['executie','receptie','facturi'] as $sub) { @mkdir($projDir . $sub, 0755, true); }
                // 3. Creeaza programare in executie_programari (acelasi tabel folosit
                //    de saveProgramare din planificare/executie pages)
                // Asigur tabela exista (idempotent — la fel ca saveProgramare)
                $db->exec("CREATE TABLE IF NOT EXISTS executie_programari (
                    id INT PRIMARY KEY AUTO_INCREMENT, proiect_id INT NOT NULL,
                    data_programata DATE NOT NULL, ora_start TIME DEFAULT '08:00:00',
                    durata_ore DECIMAL(4,1) DEFAULT 8, status VARCHAR(20) DEFAULT 'Programat',
                    obiectiv TEXT, note TEXT, created_by VARCHAR(60),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    KEY idx_data (data_programata), KEY idx_proiect (proiect_id), KEY idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $db->exec("CREATE TABLE IF NOT EXISTS executie_atribuiri (
                    programare_id INT NOT NULL, user_id VARCHAR(60) NOT NULL,
                    PRIMARY KEY (programare_id, user_id), KEY idx_user (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $stmtP = $db->prepare("INSERT INTO executie_programari (proiect_id, data_programata, ora_start, durata_ore, status, obiectiv, note, created_by) VALUES (?,?,?,?,?,?,?,?)");
                $stmtP->execute([$proiectIdDb, $dataPrg, $oraStart . ':00', $durata, 'Programat', $titlu, $note, $user]);
                $prgId = $db->lastInsertId();
                // 4. Atribuiri tehnicieni
                if ($tehnicieni) {
                    $stmtA = $db->prepare("INSERT INTO executie_atribuiri (programare_id, user_id) VALUES (?,?)");
                    foreach ($tehnicieni as $t) { if (trim($t) !== '') $stmtA->execute([$prgId, trim($t)]); }
                }
                $db->commit();
                jsonResponse(['success' => true, 'proiect_id' => $proiectIdCod, 'proiect_db_id' => $proiectIdDb, 'programare_id' => $prgId, 'client_id' => $clientId]);
            } catch (Exception $e) {
                $db->rollBack();
                jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
            }
            break;

        case 'deleteProiect':
            $id = (isset($data['id']) ? $data['id'] : 0);
            if (!$id) { jsonResponse(['success' => false, 'error' => 'ID obligatoriu'], 400); break; }
            // Șterge proiectarea asociată
            $db->prepare("DELETE FROM proiectare WHERE proiect_id = ?")->execute([$id]);
            // Șterge intrările din jurnalul de teren (best-effort — tabelul poate lipsi)
            try { $db->prepare("DELETE FROM jurnal_teren WHERE proiect_id = ?")->execute([$id]); } catch (Exception $e) {}
            // Șterge notificările
            $stmtPid = $db->prepare("SELECT proiect_id FROM proiecte WHERE id = ?");
            $stmtPid->execute([$id]);
            $pidRow = $stmtPid->fetch();
            if ($pidRow) {
                $db->prepare("DELETE FROM notificari WHERE proiect_id = ?")->execute([$pidRow['proiect_id']]);
            }
            // Șterge proiectul
            $db->prepare("DELETE FROM proiecte WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        case 'updateProiect':
            $id = (isset($data['id']) ? $data['id'] : 0);
            $fields = [];
            $values = [];
            foreach (['serviciu','obiectiv','status','valoare_estimata','valoare_contract','responsabil','adresa_obiectiv','note'] as $f) {
                if (isset($data[$f])) {
                    $fields[] = "$f = ?";
                    $values[] = $data[$f];
                }
            }
            if ($fields && $id) {
                $values[] = $id;
                $db->prepare("UPDATE proiecte SET " . implode(', ', $fields) . " WHERE id = ? OR proiect_id = ?")->execute(array_merge($values, [$id]));
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Lipsesc date'], 400);
            }
            break;

        case 'updateStatus':
            $id = (isset($data['id']) ? $data['id'] : (isset($_GET['id']) ? $_GET['id'] : ''));
            $newStatus = (isset($data['status']) ? $data['status'] : (isset($_GET['status']) ? $_GET['status'] : ''));
            $user = (isset($data['user']) ? $data['user'] : 'Admin');
            if (!$id || !$newStatus) { jsonResponse(['success' => false, 'error' => 'id si status obligatorii'], 400); break; }
            
            // Citeste proiectul curent
            $stmt = $db->prepare("SELECT p.id, p.proiect_id, p.status, p.istoric_status, c.nume AS client_nume FROM proiecte p JOIN clienti c ON p.client_id = c.id WHERE p.id = ? OR p.proiect_id = ?");
            $stmt->execute([$id, $id]);
            $row = $stmt->fetch();
            if (!$row) { jsonResponse(['success' => false, 'error' => 'Proiect negăsit'], 404); break; }
            
            $oldStatus = $row['status'];
            $istoric = json_decode($row['istoric_status'] ?: '[]', true);
            $istoric[] = ['status' => $newStatus, 'data' => date('Y-m-d H:i:s'), 'user' => $user];
            
            // Update proiect — resetează preluat_de la schimbarea statusului
            $db->prepare("UPDATE proiecte SET status = ?, istoric_status = ?, preluat_de = NULL, preluat_la = NULL WHERE id = ? OR proiect_id = ?")
               ->execute([$newStatus, json_encode($istoric), $id, $id]);
            
            // Creează notificare
            $mesaj = '📌 ' . $row['proiect_id'] . ' (' . $row['client_nume'] . ') — ' . $oldStatus . ' → ' . $newStatus . ' (de ' . $user . ')';
            $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la, etapa_noua) VALUES (?,?,?,?,?)")
               ->execute([$row['id'], $mesaj, 'status_change', $user, $newStatus]);
            
            // Auto-setup Proiectare: termen +30 zile, data_start, status în lucru
            if ($newStatus === 'Proiectare') {
                $numId = $row['id'];
                // Verifică dacă există record proiectare
                $chkPr = $db->prepare("SELECT id FROM proiectare WHERE proiect_id = ?");
                $chkPr->execute([$numId]);
                if (!$chkPr->fetch()) {
                    // Determină serviciul pentru checklist dinamic
                    $srvS = $db->prepare("SELECT serviciu FROM proiecte WHERE id = ?");
                    $srvS->execute([$numId]);
                    $srvR = $srvS->fetch();
                    $srv = $srvR ? $srvR['serviciu'] : '';
                    $cl = [
                        ['id'=>'vizita_teren','label'=>'Vizită teren efectuată','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'schema_electrica','label'=>'Schemă electrică creată','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'plan_amplasare','label'=>'Plan amplasare echipamente','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'trasee_cabluri','label'=>'Trasee cabluri proiectate','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'necesar_materiale','label'=>'Necesar materiale verificat','done'=>false,'date'=>null,'user'=>null],
                    ];
                    if (in_array($srv, ['Supraveghere Video','Alarma','Complex'])) {
                        $cl[] = ['id'=>'aviz_igpr_depus','label'=>'👮 Aviz IGPR depus','done'=>false,'date'=>null,'user'=>null];
                        $cl[] = ['id'=>'aviz_igpr_obtinut','label'=>'👮 Aviz IGPR obținut','done'=>false,'date'=>null,'user'=>null];
                    }
                    if (in_array($srv, ['Detectie Incendiu','Complex'])) {
                        $cl[] = ['id'=>'aviz_isu_depus','label'=>'🛡️ Aviz ISU depus','done'=>false,'date'=>null,'user'=>null];
                        $cl[] = ['id'=>'aviz_isu_obtinut','label'=>'🛡️ Aviz ISU obținut','done'=>false,'date'=>null,'user'=>null];
                    }
                    $cl[] = ['id'=>'dosar_complet','label'=>'Dosar proiect complet','done'=>false,'date'=>null,'user'=>null];
                    $db->prepare("INSERT INTO proiectare (proiect_id, checklist_json, termen, data_start, status) VALUES (?, ?, DATE_ADD(CURDATE(), INTERVAL 30 DAY), CURDATE(), 'In lucru')")
                       ->execute([$numId, json_encode($cl)]);
                } else {
                    // Update termen + status dacă există deja
                    $db->prepare("UPDATE proiectare SET termen = COALESCE(termen, DATE_ADD(CURDATE(), INTERVAL 30 DAY)), data_start = COALESCE(data_start, CURDATE()), status = 'In lucru' WHERE proiect_id = ?")
                       ->execute([$numId]);
                }
            }
            
            jsonResponse(['success' => true, 'notificare' => $mesaj]);
            break;

        // ══════════════════════════════════════
        // OFERTE
        // ══════════════════════════════════════
        case '_debugOferteSchema':
            // Doar admin: returnează coloanele actuale ale tabelei oferte
            requireAdmin();
            ensureOferteColumns($db);
            jsonResponse(['success' => true, 'columns' => debugOferteColumns($db)]);
            break;

        case 'getOferte':
            ensureOferteColumns($db);
            autoExpireOferte($db);
            $search = (isset($_GET['search']) ? $_GET['search'] : '');
            $clientId = (isset($_GET['client_id']) ? $_GET['client_id'] : '');
            $proiectId = (isset($_GET['proiect_id']) ? $_GET['proiect_id'] : '');
            $statusF = (isset($_GET['status']) ? $_GET['status'] : '');
            $archived = isset($_GET['archived']) ? $_GET['archived'] : '0';  // '0' = active, '1' = arhivate, 'all' = toate
            $monthF = (isset($_GET['month']) ? $_GET['month'] : '');         // YYYY-MM
            $minVal = (isset($_GET['min_val']) ? floatval($_GET['min_val']) : 0);
            $maxVal = (isset($_GET['max_val']) ? floatval($_GET['max_val']) : 0);
            $sortBy = (isset($_GET['sort']) ? $_GET['sort'] : 'data_desc'); // data_desc/asc, valoare_desc/asc, client_asc, status
            $light = !empty($_GET['light']);  // true = nu adaugă linii (mult mai rapid pentru listing)

            $sql = "SELECT vc.*, o2.client_id AS client_db_id, o2.proiect_id AS proiect_db_id, o2.motiv_respingere, o2.data_decizie, o2.decis_de, o2.archived_at, o2.expires_at, o2.mentiuni FROM v_oferte_complete vc JOIN oferte o2 ON vc.id = o2.id WHERE 1=1";
            $params = [];
            if ($clientId) { $sql .= " AND o2.client_id = ?"; $params[] = $clientId; }
            if ($proiectId) { $sql .= " AND o2.proiect_id = ?"; $params[] = $proiectId; }
            if ($search) {
                $sql .= " AND (vc.client_nume LIKE ? OR vc.oferta_id LIKE ? OR vc.obiectiv LIKE ?)";
                $s = "%$search%"; $params = array_merge($params, [$s, $s, $s]);
            }
            if ($statusF) { $sql .= " AND o2.status = ?"; $params[] = $statusF; }
            if ($archived === '0')      { $sql .= " AND o2.archived_at IS NULL"; }
            elseif ($archived === '1')  { $sql .= " AND o2.archived_at IS NOT NULL"; }
            // 'all' → fără filtru
            if ($monthF && preg_match('/^\d{4}-\d{2}$/', $monthF)) {
                $sql .= " AND DATE_FORMAT(vc.data_oferta, '%Y-%m') = ?"; $params[] = $monthF;
            }
            if ($minVal > 0) { $sql .= " AND vc.total_cu_tva >= ?"; $params[] = $minVal; }
            if ($maxVal > 0) { $sql .= " AND vc.total_cu_tva <= ?"; $params[] = $maxVal; }
            // Sortare
            $sortMap = [
                'data_desc'    => 'vc.data_oferta DESC, vc.id DESC',
                'data_asc'     => 'vc.data_oferta ASC, vc.id ASC',
                'valoare_desc' => 'vc.total_cu_tva DESC',
                'valoare_asc'  => 'vc.total_cu_tva ASC',
                'client_asc'   => 'vc.client_nume ASC, vc.data_oferta DESC',
                'status'       => "FIELD(o2.status,'Draft','Trimisa','In_discutie','Acceptata','Refuzata','Expirata') ASC, vc.data_oferta DESC",
            ];
            $sql .= " ORDER BY " . (isset($sortMap[$sortBy]) ? $sortMap[$sortBy] : $sortMap['data_desc']);

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $oferte = $stmt->fetchAll();

            // Adauga linii pt fiecare oferta (skip dacă light=1)
            if (!$light) {
                foreach ($oferte as &$o) {
                    $stmtL = $db->prepare("SELECT * FROM oferta_linii WHERE oferta_id = ? ORDER BY tip, ordine");
                    $stmtL->execute([$o['id']]);
                    $linii = $stmtL->fetchAll();
                    $o['lines'] = array_values(array_filter($linii, function($l) { return $l['tip'] === 'echipament'; }));
                    $o['labor'] = array_values(array_filter($linii, function($l) { return $l['tip'] === 'manopera'; }));
                }
                unset($o);
            }
            jsonResponse(['success' => true, 'data' => $oferte]);
            break;

        case 'getOferta':
            $id = (isset($_GET['id']) ? $_GET['id'] : 0);
            $stmt = $db->prepare("SELECT * FROM v_oferte_complete WHERE id = ? OR oferta_id = ?");
            $stmt->execute([$id, $id]);
            $o = $stmt->fetch();
            if ($o) {
                // View-ul redenumește clientId ca crm_client_id (text); luăm FK-urile
                // numerice direct din tabela oferte ca să le poată folosi frontend-ul
                // la save (păstrare legătură ofertă→client/proiect la edit)
                ensureOferteColumns($db);
                $stmtFK = $db->prepare("SELECT client_id, proiect_id, mentiuni FROM oferte WHERE id = ?");
                $stmtFK->execute([$o['id']]);
                $fk = $stmtFK->fetch();
                if ($fk) {
                    $o['client_id']  = $fk['client_id']  !== null ? intval($fk['client_id'])  : null;
                    $o['proiect_id'] = $fk['proiect_id'] !== null ? intval($fk['proiect_id']) : null;
                    if (!isset($o['mentiuni']) || $o['mentiuni'] === null) $o['mentiuni'] = $fk['mentiuni'] ?? '';
                }
                $stmtL = $db->prepare("SELECT * FROM oferta_linii WHERE oferta_id = ? ORDER BY tip, ordine");
                $stmtL->execute([$o['id']]);
                $linii = $stmtL->fetchAll();
                $o['lines'] = array_values(array_filter($linii, function($l) { return $l['tip'] === 'echipament'; }));
                $o['labor'] = array_values(array_filter($linii, function($l) { return $l['tip'] === 'manopera'; }));
            }
            jsonResponse(['success' => true, 'data' => $o]);
            break;

        case 'saveOferta':
            // Detectează update: prin oferta_db_id SAU prin nr existent
            $isUpdate = !empty($data['oferta_db_id']);
            if (!$isUpdate && !empty($data['nr'])) {
                // Verifică dacă există deja o ofertă cu acest nr (oferta_id)
                $chk = $db->prepare("SELECT id FROM oferte WHERE oferta_id = ? LIMIT 1");
                $chk->execute([$data['nr']]);
                $existing = $chk->fetch();
                if ($existing) {
                    $isUpdate = true;
                    $data['oferta_db_id'] = $existing['id'];
                }
            }
            $preGeneratedId = null;
            if (!$isUpdate) {
                $preGeneratedId = (!empty($data['nr']) ? $data['nr'] : nextId('oferta_seq', '', 0));
            }
            $db->beginTransaction();
            try {
                if ($isUpdate) {
                    // Update existing
                    $ofertaDbId = $data['oferta_db_id'];
                    // Validare FK: client_id si proiect_id trebuie sa existe sau sa fie NULL
                    $clientIdVal = null;
                    if (!empty($data['client_db_id'])) {
                        $chkC = $db->prepare("SELECT id FROM clienti WHERE id = ? LIMIT 1");
                        $chkC->execute([$data['client_db_id']]);
                        if ($chkC->fetch()) $clientIdVal = $data['client_db_id'];
                    }
                    $proiectIdVal = null;
                    if (!empty($data['proiect_db_id'])) {
                        $chkP = $db->prepare("SELECT id FROM proiecte WHERE id = ? LIMIT 1");
                        $chkP->execute([$data['proiect_db_id']]);
                        if ($chkP->fetch()) $proiectIdVal = $data['proiect_db_id'];
                    }
                    ensureOferteColumns($db);
                    $dataOf = (isset($data['data']) ? $data['data'] : date('Y-m-d'));
                    $valabUpd = (isset($data['valab']) ? $data['valab'] : '4 zile');
                    $expUpd = calcExpiresAt($dataOf, $valabUpd);
                    $db->prepare("UPDATE oferte SET titlu=?, data_oferta=?, valabilitate=?, obiectiv=?, mentiuni=?, client_id=?, proiect_id=?, subtotal_echip=?, subtotal_manop=?, total_fara_tva=?, tva=?, total_cu_tva=?, client_nume=?, client_cui=?, client_adresa=?, client_contact=?, status=?, expires_at=? WHERE id=?")
                       ->execute([
                           (isset($data['titlu']) ? $data['titlu'] : ''),
                           $dataOf,
                           $valabUpd,
                           (isset($data['obiectiv']) ? $data['obiectiv'] : ''),
                           (isset($data['mentiuni']) ? $data['mentiuni'] : ''),
                           $clientIdVal,
                           $proiectIdVal,
                           (isset($data['subtotalEchip']) ? $data['subtotalEchip'] : 0),
                           (isset($data['subtotalManop']) ? $data['subtotalManop'] : 0),
                           (isset($data['totalNet']) ? $data['totalNet'] : 0),
                           (isset($data['tva']) ? $data['tva'] : 0),
                           (isset($data['totalBrut']) ? $data['totalBrut'] : 0),
                           (isset($data['client']) ? $data['client'] : ''),
                           (isset($data['cui']) ? $data['cui'] : ''),
                           (isset($data['adresa']) ? $data['adresa'] : ''),
                           (isset($data['contact']) ? $data['contact'] : ''),
                           (isset($data['oferta_status']) ? $data['oferta_status'] : 'Draft'),
                           $expUpd,
                           $ofertaDbId
                       ]);
                    // Sterge linii vechi
                    $db->prepare("DELETE FROM oferta_linii WHERE oferta_id = ?")->execute([$ofertaDbId]);
                } else {
                    // Insert new
                    $ofertaId = $preGeneratedId;
                    // Regenerează titlul cu nr-ul corect
                    $client = (isset($data['client']) ? $data['client'] : '');
                    $obiectiv = (isset($data['obiectiv']) ? $data['obiectiv'] : '');
                    $dataOf = (isset($data['data']) ? $data['data'] : date('Y-m-d'));
                    $dataParts = explode('-', $dataOf);
                    $dataFmt = (isset($dataParts[2]) ? $dataParts[2].'.'.$dataParts[1].'.'.$dataParts[0] : $dataOf);
                    $titlu = 'Deviz ' . $client . ($obiectiv ? ' ' . $obiectiv : '') . ' ser.BV Nr. ' . $ofertaId . ' din ' . $dataFmt;
                    ensureOferteColumns($db);
                    $expIns = calcExpiresAt($dataOf, (isset($data['valab']) ? $data['valab'] : '4 zile'));
                    $stmt = $db->prepare("INSERT INTO oferte (oferta_id, titlu, data_oferta, valabilitate, obiectiv, mentiuni, client_id, proiect_id, subtotal_echip, subtotal_manop, total_fara_tva, tva, total_cu_tva, client_nume, client_cui, client_adresa, client_contact, status, expires_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    // Validare FK pt INSERT
                    $clientIdValI = null;
                    if (!empty($data['client_db_id'])) {
                        $chkCI = $db->prepare("SELECT id FROM clienti WHERE id = ? LIMIT 1");
                        $chkCI->execute([$data['client_db_id']]);
                        if ($chkCI->fetch()) $clientIdValI = $data['client_db_id'];
                    }
                    $proiectIdValI = null;
                    if (!empty($data['proiect_db_id'])) {
                        $chkPI = $db->prepare("SELECT id FROM proiecte WHERE id = ? LIMIT 1");
                        $chkPI->execute([$data['proiect_db_id']]);
                        if ($chkPI->fetch()) $proiectIdValI = $data['proiect_db_id'];
                    }
                    $stmt->execute([
                        $ofertaId,
                        $titlu,
                        $dataOf,
                        (isset($data['valab']) ? $data['valab'] : '4 zile'),
                        (isset($data['obiectiv']) ? $data['obiectiv'] : ''),
                        (isset($data['mentiuni']) ? $data['mentiuni'] : ''),
                        $clientIdValI,
                        $proiectIdValI,
                        (isset($data['subtotalEchip']) ? $data['subtotalEchip'] : 0),
                        (isset($data['subtotalManop']) ? $data['subtotalManop'] : 0),
                        (isset($data['totalNet']) ? $data['totalNet'] : 0),
                        (isset($data['tva']) ? $data['tva'] : 0),
                        (isset($data['totalBrut']) ? $data['totalBrut'] : 0),
                        (isset($data['client']) ? $data['client'] : ''),
                        (isset($data['cui']) ? $data['cui'] : ''),
                        (isset($data['adresa']) ? $data['adresa'] : ''),
                        (isset($data['contact']) ? $data['contact'] : ''),
                        (isset($data['oferta_status']) ? $data['oferta_status'] : 'Draft'),
                        $expIns]);
                    $ofertaDbId = $db->lastInsertId();
                }
                
                // Insert linii echipamente
                $stmtLine = $db->prepare("INSERT INTO oferta_linii (oferta_id, tip, denumire, cod, um, cantitate, pret_achizitie, adaos_procent, pret_vanzare, valoare, ordine) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                
                $lines = (isset($data['lines']) ? $data['lines'] : []);
                foreach ($lines as $i => $l) {
                    if (empty($l['name'])) continue;
                    $pv = ((isset($l['pAchiz']) ? $l['pAchiz'] : 0)) * (1 + ((isset($l['adaos']) ? $l['adaos'] : 40)) / 100);
                    $val = ((isset($l['cant']) ? $l['cant'] : 0)) * $pv;
                    $stmtLine->execute([
                        $ofertaDbId, 'echipament', $l['name'], (isset($l['code']) ? $l['code'] : ''), (isset($l['um']) ? $l['um'] : 'buc.'),
                        (isset($l['cant']) ? $l['cant'] : 0), (isset($l['pAchiz']) ? $l['pAchiz'] : 0), (isset($l['adaos']) ? $l['adaos'] : 40), round($pv, 2), round($val, 2), $i
                    ]);
                }
                
                // Insert linii manopera
                $labor = (isset($data['labor']) ? $data['labor'] : []);
                foreach ($labor as $i => $l) {
                    $c = floatval((isset($l['cant']) ? $l['cant'] : 0));
                    $p = floatval((isset($l['price']) ? $l['price'] : 0));
                    if (!$c || empty($l['name'])) continue;
                    $stmtLine->execute([
                        $ofertaDbId, 'manopera', $l['name'], '', (isset($l['um']) ? $l['um'] : 'ore'),
                        $c, $p, 0, $p, round($c * $p, 2), 100 + $i
                    ]);
                }
                
                $db->commit();

                // Dacă oferta provine dintr-un draft autosave, șterge draftul
                if (!empty($data['draft_id'])) {
                    try {
                        ensureOferteDrafturiSchema($db);
                        $db->prepare("DELETE FROM oferte_drafturi WHERE id = ?")->execute([intval($data['draft_id'])]);
                    } catch (Exception $e) { /* silent — draftul a fost deja consumat */ }
                }

                // Returneaza oferta completa
                $stmt = $db->prepare("SELECT * FROM oferte WHERE id = ?");
                $stmt->execute([$ofertaDbId]);
                $saved = $stmt->fetch();

                jsonResponse(['success' => true, 'id' => $ofertaDbId, 'oferta_id' => $saved['oferta_id']]);
            } catch (Exception $e) {
                $db->rollBack();
                jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
            }
            break;

        // ══════════════════════════════════════
        // OFERTE — DRAFTURI AUTOSAVE (server-side, cross-device)
        // Salvare automată a ofertelor în lucru, fără să consume număr din secvență
        // și fără să apară în /oferte.html
        // ══════════════════════════════════════
        case 'saveOfertaDraft':
            ensureOferteDrafturiSchema($db);
            $u = currentUser();
            $username = $u['username'] ?? 'anonim';
            $displayName = $u['display_name'] ?? $username;
            $draftId = !empty($data['draft_id']) ? intval($data['draft_id']) : 0;
            $clientNume = isset($data['client_nume']) ? mb_substr((string)$data['client_nume'], 0, 255) : '';
            $obiectiv = isset($data['obiectiv']) ? (string)$data['obiectiv'] : '';
            $totalCuTva = isset($data['total_cu_tva']) ? floatval($data['total_cu_tva']) : 0;
            $payload = isset($data['payload']) ? json_encode($data['payload'], JSON_UNESCAPED_UNICODE) : '{}';
            try {
                if ($draftId) {
                    // UPDATE — doar dacă draftul există (altfel cade pe INSERT)
                    $chk = $db->prepare("SELECT id FROM oferte_drafturi WHERE id = ? LIMIT 1");
                    $chk->execute([$draftId]);
                    if ($chk->fetch()) {
                        $db->prepare("UPDATE oferte_drafturi SET client_nume=?, obiectiv=?, total_cu_tva=?, payload=?, updated_by=? WHERE id=?")
                           ->execute([$clientNume, $obiectiv, $totalCuTva, $payload, $username, $draftId]);
                        jsonResponse(['success' => true, 'id' => $draftId, 'op' => 'update']);
                        break;
                    }
                    $draftId = 0;
                }
                $db->prepare("INSERT INTO oferte_drafturi (client_nume, obiectiv, total_cu_tva, payload, created_by, created_by_name, updated_by) VALUES (?,?,?,?,?,?,?)")
                   ->execute([$clientNume, $obiectiv, $totalCuTva, $payload, $username, $displayName, $username]);
                $newId = $db->lastInsertId();
                jsonResponse(['success' => true, 'id' => intval($newId), 'op' => 'insert']);
            } catch (Exception $e) {
                jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
            }
            break;

        case 'listOferteDrafturi':
            ensureOferteDrafturiSchema($db);
            // Cleanup auto: șterge drafturi mai vechi de 30 zile (lazy, la fiecare list)
            try {
                $db->exec("DELETE FROM oferte_drafturi WHERE updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            } catch (Exception $e) { /* silent */ }
            // Toți utilizatorii văd toate drafturile (decizie UX)
            $rows = $db->query("SELECT id, client_nume, obiectiv, total_cu_tva, created_by, created_by_name, updated_at, created_at FROM oferte_drafturi ORDER BY updated_at DESC LIMIT 50")->fetchAll();
            jsonResponse(['success' => true, 'data' => $rows]);
            break;

        case 'getOfertaDraft':
            ensureOferteDrafturiSchema($db);
            $id = isset($_GET['id']) ? intval($_GET['id']) : (isset($data['id']) ? intval($data['id']) : 0);
            if (!$id) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
            $stmt = $db->prepare("SELECT * FROM oferte_drafturi WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['success' => false, 'error' => 'Draft inexistent'], 404);
            $row['payload'] = json_decode($row['payload'] ?: '{}', true);
            jsonResponse(['success' => true, 'data' => $row]);
            break;

        case 'deleteOfertaDraft':
            ensureOferteDrafturiSchema($db);
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
            $db->prepare("DELETE FROM oferte_drafturi WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        case 'deleteOferta':
            $id = (isset($data['id']) ? $data['id'] : 0);
            $db->prepare("DELETE FROM oferte WHERE id = ? OR oferta_id = ?")->execute([$id, $id]);
            jsonResponse(['success' => true]);
            break;

        // Arhivare ofertă (soft) — păstrează date dar o scoate din vederea principală
        case 'archiveOferta':
            ensureOferteColumns($db);
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
            $db->prepare("UPDATE oferte SET archived_at = NOW() WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;
        case 'unarchiveOferta':
            ensureOferteColumns($db);
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
            $db->prepare("UPDATE oferte SET archived_at = NULL WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;
        // Bulk: archive/unarchive/delete/setStatus pe mai multe oferte deodata
        case 'bulkOferte':
            ensureOferteColumns($db);
            $ids = isset($data['ids']) && is_array($data['ids']) ? array_map('intval', $data['ids']) : [];
            $op  = isset($data['op']) ? $data['op'] : '';
            if (!$ids) jsonResponse(['success' => false, 'error' => 'Niciun ID selectat'], 400);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            switch ($op) {
                case 'archive':
                    $db->prepare("UPDATE oferte SET archived_at = NOW() WHERE id IN ($placeholders)")->execute($ids);
                    break;
                case 'unarchive':
                    $db->prepare("UPDATE oferte SET archived_at = NULL WHERE id IN ($placeholders)")->execute($ids);
                    break;
                case 'delete':
                    $db->prepare("DELETE FROM oferte WHERE id IN ($placeholders)")->execute($ids);
                    break;
                case 'setStatus':
                    $st = isset($data['status']) ? $data['status'] : '';
                    if (!in_array($st, ['Draft','Trimisa','In_discutie','Acceptata','Refuzata','Expirata'], true)) {
                        jsonResponse(['success' => false, 'error' => 'Status invalid'], 400);
                    }
                    $params = array_merge([$st], $ids);
                    $db->prepare("UPDATE oferte SET status = ? WHERE id IN ($placeholders)")->execute($params);
                    break;
                default:
                    jsonResponse(['success' => false, 'error' => 'Operațiune necunoscută'], 400);
            }
            jsonResponse(['success' => true, 'count' => count($ids)]);
            break;
        // Export CSV pentru oferte (cu aceleași filtre ca getOferte)
        case 'exportOferteCSV':
            ensureOferteColumns($db);
            autoExpireOferte($db);
            $statusF = isset($_GET['status']) ? $_GET['status'] : '';
            $archived = isset($_GET['archived']) ? $_GET['archived'] : '0';
            $monthF = isset($_GET['month']) ? $_GET['month'] : '';
            $sql = "SELECT vc.oferta_id, vc.data_oferta, vc.client_nume, vc.obiectiv, vc.total_cu_tva, o2.status, o2.expires_at, o2.archived_at, vc.client_cui, vc.client_adresa FROM v_oferte_complete vc JOIN oferte o2 ON vc.id = o2.id WHERE 1=1";
            $params = [];
            if ($statusF) { $sql .= " AND o2.status = ?"; $params[] = $statusF; }
            if ($archived === '0') $sql .= " AND o2.archived_at IS NULL";
            elseif ($archived === '1') $sql .= " AND o2.archived_at IS NOT NULL";
            if ($monthF && preg_match('/^\d{4}-\d{2}$/', $monthF)) { $sql .= " AND DATE_FORMAT(vc.data_oferta, '%Y-%m') = ?"; $params[] = $monthF; }
            $sql .= " ORDER BY vc.data_oferta DESC";
            $stmt = $db->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
            // Output CSV (UTF-8 BOM pentru Excel)
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="oferte-' . date('Y-m-d') . '.csv"');
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nr. ofertă','Data','Client','Obiectiv','Total CU TVA','Status','Expiră la','Arhivată la','CUI','Adresă'], ';');
            foreach ($rows as $r) {
                fputcsv($out, [$r['oferta_id'], $r['data_oferta'], $r['client_nume'], $r['obiectiv'], $r['total_cu_tva'], $r['status'], $r['expires_at'], $r['archived_at'], $r['client_cui'], $r['client_adresa']], ';');
            }
            fclose($out);
            exit;

        // Vedere client-centric: client + oferte + proiecte + LTV
        case 'getClientFull':
            ensureOferteColumns($db);
            $cid = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$cid) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
            $stmtC = $db->prepare("SELECT * FROM clienti WHERE id = ?");
            $stmtC->execute([$cid]);
            $client = $stmtC->fetch();
            if (!$client) jsonResponse(['success' => false, 'error' => 'Client inexistent'], 404);
            // Oferte
            $stmtO = $db->prepare("SELECT vc.id, vc.oferta_id, vc.data_oferta, vc.obiectiv, vc.total_cu_tva, o2.status, o2.archived_at, o2.expires_at FROM v_oferte_complete vc JOIN oferte o2 ON vc.id = o2.id WHERE o2.client_id = ? ORDER BY vc.data_oferta DESC");
            $stmtO->execute([$cid]);
            $oferte = $stmtO->fetchAll();
            // Proiecte
            $stmtP = $db->prepare("SELECT id, proiect_id, status, serviciu, valoare_contract, created_at, adresa_obiectiv FROM proiecte WHERE client_id = ? ORDER BY created_at DESC");
            $stmtP->execute([$cid]);
            $proiecte = $stmtP->fetchAll();
            // Mentenante (best-effort, dacă tabelul există)
            $mentenante = [];
            try {
                $stmtM = $db->prepare("SELECT id, proiect_id, tip, status, data_scadenta, created_at FROM mentenanta WHERE client_id = ? OR proiect_id IN (SELECT id FROM proiecte WHERE client_id = ?) ORDER BY data_scadenta DESC LIMIT 50");
                $stmtM->execute([$cid, $cid]);
                $mentenante = $stmtM->fetchAll();
            } catch (Exception $e) { /* tabel poate să nu existe */ }
            // LTV: suma ofertelor acceptate + valoarea contractelor proiectelor
            $ltvOferte = 0; foreach ($oferte as $o) if ($o['status'] === 'Acceptata') $ltvOferte += floatval($o['total_cu_tva']);
            $ltvProiecte = 0; foreach ($proiecte as $p) $ltvProiecte += floatval($p['valoare_contract']);
            // Statistici sumar
            $stat = [
                'total_oferte'    => count($oferte),
                'oferte_active'   => count(array_filter($oferte, function($o){ return !$o['archived_at']; })),
                'oferte_acceptate'=> count(array_filter($oferte, function($o){ return $o['status']==='Acceptata'; })),
                'total_proiecte'  => count($proiecte),
                'proiecte_active' => count(array_filter($proiecte, function($p){ return !in_array($p['status'], ['Inchis','Anulat'], true); })),
                'ltv_oferte'      => round($ltvOferte, 2),
                'ltv_proiecte'    => round($ltvProiecte, 2),
                'ltv_total'       => round(max($ltvOferte, $ltvProiecte), 2),
            ];
            jsonResponse(['success' => true, 'data' => [
                'client'    => $client,
                'oferte'    => $oferte,
                'proiecte'  => $proiecte,
                'mentenante'=> $mentenante,
                'stat'      => $stat,
            ]]);
            break;

        case 'updateOfertaStatus':
            $id = (isset($data['id']) ? $data['id'] : 0);
            $status = (isset($data['status']) ? $data['status'] : '');
            $motiv = (isset($data['motiv_respingere']) ? $data['motiv_respingere'] : '');
            $user = (isset($data['user']) ? $data['user'] : 'Admin');
            
            // Update oferta status + motiv + data decizie
            $db->prepare("UPDATE oferte SET status = ?, motiv_respingere = ?, data_decizie = NOW(), decis_de = ? WHERE id = ?")->execute([$status, $motiv, $user, $id]);
            
            // Dacă oferta e Acceptată → schimbă proiectul în Contract automat
            if ($status === 'Acceptata') {
                $stmt = $db->prepare("SELECT proiect_id, client_id, total_cu_tva FROM oferte WHERE id = ?");
                $stmt->execute([$id]);
                $oferta = $stmt->fetch();
                
                // Determină proiect_id: direct din ofertă sau fallback prin client_id
                $pId = null;
                if ($oferta && $oferta['proiect_id']) {
                    $pId = $oferta['proiect_id'];
                } elseif ($oferta && $oferta['client_id']) {
                    // Fallback: caută proiectul prin client_id
                    $stmtF = $db->prepare("SELECT id FROM proiecte WHERE client_id = ? ORDER BY created_at DESC LIMIT 1");
                    $stmtF->execute([$oferta['client_id']]);
                    $projRow = $stmtF->fetch();
                    if ($projRow) {
                        $pId = $projRow['id'];
                        // Linkează oferta la proiect pentru viitor
                        $db->prepare("UPDATE oferte SET proiect_id = ? WHERE id = ?")->execute([$pId, $id]);
                    }
                }
                
                if ($pId) {
                    // Suma TUTUROR ofertelor acceptate pentru acest proiect (contract inglobat)
                    $stmtSum = $db->prepare("SELECT COALESCE(SUM(total_cu_tva), 0) AS total FROM oferte WHERE (proiect_id = ? OR (client_id = ? AND proiect_id IS NULL)) AND status = 'Acceptata'");
                    $stmtSum->execute([$pId, $oferta['client_id']]);
                    $sumRow = $stmtSum->fetch();
                    $totalContract = $sumRow ? $sumRow['total'] : $oferta['total_cu_tva'];

                    $db->prepare("UPDATE proiecte SET status = 'Contract', valoare_contract = ? WHERE id = ?")->execute([
                        $totalContract, $pId
                    ]);
                    // Adaugă în istoric
                    $stmtP = $db->prepare("SELECT proiect_id, istoric_status FROM proiecte WHERE id = ?");
                    $stmtP->execute([$pId]);
                    $proj = $stmtP->fetch();
                    if ($proj) {
                        $istoric = json_decode($proj['istoric_status'] ?: '[]', true);
                        $istoric[] = ['status' => 'Contract', 'data' => date('Y-m-d H:i:s'), 'user' => $user, 'nota' => 'Ofertă acceptată'];
                        $db->prepare("UPDATE proiecte SET istoric_status = ? WHERE id = ?")->execute([json_encode($istoric), $pId]);

                        // Notificare
                        $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la, etapa_noua) VALUES (?,?,?,?,?)")->execute([
                            $proj['proiect_id'],
                            '✅ Ofertă acceptată → Proiect ' . $proj['proiect_id'] . ' trecut în Contract',
                            'status',
                            $user,
                            'Contract'
                        ]);
                    }
                }
                // ─── AUTO-CREARE DRAFT CONTRACT ────────────────────────────
                // Daca nu exista deja contract pentru aceasta oferta, creez draft
                ensureContracteSchema($db);
                $chkC = $db->prepare("SELECT id FROM contracte WHERE oferta_id = ? LIMIT 1");
                $chkC->execute([$id]);
                if (!$chkC->fetch()) {
                    $contractNr = 'C-' . date('Y') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
                    $token = generateContractToken();
                    $autoExpiresAt = date('Y-m-d H:i:s', strtotime('+14 days'));
                    // Detect tip client din clienti.tip
                    $tipClient = 'PF';
                    if ($oferta['client_id']) {
                        $stmtTC = $db->prepare("SELECT tip FROM clienti WHERE id = ?");
                        $stmtTC->execute([$oferta['client_id']]);
                        $tcRow = $stmtTC->fetch();
                        if ($tcRow && stripos($tcRow['tip'] ?? '', 'jurid') !== false) $tipClient = 'PJ';
                        elseif ($tcRow && stripos($tcRow['tip'] ?? '', 'firma') !== false) $tipClient = 'PJ';
                    }
                    // Iau valorile din oferta
                    $stmtV = $db->prepare("SELECT total_fara_tva, tva, total_cu_tva FROM oferte WHERE id = ?");
                    $stmtV->execute([$id]);
                    $vRow = $stmtV->fetch();
                    $db->prepare("INSERT INTO contracte (contract_nr, oferta_id, proiect_id, client_id, token, tip_client, status, valoare_net, valoare_tva, valoare_total, created_by, token_expires_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
                       ->execute([
                            $contractNr, $id, $pId, $oferta['client_id'], $token, $tipClient,
                            'asteapta_date',
                            $vRow['total_fara_tva'] ?? 0,
                            $vRow['tva'] ?? 0,
                            $vRow['total_cu_tva'] ?? $oferta['total_cu_tva'] ?? 0,
                            $user,
                            $autoExpiresAt
                       ]);
                    $autoCid = $db->lastInsertId();
                    logContractAccess($db, $autoCid, 'auto_create', 'oferta acceptata id=' . $id);
                    // Notificare separata pt Roxana — apare in pagina de contracte
                    if ($pId) {
                        $stmtPx = $db->prepare("SELECT proiect_id FROM proiecte WHERE id = ?");
                        $stmtPx->execute([$pId]);
                        $pxRow = $stmtPx->fetch();
                        if ($pxRow) {
                            $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la) VALUES (?,?,?,?)")->execute([
                                $pxRow['proiect_id'],
                                '📄 Contract draft creat (' . $contractNr . ') — trimite link client pentru completare date',
                                'contract',
                                $user
                            ]);
                        }
                    }
                }
            }
            
            // Dacă oferta e Refuzată → notificare
            if ($status === 'Refuzata') {
                $stmt = $db->prepare("SELECT o.proiect_id, o.client_id, p.proiect_id AS cod FROM oferte o LEFT JOIN proiecte p ON o.proiect_id = p.id WHERE o.id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                // Fallback prin client_id dacă proiect_id e NULL
                if ($row && !$row['cod'] && $row['client_id']) {
                    $stmtF = $db->prepare("SELECT proiect_id FROM proiecte WHERE client_id = ? ORDER BY created_at DESC LIMIT 1");
                    $stmtF->execute([$row['client_id']]);
                    $projF = $stmtF->fetch();
                    if ($projF) $row['cod'] = $projF['proiect_id'];
                }
                if ($row && $row['cod']) {
                    $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la) VALUES (?,?,?,?)")->execute([
                        $row['cod'],
                        '❌ Ofertă refuzată — Motiv: ' . ($motiv ?: 'nespecificat'),
                        'alerta',
                        $user
                    ]);
                }
            }
            
            jsonResponse(['success' => true]);
            break;

        // ══════════════════════════════════════
        // CONTRACTE
        // ══════════════════════════════════════
        case '_debugContracteSchema':
            requireAdmin();
            // Force schema sync — manualy add lipsa columns indiferent de static check
            try { $db->exec("ALTER TABLE contracte ADD COLUMN token_expires_at DATETIME NULL"); } catch (Exception $e) {}
            try { $db->exec("ALTER TABLE contracte ADD COLUMN locked_resubmit TINYINT(1) DEFAULT 0"); } catch (Exception $e) {}
            try { $db->exec("ALTER TABLE contracte ADD COLUMN gdpr_consent_at DATETIME NULL"); } catch (Exception $e) {}
            try { $db->exec("ALTER TABLE contracte ADD COLUMN gdpr_consent_ip VARCHAR(45) NULL"); } catch (Exception $e) {}
            ensureContracteSchema($db);
            jsonResponse(['success' => true, 'columns' => debugContracteColumns($db)]);
            break;

        case 'getContracte':
            ensureContracteSchema($db);
            $statusF = isset($_GET['status']) ? $_GET['status'] : '';
            $sql = "SELECT c.*, cl.nume AS client_nume, cl.telefon AS client_telefon, cl.email AS client_email, cl.tip AS client_tip,
                           o.oferta_id AS oferta_cod, o.obiectiv,
                           p.proiect_id AS proiect_cod, p.serviciu, p.adresa_obiectiv
                    FROM contracte c
                    LEFT JOIN clienti cl ON c.client_id = cl.id
                    LEFT JOIN oferte o ON c.oferta_id = o.id
                    LEFT JOIN proiecte p ON c.proiect_id = p.id
                    WHERE 1=1";
            $params = [];
            if ($statusF) { $sql .= " AND c.status = ?"; $params[] = $statusF; }
            $sql .= " ORDER BY c.created_at DESC";
            $stmt = $db->prepare($sql); $stmt->execute($params);
            $rows = $stmt->fetchAll();
            // Decode JSON + normalize status (gol/NULL → asteapta_date)
            foreach ($rows as &$r) {
                if (!empty($r['date_completate'])) {
                    $r['date_completate'] = json_decode($r['date_completate'], true);
                }
                if (empty($r['status'])) $r['status'] = $r['completat_la'] ? 'completat' : 'asteapta_date';
            }
            unset($r);
            jsonResponse(['success' => true, 'data' => $rows]);
            break;

        case 'getContract':
            ensureContracteSchema($db);
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$id) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
            $stmt = $db->prepare("SELECT c.*, cl.nume AS client_nume, cl.telefon AS client_telefon, cl.email AS client_email, cl.tip AS client_tip,
                                         o.oferta_id AS oferta_cod, o.obiectiv, o.client_nume AS oferta_client_nume,
                                         p.proiect_id AS proiect_cod, p.serviciu, p.adresa_obiectiv
                                  FROM contracte c
                                  LEFT JOIN clienti cl ON c.client_id = cl.id
                                  LEFT JOIN oferte o ON c.oferta_id = o.id
                                  LEFT JOIN proiecte p ON c.proiect_id = p.id
                                  WHERE c.id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['success' => false, 'error' => 'Contract inexistent'], 404);
            if (!empty($row['date_completate'])) {
                $dec = json_decode($row['date_completate'], true);
                if (is_array($dec)) {
                    foreach (['cnp','ci_seria','ci_numar'] as $f) {
                        if (!empty($dec[$f])) $dec[$f] = decryptSensitive($dec[$f]);
                    }
                    $row['date_completate'] = $dec;
                }
            }
            if (empty($row['status'])) $row['status'] = $row['completat_la'] ? 'completat' : 'asteapta_date';
            $row['locked_resubmit'] = !empty($row['locked_resubmit']);
            $row['prestator'] = prestatorData();
            logContractAccess($db, $id, 'view_admin');
            jsonResponse(['success' => true, 'data' => $row]);
            break;

        // PUBLIC: clientul deschide pagina cu token-ul primit pe WhatsApp
        case 'getContractByToken':
            ensureContracteSchema($db);
            checkContractRateLimit($db, 'view', 30);  // max 30/min/IP
            $token = isset($_GET['token']) ? trim($_GET['token']) : '';
            if (!$token || !preg_match('/^[a-f0-9]{32}$/', $token)) {
                jsonResponse(['success' => false, 'error' => 'Token invalid'], 400);
            }
            $stmt = $db->prepare("SELECT c.id, c.contract_nr, c.tip_client, c.status, c.date_completate,
                                         c.adresa_instalare, c.avans_procent, c.termen_plata_zile,
                                         c.valoare_total, c.token_expires_at, c.locked_resubmit, c.completat_la,
                                         o.obiectiv, o.oferta_id AS oferta_cod,
                                         cl.nume AS client_nume, cl.telefon AS client_telefon, cl.email AS client_email
                                  FROM contracte c
                                  LEFT JOIN clienti cl ON c.client_id = cl.id
                                  LEFT JOIN oferte o ON c.oferta_id = o.id
                                  WHERE c.token = ?");
            $stmt->execute([$token]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['success' => false, 'error' => 'Link invalid'], 404);
            // Verific expirare
            if (!empty($row['token_expires_at']) && strtotime($row['token_expires_at']) < time()) {
                logContractAccess($db, $row['id'], 'view_expired', 'token expirat');
                jsonResponse(['success' => false, 'error' => 'Link expirat. Solicitați unul nou de la CSSI.', 'expired' => true], 410);
            }
            // Decrypt date sensibile pt prefill
            if (!empty($row['date_completate'])) {
                $dec = json_decode($row['date_completate'], true);
                if (is_array($dec)) {
                    foreach (['cnp','ci_seria','ci_numar'] as $f) {
                        if (!empty($dec[$f])) $dec[$f] = decryptSensitive($dec[$f]);
                    }
                    $row['date_completate'] = $dec;
                }
            }
            if (empty($row['status'])) $row['status'] = $row['date_completate'] ? 'completat' : 'asteapta_date';
            $row['locked_resubmit'] = !empty($row['locked_resubmit']);
            logContractAccess($db, $row['id'], 'view_public');
            jsonResponse(['success' => true, 'data' => $row]);
            break;

        // PUBLIC: clientul submite datele
        case 'submitContractDate':
            ensureContracteSchema($db);
            checkContractRateLimit($db, 'submit', 5);  // max 5 submit/min/IP
            $token = isset($data['token']) ? trim($data['token']) : '';
            if (!$token || !preg_match('/^[a-f0-9]{32}$/', $token)) {
                jsonResponse(['success' => false, 'error' => 'Token invalid'], 400);
            }
            $stmt = $db->prepare("SELECT id, status, client_id, token_expires_at, locked_resubmit, completat_la FROM contracte WHERE token = ?");
            $stmt->execute([$token]);
            $row = $stmt->fetch();
            if (!$row) jsonResponse(['success' => false, 'error' => 'Link invalid'], 404);
            // Verific expirare
            if (!empty($row['token_expires_at']) && strtotime($row['token_expires_at']) < time()) {
                logContractAccess($db, $row['id'], 'submit_expired');
                jsonResponse(['success' => false, 'error' => 'Link expirat. Solicitați unul nou de la CSSI.', 'expired' => true], 410);
            }
            // Single-use: dupa primul submit, blocat. Pentru modificari -> contact CSSI
            if (!empty($row['locked_resubmit'])) {
                logContractAccess($db, $row['id'], 'submit_locked');
                jsonResponse(['success' => false, 'error' => 'Datele au fost deja transmise. Pentru modificări, contactați CSSI.', 'locked' => true], 423);
            }
            // GDPR consent obligatoriu
            if (empty($data['gdpr_consent'])) {
                jsonResponse(['success' => false, 'error' => 'Consimțământul GDPR este obligatoriu (bifează caseta).'], 400);
            }
            $tipClient = isset($data['tip_client']) && in_array($data['tip_client'], ['PF','PJ'], true) ? $data['tip_client'] : 'PF';
            // Sanitizam datele + cap lungime (anti-DOS)
            $cap = function($s, $max = 500) { $s = trim((string)$s); return mb_strlen($s) > $max ? mb_substr($s, 0, $max) : $s; };
            $dateCompletate = [];
            if ($tipClient === 'PF') {
                $dateCompletate = [
                    'nume'        => $cap($data['nume'] ?? '', 150),
                    'cnp'         => encryptSensitive($cap($data['cnp'] ?? '', 13)),
                    'ci_seria'    => encryptSensitive($cap($data['ci_seria'] ?? '', 5)),
                    'ci_numar'    => encryptSensitive($cap($data['ci_numar'] ?? '', 10)),
                    'domiciliu'   => $cap($data['domiciliu'] ?? '', 500),
                    'telefon'     => $cap($data['telefon'] ?? '', 30),
                    'email'       => $cap($data['email'] ?? '', 150),
                ];
                if (!$dateCompletate['nume']) jsonResponse(['success' => false, 'error' => 'Nume obligatoriu'], 400);
            } else {
                $dateCompletate = [
                    'denumire'         => $cap($data['denumire'] ?? '', 200),
                    'cui'              => $cap($data['cui'] ?? '', 30),
                    'reg_com'          => $cap($data['reg_com'] ?? '', 50),
                    'sediu'            => $cap($data['sediu'] ?? '', 500),
                    'reprezentant'     => $cap($data['reprezentant'] ?? '', 150),
                    'functia'          => $cap($data['functia'] ?? '', 100),
                    'cont_iban'        => $cap($data['cont_iban'] ?? '', 35),
                    'banca'            => $cap($data['banca'] ?? '', 100),
                    'telefon'          => $cap($data['telefon'] ?? '', 30),
                    'email'            => $cap($data['email'] ?? '', 150),
                ];
                if (!$dateCompletate['denumire']) jsonResponse(['success' => false, 'error' => 'Denumire firmă obligatorie'], 400);
                if (!$dateCompletate['cui'])      jsonResponse(['success' => false, 'error' => 'CUI obligatoriu'], 400);
            }
            $adresaInst = trim($data['adresa_instalare'] ?? '');
            $avansProc  = isset($data['avans_procent']) ? floatval($data['avans_procent']) : 35;
            if ($avansProc < 0 || $avansProc > 100) $avansProc = 35;
            $termenZile = isset($data['termen_plata_zile']) ? intval($data['termen_plata_zile']) : 15;

            // Normalizam status — ENUM vechi poate retrieve gol; tratam ca asteapta_date
            $curStatus = empty($row['status']) ? 'asteapta_date' : $row['status'];
            $newStatus = ($curStatus === 'asteapta_date') ? 'completat' : $curStatus;
            $clientIP = substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45);
            // Single-use: dupa submit, lock + token expira in 24h (pt confirmare la reload)
            $newTokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $db->prepare("UPDATE contracte SET tip_client=?, date_completate=?, adresa_instalare=?, avans_procent=?, termen_plata_zile=?, status=?, completat_la=NOW(), locked_resubmit=1, token_expires_at=?, gdpr_consent_at=NOW(), gdpr_consent_ip=? WHERE id=?")
               ->execute([$tipClient, json_encode($dateCompletate, JSON_UNESCAPED_UNICODE), $adresaInst, $avansProc, $termenZile, $newStatus, $newTokenExpiry, $clientIP, $row['id']]);
            logContractAccess($db, $row['id'], 'submit_data', 'tip=' . $tipClient . ' avans=' . $avansProc . '%');

            // Notificare admin (Roxana)
            try {
                $stmtN = $db->prepare("SELECT p.proiect_id FROM contracte c LEFT JOIN proiecte p ON c.proiect_id = p.id WHERE c.id = ?");
                $stmtN->execute([$row['id']]);
                $nRow = $stmtN->fetch();
                if ($nRow && $nRow['proiect_id']) {
                    $msg = '📄 Client a completat datele pentru contract (' . ($dateCompletate['nume'] ?? $dateCompletate['denumire'] ?? 'fără nume') . ')';
                    $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la) VALUES (?,?,?,?)")->execute([$nRow['proiect_id'], $msg, 'contract', 'Client']);
                }
            } catch (Exception $e) { /* ignore */ }

            jsonResponse(['success' => true, 'status' => $newStatus]);
            break;

        case 'updateContract':
            ensureContracteSchema($db);
            requireAuth();
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
            $fields = []; $values = [];
            foreach (['adresa_instalare','note','status'] as $f) {
                if (isset($data[$f])) { $fields[] = "$f = ?"; $values[] = $data[$f]; }
            }
            foreach (['avans_procent','termen_plata_zile','durata_executie_zile','garantie_luni','valoare_net','valoare_tva','valoare_total'] as $f) {
                if (isset($data[$f])) { $fields[] = "$f = ?"; $values[] = floatval($data[$f]); }
            }
            if (isset($data['date_completate']) && is_array($data['date_completate'])) {
                $fields[] = "date_completate = ?";
                $values[] = json_encode($data['date_completate'], JSON_UNESCAPED_UNICODE);
            }
            if (isset($data['tip_client']) && in_array($data['tip_client'], ['PF','PJ'], true)) {
                $fields[] = "tip_client = ?"; $values[] = $data['tip_client'];
            }
            if (isset($data['locked_resubmit'])) {
                $fields[] = "locked_resubmit = ?"; $values[] = $data['locked_resubmit'] ? 1 : 0;
            }
            if (!$fields) jsonResponse(['success' => false, 'error' => 'Nimic de actualizat'], 400);
            $values[] = $id;
            $db->prepare("UPDATE contracte SET " . implode(', ', $fields) . " WHERE id = ?")->execute($values);
            logContractAccess($db, $id, 'admin_update', implode(',', array_map(function($f){ return preg_replace('/\s*=.*/', '', $f); }, $fields)));
            jsonResponse(['success' => true]);
            break;

        // Endpoint nou: audit log pt un contract
        case 'getContractAccessLog':
            requireAuth();
            ensureContractAccessLog($db);
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$id) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
            $stmt = $db->prepare("SELECT id, action, user_id, ip, ts, details FROM contract_access_log WHERE contract_id = ? ORDER BY ts DESC LIMIT 100");
            $stmt->execute([$id]);
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'regenerateContractToken':
            ensureContracteSchema($db);
            requireAuth();
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
            $newToken = generateContractToken();
            $newExpiresAt = date('Y-m-d H:i:s', strtotime('+14 days'));
            // Regenerare token = nou TTL 14 zile + permite re-completare (unlock)
            $db->prepare("UPDATE contracte SET token = ?, token_expires_at = ?, locked_resubmit = 0 WHERE id = ?")
               ->execute([$newToken, $newExpiresAt, $id]);
            logContractAccess($db, $id, 'token_regenerated', 'expires=' . $newExpiresAt);
            jsonResponse(['success' => true, 'token' => $newToken, 'expires_at' => $newExpiresAt]);
            break;

        case 'createContractDraft':
            ensureContracteSchema($db);
            requireAuth();
            $ofertaId = isset($data['oferta_id']) ? intval($data['oferta_id']) : 0;
            if (!$ofertaId) jsonResponse(['success' => false, 'error' => 'oferta_id obligatoriu'], 400);
            // Verific dacă există deja contract pentru această ofertă
            $chk = $db->prepare("SELECT id, token FROM contracte WHERE oferta_id = ? LIMIT 1");
            $chk->execute([$ofertaId]);
            $existing = $chk->fetch();
            if ($existing) jsonResponse(['success' => true, 'id' => $existing['id'], 'token' => $existing['token'], 'existed' => true]);
            // Iau detaliile ofertei
            $stmtO = $db->prepare("SELECT o.id, o.client_id, o.proiect_id, o.total_fara_tva, o.tva, o.total_cu_tva, c.tip FROM oferte o LEFT JOIN clienti c ON o.client_id = c.id WHERE o.id = ?");
            $stmtO->execute([$ofertaId]);
            $o = $stmtO->fetch();
            if (!$o) jsonResponse(['success' => false, 'error' => 'Oferta inexistentă'], 404);
            // proiect_id e NOT NULL in tabela veche — fallback: cel mai recent proiect al clientului
            $pid = intval($o['proiect_id'] ?? 0);
            if (!$pid && $o['client_id']) {
                $stmtFP = $db->prepare("SELECT id FROM proiecte WHERE client_id = ? ORDER BY created_at DESC LIMIT 1");
                $stmtFP->execute([$o['client_id']]);
                $fpRow = $stmtFP->fetch();
                if ($fpRow) $pid = intval($fpRow['id']);
            }
            if (!$pid) jsonResponse(['success' => false, 'error' => 'Oferta nu are proiect asociat și nici client cu proiect existent. Creează proiect înainte sau atribuie ofertei un proiect.'], 400);
            $contractNr = 'C-' . date('Y') . '-' . str_pad($ofertaId, 4, '0', STR_PAD_LEFT);
            $token = generateContractToken();
            $tipClient = stripos($o['tip'] ?? '', 'jurid') !== false || stripos($o['tip'] ?? '', 'firma') !== false ? 'PJ' : 'PF';
            $userCur = currentUser();
            $createdBy = $userCur['username'] ?? 'Admin';
            $expiresAt = date('Y-m-d H:i:s', strtotime('+14 days'));
            $db->prepare("INSERT INTO contracte (contract_nr, oferta_id, proiect_id, client_id, token, tip_client, status, valoare_net, valoare_tva, valoare_total, created_by, token_expires_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
               ->execute([$contractNr, $ofertaId, $pid, $o['client_id'], $token, $tipClient, 'asteapta_date', $o['total_fara_tva'], $o['tva'], $o['total_cu_tva'], $createdBy, $expiresAt]);
            $newId = $db->lastInsertId();
            logContractAccess($db, $newId, 'create_draft', 'manual via createContractDraft');
            jsonResponse(['success' => true, 'id' => $newId, 'token' => $token, 'contract_nr' => $contractNr, 'expires_at' => $expiresAt]);
            break;

        // Raport zilnic — preview HTML (vede cum arată email-ul)
        case 'raportZilnicPreview':
            requireAuth();
            require_once __DIR__ . '/raport-helpers.php';
            $data = cssiCollectRaportData($db);
            $format = $_GET['format'] ?? 'html';
            if ($format === 'text') {
                header('Content-Type: text/plain; charset=utf-8');
                echo cssiRenderRaportText($data);
            } else {
                header('Content-Type: text/html; charset=utf-8');
                echo cssiRenderRaportHtml($data);
            }
            exit;

        // Raport zilnic — trimite manual ACUM (admin only)
        case 'raportZilnicSendNow':
            requireAdmin();
            require_once __DIR__ . '/raport-helpers.php';
            // Recipients
            $recipients = [];
            if (defined('REPORT_RECIPIENTS') && REPORT_RECIPIENTS) {
                $recipients = array_filter(array_map('trim', explode(',', REPORT_RECIPIENTS)));
            }
            if (!$recipients) {
                try {
                    $stmt = $db->query("SELECT value FROM cssi_settings WHERE `key` = 'report_recipients' LIMIT 1");
                    $row = $stmt ? $stmt->fetch() : null;
                    if ($row && $row['value']) $recipients = array_filter(array_map('trim', explode(',', $row['value'])));
                } catch (Exception $e) {}
            }
            // Override prin POST data (admin trimite la o adresă specifică)
            if (!empty($data['to'])) {
                $recipients = array_filter(array_map('trim', explode(',', $data['to'])));
            }
            if (!$recipients) jsonResponse(['success' => false, 'error' => 'Niciun destinatar — setează REPORT_RECIPIENTS în secrets.php sau prin setReportRecipients'], 400);

            $rData = cssiCollectRaportData($db);
            $subject = '📊 Raport Zilnic CSSI — ' . date('d.m.Y') . ' (manual)';
            $bodyHtml = cssiRenderRaportHtml($rData);
            $bodyText = cssiRenderRaportText($rData);
            $result = cssiSendRaportEmail($recipients, $subject, $bodyHtml, $bodyText);
            cssiLogCronEmail($db, 'raport_zilnic_manual', $recipients, $result['sent'], $result['failed'], [
                'kpi' => $rData['kpi'],
                'triggered_by' => currentUser()['username'] ?? '?',
                'from' => $result['from'] ?? '',
                'errors' => $result['errors'] ?? [],
            ]);
            jsonResponse([
                'success' => true,
                'sent' => $result['sent'],
                'failed' => $result['failed'],
                'recipients' => $recipients,
                'from' => $result['from'] ?? '',
                'errors' => $result['errors'] ?? [],
                'note' => 'PHP mail() a returnat ' . ($result['sent'] > 0 ? 'true (acceptat de MTA local)' : 'false') . '. Dacă nu primești email, verifică: 1) folder SPAM, 2) că office@cssi.ro există ca mailbox cPanel, 3) SPF record pt cssi.ro acceptă cPanel server.',
            ]);
            break;

        case 'getCronLog':
            requireAuth();
            $script = $_GET['script'] ?? 'raport_zilnic';
            try {
                $stmt = $db->prepare("SELECT id, script, status, recipients_count, success_count, failed_count, ts FROM cron_log WHERE script = ? ORDER BY ts DESC LIMIT 30");
                $stmt->execute([$script]);
                jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
            } catch (Exception $e) {
                jsonResponse(['success' => true, 'data' => [], 'note' => 'Tabelul cron_log încă nu există (apare după primul raport trimis)']);
            }
            break;

        // Settings: get/set raport recipients (alternativ la secrets.php)
        case 'getReportRecipients':
            requireAdmin();
            try { $db->exec("CREATE TABLE IF NOT EXISTS cssi_settings (`key` VARCHAR(60) PRIMARY KEY, value TEXT, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"); } catch (Exception $e) {}
            $fromSecrets = defined('REPORT_RECIPIENTS') ? REPORT_RECIPIENTS : '';
            $fromDb = '';
            try {
                $stmt = $db->query("SELECT value FROM cssi_settings WHERE `key` = 'report_recipients' LIMIT 1");
                $row = $stmt ? $stmt->fetch() : null;
                if ($row) $fromDb = $row['value'];
            } catch (Exception $e) {}
            jsonResponse(['success' => true, 'from_secrets' => $fromSecrets, 'from_db' => $fromDb, 'effective' => $fromSecrets ?: $fromDb]);
            break;

        case 'setReportRecipients':
            requireAdmin();
            $val = trim($data['recipients'] ?? '');
            try {
                $db->exec("CREATE TABLE IF NOT EXISTS cssi_settings (`key` VARCHAR(60) PRIMARY KEY, value TEXT, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $db->prepare("INSERT INTO cssi_settings (`key`, value) VALUES ('report_recipients', ?) ON DUPLICATE KEY UPDATE value = VALUES(value)")->execute([$val]);
                jsonResponse(['success' => true]);
            } catch (Exception $e) {
                jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
            }
            break;

        case 'deleteContract':
            ensureContracteSchema($db);
            requireAdmin();
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
            $db->prepare("DELETE FROM contracte WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        // Genereaza contractul in format Word (.doc — HTML cu MIME Word)
        // sau PDF (HTML print-friendly cu auto-print). Folosit prin link direct
        // <a href="api.php?action=generateContractDoc&id=X&format=word|pdf">
        case 'generateContractDoc':
            ensureContracteSchema($db);
            requireAuth();
            // Restrict acces la roluri responsabile (anti-leak la tehnicieni)
            $uCur = currentUser();
            $allowedRoles = ['admin','sales','org'];
            if (!$uCur || !in_array($uCur['role'] ?? '', $allowedRoles, true)) {
                http_response_code(403); echo 'Acces interzis pentru rolul tau'; exit;
            }
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $format = isset($_GET['format']) ? $_GET['format'] : 'word';
            if (!$id) { http_response_code(400); echo 'id obligatoriu'; exit; }
            if (!in_array($format, ['word','pdf'], true)) $format = 'word';
            logContractAccess($db, $id, 'download_' . $format);

            $stmt = $db->prepare("SELECT c.*, o.oferta_id AS oferta_cod, o.data_oferta, o.obiectiv, p.proiect_id AS proiect_cod, p.serviciu, p.adresa_obiectiv FROM contracte c LEFT JOIN oferte o ON c.oferta_id = o.id LEFT JOIN proiecte p ON c.proiect_id = p.id WHERE c.id = ?");
            $stmt->execute([$id]);
            $c = $stmt->fetch();
            if (!$c) { http_response_code(404); echo 'Contract inexistent'; exit; }

            $d = !empty($c['date_completate']) ? json_decode($c['date_completate'], true) : [];
            // Decrypt câmpurile sensibile (CNP, CI seria/nr) pentru document
            if (is_array($d)) {
                foreach (['cnp','ci_seria','ci_numar'] as $f) {
                    if (!empty($d[$f])) $d[$f] = decryptSensitive($d[$f]);
                }
            }
            $p = prestatorData();
            $tipPj = ($c['tip_client'] ?? 'PF') === 'PJ';
            $contractNr = $c['contract_nr'] ?? ('#' . $c['id']);
            $dataC = date('d.m.Y');
            $netto = floatval($c['valoare_net']);
            $tva   = floatval($c['valoare_tva']);
            $total = floatval($c['valoare_total']);
            $avansP = floatval($c['avans_procent'] ?? 35);
            $diferentaP = 100 - $avansP;
            $durata = intval($c['durata_executie_zile'] ?? 20);
            $garantie = intval($c['garantie_luni'] ?? 24);
            $sistem = strtolower($c['serviciu'] ?? 'supraveghere video');
            $adresa = $c['adresa_instalare'] ?: ($c['adresa_obiectiv'] ?? '');
            $oferta_data = $c['data_oferta'] ? date('d.m.Y', strtotime($c['data_oferta'])) : '';

            // Beneficiar bloc
            if ($tipPj) {
                $benefBloc = htmlspecialchars($d['denumire'] ?? '— denumire firmă —', ENT_QUOTES, 'UTF-8');
                $benefBloc .= ', cu sediul în ' . htmlspecialchars($d['sediu'] ?? '— sediu —', ENT_QUOTES, 'UTF-8');
                if (!empty($d['reg_com'])) $benefBloc .= ', înregistrată la Registrul Comerțului sub nr. ' . htmlspecialchars($d['reg_com'], ENT_QUOTES, 'UTF-8');
                if (!empty($d['cui']))     $benefBloc .= ', CIF ' . htmlspecialchars($d['cui'], ENT_QUOTES, 'UTF-8');
                if (!empty($d['cont_iban'])) $benefBloc .= ', cont IBAN ' . htmlspecialchars($d['cont_iban'], ENT_QUOTES, 'UTF-8') . (!empty($d['banca']) ? ' deschis la ' . htmlspecialchars($d['banca'], ENT_QUOTES, 'UTF-8') : '');
                if (!empty($d['reprezentant'])) $benefBloc .= ', reprezentată prin ' . htmlspecialchars($d['reprezentant'], ENT_QUOTES, 'UTF-8') . (!empty($d['functia']) ? ', în calitate de ' . htmlspecialchars($d['functia'], ENT_QUOTES, 'UTF-8') : '');
                $benefSemnatura = htmlspecialchars(($d['denumire'] ?? '') . ($d['reprezentant'] ? ' (prin ' . $d['reprezentant'] . ')' : ''), ENT_QUOTES, 'UTF-8');
            } else {
                $benefBloc = 'Dl./Dna. ' . htmlspecialchars($d['nume'] ?? '— nume —', ENT_QUOTES, 'UTF-8');
                if (!empty($d['domiciliu'])) $benefBloc .= ', cu domiciliul în ' . htmlspecialchars($d['domiciliu'], ENT_QUOTES, 'UTF-8');
                if (!empty($d['ci_seria']) || !empty($d['ci_numar'])) $benefBloc .= ', legitimat cu CI seria ' . htmlspecialchars($d['ci_seria'] ?? '', ENT_QUOTES, 'UTF-8') . ' nr. ' . htmlspecialchars($d['ci_numar'] ?? '', ENT_QUOTES, 'UTF-8');
                if (!empty($d['cnp'])) $benefBloc .= ', CNP ' . htmlspecialchars($d['cnp'], ENT_QUOTES, 'UTF-8');
                $benefSemnatura = htmlspecialchars($d['nume'] ?? '', ENT_QUOTES, 'UTF-8');
            }
            $emailBenef = htmlspecialchars($d['email'] ?? '', ENT_QUOTES, 'UTF-8');

            // Construim HTML-ul contractului
            ob_start();
            ?><!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<title>Contract <?= htmlspecialchars($contractNr) ?></title>
<style>
@page { margin: 2cm 1.8cm; }
body { font-family: Calibri, 'Trebuchet MS', sans-serif; font-size: 11pt; color: #000; line-height: 1.5; max-width: 18cm; margin: 0 auto; padding: 1cm; }
h1 { text-align: center; font-size: 16pt; font-weight: bold; margin-bottom: 8px; }
h1 + h2 { text-align: center; font-size: 13pt; font-weight: bold; margin-bottom: 18px; }
h3 { font-size: 12pt; font-weight: bold; margin-top: 14px; margin-bottom: 6px; }
p { margin-bottom: 8px; text-align: justify; }
.art { margin-bottom: 6px; }
.semnaturi { display: table; width: 100%; margin-top: 30px; }
.semnaturi .col { display: table-cell; width: 50%; text-align: center; vertical-align: top; padding-top: 10px; }
.semnaturi .col strong { display: block; margin-bottom: 40px; }
.print-btn { position: fixed; top: 20px; right: 20px; padding: 12px 20px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 1000; }
@media print { .print-btn { display: none; } }
</style>
</head>
<body>
<?php if ($format === 'pdf'): ?>
<button class="print-btn" onclick="window.print()">🖨️ Print / Salvează ca PDF</button>
<script>setTimeout(function(){window.print();}, 800);</script>
<?php endif; ?>

<h1>CONTRACT DE PRESTĂRI SERVICII</h1>
<h2>Nr. <?= htmlspecialchars($contractNr) ?> din <?= $dataC ?></h2>

<p><strong>Încheiat între:</strong></p>
<p><strong><?= htmlspecialchars($p['denumire']) ?></strong>, cu sediul în <?= htmlspecialchars($p['sediu']) ?>,
înregistrată la Registrul Comerțului sub nr. <?= htmlspecialchars($p['reg_com']) ?>, având cod fiscal
<?= htmlspecialchars($p['cif']) ?> și cont nr. <?= htmlspecialchars($p['cont_iban']) ?> deschis la
<?= htmlspecialchars($p['banca']) ?>, în calitate de <strong>PRESTATOR</strong>, reprezentată prin
<?= htmlspecialchars($p['reprezentant']) ?>,</p>
<p><strong>Și</strong></p>
<p><?= $benefBloc ?>, în calitate de <strong>BENEFICIAR</strong>, au convenit să încheie prezentul contract,
cu respectarea următoarelor clauze:</p>

<h3>I. OBIECTUL CONTRACTULUI</h3>
<p class="art"><strong>Art.1.</strong> PRESTATORUL asigură instalarea unui sistem de <?= htmlspecialchars($sistem) ?> conform
<?= $c['oferta_cod'] ? 'ofertei nr. <strong>' . htmlspecialchars($c['oferta_cod']) . '</strong>' . ($oferta_data ? ' din ' . $oferta_data : '') . ', atașată la contract' : 'specificațiilor agreate' ?>.</p>
<p class="art"><strong>Art.2.</strong> Lucrarea se va efectua în <?= htmlspecialchars($adresa ?: '— adresa instalare —') ?>.</p>

<h3>II. TERMENUL CONTRACTULUI</h3>
<p class="art"><strong>Art.3.</strong> Prezentul contract se încheie pentru o durată de <strong><?= $durata ?> de zile</strong>
și intră în vigoare la data semnării lui de către părți.</p>
<p class="art"><strong>Art.4.</strong> Prezentul contract definește și condițiile de vânzare și montare a sistemelor și accesoriilor,
denumite în continuare „MĂRFURI", comercializate de către prestator și achiziționate conform facturii de către beneficiar.</p>

<h3>III. PREȚ ȘI MODALITĂȚI DE PLATĂ</h3>
<p class="art"><strong>Art.5.</strong> Prețul contractului este de <strong><?= number_format($netto, 2, ',', '.') ?> lei</strong>,
la care se adaugă TVA în valoare de <strong><?= number_format($tva, 2, ',', '.') ?> lei</strong>.
Valoare contract cu TVA inclus: <strong><?= number_format($total, 2, ',', '.') ?> lei</strong>.</p>
<p class="art"><strong>Art.6.</strong> Plata contractului se va efectua cu <strong>avans de <?= rtrim(rtrim(number_format($avansP, 2, ',', '.'), '0'), ',') ?>%</strong>
la data semnării contractului, diferența de <strong><?= rtrim(rtrim(number_format($diferentaP, 2, ',', '.'), '0'), ',') ?>%</strong>
se va achita la data finalizării lucrării și semnării procesului verbal de recepție.</p>
<p class="art"><strong>Art.7.</strong> Beneficiarul va achita contravaloarea facturilor emise la data scadenței trecute pe facturi.
Pentru orice întârziere de plată, Beneficiarul va fi obligat și la achitarea unei penalități de <strong>0,5% pe zi</strong>
din soldul scadent.</p>

<h3>IV. GARANȚII</h3>
<p class="art"><strong>Art.8.</strong> PRESTATORUL are obligația ca în cadrul termenului de garanție să remedieze deficiențele
sau viciile ascunse provenite din culpa sa (cu excepția celor care se datorează culpei Beneficiarului), semnalate de
beneficiar pe durata perioadei de garanție, în termen de 3 zile de la data înregistrării cererii Beneficiarului.</p>
<p class="art"><strong>Art.9.</strong> PRESTATORUL oferă garanție pentru echipamentele instalate de <strong><?= $garantie ?> luni</strong>
de la data predării lucrării.</p>
<p class="art"><strong>Art.10.</strong> Echipamentele instalate își vor pierde garanția în cazul în care acestea suferă
intervenții ale unor persoane neautorizate.</p>
<p class="art"><strong>Art.11.</strong> Garanția oferită de PRESTATOR nu acoperă daunele survenite în urma unor acte de
vandalism, incendii, inundații, cutremure, descărcări electrice sau alte calamități naturale.</p>

<h3>V. OBLIGAȚIILE PĂRȚILOR</h3>
<p class="art"><strong>Art.12.</strong> Prestatorul de servicii se obligă: să presteze lucrările comandate de către
BENEFICIAR în termen de maxim <?= $durata ?> zile de la data semnării contractului.</p>
<p class="art"><strong>Art.13.</strong> Beneficiarul se obligă să:</p>
<p style="padding-left: 20px;">a) achite contravaloarea facturilor emise de către PRESTATOR;<br>
b) creeze front de lucru PRESTATORULUI la locația unde se va efectua lucrarea;<br>
c) verifice, la finalul lucrărilor, calitatea acestora;<br>
d) respecte toate indicațiile pe care le-a primit de la PRESTATOR în legătură cu modul de manipulare a instalației și să folosească instalația doar în scopul pentru care a fost executată.</p>

<h3>VI. CONDIȚII ÎNCETARE CONTRACT</h3>
<p>Contractul încetează în următoarele condiții:</p>
<p style="padding-left: 20px;">a) în cazul în care una dintre părți nu își execută sau își execută necorespunzător oricare dintre obligațiile asumate, prezentul contract se reziliază de plin drept, fără punere în întârziere și fără intervenția instanței de judecată, cu plata de daune interese, în condițiile prevăzute de art. 1066 Cod Civil, în valoarea contractului;<br>
b) rezilierea de către oricare dintre părțile contractante, cu un preaviz de 15 zile lucrătoare;<br>
c) falimentul uneia dintre părți.</p>

<h3>VII. FORȚA MAJORĂ</h3>
<p>Forța majoră, așa cum este definită de lege, apără și exonerează partea care o invocă, în condițiile legii, cu condiția
notificării, în termen de 5 (cinci) zile de la producerea evenimentului, cu viza Camerei de Comerț și Industrie a României.</p>

<h3>VIII. LITIGII</h3>
<p>Litigiile ce pot decurge din prezentul contract se vor soluționa pe cale amiabilă. În cazul în care acest lucru nu este
posibil, litigiul va fi dedus spre soluționare instanței competente din județul prestatorului.</p>

<h3>IX. PRELUCRAREA DATELOR CU CARACTER PERSONAL</h3>
<p>1. Datele cu caracter personal furnizate de fiecare Parte cu privire la reprezentantul legal și/sau ale persoanei de
contact (nume, prenume, email, telefon) vor fi prelucrate exclusiv în scopul încheierii și executării prezentului Contract,
pe întreaga durată a Contractului.</p>
<p>2. Părțile se obligă să păstreze confidențialitatea datelor cu caracter personal și să implementeze măsurile tehnice
necesare pentru securitatea acestora, conform GDPR (Regulamentul UE 2016/679).</p>
<p>3. La încetarea Contractului, fiecare Parte se obligă să înceteze prelucrarea datelor, cu excepția cazurilor în care o
obligație legală impune prelucrarea în continuare sau exercitarea unor drepturi în instanță.</p>

<h3>X. NOTIFICĂRI</h3>
<p>Orice notificare, comunicare sau alte informări referitoare la prezentul Contract vor fi efectuate în scris sau trimise
prin scrisoare recomandată cu confirmare de primire sau prin e-mail la adresele:</p>
<p style="padding-left: 20px;">- pentru PRESTATOR: <?= htmlspecialchars($p['email']) ?><br>
- pentru BENEFICIAR: <?= $emailBenef ?: '—' ?></p>

<h3>XI. RĂSPUNDEREA CONTRACTUALĂ</h3>
<p>Părțile, prin semnarea prezentului contract, convin asupra valabilității tuturor clauzelor înscrise, drept pentru care
s-a încheiat prezentul contract în două exemplare, câte unul pentru fiecare parte, având aceeași valoare și forță probantă,
astăzi data semnării.</p>

<div class="semnaturi">
    <div class="col">
        <strong>PRESTATOR,</strong>
        <?= htmlspecialchars($p['denumire']) ?>
    </div>
    <div class="col">
        <strong>BENEFICIAR,</strong>
        <?= $benefSemnatura ?: '—' ?>
    </div>
</div>

</body>
</html><?php
            $html = ob_get_clean();

            $safeName = preg_replace('/[^A-Za-z0-9_-]/', '_', $contractNr);
            if ($format === 'word') {
                header('Content-Type: application/msword; charset=utf-8');
                header('Content-Disposition: attachment; filename="contract-' . $safeName . '.doc"');
                header('Cache-Control: max-age=0');
                echo $html;
            } else {
                // PDF: trimitem HTML inline pentru ca browser-ul sa-l deschida + auto-print
                header('Content-Type: text/html; charset=utf-8');
                echo $html;
            }
            // Marcam status='generat' daca era 'completat'
            if ($c['status'] === 'completat') {
                try { $db->prepare("UPDATE contracte SET status='generat', generat_la=NOW() WHERE id=?")->execute([$id]); } catch (Exception $e) {}
            }
            exit;

        // ══════════════════════════════════════
        // PROIECTARE
        // ══════════════════════════════════════
        case 'getProiectare':
            $pid = (isset($_GET['proiect_id']) ? $_GET['proiect_id'] : 0);
            if (!$pid) { jsonResponse(['success' => false, 'error' => 'proiect_id obligatoriu'], 400); break; }
            
            // Caută sau creează automat
            $stmt = $db->prepare("SELECT pr.*, p.proiect_id AS cod, p.serviciu, p.status AS proiect_status, p.valoare_contract, p.responsabil, p.adresa_obiectiv, p.obiectiv, p.note AS proiect_note, c.nume AS client_nume, c.telefon, c.email, c.persoana_contact, c.adresa AS client_adresa FROM proiectare pr JOIN proiecte p ON pr.proiect_id = p.id JOIN clienti c ON p.client_id = c.id WHERE pr.proiect_id = ? OR p.proiect_id = ?");
            $stmt->execute([$pid, $pid]);
            $row = $stmt->fetch();
            
            if (!$row) {
                // Auto-creează record proiectare
                $numericId = $pid;
                if (!is_numeric($pid)) {
                    $s = $db->prepare("SELECT id FROM proiecte WHERE proiect_id = ?");
                    $s->execute([$pid]);
                    $r = $s->fetch();
                    $numericId = $r ? $r['id'] : 0;
                }
                if ($numericId) {
                    // Determină serviciul pentru checklist dinamic
                    $srvStmt = $db->prepare("SELECT serviciu FROM proiecte WHERE id = ?");
                    $srvStmt->execute([$numericId]);
                    $srvRow = $srvStmt->fetch();
                    $serviciu = $srvRow ? $srvRow['serviciu'] : '';
                    
                    // Checklist de bază (pentru toate proiectele)
                    $checklist = [
                        ['id'=>'vizita_teren','label'=>'Vizită teren efectuată','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'schema_electrica','label'=>'Schemă electrică creată','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'plan_amplasare','label'=>'Plan amplasare echipamente','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'trasee_cabluri','label'=>'Trasee cabluri proiectate','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'necesar_materiale','label'=>'Necesar materiale verificat','done'=>false,'date'=>null,'user'=>null],
                    ];
                    
                    // IGPR: Supraveghere Video, Alarmă/Efracție, Complex
                    $needsIGPR = in_array($serviciu, ['Supraveghere Video','Alarma','Complex']);
                    if ($needsIGPR) {
                        $checklist[] = ['id'=>'aviz_igpr_depus','label'=>'👮 Aviz IGPR depus','done'=>false,'date'=>null,'user'=>null];
                        $checklist[] = ['id'=>'aviz_igpr_obtinut','label'=>'👮 Aviz IGPR obținut','done'=>false,'date'=>null,'user'=>null];
                    }
                    
                    // ISU: Detecție Incendiu, Complex
                    $needsISU = in_array($serviciu, ['Detectie Incendiu','Complex']);
                    if ($needsISU) {
                        $checklist[] = ['id'=>'aviz_isu_depus','label'=>'🛡️ Aviz ISU depus','done'=>false,'date'=>null,'user'=>null];
                        $checklist[] = ['id'=>'aviz_isu_obtinut','label'=>'🛡️ Aviz ISU obținut','done'=>false,'date'=>null,'user'=>null];
                    }
                    
                    // Final
                    $checklist[] = ['id'=>'dosar_complet','label'=>'Dosar proiect complet','done'=>false,'date'=>null,'user'=>null];
                    
                    $defaultChecklist = json_encode($checklist);
                    $db->prepare("INSERT IGNORE INTO proiectare (proiect_id, checklist_json) VALUES (?, ?)")->execute([$numericId, $defaultChecklist]);
                    // Re-fetch
                    $stmt->execute([$numericId, $pid]);
                    $row = $stmt->fetch();
                }
            }
            
            // Adaugă ofertele acceptate
            $oferte = [];
            if ($row) {
                $stmtO = $db->prepare("SELECT o.id, o.oferta_id, o.titlu, o.total_cu_tva, o.status FROM oferte o WHERE (o.proiect_id = ? OR o.client_id = (SELECT client_id FROM proiecte WHERE id = ?)) AND o.status = 'Acceptata' ORDER BY o.created_at");
                $stmtO->execute([$row['proiect_id'], $row['proiect_id']]);
                $oferteRaw = $stmtO->fetchAll();
                foreach ($oferteRaw as &$of) {
                    $stmtL = $db->prepare("SELECT denumire, cod, um, cantitate, pret_vanzare, valoare FROM oferta_linii WHERE oferta_id = ? AND tip = 'echipament' ORDER BY ordine");
                    $stmtL->execute([$of['id']]);
                    $of['echipamente'] = $stmtL->fetchAll();
                }
                $oferte = $oferteRaw;
            }
            
            jsonResponse(['success' => true, 'data' => $row, 'oferte' => $oferte]);
            break;

        case 'updateProiectare':
            $pid = (isset($data['proiect_id']) ? $data['proiect_id'] : 0);
            if (!$pid) { jsonResponse(['success' => false, 'error' => 'proiect_id obligatoriu'], 400); break; }
            $fields = [];
            $values = [];
            foreach (['aviz_isu','aviz_igpr','termen','status','note','proiectant','data_start','checklist_json','progres'] as $f) {
                if (isset($data[$f])) { $fields[] = "$f = ?"; $values[] = $data[$f]; }
            }
            if ($fields && $pid) {
                $values[] = $pid;
                $db->prepare("UPDATE proiectare SET " . implode(', ', $fields) . " WHERE proiect_id = ?")->execute($values);
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Lipsesc date'], 400);
            }
            break;

        case 'toggleChecklist':
            $pid = (isset($data['proiect_id']) ? $data['proiect_id'] : 0);
            $itemId = (isset($data['item_id']) ? $data['item_id'] : '');
            $done = isset($data['done']) ? $data['done'] : false;
            $user = (isset($data['user']) ? $data['user'] : 'Admin');
            if (!$pid || !$itemId) { jsonResponse(['success' => false, 'error' => 'Parametri lipsă'], 400); break; }
            
            $stmt = $db->prepare("SELECT checklist_json FROM proiectare WHERE proiect_id = ?");
            $stmt->execute([$pid]);
            $row = $stmt->fetch();
            if ($row) {
                $checklist = json_decode($row['checklist_json'] ?: '[]', true);
                $totalDone = 0;
                foreach ($checklist as &$item) {
                    if ($item['id'] === $itemId) {
                        $item['done'] = $done;
                        $item['date'] = $done ? date('Y-m-d H:i:s') : null;
                        $item['user'] = $done ? $user : null;
                    }
                    if ($item['done']) $totalDone++;
                }
                unset($item);
                $progres = count($checklist) > 0 ? round(($totalDone / count($checklist)) * 100) : 0;
                $db->prepare("UPDATE proiectare SET checklist_json = ?, progres = ? WHERE proiect_id = ?")->execute([json_encode($checklist), $progres, $pid]);
                jsonResponse(['success' => true, 'progres' => $progres, 'checklist' => $checklist]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Record negăsit'], 404);
            }
            break;

        case 'addJurnalEntry':
            $pid = (isset($data['proiect_id']) ? $data['proiect_id'] : 0);
            $text = (isset($data['text']) ? $data['text'] : '');
            $user = (isset($data['user']) ? $data['user'] : 'Admin');
            if (!$pid || !$text) { jsonResponse(['success' => false, 'error' => 'Parametri lipsă'], 400); break; }
            
            $stmt = $db->prepare("SELECT jurnal_json FROM proiectare WHERE proiect_id = ?");
            $stmt->execute([$pid]);
            $row = $stmt->fetch();
            if ($row) {
                $jurnal = json_decode($row['jurnal_json'] ?: '[]', true);
                array_unshift($jurnal, ['date' => date('Y-m-d H:i:s'), 'user' => $user, 'text' => $text]);
                $db->prepare("UPDATE proiectare SET jurnal_json = ? WHERE proiect_id = ?")->execute([json_encode($jurnal), $pid]);
                jsonResponse(['success' => true, 'jurnal' => $jurnal]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Record negăsit'], 404);
            }
            break;

        // ══════════════════════════════════════
        // JURNAL DE TEREN — pontaj zilnic echipă (cine / unde / ce a făcut)
        // ══════════════════════════════════════
        case 'getJurnalTeren':
        case 'addJurnalTeren':
        case 'updateJurnalTeren':
        case 'deleteJurnalTeren':
            $db->exec("CREATE TABLE IF NOT EXISTS jurnal_teren (
                id INT AUTO_INCREMENT PRIMARY KEY,
                data_start DATE NOT NULL,
                data_end DATE NOT NULL,
                tehnicieni VARCHAR(255) NOT NULL DEFAULT '',
                locatie VARCHAR(255) NOT NULL DEFAULT '',
                proiect_id INT NULL,
                descriere TEXT,
                created_by VARCHAR(80) DEFAULT '',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_jt_data (data_start),
                INDEX idx_jt_proiect (proiect_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $jtUser = currentUser();
            $jtBy = $jtUser ? (($jtUser['display_name'] ?? '') ?: $jtUser['username']) : 'Admin';

            if ($action === 'getJurnalTeren') {
                $where = "1=1"; $params = [];
                if (!empty($_GET['proiect_id'])) { $where .= " AND jt.proiect_id = ?"; $params[] = intval($_GET['proiect_id']); }
                if (!empty($_GET['tehnician']))  { $where .= " AND jt.tehnicieni LIKE ?"; $params[] = '%' . $_GET['tehnician'] . '%'; }
                if (!empty($_GET['from']))       { $where .= " AND jt.data_end >= ?";   $params[] = $_GET['from']; }
                if (!empty($_GET['to']))         { $where .= " AND jt.data_start <= ?"; $params[] = $_GET['to']; }
                if (!empty($_GET['search']))     {
                    $where .= " AND (jt.locatie LIKE ? OR jt.descriere LIKE ? OR jt.tehnicieni LIKE ?)";
                    $s = '%' . $_GET['search'] . '%'; $params[] = $s; $params[] = $s; $params[] = $s;
                }
                $stmt = $db->prepare("SELECT jt.*, p.proiect_id AS proiect_cod, c.nume AS client_nume
                    FROM jurnal_teren jt
                    LEFT JOIN proiecte p ON jt.proiect_id = p.id
                    LEFT JOIN clienti c ON p.client_id = c.id
                    WHERE $where
                    ORDER BY jt.data_start DESC, jt.id DESC
                    LIMIT 500");
                $stmt->execute($params);
                jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
                break;
            }

            if ($action === 'deleteJurnalTeren') {
                $jid = isset($data['id']) ? intval($data['id']) : 0;
                if (!$jid) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
                $own = $db->prepare("SELECT created_by FROM jurnal_teren WHERE id = ?");
                $own->execute([$jid]);
                $jr = $own->fetch();
                if (!$jr) jsonResponse(['success' => false, 'error' => 'Intrare negăsită'], 404);
                $isAdm = $jtUser && (($jtUser['role'] ?? '') === 'admin');
                if (!$isAdm && $jr['created_by'] !== $jtBy) {
                    jsonResponse(['success' => false, 'error' => 'Poți șterge doar intrările proprii (sau ești admin)'], 403);
                }
                $db->prepare("DELETE FROM jurnal_teren WHERE id = ?")->execute([$jid]);
                jsonResponse(['success' => true]);
                break;
            }

            // add / update — validare comună
            $ds  = isset($data['data_start']) ? trim($data['data_start']) : '';
            $de  = isset($data['data_end'])   ? trim($data['data_end'])   : '';
            $teh = isset($data['tehnicieni']) ? trim($data['tehnicieni']) : '';
            $loc = isset($data['locatie'])    ? trim($data['locatie'])    : '';
            $desc= isset($data['descriere'])  ? trim($data['descriere'])  : '';
            $jpid= (isset($data['proiect_id']) && $data['proiect_id'] !== '' && $data['proiect_id'] !== null)
                   ? intval($data['proiect_id']) : null;
            if (!$ds) $ds = date('Y-m-d');
            if (!$de) $de = $ds;
            if ($de < $ds) jsonResponse(['success' => false, 'error' => 'Data sfârșit nu poate fi înainte de data început'], 400);
            if (!$teh)  jsonResponse(['success' => false, 'error' => 'Selectează cel puțin un tehnician'], 400);
            if (!$loc)  jsonResponse(['success' => false, 'error' => 'Locația este obligatorie'], 400);
            if (!$desc) jsonResponse(['success' => false, 'error' => 'Descrierea (ce au făcut) este obligatorie'], 400);

            if ($action === 'addJurnalTeren') {
                $stmt = $db->prepare("INSERT INTO jurnal_teren
                    (data_start, data_end, tehnicieni, locatie, proiect_id, descriere, created_by)
                    VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$ds, $de, $teh, $loc, $jpid, $desc, $jtBy]);
                jsonResponse(['success' => true, 'id' => $db->lastInsertId()]);
            } else { // updateJurnalTeren
                $jid = isset($data['id']) ? intval($data['id']) : 0;
                if (!$jid) jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400);
                $own = $db->prepare("SELECT created_by FROM jurnal_teren WHERE id = ?");
                $own->execute([$jid]);
                $jr = $own->fetch();
                if (!$jr) jsonResponse(['success' => false, 'error' => 'Intrare negăsită'], 404);
                $isAdm = $jtUser && (($jtUser['role'] ?? '') === 'admin');
                if (!$isAdm && $jr['created_by'] !== $jtBy) {
                    jsonResponse(['success' => false, 'error' => 'Poți edita doar intrările proprii (sau ești admin)'], 403);
                }
                $db->prepare("UPDATE jurnal_teren SET data_start=?, data_end=?, tehnicieni=?, locatie=?, proiect_id=?, descriere=? WHERE id=?")
                   ->execute([$ds, $de, $teh, $loc, $jpid, $desc, $jid]);
                jsonResponse(['success' => true]);
            }
            break;

        case 'getProiecteProiectare':
            $stmt = $db->query("SELECT p.id, p.proiect_id, p.status, p.valoare_contract, p.responsabil, p.serviciu, c.nume AS client_nume, c.telefon, pr.status AS pr_status, pr.progres, pr.termen, pr.proiectant FROM proiecte p JOIN clienti c ON p.client_id = c.id LEFT JOIN proiectare pr ON p.id = pr.proiect_id WHERE p.status IN ('Contract','Proiectare') ORDER BY FIELD(p.status,'Proiectare','Contract'), p.updated_at DESC");
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        // ══════════════════════════════════════
        // DASHBOARD / STATS
        // ══════════════════════════════════════
        case 'getDashboard':
            $stats = [];
            // Total clienti
            $stats['totalClienti'] = $db->query("SELECT COUNT(*) FROM clienti")->fetchColumn();
            // Proiecte pe status
            $stmt = $db->query("SELECT status, COUNT(*) as cnt FROM proiecte GROUP BY status");
            $stats['proiecteStatus'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $stats['totalProiecte'] = array_sum($stats['proiecteStatus']);
            // Valoare pipeline
            $stats['valoarePipeline'] = $db->query("SELECT COALESCE(SUM(valoare_estimata),0) FROM proiecte WHERE status NOT IN ('Finalizat','Anulat')")->fetchColumn();
            // Oferte luna curenta
            $stats['oferteLuna'] = $db->query("SELECT COUNT(*) FROM oferte WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetchColumn();
            // Facturi restante
            $stats['facturiRestante'] = $db->query("SELECT COALESCE(SUM(suma),0) FROM facturi WHERE status = 'Restanta'")->fetchColumn();
            
            jsonResponse(['success' => true, 'data' => $stats]);
            break;

        // ══════════════════════════════════════
        // NECESAR MATERIALE — echipamente din ofertele acceptate
        // ale proiectelor în Proiectare / Execuție
        // Structură: Client → Ofertă → Linii materiale (cod, denumire, UM, cantitate)
        // ══════════════════════════════════════
        case 'getNecesarMateriale':
            // Asigură că tabela necesar_comenzi există (auto-create la prima rulare)
            $db->exec("CREATE TABLE IF NOT EXISTS necesar_comenzi (
                oferta_id INT PRIMARY KEY,
                comandat_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                comandat_by VARCHAR(60) NOT NULL DEFAULT ''
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $sql = "SELECT
                c.id AS client_db_id, c.client_id AS client_cod, c.nume AS client_nume,
                p.id AS proiect_db_id, p.proiect_id, p.obiectiv, p.adresa_obiectiv, p.status AS proiect_status,
                o.id AS oferta_db_id, o.oferta_id, o.titlu AS oferta_titlu, o.data_oferta, o.total_cu_tva,
                nc.comandat_at, nc.comandat_by,
                ol.id AS linie_id, ol.cod, ol.denumire, ol.um, ol.cantitate, ol.pret_vanzare, ol.valoare, ol.ordine
                FROM proiecte p
                INNER JOIN clienti c ON p.client_id = c.id
                INNER JOIN oferte o ON o.proiect_id = p.id AND o.status = 'Acceptata'
                INNER JOIN oferta_linii ol ON ol.oferta_id = o.id AND ol.tip = 'echipament'
                LEFT JOIN necesar_comenzi nc ON nc.oferta_id = o.id
                WHERE p.status IN ('Proiectare','Executie')
                ORDER BY c.nume, p.proiect_id, o.data_oferta DESC, ol.ordine, ol.id";
            $rows = $db->query($sql)->fetchAll();

            // Grupare ierarhică: Client → Ofertă → Linii
            $clienti = [];
            foreach ($rows as $r) {
                $cKey = $r['client_db_id'];
                if (!isset($clienti[$cKey])) {
                    $clienti[$cKey] = [
                        'client_id'    => $r['client_cod'],
                        'client_nume'  => $r['client_nume'],
                        'oferte'       => [],
                    ];
                }
                $oKey = $r['oferta_db_id'];
                if (!isset($clienti[$cKey]['oferte'][$oKey])) {
                    $clienti[$cKey]['oferte'][$oKey] = [
                        'oferta_db_id'   => intval($r['oferta_db_id']),
                        'oferta_id'      => $r['oferta_id'],
                        'titlu'          => $r['oferta_titlu'],
                        'data_oferta'    => $r['data_oferta'],
                        'total_cu_tva'   => floatval($r['total_cu_tva']),
                        'proiect_id'     => $r['proiect_id'],
                        'proiect_status' => $r['proiect_status'],
                        'obiectiv'       => $r['obiectiv'],
                        'adresa'         => $r['adresa_obiectiv'],
                        'comandat_at'    => $r['comandat_at'],
                        'comandat_by'    => $r['comandat_by'],
                        'linii'          => [],
                    ];
                }
                $clienti[$cKey]['oferte'][$oKey]['linii'][] = [
                    'cod'       => $r['cod'] ?: '',
                    'denumire'  => $r['denumire'] ?: '',
                    'um'        => $r['um'] ?: 'buc',
                    'cantitate' => floatval($r['cantitate']),
                ];
            }

            // Reindex (în loc de chei DB)
            $out = array_map(function($c){ $c['oferte'] = array_values($c['oferte']); return $c; }, array_values($clienti));
            jsonResponse(['success' => true, 'data' => $out]);
            break;

        // ══════════════════════════════════════
        // EXECUȚIE — programări + atribuiri tehnicieni (echipă)
        // Tabele auto-create la primul access
        // ══════════════════════════════════════
        case 'getExecutie':
            $db->exec("CREATE TABLE IF NOT EXISTS executie_programari (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                data_programata DATE NOT NULL,
                ora_start TIME DEFAULT '08:00:00',
                durata_ore DECIMAL(4,1) DEFAULT 8,
                status VARCHAR(20) DEFAULT 'Programat',
                obiectiv TEXT,
                note TEXT,
                created_by VARCHAR(60),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_data (data_programata),
                KEY idx_proiect (proiect_id),
                KEY idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $db->exec("CREATE TABLE IF NOT EXISTS executie_atribuiri (
                programare_id INT NOT NULL,
                user_id VARCHAR(60) NOT NULL,
                PRIMARY KEY (programare_id, user_id),
                KEY idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Filtrare opțională: ?user=X (programările unui tehnician), ?from=DATE, ?to=DATE
            // SECURITATE: tehnicienii pot vedea DOAR programările lor.
            // Admin/manageri pot cere oricare (?user=X) sau lista totală (fără filtru).
            $reqUserFilter = isset($_GET['user']) ? trim($_GET['user']) : '';
            $sessUser = currentUser();
            if (isAdmin() || (!isTehnician())) {
                $userFilter = $reqUserFilter; // poate fi gol = lista completă
            } else {
                $userFilter = $sessUser['username']; // tehnician → forțat la el
            }
            $fromFilter = isset($_GET['from']) ? trim($_GET['from']) : '';
            $toFilter   = isset($_GET['to']) ? trim($_GET['to']) : '';

            // Toate proiectele cu status Executie sau Receptie (în lucru / în finisare)
            $sqlP = "SELECT p.id AS proiect_db_id, p.proiect_id, p.serviciu, p.obiectiv, p.adresa_obiectiv,
                            p.status AS proiect_status, p.valoare_contract, p.responsabil,
                            c.id AS client_db_id, c.client_id AS client_cod, c.nume AS client_nume,
                            c.telefon AS client_telefon
                     FROM proiecte p
                     INNER JOIN clienti c ON p.client_id = c.id
                     WHERE p.status IN ('Executie','Receptie','Interventie')
                     ORDER BY c.nume, p.proiect_id";
            $proiecte = $db->query($sqlP)->fetchAll();

            // Programări (cu eventuale filtre)
            $sqlPg = "SELECT pg.* FROM executie_programari pg WHERE 1=1";
            $params = [];
            if ($userFilter) {
                $sqlPg = "SELECT pg.* FROM executie_programari pg
                          INNER JOIN executie_atribuiri at ON at.programare_id = pg.id
                          WHERE at.user_id = ?";
                $params[] = $userFilter;
            }
            if ($fromFilter) { $sqlPg .= " AND pg.data_programata >= ?"; $params[] = $fromFilter; }
            if ($toFilter)   { $sqlPg .= " AND pg.data_programata <= ?"; $params[] = $toFilter; }
            $sqlPg .= " ORDER BY pg.data_programata, pg.ora_start";
            $stmtPg = $db->prepare($sqlPg);
            $stmtPg->execute($params);
            $programari = $stmtPg->fetchAll();

            // Atribuiri pentru toate programările deodată
            $atribuiriMap = [];
            if (!empty($programari)) {
                $ids = array_column($programari, 'id');
                $place = implode(',', array_fill(0, count($ids), '?'));
                $stmtA = $db->prepare("SELECT programare_id, user_id FROM executie_atribuiri WHERE programare_id IN ($place)");
                $stmtA->execute($ids);
                foreach ($stmtA->fetchAll() as $a) {
                    $atribuiriMap[$a['programare_id']][] = $a['user_id'];
                }
            }
            // Atașează atribuirile la fiecare programare
            foreach ($programari as &$p) {
                $p['atribuiri'] = isset($atribuiriMap[$p['id']]) ? $atribuiriMap[$p['id']] : [];
            }
            unset($p);

            jsonResponse(['success' => true, 'data' => [
                'proiecte'   => $proiecte,
                'programari' => $programari,
            ]]);
            break;

        case 'saveProgramare':
            // INSERT sau UPDATE programare + sincronizare atribuiri
            $db->exec("CREATE TABLE IF NOT EXISTS executie_programari (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                data_programata DATE NOT NULL,
                ora_start TIME DEFAULT '08:00:00',
                durata_ore DECIMAL(4,1) DEFAULT 8,
                status VARCHAR(20) DEFAULT 'Programat',
                obiectiv TEXT,
                note TEXT,
                created_by VARCHAR(60),
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_data (data_programata),
                KEY idx_proiect (proiect_id),
                KEY idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $db->exec("CREATE TABLE IF NOT EXISTS executie_atribuiri (
                programare_id INT NOT NULL,
                user_id VARCHAR(60) NOT NULL,
                PRIMARY KEY (programare_id, user_id),
                KEY idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $id        = isset($data['id']) ? intval($data['id']) : 0;
            $proiectId = isset($data['proiect_id']) ? intval($data['proiect_id']) : 0;
            $dataPrg   = isset($data['data_programata']) ? trim($data['data_programata']) : '';
            $oraStart  = isset($data['ora_start']) ? trim($data['ora_start']) : '08:00:00';
            $durata    = isset($data['durata_ore']) ? floatval($data['durata_ore']) : 8;
            $status    = isset($data['status']) ? trim($data['status']) : 'Programat';
            $obiectiv  = isset($data['obiectiv']) ? trim($data['obiectiv']) : '';
            $note      = isset($data['note']) ? trim($data['note']) : '';
            // SECURITATE: created_by din sesiune (anti-spoofing)
            $sessUser = currentUser();
            $createdBy = $sessUser['username'];
            $atribuiri = isset($data['atribuiri']) && is_array($data['atribuiri']) ? $data['atribuiri'] : [];

            if (!$proiectId || !$dataPrg) {
                jsonResponse(['success' => false, 'error' => 'proiect_id + data_programata obligatorii'], 400); break;
            }

            $db->beginTransaction();
            try {
                if ($id > 0) {
                    $db->prepare("UPDATE executie_programari SET proiect_id=?, data_programata=?, ora_start=?, durata_ore=?, status=?, obiectiv=?, note=? WHERE id=?")
                       ->execute([$proiectId, $dataPrg, $oraStart, $durata, $status, $obiectiv, $note, $id]);
                } else {
                    $db->prepare("INSERT INTO executie_programari (proiect_id, data_programata, ora_start, durata_ore, status, obiectiv, note, created_by) VALUES (?,?,?,?,?,?,?,?)")
                       ->execute([$proiectId, $dataPrg, $oraStart, $durata, $status, $obiectiv, $note, $createdBy]);
                    $id = intval($db->lastInsertId());
                }
                // Resincronizare atribuiri (delete + insert)
                $db->prepare("DELETE FROM executie_atribuiri WHERE programare_id=?")->execute([$id]);
                if (!empty($atribuiri)) {
                    $stmtA = $db->prepare("INSERT INTO executie_atribuiri (programare_id, user_id) VALUES (?, ?)");
                    foreach ($atribuiri as $u) {
                        if (trim($u) !== '') $stmtA->execute([$id, trim($u)]);
                    }
                }
                $db->commit();
                jsonResponse(['success' => true, 'id' => $id]);
            } catch (Exception $e) {
                $db->rollBack();
                jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
            }
            break;

        case 'deleteProgramare':
            // SECURITATE: doar admin sau cel care a creat programarea
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) { jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400); break; }
            $stmt = $db->prepare("SELECT created_by FROM executie_programari WHERE id=?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) requireOwnerOrAdmin($row['created_by']);
            $db->prepare("DELETE FROM executie_atribuiri WHERE programare_id=?")->execute([$id]);
            $db->prepare("DELETE FROM executie_programari WHERE id=?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        // ══════════════════════════════════════
        // EXECUȚIE — pagină proiect (jurnal + fișiere)
        // ══════════════════════════════════════
        case 'getProiectExecutie':
            $db->exec("CREATE TABLE IF NOT EXISTS executie_jurnal (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                data_intrare DATE NOT NULL,
                user_id VARCHAR(60) NOT NULL,
                text TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_proiect (proiect_id),
                KEY idx_data (data_intrare)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $db->exec("CREATE TABLE IF NOT EXISTS executie_files (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                tip VARCHAR(20) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255),
                size_bytes INT,
                mime_type VARCHAR(100),
                uploaded_by VARCHAR(60),
                uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_proiect (proiect_id),
                KEY idx_tip (tip)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pid = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$pid) { jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400); break; }

            // Date proiect + client
            $stmtP = $db->prepare("SELECT p.id AS proiect_db_id, p.proiect_id, p.serviciu, p.obiectiv, p.adresa_obiectiv,
                                          p.status AS proiect_status, p.valoare_contract,
                                          c.id AS client_db_id, c.client_id AS client_cod, c.nume AS client_nume,
                                          c.telefon AS client_telefon, c.persoana_contact
                                   FROM proiecte p
                                   INNER JOIN clienti c ON p.client_id = c.id
                                   WHERE p.id = ?");
            $stmtP->execute([$pid]);
            $proiect = $stmtP->fetch();
            if (!$proiect) { jsonResponse(['success' => false, 'error' => 'Proiect inexistent'], 404); break; }

            // Programări
            $stmtPr = $db->prepare("SELECT * FROM executie_programari WHERE proiect_id = ? ORDER BY data_programata DESC, ora_start");
            $stmtPr->execute([$pid]);
            $programari = $stmtPr->fetchAll();
            $atribuiriMap = [];
            if (!empty($programari)) {
                $ids = array_column($programari, 'id');
                $place = implode(',', array_fill(0, count($ids), '?'));
                $stmtA = $db->prepare("SELECT programare_id, user_id FROM executie_atribuiri WHERE programare_id IN ($place)");
                $stmtA->execute($ids);
                foreach ($stmtA->fetchAll() as $a) { $atribuiriMap[$a['programare_id']][] = $a['user_id']; }
            }
            foreach ($programari as &$p) { $p['atribuiri'] = isset($atribuiriMap[$p['id']]) ? $atribuiriMap[$p['id']] : []; }
            unset($p);

            // Jurnal
            $stmtJ = $db->prepare("SELECT * FROM executie_jurnal WHERE proiect_id = ? ORDER BY created_at DESC");
            $stmtJ->execute([$pid]);
            $jurnal = $stmtJ->fetchAll();

            // Fișiere
            $stmtF = $db->prepare("SELECT * FROM executie_files WHERE proiect_id = ? ORDER BY uploaded_at DESC");
            $stmtF->execute([$pid]);
            $fisiere = $stmtF->fetchAll();

            // URL public pentru fișiere
            $upUrl = UPLOAD_URL . 'executie/' . $proiect['proiect_id'] . '/';
            foreach ($fisiere as &$f) { $f['url'] = $upUrl . $f['tip'] . '/' . $f['filename']; }
            unset($f);

            jsonResponse(['success' => true, 'data' => [
                'proiect'    => $proiect,
                'programari' => $programari,
                'jurnal'     => $jurnal,
                'fisiere'    => $fisiere,
            ]]);
            break;

        case 'addJurnalEntryExec':
            $db->exec("CREATE TABLE IF NOT EXISTS executie_jurnal (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                data_intrare DATE NOT NULL,
                user_id VARCHAR(60) NOT NULL,
                text TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_proiect (proiect_id),
                KEY idx_data (data_intrare)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            // SECURITATE: user_id forțat la cel din sesiune (anti-spoofing)
            $sessUser = currentUser();
            $pid = isset($data['proiect_id']) ? intval($data['proiect_id']) : 0;
            $usr = $sessUser['username'];
            $txt = isset($data['text']) ? trim($data['text']) : '';
            $dt  = isset($data['data']) && $data['data'] ? trim($data['data']) : date('Y-m-d');
            if (!$pid || !$txt) { jsonResponse(['success' => false, 'error' => 'proiect_id + text obligatorii'], 400); break; }
            $db->prepare("INSERT INTO executie_jurnal (proiect_id, data_intrare, user_id, text) VALUES (?,?,?,?)")
               ->execute([$pid, $dt, $usr, $txt]);
            jsonResponse(['success' => true, 'id' => $db->lastInsertId()]);
            break;

        case 'deleteJurnalEntryExec':
            // SECURITATE: doar autorul sau admin poate șterge
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) { jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400); break; }
            $stmt = $db->prepare("SELECT user_id FROM executie_jurnal WHERE id=?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) requireOwnerOrAdmin($row['user_id']);
            $db->prepare("DELETE FROM executie_jurnal WHERE id=?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        case 'uploadProiectFile':
            // Multipart upload: $_POST proiect_id, tip, user; $_FILES['file']
            $db->exec("CREATE TABLE IF NOT EXISTS executie_files (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                tip VARCHAR(20) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255),
                size_bytes INT,
                mime_type VARCHAR(100),
                uploaded_by VARCHAR(60),
                uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_proiect (proiect_id),
                KEY idx_tip (tip)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pid = isset($_POST['proiect_id']) ? intval($_POST['proiect_id']) : 0;
            $tip = isset($_POST['tip']) ? trim($_POST['tip']) : 'doc'; // pv | poza | doc
            $_sessUserUp = currentUser(); $usr = $_sessUserUp['username']; // forțat din sesiune
            if (!$pid || !isset($_FILES['file'])) { jsonResponse(['success' => false, 'error' => 'proiect_id + file obligatorii'], 400); break; }
            if (!in_array($tip, ['pv','poza','doc'])) $tip = 'doc';

            // Aflam codul proiectului (folder folosește codul, nu ID-ul)
            $stmt = $db->prepare("SELECT proiect_id FROM proiecte WHERE id = ?");
            $stmt->execute([$pid]);
            $r = $stmt->fetch();
            if (!$r) { jsonResponse(['success' => false, 'error' => 'Proiect inexistent'], 404); break; }
            $codProiect = $r['proiect_id'];

            $f = $_FILES['file'];
            if ($f['error'] !== UPLOAD_ERR_OK) { jsonResponse(['success' => false, 'error' => 'Upload eșuat (cod '.$f['error'].')'], 400); break; }
            $maxSize = 25 * 1024 * 1024; // 25 MB
            if ($f['size'] > $maxSize) { jsonResponse(['success' => false, 'error' => 'Fișier prea mare (max 25 MB)'], 400); break; }

            $allowed = [
                'pv'   => ['pdf','jpg','jpeg','png','heic','webp'],
                'poza' => ['jpg','jpeg','png','heic','webp','gif'],
                'doc'  => ['pdf','jpg','jpeg','png','heic','webp','doc','docx','xls','xlsx'],
            ];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed[$tip])) { jsonResponse(['success' => false, 'error' => 'Extensie nepermisă: '.$ext], 400); break; }

            $folder = UPLOAD_DIR . 'executie/' . $codProiect . '/' . $tip . '/';
            if (!is_dir($folder)) @mkdir($folder, 0755, true);
            $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($f['name'], PATHINFO_FILENAME));
            $newName = date('Ymd-His') . '_' . substr($safe, 0, 40) . '.' . $ext;
            $dest = $folder . $newName;
            if (!move_uploaded_file($f['tmp_name'], $dest)) { jsonResponse(['success' => false, 'error' => 'Salvare eșuată'], 500); break; }

            $db->prepare("INSERT INTO executie_files (proiect_id, tip, filename, original_name, size_bytes, mime_type, uploaded_by) VALUES (?,?,?,?,?,?,?)")
               ->execute([$pid, $tip, $newName, $f['name'], $f['size'], $f['type'], $usr]);
            $fid = $db->lastInsertId();
            jsonResponse(['success' => true, 'id' => $fid, 'url' => UPLOAD_URL.'executie/'.$codProiect.'/'.$tip.'/'.$newName]);
            break;

        // ══════════════════════════════════════
        // EXECUȚIE — progres montaj per material (cuantificare)
        // ══════════════════════════════════════
        case 'getProiectMateriale':
            $db->exec("CREATE TABLE IF NOT EXISTS executie_progres_material (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                linie_id INT NOT NULL,
                cantitate DECIMAL(10,2) NOT NULL DEFAULT 0,
                data_montaj DATE NOT NULL,
                user_id VARCHAR(60) NOT NULL,
                note TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_proiect (proiect_id),
                KEY idx_linie (linie_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pid = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$pid) { jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400); break; }

            // Liniile materialelor din ofertele acceptate ale proiectului
            $sql = "SELECT o.id AS oferta_db_id, o.oferta_id, o.titlu AS oferta_titlu, o.obiectiv,
                           ol.id AS linie_id, ol.cod, ol.denumire, ol.um, ol.cantitate AS cant_planificata, ol.ordine
                    FROM oferte o
                    INNER JOIN oferta_linii ol ON ol.oferta_id = o.id AND ol.tip = 'echipament'
                    WHERE o.proiect_id = ? AND o.status = 'Acceptata'
                    ORDER BY o.id, ol.ordine, ol.id";
            $stmt = $db->prepare($sql);
            $stmt->execute([$pid]);
            $linii = $stmt->fetchAll();

            // Sume montat per linie + ultima actualizare
            $sumeMap = []; $ultimMap = [];
            if (!empty($linii)) {
                $ids = array_column($linii, 'linie_id');
                $place = implode(',', array_fill(0, count($ids), '?'));
                $stmtS = $db->prepare("SELECT linie_id, SUM(cantitate) AS total, MAX(created_at) AS ultim_at FROM executie_progres_material WHERE proiect_id = ? AND linie_id IN ($place) GROUP BY linie_id");
                $stmtS->execute(array_merge([$pid], $ids));
                foreach ($stmtS->fetchAll() as $row) {
                    $sumeMap[$row['linie_id']] = floatval($row['total']);
                    $ultimMap[$row['linie_id']] = $row['ultim_at'];
                }
            }

            // Eventele individuale pentru istoric (toate, frontend filtrează la expand)
            $stmtE = $db->prepare("SELECT * FROM executie_progres_material WHERE proiect_id = ? ORDER BY created_at DESC");
            $stmtE->execute([$pid]);
            $evenimente = $stmtE->fetchAll();

            // Grupează liniile pe oferte
            $oferte = [];
            foreach ($linii as $l) {
                $oid = $l['oferta_db_id'];
                if (!isset($oferte[$oid])) {
                    $oferte[$oid] = [
                        'oferta_db_id' => $oid,
                        'oferta_id'    => $l['oferta_id'],
                        'titlu'        => $l['oferta_titlu'],
                        'obiectiv'     => $l['obiectiv'],
                        'linii'        => [],
                    ];
                }
                $lid = intval($l['linie_id']);
                $oferte[$oid]['linii'][] = [
                    'linie_id'         => $lid,
                    'cod'              => $l['cod'],
                    'denumire'         => $l['denumire'],
                    'um'               => $l['um'],
                    'cant_planificata' => floatval($l['cant_planificata']),
                    'cant_montata'     => isset($sumeMap[$lid]) ? $sumeMap[$lid] : 0,
                    'ultim_montaj'     => isset($ultimMap[$lid]) ? $ultimMap[$lid] : null,
                ];
            }

            jsonResponse(['success' => true, 'data' => [
                'oferte'      => array_values($oferte),
                'evenimente'  => $evenimente,
            ]]);
            break;

        case 'addProgresMaterial':
            $db->exec("CREATE TABLE IF NOT EXISTS executie_progres_material (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                linie_id INT NOT NULL,
                cantitate DECIMAL(10,2) NOT NULL DEFAULT 0,
                data_montaj DATE NOT NULL,
                user_id VARCHAR(60) NOT NULL,
                note TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_proiect (proiect_id),
                KEY idx_linie (linie_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            // SECURITATE: user_id forțat la cel din sesiune (anti-spoofing)
            $sessUser = currentUser();
            $pid  = isset($data['proiect_id']) ? intval($data['proiect_id']) : 0;
            $lid  = isset($data['linie_id']) ? intval($data['linie_id']) : 0;
            $cant = isset($data['cantitate']) ? floatval($data['cantitate']) : 0;
            $usr  = $sessUser['username'];
            $note = isset($data['note']) ? trim($data['note']) : '';
            $dt   = isset($data['data']) && $data['data'] ? trim($data['data']) : date('Y-m-d');
            if (!$pid || !$lid || $cant == 0) { jsonResponse(['success' => false, 'error' => 'proiect_id + linie_id + cantitate (≠0) obligatorii'], 400); break; }
            $db->prepare("INSERT INTO executie_progres_material (proiect_id, linie_id, cantitate, data_montaj, user_id, note) VALUES (?,?,?,?,?,?)")
               ->execute([$pid, $lid, $cant, $dt, $usr, $note]);
            jsonResponse(['success' => true, 'id' => $db->lastInsertId()]);
            break;

        case 'deleteProgresMaterial':
            // SECURITATE: doar autorul sau admin poate șterge
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) { jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400); break; }
            $stmt = $db->prepare("SELECT user_id FROM executie_progres_material WHERE id=?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) requireOwnerOrAdmin($row['user_id']);
            $db->prepare("DELETE FROM executie_progres_material WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        // ══════════════════════════════════════
        // PROIECTARE — Checklist conform L. 333/2003 + I7-2011 + P118/3-2015 + GDPR
        // ══════════════════════════════════════
        case 'getProiectareChecklist':
            $db->exec("CREATE TABLE IF NOT EXISTS proiectare_checklist (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                item_key VARCHAR(80) NOT NULL,
                category VARCHAR(20) NOT NULL,
                status VARCHAR(10) DEFAULT 'todo',
                note TEXT,
                checked_by VARCHAR(60),
                checked_at DATETIME,
                ordine INT DEFAULT 0,
                UNIQUE KEY unq_proiect_item (proiect_id, item_key),
                KEY idx_proiect (proiect_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $db->exec("CREATE TABLE IF NOT EXISTS proiectare_documente (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                tip_doc VARCHAR(40) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255),
                size_bytes INT,
                mime_type VARCHAR(100),
                uploaded_by VARCHAR(60),
                uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_proiect (proiect_id),
                KEY idx_tip (tip_doc)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pid = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$pid) { jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400); break; }

            // Date proiect + client
            $stmtP = $db->prepare("SELECT p.id AS proiect_db_id, p.proiect_id, p.serviciu, p.obiectiv, p.adresa_obiectiv,
                                          p.status AS proiect_status, p.valoare_contract, p.responsabil,
                                          c.id AS client_db_id, c.client_id AS client_cod, c.nume AS client_nume,
                                          c.telefon AS client_telefon, c.persoana_contact, c.cui_cnp, c.tip
                                   FROM proiecte p INNER JOIN clienti c ON p.client_id = c.id
                                   WHERE p.id = ?");
            $stmtP->execute([$pid]);
            $proiect = $stmtP->fetch();
            if (!$proiect) { jsonResponse(['success' => false, 'error' => 'Proiect inexistent'], 404); break; }

            // Template checklist (50 items grupate pe 11 categorii — E1..E8)
            $TEMPLATE = [
                // E1 — Pre-analiză
                ['key'=>'e1_tip_beneficiar',     'cat'=>'e1', 'label'=>'Tip beneficiar identificat (PF/PJ/instituție publică)'],
                ['key'=>'e1_categorie_obiectiv', 'cat'=>'e1', 'label'=>'Categorie obiectiv stabilită (L. 333/2003 anexa 1)'],
                ['key'=>'e1_risc_incendiu',      'cat'=>'e1', 'label'=>'Categorie risc incendiu identificată (P118/1)'],
                ['key'=>'e1_aviz_ipj_check',     'cat'=>'e1', 'label'=>'Verificat dacă necesită aviz IPJ'],
                ['key'=>'e1_aviz_isu_check',     'cat'=>'e1', 'label'=>'Verificat dacă necesită aviz ISU (HG 571/2016)'],
                // E2 — Vizita teren
                ['key'=>'e2_intalnire',          'cat'=>'e2', 'label'=>'Întâlnire la locație efectuată cu beneficiar'],
                ['key'=>'e2_releveu',            'cat'=>'e2', 'label'=>'Releveu construcție realizat (planuri/măsurători)'],
                ['key'=>'e2_foto',               'cat'=>'e2', 'label'=>'Foto inventar zone critice (intrări, tablou electric, trasee)'],
                ['key'=>'e2_acces',              'cat'=>'e2', 'label'=>'Puncte de acces identificate (uși, ferestre, lucarne)'],
                ['key'=>'e2_alimentare',         'cat'=>'e2', 'label'=>'Alimentare electrică verificată (tablou, capacitate, RCD)'],
                ['key'=>'e2_internet',           'cat'=>'e2', 'label'=>'Conexiune internet verificată (CCTV cloud, IP)'],
                ['key'=>'e2_zone_gdpr',          'cat'=>'e2', 'label'=>'Zone NO-FILMARE identificate (toalete, vestiare — GDPR)'],
                // E3 — Analize
                ['key'=>'e3_analiza_risc',       'cat'=>'e3', 'label'=>'Analiză de risc securitate (L. 333) semnată de evaluator atestat'],
                ['key'=>'e3_grad_securitate',    'cat'=>'e3', 'label'=>'Grad securitate stabilit (1-4 conform SR EN 50131-1)'],
                ['key'=>'e3_calcul_risc_inc',    'cat'=>'e3', 'label'=>'Calcul risc incendiu efectuat (P118/1)'],
                ['key'=>'e3_scenariu',           'cat'=>'e3', 'label'=>'Scenariu securitate la incendiu redactat (pentru ISU)'],
                // E4a — Alarmă efracție
                ['key'=>'e4s_plan',              'cat'=>'e4s','label'=>'Plan amplasare detectoare (PIR, magnetic, vibrații)'],
                ['key'=>'e4s_centrala',          'cat'=>'e4s','label'=>'Centrală selectată conform grad securitate (SR EN 50131)'],
                ['key'=>'e4s_autonomie',         'cat'=>'e4s','label'=>'Autonomie sursă: 12h/24h/48h conform grad'],
                ['key'=>'e4s_comunicator',       'cat'=>'e4s','label'=>'Comunicator IP/GSM dual-path (Grad 3+)'],
                ['key'=>'e4s_dispecerat',        'cat'=>'e4s','label'=>'Conexiune dispecerat licențiat IGPR confirmată'],
                // E4b — CCTV
                ['key'=>'e4c_plan',              'cat'=>'e4c','label'=>'Plan amplasare camere cu unghiuri câmp vizual'],
                ['key'=>'e4c_rezolutie',         'cat'=>'e4c','label'=>'Rezoluție per zonă (identificare/recunoaștere/observare)'],
                ['key'=>'e4c_retentie',          'cat'=>'e4c','label'=>'Stocare NVR ≥30 zile dimensionată'],
                ['key'=>'e4c_avertizoare',       'cat'=>'e4c','label'=>'Avertizoare GDPR planificate la intrări'],
                ['key'=>'e4c_registru',          'cat'=>'e4c','label'=>'Registru prelucrare date personale (GDPR L. 190/2018)'],
                // E4c — Detecție incendiu
                ['key'=>'e4f_plan',              'cat'=>'e4f','label'=>'Plan amplasare detectoare (P118/3 + raze acoperire)'],
                ['key'=>'e4f_centrala',          'cat'=>'e4f','label'=>'Centrală conform SR EN 54-2 (adresabilă/convențională)'],
                ['key'=>'e4f_butoane',           'cat'=>'e4f','label'=>'Butoane manuale alarmă (max 30m de fiecare ieșire)'],
                ['key'=>'e4f_sirene',            'cat'=>'e4f','label'=>'Sirene + flash în zone zgomot >85dB (SR EN 54-3/23)'],
                ['key'=>'e4f_cabluri',           'cat'=>'e4f','label'=>'Cabluri rezistente la foc PH30/PH90 (SR EN 50200)'],
                ['key'=>'e4f_integrare',         'cat'=>'e4f','label'=>'Integrare trape fum / uși RF / oprire ventilație'],
                // E4d — Instalații electrice (I7-2011)
                ['key'=>'e4e_putere',            'cat'=>'e4e','label'=>'Calcul putere instalată echilibrat pe faze (I7 cap. 4)'],
                ['key'=>'e4e_protectii',         'cat'=>'e4e','label'=>'Schemă tablou cu RCD 30mA pe prize/lumini'],
                ['key'=>'e4e_cabluri',           'cat'=>'e4e','label'=>'Dimensionare cabluri (curent + cădere tensiune <3-5%)'],
                ['key'=>'e4e_impamantare',       'cat'=>'e4e','label'=>'Schemă împământare TN-S/TT (priză <4Ω)'],
                ['key'=>'e4e_iluminat_sig',     'cat'=>'e4e','label'=>'Iluminat siguranță evacuare (autonomie 1h, SR EN 1838)'],
                ['key'=>'e4e_pram',              'cat'=>'e4e','label'=>'Verificare PRAM programată la PIF'],
                // E5 — Documentație
                ['key'=>'e5_memoriu',            'cat'=>'e5', 'label'=>'Memoriu tehnic general redactat'],
                ['key'=>'e5_plan_amplasare',     'cat'=>'e5', 'label'=>'Plan amplasare echipamente (CAD: dwg/pdf)'],
                ['key'=>'e5_schema_bloc',        'cat'=>'e5', 'label'=>'Schema bloc / funcțională'],
                ['key'=>'e5_schema_electrica',   'cat'=>'e5', 'label'=>'Schema electrică desfășurată'],
                ['key'=>'e5_lista_echipamente',  'cat'=>'e5', 'label'=>'Lista echipamente cu specificații + certificate CE/SR EN'],
                ['key'=>'e5_lista_cabluri',      'cat'=>'e5', 'label'=>'Lista cabluri (tip, lungime, traseu, rezistență la foc)'],
                ['key'=>'e5_caiet_sarcini',      'cat'=>'e5', 'label'=>'Caiet de sarcini (tehnologie execuție)'],
                // E6 — Verificare internă
                ['key'=>'e6_peer_review',        'cat'=>'e6', 'label'=>'Verificare peer review (alt proiectant decât autorul)'],
                ['key'=>'e6_calcule',            'cat'=>'e6', 'label'=>'Calcule reverificate (puteri, secțiuni, autonomii)'],
                ['key'=>'e6_planuri',            'cat'=>'e6', 'label'=>'Planuri verificate (acoperire 100%, fără zone moarte)'],
                ['key'=>'e6_buget',              'cat'=>'e6', 'label'=>'Buget verificat vs. ofertă semnată'],
                // E7 — Avize externe
                ['key'=>'e7_aviz_ipj',           'cat'=>'e7', 'label'=>'Aviz IPJ obținut sau confirmat că nu e necesar'],
                ['key'=>'e7_aviz_isu',           'cat'=>'e7', 'label'=>'Aviz ISU obținut sau confirmat că nu e necesar'],
                ['key'=>'e7_gdpr_notif',         'cat'=>'e7', 'label'=>'Notificare ANSPDCP / DPIA realizată (CCTV)'],
                ['key'=>'e7_acord_vecin',        'cat'=>'e7', 'label'=>'Acord vecinătate (camere ce filmează spațiu public)'],
                // E8 — Predare execuție
                ['key'=>'e8_sedinta',            'cat'=>'e8', 'label'=>'Ședință predare cu echipa execuție'],
                ['key'=>'e8_dosar',              'cat'=>'e8', 'label'=>'Dosar tehnic complet predat'],
                ['key'=>'e8_briefing',           'cat'=>'e8', 'label'=>'Briefing puncte critice cu echipa execuție'],
                ['key'=>'e8_necesar',            'cat'=>'e8', 'label'=>'Necesar materiale validat și exportat'],
                ['key'=>'e8_planificare',        'cat'=>'e8', 'label'=>'Programare execuție creată în Planificare'],
            ];

            // Insertăm itemii lipsă din template (idempotent)
            $stmtIns = $db->prepare("INSERT IGNORE INTO proiectare_checklist (proiect_id, item_key, category, ordine) VALUES (?,?,?,?)");
            $insertedAny = false;
            foreach ($TEMPLATE as $i => $it) {
                $stmtIns->execute([$pid, $it['key'], $it['cat'], $i]);
                if ($stmtIns->rowCount() > 0) $insertedAny = true;
            }

            // SMART DEFAULTS: la prima generare, auto-marcăm n/a categoriile
            // E4a/E4b/E4c/E4d care NU sunt relevante pentru tipul de serviciu
            if ($insertedAny) {
                $serviciu = strtolower($proiect['serviciu'] ?? '');
                $relevante = ['e1','e2','e3','e5','e6','e7','e8']; // mereu relevante
                if (strpos($serviciu, 'incendiu') !== false) $relevante[] = 'e4f';
                if (strpos($serviciu, 'alarm') !== false || strpos($serviciu, 'efrac') !== false) $relevante[] = 'e4s';
                if (strpos($serviciu, 'supraveg') !== false || strpos($serviciu, 'video') !== false || strpos($serviciu, 'cctv') !== false || strpos($serviciu, 'camer') !== false) $relevante[] = 'e4c';
                // E4d Instalații electrice — relevante pentru orice sistem (alimentare, cabluri)
                $relevante[] = 'e4e';
                if (strpos($serviciu, 'complex') !== false) {
                    $relevante = array_merge($relevante, ['e4s','e4c','e4f']);
                }
                $relevante = array_unique($relevante);

                $allCats = ['e4s','e4c','e4f']; // categoriile candidate la auto-na (E4d e mereu relevant)
                foreach ($allCats as $cat) {
                    if (!in_array($cat, $relevante)) {
                        $stmtNA = $db->prepare("UPDATE proiectare_checklist SET status='n/a', note=?, checked_by='Sistem (auto)', checked_at=NOW() WHERE proiect_id=? AND category=? AND status='todo'");
                        $stmtNA->execute(['Auto-marcat: serviciul "'.($proiect['serviciu']??'').'" nu necesită această categorie. Modifică manual dacă e nevoie.', $pid, $cat]);
                    }
                }
            }

            // Citim toate item-urile (cu valoarea actuală)
            $stmtL = $db->prepare("SELECT * FROM proiectare_checklist WHERE proiect_id = ? ORDER BY ordine");
            $stmtL->execute([$pid]);
            $rows = $stmtL->fetchAll();

            // Atașăm label-ul din template (sursa adevărului = template, nu DB)
            $byKey = [];
            foreach ($TEMPLATE as $i => $it) { $byKey[$it['key']] = ['label'=>$it['label'], 'ordine'=>$i]; }
            $items = [];
            foreach ($rows as $r) {
                $items[] = [
                    'id'         => intval($r['id']),
                    'item_key'   => $r['item_key'],
                    'category'   => $r['category'],
                    'label'      => isset($byKey[$r['item_key']]) ? $byKey[$r['item_key']]['label'] : $r['item_key'],
                    'status'     => $r['status'],
                    'note'       => $r['note'],
                    'checked_by' => $r['checked_by'],
                    'checked_at' => $r['checked_at'],
                    'ordine'     => intval($r['ordine']),
                ];
            }

            // Documente proiectare
            $stmtD = $db->prepare("SELECT * FROM proiectare_documente WHERE proiect_id = ? ORDER BY uploaded_at DESC");
            $stmtD->execute([$pid]);
            $docs = $stmtD->fetchAll();
            $upUrl = UPLOAD_URL . 'proiectare/' . $proiect['proiect_id'] . '/';
            foreach ($docs as &$d) { $d['url'] = $upUrl . $d['tip_doc'] . '/' . $d['filename']; }
            unset($d);

            // Stats
            $tot = count($items);
            $done = count(array_filter($items, function($i){return $i['status']==='done';}));
            $na   = count(array_filter($items, function($i){return $i['status']==='n/a';}));
            $todo = count(array_filter($items, function($i){return $i['status']==='todo';}));
            $progres = $tot ? round((($done + $na) / $tot) * 100) : 0;

            jsonResponse(['success' => true, 'data' => [
                'proiect'    => $proiect,
                'items'      => $items,
                'documente'  => $docs,
                'stats'      => ['total'=>$tot, 'done'=>$done, 'na'=>$na, 'todo'=>$todo, 'progres'=>$progres],
                'gata_predat'=> $todo === 0,
            ]]);
            break;

        case 'saveProiectareItem':
            $id     = isset($data['id']) ? intval($data['id']) : 0;
            $status = isset($data['status']) ? trim($data['status']) : 'todo';
            $note   = isset($data['note']) ? trim($data['note']) : '';
            $user   = isset($data['user']) ? trim($data['user']) : '';
            if (!$id) { jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400); break; }
            if (!in_array($status, ['todo','done','n/a'])) $status = 'todo';
            if ($status === 'todo') {
                $db->prepare("UPDATE proiectare_checklist SET status=?, note=?, checked_by=NULL, checked_at=NULL WHERE id=?")
                   ->execute([$status, $note, $id]);
            } else {
                $db->prepare("UPDATE proiectare_checklist SET status=?, note=?, checked_by=?, checked_at=NOW() WHERE id=?")
                   ->execute([$status, $note, $user, $id]);
            }
            jsonResponse(['success' => true]);
            break;

        case 'bulkSaveProiectareItems':
            // Marcare bulk: toate items dintr-o categorie cu status nou
            $pid = isset($data['proiect_id']) ? intval($data['proiect_id']) : 0;
            $cat = isset($data['category']) ? trim($data['category']) : '';
            $status = isset($data['status']) ? trim($data['status']) : 'done';
            $user = isset($data['user']) ? trim($data['user']) : '';
            $onlyTodo = !empty($data['only_todo']); // true = doar cele nebifate
            if (!$pid || !$cat) { jsonResponse(['success' => false, 'error' => 'proiect_id + category obligatorii'], 400); break; }
            if (!in_array($status, ['todo','done','n/a'])) $status = 'done';
            $sql = "UPDATE proiectare_checklist SET status=?, checked_by=?, checked_at=NOW() WHERE proiect_id=? AND category=?";
            $params = [$status, $user, $pid, $cat];
            if ($onlyTodo) { $sql .= " AND status='todo'"; }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            jsonResponse(['success' => true, 'affected' => $stmt->rowCount()]);
            break;

        case 'uploadProiectareDoc':
            $db->exec("CREATE TABLE IF NOT EXISTS proiectare_documente (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                tip_doc VARCHAR(40) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255),
                size_bytes INT,
                mime_type VARCHAR(100),
                uploaded_by VARCHAR(60),
                uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_proiect (proiect_id),
                KEY idx_tip (tip_doc)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $pid = isset($_POST['proiect_id']) ? intval($_POST['proiect_id']) : 0;
            $tip = isset($_POST['tip_doc']) ? trim($_POST['tip_doc']) : 'altele';
            $_sessUserUp = currentUser(); $usr = $_sessUserUp['username']; // forțat din sesiune
            if (!$pid || !isset($_FILES['file'])) { jsonResponse(['success' => false, 'error' => 'proiect_id + file obligatorii'], 400); break; }

            $stmt = $db->prepare("SELECT proiect_id FROM proiecte WHERE id = ?");
            $stmt->execute([$pid]);
            $r = $stmt->fetch();
            if (!$r) { jsonResponse(['success' => false, 'error' => 'Proiect inexistent'], 404); break; }
            $codProiect = $r['proiect_id'];

            $f = $_FILES['file'];
            if ($f['error'] !== UPLOAD_ERR_OK) { jsonResponse(['success' => false, 'error' => 'Upload eșuat (cod '.$f['error'].')'], 400); break; }
            if ($f['size'] > 50 * 1024 * 1024) { jsonResponse(['success' => false, 'error' => 'Fișier prea mare (max 50 MB)'], 400); break; }

            $allowed = ['pdf','jpg','jpeg','png','heic','webp','dwg','dxf','doc','docx','xls','xlsx','zip'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) { jsonResponse(['success' => false, 'error' => 'Extensie nepermisă: '.$ext], 400); break; }

            $tipSafe = preg_replace('/[^a-z0-9_-]/', '', strtolower($tip));
            if (!$tipSafe) $tipSafe = 'altele';
            $folder = UPLOAD_DIR . 'proiectare/' . $codProiect . '/' . $tipSafe . '/';
            if (!is_dir($folder)) @mkdir($folder, 0755, true);
            $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($f['name'], PATHINFO_FILENAME));
            $newName = date('Ymd-His') . '_' . substr($safe, 0, 40) . '.' . $ext;
            $dest = $folder . $newName;
            if (!move_uploaded_file($f['tmp_name'], $dest)) { jsonResponse(['success' => false, 'error' => 'Salvare eșuată'], 500); break; }

            $db->prepare("INSERT INTO proiectare_documente (proiect_id, tip_doc, filename, original_name, size_bytes, mime_type, uploaded_by) VALUES (?,?,?,?,?,?,?)")
               ->execute([$pid, $tipSafe, $newName, $f['name'], $f['size'], $f['type'], $usr]);
            jsonResponse(['success' => true, 'id' => $db->lastInsertId(), 'url' => UPLOAD_URL.'proiectare/'.$codProiect.'/'.$tipSafe.'/'.$newName]);
            break;

        case 'deleteProiectareDoc':
            // SECURITATE: doar autorul (uploaded_by) sau admin
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) { jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400); break; }
            $stmt = $db->prepare("SELECT d.*, p.proiect_id AS cod_proiect FROM proiectare_documente d INNER JOIN proiecte p ON p.id=d.proiect_id WHERE d.id=?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                requireOwnerOrAdmin($row['uploaded_by']);
                $path = UPLOAD_DIR . 'proiectare/' . $row['cod_proiect'] . '/' . $row['tip_doc'] . '/' . $row['filename'];
                if (file_exists($path)) @unlink($path);
                $db->prepare("DELETE FROM proiectare_documente WHERE id=?")->execute([$id]);
            }
            jsonResponse(['success' => true]);
            break;

        case 'getProiectareList':
            // Listează toate proiectele cu status "Proiectare" + progres checklist
            $db->exec("CREATE TABLE IF NOT EXISTS proiectare_checklist (
                id INT PRIMARY KEY AUTO_INCREMENT, proiect_id INT NOT NULL, item_key VARCHAR(80) NOT NULL,
                category VARCHAR(20) NOT NULL, status VARCHAR(10) DEFAULT 'todo', note TEXT,
                checked_by VARCHAR(60), checked_at DATETIME, ordine INT DEFAULT 0,
                UNIQUE KEY unq_proiect_item (proiect_id, item_key), KEY idx_proiect (proiect_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $db->exec("CREATE TABLE IF NOT EXISTS proiectare_documente (
                id INT PRIMARY KEY AUTO_INCREMENT, proiect_id INT NOT NULL, tip_doc VARCHAR(40) NOT NULL,
                filename VARCHAR(255) NOT NULL, original_name VARCHAR(255), size_bytes INT,
                mime_type VARCHAR(100), uploaded_by VARCHAR(60), uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_proiect (proiect_id), KEY idx_tip (tip_doc)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $sql = "SELECT p.id AS proiect_db_id, p.proiect_id, p.serviciu, p.obiectiv, p.adresa_obiectiv,
                           p.status AS proiect_status, p.responsabil, p.created_at,
                           c.id AS client_db_id, c.nume AS client_nume, c.telefon AS client_telefon,
                           (SELECT COUNT(*) FROM proiectare_checklist WHERE proiect_id = p.id) AS checklist_total,
                           (SELECT COUNT(*) FROM proiectare_checklist WHERE proiect_id = p.id AND status = 'done') AS checklist_done,
                           (SELECT COUNT(*) FROM proiectare_checklist WHERE proiect_id = p.id AND status = 'n/a') AS checklist_na,
                           (SELECT COUNT(*) FROM proiectare_documente WHERE proiect_id = p.id) AS docs_count
                    FROM proiecte p INNER JOIN clienti c ON p.client_id = c.id
                    WHERE p.status = 'Proiectare'
                    ORDER BY p.created_at DESC";
            $rows = $db->query($sql)->fetchAll();
            jsonResponse(['success' => true, 'data' => $rows]);
            break;

        case 'deleteProiectFile':
            // SECURITATE: doar uploaderul (uploaded_by) sau admin
            $id = isset($data['id']) ? intval($data['id']) : 0;
            if (!$id) { jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400); break; }
            $stmt = $db->prepare("SELECT f.*, p.proiect_id AS cod_proiect FROM executie_files f INNER JOIN proiecte p ON p.id=f.proiect_id WHERE f.id=?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row) {
                requireOwnerOrAdmin($row['uploaded_by']);
                $path = UPLOAD_DIR . 'executie/' . $row['cod_proiect'] . '/' . $row['tip'] . '/' . $row['filename'];
                if (file_exists($path)) @unlink($path);
                $db->prepare("DELETE FROM executie_files WHERE id=?")->execute([$id]);
            }
            jsonResponse(['success' => true]);
            break;

        // ══════════════════════════════════════
        // NECESAR — marcare ofertă comandată / anulare marcaj
        // ══════════════════════════════════════
        case 'markOfertaComandata':
            $ofId = isset($data['oferta_db_id']) ? intval($data['oferta_db_id']) : 0;
            $user = isset($data['user']) ? trim($data['user']) : '';
            if (!$ofId) { jsonResponse(['success' => false, 'error' => 'oferta_db_id obligatoriu'], 400); break; }
            $db->exec("CREATE TABLE IF NOT EXISTS necesar_comenzi (
                oferta_id INT PRIMARY KEY,
                comandat_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                comandat_by VARCHAR(60) NOT NULL DEFAULT ''
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $stmt = $db->prepare("REPLACE INTO necesar_comenzi (oferta_id, comandat_at, comandat_by) VALUES (?, NOW(), ?)");
            $stmt->execute([$ofId, $user]);
            $stmt = $db->prepare("SELECT comandat_at, comandat_by FROM necesar_comenzi WHERE oferta_id = ?");
            $stmt->execute([$ofId]);
            jsonResponse(['success' => true, 'data' => $stmt->fetch()]);
            break;

        case 'unmarkOfertaComandata':
            $ofId = isset($data['oferta_db_id']) ? intval($data['oferta_db_id']) : 0;
            if (!$ofId) { jsonResponse(['success' => false, 'error' => 'oferta_db_id obligatoriu'], 400); break; }
            $db->prepare("DELETE FROM necesar_comenzi WHERE oferta_id = ?")->execute([$ofId]);
            jsonResponse(['success' => true]);
            break;

        // ══════════════════════════════════════
        // DASHBOARD STATS — counts pentru Rezumat General + Board (1 query / tabel)
        // Folosit de bootDashboard în /admin pentru randare instant a KPI + count badges
        // ══════════════════════════════════════
        case 'getDashboardStats':
            ensureOferteColumns($db);
            autoExpireOferte($db);

            // ─── Counts pe proiecte ────────────────────────────────────
            $sqlP = "SELECT
                SUM(CASE WHEN status IN ('Lead','Oferta','Contract','Proiectare','Executie','Receptie','Interventie') THEN 1 ELSE 0 END) AS proiecte_active,
                COALESCE(SUM(CASE WHEN status NOT IN ('Anulat') THEN valoare_contract ELSE 0 END), 0) AS contracte_semnate,
                SUM(CASE WHEN status='Proiectare' THEN 1 ELSE 0 END) AS la_proiectare,
                SUM(CASE WHEN status IN ('Executie','Interventie') THEN 1 ELSE 0 END) AS in_executie,
                SUM(CASE WHEN status='Lead' THEN 1 ELSE 0 END) AS b_lead,
                SUM(CASE WHEN status='Oferta' THEN 1 ELSE 0 END) AS b_oferta,
                SUM(CASE WHEN status='Contract' THEN 1 ELSE 0 END) AS b_contract,
                SUM(CASE WHEN status='Proiectare' THEN 1 ELSE 0 END) AS b_proiectare,
                SUM(CASE WHEN status='Executie' THEN 1 ELSE 0 END) AS b_executie,
                SUM(CASE WHEN status='Interventie' THEN 1 ELSE 0 END) AS b_interventie,
                SUM(CASE WHEN status='Receptie' THEN 1 ELSE 0 END) AS b_receptie,
                SUM(CASE WHEN status='Facturat' THEN 1 ELSE 0 END) AS b_facturat,
                SUM(CASE WHEN status='Mentenanta' THEN 1 ELSE 0 END) AS b_mentenanta
                FROM proiecte";
            $rowP = $db->query($sqlP)->fetch() ?: [];

            // ─── Counts + valori pe oferte (status precis) ─────────────
            $monthStart = date('Y-m-01');
            $sqlO = "SELECT
                SUM(CASE WHEN status IN ('Trimisa','In_discutie') THEN 1 ELSE 0 END) AS oferte_in_asteptare_count,
                COALESCE(SUM(CASE WHEN status IN ('Trimisa','In_discutie') THEN total_cu_tva ELSE 0 END), 0) AS oferte_in_asteptare_valoare,
                SUM(CASE WHEN status='Acceptata' THEN 1 ELSE 0 END) AS acceptate_total_count,
                COALESCE(SUM(CASE WHEN status='Acceptata' THEN total_cu_tva ELSE 0 END), 0) AS acceptate_total_valoare,
                SUM(CASE WHEN status='Acceptata' AND data_decizie >= ? THEN 1 ELSE 0 END) AS acceptate_luna_count,
                COALESCE(SUM(CASE WHEN status='Acceptata' AND data_decizie >= ? THEN total_cu_tva ELSE 0 END), 0) AS acceptate_luna_valoare,
                SUM(CASE WHEN status='Refuzata' THEN 1 ELSE 0 END) AS refuzate_count,
                SUM(CASE WHEN status='Expirata' AND archived_at IS NULL THEN 1 ELSE 0 END) AS expirate_count,
                SUM(CASE WHEN status='Draft' THEN 1 ELSE 0 END) AS draft_count,
                COUNT(*) AS oferte_total
                FROM oferte WHERE archived_at IS NULL";
            $stmtO = $db->prepare($sqlO);
            $stmtO->execute([$monthStart, $monthStart]);
            $rowO = $stmtO->fetch() ?: [];

            // ─── Valoare totala activa (Σ valoare_calc pe proiecte ne-anulate) ─────
            // Folosim aceeasi logica ca in getProiecte (valoare_calc derivata)
            $sqlA = "SELECT
                COALESCE(SUM(CASE
                    WHEN p.valoare_contract > 0 THEN p.valoare_contract
                    ELSE COALESCE((SELECT SUM(o.total_cu_tva) FROM oferte o WHERE (o.proiect_id = p.id OR (o.client_id = p.client_id AND o.proiect_id IS NULL)) AND o.status='Acceptata' AND o.archived_at IS NULL), 0)
                END), 0) AS valoare_castigata_proiecte
                FROM proiecte p WHERE p.status NOT IN ('Anulat')";
            $rowA = $db->query($sqlA)->fetch() ?: [];

            // ─── Conversion rate ─────────────────────────────────────
            $accCount = intval($rowO['acceptate_total_count'] ?? 0);
            $refCount = intval($rowO['refuzate_count'] ?? 0);
            $expCount = intval($rowO['expirate_count'] ?? 0);
            $totalDecis = $accCount + $refCount + $expCount;
            $conversion = $totalDecis > 0 ? round(($accCount / $totalDecis) * 100) : 0;

            jsonResponse(['success' => true, 'data' => [
                'rezumat' => [
                    'proiecteActive'         => intval($rowP['proiecte_active'] ?? 0),
                    'valoareCastigata'       => floatval($rowA['valoare_castigata_proiecte'] ?? 0),
                    'oferteInAsteptare'      => intval($rowO['oferte_in_asteptare_count'] ?? 0),
                    'pipelineValoare'        => floatval($rowO['oferte_in_asteptare_valoare'] ?? 0),
                    'castigatLuna'           => floatval($rowO['acceptate_luna_valoare'] ?? 0),
                    'castigatLunaCount'      => intval($rowO['acceptate_luna_count'] ?? 0),
                    'conversionRate'         => $conversion,
                    'oferteAcceptateTotal'   => $accCount,
                    'expirate'               => $expCount,
                    'inExecutie'             => intval($rowP['in_executie'] ?? 0),
                    'laProiectare'           => intval($rowP['la_proiectare'] ?? 0),
                    'oferteTotal'            => intval($rowO['oferte_total'] ?? 0),
                    // Compatibilitate cu UI vechi (nu sparge dacă cineva folosește încă)
                    'pipelineOferte'         => floatval($rowO['oferte_in_asteptare_valoare'] ?? 0),
                    'contracteSemnate'       => floatval($rowP['contracte_semnate'] ?? 0),
                    'oferteTrimise'          => intval($rowO['oferte_in_asteptare_count'] ?? 0),
                    'oferteAcceptate'        => $accCount,
                ],
                'board' => [
                    'lead'        => intval($rowP['b_lead'] ?? 0),
                    'oferta'      => intval($rowP['b_oferta'] ?? 0),
                    'contract'    => intval($rowP['b_contract'] ?? 0),
                    'proiectare'  => intval($rowP['b_proiectare'] ?? 0),
                    'executie'    => intval($rowP['b_executie'] ?? 0),
                    'interventie' => intval($rowP['b_interventie'] ?? 0),
                    'receptie'    => intval($rowP['b_receptie'] ?? 0),
                    'facturat'    => intval($rowP['b_facturat'] ?? 0),
                    'mentenanta'  => intval($rowP['b_mentenanta'] ?? 0),
                ],
            ]]);
            break;

        // ══════════════════════════════════════
        // NEXT ID (util pt frontend)
        // ══════════════════════════════════════
        case 'nextOfertaId':
            $stmt = $db->query("SELECT valoare FROM secvente WHERE cheie = 'oferta_seq'");
            $val = $stmt->fetchColumn();
            jsonResponse(['success' => true, 'next' => intval($val) + 1]);
            break;

        // ══════════════════════════════════════
        // ACTIVITATE RECENTA — feed unificat
        // ══════════════════════════════════════
        case 'getActivitateRecenta':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 15;
            $activitate = [];

            // Oferte recente
            $stmt = $db->query("SELECT o.id, o.oferta_id, o.client_nume, o.total_cu_tva, o.obiectiv, o.status, o.created_at FROM oferte o ORDER BY o.created_at DESC LIMIT 20");
            foreach ($stmt->fetchAll() as $r) {
                $val = number_format($r['total_cu_tva'], 0, ',', '.');
                $activitate[] = [
                    'text' => '📋 Ofertă ' . $r['oferta_id'] . ' — ' . $r['client_nume'] . ($r['obiectiv'] ? ' (' . $r['obiectiv'] . ')' : '') . ', ' . $val . ' RON',
                    'color' => $r['status'] === 'Acceptata' ? 'var(--green)' : ($r['status'] === 'Refuzata' ? 'var(--red)' : 'var(--blue)'),
                    'time' => $r['created_at'],
                    'type' => 'oferta'
                ];
            }

            // Proiecte recente (create sau cu status schimbat)
            $stmt = $db->query("SELECT p.proiect_id, p.status, p.serviciu, p.obiectiv, p.updated_at, p.created_at, c.nume AS client_nume FROM proiecte p JOIN clienti c ON p.client_id = c.id ORDER BY p.updated_at DESC LIMIT 20");
            foreach ($stmt->fetchAll() as $r) {
                $isNew = (strtotime($r['updated_at']) - strtotime($r['created_at'])) < 60;
                if ($isNew) {
                    $txt = '🆕 Proiect nou: ' . $r['proiect_id'] . ' — ' . $r['client_nume'] . ' (' . $r['serviciu'] . ')';
                } else {
                    $txt = '🔄 ' . $r['proiect_id'] . ' — ' . $r['client_nume'] . ' → status: ' . $r['status'];
                }
                $statusColors = ['Lead'=>'var(--blue)','Oferta'=>'var(--purple)','Contract'=>'var(--teal)','Proiectare'=>'var(--orange)','Executie'=>'var(--green)','Receptie'=>'var(--green)','Facturat'=>'var(--red)','Mentenanta'=>'var(--teal)','Finalizat'=>'var(--green)','Anulat'=>'var(--gray)'];
                $activitate[] = [
                    'text' => $txt,
                    'color' => isset($statusColors[$r['status']]) ? $statusColors[$r['status']] : 'var(--gray)',
                    'time' => $r['updated_at'],
                    'type' => 'proiect'
                ];
            }

            // Clienti noi
            $stmt = $db->query("SELECT c.client_id, c.nume, c.tip, c.oras, c.created_at FROM clienti c ORDER BY c.created_at DESC LIMIT 10");
            foreach ($stmt->fetchAll() as $r) {
                $activitate[] = [
                    'text' => '👤 Client nou: ' . $r['nume'] . ($r['oras'] ? ' — ' . $r['oras'] : '') . ' (' . $r['tip'] . ')',
                    'color' => 'var(--teal)',
                    'time' => $r['created_at'],
                    'type' => 'client'
                ];
            }

            // Sortare descrescator dupa timp
            usort($activitate, function($a, $b) { return strtotime($b['time']) - strtotime($a['time']); });
            $activitate = array_slice($activitate, 0, $limit);

            // Formatare timp relativ
            $now = time();
            foreach ($activitate as &$a) {
                $diff = $now - strtotime($a['time']);
                if ($diff < 60) $a['timeLabel'] = 'Acum';
                elseif ($diff < 3600) $a['timeLabel'] = floor($diff/60) . ' min';
                elseif ($diff < 86400) $a['timeLabel'] = floor($diff/3600) . 'h';
                elseif ($diff < 172800) $a['timeLabel'] = 'Ieri';
                elseif ($diff < 604800) $a['timeLabel'] = floor($diff/86400) . ' zile';
                else $a['timeLabel'] = date('d.m.Y', strtotime($a['time']));
            }
            unset($a);

            jsonResponse(['success' => true, 'data' => $activitate]);
            break;

        // ══════════════════════════════════════
        // NOTIFICĂRI
        // ══════════════════════════════════════
        case 'getNotificari':
            // SECURITATE: ignor parametrul URL ?user= și folosesc sesiunea
            // (admin poate cere notificările altcuiva, restul doar pe ale lor)
            $sessUser = currentUser();
            $reqUser = strtolower($_GET['user'] ?? $sessUser['username']);
            $user = isAdmin() ? $reqUser : strtolower($sessUser['username']);
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
            $cititCol = '';
            if (in_array($user, ['mihai','roxana','valentin','cristina'])) {
                $cititCol = 'citit_' . $user;
            }
            
            $stmt = $db->prepare("SELECT n.*, p.proiect_id AS cod_proiect, p.status AS status_proiect, p.preluat_de 
                FROM notificari n 
                LEFT JOIN proiecte p ON n.proiect_id = p.id 
                ORDER BY n.created_at DESC LIMIT ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $notifs = $stmt->fetchAll();
            
            $unread = 0;
            foreach ($notifs as &$n) {
                $n['citit'] = ($cititCol && isset($n[$cititCol])) ? (bool)$n[$cititCol] : false;
                if (!$n['citit']) $unread++;
            }
            unset($n);
            
            jsonResponse(['success' => true, 'data' => $notifs, 'unread' => $unread]);
            break;

        case 'markNotificareRead':
            // SECURITATE: user-ul e din sesiune (un user nu poate marca notificările altuia)
            $sessUser = currentUser();
            $nid = isset($data['id']) ? $data['id'] : '';
            $user = strtolower($sessUser['username']);
            if (!$nid || !in_array($user, ['mihai','roxana','valentin','cristina'])) {
                jsonResponse(['success' => false, 'error' => 'Parametri lipsă sau user fără coloană notificări'], 400); break;
            }
            $col = 'citit_' . $user;
            $db->prepare("UPDATE notificari SET $col = 1 WHERE id = ?")->execute([$nid]);
            jsonResponse(['success' => true]);
            break;

        case 'markAllRead':
            // SECURITATE: din sesiune
            $sessUser = currentUser();
            $user = strtolower($sessUser['username']);
            if (!in_array($user, ['mihai','roxana','valentin','cristina'])) {
                jsonResponse(['success' => false, 'error' => 'User fără coloană notificări'], 400); break;
            }
            $col = 'citit_' . $user;
            $db->exec("UPDATE notificari SET $col = 1 WHERE $col = 0");
            jsonResponse(['success' => true]);
            break;

        case 'preiaProiect':
            // SECURITATE: user-ul e DIN SESIUNE — nu poate cineva să atribuie altcuiva
            $sessUser = currentUser();
            $pid = isset($data['proiect_id']) ? $data['proiect_id'] : '';
            $user = $sessUser['display_name'] ?: $sessUser['username'];
            if (!$pid) { jsonResponse(['success' => false, 'error' => 'proiect_id obligatoriu'], 400); break; }
            
            $db->prepare("UPDATE proiecte SET preluat_de = ?, preluat_la = NOW() WHERE id = ? OR proiect_id = ?")
               ->execute([$user, $pid, $pid]);
            
            // Notificare
            $stmt = $db->prepare("SELECT p.proiect_id, p.status, c.nume FROM proiecte p JOIN clienti c ON p.client_id = c.id WHERE p.id = ? OR p.proiect_id = ?");
            $stmt->execute([$pid, $pid]);
            $p = $stmt->fetch();
            if ($p) {
                $mesaj = '✅ ' . $user . ' a preluat proiectul ' . $p['proiect_id'] . ' (' . $p['nume'] . ') — etapa: ' . $p['status'];
                $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la, etapa_noua, preluat_de, preluat_la) VALUES (?,?,?,?,?,?,NOW())")
                   ->execute([$p['proiect_id'], $mesaj, 'assignment', $user, $p['status'], $user]);
            }
            jsonResponse(['success' => true]);
            break;

        case 'getProiecteNeprelate':
            $stmt = $db->query("SELECT p.id, p.proiect_id, p.status, p.preluat_de, p.updated_at, c.nume AS client_nume 
                FROM proiecte p JOIN clienti c ON p.client_id = c.id 
                WHERE p.preluat_de IS NULL AND p.status NOT IN ('Finalizat','Anulat','Lead') 
                ORDER BY p.updated_at DESC");
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        // ══════════════════════════════════════
        // HEALTH CHECK
        // ══════════════════════════════════════
        // ══════════════════════════════════════
        // CONFIGURARE UTILIZATORI (din MySQL)
        // ══════════════════════════════════════
        case 'getUserConfig':
            $uid = isset($_GET['user_id']) ? $_GET['user_id'] : '';
            if ($uid) {
                $stmt = $db->prepare("SELECT * FROM user_config WHERE user_id = ?");
                $stmt->execute([$uid]);
                $row = $stmt->fetch();
                if ($row) { $row['modules'] = json_decode($row['modules'] ?: '[]'); $row['stages'] = json_decode($row['stages'] ?: '[]'); $row['primary_stages'] = json_decode($row['primary_stages'] ?: '[]'); }
                jsonResponse(['success' => true, 'data' => $row ?: null]);
            } else {
                $stmt = $db->query("SELECT * FROM user_config ORDER BY FIELD(user_id,'admin','mihai','roxana','valentin','cristina')");
                $rows = $stmt->fetchAll();
                foreach ($rows as &$r) { $r['modules'] = json_decode($r['modules'] ?: '[]'); $r['stages'] = json_decode($r['stages'] ?: '[]'); $r['primary_stages'] = json_decode($r['primary_stages'] ?: '[]'); }
                unset($r);
                jsonResponse(['success' => true, 'data' => $rows]);
            }
            break;

        case 'saveUserConfig':
            $uid = isset($data['user_id']) ? $data['user_id'] : '';
            if (!$uid) { jsonResponse(['success' => false, 'error' => 'user_id obligatoriu'], 400); break; }
            $modules = isset($data['modules']) ? json_encode($data['modules']) : '[]';
            $stages = isset($data['stages']) ? json_encode($data['stages']) : '[]';
            $primary = isset($data['primary_stages']) ? json_encode($data['primary_stages']) : '[]';
            $focus = isset($data['focus']) ? $data['focus'] : '';
            $stmt = $db->prepare("INSERT INTO user_config (user_id, modules, stages, primary_stages, focus) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE modules=VALUES(modules), stages=VALUES(stages), primary_stages=VALUES(primary_stages), focus=VALUES(focus)");
            $stmt->execute([$uid, $modules, $stages, $primary, $focus]);
            jsonResponse(['success' => true]);
            break;

        // ══════════════════════════════════════
        // SOCIAL MEDIA
        // ══════════════════════════════════════
        case 'getSocialPosts':
            $brand = isset($_GET['brand']) ? $_GET['brand'] : '';
            $status = isset($_GET['status']) ? $_GET['status'] : '';
            $sql = "SELECT * FROM social_posts WHERE 1=1";
            $params = [];
            if ($brand) { $sql .= " AND brand = ?"; $params[] = $brand; }
            if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
            $sql .= " ORDER BY data_programare DESC, created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            foreach ($rows as &$r) {
                $r['platforme'] = json_decode($r['platforme'] ?: '[]');
                $r['external_ids'] = json_decode($r['external_ids'] ?: '{}');
                $r['analytics'] = json_decode($r['analytics'] ?: '{}');
            }
            unset($r);
            jsonResponse(['success' => true, 'data' => $rows]);
            break;

        case 'generateSocialImage':
            $prompt = isset($data['prompt']) ? trim($data['prompt']) : '';
            if (!$prompt) { jsonResponse(['success' => false, 'error' => 'Descrie imaginea dorită'], 400); break; }
            $uploadDir = __DIR__ . '/uploads/social/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $imgPrompt = urlencode($prompt . ', professional photography, high quality, social media post');
            $imgUrl = "https://image.pollinations.ai/prompt/{$imgPrompt}?width=1080&height=1080&seed=" . rand(1,999999) . '&nologo=true';
            
            $ch = curl_init($imgUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 60
            ]);
            $imgData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300 && strlen($imgData) > 1000) {
                $ext = (strpos($contentType, 'png') !== false) ? 'png' : 'jpg';
                $newName = 'ai_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
                file_put_contents($uploadDir . $newName, $imgData);
                jsonResponse(['success' => true, 'file' => [
                    'url' => '/admin/uploads/social/' . $newName,
                    'name' => 'AI Generated - ' . substr($prompt, 0, 30),
                    'size' => strlen($imgData),
                    'type' => 'image',
                    'ext' => $ext
                ]]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Nu s-a putut genera imaginea'], 500);
            }
            break;

        case 'uploadSocialMedia':
            // Upload fișiere media pentru social posts
            if (empty($_FILES['files'])) { jsonResponse(['success' => false, 'error' => 'Niciun fișier trimis'], 400); break; }
            $uploadDir = __DIR__ . '/uploads/social/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $allowed = ['jpg','jpeg','png','gif','webp','mp4','mov','avi','mkv'];
            $maxSize = 50 * 1024 * 1024; // 50MB
            $uploaded = [];
            $files = $_FILES['files'];
            $count = is_array($files['name']) ? count($files['name']) : 1;
            for ($i = 0; $i < $count; $i++) {
                $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                $tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $size = is_array($files['size']) ? $files['size'][$i] : $files['size'];
                $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];
                if ($error !== UPLOAD_ERR_OK) { continue; }
                if ($size > $maxSize) { continue; }
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) { continue; }
                $isVideo = in_array($ext, ['mp4','mov','avi','mkv']);
                $newName = date('Ymd_His') . '_' . uniqid() . '.' . $ext;
                $dest = $uploadDir . $newName;
                if (move_uploaded_file($tmp, $dest)) {
                    $uploaded[] = [
                        'url' => '/admin/uploads/social/' . $newName,
                        'name' => $name,
                        'size' => $size,
                        'type' => $isVideo ? 'video' : 'image',
                        'ext' => $ext
                    ];
                }
            }
            if (empty($uploaded)) { jsonResponse(['success' => false, 'error' => 'Niciun fișier valid uploadat'], 400); break; }
            jsonResponse(['success' => true, 'files' => $uploaded]);
            break;

        case 'saveSocialPost':
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            $brand = isset($data['brand']) ? $data['brand'] : 'cssi';
            $continut = isset($data['continut']) ? trim($data['continut']) : '';
            $platforme = isset($data['platforme']) ? json_encode($data['platforme']) : '[]';
            $tip = isset($data['tip_continut']) ? $data['tip_continut'] : 'Foto';
            $status = isset($data['status']) ? $data['status'] : 'Draft';
            $data_prog = !empty($data['data_programare']) ? $data['data_programare'] : null;
            $imagine = isset($data['imagine_url']) ? $data['imagine_url'] : '';
            $note = isset($data['note']) ? $data['note'] : '';
            $creat_de = isset($data['creat_de']) ? $data['creat_de'] : '';
            $media_json = isset($data['media_json']) ? json_encode($data['media_json']) : null;
            if (!$continut) { jsonResponse(['success' => false, 'error' => 'Conținutul e obligatoriu'], 400); break; }
            if ($id) {
                $stmt = $db->prepare("UPDATE social_posts SET brand=?, continut=?, platforme=?, tip_continut=?, status=?, data_programare=?, imagine_url=?, media_json=?, note=? WHERE id=?");
                $stmt->execute([$brand, $continut, $platforme, $tip, $status, $data_prog, $imagine, $media_json, $note, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO social_posts (brand, continut, platforme, tip_continut, status, data_programare, imagine_url, media_json, note, creat_de) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$brand, $continut, $platforme, $tip, $status, $data_prog, $imagine, $media_json, $note, $creat_de]);
                $id = $db->lastInsertId();
            }
            jsonResponse(['success' => true, 'id' => $id]);
            break;

        case 'deleteSocialPost':
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            if (!$id) { jsonResponse(['success' => false, 'error' => 'ID obligatoriu'], 400); break; }
            $db->prepare("DELETE FROM social_posts WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        case 'updateSocialStatus':
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            $status = isset($data['status']) ? $data['status'] : '';
            if (!$id || !$status) { jsonResponse(['success' => false, 'error' => 'ID și status obligatorii'], 400); break; }
            $db->prepare("UPDATE social_posts SET status = ? WHERE id = ?")->execute([$status, $id]);
            jsonResponse(['success' => true]);
            break;

        case 'publishSocialPost':
            // Publică via Zernio API
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            if (!$id) { jsonResponse(['success' => false, 'error' => 'ID obligatoriu'], 400); break; }
            $stmt = $db->prepare("SELECT * FROM social_posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch();
            if (!$post) { jsonResponse(['success' => false, 'error' => 'Post negăsit'], 404); break; }

            $platforme = json_decode($post['platforme'] ?: '[]', true);
            $zernioKey = defined('ZERNIO_KEY') ? ZERNIO_KEY : '';
            if (!$zernioKey) { jsonResponse(['success' => false, 'error' => 'ZERNIO_KEY nesetat în secrets.php'], 500); break; }

            // Fetch connected accounts from Zernio
            $chAccounts = curl_init('https://zernio.com/api/v1/accounts');
            curl_setopt_array($chAccounts, [
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $zernioKey],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15
            ]);
            $accountsResp = json_decode(curl_exec($chAccounts), true);
            curl_close($chAccounts);
            $accountMap = [];
            if (!empty($accountsResp['accounts'])) {
                foreach ($accountsResp['accounts'] as $acc) {
                    if ($acc['isActive']) $accountMap[$acc['platform']] = $acc['_id'];
                }
            }

            $platMap = ['fb'=>'facebook','ig'=>'instagram','linkedin'=>'linkedin','yt'=>'youtube','tiktok'=>'tiktok','x'=>'twitter'];
            $zernioPlats = [];
            foreach ($platforme as $p) {
                $platName = isset($platMap[$p]) ? $platMap[$p] : $p;
                if (isset($accountMap[$platName])) {
                    $zernioPlats[] = ['platform' => $platName, 'accountId' => $accountMap[$platName]];
                }
            }
            if (empty($zernioPlats)) {
                jsonResponse(['success' => false, 'error' => 'Niciun cont Zernio conectat pentru platformele selectate. Conectate: ' . implode(', ', array_keys($accountMap))], 400);
                break;
            }

            $zernioPayload = [
                'content' => $post['continut'],
                'platforms' => $zernioPlats
            ];
            // Media as mediaItems [{type, url}]
            if (!empty($post['media_json'])) {
                $mediaFiles = json_decode($post['media_json'], true);
                if (is_array($mediaFiles) && count($mediaFiles)) {
                    $baseUrl = 'https://cssi.ro';
                    $zernioPayload['mediaItems'] = array_map(function($f) use ($baseUrl) {
                        return ['type' => $f['type'] ?: 'image', 'url' => $baseUrl . $f['url']];
                    }, $mediaFiles);
                }
            } elseif (!empty($post['imagine_url'])) {
                $imgUrl = $post['imagine_url'];
                if (strpos($imgUrl, 'http') !== 0) $imgUrl = 'https://cssi.ro' . $imgUrl;
                $zernioPayload['mediaItems'] = [['type' => 'image', 'url' => $imgUrl]];
            }
            // Schedule or publish now
            if ($post['data_programare'] && strtotime($post['data_programare']) > time()) {
                $zernioPayload['scheduledFor'] = date('c', strtotime($post['data_programare']));
                $zernioPayload['timezone'] = 'Europe/Bucharest';
            } else {
                $zernioPayload['publishNow'] = true;
            }

            $ch = curl_init('https://zernio.com/api/v1/posts');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $zernioKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode($zernioPayload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);
            if ($httpCode >= 200 && $httpCode < 300) {
                $newStatus = (!empty($zernioPayload['scheduledFor'])) ? 'Programat' : 'Publicat';
                $db->prepare("UPDATE social_posts SET status = ?, external_ids = ? WHERE id = ?")
                   ->execute([$newStatus, $response, $id]);
                jsonResponse(['success' => true, 'status' => $newStatus, 'zernio' => $result]);
            } else {
                $db->prepare("UPDATE social_posts SET status = 'Eroare' WHERE id = ?")->execute([$id]);
                jsonResponse(['success' => false, 'error' => 'Zernio error: ' . ($response ?: 'timeout'), 'httpCode' => $httpCode], 500);
            }
            break;

        // ═══════ SMS CERERE RECENZIE GOOGLE ═══════
        case 'sendReviewSMS':
            requireAuth();
            $proiectId = isset($data['proiect_id']) ? $data['proiect_id'] : '';
            if (!$proiectId) { jsonResponse(['success' => false, 'error' => 'proiect_id obligatoriu'], 400); break; }

            // Creare tabel sms_recenzii daca nu exista
            $db->exec("CREATE TABLE IF NOT EXISTS sms_recenzii (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                client_id INT NOT NULL,
                telefon VARCHAR(20) NOT NULL,
                mesaj TEXT NOT NULL,
                status VARCHAR(20) DEFAULT 'trimis',
                sms_provider_id VARCHAR(100) DEFAULT NULL,
                trimis_de VARCHAR(60) DEFAULT 'Admin',
                trimis_la DATETIME DEFAULT CURRENT_TIMESTAMP,
                KEY idx_proiect (proiect_id),
                KEY idx_client (client_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Citeste proiect + client
            $stmt = $db->prepare("
                SELECT p.id, p.proiect_id, p.serviciu, p.status,
                       c.id AS cid, c.nume, c.telefon, c.email
                FROM proiecte p
                JOIN clienti c ON p.client_id = c.id
                WHERE p.id = ? OR p.proiect_id = ?
            ");
            $stmt->execute([$proiectId, $proiectId]);
            $row = $stmt->fetch();
            if (!$row) { jsonResponse(['success' => false, 'error' => 'Proiect negăsit'], 404); break; }
            if (!$row['telefon']) { jsonResponse(['success' => false, 'error' => 'Clientul nu are număr de telefon salvat'], 400); break; }

            // Verifică dacă s-a trimis deja
            $chk = $db->prepare("SELECT id, trimis_la FROM sms_recenzii WHERE proiect_id = ?");
            $chk->execute([$row['id']]);
            $existing = $chk->fetch();
            if ($existing) {
                jsonResponse(['success' => false, 'error' => 'SMS-ul a fost deja trimis pe ' . $existing['trimis_la'], 'already_sent' => true], 409);
                break;
            }

            // Normalizare telefon
            $phone = preg_replace('/[\s\-\.\(\)]/', '', $row['telefon']);
            if (strpos($phone, '0') === 0) {
                $phone = '+4' . $phone;
            } elseif (strpos($phone, '4') === 0 && strpos($phone, '+') !== 0) {
                $phone = '+' . $phone;
            }

            // Generează token unic pentru tracking click
            $token = bin2hex(random_bytes(8)); // 16 caractere hex

            // Construiește mesajul SMS cu link tracked
            $prenume = explode(' ', trim($row['nume']))[0];
            $serviciu = $row['serviciu'] ?: 'lucrarea';
            $reviewUrl = 'https://cssi.ro/r/' . $token;
            $mesaj = 'Buna ziua, ' . $prenume . '! Multumim ca ati ales CSSI pentru ' . mb_strtolower($serviciu) . '. Ne-ar bucura o recenzie pe Google: ' . $reviewUrl . ' Echipa CSSI';

            // Trimite SMS via SMSLink API (sau simulare dacă nu e configurat)
            $smsKey = defined('SMSLINK_KEY') ? SMSLINK_KEY : (getenv('CSSI_SMSLINK_KEY') ?: '');
            $smsSenderId = defined('SMSLINK_SENDER') ? SMSLINK_SENDER : (getenv('CSSI_SMSLINK_SENDER') ?: 'CSSI');
            $smsProviderId = null;
            $smsStatus = 'simulat';

            if ($smsKey) {
                // SMSLink.ro API v2
                $smsPayload = [
                    'to' => $phone,
                    'message' => $mesaj,
                    'sender_id' => $smsSenderId
                ];
                $ch = curl_init('https://www.smslink.ro/sms/gateway/communicate/json.php');
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Bearer ' . $smsKey,
                        'Content-Type: application/json'
                    ],
                    CURLOPT_POSTFIELDS => json_encode($smsPayload),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 15
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $result = json_decode($response, true);
                if ($httpCode >= 200 && $httpCode < 300 && isset($result['message_id'])) {
                    $smsProviderId = $result['message_id'];
                    $smsStatus = 'trimis';
                } else {
                    $smsStatus = 'eroare';
                    // Salvăm oricum în DB cu status eroare, dar trimitem eroarea la client
                    $db->prepare("INSERT INTO sms_recenzii (proiect_id, client_id, telefon, mesaj, status, sms_provider_id, trimis_de) VALUES (?,?,?,?,?,?,?)")
                       ->execute([$row['id'], $row['cid'], $phone, $mesaj, 'eroare', $response, $data['user'] ?? 'Admin']);
                    jsonResponse(['success' => false, 'error' => 'Eroare trimitere SMS. Verificați cheia API SMSLink.', 'details' => $response], 500);
                    break;
                }
            }

            // Salvează în DB
            $user = $data['user'] ?? 'Admin';
            $db->prepare("INSERT INTO sms_recenzii (proiect_id, client_id, telefon, mesaj, status, sms_provider_id, trimis_de) VALUES (?,?,?,?,?,?,?)")
               ->execute([$row['id'], $row['cid'], $phone, $mesaj, $smsStatus, $smsProviderId, $user]);
            $smsId = $db->lastInsertId();

            // Creare tabel sms_clicks + intrare tracking
            $db->exec("CREATE TABLE IF NOT EXISTS sms_clicks (
                id INT PRIMARY KEY AUTO_INCREMENT,
                sms_id INT NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                clicked_at DATETIME DEFAULT NULL,
                click_count INT DEFAULT 0,
                ip VARCHAR(45) DEFAULT NULL,
                user_agent TEXT DEFAULT NULL,
                KEY idx_token (token),
                KEY idx_sms (sms_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $db->prepare("INSERT INTO sms_clicks (sms_id, token) VALUES (?, ?)")
               ->execute([$smsId, $token]);

            // Adaugă notificare
            $notifMsg = '📱 SMS recenzie trimis către ' . $row['nume'] . ' (' . $phone . ') — ' . $row['proiect_id'];
            $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la) VALUES (?,?,?,?)")
               ->execute([$row['id'], $notifMsg, 'sms_recenzie', $user]);

            jsonResponse([
                'success' => true,
                'status' => $smsStatus,
                'telefon' => $phone,
                'mesaj' => $mesaj,
                'nota' => $smsStatus === 'simulat' ? 'SMS simulat — configurați SMSLINK_KEY în secrets.php pentru trimitere reală' : 'SMS trimis cu succes'
            ]);
            break;

        // ═══════ LISTA TOATE SMS-URILE DE RECENZIE ═══════
        case 'getRecenziiList':
            requireAuth();

            // Creare tabel daca nu exista
            $db->exec("CREATE TABLE IF NOT EXISTS sms_recenzii (
                id INT PRIMARY KEY AUTO_INCREMENT,
                proiect_id INT NOT NULL,
                client_id INT NOT NULL,
                telefon VARCHAR(20) NOT NULL,
                mesaj TEXT NOT NULL,
                status VARCHAR(20) DEFAULT 'trimis',
                sms_provider_id VARCHAR(100) DEFAULT NULL,
                trimis_de VARCHAR(60) DEFAULT 'Admin',
                trimis_la DATETIME DEFAULT CURRENT_TIMESTAMP,
                delivery_status VARCHAR(20) DEFAULT NULL,
                delivery_checked_at DATETIME DEFAULT NULL,
                recenzie_primita TINYINT(1) DEFAULT 0,
                recenzie_data DATE DEFAULT NULL,
                recenzie_nota TINYINT DEFAULT NULL,
                recenzie_text TEXT DEFAULT NULL,
                note TEXT DEFAULT NULL,
                KEY idx_proiect (proiect_id),
                KEY idx_client (client_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Adauga coloane noi daca lipsesc (migrare idempotenta)
            try {
                $cols = $db->query("SHOW COLUMNS FROM sms_recenzii")->fetchAll(PDO::FETCH_COLUMN);
                $migrations = [
                    'delivery_status' => "ALTER TABLE sms_recenzii ADD COLUMN delivery_status VARCHAR(20) DEFAULT NULL",
                    'delivery_checked_at' => "ALTER TABLE sms_recenzii ADD COLUMN delivery_checked_at DATETIME DEFAULT NULL",
                    'recenzie_primita' => "ALTER TABLE sms_recenzii ADD COLUMN recenzie_primita TINYINT(1) DEFAULT 0",
                    'recenzie_data' => "ALTER TABLE sms_recenzii ADD COLUMN recenzie_data DATE DEFAULT NULL",
                    'recenzie_nota' => "ALTER TABLE sms_recenzii ADD COLUMN recenzie_nota TINYINT DEFAULT NULL",
                    'recenzie_text' => "ALTER TABLE sms_recenzii ADD COLUMN recenzie_text TEXT DEFAULT NULL",
                    'note' => "ALTER TABLE sms_recenzii ADD COLUMN note TEXT DEFAULT NULL"
                ];
                foreach ($migrations as $col => $sql) {
                    if (!in_array($col, $cols)) { try { $db->exec($sql); } catch(Exception $e){} }
                }
            } catch(Exception $e) {}

            // Creare tabel sms_clicks daca nu exista (pt JOIN)
            $db->exec("CREATE TABLE IF NOT EXISTS sms_clicks (
                id INT PRIMARY KEY AUTO_INCREMENT,
                sms_id INT NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                clicked_at DATETIME DEFAULT NULL,
                click_count INT DEFAULT 0,
                ip VARCHAR(45) DEFAULT NULL,
                user_agent TEXT DEFAULT NULL,
                KEY idx_token (token),
                KEY idx_sms (sms_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $rows = $db->query("
                SELECT s.*, c.nume AS client_nume, c.email AS client_email,
                       p.proiect_id AS proiect_cod, p.serviciu, p.status AS proiect_status,
                       sc.clicked_at, sc.click_count, sc.token AS track_token
                FROM sms_recenzii s
                JOIN clienti c ON s.client_id = c.id
                JOIN proiecte p ON s.proiect_id = p.id
                LEFT JOIN sms_clicks sc ON sc.sms_id = s.id
                ORDER BY s.trimis_la DESC
            ")->fetchAll();

            // Statistici rapide
            $total = count($rows);
            $livrate = 0; $recenzii = 0; $clicked = 0;
            foreach ($rows as $r) {
                if ($r['delivery_status'] === 'delivered' || $r['clicked_at']) $livrate++;
                if ($r['clicked_at']) $clicked++;
                if ($r['recenzie_primita']) $recenzii++;
            }

            jsonResponse([
                'success' => true,
                'data' => $rows,
                'stats' => [
                    'total_sms' => $total,
                    'clicked' => $clicked,
                    'recenzii_primite' => $recenzii,
                    'rata_conversie' => $total > 0 ? round($recenzii / $total * 100, 1) : 0
                ]
            ]);
            break;

        // ═══════ UPDATE MANUAL RECENZIE PRIMITĂ ═══════
        case 'updateRecenzie':
            requireAuth();
            $smsId = isset($data['id']) ? intval($data['id']) : 0;
            if (!$smsId) { jsonResponse(['success' => false, 'error' => 'id obligatoriu'], 400); break; }

            $fields = [];
            $values = [];
            if (isset($data['recenzie_primita'])) {
                $fields[] = 'recenzie_primita = ?';
                $values[] = $data['recenzie_primita'] ? 1 : 0;
                if ($data['recenzie_primita'] && !isset($data['recenzie_data'])) {
                    $fields[] = 'recenzie_data = ?';
                    $values[] = date('Y-m-d');
                }
            }
            if (isset($data['recenzie_data'])) { $fields[] = 'recenzie_data = ?'; $values[] = $data['recenzie_data']; }
            if (isset($data['recenzie_nota'])) { $fields[] = 'recenzie_nota = ?'; $values[] = intval($data['recenzie_nota']); }
            if (isset($data['recenzie_text'])) { $fields[] = 'recenzie_text = ?'; $values[] = $data['recenzie_text']; }
            if (isset($data['note'])) { $fields[] = 'note = ?'; $values[] = $data['note']; }
            if (isset($data['delivery_status'])) { $fields[] = 'delivery_status = ?'; $values[] = $data['delivery_status']; $fields[] = 'delivery_checked_at = NOW()'; }

            if (!$fields) { jsonResponse(['success' => false, 'error' => 'Nimic de actualizat'], 400); break; }
            $values[] = $smsId;
            $db->prepare("UPDATE sms_recenzii SET " . implode(', ', $fields) . " WHERE id = ?")->execute($values);
            jsonResponse(['success' => true]);
            break;

        // ═══════ VERIFICARE STATUS SMS RECENZIE ═══════
        case 'getReviewSMSStatus':
            requireAuth();
            $proiectId = isset($_GET['proiect_id']) ? $_GET['proiect_id'] : '';
            if (!$proiectId) { jsonResponse(['success' => false, 'error' => 'proiect_id obligatoriu'], 400); break; }

            // Verifică dacă tabelul există
            try {
                $stmt = $db->prepare("SELECT id, telefon, status, trimis_de, trimis_la FROM sms_recenzii WHERE proiect_id = (SELECT id FROM proiecte WHERE id = ? OR proiect_id = ? LIMIT 1) ORDER BY trimis_la DESC LIMIT 1");
                $stmt->execute([$proiectId, $proiectId]);
                $sms = $stmt->fetch();
            } catch (Exception $e) {
                $sms = null;
            }

            jsonResponse(['success' => true, 'sent' => !!$sms, 'data' => $sms ?: null]);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Actiune necunoscuta: ' . $action], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}