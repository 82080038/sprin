<?php
// Simple stats API for testing
header("Content-Type: application/json");
echo json_encode([
    'success' => true,
    'total_personil' => 0,
    'message' => 'API working'
]);
?>
