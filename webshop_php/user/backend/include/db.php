<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE');
//header('Access-Control-Allow-Credentials: true');
session_start();
$conn = new mysqli("localhost:3306", "root", "","eshop" );


if($conn -> connect_errno){
     echo json_encode(['error' => $conn ->connect_error]);
   // echo "Failed to connect to MySQL: " . $conn->connect_error;
    exit();
}

?>