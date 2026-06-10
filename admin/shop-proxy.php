<?php
/**
 * Shop-Security.ro Product Proxy v4 — rescris pentru platforma WooCommerce/WoodMart
 * Caută un produs după cod pe shop-security.ro și returnează JSON
 * Căutare: /?s=COD&post_type=product · cod din "sku":, preț din meta product:price:amount,
 * nume din og:title, imagine din data-large_image, descriere din #tab-description.
 * mode=image — proxy imagine binara (rezolva hotlink/referer in print PDF)
 * Versiune: 2026-06-03 — migrare la WooCommerce (vechiul /search?q= + markup custom nu mai există)
 */

// ─── MODE: image (proxy binar) ───────────────────────────────────
// Apelat ca: shop-proxy.php?mode=image&img=https://www.shop-security.ro/wp-content/uploads/...
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

// Caută pe shop-security.ro (platformă WooCommerce/WoodMart din 2026)
// Căutarea WooCommerce: /?s=TERMEN&post_type=product (input name="s")
$searchUrl = 'https://www.shop-security.ro/?s=' . urlencode($code) . '&post_type=product';

// Helper: fetch HTML cu user-agent de browser. Întoarce [body, http, errno, error].
function ssFetch($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_CONNECTTIMEOUT => 12,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_ENCODING => '', // accept gzip/deflate/br
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4, // unele shared-hosts au IPv6 rupt
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ro-RO,ro;q=0.9,en;q=0.8',
            'Connection: close',
        ],
    ]);
    $body  = curl_exec($ch);
    $http  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    curl_close($ch);
    return [$body, $http, $errno, $error];
}

// Helper: preț românesc "1.234,56" sau "411,40" → float 1234.56 / 411.40
function ssParsePrice($raw) {
    $raw = trim($raw);
    // scot separatorul de mii (.) și transform virgula zecimală în punct
    $raw = str_replace(['.', ' ', "\xc2\xa0"], '', $raw); // elimină . spațiu &nbsp;
    $raw = str_replace(',', '.', $raw);
    return floatval($raw);
}

// Helper: normalizează un cod pentru comparație — trim, lowercase, fără spații interioare.
// (SKU-ul de pe site poate avea spații/altă capitalizare decât ce tastăm noi)
function ssNormCode($s) {
    $s = mb_strtolower(trim((string)$s), 'UTF-8');
    return preg_replace('/\s+/u', '', $s);
}

// Helper: extrage codul produsului (SKU) din pagina de detaliu.
// Sursa principală cerută: <span class="sku">COD</span> ("Cod produs:"); fallback pe JSON "sku":"...".
function ssExtractSku($html) {
    if (preg_match('/<span[^>]*class="[^"]*\bsku\b[^"]*"[^>]*>\s*(.*?)\s*<\/span>/si', $html, $m)) {
        $v = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($v !== '' && strtolower($v) !== 'n/a') return $v;
    }
    if (preg_match('/"sku"\s*:\s*"([^"]+)"/', $html, $m) && trim($m[1]) !== '') {
        return trim($m[1]);
    }
    return '';
}

list($html, $httpCode, $curlErrno, $curlError) = ssFetch($searchUrl);

if (!$html || $httpCode !== 200) {
    echo json_encode([
        'found' => false,
        'error' => 'Nu s-a putut accesa shop-security.ro (HTTP '.$httpCode.')',
        'debug' => [
            'http' => $httpCode,
            'curl_errno' => $curlErrno,
            'curl_error' => $curlError,
            'url' => $searchUrl,
            'curl_ssl' => function_exists('curl_version') ? (curl_version()['ssl_version'] ?? '') : '',
        ],
    ]);
    exit;
}

