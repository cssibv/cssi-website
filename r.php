<?php
// ============================================================
// CSSI — Redirect Tracker pentru SMS Recenzii
// URL: cssi.ro/r.php?t=TOKEN  (sau cssi.ro/r/TOKEN via .htaccess)
// Logheaza click-ul si redirectioneaza la pagina de recenzie Google
// ============================================================

require_once __DIR__ . '/admin/db.php';

$token = isset($_GET['t']) ? trim($_GET['t']) : '';
if (!$token) {
    header('Location: https://cssi.ro');
    exit;
}

$db = getDB();

// Creaza tabelul daca nu exista
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

// Cauta token-ul
$stmt = $db->prepare("SELECT sc.id, sc.sms_id, sc.clicked_at FROM sms_clicks sc WHERE sc.token = ?");
$stmt->execute([$token]);
$click = $stmt->fetch();

if (!$click) {
    // Token invalid — redirect la pagina principala
    header('Location: https://cssi.ro');
    exit;
}

// Logheaza click-ul
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (!$click['clicked_at']) {
    // Primul click
    $db->prepare("UPDATE sms_clicks SET clicked_at = NOW(), click_count = 1, ip = ?, user_agent = ? WHERE id = ?")
       ->execute([$ip, $ua, $click['id']]);
    // Actualizeaza si sms_recenzii
    $db->prepare("UPDATE sms_recenzii SET delivery_status = 'clicked' WHERE id = ? AND (delivery_status IS NULL OR delivery_status != 'clicked')")
       ->execute([$click['sms_id']]);
} else {
    // Click repetat
    $db->prepare("UPDATE sms_clicks SET click_count = click_count + 1 WHERE id = ?")
       ->execute([$click['id']]);
}

// Redirect la Google Review
$reviewUrl = 'https://search.google.com/local/writereview?placeid=ChIJ4wsfwhHRISkRzf58ZZIo6HA';
header('Location: ' . $reviewUrl);
exit;
