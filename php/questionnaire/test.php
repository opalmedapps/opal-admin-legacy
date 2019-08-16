<?php
/**
 * Created by PhpStorm.
 * User: Dominic Bourdua
 * Date: 7/4/2019
 * Time: 9:30 AM
 */

include_once('questionnaire.inc');


$testUser = array(
    "id"=>"20",
    "language"=>"EN",
    "role"=>"admin",
    "sessionid"=>"oX5E3EMI0u",
    "username"=>"dbourdua",
);


$testToken = "jzc7eyHo6NWs4ykSfgou2neZ//lx4wFTpl3mBdwJaQGqKnoTTKhoMnIU7dAzyY7DN2cIXsULZle60iuFpil01ITZXZFaYV1I9SyxyU4ydfdMVVEppLTfUUZElASqzHwX+bhRKeSvEKvTPnoO5XcQzxbfRpgBmfaal8Qv6Vs85sN/fz1MfYzZT9+J8aIDwIkkpgVph22eYkDD7ghvey5evuWR7WfAxIQCoyjcpzhTYdrme5qKuEge3KnCLga/WpGiDFu/Nw6aP/e30E7CjPIykgWIrVNbhnu7krGaPO3Z4bI=";
$validator = new SecurityToken($testToken, true);
$token = $validator->updateToken();

//$validator = new SecurityToken();
//$token = $validator->generateNewToken($testUser);



print_r($validator->getData());
print "\r\n$token\r\n";



//$validator = new SecurityToken();
//
//


//
//print "token: ".$validator->getToken()."<br/><br/>";
//
//$userDecoded = $validator->decryptToken();
//
//print "decoded token: ";
//print_r($userDecoded);


//print "<br/><br/>new token:<br/><br/>" . $validator->updateToken();



die("<br/>done");