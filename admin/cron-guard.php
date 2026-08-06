<?php
/**
 * CSSI — Guard comun pentru scripturile de cron
 * ==============================================
 * Motivul existenței: secretul trimis ca `?key=...` ajunge în CLAR în access
 * log-ul Apache, care e citibil de oricine are cPanel și se păstrează în
 * arhive. Header-ul nu e logat, deci e forma corectă pentru apeluri HTTP.
 *
 * Ordinea de verificare:
 *   1. Rulare din linia de comandă (cron cPanel) → permis fără secret
 *   2. Header `X-Cron-Key`                       → recomandat pentru HTTP
 *   3. `?key=...` în URL                         → încă merge, dar DEPRECAT
 *                                                  (scrie avertisment în log)
 *
 * Utilizare, la începutul fiecărui script de cron, după require db.php:
 *     require_once __DIR__ . '/cron-guard.php';
 *     cronGuard();
 *
 * Apel HTTP corect (secretul nu ajunge în log):
 *     curl -s -H "X-Cron-Key: SECRETUL" https://cssi.ro/admin/cron-social.php
 */

/**
 * Oprește execuția cu 403 dacă apelul nu e autorizat.
 *
 * @param string $scriptName Nume folosit doar în mesajele de log.
 */
function cronGuard($scriptName = '') {
    if (php_sapi_name() === 'cli') return;   // cron-ul din cPanel

    $expected = defined('CRON_SECRET') ? (string)CRON_SECRET : '';
    if ($expected === '' || strlen($expected) < 16) {
        cronGuardDeny(503, 'CRON_SECRET nesetat în secrets.php (minim 16 caractere)');
    }

    // 1) Header — forma recomandată. Apache expune header-ele custom prin
    //    HTTP_* în $_SERVER; unele configurații le dau doar prin getallheaders().
    $header = '';
    if (isset($_SERVER['HTTP_X_CRON_KEY'])) {
        $header = (string)$_SERVER['HTTP_X_CRON_KEY'];
    } elseif (function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) {
            if (strcasecmp($k, 'X-Cron-Key') === 0) { $header = (string)$v; break; }
        }
    }
    if ($header !== '' && hash_equals($expected, $header)) return;

    // 2) Query string — merge, dar lasă secretul în access log.
    $param = isset($_GET['key']) ? (string)$_GET['key'] : '';
    if ($param !== '' && hash_equals($expected, $param)) {
        error_log(sprintf(
            'CRON-DEPRECAT: %s a fost apelat cu ?key= in URL. Secretul ajunge in access log. '
            . 'Foloseste headerul X-Cron-Key sau ruleaza scriptul din CLI. IP=%s',
            $scriptName !== '' ? $scriptName : basename($_SERVER['SCRIPT_NAME'] ?? '?'),
            $_SERVER['REMOTE_ADDR'] ?? '?'
        ));
        return;
    }

    // Întârziere mică — descurajează ghicirea prin încercări repetate.
    usleep(500000);
    cronGuardDeny(403, 'Forbidden — trimite secretul prin headerul X-Cron-Key');
}

function cronGuardDeny($code, $msg) {
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $msg . "\n";
    exit;
}
