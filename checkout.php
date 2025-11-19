<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

$input = file_get_contents('php://input');

session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "login_db";

$conn = new mysqli($host, $user, $pass, $dbname);

ob_end_clean();
header('Content-Type: application/json');

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

$data = json_decode($input, true);

if (!$data || !isset($data['cart']) || !isset($data['total'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$cart = $data['cart'];
$total = floatval($data['total']);
$userId = $_SESSION['user_id'];

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("id", $userId, $total);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create order");
    }
    
    $orderId = $conn->insert_id;
    $stmt->close();
    
    $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, flavor_id, size, quantity, price_per_item, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtTopping = $conn->prepare("INSERT INTO order_item_toppings (order_item_id, topping_id) VALUES (?, ?)");
    
    foreach ($cart as $item) {
        $flavorId = intval($item['flavorId']);
        $size = $item['size'];
        $quantity = intval($item['quantity']);
        $pricePerItem = floatval($item['pricePerItem']);
        $totalPrice = floatval($item['totalPrice']);

        $stmtItem->bind_param("iisidd", $orderId, $flavorId, $size, $quantity, $pricePerItem, $totalPrice);
        
        if (!$stmtItem->execute()) {
            throw new Exception("Failed to add item to order");
        }
        
        $orderItemId = $conn->insert_id;
        
        if (isset($item['toppings']) && is_array($item['toppings'])) {
            foreach ($item['toppings'] as $toppingId) {
                $toppingIdInt = intval($toppingId);
                $stmtTopping->bind_param("ii", $orderItemId, $toppingIdInt);
                
                if (!$stmtTopping->execute()) {
                    throw new Exception("Failed to add topping to order item");
                }
            }
        }
    }
    
    $stmtItem->close();
    $stmtTopping->close();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'message' => 'Order placed successfully'
    ]);
    exit();

} catch (Exception $e) {
    $conn->rollback();

    echo json_encode([
        'success' => false,
        'message' => 'Failed to process order: ' . $e->getMessage()
    ]);
    exit();
}

if (isset($conn)) {
    $conn->close();
}
?>
