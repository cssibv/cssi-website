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
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$data = getPostData();

try {
    $db = getDB();
    
    switch ($action) {
        // ══════════════════════════════════════
        // CLIENTI
        // ══════════════════════════════════════
        case 'getClienti':
            $search = $_GET['search'] ?? '';
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
            $id = $_GET['id'] ?? 0;
            $stmt = $db->prepare("SELECT * FROM clienti WHERE id = ? OR client_id = ?");
            $stmt->execute([$id, $id]);
            $row = $stmt->fetch();
            jsonResponse($row ? ['success' => true, 'data' => $row] : ['success' => false, 'error' => 'Client negasit']);
            break;

        case 'createClient':
            $clientId = nextId('client_seq', 'CLI-', 4);
            $stmt = $db->prepare("INSERT INTO clienti (client_id, nume, cui_cnp, telefon, email, adresa, oras, judet, persoana_contact, tip, note) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $clientId,
                $data['nume'] ?? '',
                $data['cui_cnp'] ?? '',
                $data['telefon'] ?? '',
                $data['email'] ?? '',
                $data['adresa'] ?? '',
                $data['oras'] ?? 'Brașov',
                $data['judet'] ?? 'Brașov',
                $data['persoana_contact'] ?? '',
                $data['tip'] ?? 'Firma',
                $data['note'] ?? ''
            ]);
            $insertId = $db->lastInsertId();
            jsonResponse(['success' => true, 'id' => $insertId, 'client_id' => $clientId]);
            break;

        case 'updateClient':
            $id = $data['id'] ?? 0;
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
            $id = $data['id'] ?? 0;
            $db->prepare("DELETE FROM clienti WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        // ══════════════════════════════════════
        // PROIECTE
        // ══════════════════════════════════════
        case 'getProiecte':
            $status = $_GET['status'] ?? '';
            $search = $_GET['search'] ?? '';
            $sql = "SELECT * FROM v_proiecte_complete WHERE 1=1";
            $params = [];
            if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
            if ($search) { $sql .= " AND (client_nume LIKE ? OR proiect_id LIKE ? OR obiectiv LIKE ?)"; $s = "%$search%"; $params = array_merge($params, [$s,$s,$s]); }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'getProiect':
            $id = $_GET['id'] ?? 0;
            $stmt = $db->prepare("SELECT * FROM v_proiecte_complete WHERE id = ? OR proiect_id = ?");
            $stmt->execute([$id, $id]);
            jsonResponse(['success' => true, 'data' => $stmt->fetch()]);
            break;

        case 'createProiect':
            $clientId = $data['client_id'] ?? 0;
            if (!$clientId) { jsonResponse(['success' => false, 'error' => 'client_id obligatoriu'], 400); break; }
            
            $year = date('Y');
            $proiectId = nextId('proiect_seq', "CSSI-$year-", 4);
            $istoric = json_encode([['status' => 'Lead', 'data' => date('Y-m-d H:i:s'), 'user' => $data['responsabil'] ?? 'Admin']]);
            
            $stmt = $db->prepare("INSERT INTO proiecte (proiect_id, client_id, serviciu, obiectiv, status, valoare_estimata, responsabil, adresa_obiectiv, note, istoric_status) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $proiectId,
                $clientId,
                $data['serviciu'] ?? 'Supraveghere Video',
                $data['obiectiv'] ?? '',
                $data['status'] ?? 'Lead',
                $data['valoare_estimata'] ?? 0,
                $data['responsabil'] ?? '',
                $data['adresa_obiectiv'] ?? '',
                $data['note'] ?? '',
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
            $id = $data['id'] ?? 0;
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
            $id = $data['id'] ?? $_GET['id'] ?? '';
            $newStatus = $data['status'] ?? $_GET['status'] ?? '';
            $user = $data['user'] ?? 'Admin';
            if (!$id || !$newStatus) { jsonResponse(['success' => false, 'error' => 'id si status obligatorii'], 400); break; }
            
            // Citeste istoricul curent
            $stmt = $db->prepare("SELECT istoric_status FROM proiecte WHERE id = ? OR proiect_id = ?");
            $stmt->execute([$id, $id]);
            $row = $stmt->fetch();
            $istoric = $row ? json_decode($row['istoric_status'] ?: '[]', true) : [];
            $istoric[] = ['status' => $newStatus, 'data' => date('Y-m-d H:i:s'), 'user' => $user];
            
            $db->prepare("UPDATE proiecte SET status = ?, istoric_status = ? WHERE id = ? OR proiect_id = ?")
               ->execute([$newStatus, json_encode($istoric), $id, $id]);
            jsonResponse(['success' => true]);
            break;

        // ══════════════════════════════════════
        // OFERTE
        // ══════════════════════════════════════
        case 'getOferte':
            $search = $_GET['search'] ?? '';
            $sql = "SELECT * FROM v_oferte_complete";
            $params = [];
            if ($search) {
                $sql .= " WHERE client_nume LIKE ? OR oferta_id LIKE ? OR obiectiv LIKE ?";
                $s = "%$search%";
                $params = [$s, $s, $s];
            }
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $oferte = $stmt->fetchAll();
            
            // Adauga linii pt fiecare oferta
            foreach ($oferte as &$o) {
                $stmtL = $db->prepare("SELECT * FROM oferta_linii WHERE oferta_id = ? ORDER BY tip, ordine");
                $stmtL->execute([$o['id']]);
                $linii = $stmtL->fetchAll();
                $o['lines'] = array_filter($linii, fn($l) => $l['tip'] === 'echipament');
                $o['labor'] = array_filter($linii, fn($l) => $l['tip'] === 'manopera');
                $o['lines'] = array_values($o['lines']);
                $o['labor'] = array_values($o['labor']);
            }
            unset($o);
            jsonResponse(['success' => true, 'data' => $oferte]);
            break;

        case 'getOferta':
            $id = $_GET['id'] ?? 0;
            $stmt = $db->prepare("SELECT * FROM v_oferte_complete WHERE id = ? OR oferta_id = ?");
            $stmt->execute([$id, $id]);
            $o = $stmt->fetch();
            if ($o) {
                $stmtL = $db->prepare("SELECT * FROM oferta_linii WHERE oferta_id = ? ORDER BY tip, ordine");
                $stmtL->execute([$o['id']]);
                $linii = $stmtL->fetchAll();
                $o['lines'] = array_values(array_filter($linii, fn($l) => $l['tip'] === 'echipament'));
                $o['labor'] = array_values(array_filter($linii, fn($l) => $l['tip'] === 'manopera'));
            }
            jsonResponse(['success' => true, 'data' => $o]);
            break;

        case 'saveOferta':
            $db->beginTransaction();
            try {
                $isUpdate = !empty($data['oferta_db_id']);
                
                if ($isUpdate) {
                    // Update existing
                    $ofertaDbId = $data['oferta_db_id'];
                    $db->prepare("UPDATE oferte SET titlu=?, data_oferta=?, valabilitate=?, obiectiv=?, client_id=?, proiect_id=?, subtotal_echip=?, subtotal_manop=?, total_fara_tva=?, tva=?, total_cu_tva=?, client_nume=?, client_cui=?, client_adresa=?, client_contact=?, status=? WHERE id=?")
                       ->execute([
                           $data['titlu'] ?? '',
                           $data['data'] ?? date('Y-m-d'),
                           $data['valab'] ?? '4 zile',
                           $data['obiectiv'] ?? '',
                           $data['client_db_id'] ?: null,
                           $data['proiect_db_id'] ?: null,
                           $data['subtotalEchip'] ?? 0,
                           $data['subtotalManop'] ?? 0,
                           $data['totalNet'] ?? 0,
                           $data['tva'] ?? 0,
                           $data['totalBrut'] ?? 0,
                           $data['client'] ?? '',
                           $data['cui'] ?? '',
                           $data['adresa'] ?? '',
                           $data['contact'] ?? '',
                           $data['oferta_status'] ?? 'Draft',
                           $ofertaDbId
                       ]);
                    // Sterge linii vechi
                    $db->prepare("DELETE FROM oferta_linii WHERE oferta_id = ?")->execute([$ofertaDbId]);
                } else {
                    // Insert new
                    $ofertaId = $data['nr'] ?? nextId('oferta_seq', 'OF-', 6);
                    $stmt = $db->prepare("INSERT INTO oferte (oferta_id, titlu, data_oferta, valabilitate, obiectiv, client_id, proiect_id, subtotal_echip, subtotal_manop, total_fara_tva, tva, total_cu_tva, client_nume, client_cui, client_adresa, client_contact, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $stmt->execute([
                        $ofertaId,
                        $data['titlu'] ?? '',
                        $data['data'] ?? date('Y-m-d'),
                        $data['valab'] ?? '4 zile',
                        $data['obiectiv'] ?? '',
                        $data['client_db_id'] ?: null,
                        $data['proiect_db_id'] ?: null,
                        $data['subtotalEchip'] ?? 0,
                        $data['subtotalManop'] ?? 0,
                        $data['totalNet'] ?? 0,
                        $data['tva'] ?? 0,
                        $data['totalBrut'] ?? 0,
                        $data['client'] ?? '',
                        $data['cui'] ?? '',
                        $data['adresa'] ?? '',
                        $data['contact'] ?? '',
                        $data['oferta_status'] ?? 'Draft'
                    ]);
                    $ofertaDbId = $db->lastInsertId();
                }
                
                // Insert linii echipamente
                $stmtLine = $db->prepare("INSERT INTO oferta_linii (oferta_id, tip, denumire, cod, um, cantitate, pret_achizitie, adaos_procent, pret_vanzare, valoare, ordine) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                
                $lines = $data['lines'] ?? [];
                foreach ($lines as $i => $l) {
                    if (empty($l['name'])) continue;
                    $pv = ($l['pAchiz'] ?? 0) * (1 + ($l['adaos'] ?? 40) / 100);
                    $val = ($l['cant'] ?? 0) * $pv;
                    $stmtLine->execute([
                        $ofertaDbId, 'echipament', $l['name'], $l['code'] ?? '', $l['um'] ?? 'buc.',
                        $l['cant'] ?? 0, $l['pAchiz'] ?? 0, $l['adaos'] ?? 40, round($pv, 2), round($val, 2), $i
                    ]);
                }
                
                // Insert linii manopera
                $labor = $data['labor'] ?? [];
                foreach ($labor as $i => $l) {
                    $c = floatval($l['cant'] ?? 0);
                    $p = floatval($l['price'] ?? 0);
                    if (!$c || empty($l['name'])) continue;
                    $stmtLine->execute([
                        $ofertaDbId, 'manopera', $l['name'], '', $l['um'] ?? 'ore',
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
            $id = $data['id'] ?? 0;
            $db->prepare("DELETE FROM oferte WHERE id = ? OR oferta_id = ?")->execute([$id, $id]);
            jsonResponse(['success' => true]);
            break;

        case 'updateOfertaStatus':
            $id = $data['id'] ?? 0;
            $status = $data['status'] ?? '';
            $db->prepare("UPDATE oferte SET status = ? WHERE id = ?")->execute([$status, $id]);
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
        // HEALTH CHECK
        // ══════════════════════════════════════
        case 'ping':
            jsonResponse(['success' => true, 'message' => 'CSSI Portal API v4.0', 'time' => date('Y-m-d H:i:s'), 'db' => 'MySQL OK']);
            break;

        default:
            jsonResponse(['success' => false, 'error' => 'Actiune necunoscuta: ' . $action], 400);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
}
