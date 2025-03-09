<?php
// Direct index.php file that doesn't rely on Symfony
// This will be used for the initial deployment to ensure the application starts

// Set content type
header('Content-Type: text/html; charset=UTF-8');

// Get the requested URI
$uri = $_SERVER['REQUEST_URI'] ?? '/';

// Simple routing
if ($uri === '/health' || $uri === '/health/') {
    // Health check endpoint
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok', 
        'message' => 'Application is running in standalone mode',
        'timestamp' => time(),
        'environment' => [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'hostname' => gethostname(),
        ]
    ]);
    exit;
}

// Display environment variables (only for debugging)
$env_vars = [];
foreach ($_ENV as $key => $value) {
    // Don't show sensitive information
    if (!in_array(strtolower($key), ['app_secret', 'database_url', 'paypal_client_secret'])) {
        $env_vars[$key] = $value;
    }
}

// Get server status
$server_status = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'hostname' => gethostname(),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
];

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIZA - Deployment in Progress</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 4rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .icon {
            font-size: 4rem;
            color: #b5a79c;
            margin-bottom: 1rem;
        }
        .title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #333;
        }
        .message {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
            text-align: center;
        }
        .info {
            font-size: 0.9rem;
            color: #999;
            text-align: center;
            margin-bottom: 2rem;
        }
        .section {
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .section h2 {
            margin-top: 0;
            font-size: 1.2rem;
            color: #333;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 0.5rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .data-table th {
            font-weight: bold;
            color: #333;
        }
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #222;
                color: #e0e0e0;
            }
            .container {
                background: #333;
            }
            .title {
                color: #e0e0e0;
            }
            .message {
                color: #ccc;
            }
            .section {
                background: #444;
            }
            .section h2 {
                color: #e0e0e0;
            }
            .data-table th {
                color: #e0e0e0;
            }
            .data-table th, .data-table td {
                border-bottom: 1px solid #555;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">🚀</div>
            <h1 class="title">MIZA - Deployment in Progress</h1>
        </div>
        
        <p class="message">Your application is being deployed. Please check back soon.</p>
        <p class="info">This is a temporary page while your application is being set up.</p>
        
        <div class="section">
            <h2>Server Information</h2>
            <table class="data-table">
                <?php foreach ($server_status as $key => $value): ?>
                <tr>
                    <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?></th>
                    <td><?= htmlspecialchars($value) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        
        <div class="section">
            <h2>Environment Variables</h2>
            <table class="data-table">
                <?php foreach ($env_vars as $key => $value): ?>
                <tr>
                    <th><?= htmlspecialchars($key) ?></th>
                    <td><?= htmlspecialchars($value) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>
