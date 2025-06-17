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

// Handle product deactivation
if (isset($_POST['deactivate_product'])) {
    $product_id = $_POST['product_id'];
    $updateStmt = $conn_products->prepare("UPDATE products SET is_active = FALSE WHERE id = ?");
    $updateStmt->bind_param("i", $product_id);
    if ($updateStmt->execute()) {
        echo "<script>alert('Product deactivated successfully');</script>";
    } else {
        echo "<script>alert('Error deactivating product: " . $conn_products->error . "');</script>";
    }
    $updateStmt->close();
}

// Handle product reactivation
if (isset($_POST['reactivate_product'])) {
    $product_id = $_POST['product_id'];
    $updateStmt = $conn_products->prepare("UPDATE products SET is_active = TRUE WHERE id = ?");
    $updateStmt->bind_param("i", $product_id);
    if ($updateStmt->execute()) {
        echo "<script>alert('Product reactivated successfully');</script>";
    } else {
        echo "<script>alert('Error reactivating product: " . $conn_products->error . "');</script>";
    }
    $updateStmt->close();
}

// Handle quantity update
if (isset($_POST['update_quantity'])) {
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['new_quantity'];
    $updateStmt = $conn_products->prepare("UPDATE products SET available_quantity = ? WHERE id = ?");
    $updateStmt->bind_param("ii", $new_quantity, $product_id);
    if ($updateStmt->execute()) {
        echo "<script>alert('Quantity updated successfully');</script>";
    } else {
        echo "<script>alert('Error updating quantity');</script>";
    }
    $updateStmt->close();
}

// Fetch all products
$sql = "SELECT * FROM products ORDER BY id ASC";
$result = $conn_products->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - MEDLAB</title>
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
                <li><a href="admin_add_product.php">Add New Product</a></li>
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
        <h1>Manage Products</h1>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Available Quantity</th>
                        <th>Rating</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td data-label='ID'>" . $row['id'] . "</td>";
                            echo "<td data-label='Name'>" . $row['name'] . "</td>";
                            echo "<td data-label='Price'>" . $row['price'] . "</td>";
                            echo "<td data-label='Available Quantity'>" . $row['available_quantity'] . "</td>";
                            echo "<td data-label='Rating'>" . $row['rating'] . "</td>";
                            echo "<td data-label='Image'><img src='" . $row['image'] . "' alt='" . $row['name'] . "' style='width: 50px; height: 50px;'></td>";
                            echo "<td data-label='Status'>" . ($row['is_active'] ? 'Active' : 'Inactive') . "</td>";
                            echo "<td data-label='Actions'>";
                            echo "<div class='action-buttons'>";
                            echo "<form method='post' class='update-form'>";
                            echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
                            echo "<input type='number' name='new_quantity' value='" . $row['available_quantity'] . "' min='0' style='width: 60px;'>";
                            echo "<button type='submit' name='update_quantity' class='button'>Update</button>";
                            echo "</form>";
                            if ($row['is_active']) {
                                echo "<form method='post' class='delete-form'>";
                                echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
                                echo "<button type='submit' name='deactivate_product' class='delete-button' onclick='return confirm(\"Are you sure you want to deactivate this product? It will no longer be available for purchase but will remain in transaction history.\")'><i class='fas fa-trash'></i></button>";
                                echo "</form>";
                            } else {
                                echo "<form method='post' class='reactivate-form'>";
                                echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
                                echo "<button type='submit' name='reactivate_product' class='reactivate-button' onclick='return confirm(\"Are you sure you want to reactivate this product? It will be available for purchase again.\")'><i class='fas fa-undo'></i></button>";
                                echo "</form>";
                            }
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8'>No products found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <footer>
        <p>&copy; 2024 MEDLAB Inc. All rights reserved.</p>
    </footer>
</body>
</html>
<?php
$conn_products->close();
?>

