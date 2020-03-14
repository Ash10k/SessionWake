<?php

//libraries
require 'vendor/autoload.php';
use ReallySimpleJWT\Token;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;


//Secrets
$JWTSecret = 'sec!ReT423*&en2aHPFKkn0XoN495rpg';

//CORS
$CORS="Access-Control-Allow-Origin: *";

//Authorization Levels
$AUTH_LV_ADMIN = 1;
$AUTH_LV_EDITOR = 2;
$AUTH_LV_CONSUMER = 3;

//Cause
//(Custom cause range: 1001++)
$INCORRECT_PASSWORD = 1001;
$DUPLICATE_ERR = 1002;
$TOKEN_ERR = 1003;
//(Common cause range: 10-100)
$CONNECTION_ERR = 10;
$QUERY_ERR = 11;
//(Front end behavioural range: 101-1000)
$INVALID_TOKEN = 101; //Force logout on front end
$TOKEN_REGISTRATION_ERROR = 2001;

function establishConnection(){    
    return mysqli_connect("localhost", "root", "ThisisallM!", "SessionWake");
}

function getNewToken($userId, $authLevel, $referer, $origin, $agent){    
    /*
        Returns
        Access Token valid for 10 mins
        authLevel determines role based authorization level of a requesting user
        AccessSecret must have upper case, lower case symbolic and numeric characters with atleast 100 size limit
    */
    error_log("Tokenizing for Origin: ".$origin." and Referer: ".$referer);
    $refreshId = Uuid::uuid4();
    $characters = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
    $secret = ''; 
    for ($i = 0; $i < 100; $i++) {         
        $secret .= $characters[rand(0, strlen($characters)-1)]; 
    }  
    mkdir("../secret/".$refreshId, 0755, true);
    $written  = file_put_contents("../secret/".$refreshId."/access.secret", $secret);    
    if ($written){
        $connection = establishConnection();
        if ($connection){
            $query = "INSERT INTO session(refreshId, userId, agent) VALUES ('$refreshId', '$userId', '$agent')";                    
            error_log($query);
            $refreshCursor = mysqli_query($connection, $query);
            if ($refreshCursor){            
                $accessSecret = file_get_contents("../secret/".$refreshId."/access.secret");            
                $issuer = 'sessionwake.machinist.online';            
                $accessPayload = [
                    'iat' => time(),
                    'uid' => $userId,
                    'exp' => time() + 2592000,
                    'iss' => 'machinist.online',
                    'auth' => $authLevel,
                    'ref' => $referer,
                    'origin' => $origin,
                    'agent' => $agent
                ];
                $accessToken = Token::customPayload($accessPayload, $accessSecret);                
                error_log("VALIDATING: ", validateToken($accessToken.$refreshId, $authLevel, $referer, $origin, $agent));
                return $accessToken.$refreshId;
            } else throw new Exception("Unable to register Refresh ID: ".mysqli_errno($connection));
        } else throw new Exception("Unable to establish Database Connection");
    } else throw new Exception("Unable to deploy secret for RefreshID: ".$refreshId);    
}

function validateToken($token, $authLevel, $referer, $origin, $agent){
    $refreshId = substr($token, strlen($token)-36, 36);
    error_log("VAL REF ID: ".$refreshId);
    $secret = file_get_contents("../secret/".$refreshId."/access.secret");
    error_log("VAL SEC: ". $secret);
    $accessToken = substr($token, 0, strlen($token)-36);
    error_log("VALIDATE 2: ".Token::validate($accessToken, $secret));
    if (Token::validate($accessToken, $secret)){
        $payload = Token::getPayload($accessToken, $secret);
        error_log("VAL AUTHLEV: ".$payload['auth']);
        if ($payload['auth']==$authLevel /* && $payload['ref']==$referer && $payload['origin']==$origin */ && $payload['agent']==$agent){                                    
            return $payload['uid'];
        }
    }
    return NULL;
}

function getNewUserId(){
    return "usr-".Uuid::uuid4();
}
