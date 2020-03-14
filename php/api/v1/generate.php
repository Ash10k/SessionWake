<?php

require_once("../lib.v1.php");

header($CORS);

function generateQRValues($seriesPrefix, $count, $dateStamp){
    $valArr = array();    
    for ($i=0; $i<$count; $i++){        
        $val = $seriesPrefix.substr(bin2hex(openssl_random_pseudo_bytes(ceil(5))), 0, 10);
        array_push($valArr, $val."-".$dateStamp);
    }
    return $valArr;
}

$requestHeaders = apache_request_headers();
$request = file_get_contents("php://input");
$requestBody = array();
$requestBody = json_decode($request);

@$responseBody = NULL;
@$responseBody->statusCode = 0;

if (validateToken($requestHeaders['Authorization'], $AUTH_LV_ADMIN, $requestHeaders['Referer'], $requestHeaders['Origin'], $requestHeaders['User-Agent'])){
    $dateStamp = exec('date +%s');
    $valArr = generateQRValues($requestBody->seriesPrefix, $requestBody->count, $dateStamp);
    $connection = establishConnection();
    if ($connection){        
        $query = "INSERT INTO series VALUES ('$requestBody->seriesPrefix', '$dateStamp', $requestBody->count, '$requestBody->project')";
        $cursor = mysqli_query($connection, $query);
        if ($cursor){
            $responseBody->statusCode = 1;
            $responseBody->QRs = $valArr;
        } else {
            error_log($query);
            $responseBody->msg = "Unable to store series";
            $responseBody->cause = $QUERY_ERR;
        }
        mysqli_close($connection);
    } else {
        $responseBody->msg = "Server refused to connect";
        $responseBody->cause = $CONNECTION_ERR;
    }
} else {
    $responseBody->msg = "Authorization error";
    $responseBody->cause = $INVALID_TOKEN;
}
echo json_encode($responseBody);
exit(0);