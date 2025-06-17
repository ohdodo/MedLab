<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: login.html');
    exit();
}

include ("connect.php");
$conn_users = new mysqli ($host, $user, $pass, $users_db);
if ($conn_users->connect_error) {
    die("Connection to Users Database Failed: " . $conn_users->connect_error);
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $deleteStmt = $conn_users->prepare("DELETE FROM users WHERE id = ?");
    $deleteStmt->bind_param("i", $user_id);
    if ($deleteStmt->execute()) {
        echo "<script>alert('User deleted successfully');</script>";
    } else {
        echo "<script>alert('Error deleting user: " . $conn_users->error . "');</script>";
    }
    $deleteStmt->close();
}

// Handle user status update
if (isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['new_status'];
    $updateStmt = $conn_users->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
    $updateStmt->bind_param("ii", $new_status, $user_id);
    if ($updateStmt->execute()) {
        echo "<script>alert('User status updated successfully');</script>";
    } else {
        echo "<script>alert('Error updating user status: " . $conn_users->error . "');</script>";
    }
    $updateStmt->close();
}

// Fetch all users
$sql = "SELECT * FROM users ORDER BY id ASC";
$result = $conn_users->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - MEDLAB</title>
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
    <h1>Manage Users</h1>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Birthday</th>
                        <th>Email</th>
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
                            echo "<td data-label='First Name'>" . htmlspecialchars($row['firstName']) . "</td>";
                            echo "<td data-label='Last Name'>" . htmlspecialchars($row['lastName']) . "</td>";
                            echo "<td data-label='Birthday'>" . htmlspecialchars($row['birthday']) . "</td>";
                            echo "<td data-label='Email'>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td data-label='Status'>" . ($row['is_admin'] ? 'Admin' : 'Regular') . "</td>";
                            echo "<td data-label='Actions'>";
                            echo "<div class='action-buttons'>";
                            echo "<form method='post' class='update-form'>";
                            echo "<input type='hidden' name='user_id' value='" . $row['id'] . "'>";
                            echo "<select name='new_status'>";
                            echo "<option value='0' " . ($row['is_admin'] ? '' : 'selected') . ">Regular</option>";
                            echo "<option value='1' " . ($row['is_admin'] ? 'selected' : '') . ">Admin</option>";
                            echo "</select>";
                            echo "<button type='submit' name='update_status' class='button'>Update</button>";
                            echo "</form>";
                            echo "<form method='post' class='delete-form'>";
                            echo "<input type='hidden' name='user_id' value='" . $row['id'] . "'>";
                            echo "<button type='submit' name='delete_user' class='delete-button' onclick='return confirm(\"Are you sure you want to delete this user? This action cannot be undone.\")'><i class='fas fa-trash'></i></button>";
                            echo "</form>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No users found</td></tr>";
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
$conn_users->close();
?>

