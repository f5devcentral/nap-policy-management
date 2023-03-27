<?php

$headers = array(


'Content-Type: application/json',
'Accept: application/json, text/javascript, */*; ',
'Authorization: Bearer BBDC-MjcxMjgzMDU1MDc4OnkDH+UAEWOOyM74Pk5SIAj/kgOh'
);

$url = "http://192.168.2.100:7990/rest/api/latest/projects/pol/repos/nap/raw/my-ap-policy.yaml";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl,CURLOPT_TIMEOUT,5);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

$policy_data = curl_exec($curl);
$test = $policy_data;



#save policy to file
file_put_contents("test.yaml",$test);

?>