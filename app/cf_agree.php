<?php
$apiKey = 'cfut_sHGSlvFy9NJN2ipfHAuxAFI19VD1M25johA35o2X442a7f24';
$accountId = '59553552eee83859b46df4205cf372f0';
$model = '@cf/meta/llama-3.2-11b-vision-instruct';

$url = "https://api.cloudflare.com/client/v4/accounts/{$accountId}/ai/run/{$model}";
$payload = json_encode(['messages' => [['role' => 'user', 'content' => 'agree']]]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($resp, true);
$ok = ($code === 200 && isset($data['result']['response']));
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Cloudflare Agree</title></head>
<body style="font-family:sans-serif;padding:40px;max-width:600px">
<?php if($ok): ?>
    <h2 style="color:green">✅ Đồng ý license thành công!</h2>
    <p>Response: <b><?= htmlspecialchars($data['result']['response']) ?></b></p>
    <p>Bây giờ AI đã hoạt động. <a href="admin/ai_generator">Quay lại trang AI</a></p>
<?php else: ?>
    <h2 style="color:red">❌ Thất bại</h2>
    <p>HTTP Code: <?= $code ?></p>
    <pre><?= htmlspecialchars($resp) ?></pre>
<?php endif; ?>
</body>
</html>
<?php
// Xóa file này sau khi dùng xong
// unlink(__FILE__);
?>
