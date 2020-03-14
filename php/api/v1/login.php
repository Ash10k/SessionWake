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
    $query = "SELECT * FROM user WHERE username='$requestBody->username'";
    $cursor = mysqli_query($connection, $query);
    if ($cursor && mysqli_num_rows($cursor)==1){
        $row = mysqli_fetch_assoc($cursor);
        if ($row['password']==$requestBody->password){
            $responseBody->token = getNewToken($requestBody->username, $row["authLevel"], $requestHeaders['Referer'], $requestHeaders['Origin'], $requestHeaders['User-Agent']);
            $responseBody->username = $row["username"];
            $responseBody->statusCode = 1;
        } else {
            $responseBody->msg = "Incorrect Password";   
            $INCORRECT_PASSWORD = 1001;     
        }
    } else {
        $responseBody->msg = "Couldn't find user";
        $responseBody->cause = $QUERY_ERR;    
    }
    mysqli_close($connection);
} else {
    $responseBody->msg = "Server refused to connect";
    $responseBody->cause = $CONNECTION_ERR;
}
echo json_encode($responseBody);
exit(0);
