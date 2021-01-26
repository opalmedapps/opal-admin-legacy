<?php

include_once("../config.php");

header('Content-Type: application/javascript');


$url = 'https://localhost/opalAdmin/user/system-login';

$postFields = array(
    "username"=>"TriggerSystem",
    "password"=>"pcGNdtwTV8Pd79FkLhP!ejH8Y^KR&4@u"
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_COOKIESESSION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);
$header_size = curl_getinfo($ch);
var_dump($result);

echo "\r\n\r\nlocal server session id: ".$_COOKIE['PHPSESSID']."\r\n\r\n";

if(preg_match("/PHPSESSID=(.*?)(?:;|\r\n)/", $result, $matches)){
    $phpSessionId = $matches[1];
    echo "\r\n\r\nremote server session id: $phpSessionId\r\n\r\n";
}else{
    die("Error!");
    /* Do something */
}


$info = curl_getinfo($ch);
curl_close($ch);

if($info["http_code"] == 200) {
    $url = 'https://localhost/opalAdmin/trigger/execute/questionnaire-triggers';
    $postFields = array(
        "id" => 219
    );
    $strCookie = 'PHPSESSID=' . $phpSessionId . '; path=/';

    echo "\r\n\r\n$strCookie\r\n\r\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_COOKIE, $strCookie );
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
    curl_setopt($ch, CURLOPT_HEADER, 1);

    session_write_close();

    $result = curl_exec($ch);
    $header_size = curl_getinfo($ch);
    var_dump($result);
    curl_close($ch);
}

?> 