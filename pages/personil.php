<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personil Management - SPRIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Personil Management</h2>
        <p>Personil management interface - Under Development</p>
        <div class="alert alert-info">
            <strong>Info:</strong> Full personil management functionality will be available soon.
        </div>
        <a href="/pages/main.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>