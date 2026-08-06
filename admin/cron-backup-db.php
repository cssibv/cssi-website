<?php
/**
 * CSSI — Backup zilnic baza de date (cron job)
 * ============================================
 * Ruleaza zilnic la 16:00, salveaza dump MySQL gzipat in afara public_html,
 * roteste retentie 14 zile, loghează în tabela cron_log.
 *
 * SETUP CPANEL (Cron Jobs):
 *   Schedule:  0 16 * * *
 *   Command:   /usr/bin/php /home/USERNAME/public_html/admin/cron-backup-db.php
 *
 * SAU prin HTTP cu CRON_SECRET trimis in HEADER (nu in URL — parametrii din URL
 * ajung in clar in access log-ul Apache):
 *   curl -s -H "X-Cron-Key: YOUR_CRON_SECRET" https://cssi.ro/admin/cron-backup-db.php
 *
 * LOCATIE BACKUP:
 *   1. /home/USERNAME/backups/                    (preferat — outside public_html)
 *   2. /home/USERNAME/public_html/_backups/       (fallback — cu .htaccess Deny all)
 *
 * RESTAURARE:
 *   gunzip -c cssi-db-YYYYMMDD-HHMM.sql.gz | mysql -u USER -p DB_NAME
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/cron-guard.php';
date_default_timezone_set('Europe/Bucharest');

cronGuard('cron-backup-db');

$startTime = microtime(true);
$status = 'failed';
$message = '';
$fileSize = 0;
$backupFile = '';

try {
    // ─── 1. Determină directorul de backup ─────────────────────
    $candidates = [
        dirname(dirname(__DIR__)) . '/backups',          // /home/USER/backups (preferat)
        dirname(__DIR__) . '/_backups',                  // public_html/_backups (fallback)
        __DIR__ . '/_backups',                           // admin/_backups (last resort)
    ];
    $backupDir = null;
    foreach ($candidates as $c) {
        if (!is_dir($c)) {
            @mkdir($c, 0750, true);
        }
        if (is_dir($c) && is_writable($c)) {
            $backupDir = $c;
            break;
        }
    }
    if (!$backupDir) {
        throw new Exception('Niciun director de backup disponibil/writable');
    }

    // Protecție web pentru fallback-urile din public_html
    if (strpos($backupDir, 'public_html') !== false) {
        $htaccess = $backupDir . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Order Deny,Allow\nDeny from all\n");
        }
        $indexFile = $backupDir . '/index.html';
        if (!file_exists($indexFile)) {
            @file_put_contents($indexFile, '');
        }
    }

    $stamp = date('Ymd-Hi');
    $backupFile = $backupDir . '/cssi-db-' . $stamp . '.sql.gz';

    // ─── 2. Încearcă mysqldump (rapid, preferat) ───────────────
    $usedMethod = '';
    $mysqldumpPath = '/usr/bin/mysqldump';
    if (!is_executable($mysqldumpPath)) {
        // Caută în alte locații comune
        foreach (['/usr/local/bin/mysqldump', '/opt/cpanel/ea-mysql/usr/bin/mysqldump'] as $alt) {
            if (is_executable($alt)) { $mysqldumpPath = $alt; break; }
        }
    }

    $dumpOk = false;
    if (function_exists('shell_exec') && is_executable($mysqldumpPath)) {
        // Folosim --defaults-extra-file pentru a NU expune parola în argumentele procesului
        $cnfFile = tempnam(sys_get_temp_dir(), 'mysqldump');
        $cnf  = "[client]\n";
        $cnf .= "host=" . DB_HOST . "\n";
        $cnf .= "user=" . DB_USER . "\n";
        $cnf .= "password=\"" . str_replace('"', '\\"', DB_PASS) . "\"\n";
        file_put_contents($cnfFile, $cnf);
        chmod($cnfFile, 0600);

        $cmd = escapeshellcmd($mysqldumpPath)
             . ' --defaults-extra-file=' . escapeshellarg($cnfFile)
             . ' --single-transaction --quick --routines --triggers --no-tablespaces'
             . ' --default-character-set=utf8mb4'
             . ' ' . escapeshellarg(DB_NAME)
             . ' 2>&1 | gzip -9 > ' . escapeshellarg($backupFile);
        $output = shell_exec($cmd);
        @unlink($cnfFile);

        if (file_exists($backupFile) && filesize($backupFile) > 100) {
            // Verifică că nu e un fișier de eroare gzipat
            $head = '';
            $gz = gzopen($backupFile, 'r');
            if ($gz) { $head = gzread($gz, 200); gzclose($gz); }
            if (strpos($head, 'CREATE') !== false || strpos($head, 'INSERT') !== false || strpos($head, '-- ') !== false) {
                $dumpOk = true;
                $usedMethod = 'mysqldump';
            } else {
                @unlink($backupFile);
                throw new Exception('mysqldump output invalid (probabil eroare): ' . substr($head, 0, 150));
            }
        }
    }

    // ─── 3. Fallback PHP PDO dump dacă mysqldump nu merge ──────
    if (!$dumpOk) {
        $db = getDB();
        $gz = gzopen($backupFile, 'w9');
        if (!$gz) throw new Exception('Nu pot deschide fișierul pentru scriere: ' . $backupFile);

        gzwrite($gz, "-- CSSI DB Backup (PHP PDO fallback) " . date('c') . "\n");
        gzwrite($gz, "-- Database: " . DB_NAME . "\n");
        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=0;\nSET NAMES utf8mb4;\nSET time_zone='+00:00';\n\n");

        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $t) {
            $create = $db->query("SHOW CREATE TABLE `$t`")->fetch();
            gzwrite($gz, "\n-- ─── $t ───\n");
            gzwrite($gz, "DROP TABLE IF EXISTS `$t`;\n");
            gzwrite($gz, $create['Create Table'] . ";\n");

            $stmt = $db->query("SELECT * FROM `$t`");
            $colsInfo = null;
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($colsInfo === null) {
                    $colsInfo = '(`' . implode('`,`', array_keys($r)) . '`)';
                }
                $vals = [];
                foreach ($r as $v) {
                    if ($v === null) $vals[] = 'NULL';
                    elseif (is_int($v) || is_float($v)) $vals[] = $v;
                    else $vals[] = $db->quote($v);
                }
                gzwrite($gz, "INSERT INTO `$t` $colsInfo VALUES (" . implode(',', $vals) . ");\n");
            }
        }
        gzwrite($gz, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($gz);
        $usedMethod = 'php-pdo';
        $dumpOk = file_exists($backupFile) && filesize($backupFile) > 100;
    }

    if (!$dumpOk) {
        throw new Exception('Backup eșuat — fișier inexistent sau prea mic');
    }

    $fileSize = filesize($backupFile);

    // ─── 4. Rotație: șterge backup-uri mai vechi de 14 zile ────
    $retentionDays = 14;
    $cutoff = time() - ($retentionDays * 86400);
    $deleted = 0;
    foreach (glob($backupDir . '/cssi-db-*.sql.gz') as $f) {
        if (filemtime($f) < $cutoff) {
            if (@unlink($f)) $deleted++;
        }
    }

    $status = 'success';
    $message = sprintf(
        'Backup OK • metodă=%s • dimensiune=%s • locație=%s • șterse_vechi=%d',
        $usedMethod,
        formatSize($fileSize),
        $backupDir,
        $deleted
    );
} catch (Exception $e) {
    $message = 'ERROR: ' . $e->getMessage();
    error_log('cron-backup-db: ' . $message);
}

$durationMs = (int) round((microtime(true) - $startTime) * 1000);

// ─── 5. Log în cron_log (auto-creează tabela dacă lipsește) ────
try {
    $db = getDB();
    $db->exec("CREATE TABLE IF NOT EXISTS cron_log (
        id INT PRIMARY KEY AUTO_INCREMENT,
        script VARCHAR(100) NOT NULL,
        status VARCHAR(20) NOT NULL,
        message TEXT NULL,
        file_size BIGINT NULL,
        duration_ms INT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_script (script),
        INDEX idx_created (created_at)
    ) DEFAULT CHARSET=utf8mb4");
    // Detectează coloanele existente (file_size, duration_ms pot lipsi pe schema veche)
    $cols = $db->query("SHOW COLUMNS FROM cron_log")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('file_size', $cols, true)) {
        try { $db->exec("ALTER TABLE cron_log ADD COLUMN file_size BIGINT NULL"); } catch (Exception $e) {}
    }
    if (!in_array('duration_ms', $cols, true)) {
        try { $db->exec("ALTER TABLE cron_log ADD COLUMN duration_ms INT NULL"); } catch (Exception $e) {}
    }
    $db->prepare("INSERT INTO cron_log (script, status, message, file_size, duration_ms) VALUES (?,?,?,?,?)")
       ->execute(['cron-backup-db', $status, $message, $fileSize ?: null, $durationMs]);
} catch (Exception $e) {
    error_log('cron-backup-db: log insert failed: ' . $e->getMessage());
}

// Output (CLI sau HTTP)
$out = "[" . date('Y-m-d H:i:s') . "] cron-backup-db: $status • $message • {$durationMs}ms\n";
if ($isCli) {
    echo $out;
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo $out;
}

function formatSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}
