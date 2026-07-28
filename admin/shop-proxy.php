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

$code      = isset($_GET['q'])   ? trim($_GET['q'])   : '';
$directUrl = isset($_GET['url']) ? trim($_GET['url']) : ''; // mod „link direct": user dă URL-ul produsului
if ($code === '' && $directUrl === '') {
    echo json_encode(['error' => 'Cod produs sau link lipsă', 'found' => false]);
    exit;
}
// Link direct acceptat DOAR de pe shop-security.ro (anti-SSRF)
if ($directUrl !== '' && !preg_match('#^https?://(www\.)?shop-security\.ro/#i', $directUrl)) {
    echo json_encode(['found' => false, 'error' => 'Link invalid — acceptăm doar adrese de pe shop-security.ro']);
    exit;
}

// Caută pe shop-security.ro (platformă WooCommerce/WoodMart din 2026)
// Căutarea WooCommerce + FiboSearch (dgwt_wcas=1) — rezultate mai relevante, conștiente de SKU
$searchUrl = 'https://www.shop-security.ro/?s=' . urlencode($code) . '&post_type=product&dgwt_wcas=1';

// Helper: fetch HTML cu user-agent de browser. Întoarce [body, http, errno, error].
function ssFetch($url, $connTimeout = 12, $timeout = 30) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_CONNECTTIMEOUT => $connTimeout,
        CURLOPT_TIMEOUT => $timeout,
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
    $eff   = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // URL final după redirect(uri)
    curl_close($ch);
    return [$body, $http, $errno, $error, $eff];
}

// Helper: preț românesc "1.234,56" sau "411,40" → float 1234.56 / 411.40
function ssParsePrice($raw) {
    $raw = trim($raw);
    // scot separatorul de mii (.) și transform virgula zecimală în punct
    $raw = str_replace(['.', ' ', "\xc2\xa0"], '', $raw); // elimină . spațiu &nbsp;
    $raw = str_replace(',', '.', $raw);
    return floatval($raw);
}

// Helper: normalizează un cod pentru comparație EXACTĂ dar imună la formatare.
// Codul de la furnizor și SKU-ul de pe site sunt „identice" ca semnificație, dar pot
// diferi prin separatori/spații/majuscule: "DS-2CD2043G2-LI2U-2.8mm" vs "DS-2CD2043G2-LI2U 2.8MM"
// vs "DS-2CD2043G2-LI2U/SL 2.8mm". Reducem la DOAR litere+cifre ca să se potrivească robust,
// FĂRĂ să colapsăm diferențe reale (literele rămân: -SL, -BLACK etc. produc coduri diferite).
function ssNormCode($s) {
    $s = mb_strtolower(trim((string)$s), 'UTF-8');
    return preg_replace('/[^a-z0-9]+/', '', $s);
}

// Helper: taie o dimensiune de lentilă de la FINALUL codului: "2.8mm", "4mm", "2.8-12mm".
// Magazinul lipește lentila de SKU ("DS-2CD2143G2-IS 2.8MM"), dar furnizorul dă codul de
// bază fără ea ("DS-2CD2143G2-IS"). Tăiem DOAR sufixul mm — restul (-SL, /4G, -BLACK)
// rămâne intact, ca să nu confundăm variante reale de produs.
function ssStripLensSuffix($s) {
    return preg_replace('#[\s\-/]*\d+(?:[.,]\d+)?(?:\s*-\s*\d+(?:[.,]\d+)?)?\s*mm\s*$#i', '', trim((string)$s));
}

// Helper: SKU-ul paginii se potrivește cu codul căutat? Exact, sau „cod de bază + lentilă".
function ssCodeMatches($sku, $qNorm) {
    if ($qNorm === '' || (string)$sku === '') return false;
    if (ssNormCode($sku) === $qNorm) return true;               // potrivire exactă
    return ssNormCode(ssStripLensSuffix($sku)) === $qNorm;       // cod căutat + sufix lentilă
}

