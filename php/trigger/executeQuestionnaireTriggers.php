<?php

include_once("../config.php");
            
$cypher = null;
while (is_null($cypher)) {
    $cypher = time() % floor( rand() * 20 ) + 103;
}
$creds->Username = "TriggerSystem";
$creds->Password = "pcGNdtwTV8Pd79FkLhP!ejH8Y^KR&4@u";
$toEncrypt =  strval(json_encode($creds));

$encrypted = Encrypt::encodeStringSystem($toEncrypt, $cypher);
$loginUrl = "https://lxkvmap97/opalAdmin/user/system-login";

# Our new data
$postData = array(
    'encrypted' => $encrypted,
    'cypher' => $cypher
);
# Form data string
$postString = http_build_query($postData, '', '&');

# You can also check..
if(function_exists('http_post_data') == false) {
    $ch = curl_init($loginUrl);
  
    # Setting our options
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    # Get the response
    $response = curl_exec($ch);
    echo "RESPONSE CURL: $response";
    curl_close($ch);
}

else {
    $response = http_post_data($url, $postString);
    echo "RESPONSE OTHER: $response";
}

die();

$trigger = new Trigger(true); // guest status on for now
$triggerType = MODULE_QUESTIONNAIRE; // define what type of trigger this is

$trigger->executeTrigger($_POST, $triggerType);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);

?>