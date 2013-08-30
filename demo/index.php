<?php

session_start();

$loader = require '../vendor/autoload.php';

$config = [
    'twitter' => [
        'client_id' => 'C1LNW2hUF8X4qlP3nksB6Q',
        'client_secret' => 'N8NumZfAMjCfYoSYZ2X1f2MBB4zfxWf0AewTvP2hjk',
        'redirect_uri' => 'http://' . $_SERVER['SERVER_NAME'] . '/demo/index.php?provider=twitter'
    ],

];

$adapter = new \SocialAuther\Adapter\Twitter($config['twitter']);

if (!isset($_GET['code'])) {
$url = $adapter->getAuthUrl();

echo "<a href='$url'>$url</a> <br> \n";
} else {
    if (!$adapter->authenticate()) {
        throw new RuntimeException('Not Auth');
    }

    $suid = $adapter->getSocialId();

    echo "<p>USER SOC_ID: $suid</p>";

    var_dump($adapter->getUserInfoRaw());
}