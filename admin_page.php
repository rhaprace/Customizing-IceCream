<?php
session_start();
require "config.php"; 
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css?v=1">
</head>
<body>
    <div class="admin-container">
        <header>
            <h1>Welcome Admin, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </header>

        <main>
            <section class="dashboard">
                <h2>🍦 Ice Cream Shop Admin Dashboard</h2>
                
                <div class="admin-stats">
                    <div class="stat-card">
                        <h3>Total Orders</h3>
                        <p class="stat-number">42</p>
                        <span class="stat-label">This month</span>
                    </div>
                    <div class="stat-card">
                        <h3>Revenue</h3>
                        <p class="stat-number">$589.50</p>
                        <span class="stat-label">Total earnings</span>
                    </div>
                    <div class="stat-card">
                        <h3>Customers</h3>
                        <p class="stat-number">28</p>
                        <span class="stat-label">Active users</span>
                    </div>
                    <div class="stat-card">
                        <h3>Popular Flavor</h3>
                        <p class="stat-number">🍫</p>
                        <span class="stat-label">Chocolate</span>
                    </div>
                </div>
            </section>

            <section class="admin-section">
                <div class="admin-panel">
                    <h2>📋 Recent Orders</h2>
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Flavor</th>
                                    <th>Size</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#001</td>
                                    <td>Sarah Johnson</td>
                                    <td>🍨 Vanilla</td>
                                    <td>Large</td>
                                    <td>$7.99</td>
                                    <td><span class="status completed">✓ Completed</span></td>
                                    <td><button class="action-btn" onclick="viewOrder(1)">View</button></td>
                                </tr>
                                <tr>
                                    <td>#002</td>
                                    <td>Emily Davis</td>
                                    <td>🍫 Chocolate</td>
                                    <td>Medium</td>
                                    <td>$6.50</td>
                                    <td><span class="status pending">⏳ Pending</span></td>
                                    <td><button class="action-btn" onclick="updateOrder(2)">Process</button></td>
                                </tr>
                               
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-panel">
                    <h2>👥 Manage Users</h2>
                    <div class="users-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>U001</td>
                                    <td>Sarah Johnson</td>
                                    <td>sarah@email.com</td>
                                    <td><span class="role-badge user">User</span></td>
                                    <td>Nov 10, 2025</td>
                                    <td><button class="action-btn delete-btn" onclick="deleteUser(1)">Delete</button></td>
                                </tr>
                               
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-panel">
                    <h2>🍨 Manage Flavors</h2>
                    <div class="flavors-grid">
                        <div class="flavor-item">
                            <span class="flavor-emoji">🍨</span>
                            <h4>Vanilla</h4>
                            <p>Stock: 45 units</p>
                            <button class="action-btn" onclick="editFlavor('vanilla')">Edit</button>
                        </div>
                        <div class="flavor-item">
                            <span class="flavor-emoji">🍫</span>
                            <h4>Chocolate</h4>
                            <p>Stock: 32 units</p>
                            <button class="action-btn" onclick="editFlavor('chocolate')">Edit</button>
                        </div>
                        <div class="flavor-item">
                            <span class="flavor-emoji">🍓</span>
                            <h4>Strawberry</h4>
                            <p>Stock: 28 units</p>
                            <button class="action-btn" onclick="editFlavor('strawberry')">Edit</button>
                        </div>
                        <div class="flavor-item">
                            <span class="flavor-emoji">🌿</span>
                            <h4>Mint Chocolate</h4>
                            <p>Stock: 35 units</p>
                            <button class="action-btn" onclick="editFlavor('mint')">Edit</button>
                        </div>
                        <div class="flavor-item">
                            <span class="flavor-emoji">🍯</span>
                            <h4>Caramel</h4>
                            <p>Stock: 40 units</p>
                            <button class="action-btn" onclick="editFlavor('caramel')">Edit</button>
                        </div>
                        <div class="flavor-item">
                            <span class="flavor-emoji">💚</span>
                            <h4>Pistachio</h4>
                            <p>Stock: 25 units</p>
                            <button class="action-btn" onclick="editFlavor('pistachio')">Edit</button>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
<script>
function viewOrder(orderId) {
    alert('Viewing order #' + orderId);
}

function updateOrder(orderId) {
    alert('Order #' + orderId + ' has been processed!');
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        alert('User #' + userId + ' has been deleted!');
    }
}

function editFlavor(flavorName) {
    alert('Editing ' + flavorName + ' flavor...');
}
</script>

</html>
