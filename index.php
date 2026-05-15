<?php
// Front controller - route all requests

$request = $_SERVER['REQUEST_URI'];

// Basic routing
switch ($request) {
    case '/':
    case '/smart-water-billing/':
        require_once __DIR__ . '/views/login.php';
        break;
    case '/smart-water-billing/login':
        require_once __DIR__ . '/views/login.php';
        break;
    case '/smart-water-billing/dashboard':
        require_once __DIR__ . '/views/customers/dashboard.php';
        break;
    case '/smart-water-billing/buy-token':
        require_once __DIR__ . '/views/customers/buy_token.php';
        break;
    case '/smart-water-billing/history':
        require_once __DIR__ . '/views/customers/history.php';
        break;
    case '/smart-water-billing/admin':
        require_once __DIR__ . '/views/admin/dashboard.php';
        break;
    default:
        http_response_code(404);
        echo 'Page not found';
        break;
}