<?php
// ============================================================
// CSSI Portal — Secrets template
// ============================================================
// COPIAZĂ acest fișier ca `secrets.php` (în root, NU în git)
// și pune valorile reale. Fișierul `secrets.php` e gitignored.
//
// Pe server (cPanel): upload manual prin File Manager în /home/r101042brea/cssi.ro/
// ============================================================

return [
    'DB_PASS'        => 'PUNE_PAROLA_DB_AICI',
    'ZERNIO_KEY'     => 'PUNE_KEY_ZERNIO_AICI',
    'ANTHROPIC_KEY'  => 'PUNE_KEY_ANTHROPIC_AICI',
    // Opțional: suprascrie modelul Claude. Dacă lipsește, codul folosește
    // claude-sonnet-5 (vezi callClaude() în admin/api.php).
    // 'CLAUDE_MODEL'   => 'claude-sonnet-5',
    // Token pentru recuperare conturi blocate (ex: admin lockout)
    // Generează cu: bin2hex(random_bytes(32))
    'RECOVERY_TOKEN' => 'PUNE_UN_TOKEN_LUNG_ALEATOR_DOAR_TU_AICI',
];
