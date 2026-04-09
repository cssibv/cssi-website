<?php
// ============================================================
// CSSI Portal — CRON Social Media Publisher
// Rulează la fiecare minut via cPanel Cron Jobs
// Comandă cPanel: php /home/r101042brea/cssi.ro/admin/cron-social.php
// ============================================================

require_once __DIR__ . '/db.php';

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

foreach ($posts as $post) {
    $platforme = json_decode($post['platforme'] ?: '[]', true);
    
    $payload = [
        'content' => $post['continut'],
        'platforms' => array_map(function($p) use ($platMap) {
            return isset($platMap[$p]) ? $platMap[$p] : $p;
        }, $platforme)
    ];

    // Media din media_json sau imagine_url
    if (!empty($post['media_json'])) {
        $mediaFiles = json_decode($post['media_json'], true);
        if (is_array($mediaFiles) && count($mediaFiles)) {
            $payload['mediaUrls'] = array_map(function($f) use ($baseUrl) {
                return $baseUrl . $f['url'];
            }, $mediaFiles);
        }
    } elseif (!empty($post['imagine_url'])) {
        $payload['mediaUrls'] = [$post['imagine_url']];
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
