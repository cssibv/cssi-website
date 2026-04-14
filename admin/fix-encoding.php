<?php
// ============================================================
// One-time fix: convert social_posts + other tables to utf8mb4
// Usage: Visit https://cssi.ro/admin/fix-encoding.php ONCE, then DELETE this file.
// ============================================================
require __DIR__ . '/db.php';

header('Content-Type: text/plain; charset=utf-8');
$db = getDB();

$tables = ['social_posts']; // add more if needed
$results = [];

foreach ($tables as $t) {
    try {
        // Show current state
        $before = $db->query("SHOW CREATE TABLE `$t`")->fetch();
        $results[] = "=== $t (BEFORE) ===\n" . ($before['Create Table'] ?? 'n/a');

        // Convert
        $db->exec("ALTER TABLE `$t` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $results[] = "✓ ALTER TABLE $t CONVERT TO utf8mb4 — OK";

        // Verify
        $after = $db->query("SHOW CREATE TABLE `$t`")->fetch();
        $results[] = "=== $t (AFTER) ===\n" . ($after['Create Table'] ?? 'n/a');
    } catch (Exception $e) {
        $results[] = "✗ Eroare la $t: " . $e->getMessage();
    }
}

echo implode("\n\n", $results);
echo "\n\nGATA. Șterge acest fișier după ce vezi output-ul: admin/fix-encoding.php\n";
