<?php
global $conn_users, $conn_products;

$host = "localhost";
$user = "root";
$pass = '';
$users_db = "medlab_users";
$products_db = "medlab_products";

function connectToDatabase($host, $user, $pass, $db) {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Failed to Connect to " . $db . " Database: " . $conn->connect_error);
    }
    return $conn;
}

$conn_users = connectToDatabase($host, $user, $pass, $users_db);
$conn_products = connectToDatabase($host, $user, $pass, $products_db);
?>