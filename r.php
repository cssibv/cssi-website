<?php
// ============================================================
// CSSI — Redirect Tracker pentru cereri de recenzie (WhatsApp/SMS)
// URL: cssi.ro/r.php?t=TOKEN  (sau cssi.ro/r/TOKEN via .htaccess)
// Logheaza click-ul, afiseaza landing CSSI, apoi redirect la Google
// ============================================================

require_once __DIR__ . '/admin/db.php';

$token = isset($_GET['t']) ? trim($_GET['t']) : '';
$reviewUrl = 'https://search.google.com/local/writereview?placeid=ChIJ4wsfwhHRISkRzf58ZZIo6HA';

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
    // Token invalid — redirect direct la pagina principala
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
    $db->prepare("UPDATE sms_recenzii SET delivery_status = 'clicked' WHERE id = ? AND (delivery_status IS NULL OR delivery_status != 'clicked')")
       ->execute([$click['sms_id']]);
} else {
    $db->prepare("UPDATE sms_clicks SET click_count = click_count + 1 WHERE id = ?")
       ->execute([$click['id']]);
}

// Afiseaza landing page CSSI (auto-redirect dupa 3s)
?><!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<meta http-equiv="refresh" content="3;url=<?= htmlspecialchars($reviewUrl, ENT_QUOTES) ?>">
<title>Mulțumim! — CSSI</title>
<link rel="icon" type="image/x-icon" href="/images/favicon.ico">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',system-ui,sans-serif;background:linear-gradient(135deg,#0f172a 0%,#1e293b 60%,#dc2626 130%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;color:#fff;-webkit-font-smoothing:antialiased;}
.card{background:rgba(255,255,255,0.06);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.12);border-radius:24px;padding:40px 32px;max-width:460px;width:100%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.4);}
.logo{display:inline-flex;align-items:center;justify-content:center;width:80px;height:80px;border-radius:20px;background:#dc2626;font-size:36px;font-weight:900;margin-bottom:20px;box-shadow:0 8px 24px rgba(220,38,38,0.4);}
h1{font-size:26px;font-weight:900;letter-spacing:-0.5px;margin-bottom:10px;line-height:1.2;}
.sub{font-size:15px;color:rgba(255,255,255,0.75);line-height:1.5;margin-bottom:28px;}
.stars{font-size:32px;letter-spacing:4px;margin:10px 0 20px;color:#fbbf24;text-shadow:0 2px 12px rgba(251,191,36,0.4);}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;background:#fff;color:#0f172a;padding:14px 26px;border-radius:99px;font-weight:800;font-size:15px;text-decoration:none;transition:all 0.2s;box-shadow:0 8px 24px rgba(0,0,0,0.3);width:100%;max-width:340px;}
.btn:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(0,0,0,0.4);}
.btn svg{width:20px;height:20px;}
.countdown{font-size:13px;color:rgba(255,255,255,0.55);margin-top:18px;}
.countdown b{color:#fff;font-weight:800;}
.foot{margin-top:24px;padding-top:20px;border-top:1px solid rgba(255,255,255,0.08);font-size:12px;color:rgba(255,255,255,0.5);}
.foot a{color:rgba(255,255,255,0.8);text-decoration:none;font-weight:600;}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.05)}}
.logo{animation:pulse 2s ease-in-out infinite;}
</style>
</head>
<body>
<div class="card">
    <div class="logo">C</div>
    <h1>Mulțumim că ne-ai ales! 🙏</h1>
    <div class="stars">⭐ ⭐ ⭐ ⭐ ⭐</div>
    <div class="sub">Părerea ta contează enorm pentru noi. Te redirecționăm către Google ca să poți lăsa o recenzie — durează mai puțin de un minut.</div>
    <a class="btn" href="<?= htmlspecialchars($reviewUrl, ENT_QUOTES) ?>">
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
        Lasă o recenzie pe Google
    </a>
    <div class="countdown">Redirecționare automată în <b id="cd">3</b> secunde…</div>
    <div class="foot">
        <a href="https://cssi.ro">CSSI — Confort, Securitate, Siguranță, Imobil</a>
    </div>
</div>
<script>
var n=3, el=document.getElementById('cd');
var t=setInterval(function(){n--; if(el) el.textContent=n; if(n<=0){clearInterval(t);}},1000);
</script>
</body>
</html>
