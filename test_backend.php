<?php
header('Content-Type: application/json');

// Simple test response
echo json_encode([
    'success' => true, 
    'message' => 'Test backend is working!',
    'data' => ['test' => 'value']
]);
?>