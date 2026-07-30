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

// ═══════════════════════════════════════════════════════════════
// AUTORIZARE PE MODULE — deocamdată în modul OBSERVARE
// ═══════════════════════════════════════════════════════════════
// Problema: access-guard.js protejează PAGINA, nu DATELE. Un user fără
// modulul „calculator" e redirecționat de la /admin/oferte.html, dar poate
// apela direct api.php?action=getOferte și primește tot, cu prețuri de
// achiziție și adaos.
//
// Harta de mai jos a fost derivată din codul real: pentru fiecare pagină cu
// data-module s-au extras acțiunile pe care le apelează. Acțiunile chemate
// din module diferite au mai multe module acceptate (e destul UNUL).
//
// ⚠️ MODUL OBSERVARE: auditModuleAccess() DOAR loghează încălcările, nu
// blochează nimic. Se rulează așa ~1 săptămână; dacă log-ul rămâne curat,
// se schimbă error_log cu un răspuns 403 (vezi comentariul din funcție).
//
// Acțiunile care NU apar în hartă sunt scutite (comportament actual).
function actionModules() {
    return [
        // ── CRM / clienți ────────────────────────────────────────
        'getClienti'            => ['crm','calculator','interventii','planificare','proiecte'],
        'getClient'             => ['crm','calculator','proiecte'],
        'getClientFull'         => ['crm'],
        'createClient'          => ['crm','proiecte'],
        'updateClient'          => ['crm'],
        'deleteClient'          => ['crm'],

        // ── Proiecte + dosar ─────────────────────────────────────
        'getProiecte'           => ['proiecte','crm','proiectare'],
        'getProiect'            => ['proiecte','crm','proiectare','executie'],
        'createProiect'         => ['proiecte','crm'],
        'updateProiect'         => ['proiecte','crm'],
        'deleteProiect'         => ['proiecte'],
        'updateStatus'          => ['proiecte','crm','executie','proiectare'],
        'setStatus'             => ['proiecte'],
        'archive'               => ['proiecte'],
        'unarchive'             => ['proiecte'],
        'delete'                => ['proiecte'],
        'preiaProiect'          => ['proiecte'],
        'getProiecteNeprelate'  => ['proiecte'],
        'getDateFinalizare'     => ['proiecte'],
        'finalizeazaProiect'    => ['proiecte'],
        'sendFinalizareEmail'   => ['proiecte'],
        'getGarantiiExpiraCurand' => ['proiecte'],
        'runMigrationFinalizare'  => ['proiecte'],
        'getDosar'              => ['proiecte'],
        'getDosareClienti'      => ['proiecte'],
        'addDosarNota'          => ['proiecte'],
        'dosarAvanseazaStage'   => ['proiecte'],
        'dosarDezblocheaza'     => ['proiecte'],
        'dosarMarcheazaBlocaj'  => ['proiecte'],
        'dosarMarcheazaPierdut' => ['proiecte'],

        // ── Ofertare ─────────────────────────────────────────────
        'getOferte'             => ['calculator','crm','proiecte'],
        'getOferta'             => ['calculator'],
        'saveOferta'            => ['calculator'],
        'deleteOferta'          => ['calculator'],
        'archiveOferta'         => ['calculator'],
        'unarchiveOferta'       => ['calculator'],
        'bulkOferte'            => ['calculator'],
        'exportOferteCSV'       => ['calculator'],
        'nextOfertaId'          => ['calculator'],
        'updateOfertaStatus'    => ['calculator','crm'],
        'saveOfertaDraft'       => ['calculator'],
        'getOfertaDraft'        => ['calculator'],
        'listOferteDrafturi'    => ['calculator'],
        'deleteOfertaDraft'     => ['calculator'],
        '_debugOferteSchema'    => ['calculator'],

        // ── Contracte ────────────────────────────────────────────
        'getContracte'          => ['contracte'],
        'getContract'           => ['contracte'],
        'updateContract'        => ['contracte'],
        'deleteContract'        => ['contracte'],
        'completeContractDate'  => ['contracte'],
        'getContractMateriale'  => ['contracte'],
        'getContractAccessLog'  => ['contracte'],
        'generateContractDoc'   => ['contracte'],
        'createContractDraft'   => ['contracte'],
        'regenerateContractToken' => ['contracte'],
        '_debugContracteSchema' => ['contracte'],

        // ── Intervenții / planificare / execuție ─────────────────
        'getInterventii'        => ['interventii'],
        'createInterventie'     => ['interventii','planificare'],
        'deleteInterventie'     => ['interventii'],
        'getInterventiePV'      => ['interventii'],
        'saveInterventiePV'     => ['interventii'],
        'migrateReclamatii'     => ['interventii'],
        'getExecutie'           => ['executie','planificare'],
        'getProiectExecutie'    => ['executie'],
        'saveProgramare'        => ['executie','interventii','planificare'],
        'deleteProgramare'      => ['executie','interventii','planificare'],
        'uploadProiectFile'     => ['executie'],
        'deleteProiectFile'     => ['executie'],
        'getProiectMateriale'   => ['executie'],
        'addProgresMaterial'    => ['executie'],
        'deleteProgresMaterial' => ['executie'],
        'addJurnalEntryExec'    => ['executie'],
        'deleteJurnalEntryExec' => ['executie'],

        // ── Proiectare ───────────────────────────────────────────
        'getProiectare'         => ['proiectare'],
        'updateProiectare'      => ['proiectare'],
        'getProiectareList'     => ['proiectare'],
        'getProiecteProiectare' => ['proiectare'],
        'getProiectareChecklist'=> ['proiectare'],
        'toggleChecklist'       => ['proiectare'],
        'saveProiectareItem'    => ['proiectare'],
        'bulkSaveProiectareItems' => ['proiectare'],
        'uploadProiectareDoc'   => ['proiectare'],
        'deleteProiectareDoc'   => ['proiectare'],
        'getJurnalTeren'        => ['proiectare'],
        'addJurnalTeren'        => ['proiectare'],
        'addJurnalEntry'        => ['proiectare'],
        'updateJurnalTeren'     => ['proiectare'],
        'deleteJurnalTeren'     => ['proiectare'],

        // ── Necesar materiale ────────────────────────────────────
        'getNecesarMateriale'   => ['necesar'],
        'markOfertaComandata'   => ['necesar'],
        'unmarkOfertaComandata' => ['necesar'],

        // ── Mentenanță ───────────────────────────────────────────
        'getMentenanta'         => ['mentenanta'],
        'addMentenanta'         => ['mentenanta'],
        'updateMentenanta'      => ['mentenanta'],
        'updateMentenantaStatus'=> ['mentenanta'],
        'markMentenantaDone'    => ['mentenanta'],

        // ── Reclamații (modul public — toți îl au) ───────────────
        'getReclamatii'         => ['reclamatii'],
        'saveReclamatie'        => ['reclamatii'],
        'updateReclamatieStatus'=> ['reclamatii'],
        'deleteReclamatie'      => ['reclamatii'],

        // ── Marketing / social / recenzii ────────────────────────
        'getSocialPosts'        => ['marketing','social'],
        'saveSocialPost'        => ['marketing','social'],
        'deleteSocialPost'      => ['marketing','social'],
        'updateSocialStatus'    => ['marketing','social'],
        'publishSocialPost'     => ['marketing','social'],
        'generateSocialImage'   => ['marketing','social'],
        'generateSinglePost'    => ['marketing','social'],
        'generateWeekPosts'     => ['marketing','social'],
        'uploadSocialMedia'     => ['marketing','social'],
        'pingClaude'            => ['marketing','social'],
        'getRecenziiList'       => ['marketing'],
        'updateRecenzie'        => ['marketing'],
        'deleteRecenzie'        => ['marketing'],
        'sendReviewSMS'         => ['marketing'],
        'getReviewSMSStatus'    => ['marketing','executie'],

        // ── Administrare (deja protejate cu requireAdmin) ────────
        'adminListUsers'        => ['utilizatori'],
        'adminCreateUser'       => ['utilizatori'],
        'adminUpdateUser'       => ['utilizatori'],
        'adminSetPassword'      => ['utilizatori'],
        'adminUnlockUser'       => ['utilizatori'],
        'adminGetUserModules'   => ['utilizatori'],
        'adminSetUserModules'   => ['utilizatori'],
        'raportZilnicPreview'   => ['utilizatori'],
        'raportZilnicSendNow'   => ['utilizatori'],
        'getReportRecipients'   => ['utilizatori'],
        'setReportRecipients'   => ['utilizatori'],
        'getCronLog'            => ['utilizatori'],

        // ── SCUTITE INTENȚIONAT (transversale, orice user autentificat) ──
        // login, logout, me, ping, _resetLock, checkModuleAccess, getMyModules,
        // getUserConfig, saveUserConfig, changeMyPassword, getNotificari,
        // markNotificareRead, markAllRead, getDashboard, getDashboardStats,
        // getActivitateRecenta, getContractByToken, submitContractDate,
        // getProjects, getProiectMateriale (dashboard)
    ];
}

