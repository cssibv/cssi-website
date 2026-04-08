<?php
/**
 * CSSI - Generare pagini localizate Brașov
 * Rulat automat la deploy via .cpanel.yml
 */

$deployPath = getenv('DEPLOYPATH') ?: __DIR__;

$zones_html = '
<!-- ========== ZONE DESERVITE ========== -->
<section style="padding:40px 16px;background:#f9fafb;">
    <div style="max-width:1280px;margin:0 auto;text-align:center;">
        <h2 style="font-size:24px;font-weight:700;color:#111827;margin-bottom:24px;">Zone Deservite în Brașov</h2>
        <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:12px;">
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Brașov Centru</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Tractorul</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Astra</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Bartolomeu</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Noua</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Răcădău</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Scriitori</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Stupini</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Poiana Brașov</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Sânpetru</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Ghimbav</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Codlea</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Râșnov</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Săcele</span>
            <span style="padding:8px 16px;background:#fff;color:#334155;border-radius:999px;font-size:14px;font-weight:500;box-shadow:0 1px 2px rgba(0,0,0,0.05);">Predeal</span>
        </div>
    </div>
</section>
';

$pages = [
    ['src' => 'camere-supraveghere.html',    'dst' => 'camere-supraveghere-brasov.html',    'slug' => 'camere-supraveghere-brasov',    'old_url' => 'https://cssi.ro/camere-supraveghere'],
    ['src' => 'detectie-incendiu-isu.html',   'dst' => 'detectie-incendiu-brasov.html',      'slug' => 'detectie-incendiu-brasov',      'old_url' => 'https://cssi.ro/detectie-incendiu-isu'],
    ['src' => 'control-acces.html',           'dst' => 'control-acces-brasov.html',           'slug' => 'control-acces-brasov',           'old_url' => 'https://cssi.ro/control-acces'],
    ['src' => 'aer-conditionat.html',         'dst' => 'montaj-aer-conditionat-brasov.html',  'slug' => 'montaj-aer-conditionat-brasov',  'old_url' => 'https://cssi.ro/aer-conditionat'],
    ['src' => 'automatizari-porti.html',      'dst' => 'automatizari-porti-brasov.html',      'slug' => 'automatizari-porti-brasov',      'old_url' => 'https://cssi.ro/automatizari-porti'],
];

foreach ($pages as $p) {
    $srcPath = $deployPath . '/' . $p['src'];
    $dstPath = $deployPath . '/' . $p['dst'];
    
    if (!file_exists($srcPath)) {
        echo "SKIP: {$p['src']} not found\n";
        continue;
    }
    
    $html = file_get_contents($srcPath);
    $newUrl = 'https://cssi.ro/' . $p['slug'];
    
    // 1. Replace canonical URL
    $html = str_replace('href="' . $p['old_url'] . '"', 'href="' . $newUrl . '"', $html);
    
    // 2. Replace OG URL
    $html = str_replace('content="' . $p['old_url'] . '"', 'content="' . $newUrl . '"', $html);
    
    // 3. Replace schema URLs
    $html = str_replace('"url":"' . $p['old_url'] . '"', '"url":"' . $newUrl . '"', $html);
    
    // 4. Replace breadcrumb item URL
    $html = str_replace('"item":"' . $p['old_url'] . '"', '"item":"' . $newUrl . '"', $html);
    
    // 5. Add Brașov to H1 (find </h1> in hero section)
    $html = preg_replace(
        '/<\/h1>/',
        ' <span style="color:#fde047;">Brașov</span></h1>',
        $html, 1
    );
    
    // 6. Insert zones section before CTA
    $html = str_replace(
        '<!-- ========== CTA ==========',
        $zones_html . '<!-- ========== CTA ==========',
        $html
    );
    
    file_put_contents($dstPath, $html);
    $size = strlen($html);
    echo "OK: {$p['dst']} ({$size} bytes)\n";
}

echo "Done: " . count($pages) . " localized pages generated.\n";
