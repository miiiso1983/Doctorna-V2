<?php
/**
 * Test Login Endpoint
 * For testing purposes only
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log for debugging
error_log("Test Login - Raw input: " . $input);
error_log("Test Login - Decoded data: " . print_r($data, true));
error_log("Test Login - POST data: " . print_r($_POST, true));

// Check if data is valid
if (!$data || !isset($data['email']) || !isset($data['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required',
        'debug' => [
            'raw_input' => $input,
            'decoded_data' => $data,
            'post_data' => $_POST,
            'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set'
        ]
    ]);
    exit();
}

// Return success for testing
echo json_encode([
    'success' => true,
    'message' => 'Login successful (test mode)',
    'data' => [
        'token' => 'test_token_123',
        'refresh_token' => 'test_refresh_token_456',
        'user' => [
            'id' => 1,
            'name' => 'Test User',
            'email' => $data['email'],
            'role' => 'patient',
            'phone' => '1234567890',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ],
        'profile' => null
    ]
]);

