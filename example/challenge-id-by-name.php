<?php
header("Content-Type: application/json");
include __DIR__."/../vendor/autoload.php";
$api = new \Sovit\TikTokPrivate\Api([
    "api_key"=>"XXX",
]);
$result = $api->getChallengeID('bushit');
echo json_encode($result,JSON_PRETTY_PRINT);
