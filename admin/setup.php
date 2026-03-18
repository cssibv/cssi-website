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
// === QUICK FIX MODE ===
if (isset($_GET['fix']) && $pass === 'cssi2026') {
    $file = __DIR__ . '/calculator-pret.html';
    $content = file_get_contents($file);
    $changed = false;
    // Fix 1: commented out variables
    $old1 = '// Google API removed — using MySQL localvar DEFAULT_ADAOS=40;var OFFER_START_NR=244100;';
    if (strpos($content, $old1) !== false) {
        $content = str_replace($old1, 'var DEFAULT_ADAOS=40;var OFFER_START_NR=244100;', $content);
        $changed = true;
    }
    if ($changed) {
        file_put_contents($file, $content);
        echo '<h2 style="color:green">FIXED! calculator-pret.html reparat.</h2>';
    } else {
        echo '<h2 style="color:blue">Deja reparat sau pattern diferit.</h2>';
    }
    echo '<p><a href="/admin/calculator-pret">Testeaza acum</a></p>';
    exit;
}

require_once __DIR__ . '/db.php';
$db = getDB();
$ok = 0; $err = 0; $msgs = array();

function run($db, $name, $sql, &$ok, &$err, &$msgs) {
    try {
        $db->exec($sql);
        $ok++;
        $msgs[] = array('ok', $name);
    } catch (PDOException $e) {
        $err++;
        $msgs[] = array('err', $name, $e->getMessage());
    }
}

