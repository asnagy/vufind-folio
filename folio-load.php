<?php
require_once 'HTTP/Request.php';
require_once 'File/MARC.php';
//require __DIR__ . '/vendor/autoload.php';

//use \Firebase\JWT\JWT;

// --------- Configuration ------------
$configs = parse_ini_file('folio-vufind.ini', true);
$tenant = $configs['FOLIO']['tenant'];
$bibApiUrl = $configs['FOLIO']['bibApiUrl'];

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

echo "POST Bibs\n";
$i=0;
$marc = new File_MARC('load.mrc', File_MARC::SOURCE_FILE);
while ($record = $marc->next()) {
    $http = new HTTP_Request($bibApiUrl);
    $http->setMethod(HTTP_REQUEST_METHOD_POST);
    $http->addHeader('X-Okapi-Tenant', $tenant);
    $http->addHeader('Authorization', 'andrew');
    //$http->addHeader('Authorization', "Bearer $jwt");
    $http->addHeader('Content-Type', 'application/json');
    $http->addHeader('Accept', 'application/json');

    $bibrecord = array('bib_view' => 
    				array('Title' => ($record->getField('245') ? $record->getField('245')->getSubfield('a')->getData() : 'Missing Data'),
                          'Author' => ($record->getField('100') ? $record->getField('100')->getSubfield('a')->getData() : 'Missing Data'),
                          'publication_date' => str_replace('.', '', $record->getField('260')->getSubfield('c')->getData()),
                          'desc' => $record->toRaw())
                      );
    $json = json_encode($bibrecord);
    $http->setBody($json);
    echo "$json\n";
    $response = $http->sendRequest();
    if (PEAR::isError($response)) {
        echo 'Error: ' . $response->getMessage() . "\n";
    } else {
        echo 'Code: ' . $http->getResponseCode() . "\n";
        echo 'Body: ' . $http->getResponseBody() . "\n";
        $i++;
    }
}
echo "Records Submitted: $i";
echo "\n";

?>
