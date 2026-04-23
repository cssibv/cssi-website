<?php
// ============================================================
// CSSI Portal v4.0 — REST API -
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
date_default_timezone_set('Europe/Bucharest');

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

        // ════════════════════════════════════
        // ANAF CUI LOOKUP (proxy către registrul ANAF v9)
        // ════════════════════════════════════
        case 'lookupCUI':
            $cuiRaw = isset($_GET['cui']) ? $_GET['cui'] : (isset($data['cui']) ? $data['cui'] : '');
            $cui = preg_replace('/[^0-9]/', '', $cuiRaw);
            if (!$cui || strlen($cui) < 2 || strlen($cui) > 10) {
                jsonResponse(['success' => false, 'error' => 'CUI invalid (2-10 cifre)'], 400);
                break;
            }

            $postData = json_encode([['cui' => intval($cui), 'data' => date('Y-m-d')]]);
            // URL corect v9 (format nou: /api/PlatitorTvaRest/v9/tva)
            $ch = curl_init('https://webservicesp.anaf.ro/api/PlatitorTvaRest/v9/tva');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errMsg = curl_error($ch);
            curl_close($ch);

            if ($httpCode !== 200) {
                jsonResponse(['success' => false, 'error' => 'ANAF indisponibil (HTTP ' . $httpCode . ')' . ($errMsg ? ': ' . $errMsg : '')], 502);
                break;
            }

            $result = json_decode($response, true);
            if (!$result || !isset($result['found'])) {
                jsonResponse(['success' => false, 'error' => 'Răspuns invalid de la ANAF']);
                break;
            }
            if (empty($result['found'])) {
                jsonResponse(['success' => false, 'error' => 'CUI ' . $cui . ' nu a fost găsit în registrul ANAF']);
                break;
            }

            $firma = $result['found'][0];
            $dg = isset($firma['date_generale']) ? $firma['date_generale'] : [];
            $tva = isset($firma['inregistrare_scop_Tva']) ? $firma['inregistrare_scop_Tva'] : [];
            $adrSed = isset($firma['adresa_sediu_social']) ? $firma['adresa_sediu_social'] : [];
            $adrDom = isset($firma['adresa_domiciliu_fiscal']) ? $firma['adresa_domiciliu_fiscal'] : [];

            $fullAddr = isset($dg['adresa']) ? trim($dg['adresa']) : '';
            $oras = ''; $judet = ''; $stradaEtc = $fullAddr;

            // Date structurate v9 din adresa_sediu_social (prioritar) sau adresa_domiciliu_fiscal
            $src = !empty($adrSed) ? $adrSed : $adrDom;
            if (!empty($src)) {
                if (!empty($src['sdenumire_Localitate'])) $oras = trim($src['sdenumire_Localitate']);
                if (!empty($src['sdenumire_Judet'])) $judet = trim($src['sdenumire_Judet']);
                // Fallback pentru adresa_domiciliu_fiscal (prefix d)
                if (!$oras && !empty($src['ddenumire_Localitate'])) $oras = trim($src['ddenumire_Localitate']);
                if (!$judet && !empty($src['ddenumire_Judet'])) $judet = trim($src['ddenumire_Judet']);
                $strada = !empty($src['sdenumire_Strada']) ? trim($src['sdenumire_Strada']) : (!empty($src['ddenumire_Strada']) ? trim($src['ddenumire_Strada']) : '');
                $nr = !empty($src['snumar_Strada']) ? trim($src['snumar_Strada']) : (!empty($src['dnumar_Strada']) ? trim($src['dnumar_Strada']) : '');
                if ($strada) {
                    $stradaEtc = $strada . ($nr ? ' nr. ' . $nr : '');
                }
            }

            // Fallback final: parse textul brut dacă structuratele-s goale
            if (!$oras && $fullAddr) {
                if (preg_match('/(?:MUN\.?|ORA[SȘ]\.?|COM\.?|SAT)\s+([^,]+)/u', $fullAddr, $m)) {
                    $oras = trim($m[1]);
                }
            }
            if (!$judet && $fullAddr) {
                if (preg_match('/JUD\.?\s+([^,]+)/u', $fullAddr, $m)) {
                    $judet = trim($m[1]);
                }
            }

            $stareInreg = isset($dg['stare_inregistrare']) ? $dg['stare_inregistrare'] : '';
            jsonResponse(['success' => true, 'data' => [
                'cui' => isset($dg['cui']) ? (string)$dg['cui'] : $cui,
                'denumire' => isset($dg['denumire']) ? trim($dg['denumire']) : '',
                'nrRegCom' => isset($dg['nrRegCom']) ? trim($dg['nrRegCom']) : '',
                'adresa_completa' => $fullAddr,
                'adresa' => $stradaEtc,
                'oras' => $oras,
                'judet' => $judet,
                'cod_postal' => isset($dg['codPostal']) ? $dg['codPostal'] : (isset($dg['cod_postal']) ? $dg['cod_postal'] : ''),
                'telefon' => isset($dg['telefon']) ? $dg['telefon'] : '',
                'stare_inregistrare' => $stareInreg,
                'platitor_tva' => !empty($tva['scpTVA']),
                'data_inceput_tva' => isset($tva['data_inceput_ScpTVA']) ? $tva['data_inceput_ScpTVA'] : '',
                'radiat' => (stripos($stareInreg, 'RADIAT') !== false)
            ]]);
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

        case 'deleteProiect':
            $id = (isset($data['id']) ? $data['id'] : 0);
            if (!$id) { jsonResponse(['success' => false, 'error' => 'ID obligatoriu'], 400); break; }
            // Șterge proiectarea asociată
            $db->prepare("DELETE FROM proiectare WHERE proiect_id = ?")->execute([$id]);
            // Șterge notificările
            $stmtPid = $db->prepare("SELECT proiect_id FROM proiecte WHERE id = ?");
            $stmtPid->execute([$id]);
            $pidRow = $stmtPid->fetch();
            if ($pidRow) {
                $db->prepare("DELETE FROM notificari WHERE proiect_id = ?")->execute([$pidRow['proiect_id']]);
            }
            // Șterge proiectul
            $db->prepare("DELETE FROM proiecte WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true]);
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
            
            // Creează notificare
            $mesaj = '📌 ' . $row['proiect_id'] . ' (' . $row['client_nume'] . ') — ' . $oldStatus . ' → ' . $newStatus . ' (de ' . $user . ')';
            $db->prepare("INSERT INTO notificari (proiect_id, mesaj, tip, de_la, etapa_noua) VALUES (?,?,?,?,?)")
               ->execute([$row['id'], $mesaj, 'status_change', $user, $newStatus]);
            
            // Auto-setup Proiectare: termen +30 zile, data_start, status în lucru
            if ($newStatus === 'Proiectare') {
                $numId = $row['id'];
                // Verifică dacă există record proiectare
                $chkPr = $db->prepare("SELECT id FROM proiectare WHERE proiect_id = ?");
                $chkPr->execute([$numId]);
                if (!$chkPr->fetch()) {
                    // Determină serviciul pentru checklist dinamic
                    $srvS = $db->prepare("SELECT serviciu FROM proiecte WHERE id = ?");
                    $srvS->execute([$numId]);
                    $srvR = $srvS->fetch();
                    $srv = $srvR ? $srvR['serviciu'] : '';
                    $cl = [
                        ['id'=>'vizita_teren','label'=>'Vizită teren efectuată','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'schema_electrica','label'=>'Schemă electrică creată','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'plan_amplasare','label'=>'Plan amplasare echipamente','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'trasee_cabluri','label'=>'Trasee cabluri proiectate','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'necesar_materiale','label'=>'Necesar materiale verificat','done'=>false,'date'=>null,'user'=>null],
                    ];
                    if (in_array($srv, ['Supraveghere Video','Alarma','Complex'])) {
                        $cl[] = ['id'=>'aviz_igpr_depus','label'=>'👮 Aviz IGPR depus','done'=>false,'date'=>null,'user'=>null];
                        $cl[] = ['id'=>'aviz_igpr_obtinut','label'=>'👮 Aviz IGPR obținut','done'=>false,'date'=>null,'user'=>null];
                    }
                    if (in_array($srv, ['Detectie Incendiu','Complex'])) {
                        $cl[] = ['id'=>'aviz_isu_depus','label'=>'🛡️ Aviz ISU depus','done'=>false,'date'=>null,'user'=>null];
                        $cl[] = ['id'=>'aviz_isu_obtinut','label'=>'🛡️ Aviz ISU obținut','done'=>false,'date'=>null,'user'=>null];
                    }
                    $cl[] = ['id'=>'dosar_complet','label'=>'Dosar proiect complet','done'=>false,'date'=>null,'user'=>null];
                    $db->prepare("INSERT INTO proiectare (proiect_id, checklist_json, termen, data_start, status) VALUES (?, ?, DATE_ADD(CURDATE(), INTERVAL 30 DAY), CURDATE(), 'In lucru')")
                       ->execute([$numId, json_encode($cl)]);
                } else {
                    // Update termen + status dacă există deja
                    $db->prepare("UPDATE proiectare SET termen = COALESCE(termen, DATE_ADD(CURDATE(), INTERVAL 30 DAY)), data_start = COALESCE(data_start, CURDATE()), status = 'In lucru' WHERE proiect_id = ?")
                       ->execute([$numId]);
                }
            }
            
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
                $stmt = $db->prepare("SELECT proiect_id, client_id, total_cu_tva FROM oferte WHERE id = ?");
                $stmt->execute([$id]);
                $oferta = $stmt->fetch();
                
                // Determină proiect_id: direct din ofertă sau fallback prin client_id
                $pId = null;
                if ($oferta && $oferta['proiect_id']) {
                    $pId = $oferta['proiect_id'];
                } elseif ($oferta && $oferta['client_id']) {
                    // Fallback: caută proiectul prin client_id
                    $stmtF = $db->prepare("SELECT id FROM proiecte WHERE client_id = ? ORDER BY created_at DESC LIMIT 1");
                    $stmtF->execute([$oferta['client_id']]);
                    $projRow = $stmtF->fetch();
                    if ($projRow) {
                        $pId = $projRow['id'];
                        // Linkează oferta la proiect pentru viitor
                        $db->prepare("UPDATE oferte SET proiect_id = ? WHERE id = ?")->execute([$pId, $id]);
                    }
                }
                
                if ($pId) {
                    // Suma TUTUROR ofertelor acceptate pentru acest proiect (contract inglobat)
                    $stmtSum = $db->prepare("SELECT COALESCE(SUM(total_cu_tva), 0) AS total FROM oferte WHERE (proiect_id = ? OR (client_id = ? AND proiect_id IS NULL)) AND status = 'Acceptata'");
                    $stmtSum->execute([$pId, $oferta['client_id']]);
                    $sumRow = $stmtSum->fetch();
                    $totalContract = $sumRow ? $sumRow['total'] : $oferta['total_cu_tva'];
                    
                    $db->prepare("UPDATE proiecte SET status = 'Contract', valoare_contract = ? WHERE id = ?")->execute([
                        $totalContract, $pId
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
                $stmt = $db->prepare("SELECT o.proiect_id, o.client_id, p.proiect_id AS cod FROM oferte o LEFT JOIN proiecte p ON o.proiect_id = p.id WHERE o.id = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                // Fallback prin client_id dacă proiect_id e NULL
                if ($row && !$row['cod'] && $row['client_id']) {
                    $stmtF = $db->prepare("SELECT proiect_id FROM proiecte WHERE client_id = ? ORDER BY created_at DESC LIMIT 1");
                    $stmtF->execute([$row['client_id']]);
                    $projF = $stmtF->fetch();
                    if ($projF) $row['cod'] = $projF['proiect_id'];
                }
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
        // PROIECTARE
        // ══════════════════════════════════════
        case 'getProiectare':
            $pid = (isset($_GET['proiect_id']) ? $_GET['proiect_id'] : 0);
            if (!$pid) { jsonResponse(['success' => false, 'error' => 'proiect_id obligatoriu'], 400); break; }
            
            // Caută sau creează automat
            $stmt = $db->prepare("SELECT pr.*, p.proiect_id AS cod, p.serviciu, p.status AS proiect_status, p.valoare_contract, p.responsabil, p.adresa_obiectiv, p.obiectiv, p.note AS proiect_note, c.nume AS client_nume, c.telefon, c.email, c.persoana_contact, c.adresa AS client_adresa FROM proiectare pr JOIN proiecte p ON pr.proiect_id = p.id JOIN clienti c ON p.client_id = c.id WHERE pr.proiect_id = ? OR p.proiect_id = ?");
            $stmt->execute([$pid, $pid]);
            $row = $stmt->fetch();
            
            if (!$row) {
                // Auto-creează record proiectare
                $numericId = $pid;
                if (!is_numeric($pid)) {
                    $s = $db->prepare("SELECT id FROM proiecte WHERE proiect_id = ?");
                    $s->execute([$pid]);
                    $r = $s->fetch();
                    $numericId = $r ? $r['id'] : 0;
                }
                if ($numericId) {
                    // Determină serviciul pentru checklist dinamic
                    $srvStmt = $db->prepare("SELECT serviciu FROM proiecte WHERE id = ?");
                    $srvStmt->execute([$numericId]);
                    $srvRow = $srvStmt->fetch();
                    $serviciu = $srvRow ? $srvRow['serviciu'] : '';
                    
                    // Checklist de bază (pentru toate proiectele)
                    $checklist = [
                        ['id'=>'vizita_teren','label'=>'Vizită teren efectuată','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'schema_electrica','label'=>'Schemă electrică creată','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'plan_amplasare','label'=>'Plan amplasare echipamente','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'trasee_cabluri','label'=>'Trasee cabluri proiectate','done'=>false,'date'=>null,'user'=>null],
                        ['id'=>'necesar_materiale','label'=>'Necesar materiale verificat','done'=>false,'date'=>null,'user'=>null],
                    ];
                    
                    // IGPR: Supraveghere Video, Alarmă/Efracție, Complex
                    $needsIGPR = in_array($serviciu, ['Supraveghere Video','Alarma','Complex']);
                    if ($needsIGPR) {
                        $checklist[] = ['id'=>'aviz_igpr_depus','label'=>'👮 Aviz IGPR depus','done'=>false,'date'=>null,'user'=>null];
                        $checklist[] = ['id'=>'aviz_igpr_obtinut','label'=>'👮 Aviz IGPR obținut','done'=>false,'date'=>null,'user'=>null];
                    }
                    
                    // ISU: Detecție Incendiu, Complex
                    $needsISU = in_array($serviciu, ['Detectie Incendiu','Complex']);
                    if ($needsISU) {
                        $checklist[] = ['id'=>'aviz_isu_depus','label'=>'🛡️ Aviz ISU depus','done'=>false,'date'=>null,'user'=>null];
                        $checklist[] = ['id'=>'aviz_isu_obtinut','label'=>'🛡️ Aviz ISU obținut','done'=>false,'date'=>null,'user'=>null];
                    }
                    
                    // Final
                    $checklist[] = ['id'=>'dosar_complet','label'=>'Dosar proiect complet','done'=>false,'date'=>null,'user'=>null];
                    
                    $defaultChecklist = json_encode($checklist);
                    $db->prepare("INSERT IGNORE INTO proiectare (proiect_id, checklist_json) VALUES (?, ?)")->execute([$numericId, $defaultChecklist]);
                    // Re-fetch
                    $stmt->execute([$numericId, $pid]);
                    $row = $stmt->fetch();
                }
            }
            
            // Adaugă ofertele acceptate
            $oferte = [];
            if ($row) {
                $stmtO = $db->prepare("SELECT o.id, o.oferta_id, o.titlu, o.total_cu_tva, o.status FROM oferte o WHERE (o.proiect_id = ? OR o.client_id = (SELECT client_id FROM proiecte WHERE id = ?)) AND o.status = 'Acceptata' ORDER BY o.created_at");
                $stmtO->execute([$row['proiect_id'], $row['proiect_id']]);
                $oferteRaw = $stmtO->fetchAll();
                foreach ($oferteRaw as &$of) {
                    $stmtL = $db->prepare("SELECT denumire, cod, um, cantitate, pret_vanzare, valoare FROM oferta_linii WHERE oferta_id = ? AND tip = 'echipament' ORDER BY ordine");
                    $stmtL->execute([$of['id']]);
                    $of['echipamente'] = $stmtL->fetchAll();
                }
                $oferte = $oferteRaw;
            }
            
            jsonResponse(['success' => true, 'data' => $row, 'oferte' => $oferte]);
            break;

        case 'updateProiectare':
            $pid = (isset($data['proiect_id']) ? $data['proiect_id'] : 0);
            if (!$pid) { jsonResponse(['success' => false, 'error' => 'proiect_id obligatoriu'], 400); break; }
            $fields = [];
            $values = [];
            foreach (['aviz_isu','aviz_igpr','termen','status','note','proiectant','data_start','checklist_json','progres'] as $f) {
                if (isset($data[$f])) { $fields[] = "$f = ?"; $values[] = $data[$f]; }
            }
            if ($fields && $pid) {
                $values[] = $pid;
                $db->prepare("UPDATE proiectare SET " . implode(', ', $fields) . " WHERE proiect_id = ?")->execute($values);
                jsonResponse(['success' => true]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Lipsesc date'], 400);
            }
            break;

        case 'toggleChecklist':
            $pid = (isset($data['proiect_id']) ? $data['proiect_id'] : 0);
            $itemId = (isset($data['item_id']) ? $data['item_id'] : '');
            $done = isset($data['done']) ? $data['done'] : false;
            $user = (isset($data['user']) ? $data['user'] : 'Admin');
            if (!$pid || !$itemId) { jsonResponse(['success' => false, 'error' => 'Parametri lipsă'], 400); break; }
            
            $stmt = $db->prepare("SELECT checklist_json FROM proiectare WHERE proiect_id = ?");
            $stmt->execute([$pid]);
            $row = $stmt->fetch();
            if ($row) {
                $checklist = json_decode($row['checklist_json'] ?: '[]', true);
                $totalDone = 0;
                foreach ($checklist as &$item) {
                    if ($item['id'] === $itemId) {
                        $item['done'] = $done;
                        $item['date'] = $done ? date('Y-m-d H:i:s') : null;
                        $item['user'] = $done ? $user : null;
                    }
                    if ($item['done']) $totalDone++;
                }
                unset($item);
                $progres = count($checklist) > 0 ? round(($totalDone / count($checklist)) * 100) : 0;
                $db->prepare("UPDATE proiectare SET checklist_json = ?, progres = ? WHERE proiect_id = ?")->execute([json_encode($checklist), $progres, $pid]);
                jsonResponse(['success' => true, 'progres' => $progres, 'checklist' => $checklist]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Record negăsit'], 404);
            }
            break;

        case 'addJurnalEntry':
            $pid = (isset($data['proiect_id']) ? $data['proiect_id'] : 0);
            $text = (isset($data['text']) ? $data['text'] : '');
            $user = (isset($data['user']) ? $data['user'] : 'Admin');
            if (!$pid || !$text) { jsonResponse(['success' => false, 'error' => 'Parametri lipsă'], 400); break; }
            
            $stmt = $db->prepare("SELECT jurnal_json FROM proiectare WHERE proiect_id = ?");
            $stmt->execute([$pid]);
            $row = $stmt->fetch();
            if ($row) {
                $jurnal = json_decode($row['jurnal_json'] ?: '[]', true);
                array_unshift($jurnal, ['date' => date('Y-m-d H:i:s'), 'user' => $user, 'text' => $text]);
                $db->prepare("UPDATE proiectare SET jurnal_json = ? WHERE proiect_id = ?")->execute([json_encode($jurnal), $pid]);
                jsonResponse(['success' => true, 'jurnal' => $jurnal]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Record negăsit'], 404);
            }
            break;

        case 'getProiecteProiectare':
            $stmt = $db->query("SELECT p.id, p.proiect_id, p.status, p.valoare_contract, p.responsabil, p.serviciu, c.nume AS client_nume, c.telefon, pr.status AS pr_status, pr.progres, pr.termen, pr.proiectant FROM proiecte p JOIN clienti c ON p.client_id = c.id LEFT JOIN proiectare pr ON p.id = pr.proiect_id WHERE p.status IN ('Contract','Proiectare') ORDER BY FIELD(p.status,'Proiectare','Contract'), p.updated_at DESC");
            jsonResponse(['success' => true, 'data' => $stmt->fetchAll()]);
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

        // ══════════════════════════════════════
        // SOCIAL MEDIA
        // ══════════════════════════════════════
        case 'getSocialPosts':
            $brand = isset($_GET['brand']) ? $_GET['brand'] : '';
            $status = isset($_GET['status']) ? $_GET['status'] : '';
            $sql = "SELECT * FROM social_posts WHERE 1=1";
            $params = [];
            if ($brand) { $sql .= " AND brand = ?"; $params[] = $brand; }
            if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
            $sql .= " ORDER BY data_programare DESC, created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            foreach ($rows as &$r) {
                $r['platforme'] = json_decode($r['platforme'] ?: '[]');
                $r['external_ids'] = json_decode($r['external_ids'] ?: '{}');
                $r['analytics'] = json_decode($r['analytics'] ?: '{}');
            }
            unset($r);
            jsonResponse(['success' => true, 'data' => $rows]);
            break;

        case 'generateSocialImage':
            $prompt = isset($data['prompt']) ? trim($data['prompt']) : '';
            if (!$prompt) { jsonResponse(['success' => false, 'error' => 'Descrie imaginea dorită'], 400); break; }
            $uploadDir = __DIR__ . '/uploads/social/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $imgPrompt = urlencode($prompt . ', professional photography, high quality, social media post');
            $imgUrl = "https://image.pollinations.ai/prompt/{$imgPrompt}?width=1080&height=1080&seed=" . rand(1,999999) . '&nologo=true';
            
            $ch = curl_init($imgUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 60
            ]);
            $imgData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            
            if ($httpCode >= 200 && $httpCode < 300 && strlen($imgData) > 1000) {
                $ext = (strpos($contentType, 'png') !== false) ? 'png' : 'jpg';
                $newName = 'ai_' . date('Ymd_His') . '_' . uniqid() . '.' . $ext;
                file_put_contents($uploadDir . $newName, $imgData);
                jsonResponse(['success' => true, 'file' => [
                    'url' => '/admin/uploads/social/' . $newName,
                    'name' => 'AI Generated - ' . substr($prompt, 0, 30),
                    'size' => strlen($imgData),
                    'type' => 'image',
                    'ext' => $ext
                ]]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Nu s-a putut genera imaginea'], 500);
            }
            break;

        case 'uploadSocialMedia':
            // Upload fișiere media pentru social posts
            if (empty($_FILES['files'])) { jsonResponse(['success' => false, 'error' => 'Niciun fișier trimis'], 400); break; }
            $uploadDir = __DIR__ . '/uploads/social/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $allowed = ['jpg','jpeg','png','gif','webp','mp4','mov','avi','mkv'];
            $maxSize = 50 * 1024 * 1024; // 50MB
            $uploaded = [];
            $files = $_FILES['files'];
            $count = is_array($files['name']) ? count($files['name']) : 1;
            for ($i = 0; $i < $count; $i++) {
                $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                $tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $size = is_array($files['size']) ? $files['size'][$i] : $files['size'];
                $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];
                if ($error !== UPLOAD_ERR_OK) { continue; }
                if ($size > $maxSize) { continue; }
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) { continue; }
                $isVideo = in_array($ext, ['mp4','mov','avi','mkv']);
                $newName = date('Ymd_His') . '_' . uniqid() . '.' . $ext;
                $dest = $uploadDir . $newName;
                if (move_uploaded_file($tmp, $dest)) {
                    $uploaded[] = [
                        'url' => '/admin/uploads/social/' . $newName,
                        'name' => $name,
                        'size' => $size,
                        'type' => $isVideo ? 'video' : 'image',
                        'ext' => $ext
                    ];
                }
            }
            if (empty($uploaded)) { jsonResponse(['success' => false, 'error' => 'Niciun fișier valid uploadat'], 400); break; }
            jsonResponse(['success' => true, 'files' => $uploaded]);
            break;

        case 'saveSocialPost':
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            $brand = isset($data['brand']) ? $data['brand'] : 'cssi';
            $continut = isset($data['continut']) ? trim($data['continut']) : '';
            $platforme = isset($data['platforme']) ? json_encode($data['platforme']) : '[]';
            $tip = isset($data['tip_continut']) ? $data['tip_continut'] : 'Foto';
            $status = isset($data['status']) ? $data['status'] : 'Draft';
            $data_prog = !empty($data['data_programare']) ? $data['data_programare'] : null;
            $imagine = isset($data['imagine_url']) ? $data['imagine_url'] : '';
            $note = isset($data['note']) ? $data['note'] : '';
            $creat_de = isset($data['creat_de']) ? $data['creat_de'] : '';
            $media_json = isset($data['media_json']) ? json_encode($data['media_json']) : null;
            if (!$continut) { jsonResponse(['success' => false, 'error' => 'Conținutul e obligatoriu'], 400); break; }
            if ($id) {
                $stmt = $db->prepare("UPDATE social_posts SET brand=?, continut=?, platforme=?, tip_continut=?, status=?, data_programare=?, imagine_url=?, media_json=?, note=? WHERE id=?");
                $stmt->execute([$brand, $continut, $platforme, $tip, $status, $data_prog, $imagine, $media_json, $note, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO social_posts (brand, continut, platforme, tip_continut, status, data_programare, imagine_url, media_json, note, creat_de) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$brand, $continut, $platforme, $tip, $status, $data_prog, $imagine, $media_json, $note, $creat_de]);
                $id = $db->lastInsertId();
            }
            jsonResponse(['success' => true, 'id' => $id]);
            break;

        case 'deleteSocialPost':
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            if (!$id) { jsonResponse(['success' => false, 'error' => 'ID obligatoriu'], 400); break; }
            $db->prepare("DELETE FROM social_posts WHERE id = ?")->execute([$id]);
            jsonResponse(['success' => true]);
            break;

        case 'updateSocialStatus':
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            $status = isset($data['status']) ? $data['status'] : '';
            if (!$id || !$status) { jsonResponse(['success' => false, 'error' => 'ID și status obligatorii'], 400); break; }
            $db->prepare("UPDATE social_posts SET status = ? WHERE id = ?")->execute([$status, $id]);
            jsonResponse(['success' => true]);
            break;

        case 'publishSocialPost':
            // Publică via Zernio API
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            if (!$id) { jsonResponse(['success' => false, 'error' => 'ID obligatoriu'], 400); break; }
            $stmt = $db->prepare("SELECT * FROM social_posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch();
            if (!$post) { jsonResponse(['success' => false, 'error' => 'Post negăsit'], 404); break; }

            $platforme = json_decode($post['platforme'] ?: '[]', true);
            $zernioKey = 'sk_c7b7a4f08d5bab22497ab169e58313a02d6ef47ead1d2bfa39b5bd7237fd76c0';

            // Fetch connected accounts from Zernio
            $chAccounts = curl_init('https://zernio.com/api/v1/accounts');
            curl_setopt_array($chAccounts, [
                CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $zernioKey],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15
            ]);
            $accountsResp = json_decode(curl_exec($chAccounts), true);
            curl_close($chAccounts);
            $accountMap = [];
            if (!empty($accountsResp['accounts'])) {
                foreach ($accountsResp['accounts'] as $acc) {
                    if ($acc['isActive']) $accountMap[$acc['platform']] = $acc['_id'];
                }
            }

            $platMap = ['fb'=>'facebook','ig'=>'instagram','linkedin'=>'linkedin','yt'=>'youtube','tiktok'=>'tiktok','x'=>'twitter'];
            $zernioPlats = [];
            foreach ($platforme as $p) {
                $platName = isset($platMap[$p]) ? $platMap[$p] : $p;
                if (isset($accountMap[$platName])) {
                    $zernioPlats[] = ['platform' => $platName, 'accountId' => $accountMap[$platName]];
                }
            }
            if (empty($zernioPlats)) {
                jsonResponse(['success' => false, 'error' => 'Niciun cont Zernio conectat pentru platformele selectate. Conectate: ' . implode(', ', array_keys($accountMap))], 400);
                break;
            }

            $zernioPayload = [
                'content' => $post['continut'],
                'platforms' => $zernioPlats
            ];
            // Media as mediaItems [{type, url}]
            if (!empty($post['media_json'])) {
                $mediaFiles = json_decode($post['media_json'], true);
                if (is_array($mediaFiles) && count($mediaFiles)) {
                    $baseUrl = 'https://cssi.ro';
                    $zernioPayload['mediaItems'] = array_map(function($f) use ($baseUrl) {
                        return ['type' => $f['type'] ?: 'image', 'url' => $baseUrl . $f['url']];
                    }, $mediaFiles);
                }
            } elseif (!empty($post['imagine_url'])) {
                $imgUrl = $post['imagine_url'];
                if (strpos($imgUrl, 'http') !== 0) $imgUrl = 'https://cssi.ro' . $imgUrl;
                $zernioPayload['mediaItems'] = [['type' => 'image', 'url' => $imgUrl]];
            }
            // Schedule or publish now
            if ($post['data_programare'] && strtotime($post['data_programare']) > time()) {
                $zernioPayload['scheduledFor'] = date('c', strtotime($post['data_programare']));
                $zernioPayload['timezone'] = 'Europe/Bucharest';
            } else {
                $zernioPayload['publishNow'] = true;
            }

            $ch = curl_init('https://zernio.com/api/v1/posts');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $zernioKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode($zernioPayload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);
            if ($httpCode >= 200 && $httpCode < 300) {
                $newStatus = (!empty($zernioPayload['scheduledFor'])) ? 'Programat' : 'Publicat';
                $db->prepare("UPDATE social_posts SET status = ?, external_ids = ? WHERE id = ?")
                   ->execute([$newStatus, $response, $id]);
                jsonResponse(['success' => true, 'status' => $newStatus, 'zernio' => $result]);
            } else {
                $db->prepare("UPDATE social_posts SET status = 'Eroare' WHERE id = ?")->execute([$id]);
                jsonResponse(['success' => false, 'error' => 'Zernio error: ' . ($response ?: 'timeout'), 'httpCode' => $httpCode], 500);
            }
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