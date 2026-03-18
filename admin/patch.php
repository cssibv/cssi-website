<?php
// CSSI Patch - repara api.php si calculator-pret.html pe server
// STERGE DUPA UTILIZARE!
header('Content-Type: text/html; charset=utf-8');
$p = isset($_GET['go']) ? $_GET['go'] : '';
if ($p !== 'cssi2026') {
    die('Usage: patch.php?go=cssi2026');
}

$dir = __DIR__;
$log = array();

// ========== FIX 1: api.php - transaction bug ==========
$api = file_get_contents($dir . '/api.php');
$fixed = 0;

// Fix: move nextId BEFORE beginTransaction in saveOferta
$old1 = "case 'saveOferta':\n            \$db->beginTransaction();\n            try {\n                \$isUpdate = !empty(\$data['oferta_db_id']);";
$new1 = "case 'saveOferta':\n            // ID generated BEFORE transaction (nextId has own transaction)\n            \$isUpdate = !empty(\$data['oferta_db_id']);\n            \$preGeneratedId = null;\n            if (!\$isUpdate) { \$preGeneratedId = (isset(\$data['nr']) ? \$data['nr'] : nextId('oferta_seq', 'OF-', 6)); }\n            \$db->beginTransaction();\n            try {";

if (strpos($api, $old1) !== false) {
    $api = str_replace($old1, $new1, $api);
    $fixed++;
    $log[] = 'FIX1a: Transaction bug - moved nextId before beginTransaction';
}

// Fix: replace nextId call inside transaction with preGeneratedId
$old2 = "\$ofertaId = (isset(\$data['nr']) ? \$data['nr'] : nextId('oferta_seq', 'OF-', 6));";
$new2 = "\$ofertaId = \$preGeneratedId;";
if (strpos($api, $old2) !== false) {
    $api = str_replace($old2, $new2, $api);
    $fixed++;
    $log[] = 'FIX1b: Replaced nextId inside transaction with preGeneratedId';
}

// Fix: client_db_id ?: null warnings
$old3 = "\$data['client_db_id'] ?: null";
$new3 = "(isset(\$data['client_db_id']) && \$data['client_db_id']) ? \$data['client_db_id'] : null";
$count3 = substr_count($api, $old3);
if ($count3 > 0) {
    $api = str_replace($old3, $new3, $api);
    $fixed += $count3;
    $log[] = 'FIX1c: Fixed client_db_id warnings (' . $count3 . ' occurrences)';
}

$old4 = "\$data['proiect_db_id'] ?: null";
$new4 = "(isset(\$data['proiect_db_id']) && \$data['proiect_db_id']) ? \$data['proiect_db_id'] : null";
$count4 = substr_count($api, $old4);
if ($count4 > 0) {
    $api = str_replace($old4, $new4, $api);
    $fixed += $count4;
    $log[] = 'FIX1d: Fixed proiect_db_id warnings (' . $count4 . ' occurrences)';
}

if ($fixed > 0) {
    file_put_contents($dir . '/api.php', $api);
    $log[] = 'api.php SAVED (' . $fixed . ' fixes)';
} else {
    $log[] = 'api.php - no changes needed or already fixed';
}

// ========== FIX 2: calculator-pret.html - commented variables ==========
$calc = file_get_contents($dir . '/calculator-pret.html');
$fixedCalc = 0;

$oldCalc = '// Google API removed';
if (strpos($calc, $oldCalc) !== false) {
    // Remove the comment that breaks the variables
    $calc = str_replace('// Google API removed — using MySQL localvar', 'var', $calc);
    $fixedCalc++;
    $log[] = 'FIX2: Removed comment breaking DEFAULT_ADAOS and OFFER_START_NR';
}

if ($fixedCalc > 0) {
    file_put_contents($dir . '/calculator-pret.html', $calc);
    $log[] = 'calculator-pret.html SAVED';
} else {
    $log[] = 'calculator-pret.html - no changes needed or already fixed';
}

// ========== OUTPUT ==========
echo '<h2 style="font-family:system-ui;color:green">CSSI Patch Results</h2>';
echo '<ul style="font-family:monospace;font-size:13px">';
foreach ($log as $l) {
    $color = strpos($l, 'SAVED') !== false ? 'green' : (strpos($l, 'no changes') !== false ? 'blue' : '#333');
    echo '<li style="color:' . $color . '">' . htmlspecialchars($l) . '</li>';
}
echo '</ul>';
echo '<p><a href="/admin/api.php?action=ping">Test API</a> | <a href="/admin/calculator-pret">Test Generator</a></p>';
echo '<p style="color:red;font-weight:bold">STERGE patch.php dupa utilizare!</p>';
