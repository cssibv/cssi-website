<?php
/**
 * Shop-Security.ro Product Proxy v2
 * Caută un produs după cod pe shop-security.ro și returnează JSON
 * Folosit de Generator Oferte CSSI (calculator-pret.html)
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

$code = isset($_GET['q']) ? trim($_GET['q']) : '';
if (!$code) {
    echo json_encode(['error' => 'Cod produs lipsă', 'found' => false]);
    exit;
}

// Caută pe shop-security.ro
$searchUrl = 'https://www.shop-security.ro/search?q=' . urlencode($code);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $searchUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    CURLOPT_HTTPHEADER => [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: ro-RO,ro;q=0.9,en;q=0.8',
    ],
]);
$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$html || $httpCode !== 200) {
    echo json_encode(['error' => 'Nu s-a putut accesa shop-security.ro (HTTP '.$httpCode.')', 'found' => false]);
    exit;
}

$products = [];

// === METODA 1: Caută titlu produs în <h5 class="...product-item__title"><a ...>TITLU</a></h5> ===
if (preg_match_all('/<h5[^>]*product-item__title[^>]*>\s*<a[^>]*href="([^"]+)"[^>]*>\s*(.+?)\s*<\/a>/si', $html, $titleMatches, PREG_SET_ORDER)) {
    foreach ($titleMatches as $m) {
        $name = trim(strip_tags($m[2]));
        $url = trim($m[1]);
        if (strlen($name) > 10) {
            $products[] = ['name' => $name, 'url' => $url];
        }
    }
}

// === METODA 2 FALLBACK: Caută link-uri cu class "text-blue font-weight-bold" spre .html ===
if (empty($products)) {
    if (preg_match_all('/<a[^>]*class="[^"]*text-blue[^"]*font-weight-bold[^"]*"[^>]*href="(https?:\/\/[^"]*\.html)"[^>]*>\s*(.+?)\s*<\/a>/si', $html, $titleMatches2, PREG_SET_ORDER)) {
        foreach ($titleMatches2 as $m) {
            $name = trim(strip_tags($m[2]));
            $url = trim($m[1]);
            if (strlen($name) > 10 && stripos($name, 'Adauga') === false && stripos($name, 'Citeste') === false) {
                $products[] = ['name' => $name, 'url' => $url];
            }
        }
    }
}

// === METODA 3 FALLBACK: Orice link .html cu text lung care nu e navigare ===
if (empty($products)) {
    if (preg_match_all('/<a[^>]*href="(https:\/\/www\.shop-security\.ro\/(?!p\/|search)[^"]+\.html)"[^>]*>\s*(.+?)\s*<\/a>/si', $html, $titleMatches3, PREG_SET_ORDER)) {
        foreach ($titleMatches3 as $m) {
            $name = trim(strip_tags($m[2]));
            $url = trim($m[1]);
            if (strlen($name) > 20 && stripos($name, 'Adauga') === false && stripos($name, 'Citeste') === false && stripos($name, 'Montaj') === false) {
                $products[] = ['name' => $name, 'url' => $url];
            }
        }
    }
}

// === EXTRAGE PREȚURI ===
$prices = [];

// Metoda 1: <div class="prodcut-price...">XXX,XX lei</div>
if (preg_match_all('/<div[^>]*prodcut-price[^>]*>\s*([\d\s]+[,\.]\d{2})\s*lei/si', $html, $pm)) {
    foreach ($pm[1] as $p) {
        $clean = floatval(str_replace([' ', ','], ['', '.'], $p));
        if ($clean > 0) $prices[] = $clean;
    }
}

// Metoda 2: <ins ...>XXX,XX lei</ins>
if (empty($prices)) {
    if (preg_match_all('/<ins[^>]*>\s*([\d\s]+[,\.]\d{2})\s*lei\s*<\/ins>/si', $html, $pm2)) {
        foreach ($pm2[1] as $p) {
            $clean = floatval(str_replace([' ', ','], ['', '.'], $p));
            if ($clean > 0) $prices[] = $clean;
        }
    }
}

// Metoda 3 fallback: Orice "XXX,XX lei" din pagină (nu 0,00)
if (empty($prices)) {
    if (preg_match_all('/(\d[\d\s]*\d?[,\.]\d{2})\s*lei/u', $html, $pm3)) {
        foreach ($pm3[1] as $p) {
            $clean = floatval(str_replace([' ', ','], ['', '.'], $p));
            if ($clean > 1) $prices[] = $clean; // Exclude 0,00 lei
        }
    }
}

// === EXTRAGE CODURI ===
$codes = [];
if (preg_match_all('/Cod:\s*<[^>]*>\s*([^<]+)/u', $html, $cm)) {
    $codes = array_map('trim', $cm[1]);
}

// === CONSTRUIEȘTE RĂSPUNS ===
if (!empty($products) && !empty($prices)) {
    // Elimină codul SKU din denumire dacă e prezent
    $name = $products[0]['name'];
    // Elimină "HIKVISION DS-XXXX" sau "- HIKVISION DS-XXXX" de la sfârsit
    $name = preg_replace('/\s*-?\s*HIKVISION\s+DS-[\w\-\/]+$/i', '', $name);
    // Elimină "- DS-XXXX" de la sfârsit
    $name = preg_replace('/\s*-?\s*DS-[\w\-\/]+$/i', '', $name);
    $name = trim($name, ' -');

    $result = [
        'found' => true,
        'name' => $name ?: $products[0]['name'],
        'nameOriginal' => $products[0]['name'],
        'price' => $prices[0],
        'url' => $products[0]['url'],
        'code' => !empty($codes) ? $codes[0] : $code,
        'total_results' => count($products),
    ];

    // Adaugă alternative dacă există
    if (count($products) > 1) {
        $result['alternatives'] = [];
        for ($i = 1; $i < min(count($products), 5); $i++) {
            $altName = $products[$i]['name'];
            $result['alternatives'][] = [
                'name' => $altName,
                'url' => $products[$i]['url'],
                'price' => isset($prices[$i]) ? $prices[$i] : null,
            ];
        }
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'found' => false,
        'error' => 'Produsul nu a fost găsit pe shop-security.ro',
        'search_url' => $searchUrl,
        'debug' => [
            'products_found' => count($products),
            'prices_found' => count($prices),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
