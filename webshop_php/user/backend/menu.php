<?php
 header('Access-Control-Allow-Origin: *');
 require './include/db.php';

// if($_SERVER['REQUEST_METHOD']==="GET"){
//     $stmt="select name from category where status=1;";
//     if($result= $conn->query($stmt)){
//         $arr=array();
//         while($name=$result ->fetch_assoc()['name']){
//             array_push($arr, $name);
//         }
//         echo json_encode(['categories' => $arr]);
//     }
//     else{
//         echo json_encode(['error' => 'something went wrong' . $conn->error]);
//     }
//     exit();
// }
if ($_SERVER['REQUEST_METHOD'] === "GET") {
    $stmt = "SELECT name FROM category WHERE status=1;";
    
    if ($result = $conn->query($stmt)) {
        $arr = array();

        while ($row = $result->fetch_assoc()) {
            $name = $row['name'];
            array_push($arr, $name);
        }

        echo json_encode(['categories' => $arr]);
    } else {
        // Provide a JSON response with an error message
        echo json_encode(['error' => 'Query execution failed: ' . $conn->error]);
    }

    exit();
}
?>