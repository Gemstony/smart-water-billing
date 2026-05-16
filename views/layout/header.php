<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default page title
$pageTitle = $pageTitle ?? 'Smart Water Billing';

// Determine user role (adapt to your auth system)
$userRole = $_SESSION['user_role'] ?? 'guest'; // 'admin' or 'customer'
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Bootstrap 5 CSS + Icons + Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Custom style -->
    <link rel="stylesheet" href="<?= BASE_URL ?? '' ?>assets/css/style.css">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h4><i class="fas fa-tint"></i> Water Billing</h4>
                <small>Smart System</small>
            </div>
            <ul class="list-unstyled components">
                <?php if ($userRole === 'admin'): ?>
                    <li>
                        <a href="dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="users">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li>
                        <a href="reports">
                            <i class="fas fa-chart-line"></i> Reports
                        </a>
                    </li>
                    <li>
                        <a href="/smart-water-billing/admin/transactions">
                            <i class="fas fa-credit-card"></i> Transactions
                        </a>
                    </li>
                    <li>
                        <a href="/smart-water-billing/admin/customer-tokens">
                            <i class="fas fa-ticket-alt"></i> Customer Tokens
                        </a>
                    </li>
                    <li>
                        <a href="/smart-water-billing/admin/rates">
                            <i class="fas fa-dollar-sign"></i> Water Rates
                        </a>
                    </li>
                    <li><a href="/smart-water-billing/admin/add_user"><i class="fas fa-user-plus"></i> Add Customer</a></li>
                <?php elseif ($userRole === 'customer'): ?>
                    <li>
                        <a href="dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="buy-token">
                            <i class="fas fa-ticket-alt"></i> Buy Token
                        </a>
                    </li>
                    <li>
                        <a href="history">
                            <i class="fas fa-history"></i> History
                        </a>
                    </li>
                    <li>
                        <a href="tokens">
                            <i class="fas fa-history"></i> Token History
                        </a>
                    </li>
                <?php else: ?>
                    <li><a href="login"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <?php endif; ?>
            </ul>
            <!-- Optional logout button at bottom -->
            <div class="sidebar-footer p-3 border-top border-secondary">
                <a href="logout" class="text-white text-decoration-none">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary">
                        <i class="fas fa-bars"></i> <span>Toggle Menu</span>
                    </button>
                    <div class="ms-auto">
                        <span class="navbar-text">
                            <i class="fas fa-user-circle"></i>
                            <?= htmlspecialchars($_SESSION['user_name'] ?? 'Guest') ?>
                        </span>
                    </div>
                </div>
            </nav>

            <!-- Main content start -->
            <div class="page-content">