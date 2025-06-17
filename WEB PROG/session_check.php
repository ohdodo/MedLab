<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function redirect_to_login() {
    header("Location: login.html");
    exit();
}
?>

