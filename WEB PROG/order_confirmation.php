<?php
session_start();

$last_order_phone = isset($_SESSION['last_order_phone']) ? $_SESSION['last_order_phone'] : 'Not provided';

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

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

function getOrderDetails($orderId, $userId) {
    global $conn_products;
    
    $stmt = $conn_products->prepare("
        SELECT o.id, o.total_amount, o.payment_method, o.shipping_address, o.order_date,
               oi.product_id, oi.quantity, oi.price,
               p.name, p.image
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $order = null;
    $items = [];
    
    while ($row = $result->fetch_assoc()) {
        if ($order === null) {
            $order = [
                'id' => $row['id'],
                'total_amount' => $row['total_amount'],
                'payment_method' => $row['payment_method'],
                'shipping_address' => $row['shipping_address'],
                'order_date' => $row['order_date'],
                'items' => []
            ];
        }
        
        $order['items'][] = [
            'name' => $row['name'],
            'quantity' => $row['quantity'],
            'price' => $row['price'],
            'image' => $row['image']
        ];
    }
    
    return $order;
}

$order = getOrderDetails($order_id, $user_id);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - MEDLAB</title>
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
        <?php if ($order): ?>
            <div class="receipt">
                <h2>Order Confirmation</h2>
                <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['id']); ?></p>
                <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></p>
                <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($first_name . ' ' . $last_name); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($last_order_phone); ?></p>
                <p><strong>Shipping Address:</strong> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                <p><strong>Payment Method:</strong> <?php echo ucfirst(htmlspecialchars($order['payment_method'])); ?></p>
                
                <h3>Order Items</h3>
                <div class="order-items">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="receipt-item">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="receipt-image">
                            <div class="receipt-item-details">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p>Quantity: <?php echo htmlspecialchars($item['quantity']); ?></p>
                                <p>Price: PHP <?php echo number_format($item['price'], 2); ?></p>
                                <p>Subtotal: PHP <?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="receipt-total">
                    <h3>Total Amount: PHP <?php echo number_format($order['total_amount'], 2); ?></h3>
                </div>
            </div>
        <?php else: ?>
            <p class="error">No order found or you don't have permission to view this order.</p>
        <?php endif; ?>
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
unset($_SESSION['last_order_phone']);
$conn_products->close();
$conn_users->close();
?>

