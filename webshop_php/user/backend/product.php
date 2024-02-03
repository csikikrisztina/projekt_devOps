<?php
require './include/db.php';
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] === "GET" && isset( $_GET['category'])) {
    $stmt = "select * from product where status=1 and category_id = (select id from category where name = ?);";
    $prep_stmt = $conn ->prepare($stmt);
    $category = $_GET['category'];
    $prep_stmt -> bind_param('s', $category);
    $prep_stmt -> execute();
    if ($result =  $prep_stmt -> get_result()) {
        $arr = array();
        while ($rowArray = $result->fetch_assoc()) {
            array_push($arr, $rowArray);
        }

        echo json_encode(['products' => $arr]);
    } else {
        echo json_encode(['error' => 'Query execution failed: ' . $conn->error]);
    }
    $prep_stmt -> close();
    exit();
}
?>