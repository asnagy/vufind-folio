<?php
require_once 'HTTP/Request.php';
//require __DIR__ . '/vendor/autoload.php';

//use \Firebase\JWT\JWT;

// -- Configuration --
$configs = parse_ini_file('folio-vufind.ini', true);
$tenant = $configs['FOLIO']['tenant'];
$bibApiUrl = $configs['FOLIO']['bibApiUrl'];
$solrBibUrl = $configs['VuFind']['solrUrl'];

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

// Autoincrement Record ID
$id = 1;

// Paging
$page = 0;

// Records from BIBS API
$recordsExist = true;

echo "Start Indexing\n";

while ($recordsExist) {
    $http = new HTTP_Request($bibApiUrl . '?limit=100&offset=' . 100*$page);
    $http->setMethod(HTTP_REQUEST_METHOD_GET);
    $http->addHeader('X-Okapi-Tenant', $tenant);
    $http->addHeader('Authorization', 'andrew'); // Dummy auth token
    //$http->addHeader('Authorization', "Bearer $jwt");
    $http->addHeader('Content-Type', 'application/json');
    $http->addHeader('Accept', 'application/json');
    $response = $http->sendRequest();

    if (PEAR::isError($response)) {
        die('Error: ' . $response->getMessage() . "\n");
    }
    $response = $http->getResponseBody();
    $records = json_decode($response);
    $recordsExist = count($records->bibs);
    if ($recordsExist) {
        echo "Page: " . 100*$page . " | Records: $recordsExist\n";

        $docs = array();
        foreach ($records->bibs as $record) {
            // Hardcoded data due to missing fields in Bib API service
            $docs[] = array('id' => $id,
                            'title' => $record->bib_view->Title,
                            'author' => $record->bib_view->Author,
                            'format' => 'Book',
                            'language' => 'English');
            $id++;
        } 
        $collection = array('add' => $docs);
        $json = json_encode($collection);
        echo "Record: $json\n";

        // Push to records to SOLR
        $http = new HTTP_Request($solrBibUrl . '/update?commit=true');
        $http->setMethod(HTTP_REQUEST_METHOD_POST);
        $http->addHeader('Content-Type', 'application/json');
        $http->setBody($json);
        $response = $http->sendRequest();

        if (PEAR::isError($response)) {
            echo 'Error: ' . $response->getMessage() . "\n";
        } else {
            echo 'Code: ' . $http->getResponseCode() . "\n";
            echo 'Body: ' . $http->getResponseBody() . "\n";
            $page++;

            // Killing the process after the inital loop due to issue with offset param
            die();
        }
    } else {
        echo 'no records found';
    }
}

echo "\n";

?>
