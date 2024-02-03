<?php
require './include/db.php';
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] === "GET" && isset( $_GET['id'])) {
    $stmt = "select stock from inventory where product_id = ?;";
    $prep_stmt = $conn ->prepare($stmt);
    $id= $_GET['id'];
    $prep_stmt -> bind_param('i', $id);
    $prep_stmt -> execute();
    if ($result =  $prep_stmt -> get_result()) {
      //  $arr = array();
      //  while ($rowArray = $result->fetch_assoc()) {
        //    array_push($arr, $result->fetch_assoc());
       // }

        echo json_encode(['stock' => $result->fetch_assoc()['stock']]);
    } else {
        echo json_encode(['error' => 'Query execution failed: ' . $conn->error]);
    }
    $prep_stmt -> close();
    exit();
}
?>