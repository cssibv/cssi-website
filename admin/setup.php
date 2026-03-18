<?php
header('Content-Type: text/html; charset=utf-8');
$pass = isset($_POST['p']) ? $_POST['p'] : '';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $pass !== 'cssi2026') {
    echo '<html><body style="font-family:system-ui;max-width:400px;margin:80px auto;text-align:center">';
    echo '<h2 style="color:#dc2626">CSSI DB Setup</h2>';
    echo '<form method="POST"><input name="p" type="password" placeholder="Parola" style="padding:10px;width:90%;margin:10px 0"><br>';
    echo '<button style="background:#dc2626;color:#fff;padding:10px 30px;border:none;border-radius:6px;cursor:pointer">Install DB</button></form></body></html>';
    exit;
}
require_once __DIR__ . '/db.php';
$db = getDB();
$sql = file_get_contents(__DIR__ . '/schema.sql');
$stmts = explode(';', $sql);
$ok = 0; $err = 0; $msgs = array();
foreach ($stmts as $s) {
    $s = trim($s);
    if (empty($s) || substr($s, 0, 2) === '--') continue;
    try {
        $db->exec($s);
        $ok++;
        if (preg_match('/(?:CREATE TABLE|CREATE OR REPLACE VIEW|INSERT INTO)\s+(?:IF NOT EXISTS\s+)?(\S+)/i', $s, $m)) {
            $msgs[] = 'OK: ' . $m[1];
        }
    } catch (PDOException $e) {
        $err++;
        $msgs[] = 'ERR: ' . substr($s, 0, 50) . ' => ' . $e->getMessage();
    }
}
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo '<html><body style="font-family:system-ui;max-width:600px;margin:40px auto">';
echo '<h2 style="color:#16a34a">Instalare completa!</h2>';
echo '<p>' . $ok . ' operatii OK, ' . $err . ' erori</p>';
foreach ($msgs as $m) {
    $color = (strpos($m, 'OK') === 0) ? '#16a34a' : '#dc2626';
    echo '<div style="color:' . $color . ';font-size:13px">' . htmlspecialchars($m) . '</div>';
}
echo '<h3>Tabele create (' . count($tables) . '):</h3><pre>';
foreach ($tables as $t) echo $t . "\n";
echo '</pre>';
echo '<p><a href="/admin/api.php?action=ping">Test API</a> | <a href="/admin/api.php?action=getClienti">Test Clienti</a> | <a href="/admin/api.php?action=getOferte">Test Oferte</a></p>';
echo '<p style="color:#dc2626;font-weight:bold">STERGE setup.php dupa instalare!</p>';
echo '</body></html>';
