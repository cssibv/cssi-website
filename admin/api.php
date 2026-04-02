<?php
// ============================================================
// CSSI Portal v4.0 — REST API
// ============================================================
// Endpoint unic: /admin/api.php?action=...
// GET  = citire date
// POST = creare/modificare/stergere
// ============================================================

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/db.php';

$action = (isset($_GET['action']) ? $_GET['action'] : ((isset($_POST['action']) ? $_POST['action'] : '')));
$data = getPostData();

try {
    $db = getDB();
    
    switch ($action) {
        // ══════════════════════════════════════
        // CLIENTI
        // ══════════════════════════════════════
        case 'getClienti':
            $search = (isset($_GET['search']) ? $_GET['search'] : '');
            $sql = "SELECT * FROM clienti";
            $params = [];
            if ($search) {
                $sql .= " WHERE nume LIKE ? OR telefon LIKE ? OR cui_cnp LIKE ? OR email LIKE ?";
                $s = "%$search%";
                $params = [$s, $s, $s, $s];
            }
            $sql .= " ORDER BY created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'getClient':
            $id = (isset($_GET['id']) ? $_GET['id'] : 0);
            $stmt = $db->prepare("SELECT * FROM clienti WHERE id = ? OR client_id = ?");
            $stmt->execute([$id, $id]);
            $row = $stmt->fetch();
            jsonResponse($row ? ['success' => true, 'data' => $row] : ['success' => false, 'error' => 'Client negasit']);
            break;

        case 'createClient':
            $nume = isset($data['nume']) ? trim($data['nume']) : '';
            $cui = isset($data['cui_cnp']) ? trim($data['cui_cnp']) : '';
            
            // Verifică dacă clientul există deja (după CUI sau nume exact)
            $existing = null;
            if ($cui) {
                $stmt = $db->prepare("SELECT id, client_id FROM clienti WHERE cui_cnp = ? LIMIT 1");
                $stmt->execute([$cui]);
                $existing = $stmt->fetch();
            }
            if (!$existing && $nume) {
                $stmt = $db->prepare("SELECT id, client_id FROM clienti WHERE LOWER(TRIM(nume)) = LOWER(?) LIMIT 1");
                $stmt->execute([$nume]);
                $existing = $stmt->fetch();
            }
            
            if ($existing) {
                // Client existent — actualizează datele dacă sunt noi
                $updates = [];
                $vals = [];
                foreach (['telefon','email','adresa','oras','persoana_contact'] as $f) {
                    if (!empty($data[$f])) {
                        $updates[] = "$f = CASE WHEN $f = '' OR $f IS NULL THEN ? ELSE $f END";
                        $vals[] = $data[$f];
                    }
                }
                if ($updates) {
                    $vals[] = $existing['id'];
                    $db->prepare("UPDATE clienti SET " . implode(', ', $updates) . " WHERE id = ?")->execute($vals);
                }
                jsonResponse(['success' => true, 'id' => $existing['id'], 'client_id' => $existing['client_id'], 'existing' => true]);
            } else {
                // Client nou
                $clientId = nextId('client_seq', 'CLI-', 4);
                $stmt = $db->prepare("INSERT INTO clienti (client_id, nume, cui_cnp, telefon, email, adresa, oras, judet, persoana_contact, tip, note) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([
                    $clientId, $nume, $cui,
                    (isset($data['telefon']) ? $data['telefon'] : ''),
                    (isset($data['email']) ? $data['email'] : ''),
                    (isset($data['adresa']) ? $data['adresa'] : ''),
                    (isset($data['oras']) ? $data['oras'] : 'Brașov'),
                    (isset($data['judet']) ? $data['judet'] : 'Brașov'),
                    (isset($data['persoana_contact']) ? $data['persoana_contact'] : ''),
                    (isset($data['tip']) ? $data['tip'] : 'Firma'),
                    (isset($data['note']) ? $data['note'] : '')]);
                $insertId = $db->lastInsertId();
                jsonResponse(['success' => true, 'id' => $insertId, 'client_id' => $clientId, 'existing' => false]);
            }
            break;

        case 'updateClient':
            $id = (isset($data['id']) ? $data['id'] : 0);
            $fields = [];
            $values = [];
            foreach (['nume','cui_cnp','telefon','email','adresa','oras','judet','persoana_contact','tip','note'] as $f) {
                if (isset($data[$f])) {
                    $fields[] = "$f = ?";
                    $values[] = $data[$f];
                }
            }
            if ($fields && $id) {
                $values[] = $id;
                $db->prepare("UPDATE clienti SET " . implode(', ', $fields) . " WHERE id = ?")->execute($values);
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Lipsesc date'], 400);
            }
            break;

        case 'deleteClient':
            $id = (isset($data['id']) ? $data['id'] : 0);
            $db->prepare("DELETE FROM clienti WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        // ══════════════════════════════════════
        // PROIECTE
        // ══════════════════════════════════════
        case 'getProiecte':
            $status = (isset($_GET['status']) ? $_GET['status'] : '');
            $search = (isset($_GET['search']) ? $_GET['search'] : '');
            $sql = "SELECT v.*, (SELECT client_id FROM proiecte WHERE id = v.id) AS client_db_id FROM v_proiecte_complete v WHERE 1=1";
            $params = [];
            if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
            if ($search) { $sql .= " AND (client_nume LIKE ? OR proiect_id LIKE ? OR obiectiv LIKE ?)"; $s = "%$search%"; $params = array_merge($params, [$s,$s,$s]); }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'getProiect':
            $id = (isset($_GET['id']) ? $_GET['id'] : 0);
            $stmt = $db->prepare("SELECT * FROM v_proiecte_complete WHERE id = ? OR proiect_id = ?");
            $stmt->execute([$id, $id]);
            jsonResponse(['success' => true, 'data' => $stmt->fetch()]);
            break;

        case 'createProiect':
            $clientId = (isset($data['client_id']) ? $data['client_id'] : 0);
            if (!$clientId) { jsonResponse(['success' => false, 'error' => 'client_id obligatoriu'], 400); break; }
            
            $year = date('Y');
            $proiectId = nextId('proiect_seq', "CSSI-$year-", 4);
            $istoric = json_encode([['status' => 'Lead', 'data' => date('Y-m-d H:i:s'), 'user' => (isset($data['responsabil']) ? $data['responsabil'] : 'Admin')]]);
            
            $stmt = $db->prepare("INSERT INTO proiecte (proiect_id, client_id, serviciu, obiectiv, status, valoare_estimata, responsabil, adresa_obiectiv, note, istoric_status) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $proiectId,
                $clientId,
                (isset($data['serviciu']) ? $data['serviciu'] : 'Supraveghere Video'),
                (isset($data['obiectiv']) ? $data['obiectiv'] : ''),
                (isset($data['status']) ? $data['status'] : 'Lead'),
                (isset($data['valoare_estimata']) ? $data['valoare_estimata'] : 0),
                (isset($data['responsabil']) ? $data['responsabil'] : ''),
                (isset($data['adresa_obiectiv']) ? $data['adresa_obiectiv'] : ''),
                (isset($data['note']) ? $data['note'] : ''),
                $istoric
            ]);
            
            // Creare directoare uploads pt proiect
            $projDir = PROIECTE_DIR . $proiectId . '/';
            foreach (['oferte', 'contract', 'proiectare', 'executie', 'receptie', 'facturi'] as $sub) {
                @mkdir($projDir . $sub, 0755, true);
            }
            
            jsonResponse(['success' => true, 'id' => $db->lastInsertId(), 'proiect_id' => $proiectId]);
            break;

        case 'updateProiect':
            $id = (isset($data['id']) ? $data['id'] : 0);
            $fields = [];
            $values = [];
            foreach (['serviciu','obiectiv','status','valoare_estimata','valoare_contract','responsabil','adresa_obiectiv','note'] as $f) {
                if (isset($data[$f])) {
                    $fields[] = "$f = ?";
                    $values[] = $data[$f];
                }
            }
            if ($fields && $id) {
                $values[] = $id;
                $db->prepare("UPDATE proiecte SET " . implode(', ', $fields) . " WHERE id = ? OR proiect_id = ?")->execute(array_merge($values, [$id]));
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Lipsesc date'], 400);
            }
            break;

        case 'updateStatus':
            $id = (isset($data['id']) ? $data['id'] : (isset($_GET['id']) ? $_GET['id'] : ''));
            $newStatus = (isset($data['status']) ? $data['status'] : (isset($_GET['status']) ? $_GET['status'] : ''));
            $user = (isset($data['user']) ? $data['user'] : 'Admin');
            if (!$id || !$newStatus) { jsonResponse(['success' => false, 'error' => 'id si status obligatorii'], 400); break; }
            
            // Citeste proiectul curent
            $stmt = $db->prepare("SELECT p.id, p.proiect_id, p.status, p.istoric_status, c.nume AS client_nume FROM proiecte p JOIN clienti c ON p.client_id = c.id WHERE p.id = ? OR p.proiect_id = ?");
            $stmt->execute([$id, $id]);
            $row = $stmt->fetch();
            if (!$row) { jsonResponse(['success' => false, 'error' => 'Proiect negăsit'], 404); break; }
            
            $oldStatus = $row['status'];
            $istoric = json_decode($row['istoric_status'] ?: '[]', true);
            $istoric[] = ['status' => $newStatus, 'data' => date('Y-m-d H:i:s'), 'user' => $user];
            
            // Update proiect — resetează preluat_de la schimbarea statusului
            $db->prepare("UPDATE proiecte SET status = ?, istoric_status = ?, preluat_de = NULL, preluat_la = NULL WHERE id = ? OR proiect_id = ?")
               ->execute([$newStatus, json_encode($istoric), $id, $id]);
            
            // Creează notificare pentru toți utilizatorii
            $mesaj = '📌 ' . $row['proiect_id'] . ' (' . $row['client_nume'] . ') — ' . $oldStatus . ' → ' . $newStatus . ' (de ' . $user . ')';
            $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la, etapa_noua) VALUES (?,?,?,?,?)")
               ->execute([$row['id'], $mesaj, 'status_change', $user, $newStatus]);
            
            jsonResponse(['success' => true, 'notificare' => $mesaj]);
            break;

        // ══════════════════════════════════════
        // OFERTE
        // ══════════════════════════════════════
        case 'getOferte':
            $search = (isset($_GET['search']) ? $_GET['search'] : '');
            $clientId = (isset($_GET['client_id']) ? $_GET['client_id'] : '');
            $proiectId = (isset($_GET['proiect_id']) ? $_GET['proiect_id'] : '');
            $sql = "SELECT vc.*, o2.client_id AS client_db_id, o2.proiect_id AS proiect_db_id, o2.motiv_respingere, o2.data_decizie, o2.decis_de FROM v_oferte_complete vc JOIN oferte o2 ON vc.id = o2.id WHERE 1=1";
            $params = [];
            if ($clientId) {
                $sql .= " AND o2.client_id = ?";
                $params[] = $clientId;
            }
            if ($proiectId) {
                $sql .= " AND o2.proiect_id = ?";
                $params[] = $proiectId;
            }
            if ($search) {
                $sql .= " AND (vc.client_nume LIKE ? OR vc.oferta_id LIKE ? OR vc.obiectiv LIKE ?)";
                $s = "%$search%";
                $params = array_merge($params, [$s, $s, $s]);
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $oferte = $stmt->fetchAll();
            
            // Adauga linii pt fiecare oferta
            foreach ($oferte as &$o) {
                $stmtL = $db->prepare("SELECT * FROM oferta_linii WHERE oferta_id = ? ORDER BY tip, ordine");
                $stmtL->execute([$o['id']]);
                $linii = $stmtL->fetchAll();
                $o['lines'] = array_filter($linii, function($l) { return $l['tip'] === 'echipament'; });
                $o['labor'] = array_filter($linii, function($l) { return $l['tip'] === 'manopera'; });
                $o['lines'] = array_values($o['lines']);
                $o['labor'] = array_values($o['labor']);
            }
            unset($o);
            jsonResponse(['success' => true, 'data' => $oferte]);
            break;

        case 'getOferta':
            $id = (isset($_GET['id']) ? $_GET['id'] : 0);
            $stmt = $db->prepare("SELECT * FROM v_oferte_complete WHERE id = ? OR oferta_id = ?");
            $stmt->execute([$id, $id]);
            $o = $stmt->fetch();
            if ($o) {
                $stmtL = $db->prepare("SELECT * FROM oferta_linii WHERE oferta_id = ? ORDER BY tip, ordine");
                $stmtL->execute([$o['id']]);
                $linii = $stmtL->fetchAll();
                $o['lines'] = array_values(array_filter($linii, function($l) { return $l['tip'] === 'echipament'; }));
                $o['labor'] = array_values(array_filter($linii, function($l) { return $l['tip'] === 'manopera'; }));
            }
            jsonResponse(['success' => true, 'data' => $o]);
            break;

        case 'saveOferta':
            // Detectează update: prin oferta_db_id SAU prin nr existent
            $isUpdate = !empty($data['oferta_db_id']);
            if (!$isUpdate && !empty($data['nr'])) {
                // Verifică dacă există deja o ofertă cu acest nr (oferta_id)
                $chk = $db->prepare("SELECT id FROM oferte WHERE oferta_id = ? LIMIT 1");
                $chk->execute([$data['nr']]);
                $existing = $chk->fetch();
                if ($existing) {
                    $isUpdate = true;
                    $data['oferta_db_id'] = $existing['id'];
                }
            }
            $preGeneratedId = null;
            if (!$isUpdate) {
                $preGeneratedId = (!empty($data['nr']) ? $data['nr'] : nextId('oferta_seq', '', 0));
            }
            $db->beginTransaction();
            try {
                if ($isUpdate) {
                    // Update existing
                    $ofertaDbId = $data['oferta_db_id'];
                    // Validare FK: client_id si proiect_id trebuie sa existe sau sa fie NULL
                    $clientIdVal = null;
                    if (!empty($data['client_db_id'])) {
                        $chkC = $db->prepare("SELECT id FROM clienti WHERE id = ? LIMIT 1");
                        $chkC->execute([$data['client_db_id']]);
                        if ($chkC->fetch()) $clientIdVal = $data['client_db_id'];
                    }
                    $proiectIdVal = null;
                    if (!empty($data['proiect_db_id'])) {
                        $chkP = $db->prepare("SELECT id FROM proiecte WHERE id = ? LIMIT 1");
                        $chkP->execute([$data['proiect_db_id']]);
                        if ($chkP->fetch()) $proiectIdVal = $data['proiect_db_id'];
                    }
                    $db->prepare("UPDATE oferte SET titlu=?, data_oferta=?, valabilitate=?, obiectiv=?, client_id=?, proiect_id=?, subtotal_echip=?, subtotal_manop=?, total_fara_tva=?, tva=?, total_cu_tva=?, client_nume=?, client_cui=?, client_adresa=?, client_contact=?, status=? WHERE id=?")
                       ->execute([
                           (isset($data['titlu']) ? $data['titlu'] : ''),
                           (isset($data['data']) ? $data['data'] : date('Y-m-d')),
                           (isset($data['valab']) ? $data['valab'] : '4 zile'),
                           (isset($data['obiectiv']) ? $data['obiectiv'] : ''),
                           $clientIdVal,
                           $proiectIdVal,
                           (isset($data['subtotalEchip']) ? $data['subtotalEchip'] : 0),
                           (isset($data['subtotalManop']) ? $data['subtotalManop'] : 0),
                           (isset($data['totalNet']) ? $data['totalNet'] : 0),
                           (isset($data['tva']) ? $data['tva'] : 0),
                           (isset($data['totalBrut']) ? $data['totalBrut'] : 0),
                           (isset($data['client']) ? $data['client'] : ''),
                           (isset($data['cui']) ? $data['cui'] : ''),
                           (isset($data['adresa']) ? $data['adresa'] : ''),
                           (isset($data['contact']) ? $data['contact'] : ''),
                           (isset($data['oferta_status']) ? $data['oferta_status'] : 'Draft'),
                           $ofertaDbId
                       ]);
                    // Sterge linii vechi
                    $db->prepare("DELETE FROM oferta_linii WHERE oferta_id = ?")->execute([$ofertaDbId]);
                } else {
                    // Insert new
                    $ofertaId = $preGeneratedId;
                    // Regenerează titlul cu nr-ul corect
                    $client = (isset($data['client']) ? $data['client'] : '');
                    $obiectiv = (isset($data['obiectiv']) ? $data['obiectiv'] : '');
                    $dataOf = (isset($data['data']) ? $data['data'] : date('Y-m-d'));
                    $dataParts = explode('-', $dataOf);
                    $dataFmt = (isset($dataParts[2]) ? $dataParts[2].'.'.$dataParts[1].'.'.$dataParts[0] : $dataOf);
                    $titlu = 'Deviz ' . $client . ($obiectiv ? ' ' . $obiectiv : '') . ' ser.BV Nr. ' . $ofertaId . ' din ' . $dataFmt;
                    $stmt = $db->prepare("INSERT INTO oferte (oferta_id, titlu, data_oferta, valabilitate, obiectiv, client_id, proiect_id, subtotal_echip, subtotal_manop, total_fara_tva, tva, total_cu_tva, client_nume, client_cui, client_adresa, client_contact, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    // Validare FK pt INSERT
                    $clientIdValI = null;
                    if (!empty($data['client_db_id'])) {
                        $chkCI = $db->prepare("SELECT id FROM clienti WHERE id = ? LIMIT 1");
                        $chkCI->execute([$data['client_db_id']]);
                        if ($chkCI->fetch()) $clientIdValI = $data['client_db_id'];
                    }
                    $proiectIdValI = null;
                    if (!empty($data['proiect_db_id'])) {
                        $chkPI = $db->prepare("SELECT id FROM proiecte WHERE id = ? LIMIT 1");
                        $chkPI->execute([$data['proiect_db_id']]);
                        if ($chkPI->fetch()) $proiectIdValI = $data['proiect_db_id'];
                    }
                    $stmt->execute([
                        $ofertaId,
                        $titlu,
                        (isset($data['data']) ? $data['data'] : date('Y-m-d')),
                        (isset($data['valab']) ? $data['valab'] : '4 zile'),
                        (isset($data['obiectiv']) ? $data['obiectiv'] : ''),
                        $clientIdValI,
                        $proiectIdValI,
                        (isset($data['subtotalEchip']) ? $data['subtotalEchip'] : 0),
                        (isset($data['subtotalManop']) ? $data['subtotalManop'] : 0),
                        (isset($data['totalNet']) ? $data['totalNet'] : 0),
                        (isset($data['tva']) ? $data['tva'] : 0),
                        (isset($data['totalBrut']) ? $data['totalBrut'] : 0),
                        (isset($data['client']) ? $data['client'] : ''),
                        (isset($data['cui']) ? $data['cui'] : ''),
                        (isset($data['adresa']) ? $data['adresa'] : ''),
                        (isset($data['contact']) ? $data['contact'] : ''),
                        (isset($data['oferta_status']) ? $data['oferta_status'] : 'Draft')]);
                    $ofertaDbId = $db->lastInsertId();
                }
                
                // Insert linii echipamente
                $stmtLine = $db->prepare("INSERT INTO oferta_linii (oferta_id, tip, denumire, cod, um, cantitate, pret_achizitie, adaos_procent, pret_vanzare, valoare, ordine) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                
                $lines = (isset($data['lines']) ? $data['lines'] : []);
                foreach ($lines as $i => $l) {
                    if (empty($l['name'])) continue;
                    $pv = ((isset($l['pAchiz']) ? $l['pAchiz'] : 0)) * (1 + ((isset($l['adaos']) ? $l['adaos'] : 40)) / 100);
                    $val = ((isset($l['cant']) ? $l['cant'] : 0)) * $pv;
                    $stmtLine->execute([
                        $ofertaDbId, 'echipament', $l['name'], (isset($l['code']) ? $l['code'] : ''), (isset($l['um']) ? $l['um'] : 'buc.'),
                        (isset($l['cant']) ? $l['cant'] : 0), (isset($l['pAchiz']) ? $l['pAchiz'] : 0), (isset($l['adaos']) ? $l['adaos'] : 40), round($pv, 2), round($val, 2), $i
                    ]);
                }
                
                // Insert linii manopera
                $labor = (isset($data['labor']) ? $data['labor'] : []);
                foreach ($labor as $i => $l) {
                    $c = floatval((isset($l['cant']) ? $l['cant'] : 0));
                    $p = floatval((isset($l['price']) ? $l['price'] : 0));
                    if (!$c || empty($l['name'])) continue;
                    $stmtLine->execute([
                        $ofertaDbId, 'manopera', $l['name'], '', (isset($l['um']) ? $l['um'] : 'ore'),
                        $c, $p, 0, $p, round($c * $p, 2), 100 + $i
                    ]);
                }
                
                $db->commit();
                
                // Returneaza oferta completa
                $stmt = $db->prepare("SELECT * FROM oferte WHERE id = ?");
                $stmt->execute([$ofertaDbId]);
                $saved = $stmt->fetch();
                
                jsonResponse(['success' => true, 'id' => $ofertaDbId, 'oferta_id' => $saved['oferta_id']]);
            } catch (Exception $e) {
                $db->rollBack();
                jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
            }
            break;

        case 'deleteOferta':
            $id = (isset($data['id']) ? $data['id'] : 0);
            $db->prepare("DELETE FROM oferte WHERE id = ? OR oferta_id = ?")->execute([$id, $id]);
            jsonResponse(['success' => true]);
            break;

        case 'updateOfertaStatus':
            $id = (isset($data['id']) ? $data['id'] : 0);
            $status = (isset($data['status']) ? $data['status'] : '');
            $motiv = (isset($data['motiv_respingere']) ? $data['motiv_respingere'] : '');
            $user = (isset($data['user']) ? $data['user'] : 'Admin');
            
            // Update oferta status + motiv + data decizie
            $db->prepare("UPDATE oferte SET status = ?, motiv_respingere = ?, data_decizie = NOW(), decis_de = ? WHERE id = ?")->execute([$status, $motiv, $user, $id]);
            
            // Dacă oferta e Acceptată → schimbă proiectul în Contract automat
            if ($status === 'Acceptata') {
                $stmt = $db->prepare("SELECT proiect_id, total_cu_tva FROM oferte WHERE id = ?");
                $stmt->execute([$id]);
                $oferta = $stmt->fetch();
                if ($oferta && $oferta['proiect_id']) {
                    $pId = $oferta['proiect_id'];
                    // Update proiect status + valoare contract
                    $db->prepare("UPDATE proiecte SET status = 'Contract', valoare_contract = ? WHERE id = ?")->execute([
                        $oferta['total_cu_tva'], $pId
                    ]);
                    // Adaugă în istoric
                    $stmtP = $db->prepare("SELECT proiect_id, istoric_status FROM proiecte WHERE id = ?");
                    $stmtP->execute([$pId]);
                    $proj = $stmtP->fetch();
                    if ($proj) {
                        $istoric = json_decode($proj['istoric_status'] ?: '[]', true);
                        $istoric[] = ['status' => 'Contract', 'data' => date('Y-m-d H:i:s'), 'user' => $user, 'nota' => 'Ofertă acceptată'];
                        $db->prepare("UPDATE proiecte SET istoric_status = ? WHERE id = ?")->execute([json_encode($istoric), $pId]);
                        
                        // Notificare
                        $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la, etapa_noua) VALUES (?,?,?,?,?)")->execute([
                            $proj['proiect_id'],
                            '✅ Ofertă acceptată → Proiect ' . $proj['proiect_id'] . ' trecut în Contract',
                            'status',
                            $user,
                            'Contract'
                        ]);
                    }
                }
            }
            
            // Dacă oferta e Refuzată → notificare
            if ($status === 'Refuzata') {
                $stmt = $db->prepare("SELECT o.proiect_id, p.proiect_id AS cod FROM oferte o LEFT JOIN proiecte p ON o.proiect_id = p.id WHERE o.id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if ($row && $row['cod']) {
                    $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la) VALUES (?,?,?,?)")->execute([
                        $row['cod'],
                        '❌ Ofertă refuzată — Motiv: ' . ($motiv ?: 'nespecificat'),
                        'alerta',
                        $user
                    ]);
                }
            }
            
            jsonResponse(['success' => true]);
            break;

        // ══════════════════════════════════════
        // DASHBOARD / STATS
        // ══════════════════════════════════════
        case 'getDashboard':
            $stats = [];
            // Total clienti
            $stats['totalClienti'] = $db->query("SELECT COUNT(*) FROM clienti")->fetchColumn();
            // Proiecte pe status
            $stmt = $db->query("SELECT status, COUNT(*) as cnt FROM proiecte GROUP BY status");
            $stats['proiecteStatus'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $stats['totalProiecte'] = array_sum($stats['proiecteStatus']);
            // Valoare pipeline
            $stats['valoarePipeline'] = $db->query("SELECT COALESCE(SUM(valoare_estimata),0) FROM proiecte WHERE status NOT IN ('Finalizat','Anulat')")->fetchColumn();
            // Oferte luna curenta
            $stats['oferteLuna'] = $db->query("SELECT COUNT(*) FROM oferte WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())")->fetchColumn();
            // Facturi restante
            $stats['facturiRestante'] = $db->query("SELECT COALESCE(SUM(suma),0) FROM facturi WHERE status = 'Restanta'")->fetchColumn();
            
            jsonResponse(['success' => true, 'data' => $stats]);
            break;

        // ══════════════════════════════════════
        // NEXT ID (util pt frontend)
        // ══════════════════════════════════════
        case 'nextOfertaId':
            $stmt = $db->query("SELECT valoare FROM secvente WHERE cheie = 'oferta_seq'");
            $val = $stmt->fetchColumn();
            jsonResponse(['success' => true, 'next' => intval($val) + 1]);
            break;

        // ══════════════════════════════════════
        // ACTIVITATE RECENTA — feed unificat
        // ══════════════════════════════════════
        case 'getActivitateRecenta':
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 15;
            $activitate = [];

            // Oferte recente
            $stmt = $db->query("SELECT o.id, o.oferta_id, o.client_nume, o.total_cu_tva, o.obiectiv, o.status, o.created_at FROM oferte o ORDER BY o.created_at DESC LIMIT 20");
            foreach ($stmt->fetchAll() as $r) {
                $val = number_format($r['total_cu_tva'], 0, ',', '.');
                $activitate[] = [
                    'text' => '📋 Ofertă ' . $r['oferta_id'] . ' — ' . $r['client_nume'] . ($r['obiectiv'] ? ' (' . $r['obiectiv'] . ')' : '') . ', ' . $val . ' RON',
                    'color' => $r['status'] === 'Acceptata' ? 'var(--green)' : ($r['status'] === 'Refuzata' ? 'var(--red)' : 'var(--blue)'),
                    'time' => $r['created_at'],
                    'type' => 'oferta'
                ];
            }

            // Proiecte recente (create sau cu status schimbat)
            $stmt = $db->query("SELECT p.proiect_id, p.status, p.serviciu, p.obiectiv, p.updated_at, p.created_at, c.nume AS client_nume FROM proiecte p JOIN clienti c ON p.client_id = c.id ORDER BY p.updated_at DESC LIMIT 20");
            foreach ($stmt->fetchAll() as $r) {
                $isNew = (strtotime($r['updated_at']) - strtotime($r['created_at'])) < 60;
                if ($isNew) {
                    $txt = '🆕 Proiect nou: ' . $r['proiect_id'] . ' — ' . $r['client_nume'] . ' (' . $r['serviciu'] . ')';
                } else {
                    $txt = '🔄 ' . $r['proiect_id'] . ' — ' . $r['client_nume'] . ' → status: ' . $r['status'];
                }
                $statusColors = ['Lead'=>'var(--blue)','Oferta'=>'var(--purple)','Contract'=>'var(--teal)','Proiectare'=>'var(--orange)','Executie'=>'var(--green)','Receptie'=>'var(--green)','Facturat'=>'var(--red)','Mentenanta'=>'var(--teal)','Finalizat'=>'var(--green)','Anulat'=>'var(--gray)'];
                $activitate[] = [
                    'text' => $txt,
                    'color' => isset($statusColors[$r['status']]) ? $statusColors[$r['status']] : 'var(--gray)',
                    'time' => $r['updated_at'],
                    'type' => 'proiect'
                ];
            }

            // Clienti noi
            $stmt = $db->query("SELECT c.client_id, c.nume, c.tip, c.oras, c.created_at FROM clienti c ORDER BY c.created_at DESC LIMIT 10");
            foreach ($stmt->fetchAll() as $r) {
                $activitate[] = [
                    'text' => '👤 Client nou: ' . $r['nume'] . ($r['oras'] ? ' — ' . $r['oras'] : '') . ' (' . $r['tip'] . ')',
                    'color' => 'var(--teal)',
                    'time' => $r['created_at'],
                    'type' => 'client'
                ];
            }

            // Sortare descrescator dupa timp
            usort($activitate, function($a, $b) { return strtotime($b['time']) - strtotime($a['time']); });
            $activitate = array_slice($activitate, 0, $limit);

            // Formatare timp relativ
            $now = time();
            foreach ($activitate as &$a) {
                $diff = $now - strtotime($a['time']);
                if ($diff < 60) $a['timeLabel'] = 'Acum';
                elseif ($diff < 3600) $a['timeLabel'] = floor($diff/60) . ' min';
                elseif ($diff < 86400) $a['timeLabel'] = floor($diff/3600) . 'h';
                elseif ($diff < 172800) $a['timeLabel'] = 'Ieri';
                elseif ($diff < 604800) $a['timeLabel'] = floor($diff/86400) . ' zile';
                else $a['timeLabel'] = date('d.m.Y', strtotime($a['time']));
            }
            unset($a);

            jsonResponse(['success' => true, 'data' => $activitate]);
            break;

        // ══════════════════════════════════════
        // NOTIFICĂRI
        // ══════════════════════════════════════
        case 'getNotificari':
            $user = isset($_GET['user']) ? strtolower($_GET['user']) : '';
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 20;
            $cititCol = '';
            if (in_array($user, ['mihai','roxana','valentin','cristina'])) {
                $cititCol = 'citit_' . $user;
            }
            
            $stmt = $db->prepare("SELECT n.*, p.proiect_id AS cod_proiect, p.status AS status_proiect, p.preluat_de 
                FROM notificari n 
                LEFT JOIN proiecte p ON n.proiect_id = p.id 
                ORDER BY n.created_at DESC LIMIT ?");
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $notifs = $stmt->fetchAll();
            
            $unread = 0;
            foreach ($notifs as &$n) {
                $n['citit'] = ($cititCol && isset($n[$cititCol])) ? (bool)$n[$cititCol] : false;
                if (!$n['citit']) $unread++;
            }
            unset($n);
            
            jsonResponse(['success' => true, 'data' => $notifs, 'unread' => $unread]);
            break;

        case 'markNotificareRead':
            $nid = isset($data['id']) ? $data['id'] : '';
            $user = isset($data['user']) ? strtolower($data['user']) : '';
            if (!$nid || !in_array($user, ['mihai','roxana','valentin','cristina'])) {
                jsonResponse(['success' => false, 'error' => 'Parametri lipsă'], 400); break;
            }
            $col = 'citit_' . $user;
            $db->prepare("UPDATE notificari SET $col = 1 WHERE id = ?")->execute([$nid]);
            jsonResponse(['success' => true]);
            break;

        case 'markAllRead':
            $user = isset($data['user']) ? strtolower($data['user']) : '';
            if (!in_array($user, ['mihai','roxana','valentin','cristina'])) {
                jsonResponse(['success' => false, 'error' => 'User invalid'], 400); break;
            }
            $col = 'citit_' . $user;
            $db->exec("UPDATE notificari SET $col = 1 WHERE $col = 0");
            jsonResponse(['success' => true]);
            break;

        case 'preiaProiect':
            $pid = isset($data['proiect_id']) ? $data['proiect_id'] : '';
            $user = isset($data['user']) ? $data['user'] : '';
            if (!$pid || !$user) { jsonResponse(['success' => false, 'error' => 'Parametri lipsă'], 400); break; }
            
            $db->prepare("UPDATE proiecte SET preluat_de = ?, preluat_la = NOW() WHERE id = ? OR proiect_id = ?")
               ->execute([$user, $pid, $pid]);
            
            // Notificare
            $stmt = $db->prepare("SELECT p.proiect_id, p.status, c.nume FROM proiecte p JOIN clienti c ON p.client_id = c.id WHERE p.id = ? OR p.proiect_id = ?");
            $stmt->execute([$pid, $pid]);
            $p = $stmt->fetch();
            if ($p) {
                $mesaj = '✅ ' . $user . ' a preluat proiectul ' . $p['proiect_id'] . ' (' . $p['nume'] . ') — etapa: ' . $p['status'];
                $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la, etapa_noua, preluat_de, preluat_la) VALUES (?,?,?,?,?,?,NOW())")
                   ->execute([$p['proiect_id'], $mesaj, 'assignment', $user, $p['status'], $user]);
            }
            jsonResponse(['success' => true]);
            break;

        case 'getProiecteNeprelate':
            $stmt = $db->query("SELECT p.id, p.proiect_id, p.status, p.preluat_de, p.updated_at, c.nume AS client_nume 
                FROM proiecte p JOIN clienti c ON p.client_id = c.id 
                WHERE p.preluat_de IS NULL AND p.status NOT IN ('Finalizat','Anulat','Lead') 
                ORDER BY p.updated_at DESC");
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        // ══════════════════════════════════════
        // HEALTH CHECK
        // ══════════════════════════════════════
        // ══════════════════════════════════════
        // CONFIGURARE UTILIZATORI (din MySQL)
        // ══════════════════════════════════════
        case 'getUserConfig':
            $uid = isset($_GET['user_id']) ? $_GET['user_id'] : '';
            if ($uid) {
                $stmt = $db->prepare("SELECT * FROM user_config WHERE user_id = ?");
                $stmt->execute([$uid]);
                $row = $stmt->fetch();
                if ($row) { $row['modules'] = json_decode($row['modules'] ?: '[]'); $row['stages'] = json_decode($row['stages'] ?: '[]'); $row['primary_stages'] = json_decode($row['primary_stages'] ?: '[]'); }
                jsonResponse(['success' => true, 'data' => $row ?: null]);
            } else {
                $stmt = $db->query("SELECT * FROM user_config ORDER BY FIELD(user_id,'admin','mihai','roxana','valentin','cristina')");
                $rows = $stmt->fetchAll();
                foreach ($rows as &$r) { $r['modules'] = json_decode($r['modules'] ?: '[]'); $r['stages'] = json_decode($r['stages'] ?: '[]'); $r['primary_stages'] = json_decode($r['primary_stages'] ?: '[]'); }
                unset($r);
                jsonResponse(['success' => true, 'data' => $rows]);
            }
            break;

        case 'saveUserConfig':
            $uid = isset($data['user_id']) ? $data['user_id'] : '';
            if (!$uid) { jsonResponse(['success' => false, 'error' => 'user_id obligatoriu'], 400); break; }
            $modules = isset($data['modules']) ? json_encode($data['modules']) : '[]';
            $stages = isset($data['stages']) ? json_encode($data['stages']) : '[]';
            $primary = isset($data['primary_stages']) ? json_encode($data['primary_stages']) : '[]';
            $focus = isset($data['focus']) ? $data['focus'] : '';
            $stmt = $db->prepare("INSERT INTO user_config (user_id, modules, stages, primary_stages, focus) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE modules=VALUES(modules), stages=VALUES(stages), primary_stages=VALUES(primary_stages), focus=VALUES(focus)");
            $stmt->execute([$uid, $modules, $stages, $primary, $focus]);
            jsonResponse(['success' => true]);
            break;

        case 'ping':
            jsonResponse(['success' => true, 'message' => 'CSSI Portal API v4.0', 'time' => date('Y-m-d H:i:s'), 'db' => 'MySQL OK']);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Actiune necunoscuta: ' . $action], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}