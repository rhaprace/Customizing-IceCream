<?php
session_start();
require "config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'get_order') {
        $orderId = intval($_GET['id'] ?? 0);
        
        $stmt = $conn->prepare("
            SELECT o.*, u.name as customer_name, u.email as customer_email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit();
        }
        
        $order = $result->fetch_assoc();
        $stmt->close();
        
        $stmt = $conn->prepare("
            SELECT oi.*, f.name as flavor_name, f.emoji as flavor_emoji
            FROM order_items oi
            JOIN flavors f ON oi.flavor_id = f.id
            WHERE oi.order_id = ?
        ");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $items = [];
        while ($item = $result->fetch_assoc()) {
            $stmtTopping = $conn->prepare("
                SELECT t.name, t.emoji
                FROM order_item_toppings oit
                JOIN toppings t ON oit.topping_id = t.id
                WHERE oit.order_item_id = ?
            ");
            $stmtTopping->bind_param("i", $item['id']);
            $stmtTopping->execute();
            $toppingResult = $stmtTopping->get_result();
            
            $toppings = [];
            while ($topping = $toppingResult->fetch_assoc()) {
                $toppings[] = $topping;
            }
            $stmtTopping->close();
            
            $item['toppings'] = $toppings;
            $items[] = $item;
        }
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'order' => $order,
            'items' => $items
        ]);
        
    } elseif ($action === 'get_flavor') {
        $flavorId = intval($_GET['id'] ?? 0);
        
        $stmt = $conn->prepare("SELECT * FROM flavors WHERE id = ?");
        $stmt->bind_param("i", $flavorId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Flavor not found']);
            exit();
        }
        
        $flavor = $result->fetch_assoc();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'flavor' => $flavor
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request data',
            'debug' => [
                'input' => $input,
                'json_error' => json_last_error_msg()
            ]
        ]);
        exit();
    }

    $action = $data['action'] ?? '';
    
    if ($action === 'update_flavor') {
        $id = intval($data['id'] ?? 0);
        $name = $data['name'] ?? '';
        $emoji = $data['emoji'] ?? '';
        $priceSmall = floatval($data['price_small'] ?? 0);
        $priceMedium = floatval($data['price_medium'] ?? 0);
        $priceLarge = floatval($data['price_large'] ?? 0);
        $isActive = intval($data['is_active'] ?? 1);
        
        $stmt = $conn->prepare("
            UPDATE flavors 
            SET name = ?, emoji = ?, price_small = ?, price_medium = ?, price_large = ?, is_active = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssdddii", $name, $emoji, $priceSmall, $priceMedium, $priceLarge, $isActive, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Flavor updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update flavor']);
        }
        $stmt->close();
        
    } elseif ($action === 'update_order_status') {
        $orderId = intval($data['id'] ?? 0);
        $status = $data['status'] ?? '';
        $notes = $data['notes'] ?? '';

        $validStatuses = ['pending', 'processing', 'paid', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit();
        }

        if ($status === 'completed') {
            $adminId = $_SESSION['user_id'];
            $stmt = $conn->prepare("
                UPDATE orders
                SET status = ?,
                    completed_at = NOW(),
                    completed_by = ?,
                    completion_notes = ?
                WHERE id = ?
            ");
            $stmt->bind_param("sisi", $status, $adminId, $notes, $orderId);
        } else {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $status, $orderId);
        }

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
        }
        $stmt->close();

    } elseif ($action === 'delete_user') {
        $userId = intval($data['id'] ?? 0);

        if ($userId == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
        $stmt->close();

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>

