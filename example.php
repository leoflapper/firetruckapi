<?php

include 'vendor/autoload.php';

$client = new FireTruck\API\Client('APIKEY');
$client->setVerify(false);


try {
    $response = $client->get('domains/DOMAINHASH');
} catch(FireTruck\API\Exception\FiretruckAPIException $e) {
    $response = $e;
}

echo '<pre>'; print_r($response->getBody()); echo '</pre>'; die();

?>