// Verifică dacă user-ul are dreptul la acțiune — DEOCAMDATĂ DOAR LOGHEAZĂ.
// Când log-ul e curat, înlocuiește error_log(...) cu:
//   http_response_code(403);
//   header('Content-Type: application/json; charset=utf-8');
//   echo json_encode(['success'=>false,'error'=>'Acces interzis pentru acest modul.','code'=>'MODULE_REQUIRED']);
//   exit;
function auditModuleAccess($db, $action) {
    $u = currentUser();
    if (!$u) return;                              // gate-ul de auth se ocupă
    if (($u['role'] ?? '') === 'admin') return;   // admin are tot
    $map = actionModules();
    if (!isset($map[$action])) return;            // acțiune scutită
    try {
        $allowed = getAllowedModules($db, $u);
    } catch (Exception $e) {
        return;                                   // nu blocăm din cauza unei erori de citire
    }
    foreach ($map[$action] as $needed) {
        if (in_array($needed, $allowed, true)) return; // are cel puțin unul → OK
    }
    error_log(sprintf(
        'AUTHZ-AUDIT: user=%s rol=%s actiune=%s necesita=[%s] are=[%s]',
        $u['username'] ?? '?',
        $u['role'] ?? '?',
        $action,
        implode('|', $map[$action]),
        implode('|', $allowed)
    ));
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
            'interventii','mentenanta','reclamatii','materiale','necesar','social','marketing','planificator','documente','utilizatori'];
}

