<?php
/**
 * CSSI — Helpers pentru raport zilnic email
 * Folosit din cron-raport-zilnic.php (CLI/HTTP) și din api.php (preview/send manual)
 */

if (!function_exists('cssiCollectRaportData')) {

function cssiCollectRaportData($db) {
    try { $db->exec("UPDATE oferte SET status='Expirata' WHERE status IN ('Draft','Trimisa','In_discutie') AND expires_at IS NOT NULL AND expires_at < CURDATE() AND archived_at IS NULL"); } catch (Exception $e) {}

    $sqlP = "SELECT
        SUM(CASE WHEN status IN ('Lead','Oferta','Contract','Proiectare','Executie','Receptie','Interventie') THEN 1 ELSE 0 END) AS active,
        SUM(CASE WHEN status='Lead' THEN 1 ELSE 0 END) AS leaduri,
        SUM(CASE WHEN status='Oferta' THEN 1 ELSE 0 END) AS oferta,
        SUM(CASE WHEN status='Contract' THEN 1 ELSE 0 END) AS contract,
        SUM(CASE WHEN status='Proiectare' THEN 1 ELSE 0 END) AS proiectare,
        SUM(CASE WHEN status IN ('Executie','Interventie') THEN 1 ELSE 0 END) AS executie,
        SUM(CASE WHEN status='Receptie' THEN 1 ELSE 0 END) AS receptie,
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN status NOT IN ('Anulat') THEN valoare_contract ELSE 0 END), 0) AS contract_value
        FROM proiecte";
    $kpiP = $db->query($sqlP)->fetch() ?: [];

    $monthStart = date('Y-m-01');
    $sqlO = "SELECT
        SUM(CASE WHEN status IN ('Trimisa','In_discutie') THEN 1 ELSE 0 END) AS asteptare_n,
        COALESCE(SUM(CASE WHEN status IN ('Trimisa','In_discutie') THEN total_cu_tva ELSE 0 END), 0) AS asteptare_val,
        SUM(CASE WHEN status='Acceptata' AND data_decizie >= ? THEN 1 ELSE 0 END) AS acc_luna_n,
        COALESCE(SUM(CASE WHEN status='Acceptata' AND data_decizie >= ? THEN total_cu_tva ELSE 0 END), 0) AS acc_luna_val,
        SUM(CASE WHEN status='Expirata' AND archived_at IS NULL THEN 1 ELSE 0 END) AS expirate
        FROM oferte WHERE archived_at IS NULL";
    $stmtO = $db->prepare($sqlO);
    $stmtO->execute([$monthStart, $monthStart]);
    $kpiO = $stmtO->fetch() ?: [];

    try {
        $stmtL = $db->prepare("SELECT p.proiect_id, c.nume FROM proiecte p LEFT JOIN clienti c ON p.client_id = c.id WHERE p.status = 'Lead' AND p.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY p.created_at DESC LIMIT 10");
        $stmtL->execute();
        $leaduri24h = $stmtL->fetchAll();
    } catch (Exception $e) { $leaduri24h = []; }

    try {
        $stmtPg = $db->prepare("SELECT pg.id, pg.ora_start, pg.durata_ore, pg.obiectiv, pg.note, p.proiect_id, c.nume AS client_nume, c.telefon FROM executie_programari pg LEFT JOIN proiecte p ON pg.proiect_id = p.id LEFT JOIN clienti c ON p.client_id = c.id WHERE pg.data_programata = CURDATE() AND pg.status NOT IN ('Anulat','Finalizat') ORDER BY pg.ora_start");
        $stmtPg->execute();
        $programariAzi = $stmtPg->fetchAll();
        if ($programariAzi) {
            $ids = array_column($programariAzi, 'id');
            $place = implode(',', array_fill(0, count($ids), '?'));
            $stmtA = $db->prepare("SELECT programare_id, user_id FROM executie_atribuiri WHERE programare_id IN ($place)");
            $stmtA->execute($ids);
            $atribMap = [];
            foreach ($stmtA->fetchAll() as $a) { $atribMap[$a['programare_id']][] = $a['user_id']; }
            foreach ($programariAzi as &$p) { $p['echipa'] = $atribMap[$p['id']] ?? []; }
            unset($p);
        }
    } catch (Exception $e) { $programariAzi = []; }

    $mentScadente = [];
    try {
        $stmtM = $db->prepare("SELECT m.*, c.nume AS client_nume, p.proiect_id FROM mentenanta m LEFT JOIN clienti c ON m.client_id = c.id LEFT JOIN proiecte p ON m.proiect_id = p.id WHERE m.status = 'Activ' AND m.data_scadenta BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY) ORDER BY m.data_scadenta LIMIT 20");
        $stmtM->execute();
        $mentScadente = $stmtM->fetchAll();
    } catch (Exception $e) {}

    try {
        $stmtCo = $db->prepare("SELECT c.contract_nr, cl.nume AS client_nume, c.created_at FROM contracte c LEFT JOIN clienti cl ON c.client_id = cl.id WHERE c.status IN ('asteapta_date','') AND c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY c.created_at DESC LIMIT 10");
        $stmtCo->execute();
        $contracteAsteapta = $stmtCo->fetchAll();
    } catch (Exception $e) { $contracteAsteapta = []; }

    return [
        'kpi' => [
            'proiecte_active'  => intval($kpiP['active'] ?? 0),
            'proiecte_total'   => intval($kpiP['total'] ?? 0),
            'leaduri'          => intval($kpiP['leaduri'] ?? 0),
            'in_executie'      => intval($kpiP['executie'] ?? 0),
            'la_proiectare'    => intval($kpiP['proiectare'] ?? 0),
            'la_contract'      => intval($kpiP['contract'] ?? 0),
            'pipeline_oferte_n'  => intval($kpiO['asteptare_n'] ?? 0),
            'pipeline_oferte_v'  => floatval($kpiO['asteptare_val'] ?? 0),
            'acceptate_luna_n'   => intval($kpiO['acc_luna_n'] ?? 0),
            'acceptate_luna_v'   => floatval($kpiO['acc_luna_val'] ?? 0),
            'expirate_oferte'    => intval($kpiO['expirate'] ?? 0),
        ],
        'leaduri_24h'        => $leaduri24h,
        'programari_azi'     => $programariAzi,
        'mentenante_scadente'=> $mentScadente,
        'contracte_asteapta' => $contracteAsteapta,
    ];
}

