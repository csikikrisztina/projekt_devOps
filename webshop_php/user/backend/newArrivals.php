<?php
require './include/db.php';
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] === "GET") {
    $stmt = "select * from product where status=1  order by added_on desc limit 3";
    
    if ($result = $conn->query($stmt)) {
        $arr = array();

        while ($rowArray = $result->fetch_assoc()) {
            array_push($arr, $rowArray);
        }

        echo json_encode(['newArrivals' => $arr]);
    } else {
        echo json_encode(['error' => 'Query execution failed: ' . $conn->error]);
    }

    exit();
}
?>