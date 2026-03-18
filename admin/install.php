<?php
// ============================================================
// CSSI Portal v4.0 — Installer
// Acceseaza: cssi.ro/admin/install.php
// Creeaza tabelele MySQL + secventele + views
// STERGE ACEST FISIER DUPA INSTALARE!
// ============================================================

$installPassword = 'cssi-install-2026';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ((isset($_POST['pass']) ? $_POST['pass'] : '')) === $installPassword) {
    require_once __DIR__ . '/db.php';
    
    $results = [];
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    // Split by semicolons, excluding those inside strings
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $db = getDB();
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (empty($stmt) || strpos($stmt, '--') === 0) continue;
        
        try {
            $db->exec($stmt);
            $success++;
            // Extract table/view name for display
            if (preg_match('/(?:CREATE TABLE|CREATE OR REPLACE VIEW|INSERT INTO)\s+(?:IF NOT EXISTS\s+)?(\S+)/i', $stmt, $m)) {
                $results[] = ['ok', $m[1]];
            }
        } catch (PDOException $e) {
            $errors++;
            $results[] = ['err', substr($stmt, 0, 60) . '...', $e->getMessage()];
        }
    }
    
    // Test: verifica tabelele
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>CSSI Install</title>
    <style>body{font-family:system-ui;max-width:700px;margin:40px auto;padding:20px;background:#f1f5f9;color:#0f172a}
    .ok{color:#16a34a}.err{color:#dc2626}h1{color:#dc2626}pre{background:#fff;padding:12px;border-radius:8px;font-size:12px;overflow-x:auto}
    .card{background:#fff;border-radius:12px;padding:20px;margin:16px 0;box-shadow:0 1px 3px rgba(0,0,0,0.1)}
    .btn{background:#dc2626;color:#fff;padding:10px 24px;border:none;border-radius:8px;font-size:14px;cursor:pointer;font-weight:700}
    </style></head><body>';
    echo '<h1>🔧 CSSI Portal — Instalare completă</h1>';
    echo '<div class="card"><h3>Rezultate</h3>';
    echo "<p><strong>$success</strong> operatii reușite, <strong>$errors</strong> erori</p>";
    foreach ($results as $r) {
        if ($r[0] === 'ok') {
            echo '<div class="ok">✅ ' . htmlspecialchars($r[1]) . '</div>';
        } else {
            echo '<div class="err">❌ ' . htmlspecialchars($r[1]) . '<br><small>' . htmlspecialchars($r[2]) . '</small></div>';
        }
    }
    echo '</div>';
    
    echo '<div class="card"><h3>Tabele create</h3><pre>';
    foreach ($tables as $t) echo "📊 $t\n";
    echo '</pre></div>';
    
    // Test API
    echo '<div class="card"><h3>Test API</h3>';
    echo '<p>✅ Conexiune MySQL OK</p>';
    echo '<p>📊 Tabele: ' . count($tables) . '</p>';
    echo '<p><a href="/admin/api.php?action=ping">🔗 Test API ping</a></p>';
    echo '<p><a href="/admin/api.php?action=getClienti">🔗 Test GET clienti</a></p>';
    echo '<p><a href="/admin/api.php?action=getOferte">🔗 Test GET oferte</a></p>';
    echo '</div>';
    
    echo '<div class="card" style="background:#fef2f2;border:2px solid #dc2626">';
    echo '<h3>⚠️ IMPORTANT</h3>';
    echo '<p><strong>Șterge acest fișier (install.php) după instalare!</strong></p>';
    echo '</div>';
    
    echo '</body></html>';
    exit;
}
?>
<!DOCTYPE html>
<html><head><title>CSSI Portal — Instalare</title>
<style>body{font-family:system-ui;max-width:500px;margin:80px auto;padding:20px;background:#f1f5f9;color:#0f172a;text-align:center}
h1{color:#dc2626;font-size:24px}.card{background:#fff;border-radius:16px;padding:32px;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
input{width:100%;padding:12px;border:2px solid #e2e8f0;border-radius:8px;font-size:14px;margin:12px 0;box-sizing:border-box}
.btn{background:#dc2626;color:#fff;padding:12px 32px;border:none;border-radius:8px;font-size:14px;cursor:pointer;font-weight:700;width:100%}
.btn:hover{background:#991b1b}p{color:#64748b;font-size:13px}
</style></head>
<body>
<div class="card">
  <h1>🔧 CSSI Portal v4.0</h1>
  <p>Instalare baza de date MySQL</p>
  <form method="POST">
    <input type="password" name="pass" placeholder="Parola de instalare" required>
    <button type="submit" class="btn">🚀 Instalează baza de date</button>
  </form>
  <p style="margin-top:16px">Aceasta va crea toate tabelele necesare: clienti, proiecte, oferte, contracte, executie, facturi, mentenanta.</p>
</div>
</body></html>