// === PRODUSE din pagina de rezultate (WooCommerce/WoodMart) ===
// Card produs: <h3 class="wd-entities-title"><a href="URL">NUME</a></h3>
$products = [];
$seenUrls = [];
if (preg_match_all('/<h[1-6][^>]*class="[^"]*wd-entities-title[^"]*"[^>]*>\s*<a[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/si', $html, $tm, PREG_SET_ORDER)) {
    foreach ($tm as $m) {
        $url = trim($m[1]);
        $name = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($name === '' || isset($seenUrls[$url])) continue;
        // doar URL-uri de produs (un singur segment de path), nu categorii/tag-uri
        $path = trim((string)parse_url($url, PHP_URL_PATH), '/');
        if ($path === '' || strpos($path, '/') !== false) continue; // sare peste /product-category/... etc
        $seenUrls[$url] = true;
        $products[] = ['name' => $name, 'url' => $url];
    }
}

// === Selectează produsul al cărui COD PRODUS (SKU) == codul căutat ===
// Sursa de adevăr e <span class="sku">COD</span> de pe pagina de detaliu, NU denumirea/slug-ul.
// Parcurgem candidații (max N), luăm pagina fiecăruia și comparăm SKU-ul cu ce am tastat.
// Ne oprim la prima potrivire exactă; dacă niciuna nu se potrivește → cădem pe primul rezultat.
$qNorm      = ssNormCode($code);
$detailHtml = '';
$detailUrl  = '';
$firstHtml  = '';
$firstUrl   = '';
$matchedIdx = -1;

if (!empty($products)) {
    $maxCheck = min(count($products), 6); // limităm request-urile ca să nu fie lent
    for ($i = 0; $i < $maxCheck; $i++) {
        list($dHtml, $dHttp) = ssFetch($products[$i]['url']);
        if (!$dHtml || $dHttp !== 200) continue;
        if ($firstHtml === '') { $firstHtml = $dHtml; $firstUrl = $products[$i]['url']; }
        $sku = ssExtractSku($dHtml);
        if ($sku !== '' && $qNorm !== '' && ssNormCode($sku) === $qNorm) {
            $detailHtml = $dHtml;
            $detailUrl  = $products[$i]['url'];
            $matchedIdx = $i;
            break;
        }
    }
    // Nicio potrivire de SKU → folosim primul rezultat valid (comportament vechi)
    if ($detailHtml === '') {
        $detailHtml = $firstHtml;
        $detailUrl  = $firstUrl ?: $products[0]['url'];
        $matchedIdx = 0;
    }
    // Aducem produsul ales pe poziția 0 (pentru name + lista de alternative)
    if ($matchedIdx > 0) {
        $chosen = $products[$matchedIdx];
        array_splice($products, $matchedIdx, 1);
        array_unshift($products, $chosen);
    }
} elseif (strpos($html, 'product:price:amount') !== false) {
    // search a aterizat direct pe pagina produsului (rezultat unic)
    $detailHtml = $html;
    if (preg_match('/<meta[^>]+property="og:url"[^>]+content="([^"]+)"/i', $html, $ou)) $detailUrl = trim($ou[1]);
    $products[] = ['name' => '', 'url' => $detailUrl];
}

$name = !empty($products) ? $products[0]['name'] : '';
$price = 0;
$pcode = $code;
$image = '';
$description = '';

