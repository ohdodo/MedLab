<?php
session_start();

include 'connect.php';

if(isset($_POST['signUp'])){
    $firstName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $email = $_POST['email'];
    $birthday = $_POST['birthday'];
    $password = $_POST['password'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0; 

    if (empty($firstName) || empty($lastName) || empty($email) || empty($birthday) || empty($password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        $checkEmail = $conn_users->prepare("SELECT * FROM users WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $result = $checkEmail->get_result();

        if($result->num_rows > 0){
            $error_message = "Email Address already exists!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insertQuery = $conn_users->prepare("INSERT INTO users (firstName, lastName, email, birthday, password, is_admin) VALUES (?, ?, ?, ?, ?, ?)");
            $insertQuery->bind_param("sssssi", $firstName, $lastName, $email, $birthday, $hashedPassword, $is_admin);

            if ($insertQuery->execute()) {
                $_SESSION['signup_success'] = true;
                $_SESSION['email'] = $email;
                $_SESSION['user_id'] = $conn_users->insert_id;
                
                if ($is_admin) {
                    header("Location: admin_add_product.php");
                } else {
                    header("Location: main_products.php");
                }
                exit();
            } else {
                $error_message = "Error: " . $conn_users->error;
            }
            $insertQuery->close();
        }
        $checkEmail->close();
    }
}

if (isset($error_message)) {
    echo "<script>alert('$error_message');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>MEDLAB - Sign Up</title>
    <link rel="stylesheet" href="signup_design.css" />
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.15.0/css/all.css" />
  </head>
  <body>
    <div class="container">
      <img id="signup-bg-img" src="signup_image.jpg" alt="Background Image" />
      <div id="sign_up" class="form-container">
        <img id="logo" src="medlab_logo.png" />
        <h2>Sign Up Now</h2>
        <form action="index_signup.php" method="post">
          <input type="text" name="fName" placeholder="Insert First Name" required />
          <input type="text" name="lName" placeholder="Insert Last Name" required />
          <input type="email" name="email" placeholder="Insert Email" required />
          <input type="date" name="birthday" placeholder="Insert Birthday" required />
          <input type="password" name="password" placeholder="Insert Password" required />
          <div class="form-group checkbox-group">
            <input type="checkbox" id="is_admin" name="is_admin" />
            <label for="is_admin">Register as Admin</label>
          </div>
          <input type="submit" class="button" value="Sign Up" name="signUp" />
          <p>
            Already have an account?
            <a href="login.html" onclick="toggleForm('login-form')">Log In Here</a>
          </p>
        </form>
      </div>
    </div>
  </body>
</html>

