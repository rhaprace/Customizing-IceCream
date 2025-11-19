<?php
header('Content-Type: application/json');

$input = file_get_contents('php://input');

$response = [
    'received_input' => $input,
    'input_length' => strlen($input),
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
    'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'not set',
    'decoded_data' => json_decode($input, true),
    'json_error' => json_last_error_msg()
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>