function cssiFmtRON($n) { return number_format($n, 0, ',', '.'); }

function cssiRenderRaportText($d) {
    $k = $d['kpi'];
    $b  = "📊 RAPORT ZILNIC CSSI — " . date('d.m.Y') . "\n";
    $b .= "═══════════════════════════════════\n\n";
    $b .= "📋 Proiecte active:       {$k['proiecte_active']} / {$k['proiecte_total']}\n";
    $b .= "📞 Lead-uri noi:          {$k['leaduri']}\n";
    $b .= "📐 La proiectare:         {$k['la_proiectare']}\n";
    $b .= "🔧 În execuție:           {$k['in_executie']}\n";
    $b .= "💰 Pipeline oferte:       {$k['pipeline_oferte_n']} oferte (" . cssiFmtRON($k['pipeline_oferte_v']) . " RON)\n";
    $b .= "🤝 Acceptate luna asta:   {$k['acceptate_luna_n']} (" . cssiFmtRON($k['acceptate_luna_v']) . " RON)\n";
    if ($k['expirate_oferte']) $b .= "⏱️ Oferte expirate:       {$k['expirate_oferte']}\n";
    $b .= "\n";

    if (!empty($d['programari_azi'])) {
        $b .= "📅 PROGRAMĂRI AZI (" . count($d['programari_azi']) . "):\n";
        foreach ($d['programari_azi'] as $p) {
            $b .= "  • " . substr($p['ora_start'], 0, 5) . " — " . ($p['client_nume'] ?? '?') . " (" . $p['durata_ore'] . "h)";
            if (!empty($p['echipa'])) $b .= " · echipa: " . implode(', ', $p['echipa']);
            $b .= "\n";
        }
        $b .= "\n";
    }

    if (!empty($d['contracte_asteapta'])) {
        $b .= "📄 CONTRACTE — așteaptă date client:\n";
        foreach ($d['contracte_asteapta'] as $c) {
            $b .= "  • " . $c['contract_nr'] . " — " . ($c['client_nume'] ?? '?') . "\n";
        }
        $b .= "\n";
    }

    if (!empty($d['leaduri_24h'])) {
        $b .= "📞 LEAD-URI NOI (ultimele 24h):\n";
        foreach ($d['leaduri_24h'] as $l) {
            $b .= "  • " . $l['proiect_id'] . " — " . ($l['nume'] ?? '?') . "\n";
        }
        $b .= "\n";
    }

    if (!empty($d['mentenante_scadente'])) {
        $b .= "🔄 MENTENANȚE SCADENTE (urm. 14 zile):\n";
        foreach ($d['mentenante_scadente'] as $m) {
            $zile = max(0, ceil((strtotime($m['data_scadenta']) - time()) / 86400));
            $b .= "  • " . ($m['client_nume'] ?? '?') . " — în {$zile} zile (" . $m['data_scadenta'] . ")\n";
        }
        $b .= "\n";
    }

    $b .= "─────────────────────────────────\n";
    $b .= "Portal CSSI · https://cssi.ro/admin\n";
    return $b;
}

