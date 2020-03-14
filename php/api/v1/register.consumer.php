<?php

require_once("../lib.v1.php");

header($CORS);

$requestHeaders = apache_request_headers();
$request = file_get_contents("php://input");
$requestBody = array();
$requestBody = json_decode($request);

@$responseBody = NULL;
@$responseBody->statusCode = 0;

$connection = establishConnection();
if ($connection){
    $userId = getNewUserId();
    $username = $requestBody->username;
    $password = $requestBody->password;
    $authLevel = $AUTH_LV_CONSUMER;
    mysqli_autocommit($connection, FALSE);
    $query = "INSERT INTO user VALUES('$userId', '$username', '$password', $authLevel)";
    $cursor = mysqli_query($connection, $query);
    if ($cursor){
        $responseBody->statusCode = 1;
        mysqli_commit($connection);
        /* try{
            mkdir("../secret/".$userId, 0755, true);
            $responseBody->token = getNewToken($userId, $authLevel, $requestHeaders['Referer'], $requestHeaders['Origin'], $requestHeaders['User-Agent']);
            $responseBody->statusCode = 1;
            mysqli_commit($connection);
        } catch (Exception $e){
            error_log("Error while registering consumer....".$e);
            $responseBody->msg = "Unable to register";
            $responseBody->cause = $TOKEN_ERR;
        } */
        
    } else {        
        $responseBody->msg = mysqli_errno($connection)==1062?"Username already exists":"Couldn't register user";
        $responseBody->cause = mysqli_errno($connection)==1062?$DUPLICATE_ERR:$QUERY_ERR;    
    }
    mysqli_close($connection);
} else {
    $responseBody->msg = "Server refused to connect";
    $responseBody->cause = $CONNECTION_ERR;
}
echo json_encode($responseBody);
exit(0);
