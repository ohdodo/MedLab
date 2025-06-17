<?php
    session_start();

    if (!isset($_SESSION['email'])) {
      header('Location: login.html');
      exit();
    }

    include ("connect.php");
    $conn_products = new mysqli ($host, $user, $pass, $products_db);
    if ($conn_products->connect_error) {
        die("Connection to Products Database Failed: " . $conn_products->connect_error);
    }

    $user_id = $_SESSION['user_id'];
    $user_query = "SELECT firstName FROM users WHERE id = ?";
    $stmt = $conn_users->prepare($user_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $first_name = $user['firstName'];

    $transactions_query = "
    SELECT o.id, o.total_amount, o.order_date, o.payment_method, o.shipping_address,
           oi.product_id, oi.quantity, oi.price,
           p.name AS product_name, p.image AS product_image, p.is_active
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
";
$stmt = $conn_products->prepare($transactions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - MEDLAB</title>
    <link rel="stylesheet" href="mainprod_design.css" />
    <link rel="stylesheet" href="transactions_design.css" />
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.15.0/css/all.css" />
</head>
<body>
    <header>
        <img id="logo" src="medlab_logo.png" alt="MEDLAB Logo" />
        <input type="checkbox" name="" id="toggler" />
        <label for="toggler" class="fas fa-bars"></label>
        <nav class="navbar">
            <a href="./index.php">Home</a>
            <a href="./main_products.php">Medical Products</a>
            <a href="./transactions.php">My Transactions</a>
            <a href="cart.php" class="fas fa-shopping-cart"></a>
            <div class="user-indicator">
                <i class="fas fa-user"></i>
                <span><?php echo htmlspecialchars($first_name); ?></span>
            </div>
            <?php if (isset($_SESSION['email'])): ?>
                <a href="logout.php" class="fas fa-sign-out-alt">Logout</a>
            <?php else: ?>
                <a href="login.html" class="fas fa-user">Login</a>
            <?php endif; ?>
        </nav>
        <div class="icons">
            <div class="user-indicator">
                <i class="fas fa-user"></i>
                <span><?php echo htmlspecialchars($first_name); ?></span>
            </div>
            <?php if (isset($_SESSION['email'])): ?>
                <a href="logout.php" class="fas fa-sign-out-alt"></a>
            <?php else: ?>
                <a href="login.html" class="fas fa-user"></a>
            <?php endif; ?>
        </div>
    </header>
    <section class="transactions">
        <h1 class="heading">My <span>Transactions</span></h1>
        <?php
        if ($transactions_result->num_rows > 0) {
            $current_order_id = null;
            while ($transaction = $transactions_result->fetch_assoc()) {
                if ($current_order_id !== $transaction['id']) {
                    if ($current_order_id !== null) {
                        echo "</div></div>";
                    }
                    echo "<div class='transaction-item'>";
                    echo "<h3>Order ID: " . htmlspecialchars($transaction['id']) . "</h3>";
                    echo "<p>Date: " . htmlspecialchars($transaction['order_date']) . "</p>";
                    echo "<p>Total Amount: PHP " . htmlspecialchars($transaction['total_amount']) . "</p>";
                    echo "<p>Payment Method: " . htmlspecialchars($transaction['payment_method']) . "</p>";
                    echo "<p>Shipping Address: " . htmlspecialchars($transaction['shipping_address']) . "</p>";
                    echo "<div class='order-items'>";
                    $current_order_id = $transaction['id'];
                }
                echo "<div class='order-item'>";
                if ($transaction['product_name'] !== null) {
                    echo "<img src='" . htmlspecialchars($transaction['product_image']) . "' alt='" . htmlspecialchars($transaction['product_name']) . "' class='product-image'>";
                    echo "<div class='item-details'>";
                    echo "<p>" . htmlspecialchars($transaction['product_name']) . ($transaction['is_active'] ? '' : ' (No longer available)') . "</p>";
                } else {
                    echo "<img src='placeholder.jpg' alt='Product no longer available' class='product-image'>";
                    echo "<div class='item-details'>";
                    echo "<p>Product no longer available (ID: " . htmlspecialchars($transaction['product_id']) . ")</p>";
                }
                echo "<p>Quantity: " . htmlspecialchars($transaction['quantity']) . "</p>";
                echo "<p>Price: PHP " . htmlspecialchars($transaction['price']) . "</p>";
                echo "</div>";
                echo "</div>";
            }
            if ($current_order_id !== null) {
                echo "</div></div>";
            }
        } else {
            echo "<p class='no-transactions'>You haven't made any transactions yet.</p>";
        }
        ?>
    </section>
    <footer>
        <section class="footer">
            <div class="box_container">
                <div class="box">
                    <h3>Useful Links</h3>
                    <a href="#home">Home</a>
                    <a href="#products">Medical Products</a>
                    <a href="transactions.php">My Transactions</a>
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
</body>
</html>

<?php
$conn_products->close();
$conn_users->close();
?>

