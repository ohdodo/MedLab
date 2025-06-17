<?php
session_start();

include 'connect.php';

if (isset($_POST['logIn'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn_users->prepare("SELECT id, email, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['is_admin'] = $row['is_admin'];
            
            $_SESSION['login_success'] = true;
            
            if ($row['is_admin']) {
                header("Location: admin_manage_product.php");
            } else {
                header("Location: main_products.php");
            }
            exit();
        } else {
            $error_message = "Incorrect email or password. Please try again.";
        }
    } else {
        $error_message = "Incorrect email or password. Please try again.";
    }
    $stmt->close();
}
?>