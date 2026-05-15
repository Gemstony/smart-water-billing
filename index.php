<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController($pdo);
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string from request URI (if any)
$request = strtok($request, '?');

// Normalize path – remove base folder if needed
$basePath = '/smart-water-billing';
if (strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath));
}
if ($request === '') $request = '/';

// Route definitions
switch ($request) {
    case '/':
    case '/login':
        if ($method === 'POST') {
            // Handle login submission
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            if ($auth->login($email, $password)) {
                // Redirect based on role
                if ($_SESSION['user_role'] === 'admin') {
                    header('Location: /smart-water-billing/admin');
                } else {
                    header('Location: /smart-water-billing/dashboard');
                }
            } else {
                header('Location: /smart-water-billing/login?error=1');
            }
            exit;
        } else {
            // Show login page
            require_once __DIR__ . '/views/login.php';
        }
        break;

    case '/dashboard':
        $auth->requireLogin();
        require_once __DIR__ . '/views/customers/dashboard.php';
        break;


    case '/history':
        $auth->requireLogin();
        require_once __DIR__ . '/views/customers/history.php';
        break;

    case '/buy-token':
        $auth->requireLogin();
        require_once __DIR__ . '/views/customers/buy_token.php';
        break;

    case '/admin':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/dashboard.php';
        break;

    case '/users':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/users.php';
        break;
    
    case '/reports':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/reports.php';
        break;
    
            
    case '/logout':
        $auth->logout();
        header('Location: /smart-water-billing/login');
        break;
    default:
        http_response_code(404);
        echo 'Page not found';
        break;
}