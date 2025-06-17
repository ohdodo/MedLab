<?php
    session_start();

    if (!isset($_SESSION['email'])) {
      header('Location: login.php');
      exit();
    }

    include ("connect.php");
    $conn_products = new mysqli ($host,$user, $pass, $products_db);
    if ($conn_products->connect_error) {
        die("Connection to Products Database Failed: " .$conn_products->connect_error);
    }

   
    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT firstName FROM users WHERE id = ?";
    $stmt = $conn_users->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $first_name = $user['firstName'];

  
    if(isset($_POST['addToCart']) && isset($_SESSION['user_id'])) {
      $productId = $_POST['productId'];
      $userId = $_SESSION['user_id'];
      
      // Check if the product is available before adding to cart
      $availCheck = $conn_products->prepare("SELECT available_quantity FROM products WHERE id = ?");
      $availCheck->bind_param("i", $productId);
      $availCheck->execute();
      $availResult = $availCheck->get_result();
      $availRow = $availResult->fetch_assoc();
      
      if($availRow['available_quantity'] > 0) {
          $checkStmt = $conn_products->prepare("SELECT id, quantity FROM cart WHERE product_id = ? AND user_id = ?");
          $checkStmt->bind_param("ii", $productId, $userId);
          $checkStmt->execute();
          $result = $checkStmt->get_result();
          
          if($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $newQuantity = $row['quantity'] + 1;
              
              if($newQuantity <= $availRow['available_quantity']) {
                  $updateStmt = $conn_products->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                  $updateStmt->bind_param("ii", $newQuantity, $row['id']);
                  $updateStmt->execute();
                  echo "<script>alert('Product quantity updated in cart!');</script>";
              } else {
                  echo "<script>alert('Cannot add more of this product - maximum quantity reached!');</script>";
              }
          } else {
              $insertStmt = $conn_products->prepare("INSERT INTO cart (product_id, user_id, quantity) VALUES (?, ?, 1)");
              $insertStmt->bind_param("ii", $productId, $userId);
              $insertStmt->execute();
              echo "<script>alert('Product added to cart!');</script>";
          }
      } else {
          echo "<script>alert('This product is out of stock!');</script>";
      }
  }
    
    $sql = "SELECT * FROM products WHERE is_active = TRUE ORDER BY id ASC";
    $result = $conn_products->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MEDLAB</title>
    <link rel="stylesheet" href="mainprod_design.css" />
    <link
      rel="stylesheet"
      href="https://pro.fontawesome.com/releases/v5.15.0/css/all.css"
    />
</head>
<body>
    <header>
    <img id="logo" src="medlab_logo.png" alt="MEDLAB Logo" />
    <input type="checkbox" name="" id="toggler" />
    <nav class="navbar">
        <a href="./index.php">Home</a>
        <a href="./main_products.php">Medical Products</a>
        <a href="./transactions.php">My Transactions</a>
        <a href="cart.php" class="fas fa-shopping-cart"></a>
        <div class="user-indicator">
            <i class="fas fa-user"></i>
            <span><?php echo htmlspecialchars($first_name); ?></span>
        </div>
        <a href="logout.php" class="fas fa-sign-out-alt">Logout</a>
    </nav>
    <label for="toggler" class="fas fa-bars"></label>
    <div class="icons">
        <div class="user-indicator">
            <i class="fas fa-user"></i>
            <span><?php echo htmlspecialchars($first_name); ?></span>
        </div>
        <a href="logout.php" class="fas fa-sign-out-alt"></a>
    </div>
</header>
<section class="products" id="products">
      <h1 class="heading">Products <span>Available</span></h1>
      <div class="box_container">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='box'>";
                echo "<div class='image'><img src='" . htmlspecialchars($row['image']) . "' alt='" . htmlspecialchars($row['name']) . "' /></div>";
                echo "<div class='content'>";
                echo "<div class='stars'>";
                for ($i = 0; $i < $row['rating']; $i++) {
                    echo "<i class='fas fa-star'></i>";
                }
                echo "</div>";
                echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                echo "<div class='price'>" . number_format($row['price'], 2) . " PHP</div>";
                echo "<div class='available_quantity'>" . $row['available_quantity'] . " in stock</div>";
                
                echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
                echo "<input type='hidden' name='productId' value='" . $row['id'] . "'>";
                if ($row['available_quantity'] > 0) {
                    echo "<button type='submit' name='addToCart' class='add-to-cart-btn'>Add to Cart</button>";
                } else {
                    echo "<button type='button' class='add-to-cart-btn' disabled>Out of Stock</button>";
                }
                echo "</form>";
                
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<p>No products found.</p>";
        }
        ?>
      </div>
    </section>
  </body>
  <footer>
  <section class="footer">
    <div class="box_container">
      <div class="box">
        <h3>Useful Links</h3>
        <a href="#home">Home</a>
        <a href="#products">Medical Products</a>
      </div>
      <div class="box">
        <h3>Follow Our Socials</h3>
        <a href="#">Facebook</a>
        <a href="#">Instagram</a>
        <a href="#">Tiktok</a>
      </div>
      <div class="box">
        <h3>Shipping Locations</h3>
        <a href="#">Worldwide</a>
        <a href="#">United States</a>
        <a href="#">Philippines</a>
        <a href="#">Thailand</a>
        <a href="#">Japan</a>
        <a href="#">China</a>
        <a href="#">India</a>
      </div>
      <div class="box">
        <h3>Contact Informations</h3>
        <a href="#">+639-998-765-4321</a>
        <a href="#">medlab@business.ca</a>
        <a href="#">Regina, Saskatchewan S4N 3S5</a>
      </div>
    </div>
    <div class="credit">
      CREATED BY <span> MEDLAB Inc.</span> || All Rights Reserve 2024
    </div>
  </section>
</footer>
</html>

<?php
$conn_products->close();
$conn_users->close();
?>

