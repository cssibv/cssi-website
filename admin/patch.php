<?php
header('Content-Type: text/html; charset=utf-8');
$p = isset($_GET['go']) ? $_GET['go'] : '';
if ($p !== 'cssi2026') { die('Usage: patch.php?go=cssi2026'); }
$dir = __DIR__;
$log = array();

// === FIX api.php ===
$api = file_get_contents($dir . '/api.php');
$fx = 0;

// Fix transaction bug
$old1 = "case 'saveOferta':\n            \$db->beginTransaction();\n            try {\n                \$isUpdate = !empty(\$data['oferta_db_id']);";
if (strpos($api, $old1) !== false) {
    $api = str_replace($old1, "case 'saveOferta':\n            \$isUpdate = !empty(\$data['oferta_db_id']);\n            \$preGeneratedId = null;\n            if (!\$isUpdate) { \$preGeneratedId = (isset(\$data['nr']) ? \$data['nr'] : nextId('oferta_seq', 'OF-', 6)); }\n            \$db->beginTransaction();\n            try {", $api);
    $fx++;
    $log[] = 'FIX: api.php transaction bug';
}
$old2 = "\$ofertaId = (isset(\$data['nr']) ? \$data['nr'] : nextId('oferta_seq', 'OF-', 6));";
if (strpos($api, $old2) !== false) {
    $api = str_replace($old2, "\$ofertaId = \$preGeneratedId;", $api);
    $fx++;
    $log[] = 'FIX: api.php nextId inside transaction';
}
// Fix undefined key warnings
$old3 = "\$data['client_db_id'] ?: null";
$new3 = "(isset(\$data['client_db_id']) && \$data['client_db_id']) ? \$data['client_db_id'] : null";
if (strpos($api, $old3) !== false) {
    $api = str_replace($old3, $new3, $api);
    $fx++;
    $log[] = 'FIX: api.php client_db_id warning';
}
$old4 = "\$data['proiect_db_id'] ?: null";
$new4 = "(isset(\$data['proiect_db_id']) && \$data['proiect_db_id']) ? \$data['proiect_db_id'] : null";
if (strpos($api, $old4) !== false) {
    $api = str_replace($old4, $new4, $api);
    $fx++;
    $log[] = 'FIX: api.php proiect_db_id warning';
}
if ($fx > 0) {
    file_put_contents($dir . '/api.php', $api);
    $log[] = 'api.php SAVED (' . $fx . ' fixes)';
} else {
    $log[] = 'api.php - already fixed';
}

// === FIX calculator-pret.html ===
$calc = file_get_contents($dir . '/calculator-pret.html');
$fc = 0;

// Fix commented variables
if (strpos($calc, '// Google API removed') !== false) {
    $calc = str_replace('// Google API removed — using MySQL localvar', 'var', $calc);
    $fc++;
    $log[] = 'FIX: calculator variables uncommented';
}

// Fix field mapping - API returns total_cu_tva but renderSaved expects totalBrut
$oldMap = 'if(res.success&&res.data){offers=res.data;';
$newMap = 'if(res.success&&res.data){offers=res.data.map(function(o){o.totalBrut=parseFloat(o.total_cu_tva||o.totalBrut)||0;o.totalNet=parseFloat(o.total_fara_tva||o.totalNet)||0;o.subtotalEchip=parseFloat(o.subtotal_echip||o.subtotalEchip)||0;o.subtotalManop=parseFloat(o.subtotal_manop||o.subtotalManop)||0;o.tva=parseFloat(o.tva)||0;o.nr=o.oferta_id||o.nr||"";o.client=o.client_nume||o.client||"";o.cui=o.client_cui||o.cui||"";o.adresa=o.client_adresa||o.adresa||"";o.contact=o.client_contact||o.contact||"";o.obiectiv=o.obiectiv||"";o.data=o.data_oferta||o.data||"";o.valab=o.valabilitate||o.valab||"4 zile";o.createdAt=o.created_at||"";o.lines=(o.lines||[]).map(function(l){return{id:l.id,name:l.denumire||l.name||"",code:l.cod||l.code||"",um:l.um||"buc.",cant:parseFloat(l.cantitate||l.cant)||0,pAchiz:parseFloat(l.pret_achizitie||l.pAchiz)||0,adaos:parseFloat(l.adaos_procent||l.adaos)||40}});o.labor=(o.labor||[]).map(function(l){return{id:l.id,name:l.denumire||l.name||"",um:l.um||"ore",cant:parseFloat(l.cantitate||l.cant)||0,price:parseFloat(l.pret_achizitie||l.pret_unitar||l.price)||0}});return o});';
if (strpos($calc, $oldMap) !== false) {
    $calc = str_replace($oldMap, $newMap, $calc);
    $fc++;
    $log[] = 'FIX: calculator field mapping (totalBrut, client, lines, labor)';
}

if ($fc > 0) {
    file_put_contents($dir . '/calculator-pret.html', $calc);
    $log[] = 'calculator-pret.html SAVED (' . $fc . ' fixes)';
} else {
    $log[] = 'calculator-pret.html - already fixed';
}

// === OUTPUT ===
echo '<h2 style="font-family:system-ui;color:green">CSSI Patch v2</h2>';
echo '<ul style="font-family:monospace;font-size:13px">';
foreach ($log as $l) {
    $c = strpos($l, 'SAVED') !== false ? 'green' : (strpos($l, 'already') !== false ? 'blue' : '#333');
    echo '<li style="color:' . $c . '">' . htmlspecialchars($l) . '</li>';
}
echo '</ul>';
echo '<p><a href="/admin/api.php?action=ping">Test API</a> | <a href="/admin/calculator-pret">Test Generator</a></p>';
echo '<p style="color:red;font-weight:bold">STERGE patch.php dupa!</p>';
