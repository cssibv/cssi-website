<?php
// Quick fix: repair calculator-pret.html line 40
// DELETE THIS FILE AFTER USE
$file = __DIR__ . '/calculator-pret.html';
$content = file_get_contents($file);
if ($content === false) { die('Cannot read file'); }

$old = '// Google API removed — using MySQL localvar DEFAULT_ADAOS=40;var OFFER_START_NR=244100;';
$new = 'var DEFAULT_ADAOS=40;var OFFER_START_NR=244100;';

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo 'FIXED! Removed comment that broke OFFER_START_NR and DEFAULT_ADAOS variables.';
} else if (strpos($content, 'var DEFAULT_ADAOS=40;var OFFER_START_NR=244100;') !== false) {
    echo 'Already fixed - no changes needed.';
} else {
    echo 'Pattern not found. File may have different content.';
}
