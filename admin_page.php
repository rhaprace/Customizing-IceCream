<?php
session_start();
require "config.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

$totalOrders = 0;
$totalRevenue = 0;
$totalCustomers = 0;
$popularFlavor = '';
$popularFlavorEmoji = '🍦';

$result = $conn->query("SELECT COUNT(*) as count FROM orders");
if ($result) {
    $row = $result->fetch_assoc();
    $totalOrders = $row['count'];
}

$result = $conn->query("SELECT SUM(total_amount) as revenue FROM orders");
if ($result) {
    $row = $result->fetch_assoc();
    $totalRevenue = $row['revenue'] ?? 0;
}

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
if ($result) {
    $row = $result->fetch_assoc();
    $totalCustomers = $row['count'];
}

$result = $conn->query("
    SELECT f.name, f.emoji, COUNT(*) as order_count
    FROM order_items oi
    JOIN flavors f ON oi.flavor_id = f.id
    GROUP BY oi.flavor_id
    ORDER BY order_count DESC
    LIMIT 1
");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $popularFlavor = $row['name'];
    $popularFlavorEmoji = $row['emoji'];
}

$recentOrders = [];
$stmt = $conn->prepare("
    SELECT o.id, o.total_amount, o.status, o.order_date, o.completed_at, o.completion_notes,
           u.name as customer_name
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
    LIMIT 20
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recentOrders[] = $row;
}
$stmt->close();

$allUsers = [];
$stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $allUsers[] = $row;
}
$stmt->close();

