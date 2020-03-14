<?php
$requestHeaders = apache_request_headers();
error_log($requestHeaders['User-Agent']);
echo "Device Recorded!";