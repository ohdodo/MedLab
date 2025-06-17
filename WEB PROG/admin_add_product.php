<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: login.html');
    exit();
}

include ("connect.php");
$conn_products = new mysqli ($host, $user, $pass, $products_db);
if ($conn_products->connect_error) {
    die("Connection to Products Database Failed: " . $conn_products->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $available_quantity = $_POST['available_quantity'];
    $rating = $_POST['rating'];
    
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    chmod($target_dir, 0777);
    
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
    
    if ($_FILES["image"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
    
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
            
            $stmt = $conn_products->prepare("INSERT INTO products (name, price, available_quantity, rating, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sdiis", $name, $price, $available_quantity, $rating, $image);
            
            if ($stmt->execute()) {
                echo "<script>alert('New product added successfully');</script>";
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file. Error: " . error_get_last()['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product - MEDLAB</title>
    <link rel="stylesheet" href="admin_design.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <img id="logo" src="medlab_logo.png" alt="MEDLAB Logo">
        <input type="checkbox" id="toggler">
        <label for="toggler" class="fas fa-bars"></label>
        <nav class="navbar">
            <ul>
                <li><a href="admin_manage_product.php">Manage Products</a></li>
                <li><a href="admin_manage_users.php">Manage Users</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        <div class="admin-indicator">
            <i class="fas fa-user-shield"></i>
            <span>Admin</span>
        </div>
    </header>
    <main class="container">
        <h1>Add New Product</h1>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
            <label for="name">Product Name:</label>
            <input type="text" id="name" name="name" required>
            
            <label for="price">Price (PHP):</label>
            <input type="number" id="price" name="price" step="0.01" required>
            
            <label for="available_quantity">Available Quantity:</label>
            <input type="number" id="available_quantity" name="available_quantity" required>
            
            <label for="rating">Rating (1-5):</label>
            <input type="number" id="rating" name="rating" min="1" max="5" required>
            
            <label for="image">Product Image:</label>
            <input type="file" id="image" name="image" accept="image/*" required>
            
            <button type="submit" class="button">Add Product</button>
        </form>
    </main>
    
    <footer>
        <p>&copy; 2024 MEDLAB Inc. All rights reserved.</p>
    </footer>
</body>
</html>


<?php
$conn_products->close();
?>

