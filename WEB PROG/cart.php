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

function increaseQuantity($cartId, $userId) {
    global $conn_products;
    
    $stmt = $conn_products->prepare("
        SELECT c.quantity, p.available_quantity 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $newQuantity = min($row['quantity'] + 1, $row['available_quantity']);
        
        $updateStmt = $conn_products->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $updateStmt->bind_param("iii", $newQuantity, $cartId, $userId);
        $updateStmt->execute();
        $updateStmt->close();
    }
    $stmt->close();
}

function decreaseQuantity($cartId, $userId) {
    global $conn_products;
    
    $stmt = $conn_products->prepare("SELECT quantity FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $newQuantity = max(1, $row['quantity'] - 1);
        
        $updateStmt = $conn_products->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $updateStmt->bind_param("iii", $newQuantity, $cartId, $userId);
        $updateStmt->execute();
        $updateStmt->close();
    }
    $stmt->close();
}


function removeFromCart($cartId, $userId) {
    global $conn_products;
    
    $stmt = $conn_products->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
    $stmt->close();
}


function processCheckout($userId, $paymentMethod, $shippingAddress) {
    global $conn_products;
    
   
    $conn_products->begin_transaction();
    
    try {
       
        $stmt = $conn_products->prepare("
            SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.available_quantity, p.image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $totalAmount = 0;
        $orderItems = [];
        
        while ($row = $result->fetch_assoc()) {
            if ($row['quantity'] > $row['available_quantity']) {
                throw new Exception("Not enough stock for " . $row['name']);
            }
            
            $totalAmount += $row['price'] * $row['quantity'];
            $orderItems[] = $row;
            
            // Update product quantity
            $newQuantity = $row['available_quantity'] - $row['quantity'];
            $updateStmt = $conn_products->prepare("UPDATE products SET available_quantity = ? WHERE id = ?");
            $updateStmt->bind_param("ii", $newQuantity, $row['product_id']);
            $updateStmt->execute();
            $updateStmt->close();
        }
        
        // Create order
        $orderStmt = $conn_products->prepare("
            INSERT INTO orders (user_id, total_amount, payment_method, shipping_address, order_date)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $orderStmt->bind_param("idss", $userId, $totalAmount, $paymentMethod, $shippingAddress);
        $orderStmt->execute();
        $orderId = $orderStmt->insert_id;
        $orderStmt->close();
        
        // Create order items
        $orderItemStmt = $conn_products->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($orderItems as $item) {
            $orderItemStmt->bind_param("iiid", $orderId, $item['product_id'], $item['quantity'], $item['price']);
            $orderItemStmt->execute();
        }
        $orderItemStmt->close();
        
        // Clear cart
        $clearCartStmt = $conn_products->prepare("DELETE FROM cart WHERE user_id = ?");
        $clearCartStmt->bind_param("i", $userId);
        $clearCartStmt->execute();
        $clearCartStmt->close();
        
        // Commit transaction
        $conn_products->commit();
        
        return [
            'success' => true,
            'orderId' => $orderId,
            'totalAmount' => $totalAmount,
            'items' => $orderItems
        ];
    } catch (Exception $e) {
        $conn_products->rollback();
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    
    if (isset($_POST['increase'])) {
        increaseQuantity($_POST['cartId'], $userId);
    } elseif (isset($_POST['decrease'])) {
        decreaseQuantity($_POST['cartId'], $userId);
    } elseif (isset($_POST['remove'])) {
        removeFromCart($_POST['cartId'], $userId);
    } elseif (isset($_POST['checkout'])) {
        $paymentMethod = $_POST['payment_method'];
        $shippingAddress = $_POST['shipping_address'];
        $checkoutResult = processCheckout($userId, $paymentMethod, $shippingAddress);
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="mainprod_design.css" />
    <link rel="stylesheet" href="cart_design.css" />
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
    <main>
        <section class="cart" id="cart">
            <?php
            $stmt = $conn_products->prepare("
                SELECT c.id as cart_id, p.name, p.price, c.quantity, p.available_quantity, p.image
                FROM cart c 
                JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?
            ");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th>Action</th></tr>";
                
                $total = 0;
                while ($row = $result->fetch_assoc()) {
                    $itemTotal = $row['price'] * $row['quantity'];
                    $total += $itemTotal;
                    
                    echo "<tr>";
                    echo "<td class='product-info'>";
                    echo "<img src='" . $row['image'] . "' alt='" . $row['name'] . "' class='product-image'>";
                    echo "<span>" . $row['name'] . "</span>";
                    echo "</td>";
                    echo "<td>" . $row['price'] . " PHP</td>";
                    echo "<td>";
                    echo "<div class='quantity-controls'>";
                    echo "<form method='post' action='cart.php' style='display: inline;'>";
                    echo "<input type='hidden' name='cartId' value='" . $row['cart_id'] . "'>";
                    echo "<button type='submit' name='decrease' " . ($row['quantity'] <= 1 ? 'disabled' : '') . ">-</button>";
                    echo "<span>" . $row['quantity'] . "</span>";
                    echo "<button type='submit' name='increase' " . ($row['quantity'] >= $row['available_quantity'] ? 'disabled' : '') . ">+</button>";
                    echo "</form>";
                    echo "</div>";
                    echo "</td>";
                    echo "<td>" . $itemTotal . " PHP</td>";
                    echo "<td>";
                    echo "<form method='post' action='cart.php'>";
                    echo "<input type='hidden' name='cartId' value='" . $row['cart_id'] . "'>";
                    echo "<button type='submit' name='remove' class='remove-btn'><i class='fas fa-trash'></i></button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                
                echo "<tr class='cart-total'><td colspan='3'>Total:</td><td colspan='2'>" . $total . " PHP</td></tr>";
                echo "</table>";

                echo "<a href='checkout.php' class='checkout-btn'>Proceed to Checkout</a>";

            } else {
                echo "<p class='empty-cart'>Your cart is empty.</p>";
            }
            ?>
        </section>
    </main>

    <footer>
        <section class="footer">
            <div class="box_container">
                <div class="box">
                    <h3>Useful Links</h3>
                    <a href="#home">Home</a>
                    <a href="#about_us">About Us</a>
                    <a href="#products">Medical Products</a>
                    <a href="#review">Customer Reviews</a>
                    <a href="#contact">Contact Us</a>
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
                    <h3>Contact Information</h3>
                    <a href="#">+639-998-765-4321</a>
                    <a href="#">medlab@business.ca</a>
                    <a href="#">Regina, Saskatchewan S4N 3S5</a>
                </div>
            </div>
            <div class="credit">
                CREATED BY <span>MEDLAB Inc.</span> || All Rights Reserved 2024
            </div>
        </section>
    </footer>
</body>
</html>

<?php
$conn_products->close();
$conn_users->close();
?>

