<?php
/**
 * CSSI — Raport Zilnic prin Email (cron job)
 * ============================================
 * Folosit prin cron job (CLI sau HTTP cu CRON_SECRET key).
 *
 * SETUP CPANEL (Cron Job):
 *   Schedule:  0 8 * * *
 *   Command:   /usr/bin/php /home/USERNAME/public_html/admin/cron-raport-zilnic.php
 *
 * SAU prin curl HTTP, cu secretul in HEADER (nu in URL — parametrii din URL
 * ajung in clar in access log-ul Apache):
 *   curl -s -H "X-Cron-Key: YOUR_CRON_SECRET" https://cssi.ro/admin/cron-raport-zilnic.php
 *
 * SETUP secrets.php (admin/secrets.php):
 *   define('CRON_SECRET', 'random_min_32_chars');
 *   define('REPORT_RECIPIENTS', 'cssirobv@gmail.com,office@cssi.ro');
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/raport-helpers.php';
require_once __DIR__ . '/cron-guard.php';
date_default_timezone_set('Europe/Bucharest');

cronGuard('cron-raport-zilnic');

// Destinatari (din secrets sau DB cssi_settings)
$recipients = [];
if (defined('REPORT_RECIPIENTS') && REPORT_RECIPIENTS) {
    $recipients = array_filter(array_map('trim', explode(',', REPORT_RECIPIENTS)));
}
$db = getDB();
if (!$recipients) {
    try {
        $stmt = $db->query("SELECT value FROM cssi_settings WHERE `key` = 'report_recipients' LIMIT 1");
        $row = $stmt ? $stmt->fetch() : null;
        if ($row && $row['value']) $recipients = array_filter(array_map('trim', explode(',', $row['value'])));
    } catch (Exception $e) {}
}
if (!$recipients) {
    error_log('cron-raport-zilnic: niciun destinatar configurat');
    if (!$isCli) echo "No recipients configured\n";
    exit(1);
}

// Auto-expire oferte + notificari follow-up: ruleaza inline (cron-ul nu include api.php).
// Acelasi cod ca autoExpireOferte() din api.php — duplicat aici DOAR pentru garantie zilnica
// in caz ca nimeni nu deschide admin-ul intr-o zi. Idempotent prin notificat_*_la pe oferte.
try {
    // Asigur coloanele (idempotent)
    $cols = $db->query("SHOW COLUMNS FROM oferte")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('notificat_expirare_la', $cols))
        $db->exec("ALTER TABLE oferte ADD COLUMN notificat_expirare_la DATETIME NULL DEFAULT NULL");
    if (!in_array('notificat_pre_expirare_la', $cols))
        $db->exec("ALTER TABLE oferte ADD COLUMN notificat_pre_expirare_la DATETIME NULL DEFAULT NULL");
    try {
        $nc = $db->query("SHOW COLUMNS FROM notificari")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('action_url', $nc)) $db->exec("ALTER TABLE notificari ADD COLUMN action_url VARCHAR(255) NULL DEFAULT NULL");
        if (!in_array('telefon', $nc))    $db->exec("ALTER TABLE notificari ADD COLUMN telefon VARCHAR(30) NULL DEFAULT NULL");
    } catch (Exception $e) {}

    $extractTel = function($txt) {
        if (!$txt) return '';
        if (preg_match('/(\+?\d[\d\s\-\.\(\)]{7,}\d)/', $txt, $m)) return trim($m[1]);
        return '';
    };
    $baseSel = "SELECT o.id, o.oferta_id, o.total_cu_tva, o.client_nume, o.client_contact,
                       o.proiect_id, p.proiect_id AS cod_proiect,
                       c.nume AS client_db_nume, c.telefon AS client_telefon
                FROM oferte o
                LEFT JOIN proiecte p ON o.proiect_id = p.id
                LEFT JOIN clienti  c ON o.client_id  = c.id
                WHERE o.status IN ('Draft','Trimisa','In_discutie')
                  AND o.expires_at IS NOT NULL
                  AND o.archived_at IS NULL";

    // Pre-expirare
    $stPre = $db->prepare($baseSel . " AND o.expires_at = DATE_ADD(CURDATE(), INTERVAL 1 DAY) AND o.notificat_pre_expirare_la IS NULL");
    $stPre->execute();
    $insN = $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la, action_url, telefon) VALUES (?,?,?,?,?,?)");
    $markPre = $db->prepare("UPDATE oferte SET notificat_pre_expirare_la = NOW() WHERE id = ?");
    foreach ($stPre->fetchAll() as $o) {
        $tel = trim((string)($o['client_telefon'] ?? '')); if (!$tel) $tel = $extractTel($o['client_contact'] ?? '');
        $clientLbl = $o['client_db_nume'] ?: ($o['client_nume'] ?: '—');
        $sumLbl = number_format((float)$o['total_cu_tva'], 0, ',', '.') . ' lei';
        $mesaj = '⏰ Sună mâine ' . $clientLbl . ' — oferta ' . ($o['oferta_id'] ?: '#'.$o['id']) . ' (' . $sumLbl . ') expiră mâine' . ($tel ? ' · 📞 ' . $tel : '');
        $insN->execute([$o['cod_proiect'] ?: null, $mesaj, 'oferta_pre_exp', 'Sistem', '/admin/oferte.html', $tel ?: null]);
        $markPre->execute([$o['id']]);
    }

    // Expirate azi
    $stExp = $db->prepare($baseSel . " AND o.expires_at < CURDATE() AND o.notificat_expirare_la IS NULL");
    $stExp->execute();
    $expList = $stExp->fetchAll();
    $db->exec("UPDATE oferte SET status='Expirata' WHERE status IN ('Draft','Trimisa','In_discutie') AND expires_at IS NOT NULL AND expires_at < CURDATE() AND archived_at IS NULL");
    $markExp = $db->prepare("UPDATE oferte SET notificat_expirare_la = NOW() WHERE id = ?");
    foreach ($expList as $o) {
        $tel = trim((string)($o['client_telefon'] ?? '')); if (!$tel) $tel = $extractTel($o['client_contact'] ?? '');
        $clientLbl = $o['client_db_nume'] ?: ($o['client_nume'] ?: '—');
        $sumLbl = number_format((float)$o['total_cu_tva'], 0, ',', '.') . ' lei';
        $mesaj = '📞 Resună ' . $clientLbl . ' — oferta ' . ($o['oferta_id'] ?: '#'.$o['id']) . ' (' . $sumLbl . ') a expirat — întreabă dacă o mai vrea' . ($tel ? ' · 📞 ' . $tel : '');
        $insN->execute([$o['cod_proiect'] ?: null, $mesaj, 'oferta_expirata', 'Sistem', '/admin/oferte.html', $tel ?: null]);
        $markExp->execute([$o['id']]);
    }
} catch (Exception $e) {
    error_log('cron-raport-zilnic: autoExpireOferte fallback FAILED: ' . $e->getMessage());
}

$data = cssiCollectRaportData($db);
$subject = '📊 Raport Zilnic CSSI — ' . date('d.m.Y');
$bodyHtml = cssiRenderRaportHtml($data);
$bodyText = cssiRenderRaportText($data);

$result = cssiSendRaportEmail($recipients, $subject, $bodyHtml, $bodyText);
cssiLogCronEmail($db, 'raport_zilnic', $recipients, $result['sent'], $result['failed'], ['kpi' => $data['kpi']]);

if (!$isCli) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'sent' => $result['sent'], 'failed' => $result['failed'], 'recipients' => count($recipients)]);
}