// Defaults per rol — folosite când user_config.modules e gol
function defaultModulesForUser($u) {
    if (($u['role'] ?? '') === 'admin') return allModules();
    if (!empty($u['is_tehnician'])) return ['executie','planificare','interventii','necesar','mentenanta','reclamatii'];
    switch ($u['role'] ?? '') {
        case 'sales': return ['calculator','contracte','crm','proiecte','financiar','marketing','social','recenzii','documente'];
        case 'org':   return ['proiecte','contracte','planificare','interventii','financiar','mentenanta','reclamatii','documente','crm','recenzii'];
        case 'mkt':   return ['marketing','social','recenzii','documente'];
        case 'tech':  return ['proiecte','proiectare','executie','planificare','interventii','materiale','necesar','mentenanta','reclamatii','documente'];
        default:      return [];
    }
}

// Returnează lista de module accesibile pentru un user.
// Logica: dacă există override în user_config.modules → folosește acela.
// Altfel → defaults per rol.
// Module publice — vizibile/accesibile pentru TOȚI utilizatorii, indiferent de rol
// sau de override-ul din user_config (intervenții + reclamații).
function publicModules() {
    return ['interventii', 'reclamatii'];
}
function injectPublicModules($list) {
    foreach (publicModules() as $pub) {
        if (!in_array($pub, $list, true)) $list[] = $pub;
    }
    return $list;
}

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
                // Module publice — pentru toată lumea
                $custom = injectPublicModules($custom);
                return array_values($custom);
            }
        }
    } catch (Exception $e) { /* user_config tabelă lipsă — folosim defaults */ }
    $def = array_diff(defaultModulesForUser($u), ['utilizatori']);
    $def = injectPublicModules($def);
    return array_values($def);
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