function _kpiCellHtml($icon, $label, $value, $color) {
    return '<td style="padding:10px;width:50%"><div style="background:#fafbfc;border-left:4px solid ' . $color . ';padding:14px 16px;border-radius:8px"><div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:0.5px">' . $icon . ' ' . $label . '</div><div style="font-size:18px;font-weight:800;color:#0f172a;margin-top:4px;line-height:1.2">' . $value . '</div></div></td>';
}

function cssiRenderRaportHtml($d) {
    $k = $d['kpi'];
    $h  = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>';
    $h .= '<body style="font-family:Arial,sans-serif;color:#0f172a;background:#f1f5f9;margin:0;padding:20px">';
    $h .= '<table style="max-width:680px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.06)" cellpadding="0" cellspacing="0" width="100%">';

    $h .= '<tr><td style="background:linear-gradient(135deg,#1e3a8a,#3b82f6);color:#fff;padding:24px 30px">';
    $h .= '<h1 style="margin:0;font-size:22px;font-weight:800">📊 Raport Zilnic CSSI</h1>';
    $h .= '<div style="font-size:13px;opacity:0.9;margin-top:4px">' . date('l, d F Y') . '</div>';
    $h .= '</td></tr>';

    $h .= '<tr><td style="padding:24px 30px 10px"><table width="100%" cellpadding="10" cellspacing="0">';
    $h .= '<tr>' . _kpiCellHtml('📋', 'Proiecte active', "{$k['proiecte_active']} / {$k['proiecte_total']}", '#3b82f6');
    $h .=        _kpiCellHtml('🤝', 'Acceptate luna asta', $k['acceptate_luna_n'] . ' · ' . cssiFmtRON($k['acceptate_luna_v']) . ' RON', '#22c55e') . '</tr>';
    $h .= '<tr>' . _kpiCellHtml('💰', 'Pipeline oferte', $k['pipeline_oferte_n'] . ' oferte · ' . cssiFmtRON($k['pipeline_oferte_v']) . ' RON', '#f97316');
    $h .=        _kpiCellHtml('📞', 'Lead-uri', (string)$k['leaduri'], '#8b5cf6') . '</tr>';
    $h .= '<tr>' . _kpiCellHtml('📐', 'La proiectare', (string)$k['la_proiectare'], '#14b8a6');
    $h .=        _kpiCellHtml('🔧', 'În execuție', (string)$k['in_executie'], '#f97316') . '</tr>';
    if ($k['expirate_oferte']) {
        $h .= '<tr><td colspan="2" style="padding:10px"><div style="background:#fef2f2;border-left:4px solid #dc2626;padding:10px 14px;border-radius:6px;color:#991b1b;font-size:13px"><strong>⏱️ ' . $k['expirate_oferte'] . ' oferte expirate</strong> — necesită follow-up</div></td></tr>';
    }
    $h .= '</table></td></tr>';

    if (!empty($d['programari_azi'])) {
        $h .= '<tr><td style="padding:0 30px 14px"><h3 style="margin:14px 0 8px;color:#0f172a;font-size:14px;border-bottom:2px solid #f1f5f9;padding-bottom:6px">📅 Programări AZI (' . count($d['programari_azi']) . ')</h3>';
        $h .= '<table width="100%" cellpadding="6" cellspacing="0" style="font-size:13px">';
        foreach ($d['programari_azi'] as $p) {
            $echipa = !empty($p['echipa']) ? '<span style="color:#64748b;font-size:11px"> · echipa: <strong>' . htmlspecialchars(implode(', ', $p['echipa'])) . '</strong></span>' : '';
            $h .= '<tr><td style="padding:6px 8px;border-bottom:1px solid #f1f5f9"><strong style="color:#3b82f6">' . substr($p['ora_start'], 0, 5) . '</strong> · ' . htmlspecialchars($p['client_nume'] ?? '?') . ' (' . $p['durata_ore'] . 'h)' . $echipa . '</td></tr>';
        }
        $h .= '</table></td></tr>';
    }

    if (!empty($d['contracte_asteapta'])) {
        $h .= '<tr><td style="padding:0 30px 14px"><h3 style="margin:14px 0 8px;color:#0f172a;font-size:14px;border-bottom:2px solid #f1f5f9;padding-bottom:6px">📄 Contracte — așteaptă date client</h3>';
        $h .= '<table width="100%" cellpadding="6" cellspacing="0" style="font-size:13px">';
        foreach ($d['contracte_asteapta'] as $c) {
            $h .= '<tr><td style="padding:6px 8px;border-bottom:1px solid #f1f5f9"><strong>' . htmlspecialchars($c['contract_nr']) . '</strong> · ' . htmlspecialchars($c['client_nume'] ?? '?') . '</td></tr>';
        }
        $h .= '</table></td></tr>';
    }

    if (!empty($d['leaduri_24h'])) {
        $h .= '<tr><td style="padding:0 30px 14px"><h3 style="margin:14px 0 8px;color:#0f172a;font-size:14px;border-bottom:2px solid #f1f5f9;padding-bottom:6px">📞 Lead-uri noi (ultimele 24h)</h3>';
        $h .= '<table width="100%" cellpadding="6" cellspacing="0" style="font-size:13px">';
        foreach ($d['leaduri_24h'] as $l) {
            $h .= '<tr><td style="padding:6px 8px;border-bottom:1px solid #f1f5f9"><strong>' . htmlspecialchars($l['proiect_id']) . '</strong> · ' . htmlspecialchars($l['nume'] ?? '?') . '</td></tr>';
        }
        $h .= '</table></td></tr>';
    }

    if (!empty($d['mentenante_scadente'])) {
        $h .= '<tr><td style="padding:0 30px 14px"><h3 style="margin:14px 0 8px;color:#0f172a;font-size:14px;border-bottom:2px solid #f1f5f9;padding-bottom:6px">🔄 Mentenanțe scadente (urm. 14 zile)</h3>';
        $h .= '<table width="100%" cellpadding="6" cellspacing="0" style="font-size:13px">';
        foreach ($d['mentenante_scadente'] as $m) {
            $zile = max(0, ceil((strtotime($m['data_scadenta']) - time()) / 86400));
            $color = $zile <= 3 ? '#dc2626' : '#f97316';
            $h .= '<tr><td style="padding:6px 8px;border-bottom:1px solid #f1f5f9">' . htmlspecialchars($m['client_nume'] ?? '?') . ' — <strong style="color:' . $color . '">în ' . $zile . ' zile</strong> (' . $m['data_scadenta'] . ')</td></tr>';
        }
        $h .= '</table></td></tr>';
    }

    $h .= '<tr><td style="background:#f1f5f9;padding:18px 30px;text-align:center;font-size:11px;color:#64748b">';
    $h .= 'Portal CSSI 3.0 · Raport automat zilnic · <a href="https://cssi.ro/admin" style="color:#3b82f6;text-decoration:none">Deschide Portal</a>';
    $h .= '</td></tr>';

    $h .= '</table></body></html>';
    return $h;
}

