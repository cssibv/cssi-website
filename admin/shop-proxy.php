<?php
/**
 * Shop-Security.ro Product Proxy
 * Caută un produs după cod pe shop-security.ro și returnează JSON cu denumire + preț
 * Folosit de Generator Oferte CSSI (calculator-pret.html)
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

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
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    CURLOPT_HTTPHEADER => ['Accept: text/html,application/xhtml+xml'],
]);
$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if (!$html || $httpCode !== 200) {
    echo json_encode(['error' => 'Nu s-a putut accesa shop-security.ro', 'found' => false]);
    exit;
}

// Extrage primul produs din rezultate
// Structură: titlu produs în link-uri, preț în format "XXX,XX lei"
$products = [];

// Metodă 1: Caută titlul produsului - linkuri cu text UPPERCASE care conțin "CAMERA" sau alte cuvinte cheie
// Prețul apare ca "603,42 lei" în pagină
if (preg_match_all('/<a[^>]*href="(https:\/\/www\.shop-security\.ro\/[^"]+\.html)"[^>]*>\s*([A-Z][A-Z0-9\s,.\-\/\+\(\)]+)\s*<\/a>/u', $html, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $m) {
        $name = trim($m[2]);
        $url = $m[1];
        // Ignoră linkuri scurte sau de navigare
        if (strlen($name) < 15) continue;
        if (stripos($name, 'citeste') !== false) continue;
        if (stripos($name, 'adauga') !== false) continue;
        
        $products[] = [
            'name' => $name,
            'url' => $url,
        ];
    }
}

// Extrage prețurile - format "XXX,XX lei" sau "X XXX,XX lei"
$prices = [];
if (preg_match_all('/(\d[\d\s]*\d?[,\.]\d{2})\s*lei/u', $html, $priceMatches)) {
    foreach ($priceMatches[1] as $p) {
        $clean = str_replace([' ', ','], ['', '.'], $p);
        $val = floatval($clean);
        if ($val > 0 && $val < 100000) {
            $prices[] = $val;
        }
    }
}

// Extrage codurile produselor
$codes = [];
if (preg_match_all('/Cod:\s*<[^>]*>([^<]+)</u', $html, $codeMatches)) {
    $codes = array_map('trim', $codeMatches[1]);
}

// Asociază primul produs cu primul preț
if (!empty($products) && !empty($prices)) {
    $result = [
        'found' => true,
        'name' => $products[0]['name'],
        'price' => $prices[0],
        'url' => $products[0]['url'],
        'code' => !empty($codes) ? $codes[0] : $code,
        'total_results' => count($products),
    ];
    
    // Dacă sunt mai multe produse, adaugă lista
    if (count($products) > 1) {
        $result['alternatives'] = [];
        for ($i = 1; $i < min(count($products), 5); $i++) {
            $result['alternatives'][] = [
                'name' => $products[$i]['name'],
                'url' => $products[$i]['url'],
                'price' => isset($prices[$i]) ? $prices[$i] : null,
            ];
        }
    }
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} else {
    // Fallback: încearcă să extragă orice text de produs
    echo json_encode([
        'found' => false,
        'error' => 'Produsul nu a fost găsit pe shop-security.ro',
        'search_url' => $searchUrl,
        'debug_products' => count($products),
        'debug_prices' => count($prices),
    ], JSON_UNESCAPED_UNICODE);
}
