<?php
// Simple fallback page for when the main application can't boot

// Set content type
header('Content-Type: text/html; charset=UTF-8');

// Get the requested URI
$uri = $_SERVER['REQUEST_URI'] ?? '/';

// Simple routing
if ($uri === '/health' || $uri === '/health/') {
    // Health check endpoint
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'message' => 'Application is running in fallback mode']);
    exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIZA - Site Maintenance</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .maintenance-container {
            max-width: 800px;
            margin: 4rem auto;
            padding: 2rem;
            text-align: center;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .maintenance-icon {
            font-size: 4rem;
            color: #b5a79c;
            margin-bottom: 1rem;
        }
        .maintenance-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #333;
        }
        .maintenance-message {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
        }
        .maintenance-info {
            font-size: 0.9rem;
            color: #999;
        }
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #222;
                color: #e0e0e0;
            }
            .maintenance-container {
                background: #333;
            }
            .maintenance-title {
                color: #e0e0e0;
            }
            .maintenance-message {
                color: #ccc;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">🛠️</div>
        <h1 class="maintenance-title">Site Maintenance</h1>
        <p class="maintenance-message">Our site is currently undergoing maintenance. Please check back soon.</p>
        <p class="maintenance-info">We're working to improve our service and will be back online shortly.</p>
    </div>
</body>
</html> 