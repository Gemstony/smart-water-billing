<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/User.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$userModel = new User($pdo);
$totalCustomers = count($userModel->getAllCustomers());

// Get total revenue from completed transactions
$stmt = $pdo->prepare("SELECT SUM(amount) as total_revenue FROM transactions WHERE status = 'completed'");
$stmt->execute();
$totalRevenue = $stmt->fetch()['total_revenue'] ?? 0;

// Get total water units sold (sum of units from completed transactions)
$stmt = $pdo->prepare("SELECT SUM(water_units) as total_units FROM transactions WHERE status = 'completed'");
$stmt->execute();
$totalUnitsSold = $stmt->fetch()['total_units'] ?? 0;

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Admin Dashboard</h1>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Customers</h5>
                            <p class="card-text display-6"><?= $totalCustomers ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Total Revenue</h5>
                            <p class="card-text display-6">TZS <?= number_format($totalRevenue, 2) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Water Units Sold</h5>
                            <p class="card-text display-6"><?= number_format($totalUnitsSold, 0) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-users-cog"></i> Quick Actions
                        </div>
                        <div class="card-body">
                            <a href="/smart-water-billing/users" class="btn btn-primary">
                                <i class="fas fa-list"></i> Manage Customers
                            </a>
                            <a href="#" class="btn btn-secondary">Generate Reports</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>