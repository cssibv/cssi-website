<?php
/**
 * Shop-Security.ro Product Proxy v2
 * Caută un produs după cod pe shop-security.ro și returnează JSON
 * Folosit de Generator Oferte CSSI (calculator-pret.html)
 * mode=image — proxy imagine binara (rezolva hotlink/referer in print PDF)
 */

// ─── MODE: image (proxy binar) ───────────────────────────────────
// Apelat ca: shop-proxy.php?mode=image&img=https://www.shop-security.ro/upload/img/products/...
// Browser-ul face request same-origin → fără probleme de CORS/Referer/hotlink
if (isset($_GET['mode']) && $_GET['mode'] === 'image') {
    $imgUrl = isset($_GET['img']) ? $_GET['img'] : '';
    if (!$imgUrl || !preg_match('#^https?://(www\.)?shop-security\.ro/#i', $imgUrl)) {
        http_response_code(400);
        header('Content-Type: text/plain');
        echo 'Invalid img URL (only shop-security.ro permitted)';
        exit;
    }
    $chi = curl_init();
    curl_setopt_array($chi, [
        CURLOPT_URL => $imgUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_REFERER => 'https://www.shop-security.ro/',
        CURLOPT_HTTPHEADER => ['Accept: image/avif,image/webp,image/png,image/jpeg,image/*,*/*;q=0.8'],
    ]);
    $imgData = curl_exec($chi);
    $imgCt   = curl_getinfo($chi, CURLINFO_CONTENT_TYPE);
    $imgHttp = curl_getinfo($chi, CURLINFO_HTTP_CODE);
    curl_close($chi);
    if (!$imgData || $imgHttp !== 200) {
        http_response_code(404);
        header('Content-Type: text/plain');
        echo 'Image fetch failed (HTTP ' . $imgHttp . ')';
        exit;
    }
    if (!$imgCt) {
        $ext = strtolower(pathinfo(parse_url($imgUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
        $imgCt = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','gif'=>'image/gif'][$ext] ?? 'image/jpeg';
    }
    header('Content-Type: ' . $imgCt);
    header('Cache-Control: public, max-age=604800, immutable');
    header('Access-Control-Allow-Origin: *');
    echo $imgData;
    exit;
}

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

// === EXTRAGE IMAGINI ===
// Strategia primară: dacă avem un produs cu URL, facem un al doilea request la
// pagina de detaliu și scoatem imaginea HD din slick carousel (better quality).
// Fallback: imagine din pagina de search (thumbnail, mai mic).
$images = [];

// PRIMARY — fetch detail page și extrage din slick carousel
if (!empty($products) && !empty($products[0]['url'])) {
    $detailUrl = $products[0]['url'];
    $ch2 = curl_init();
    curl_setopt_array($ch2, [
        CURLOPT_URL => $detailUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ]);
    $detailHtml = curl_exec($ch2);
    $detailHttp = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);

    if ($detailHtml && $detailHttp === 200) {
        // Metoda A: exact div-ul slick-current din carusel (cea mai sigură)
        if (preg_match('/<div[^>]*class="[^"]*slick-slide\s+slick-current[^"]*"[^>]*>\s*<img[^>]*class="img-fluid"[^>]*src="([^"]+)"/si', $detailHtml, $mA)) {
            $images[] = $mA[1];
        }
        // Metoda B: orice <img class="img-fluid" src="..."> care e în /upload/img/products/
        if (empty($images) && preg_match_all('/<img[^>]*class="[^"]*img-fluid[^"]*"[^>]*src="(https?:\/\/[^"]*shop-security\.ro\/upload\/img\/products\/[^"]+)"/si', $detailHtml, $mB, PREG_SET_ORDER)) {
            foreach ($mB as $m) $images[] = $m[1];
        }
        // Metoda C: orice <img src=...> care e în /upload/img/products/
        if (empty($images) && preg_match_all('/<img[^>]*src="(https?:\/\/[^"]*shop-security\.ro\/upload\/img\/products\/[^"]+\.(?:jpg|jpeg|png|webp))"/si', $detailHtml, $mC, PREG_SET_ORDER)) {
            foreach ($mC as $m) $images[] = $m[1];
        }
    }
}

// FALLBACK — imagine din pagina de search dacă detaliu a eșuat
if (empty($images)) {
    if (preg_match_all('/<img[^>]*class="[^"]*product-item__image[^"]*"[^>]*src="([^"]+)"/si', $html, $im1, PREG_SET_ORDER)) {
        foreach ($im1 as $m) $images[] = $m[1];
    }
    if (empty($images) && preg_match_all('/<img[^>]*src="(https?:\/\/[^"]*shop-security\.ro\/upload\/img\/products\/[^"]+\.(?:jpg|jpeg|png|webp))"/si', $html, $im2, PREG_SET_ORDER)) {
        foreach ($im2 as $m) $images[] = $m[1];
    }
}

// === EXTRAGE DESCRIERE LUNGĂ (din pagina de detaliu, după <!-- Tab Prodcut Section -->) ===
// Structura tipica shop-security:
//   <!-- Tab Prodcut Section -->
//     <ul class="nav-tabs">...butoane...</ul>
//     <div class="tab-content" id="pills-tabContent">
//       <div class="tab-pane fade show active" id="pills-one-example1">  ← DESCRIERE aici
//       <div class="tab-pane fade" id="pills-two-example1">              ← Specificatii
//       <div class="tab-pane fade" id="product-tab-reviews">             ← Recenzii
//     </div>
$description = '';
if (!empty($detailHtml)) {
    // Metoda A (cea mai sigura): primul tab-pane cu „show active" (= Descriere)
    //   captura cuprinde tot pana la urmatorul tab-pane (Specificatii)
    if (preg_match('/<div[^>]*class="[^"]*tab-pane[^"]*\bshow\s+active\b[^"]*"[^>]*>(.*?)<div[^>]*class="[^"]*tab-pane\b/si', $detailHtml, $dA)) {
        $description = $dA[1];
    }
    // Metoda B: explicit pe id pills-one-example1
    if (empty($description) && preg_match('/<div[^>]*id="pills-one-example1"[^>]*>(.*?)<div[^>]*id="pills-two-example1"/si', $detailHtml, $dB)) {
        $description = $dB[1];
    }
    // Metoda C fallback: id-uri uzuale (tab-description, product-description)
    if (empty($description) && preg_match('/<div[^>]*(?:id|class)="[^"]*(?:tab-description|description-tab|product-description)[^"]*"[^>]*>(.*?)<\/div>\s*<\/div>/si', $detailHtml, $dC)) {
        $description = $dC[1];
    }
    // Curățare HTML → text simplu
    if ($description) {
        // Scot tag-uri script/style cu tot conținutul
        $description = preg_replace('/<(script|style|noscript)\b[^>]*>.*?<\/\1>/si', '', $description);
        // Convertesc <br> și </p> în newline-uri ca să păstrez structura
        $description = preg_replace('/<br\s*\/?>/i', "\n", $description);
        $description = preg_replace('/<\/p>/i', "\n\n", $description);
        $description = preg_replace('/<\/li>/i', "\n", $description);
        $description = preg_replace('/<li[^>]*>/i', '• ', $description);
        // Scot toate tag-urile
        $description = strip_tags($description);
        // Decodez entități HTML
        $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Normalizez spațiile (păstrez newline-urile)
        $description = preg_replace('/[ \t]+/', ' ', $description);
        $description = preg_replace('/\n[ \t]+/', "\n", $description);
        $description = preg_replace('/\n{3,}/', "\n\n", $description);
        $description = trim($description);
        // Limitez la 2000 caractere ca să nu intre conținut imens
        if (mb_strlen($description) > 2000) {
            $description = mb_substr($description, 0, 1997) . '...';
        }
    }
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
        'image' => !empty($images) ? $images[0] : '',
        'description' => $description,
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
