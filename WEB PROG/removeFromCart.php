<?php
include ("connect.php");
include ("cart.php");
$conn_products = new mysqli ($host,$user, $pass, $products_db);
if ($conn_products->connect_error) {
    die("Connection to Products Database Failed: " .$conn_products->connect_error);
}
if (isset($_GET['id'])) {
    $cartItemId = $_GET['id'];
    $stmt = $conn_products->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->bind_param("i", $cartItemId);
    $result = $stmt->execute();
    if (!$result) {
        die("Error removing item from cart: " . $conn_products->error);
    }
    $stmt->close();
    echo "<script>alert('Item removed from cart'); window.location.href='main_products.php';</script>";
    exit();
}

?>
