<?php
// ============================================================
// CSSI Portal v4.0 — Authentication Helper
// session_start() + helpers pentru protecția endpoint-urilor
// ============================================================

// Session config — securitate
ini_set('session.cookie_httponly', '1');           // Cookie inaccesibil din JS (anti-XSS)
ini_set('session.cookie_secure', '1');             // Doar HTTPS
ini_set('session.cookie_samesite', 'Strict');      // Anti-CSRF strong (era Lax)
ini_set('session.use_strict_mode', '1');           // Refuză session ID nevalid
session_set_cookie_params([
    'lifetime' => 0,                                // Session cookie (până la închidere browser)
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',                         // Strict — cookie nu se trimite cross-site, anti-CSRF
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Asigură că tabela users există + seed conturile inițiale
function ensureUsersTable($db) {
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(60) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        display_name VARCHAR(100),
        role VARCHAR(40) NOT NULL DEFAULT 'tech',
        is_tehnician TINYINT(1) DEFAULT 0,
        active TINYINT(1) DEFAULT 1,
        last_login DATETIME,
        failed_attempts INT DEFAULT 0,
        locked_until DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        KEY idx_username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Seed conturile inițiale dacă tabela e goală
    $cnt = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($cnt > 0) return;

    $seed = [
        ['admin',    'admin',         'Administrator', 'admin',    0],
        ['mihai',    'mihai2026',     'Mihai',         'sales',    0],
        ['roxana',   'roxana2026',    'Roxana',        'org',      0],
        ['valentin', 'valentin2026',  'Valentin',      'tech',     0],
        ['cristina', 'cristina2026',  'Cristina',      'sales',    0],
        ['zoli',     'zoli2000',      'Zoli',          'tech',     1],
        ['sanyi',    'sanyi3000',     'Sanyi',         'tech',     1],
        ['bogdan',   'bogdan4000',    'Bogdan',        'tech',     1],
        ['cezar',    'cezar5000',     'Cezar',         'tech',     1],
        ['cristi',   'cristi6000',    'Cristi',        'tech',     1],
        ['denes',    'denes7000',     'Denes',         'tech',     1],
    ];
    $stmt = $db->prepare("INSERT INTO users (username, password_hash, display_name, role, is_tehnician) VALUES (?,?,?,?,?)");
    foreach ($seed as $u) {
        $hash = password_hash($u[1], PASSWORD_BCRYPT, ['cost' => 11]);
        $stmt->execute([$u[0], $hash, $u[2], $u[3], $u[4]]);
    }
}

// Endpoint-uri care NU necesită autentificare
function publicActions() {
    // getContractByToken + submitContractDate trebuie publice — clientul primeste
    // link-ul cu token pe WhatsApp, fara cont in platforma
    return ['login', 'logout', 'me', 'ping', 'getContractByToken', 'submitContractDate'];
}

// Returnează user-ul curent sau null
function currentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

// Cere autentificare — die cu 401 dacă lipsește
function requireAuth() {
    $u = currentUser();
    if (!$u) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Neautentificat. Login required.', 'code' => 'AUTH_REQUIRED']);
        exit;
    }
    return $u;
}

// Cere rol admin
function requireAdmin() {
    $u = requireAuth();
    if (($u['role'] ?? '') !== 'admin') {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Acces interzis. Doar admin.', 'code' => 'ADMIN_REQUIRED']);
        exit;
    }
    return $u;
}

function isAdmin() {
    $u = currentUser();
    return $u && ($u['role'] ?? '') === 'admin';
}

function isTehnician() {
    $u = currentUser();
    return $u && !empty($u['is_tehnician']);
}

// ─── MODULE ACCESS ────────────────────────────────────────────
// Lista TOATE modulele din portal (sursă unică de adevăr)
function allModules() {
    return ['proiecte','crm','calculator','contracte','financiar','planificare','executie','proiectare',
            'mentenanta','materiale','necesar','social','marketing','planificator','documente','utilizatori'];
}

// Defaults per rol — folosite când user_config.modules e gol
function defaultModulesForUser($u) {
    if (($u['role'] ?? '') === 'admin') return allModules();
    if (!empty($u['is_tehnician'])) return ['executie','planificare','necesar','mentenanta'];
    switch ($u['role'] ?? '') {
        case 'sales': return ['calculator','contracte','crm','proiecte','financiar','marketing','social','recenzii','documente'];
        case 'org':   return ['proiecte','contracte','planificare','financiar','mentenanta','documente','crm','recenzii'];
        case 'mkt':   return ['marketing','social','recenzii','documente'];
        case 'tech':  return ['proiecte','proiectare','executie','planificare','materiale','necesar','mentenanta','documente'];
        default:      return [];
    }
}

// Returnează lista de module accesibile pentru un user.
// Logica: dacă există override în user_config.modules → folosește acela.
// Altfel → defaults per rol.
function getAllowedModules($db, $u) {
    if (($u['role'] ?? '') === 'admin') return allModules(); // admin = mereu tot
    // Citim override din user_config
    try {
        $stmt = $db->prepare("SELECT modules FROM user_config WHERE user_id = ?");
        $stmt->execute([$u['username']]);
        $row = $stmt->fetch();
        if ($row && !empty($row['modules'])) {
            $custom = json_decode($row['modules'], true);
            if (is_array($custom) && !empty($custom)) {
                // utilizatori e mereu admin-only, nu suprascriem
                $custom = array_diff($custom, ['utilizatori']);
                // Auto-inject recenzii pentru oricine are marketing
                if (in_array('marketing', $custom) && !in_array('recenzii', $custom)) {
                    $custom[] = 'recenzii';
                }
                return array_values($custom);
            }
        }
    } catch (Exception $e) { /* user_config tabelă lipsă — folosim defaults */ }
    $def = defaultModulesForUser($u);
    return array_values(array_diff($def, ['utilizatori']));
}

// Login attempt — întoarce array cu success + user/error
function attemptLogin($db, $username, $password) {
    ensureUsersTable($db);
    $username = strtolower(trim($username));
    if (!$username || !$password) {
        return ['success' => false, 'error' => 'Username și parolă obligatorii'];
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND active = 1");
    $stmt->execute([$username]);
    $u = $stmt->fetch();
    if (!$u) {
        usleep(800000); // 0.8s delay anti-enumerare
        return ['success' => false, 'error' => 'Credențiale invalide'];
    }

    // Verifică lockout
    if (!empty($u['locked_until']) && strtotime($u['locked_until']) > time()) {
        $remaining = ceil((strtotime($u['locked_until']) - time()) / 60);
        return ['success' => false, 'error' => "Cont blocat. Reîncearcă în $remaining minute.", 'code' => 'LOCKED'];
    }

    if (!password_verify($password, $u['password_hash'])) {
        // Incrementează failed
        $newFailed = intval($u['failed_attempts']) + 1;
        $lockUntil = null;
        if ($newFailed >= 5) {
            $lockUntil = date('Y-m-d H:i:s', time() + 15 * 60); // 15 min lockout
        }
        $db->prepare("UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?")
           ->execute([$newFailed, $lockUntil, $u['id']]);
        usleep(800000);
        if ($lockUntil) return ['success' => false, 'error' => 'Prea multe încercări. Cont blocat 15 minute.', 'code' => 'LOCKED'];
        return ['success' => false, 'error' => 'Credențiale invalide'];
    }

    // Succes — reset failed, marchează last_login
    $db->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = ?")
       ->execute([$u['id']]);

    // Regenerare ID sesiune anti-fixation
    session_regenerate_id(true);

    $userInfo = [
        'id'           => intval($u['id']),
        'username'     => $u['username'],
        'display_name' => $u['display_name'],
        'role'         => $u['role'],
        'is_tehnician' => (bool)$u['is_tehnician'],
    ];
    $_SESSION['user'] = $userInfo;
    return ['success' => true, 'user' => $userInfo];
}

// Verificare CSRF: pentru toate POST-urile, cerem fie X-Requested-With, fie
// Content-Type application/json (browser-ele nu permit cross-origin form POST
// cu header-uri custom fără CORS preflight). SameSite=Strict pe cookie face
// CSRF practic imposibil din cross-site, asta e doar o linie suplimentară.
function requireCsrfProtection() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return;
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    // Acceptăm dacă: Content-Type = application/json, SAU X-Requested-With = XMLHttpRequest,
    // SAU multipart/form-data (necesar pt upload, dar SameSite=Strict deja protejează)
    if (stripos($ct, 'application/json') !== false) return;
    if ($xrw === 'XMLHttpRequest' || $xrw === 'fetch') return;
    if (stripos($ct, 'multipart/form-data') !== false) return;
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'CSRF protection: header lipsă', 'code' => 'CSRF_BLOCKED']);
    exit;
}

// Forțează ca user-ul țintă să fie cel curent (anti scraping notificări altora)
// Dacă $userParam e setat și diferit de currentUser și nu sunt admin → 403
function enforceOwnUserOrAdmin($userParam) {
    $u = currentUser();
    if (!$u) requireAuth(); // 401
    if (isAdmin()) return $userParam ?: $u['username']; // admin poate cere altcuiva
    // Non-admin: ignor parametru, returnez username-ul lui
    return $u['username'];
}

// Helper: verifică ownership pe un row (admin sau user creator)
function requireOwnerOrAdmin($creatorUsername) {
    $u = requireAuth();
    if (isAdmin()) return $u;
    if (strtolower(trim($creatorUsername)) === strtolower($u['username'])) return $u;
    if (strtolower(trim($creatorUsername)) === strtolower($u['display_name'] ?? '')) return $u;
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => 'Acces interzis. Doar autorul sau admin poate modifica.', 'code' => 'OWNERSHIP_REQUIRED']);
    exit;
}

function doLogout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