if ($detailHtml !== '') {
    // --- NUME din og:title (curat) ---
    if (preg_match('/<meta[^>]+property="og:title"[^>]+content="([^"]+)"/i', $detailHtml, $ot)) {
        $ogTitle = html_entity_decode(trim($ot[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $ogTitle = preg_replace('/\s*[-–—]\s*Shop Security\s*$/iu', '', $ogTitle);
        if ($ogTitle !== '') $name = $ogTitle;
    }

    // --- PREȚ: meta product:price:amount (deja în format cu punct, = preț curent/redus) ---
    if (preg_match('/<meta[^>]+property="product:price:amount"[^>]+content="([\d.,]+)"/i', $detailHtml, $pmeta)) {
        $price = floatval(str_replace(',', '.', $pmeta[1]));
    }
    // Fallback preț: <ins> (preț redus) apoi orice woocommerce-Price-amount
    if ($price <= 0 && preg_match('/<ins\b[^>]*>.*?woocommerce-Price-amount[^>]*>\s*<bdi>\s*([\d.\s\xc2\xa0]+,\d{2})/si', $detailHtml, $pi)) {
        $price = ssParsePrice($pi[1]);
    }
    if ($price <= 0 && preg_match('/woocommerce-Price-amount[^>]*>\s*<bdi>\s*([\d.\s\xc2\xa0]+,\d{2})/si', $detailHtml, $pb)) {
        $price = ssParsePrice($pb[1]);
    }

    // --- COD / SKU: din <span class="sku"> ("Cod produs:"), fallback pe JSON "sku":"..." ---
    $skuFound = ssExtractSku($detailHtml);
    if ($skuFound !== '') $pcode = $skuFound;

    // --- IMAGINE: data-large_image (HD din galerie) → wp-post-image → og:image ---
    if (preg_match('/data-large_image="([^"]+)"/i', $detailHtml, $imA)) {
        $image = $imA[1];
    } elseif (preg_match('/<img[^>]*class="[^"]*wp-post-image[^"]*"[^>]*src="([^"]+)"/i', $detailHtml, $imB)) {
        $image = $imB[1];
    } elseif (preg_match('/<meta[^>]+property="og:image"[^>]+content="([^"]+)"/i', $detailHtml, $imC)) {
        $image = $imC[1];
    }

    // --- DESCRIERE: conținutul tab-ului #tab-description (până la tab-ul următor) ---
    if (preg_match('/id="tab-description"[^>]*>(.*?)<div[^>]*id="tab-(?:additional_information|reviews)"/si', $detailHtml, $dT)) {
        $description = $dT[1];
    } elseif (preg_match('/<meta[^>]+property="og:description"[^>]+content="([^"]+)"/i', $detailHtml, $dOg)) {
        $description = $dOg[1];
    }
    // Curățare HTML → text simplu
    if ($description) {
        $description = preg_replace('/<(script|style|noscript)\b[^>]*>.*?<\/\1>/si', '', $description);
        $description = preg_replace('/<br\s*\/?>/i', "\n", $description);
        $description = preg_replace('/<\/p>/i', "\n\n", $description);
        $description = preg_replace('/<\/li>/i', "\n", $description);
        $description = preg_replace('/<li[^>]*>/i', '• ', $description);
        $description = strip_tags($description);
        $description = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $description = preg_replace('/[ \t]+/', ' ', $description);
        $description = preg_replace('/\n[ \t]+/', "\n", $description);
        $description = preg_replace('/\n{3,}/', "\n\n", $description);
        $description = trim($description);
        if (mb_strlen($description) > 2000) {
            $description = mb_substr($description, 0, 1997) . '...';
        }
    }
}

// === CONSTRUIEȘTE RĂSPUNS ===
if (!empty($products) && $price > 0) {
    $nameOriginal = $name;
    // Curăț denumirea: elimin sufixul "- HIKVISION DS-XXXX" / "- DS-XXXX" / "- COD"
    $clean = preg_replace('/\s*[-–—]\s*HIKVISION\s+[\w\-\/.]+$/i', '', $name);
    $clean = preg_replace('/\s*[-–—]\s*' . preg_quote($pcode, '/') . '\s*$/i', '', $clean);
    $clean = preg_replace('/\s*[-–—]\s*DS-[\w\-\/.]+$/i', '', $clean);
    $clean = trim($clean, " -–—");

    $result = [
        'found' => true,
        'name' => $clean ?: $name,
        'nameOriginal' => $nameOriginal,
        'price' => $price,
        'url' => $detailUrl,
        'code' => $pcode,
        'code_match' => ($qNorm !== '' && ssNormCode($pcode) === $qNorm), // true = SKU-ul paginii == codul căutat
        'image' => $image,
        'description' => $description,
        'total_results' => count($products),
    ];

    // Alternative (celelalte rezultate din căutare)
    if (count($products) > 1) {
        $result['alternatives'] = [];
        for ($i = 1; $i < min(count($products), 5); $i++) {
            $result['alternatives'][] = [
                'name' => $products[$i]['name'],
                'url' => $products[$i]['url'],
                'price' => null,
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
            'price' => $price,
            'detail_fetched' => $detailHtml !== '',
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