$allFlavors = [];
$stmt = $conn->prepare("SELECT id, name, emoji, price_small, price_medium, price_large, is_active FROM flavors ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $allFlavors[] = $row;
}
$stmt->close();
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
            <section class="dashboard-header">
                <h2>🍦 Ice Cream Shop Admin Dashboard</h2>
            </section>
            <section class="stats-section">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-content">
                        <h3>Total Orders</h3>
                        <p class="stat-number"><?php echo $totalOrders; ?></p>
                        <span class="stat-label">All time</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <h3>Revenue</h3>
                        <p class="stat-number">$<?php echo number_format($totalRevenue, 2); ?></p>
                        <span class="stat-label">Total earnings</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3>Customers</h3>
                        <p class="stat-number"><?php echo $totalCustomers; ?></p>
                        <span class="stat-label">Active users</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><?php echo $popularFlavorEmoji; ?></div>
                    <div class="stat-content">
                        <h3>Popular Flavor</h3>
                        <p class="stat-number"><?php echo $popularFlavor ?: 'N/A'; ?></p>
                        <span class="stat-label">Most ordered</span>
                    </div>
                </div>
            </section>
            <section class="admin-grid">
                <div class="admin-panel full-width">
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
                                <?php if (empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; color: #a5778a; padding: 20px;">
                                            No orders yet
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT oi.*, f.name as flavor_name, f.emoji as flavor_emoji
                                            FROM order_items oi
                                            JOIN flavors f ON oi.flavor_id = f.id
                                            WHERE oi.order_id = ?
                                        ");
                                        $stmt->bind_param("i", $order['id']);
                                        $stmt->execute();
                                        $itemsResult = $stmt->get_result();
                                        $items = [];
                                        while ($item = $itemsResult->fetch_assoc()) {
                                            $items[] = $item;
                                        }
                                        $stmt->close();

                                        $firstItem = $items[0] ?? null;
                                        $itemCount = count($items);
                                        ?>
                                        <tr>
                                            <td>#<?php echo str_pad($order['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td>
                                                <?php if ($firstItem): ?>
                                                    <?php echo $firstItem['flavor_emoji'] . ' ' . $firstItem['flavor_name']; ?>
                                                    <?php if ($itemCount > 1): ?>
                                                        <br><small>(+<?php echo $itemCount - 1; ?> more)</small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $firstItem ? ucfirst($firstItem['size']) : 'N/A'; ?></td>
                                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <select class="status-select" onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value, this)">
                                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                                    <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>🔄 Processing</option>
                                                    <option value="paid" <?php echo $order['status'] == 'paid' ? 'selected' : ''; ?>>💳 Paid</option>
                                                    <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>✓ Completed</option>
                                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>✗ Cancelled</option>
                                                </select>
                                            </td>
                                            <td>
                                                <button class="action-btn" onclick="viewOrder(<?php echo $order['id']; ?>)">View</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
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
                                <?php if (empty($allUsers)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center; color: #a5778a; padding: 20px;">
                                            No users found
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($allUsers as $user): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($user['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="role-badge <?php echo $user['role']; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button class="action-btn delete-btn" onclick="deleteUser(<?php echo $user['id']; ?>)">Delete</button>
                                                <?php else: ?>
                                                    <span style="color: #a5778a;">Current User</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="admin-panel">
                    <h2>🍨 Manage Flavors</h2>
                    <div class="flavor-controls">
                        <div class="control-group">
                            <label for="sortFlavors">Sort by:</label>
                            <select id="sortFlavors" onchange="sortFlavors(this.value)">
                                <option value="name-asc">Name (A-Z)</option>
                                <option value="name-desc">Name (Z-A)</option>
                                <option value="price-asc">Price (Low to High)</option>
                                <option value="price-desc">Price (High to Low)</option>
                            </select>
                        </div>
                        <div class="control-group">
                            <label for="filterFlavors">Filter:</label>
                            <select id="filterFlavors" onchange="filterFlavors(this.value)">
                                <option value="all">All Flavors</option>
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>
                    </div>
                    <div class="flavors-container">
                        <div class="flavors-grid" id="flavorsGrid">
                            <?php if (empty($allFlavors)): ?>
                                <p style="text-align: center; color: #a5778a; padding: 20px; grid-column: 1 / -1;">No flavors found</p>
                            <?php else: ?>
                                <?php foreach ($allFlavors as $flavor): ?>
                                    <div class="flavor-item <?php echo $flavor['is_active'] ? '' : 'inactive'; ?>"
                                         data-name="<?php echo htmlspecialchars($flavor['name']); ?>"
                                         data-price="<?php echo $flavor['price_medium']; ?>"
                                         data-status="<?php echo $flavor['is_active'] ? 'active' : 'inactive'; ?>">
                                        <span class="flavor-emoji"><?php echo $flavor['emoji']; ?></span>
                                        <h4><?php echo htmlspecialchars($flavor['name']); ?></h4>
                                        <p>
                                            S: $<?php echo number_format($flavor['price_small'], 2); ?> |
                                            M: $<?php echo number_format($flavor['price_medium'], 2); ?> |
                                            L: $<?php echo number_format($flavor['price_large'], 2); ?>
                                        </p>
                                        <p style="font-size: 0.85em; color: <?php echo $flavor['is_active'] ? '#4CAF50' : '#f44336'; ?>;">
                                            <?php echo $flavor['is_active'] ? '✓ Active' : '✗ Inactive'; ?>
                                        </p>
                                        <button class="action-btn" onclick="editFlavor(<?php echo $flavor['id']; ?>)">Edit</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('orderModal')">&times;</span>
            <h2>Order Details</h2>
            <div id="orderDetails"></div>
        </div>
    </div>
    <div id="flavorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('flavorModal')">&times;</span>
            <h2>Edit Flavor</h2>
            <div id="flavorForm"></div>
        </div>
    </div>
    <div id="completeOrderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('completeOrderModal')">&times;</span>
            <h2>Complete Order</h2>
            <p>Mark this order as completed?</p>
            <form id="completeOrderForm" onsubmit="confirmCompleteOrder(event)">
                <div class="form-group">
                    <label>Completion Notes (Optional):</label>
                    <textarea name="notes" rows="4" placeholder="Add any notes about the order completion..."></textarea>
                </div>
                <button type="submit" class="btn-save">✓ Mark as Completed</button>
                <button type="button" class="btn-cancel" onclick="closeModal('completeOrderModal')">Cancel</button>
            </form>
        </div>
    </div>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
        .order-item {
            background: #f9f9f9;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #d4508a;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn-save {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-save:hover {
            background: #45a049;
        }
        .inactive {
            opacity: 0.6;
        }
        .status-select {
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            cursor: pointer;
            font-size: 14px;
        }
        .status-select:hover {
            border-color: #d4508a;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            resize: vertical;
        }
        .btn-cancel {
            background: #999;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
        }
        .btn-cancel:hover {
            background: #777;
        }
    </style>
</body>
<script>
async function viewOrder(orderId) {
    try {
        const response = await fetch('admin_api.php?action=get_order&id=' + orderId);
        const data = await response.json();

        if (data.success) {
            let statusBadge = '';
            switch(data.order.status) {
                case 'pending': statusBadge = '⏳ Pending'; break;
                case 'processing': statusBadge = '🔄 Processing'; break;
                case 'paid': statusBadge = '💳 Paid'; break;
                case 'completed': statusBadge = '✓ Completed'; break;
                case 'cancelled': statusBadge = '✗ Cancelled'; break;
                default: statusBadge = data.order.status;
            }

            let html = `
                <div style="margin-bottom: 20px;">
                    <p><strong>Order ID:</strong> #${String(data.order.id).padStart(3, '0')}</p>
                    <p><strong>Customer:</strong> ${data.order.customer_name}</p>
                    <p><strong>Email:</strong> ${data.order.customer_email}</p>
                    <p><strong>Order Date:</strong> ${new Date(data.order.order_date).toLocaleString()}</p>
                    <p><strong>Status:</strong> <span style="color: ${data.order.status === 'completed' ? '#4CAF50' : '#d4508a'};">${statusBadge}</span></p>
                    <p><strong>Total:</strong> $${parseFloat(data.order.total_amount).toFixed(2)}</p>
            `;
            if (data.order.status === 'completed' && data.order.completed_at) {
                html += `
                    <hr style="margin: 15px 0; border: none; border-top: 1px solid #ddd;">
                    <p><strong>Completed At:</strong> ${new Date(data.order.completed_at).toLocaleString()}</p>
                `;
                if (data.order.completion_notes) {
                    html += `<p><strong>Completion Notes:</strong> ${data.order.completion_notes}</p>`;
                }
            }

            html += `</div><h3>Order Items:</h3>`;

            data.items.forEach(item => {
                html += `
                    <div class="order-item">
                        <p><strong>${item.flavor_emoji} ${item.flavor_name}</strong></p>
                        <p>Size: ${item.size.charAt(0).toUpperCase() + item.size.slice(1)}</p>
                        <p>Quantity: ${item.quantity}</p>
                        <p>Price: $${parseFloat(item.price_per_item).toFixed(2)} each</p>
                        ${item.toppings && item.toppings.length > 0 ?
                            '<p>Toppings: ' + item.toppings.map(t => t.emoji + ' ' + t.name).join(', ') + '</p>'
                            : ''}
                        <p><strong>Subtotal: $${parseFloat(item.total_price).toFixed(2)}</strong></p>
                    </div>
                `;
            });

            document.getElementById('orderDetails').innerHTML = html;
            document.getElementById('orderModal').style.display = 'block';
        } else {
            alert('Failed to load order details: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load order details');
    }
}

async function editFlavor(flavorId) {
    try {
        const response = await fetch('admin_api.php?action=get_flavor&id=' + flavorId);
        const data = await response.json();

        if (data.success) {
            const flavor = data.flavor;
            const html = `
                <form id="editFlavorForm" onsubmit="saveFlavor(event, ${flavorId})">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" value="${flavor.name}" required>
                    </div>
                    <div class="form-group">
                        <label>Emoji:</label>
                        <input type="text" name="emoji" value="${flavor.emoji}" required maxlength="2">
                    </div>
                    <div class="form-group">
                        <label>Small Price ($):</label>
                        <input type="number" name="price_small" value="${flavor.price_small}" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Medium Price ($):</label>
                        <input type="number" name="price_medium" value="${flavor.price_medium}" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Large Price ($):</label>
                        <input type="number" name="price_large" value="${flavor.price_large}" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Status:</label>
                        <select name="is_active">
                            <option value="1" ${flavor.is_active == 1 ? 'selected' : ''}>Active</option>
                            <option value="0" ${flavor.is_active == 0 ? 'selected' : ''}>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-save">Save Changes</button>
                </form>
            `;

            document.getElementById('flavorForm').innerHTML = html;
            document.getElementById('flavorModal').style.display = 'block';
        } else {
            alert('Failed to load flavor details: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load flavor details');
    }
}

async function saveFlavor(event, flavorId) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);

    const data = {
        action: 'update_flavor',
        id: flavorId,
        name: formData.get('name'),
        emoji: formData.get('emoji'),
        price_small: formData.get('price_small'),
        price_medium: formData.get('price_medium'),
        price_large: formData.get('price_large'),
        is_active: formData.get('is_active')
    };

    try {
        const response = await fetch('admin_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            alert('Flavor updated successfully!');
            closeModal('flavorModal');
            location.reload();
        } else {
            alert('Failed to update flavor: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update flavor');
    }
}

let currentOrderId = null;
let currentSelectElement = null;

async function updateOrderStatus(orderId, newStatus, selectElement) {
    if (newStatus === 'completed') {
        currentOrderId = orderId;
        currentSelectElement = selectElement;
        document.getElementById('completeOrderModal').style.display = 'block';
        return;
    }
    await performStatusUpdate(orderId, newStatus, selectElement, '');
}

async function confirmCompleteOrder(event) {
    event.preventDefault();

    const form = event.target;
    const notes = form.notes.value;

    closeModal('completeOrderModal');

    await performStatusUpdate(currentOrderId, 'completed', currentSelectElement, notes);

    currentOrderId = null;
    currentSelectElement = null;
}

async function performStatusUpdate(orderId, newStatus, selectElement, notes) {
    try {
        const response = await fetch('admin_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'update_order_status',
                id: orderId,
                status: newStatus,
                notes: notes
            })
        });

        const data = await response.json();

        if (data.success) {
            const originalBg = selectElement.style.backgroundColor;
            selectElement.style.backgroundColor = '#4CAF50';
            selectElement.style.color = 'white';

            setTimeout(() => {
                selectElement.style.backgroundColor = originalBg;
                selectElement.style.color = '';
            }, 1000);
            if (newStatus === 'completed') {
                alert('✓ Order marked as completed!');
            }
        } else {
            alert('Failed to update order status: ' + data.message);
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update order status: ' + error.message);
        location.reload();
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        fetch('admin_api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'delete_user', id: userId})
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully!');
                location.reload();
            } else {
                alert('Failed to delete user: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete user');
        });
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

function sortFlavors(sortBy) {
    const grid = document.getElementById('flavorsGrid');
    const items = Array.from(grid.querySelectorAll('.flavor-item'));
    items.sort((a, b) => {
        switch(sortBy) {
            case 'name-asc':
                return a.dataset.name.localeCompare(b.dataset.name);
            case 'name-desc':
                return b.dataset.name.localeCompare(a.dataset.name);
            case 'price-asc':
                return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            case 'price-desc':
                return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            default:
                return 0;
        }
    });
    while (grid.firstChild) {
        grid.removeChild(grid.firstChild);
    }
    items.forEach(item => grid.appendChild(item));
    const currentFilter = document.getElementById('filterFlavors').value;
    if (currentFilter !== 'all') {
        filterFlavors(currentFilter);
    }
}

function filterFlavors(filterBy) {
    const items = document.getElementsByClassName('flavor-item');
    let visibleCount = 0;

    Array.from(items).forEach(item => {
        const status = item.dataset.status;

        switch(filterBy) {
            case 'all':
                item.style.display = '';
                visibleCount++;
                break;
            case 'active':
                if (status === 'active') {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
                break;
            case 'inactive':
                if (status === 'inactive') {
                    item.style.display = '';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
                break;
        }
    });
    const grid = document.getElementById('flavorsGrid');
    let noResultsMsg = grid.querySelector('.no-results-message');

    if (visibleCount === 0) {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('p');
            noResultsMsg.className = 'no-results-message';
            noResultsMsg.style.cssText = 'text-align: center; color: #a5778a; padding: 40px; grid-column: 1 / -1; font-size: 1.1rem;';
            noResultsMsg.textContent = 'No flavors match the selected filter.';
            grid.appendChild(noResultsMsg);
        }
    } else {
        if (noResultsMsg) {
            noResultsMsg.remove();
        }
    }
}
</script>

</html>