// Helper: extrage prețul (curent/redus) dintr-o pagină de produs. 0 dacă nu găsește.
function ssExtractPrice($html) {
    if (preg_match('/<meta[^>]+property="product:price:amount"[^>]+content="([\d.,]+)"/i', $html, $pmeta)) {
        $p = floatval(str_replace(',', '.', $pmeta[1]));
        if ($p > 0) return $p;
    }
    if (preg_match('/<ins\b[^>]*>.*?woocommerce-Price-amount[^>]*>\s*<bdi>\s*([\d.\s\xc2\xa0]+,\d{2})/si', $html, $pi)) {
        $p = ssParsePrice($pi[1]); if ($p > 0) return $p;
    }
    if (preg_match('/woocommerce-Price-amount[^>]*>\s*<bdi>\s*([\d.\s\xc2\xa0]+,\d{2})/si', $html, $pb)) {
        $p = ssParsePrice($pb[1]); if ($p > 0) return $p;
    }
    return 0;
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

$qNorm      = ssNormCode($code);
$products   = [];
$scanned    = []; // paginile de produs deschise în scanare: [name, url, sku, price]
$detailHtml = '';
$detailUrl  = '';
$exactMatch = false;

if ($directUrl !== '') {
    // ─── MOD LINK DIRECT: utilizatorul a dat URL-ul exact al produsului ───
    list($dHtml, $dHttp) = ssFetch($directUrl);
    if (!$dHtml || $dHttp !== 200) {
        echo json_encode(['found' => false, 'error' => 'Nu s-a putut accesa link-ul (HTTP ' . $dHttp . ')']);
        exit;
    }
    $detailHtml = $dHtml;
    $detailUrl  = $directUrl;
    $products[] = ['name' => '', 'url' => $directUrl];
    $exactMatch = true; // user a ales explicit produsul → îl acceptăm ca atare

} else {
    // ─── MOD DUPĂ COD: caut și aleg DOAR produsul cu SKU identic ───
    list($html, $httpCode, $curlErrno, $curlError, $effUrl) = ssFetch($searchUrl);
    if (!$html || $httpCode !== 200) {
        echo json_encode([
            'found' => false,
            'error' => 'Nu s-a putut accesa shop-security.ro (HTTP ' . $httpCode . ')',
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

    // ─── CAZ: FiboSearch a găsit o potrivire UNICĂ și a redirectat direct pe pagina produsului.
    // Atunci pagina NU e o listă de rezultate, ci detaliul produsului (are product:price:amount).
    // Cardurile wd-entities-title de aici sunt produse ÎNRUDITE, nu produsul căutat — deci verificăm
    // ÎNTÂI SKU-ul produsului principal (din <span class="sku">), altfel produsele cu match perfect
    // (exact cele pentru care shop-ul redirectează) ar fi ratate. ───
    $effPath = trim((string)parse_url((string)$effUrl, PHP_URL_PATH), '/');
    $landedOnProduct = ($effPath !== '' && strpos($effPath, '/') === false
                        && strpos($html, 'product:price:amount') !== false);
    if ($landedOnProduct) {
        $skuMain = ssExtractSku($html); // <span class="sku"> = produsul principal al paginii
        if ($skuMain !== '' && ssCodeMatches($skuMain, $qNorm)) {
            $detailHtml = $html;
            $detailUrl  = $effUrl;
            $products[] = ['name' => '', 'url' => $effUrl];
            $exactMatch = true;
        }
    }

    // Produse din pagina de rezultate (card: <h3 class="wd-entities-title"><a href="URL">NUME</a>)
    // (sărim dacă am prins deja produsul principal prin redirect)
    if (!$exactMatch) {
        $seenUrls = [];
        if (preg_match_all('/<h[1-6][^>]*class="[^"]*wd-entities-title[^"]*"[^>]*>\s*<a[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/si', $html, $tm, PREG_SET_ORDER)) {
            foreach ($tm as $m) {
                $url  = trim($m[1]);
                $name = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($name === '' || isset($seenUrls[$url])) continue;
                $path = trim((string)parse_url($url, PHP_URL_PATH), '/');
                if ($path === '' || strpos($path, '/') !== false) continue; // sare peste /product-category/... etc
                $seenUrls[$url] = true;
                $products[] = ['name' => $name, 'url' => $url];
            }
        }
    }

    // Aleg DOAR produsul al cărui <span class="sku"> == codul căutat.
    // SKU-ul NU apare pe cardurile din lista de rezultate → trebuie deschisă pagina fiecărui produs.
    // Optimizare: deschid întâi produsele a căror DENUMIRE se potrivește cel mai bine cu termenul
    // căutat (cel corect e de regulă printre ele), ca să nu deschid zeci de pagini irelevante.
    if (!$exactMatch && !empty($products)) {
        $nameTokens = preg_split('/\s+/', mb_strtolower(trim($code), 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY);
        $ranked = [];
        foreach ($products as $idx => $p) {
            $nm = mb_strtolower($p['name'], 'UTF-8');
            $score = 0;
            foreach ($nameTokens as $t) { if ($t !== '' && mb_strpos($nm, $t) !== false) $score++; }
            $ranked[] = ['p' => $p, 'i' => $idx, 's' => $score];
        }
        usort($ranked, function ($a, $b) {
            if ($a['s'] !== $b['s']) return $b['s'] - $a['s']; // denumire mai relevantă întâi
            return $a['i'] - $b['i'];                          // egalitate → ordinea magazinului
        });

        $maxCheck = min(count($ranked), 20); // câte pagini deschidem cel mult ca să găsim SKU-ul exact
        $lensHits = []; // produse „cod căutat + lentilă" (fără potrivire exactă): [cand, html]
        for ($k = 0; $k < $maxCheck; $k++) {
            $cand = $ranked[$k]['p'];
            list($dHtml, $dHttp) = ssFetch($cand['url'], 8, 15); // timeout scurt — pot fi multe probe
            if (!$dHtml || $dHttp !== 200) continue;
            $sku = ssExtractSku($dHtml);
            // Rețin ce am descărcat (SKU + preț) ca să pot arăta un selector de variante
            // dacă nu iese potrivire exactă — fără a mai descărca paginile a doua oară.
            $scanned[] = ['name' => $cand['name'], 'url' => $cand['url'], 'sku' => $sku, 'price' => ssExtractPrice($dHtml)];
            if ($sku === '' || $qNorm === '') continue;
            if (ssNormCode($sku) === $qNorm) {
                $detailHtml = $dHtml;
                $detailUrl  = $cand['url'];
                // aduc produsul ales pe poziția 0 (pentru name + lista de alternative)
                $rest = [];
                foreach ($products as $x) { if ($x['url'] !== $cand['url']) $rest[] = $x; }
                $products = array_merge([$cand], $rest);
                $exactMatch = true;
                break;
            }
            // Nu e exact, dar SKU-ul = codul căutat + o lentilă → candidat pe lentilă.
            if (ssNormCode(ssStripLensSuffix($sku)) === $qNorm) {
                $lensHits[] = ['cand' => $cand, 'html' => $dHtml];
            }
        }
        // Fără potrivire exactă, dar EXACT o singură variantă = codul căutat + o lentilă → o acceptăm.
        // (mai multe lentile ⇒ prețuri diferite ⇒ le lăsăm în selector ca userul să aleagă)
        if (!$exactMatch && count($lensHits) === 1) {
            $cand = $lensHits[0]['cand'];
            $detailHtml = $lensHits[0]['html'];
            $detailUrl  = $cand['url'];
            $rest = [];
            foreach ($products as $x) { if ($x['url'] !== $cand['url']) $rest[] = $x; }
            $products = array_merge([$cand], $rest);
            $exactMatch = true;
        }
    } elseif (!$exactMatch && strpos($html, 'product:price:amount') !== false) {
        // search a aterizat direct pe pagina unui produs — accept doar dacă SKU-ul se potrivește
        $skuDirect = ssExtractSku($html);
        if ($skuDirect !== '' && ssCodeMatches($skuDirect, $qNorm)) {
            $detailHtml = $html;
            if (preg_match('/<meta[^>]+property="og:url"[^>]+content="([^"]+)"/i', $html, $ou)) $detailUrl = trim($ou[1]);
            $products[] = ['name' => '', 'url' => $detailUrl];
            $exactMatch = true;
        }
    }

    // Niciun produs cu codul EXACT → nu ghicim: arătăm variantele găsite (SKU + preț)
    // ca utilizatorul să aleagă cea corectă cu un click. Fallback: link manual.
    if (!$exactMatch) {
        $cand = [];
        // Preferăm candidații deja scanați (au SKU + preț) în ordinea relevanței;
        // dacă lista de scanare e goală, cădem pe cardurile brute (doar nume + link).
        $src = !empty($scanned) ? $scanned : array_slice($products, 0, 8);
        foreach (array_slice($src, 0, 8) as $p) {
            $cand[] = [
                'name'  => isset($p['name'])  ? $p['name']  : '',
                'url'   => isset($p['url'])   ? $p['url']   : '',
                'sku'   => isset($p['sku'])   ? $p['sku']   : '',
                'price' => (isset($p['price']) && $p['price'] > 0) ? $p['price'] : null,
            ];
        }
        echo json_encode([
            'found'      => false,
            'need_url'   => true,
            'error'      => 'Niciun produs cu codul exact „' . $code . '". Alege varianta corectă din listă sau lipește link-ul produsului.',
            'search_url' => $searchUrl,
            'candidates' => $cand,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
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

    // --- PREȚ: meta product:price:amount, fallback <ins>/woocommerce-Price-amount ---
    $price = ssExtractPrice($detailHtml);

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
        'code_match' => ssCodeMatches($pcode, $qNorm), // true = SKU-ul paginii == codul căutat (exact sau + lentilă)
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
