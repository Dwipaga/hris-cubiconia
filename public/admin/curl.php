<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $_ENV['URL_SIKI'] . 'siki-api/login',
  CURLOPT_RETURNTRANSFER => true,
  // CURLOPT_SSL_VERIFYHOST => 0,
  // CURLOPT_SSL_VERIFYPEER => 0,
  // CURLOPT_SSLVERSION => 1,
  CURLOPT_VERBOSE => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => '{
    "email":"lsppwk@gmail.com",
    "password":"srKdYz@5u"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json'
  ),
));
$response = curl_exec($curl);
if ($response === false) {
  echo 'Curl error: ' . curl_error($curl);
} else {
  echo $response;
}

curl_close($curl);
