<?php
// ============================================================
// CSSI Portal — CRON Social Media Publisher
// Rulează la fiecare minut via cPanel Cron Jobs
// Comandă cPanel: php /home/r101042brea/cssi.ro/admin/cron-social.php
// ============================================================

require_once __DIR__ . '/db.php';
date_default_timezone_set('Europe/Bucharest');

$db = getDB();
$now = date('Y-m-d H:i:s');
$zernioKey = 'sk_c7b7a4f08d5bab22497ab169e58313a02d6ef47ead1d2bfa39b5bd7237fd76c0';
$baseUrl = 'https://cssi.ro';

// Găsește postările programate care trebuie publicate
$stmt = $db->prepare("SELECT * FROM social_posts WHERE status = 'Programat' AND data_programare <= ? AND data_programare IS NOT NULL");
$stmt->execute([$now]);
$posts = $stmt->fetchAll();

if (empty($posts)) {
    echo "[" . $now . "] Nicio postare de publicat.\n";
    exit;
}

echo "[" . $now . "] " . count($posts) . " postări de publicat.\n";

$platMap = ['fb'=>'facebook','ig'=>'instagram','linkedin'=>'linkedin','yt'=>'youtube','tiktok'=>'tiktok','x'=>'twitter'];

// Fetch connected Zernio accounts
$chAccounts = curl_init('https://zernio.com/api/v1/accounts');
curl_setopt_array($chAccounts, [
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $zernioKey],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15
]);
$accountsResp = json_decode(curl_exec($chAccounts), true);
curl_close($chAccounts);
$accountMap = [];
if (!empty($accountsResp['accounts'])) {
    foreach ($accountsResp['accounts'] as $acc) {
        if ($acc['isActive']) $accountMap[$acc['platform']] = $acc['_id'];
    }
}
echo "Conturi Zernio conectate: " . implode(', ', array_keys($accountMap)) . "\n";

foreach ($posts as $post) {
    $platforme = json_decode($post['platforme'] ?: '[]', true);
    
    $zernioPlats = [];
    foreach ($platforme as $p) {
        $platName = isset($platMap[$p]) ? $platMap[$p] : $p;
        if (isset($accountMap[$platName])) {
            $zernioPlats[] = ['platform' => $platName, 'accountId' => $accountMap[$platName]];
        }
    }
    if (empty($zernioPlats)) {
        echo "  ⏭️ Post #{$post['id']} - niciun cont conectat pentru platformele selectate, skip.\n";
        continue;
    }

    $payload = [
        'content' => $post['continut'],
        'platforms' => $zernioPlats,
        'publishNow' => true
    ];

    // Media as mediaItems [{type, url}]
    if (!empty($post['media_json'])) {
        $mediaFiles = json_decode($post['media_json'], true);
        if (is_array($mediaFiles) && count($mediaFiles)) {
            $payload['mediaItems'] = array_map(function($f) use ($baseUrl) {
                return ['type' => $f['type'] ?: 'image', 'url' => $baseUrl . $f['url']];
            }, $mediaFiles);
        }
    } elseif (!empty($post['imagine_url'])) {
        $imgUrl = $post['imagine_url'];
        if (strpos($imgUrl, 'http') !== 0) $imgUrl = $baseUrl . $imgUrl;
        $payload['mediaItems'] = [['type' => 'image', 'url' => $imgUrl]];
    }

    // Trimite la Zernio
    $ch = curl_init('https://zernio.com/api/v1/posts');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $zernioKey,
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        $db->prepare("UPDATE social_posts SET status = 'Publicat', external_ids = ? WHERE id = ?")
           ->execute([$response, $post['id']]);
        echo "  ✅ Post #{$post['id']} publicat cu succes.\n";
    } else {
        $db->prepare("UPDATE social_posts SET status = 'Eroare', external_ids = ? WHERE id = ?")
           ->execute([$response, $post['id']]);
        echo "  ❌ Post #{$post['id']} eroare: {$response}\n";
    }
}

echo "Done.\n";
