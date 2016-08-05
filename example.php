<?php


include 'vendor/autoload.php';

$client = new FireTruck\API\Client('a2047ea0b10a08dac0d2fa6bcb14d6171a7cf584');
$client->setVerify(false);


try {
    $response = $client->get('domains/5791c9ef0f9d1f0001bdb56a');
} catch(FireTruck\API\Exception\FiretruckAPIException $e) {
    $response = $e;
}

echo '<pre>'; print_r($response->getBody()); echo '</pre>'; die();


