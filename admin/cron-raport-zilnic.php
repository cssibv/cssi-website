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
 * SAU prin curl HTTP (mai simplu de testat):
 *   curl -s "https://cssi.ro/admin/cron-raport-zilnic.php?key=YOUR_CRON_SECRET"
 *
 * SETUP secrets.php (admin/secrets.php):
 *   define('CRON_SECRET', 'random_min_32_chars');
 *   define('REPORT_RECIPIENTS', 'cssirobv@gmail.com,office@cssi.ro');
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/raport-helpers.php';
date_default_timezone_set('Europe/Bucharest');

$isCli = php_sapi_name() === 'cli';
$keyParam = $_GET['key'] ?? '';
$keyOk = defined('CRON_SECRET') && CRON_SECRET && hash_equals(CRON_SECRET, (string)$keyParam);
if (!$isCli && !$keyOk) {
    http_response_code(403);
    exit('Forbidden — set CRON_SECRET in secrets.php and pass ?key=...');
}

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
