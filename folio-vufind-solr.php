<?php
require_once 'HTTP/Request.php';
//require __DIR__ . '/vendor/autoload.php';

//use \Firebase\JWT\JWT;

$tenant = 'andrew';
$bibApiUrl = 'http://localhost:8084/apis/bibs';
$solrBibUrl = 'http://localhost:8080/solr/biblio/update';

/*
// Generate JWT Token
$key = "example_key";
$token = array(
    "iss" => "http://example.org",
    "aud" => "http://example.com",
    "iat" => 1356999524,
    "nbf" => 1357000000
);
$jwt = JWT::encode($token, $key);
*/

$http = new HTTP_Request($bibApiUrl);
$http->setMethod(HTTP_REQUEST_METHOD_GET);
$http->addHeader('X-Okapi-Tenant', $tenant);
$http->addHeader('Authorization', 'andrew'); // Dummy auth token
//$http->addHeader('Authorization', "Bearer $jwt");
$http->addHeader('Content-Type', 'application/json');
$http->addHeader('Accept', 'application/json');

$response = $http->sendRequest();
if (PEAR::isError($response)) {
    echo 'Error: ' . $response->getMessage() . "\n";
} else {
    //echo 'Code: ' . $http->getResponseCode() . "\n";
    //echo 'Body: ' . $http->getResponseBody() . "\n";

    $response = $http->getResponseBody();

    $http = new HTTP_Request($solrBibUrl);
    $http->setMethod(HTTP_REQUEST_METHOD_POST);
    $http->addHeader('Content-Type', 'application/json');

    $records = json_decode($response);

    if (count($records)) {
        echo "Start Indexing\n";
        $docs = array();
        $i=1;
        foreach ($records->bibs as $record) {
            // Hardcoded data due to missing fields in Bib API service
            $docs[] = array('id' => $i,
                            'title' => $record->bib_view->Title,
                            'author' => $record->bib_view->Author,
                            'format' => 'Book',
                            'language' => 'English');
            $i++;
        } 
        $collection = array('add' => $docs);
        $json = json_encode($collection);
        echo "Record: $json\n";
        $http->setBody($json);
        $response = $http->sendRequest();

        if (PEAR::isError($response)) {
            echo 'Error: ' . $response->getMessage() . "\n";
        } else {
            echo 'Code: ' . $http->getResponseCode() . "\n";
            echo 'Body: ' . $http->getResponseBody() . "\n";
        }
    } else {
        echo 'no records found';
    }
}

echo "\n";

?>