function cssiSendRaportEmail($recipients, $subject, $bodyHtml, $bodyText) {
    $boundary = 'cssi_' . md5(uniqid());
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        'From: Portal CSSI <noreply@cssi.ro>',
        'Reply-To: office@cssi.ro',
        'X-Mailer: CSSI-Cron/1.0',
    ];
    $multipart  = "--$boundary\r\n";
    $multipart .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $multipart .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $multipart .= $bodyText . "\r\n\r\n";
    $multipart .= "--$boundary\r\n";
    $multipart .= "Content-Type: text/html; charset=UTF-8\r\n";
    $multipart .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $multipart .= $bodyHtml . "\r\n\r\n";
    $multipart .= "--$boundary--";

    $sent = 0; $failed = 0;
    foreach ($recipients as $to) {
        if (mail($to, $subject, $multipart, implode("\r\n", $headers))) $sent++;
        else $failed++;
    }
    return ['sent' => $sent, 'failed' => $failed];
}

function cssiLogCronEmail($db, $script, $recipients, $sent, $failed, $extra = []) {
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS cron_log (
            id INT PRIMARY KEY AUTO_INCREMENT,
            script VARCHAR(60) NOT NULL,
            status VARCHAR(20) NOT NULL,
            recipients_count INT DEFAULT 0,
            success_count INT DEFAULT 0,
            failed_count INT DEFAULT 0,
            details TEXT,
            ts DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY idx_script_ts (script, ts)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $statusStr = $failed === 0 ? 'sent' : ($sent === 0 ? 'failed' : 'partial');
        $db->prepare("INSERT INTO cron_log (script, status, recipients_count, success_count, failed_count, details) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$script, $statusStr, count($recipients), $sent, $failed, json_encode(array_merge(['recipients' => $recipients], $extra))]);
    } catch (Exception $e) {}
}

}  // !function_exists check
