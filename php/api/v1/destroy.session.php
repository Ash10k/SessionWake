<?php

require_once("../lib.v1.php");

header($CORS);
header("Access-Control-Allow-Headers: Authorization, Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { 
    error_log("Exiting");
    exit(0);
} else {
    $requestHeaders = apache_request_headers();
    $request = file_get_contents("php://input");
    $requestBody = array();
    $requestBody = json_decode($request);
    
    @$responseBody = NULL;
    @$responseBody->statusCode = 0;
    $connection = establishConnection();
    if ($connection){
        error_log("Retrieved in header: ".$requestHeaders["Authorization"]);
        $userId = validateToken($requestHeaders["Authorization"], 3, $requestHeaders['Referer'], $requestHeaders['Origin'], $requestHeaders['User-Agent']);            
        if ($userId){
            $query = "UPDATE session SET active=0 WHERE userId='$userId' AND refreshId='$requestBody->refreshId'";
            $cursor = mysqli_query($connection, $query);
            if ($cursor){
                error_log("Deleting secret of RefreshID: ".$requestBody->refreshId.": ".unlink("../secret/".$requestBody->refreshId."/access.secret"));                
                $responseBody->statusCode = 1;
            } else {
                $responseBody->msg = "Couldn't destroy session";
                $responseBody->cause = $QUERY_ERR;    
            }
        } else {
            $responseBody->msg = "Unauthorized";            
            http_response_code(401);
        }
        mysqli_close($connection);
    } else {
        $responseBody->msg = "Server refused to connect";
        $responseBody->cause = $CONNECTION_ERR;
    }
    echo json_encode($responseBody);
    exit(0);
    
}

