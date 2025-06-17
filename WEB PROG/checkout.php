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
$user_query = "SELECT firstName, lastName, email FROM users WHERE id = ?";
$stmt = $conn_users->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$first_name = $user['firstName'];
$last_name = $user['lastName'];
$email = $user['email'];

function processCheckout($userId, $paymentMethod, $shippingAddress, $phoneNumber) {
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
            INSERT INTO orders (user_id, total_amount, payment_method, shipping_address, phone_number, order_date)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $orderStmt->bind_param("idsss", $userId, $totalAmount, $paymentMethod, $shippingAddress, $phoneNumber);
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

$checkoutResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $userId = $_SESSION['user_id'];
    $paymentMethod = $_POST['payment_method'];
    $shippingAddress = $_POST['shipping_address'];
    $phoneNumber = $_POST['phone_number'];
    $_SESSION['last_order_phone'] = $phoneNumber; // Add this line
    $checkoutResult = processCheckout($userId, $paymentMethod, $shippingAddress, $phoneNumber);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MEDLAB</title>
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
        <section class="checkout" id="checkout">

            <?php if ($checkoutResult === null): ?>
                <form method="post" action="checkout.php" class="checkout-form">
                    <div>
                    <div class="user-details">
                        <h2>Your Information</h2>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                        <textarea name="phone_number" placeholder="Enter your phone number" required></textarea>
                    </div>
                    <div class="payment-options">
                        <h2>Payment Method</h2>
                        <label>
                            <input type="radio" name="payment_method" value="cash" checked>
                            <span class="radio-custom"></span>
                            Cash on Delivery
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="bank">
                            <span class="radio-custom"></span>
                            Bank Transfer
                        </label>
                    </div>
                    <div class="shipping-address">
                        <h2>Shipping Address</h2>
                        <textarea name="shipping_address" placeholder="Enter your full shipping address" required></textarea>
                    </div>
                    <button type="submit" name="checkout" class="checkout-btn">Complete Checkout</button>
                    </div>
                </form>
            <?php elseif ($checkoutResult['success']): ?>
                <div class="receipt">
                    <h2>Order Confirmation</h2>
                    <p><strong>Order ID:</strong> <?php echo $checkoutResult['orderId']; ?></p>
                    <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                    <p><strong>Total Amount:</strong> <?php echo number_format($checkoutResult['totalAmount'], 2); ?> PHP</p>
                    <h3>Items:</h3>
                    <div class="order-items">
                        <?php foreach ($checkoutResult['items'] as $item): ?>
                            <div class="receipt-item">
                                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="receipt-image">
                                <div class="receipt-item-details">
                                    <p class="item-name"><?php echo $item['name']; ?></p>
                                    <p class="item-quantity">Quantity: <?php echo $item['quantity']; ?></p>
                                    <p class="item-price">Price: <?php echo number_format($item['price'], 2); ?> PHP</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="order_confirmation.php?order_id=<?php echo $checkoutResult['orderId']; ?>" class="view-order-btn">View Full Order Details</a>
                </div>
            <?php else: ?>
                <p class="error">Error: <?php echo $checkoutResult['error']; ?></p>
            <?php endif; ?>
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