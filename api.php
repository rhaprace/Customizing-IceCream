<?php

session_start();
require "config.php";

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_flavors':
        getFlavors($conn);
        break;
    
    case 'get_toppings':
        getToppings($conn);
        break;
    
    case 'get_pricing':
        getPricing($conn);
        break;
    
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function getFlavors($conn) {
    $stmt = $conn->prepare("SELECT id, name, emoji, price_small, price_medium, price_large FROM flavors WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $flavors = [];
    while ($row = $result->fetch_assoc()) {
        $flavors[] = $row;
    }
    
    $stmt->close();
    echo json_encode($flavors);
}

function getToppings($conn) {
    $stmt = $conn->prepare("SELECT id, name, emoji, price FROM toppings WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $toppings = [];
    while ($row = $result->fetch_assoc()) {
        $toppings[] = $row;
    }
    
    $stmt->close();
    echo json_encode($toppings);
}

function getPricing($conn) {
    $stmt = $conn->prepare("SELECT id, name, price_small, price_medium, price_large FROM flavors WHERE is_active = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pricing = [
        'flavors' => [],
        'sizes' => [
            'small' => 'Small',
            'medium' => 'Medium',
            'large' => 'Large'
        ]
    ];
    
    while ($row = $result->fetch_assoc()) {
        $pricing['flavors'][$row['id']] = [
            'name' => $row['name'],
            'small' => floatval($row['price_small']),
            'medium' => floatval($row['price_medium']),
            'large' => floatval($row['price_large'])
        ];
    }
    
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT id, name, price FROM toppings WHERE is_active = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pricing['toppings'] = [];
    while ($row = $result->fetch_assoc()) {
        $pricing['toppings'][$row['id']] = [
            'name' => $row['name'],
            'price' => floatval($row['price'])
        ];
    }
    
    $stmt->close();
    echo json_encode($pricing);
}