run($db, 'clienti', "CREATE TABLE IF NOT EXISTS clienti (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_id VARCHAR(20) UNIQUE NOT NULL,
  nume VARCHAR(255) NOT NULL,
  cui_cnp VARCHAR(30) DEFAULT '',
  telefon VARCHAR(30) DEFAULT '',
  email VARCHAR(100) DEFAULT '',
  adresa VARCHAR(500) DEFAULT '',
  oras VARCHAR(100) DEFAULT 'Brasov',
  judet VARCHAR(100) DEFAULT 'Brasov',
  persoana_contact VARCHAR(200) DEFAULT '',
  tip ENUM('Firma','Persoana','Institutie') DEFAULT 'Firma',
  note TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_nume (nume),
  INDEX idx_telefon (telefon),
  INDEX idx_cui (cui_cnp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $ok, $err, $msgs);

run($db, 'proiecte', "CREATE TABLE IF NOT EXISTS proiecte (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proiect_id VARCHAR(30) UNIQUE NOT NULL,
  client_id INT NOT NULL,
  serviciu ENUM('Supraveghere Video','Alarma','Detectie Incendiu','Control Acces','Electric','Automatizari','Retelistica','Complex') DEFAULT 'Supraveghere Video',
  obiectiv TEXT,
  status ENUM('Lead','Oferta','Contract','Proiectare','Executie','Receptie','Facturat','Mentenanta','Finalizat','Anulat') DEFAULT 'Lead',
  valoare_estimata DECIMAL(12,2) DEFAULT 0,
  valoare_contract DECIMAL(12,2) DEFAULT 0,
  responsabil VARCHAR(100) DEFAULT '',
  adresa_obiectiv VARCHAR(500) DEFAULT '',
  note TEXT,
  istoric_status TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (client_id) REFERENCES clienti(id) ON DELETE RESTRICT,
  INDEX idx_status (status),
  INDEX idx_client (client_id),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $ok, $err, $msgs);

run($db, 'oferte', "CREATE TABLE IF NOT EXISTS oferte (
  id INT AUTO_INCREMENT PRIMARY KEY,
  oferta_id VARCHAR(30) UNIQUE NOT NULL,
  proiect_id INT DEFAULT NULL,
  client_id INT DEFAULT NULL,
  titlu VARCHAR(500) DEFAULT '',
  data_oferta DATE,
  valabilitate VARCHAR(50) DEFAULT '4 zile',
  obiectiv VARCHAR(500) DEFAULT '',
  subtotal_echip DECIMAL(12,2) DEFAULT 0,
  subtotal_manop DECIMAL(12,2) DEFAULT 0,
  total_fara_tva DECIMAL(12,2) DEFAULT 0,
  tva DECIMAL(12,2) DEFAULT 0,
  total_cu_tva DECIMAL(12,2) DEFAULT 0,
  client_nume VARCHAR(255) DEFAULT '',
  client_cui VARCHAR(30) DEFAULT '',
  client_adresa VARCHAR(500) DEFAULT '',
  client_contact VARCHAR(200) DEFAULT '',
  status ENUM('Draft','Trimisa','Acceptata','Refuzata') DEFAULT 'Draft',
  pdf_path VARCHAR(500) DEFAULT '',
  xlsx_path VARCHAR(500) DEFAULT '',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (proiect_id) REFERENCES proiecte(id) ON DELETE SET NULL,
  FOREIGN KEY (client_id) REFERENCES clienti(id) ON DELETE SET NULL,
  INDEX idx_client (client_id),
  INDEX idx_proiect (proiect_id),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $ok, $err, $msgs);

run($db, 'oferta_linii', "CREATE TABLE IF NOT EXISTS oferta_linii (
  id INT AUTO_INCREMENT PRIMARY KEY,
  oferta_id INT NOT NULL,
  tip ENUM('echipament','manopera') DEFAULT 'echipament',
  denumire VARCHAR(500) NOT NULL,
  cod VARCHAR(100) DEFAULT '',
  um VARCHAR(20) DEFAULT 'buc.',
  cantitate DECIMAL(10,2) DEFAULT 1,
  pret_achizitie DECIMAL(10,2) DEFAULT 0,
  adaos_procent DECIMAL(5,2) DEFAULT 40,
  pret_vanzare DECIMAL(10,2) DEFAULT 0,
  valoare DECIMAL(12,2) DEFAULT 0,
  ordine INT DEFAULT 0,
  FOREIGN KEY (oferta_id) REFERENCES oferte(id) ON DELETE CASCADE,
  INDEX idx_oferta (oferta_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $ok, $err, $msgs);

run($db, 'contracte', "CREATE TABLE IF NOT EXISTS contracte (
  id INT AUTO_INCREMENT PRIMARY KEY,
  contract_id VARCHAR(30) UNIQUE NOT NULL,
  proiect_id INT NOT NULL,
  data_semnare DATE,
  valoare DECIMAL(12,2) DEFAULT 0,
  conditii_plata VARCHAR(500) DEFAULT '50% avans, 50% la PIF',
  pdf_path VARCHAR(500) DEFAULT '',
  status ENUM('Draft','Activ','Finalizat','Anulat') DEFAULT 'Draft',
  clauze TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (proiect_id) REFERENCES proiecte(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $ok, $err, $msgs);

run($db, 'proiectare', "CREATE TABLE IF NOT EXISTS proiectare (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proiect_id INT NOT NULL UNIQUE,
  aviz_isu ENUM('DA','NU','NA') DEFAULT 'NA',
  schema_path VARCHAR(500) DEFAULT '',
  dosar_path VARCHAR(500) DEFAULT '',
  termen DATE,
  status ENUM('Nepornit','In lucru','Finalizat') DEFAULT 'Nepornit',
  note TEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (proiect_id) REFERENCES proiecte(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $ok, $err, $msgs);

run($db, 'executie', "CREATE TABLE IF NOT EXISTS executie (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proiect_id INT NOT NULL UNIQUE,
  data_start DATE,
  data_finish DATE,
  ore_lucrate DECIMAL(8,1) DEFAULT 0,
  echipa VARCHAR(300) DEFAULT '',
  status ENUM('Planificat','In lucru','Finalizat') DEFAULT 'Planificat',
  raport TEXT,
  poze_json TEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (proiect_id) REFERENCES proiecte(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $ok, $err, $msgs);

run($db, 'receptie', "CREATE TABLE IF NOT EXISTS receptie (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proiect_id INT NOT NULL UNIQUE,
  data_receptie DATE,
  pv_path VARCHAR(500) DEFAULT '',
  garantie_luni INT DEFAULT 24,
  garantie_expira DATE,
  status ENUM('Programat','Efectuat') DEFAULT 'Programat',
  observatii TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (proiect_id) REFERENCES proiecte(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $ok, $err, $msgs);

run($db, 'facturi', "CREATE TABLE IF NOT EXISTS facturi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  factura_id VARCHAR(30) UNIQUE NOT NULL,
  proiect_id INT NOT NULL,
  data_emitere DATE,
  suma DECIMAL(12,2) DEFAULT 0,
  tip ENUM('Avans','Finala','Mentenanta','Interventie') DEFAULT 'Finala',
  status ENUM('Emisa','Incasata','Restanta','Anulata') DEFAULT 'Emisa',
  data_scadenta DATE,
  data_incasare DATE DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (proiect_id) REFERENCES proiecte(id) ON DELETE CASCADE,
  INDEX idx_status (status),
  INDEX idx_scadenta (data_scadenta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $ok, $err, $msgs);

run($db, 'mentenanta', "CREATE TABLE IF NOT EXISTS mentenanta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  proiect_id INT NOT NULL,
  contract_mnt_id VARCHAR(30) DEFAULT '',
  data_start DATE,
  data_expirare DATE,
  valoare_anuala DECIMAL(12,2) DEFAULT 0,
  status ENUM('Activ','Expirat','Anulat') DEFAULT 'Activ',
  interventii_json TEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (proiect_id) REFERENCES proiecte(id) ON DELETE CASCADE,
  INDEX idx_expirare (data_expirare)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $ok, $err, $msgs);

run($db, 'secvente', "CREATE TABLE IF NOT EXISTS secvente (
  cheie VARCHAR(50) PRIMARY KEY,
  valoare INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $ok, $err, $msgs);

run($db, 'secvente_data', "INSERT INTO secvente (cheie, valoare) VALUES
  ('client_seq', 0),
  ('proiect_seq', 0),
  ('oferta_seq', 244100),
  ('contract_seq', 0),
  ('factura_seq', 0),
  ('mentenanta_seq', 0)
ON DUPLICATE KEY UPDATE cheie=cheie", $ok, $err, $msgs);

run($db, 'v_proiecte_complete', "CREATE OR REPLACE VIEW v_proiecte_complete AS
SELECT 
  p.id, p.proiect_id, p.status, p.serviciu, p.obiectiv,
  p.valoare_estimata, p.valoare_contract, p.responsabil,
  p.adresa_obiectiv, p.note, p.created_at, p.updated_at,
  c.client_id, c.nume AS client_nume, c.cui_cnp, c.telefon, 
  c.email, c.adresa AS client_adresa, c.oras, c.persoana_contact
FROM proiecte p
JOIN clienti c ON p.client_id = c.id
ORDER BY p.created_at DESC", $ok, $err, $msgs);

run($db, 'v_oferte_complete', "CREATE OR REPLACE VIEW v_oferte_complete AS
SELECT 
  o.id, o.oferta_id, o.titlu, o.data_oferta, o.valabilitate,
  o.subtotal_echip, o.subtotal_manop, o.total_fara_tva, o.tva,
  o.total_cu_tva, o.oferta_id AS nr,
  COALESCE(c.nume, o.client_nume) AS client_nume,
  COALESCE(c.cui_cnp, o.client_cui) AS client_cui,
  COALESCE(c.telefon, '') AS client_telefon,
  COALESCE(c.email, '') AS client_email,
  COALESCE(c.adresa, o.client_adresa) AS client_adresa,
  COALESCE(c.persoana_contact, o.client_contact) AS client_contact,
  o.client_nume AS client_nume_manual,
  o.status AS oferta_status, o.pdf_path, o.xlsx_path,
  o.obiectiv, o.created_at,
  c.client_id AS crm_client_id,
  p.proiect_id, p.status AS proiect_status, p.serviciu
FROM oferte o
LEFT JOIN clienti c ON o.client_id = c.id
LEFT JOIN proiecte p ON o.proiect_id = p.id
ORDER BY o.created_at DESC", $ok, $err, $msgs);

$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

echo '<html><body style="font-family:system-ui;max-width:600px;margin:40px auto">';
echo '<h2 style="color:#16a34a">CSSI Portal v4.0 — Setup</h2>';
echo '<p><strong>' . $ok . '</strong> OK, <strong>' . $err . '</strong> erori</p>';
foreach ($msgs as $m) {
    if ($m[0] === 'ok') {
        echo '<div style="color:#16a34a;font-size:13px;margin:2px 0">&#10004; ' . htmlspecialchars($m[1]) . '</div>';
    } else {
        echo '<div style="color:#dc2626;font-size:13px;margin:2px 0">&#10008; ' . htmlspecialchars($m[1]) . ': ' . htmlspecialchars($m[2]) . '</div>';
    }
}
echo '<h3>Tabele (' . count($tables) . '):</h3><pre>';
foreach ($tables as $t) echo $t . "\n";
echo '</pre>';
echo '<p><a href="/admin/api.php?action=ping">Test API ping</a> | ';
echo '<a href="/admin/api.php?action=getClienti">Test Clienti</a> | ';
echo '<a href="/admin/api.php?action=getOferte">Test Oferte</a></p>';
echo '<p style="color:#dc2626"><strong>STERGE setup.php dupa instalare!</strong></p>';
echo '</body></html>';
