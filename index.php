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
        // Show public welcome page
        require_once __DIR__ . '/views/welcome.php';
        break;

    case '/login':
        if ($method === 'POST') {
            // Handle login submission
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            if ($auth->login($email, $password)) {
                if ($_SESSION['user_role'] === 'admin') {
                    header('Location: /smart-water-billing/admin/dashboard');
                } else {
                    header('Location: /smart-water-billing/dashboard');
                }
            } else {
                header('Location: /smart-water-billing/login?error=1');
            }
            exit;
        } else {
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

    case '/tokens':
        $auth->requireLogin();
        require_once __DIR__ . '/views/customers/token_history.php';
        break;

    case '/admin/dashboard':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/dashboard.php';
        break;

    case '/admin/users':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/users.php';
        break;
    
    case '/admin/reports':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/reports.php';
        break;
    
            
    case '/logout':
        $auth->logout();
        header('Location: /smart-water-billing/login');
        break;
    case '/admin/logout':
        $auth->logout();
        header('Location: /smart-water-billing/login');
        break;
    
    case '/admin/transactions':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/transactions.php';
        break;
        
    case '/admin/customer-tokens':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/customer_tokens.php';
        break;
        
    case '/admin/rates':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/rates.php';
        break;
    case '/admin/add_user':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/add_user.php';
        break;
    case '/admin/edit_user':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/edit_user.php';
        break;
    case '/admin/view_customer':
        $auth->requireAdmin();
        require_once __DIR__ . '/views/admin/view_customer.php';
        break;
    default:
        http_response_code(404);
        echo 'Page not found';
        break;
}