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
            $readQuery = "SELECT refreshId FROM session WHERE userId='$userId' AND active=1 AND refreshId <> '$requestBody->refreshId'";
            $readCursor = mysqli_query($connection, $readQuery);
            if ($readCursor){
                mysqli_autocommit($connection, FALSE);
                error_log("Updating sessions");
                $updateQuery = "UPDATE session SET active=0 WHERE userId='$userId' AND refreshId <> '$requestBody->refreshId'";
                $updateCursor = mysqli_query($connection, $updateQuery);
                if ($updateCursor){
                    error_log("Deleting all secrets of User: ".$userId);                
                    while ($row=mysqli_fetch_assoc($readCursor)){
                        error_log("Deleting RefreshId: ".$row["refreshId"].": ".unlink("../secret/".$row["refreshId"]."/access.secret"));
                    }
                    mysqli_commit($connection);              
                    $responseBody->statusCode = 1;
                } else {
                    $responseBody->msg = "Couldn't destroy session";
                    $responseBody->cause = $QUERY_ERR;    
                }
            } else {
                $responseBody->msg = "Unable to get your sessions";
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

