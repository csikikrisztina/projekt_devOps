<?php
require './include/db.php';
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if(isset($_SESSION['logged_user'])){
        global $conn;
        $cart = array();
        $user_id = getUserId();
        $cart_id = getCartId($user_id);
        if($cart_id){
            $cart=getAllCartItems($cart_id);
        }
        error_log("Debug: Cart for logged-in user - " . print_r($cart, true));
        echo json_encode(['cart' => $cart]);
    }
    else{
        if(!isset($_SESSION['cart'])){
            $_SESSION['cart']=array();
        }
        echo json_encode(['cart'=> $_SESSION['cart']]);
    }
    
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if(isset($_SESSION['logged_user'])){
        addToLoggedUserCart();
    }else{
       addToGuestUserCart();
      
    }
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == "PATCH") {
    parse_str(file_get_contents('php://input'), $_PATCH);
    if(isset($_SESSION['logged_user'])){
        updateLoggedUserCart($_PATCH);
    }
    else{
        updateToGuestUserCart($_PATCH);
    }
    
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
    parse_str(file_get_contents('php://input'), $_DELETE);
    if(isset($_SESSION['logged_user'])){
        updateLoggedUserCartProduct($_DELETE);
    }
    else{
        updateToGuestUserCartProduct($_DELETE);
    }
    
    exit();
}
function getProductPrice($id){
    global $conn;
    $stmt = 'select price from product where id = ?;';
    $prep_stmt = $conn ->prepare($stmt);
    $prep_stmt->bind_param('i', $id);
    $prep_stmt->execute();
    if($result = $prep_stmt->get_result())
       return $result->fetch_assoc()['price'];
   else
       return -1;
}

function updateTotalCart(){
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
   $total =0.00;
   foreach($_SESSION['cart'] as $item){
       $total += $item['price'];
       $total=round($total, 2);
   }
   $_SESSION['cart']['total']=$total; 
} 
function addToGuestUserCart(){
    $id = $_POST['id'];
    $image = $_POST['image'];
    $stock = $_POST['stock'];
    $quantity = $_POST['quantity'];
    if(!isset($_SESSION['cart'][$id])){
        $_SESSION['cart'][$id]['image'] = $image;
        $_SESSION['cart'][$id]['stock'] = $stock;
        $_SESSION['cart'][$id]['quantity'] = $quantity;
    }else{
        $_SESSION['cart'][$id]['quantity'] +=$quantity;
    }
    $price=getProductPrice($id);
    $_SESSION['cart'][$id]['price'] = round(($price* $_SESSION['cart'][$id]['quantity']),2);
    updateTotalCart();
    echo json_encode(['cart' => $_SESSION['cart']]);
}

function  updateToGuestUserCart($_PATCH){
    $id = $_PATCH['id'];
    $quantity = $_PATCH['quantity'];
    $_SESSION['cart'][$id]['quantity'] = $quantity;
    $price = $quantity * getProductPrice($id);
    $price = bcdiv($price, 1,2);
    $_SESSION['cart'][$id]['price']=$price;
    updateTotalCart();
    echo json_encode(['cart'=> $_SESSION['cart']]);
}

function  updateToGuestUserCartProduct($_DELETE){
    $id = $_DELETE['id'];
    unset($_SESSION['cart'][$id]);
    updateTotalCart();
    echo json_encode(['cart'=> $_SESSION['cart']]);
}

function getUserId(){
    return $_SESSION['logged_user']['id'];
}

function getCartId($user_id){
    global $conn;
    $cart_id = null;
    $stmt="select id from cart where user_id = $user_id;";
    if($result = $conn ->quert($stmt)){
        if($result->num_rows){
            $cart_id = $result->fetch_assoc()['id'];
        }else{
            $stmt = "insert into cart (user_id) values ($user_id);";
            if($result = $conn ->query($stmt)){
                if($result->affected_rows){
                    $cart_id=$conn->insert_id;
                }
            }
        }
    }
    return $cart_id;
}
function getAllCartItems($cart_id){
    global $conn; 
    $stmt = "select p.id, p.image, ci.quantity, truncate((p.price * ci.quantity),2) as price, i.stock
    from product p inner join cart_item ci
    on p.id=ci.prod_id AND ci.cart_id = $cart_id
    inner join inventory i
    on p.id=i.product_id;";
    error_log("Debug: SQL query - " . $stmt);
    if($result = $conn->quert($stmt)){
        if($result ->num_rows){
            while($row=$result->fetch_assoc()){
                $id = $row['id'];
                $prod_array[$id]['image']=$row['image'];
                $prod_array[$id]['stock']=$row['stock'];
                $prod_array[$id]['quantity']=$row['quantity'];
                $prod_array[$id]['price']=$row['price'];
            }
        }
    }
    return updateTotaleLoggedCart($prod_array);
}

function updateTotaleLoggedCart($prod_array){
    $total=0.0;
    foreach($prod_array as $item){
        $total+= $item['price'];
        $total = round($total, 2);
    }
    $prod_array['total']=$total;
    return $prod_array;
}
function  addToLoggedUserCart(){
    global $conn;
    $prod_id=$_POST['id'];
    $quantity = $_POST['quantity'];
    $cart_id = getCartId(getUserId());
    $stmt = 'insert into cart_item (cart_id, prod_id, quantity) values (?,?,?) on duplicate key update quantity = quantity + ?;';
    $prep_stmt = $conn -> prepare($stmt);
    $prep_stmt->bind_param('iiii', $cart_id, $prod_id, $quantity, $quantity);
    $prep_stmt ->execute();
    $prep_stmt->close();
    $cart = getAllCartItems($cart_id);
    echo json_encode(['cart'=> $cart]);
}

function updateLoggedUserCart($_PATCH){
    global $conn;
    $prod_id=$_PATCH['id'];
    $quantity = $_PATCH['quantity'];
    $quantity= getCartId(getUserId);
    $stmt = "update cart_item set quantity =? where cart_id=$cart_id and prod_id=?;";
    $prep_stmt = $conn -> prepare($stmt);
    $prep_stmt->bind_param('ii',  $quantity, $prod_id);
    $prep_stmt ->execute();
    $prep_stmt->close();
    $cart = getAllCartItems($cart_id);
    echo json_encode(['cart'=> $cart]);
}
function  updateLoggedUserCartProduct($_DELETE){
    global $conn;
    $prod_id = $_DELETE['id'];
    $cart_id=getCartId(getUserId());
    $stmt = "delete from cart_item where cart_id = $cart_id and prod_id=?;";
    $prep_stmt = $conn -> prepare($stmt);
    $prep_stmt->bind_param('i', $prod_id);
    $prep_stmt ->execute();
    $prep_stmt->close();
    $cart = getAllCartItems($cart_id);
    echo json_encode(['cart'=> $cart]);
}