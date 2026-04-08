<?php
require __DIR__ . '/../vendor/autoload.php';

$jar = new \GuzzleHttp\Cookie\CookieJar();
$client = new \GuzzleHttp\Client([
    'timeout' => 15,
    'allow_redirects' => true,
    'cookies' => $jar,
    'headers' => ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'Accept-Language' => 'ja']
]);

$client->get('https://www.mgstage.com/');
$jar->setCookie(new \GuzzleHttp\Cookie\SetCookie(['Name'=>'adc','Value'=>'1','Domain'=>'.mgstage.com','Path'=>'/']));

// 涼森れむで検索
$name = '涼森れむ';
$encoded = urlencode($name);
$url = "https://www.mgstage.com/search/cSearch.php?type=actress&keyword={$encoded}";

$res = $client->get($url);
$html = (string)$res->getBody();

// 涼森が含まれる行を探す
$lines = explode("\n", $html);
foreach ($lines as $i => $line) {
    if (str_contains($line, '涼森') || str_contains($line, 'suzumori') || str_contains($line, 'REMU')) {
        echo "Line $i: " . trim($line) . "\n";
    }
}

echo "\n--- href パターン調査 ---\n";
preg_match_all('/href="([^"]*actress[^"]*)"/', $html, $m1);
echo "actress含むhref: " . implode("\n", array_slice(array_unique($m1[1]), 0, 10)) . "\n";

preg_match_all('/href="([^"]*actor[^"]*)"/', $html, $m2);
echo "actor含むhref: " . implode("\n", array_slice(array_unique($m2[1]), 0, 10)) . "\n";

// product_detailリンクを探す
preg_match_all('/href="(\/product\/product_detail\/[^"]+)"/', $html, $m3);
echo "\nproduct links (first 5): " . implode(", ", array_slice($m3[1], 0, 5)) . "\n";
