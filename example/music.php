<?php
header("Content-Type: application/json");
include __DIR__."/../vendor/autoload.php";
$api = new \Sovit\TikTokPrivate\Api([
    "api_key"=>"XXX",
]);
$result = $api->getMusic("6886941451360405505");
echo json_encode($result,JSON_PRETTY_PRINT);
