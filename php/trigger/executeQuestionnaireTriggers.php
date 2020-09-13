<?php

include_once("../config.php");
   
/* --- BEGIN SECTION TO CONNECT TO OPALADMIN --- */
/* --- TODO: THIS NEEDS TO BE IN A METHOD --- */
$cypher = null;
while (is_null($cypher)) {
    $cypher = time() % floor( rand() * 20 ) + 103;
}
$creds = array("username" => "TriggerSystem", "password" => "pcGNdtwTV8Pd79FkLhP!ejH8Y^KR&4@u");
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

$ch = curl_init($loginUrl);

# Setting our options
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
# Get the response
$response = curl_exec($ch);
echo "RESPONSE CURL: $response";
curl_close($ch);

/* --- END SECTION TO CONNECT TO OPALADMIN --- */

$trigger = new Trigger();
$sourceModuleId = MODULE_QUESTIONNAIRE; // define what type of trigger this is

// Need patientQuestionnaireSerNum from caller
$trigger->executeTrigger($_POST, $sourceModuleId);

header('Content-Type: application/javascript');
http_response_code(HTTP_STATUS_SUCCESS);

?>
