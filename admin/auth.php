<?php
// ============================================================
// CSSI Portal v4.0 — Authentication Helper
// session_start() + helpers pentru protecția endpoint-urilor
// ============================================================

// Session config — securitate
ini_set('session.cookie_httponly', '1');           // Cookie inaccesibil din JS (anti-XSS)
ini_set('session.cookie_secure', '1');             // Doar HTTPS
ini_set('session.cookie_samesite', 'Lax');         // Anti-CSRF basic
ini_set('session.use_strict_mode', '1');           // Refuză session ID nevalid
session_set_cookie_params([
    'lifetime' => 0,                                // Session cookie (până la închidere browser) — sau 86400 pt 24h
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
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
    return ['login', 'logout', 'me', 'ping'];
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

function doLogout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
