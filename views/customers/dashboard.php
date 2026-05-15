<?php
// Example dashboard for customer
$pageTitle = 'Customer Dashboard';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Customer') ?></h1>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Current Balance</h5>
                            <p class="card-text display-6">KES 1,250.00</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Last Reading</h5>
                            <p class="card-text display-6">124 m³</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Active Tokens</h5>
                            <p class="card-text display-6">3</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Add more widgets as needed -->
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>