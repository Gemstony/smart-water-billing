<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../controllers/DashboardController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-water-billing/login');
    exit;
}

$dashboardController = new DashboardController($pdo);
$data = $dashboardController->getCustomerData($_SESSION['user_id']);
$user = $data['user'];
$lastUsage = $data['lastUsage'];
$recentTransactions = $data['recentTransactions'];
$recentUsage = $data['recentUsage'];
$chartDates = $data['chartDates'];
$chartUsage = $data['chartUsage'];

$pageTitle = 'Customer Dashboard';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Customer') ?></h1>
            
            <!-- Stats Cards -->
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Current Balance</h5>
                            <p class="card-text display-6"><?= number_format($user['account_balance'], 2) ?> units</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Meter ID</h5>
                            <p class="card-text display-6"><?= htmlspecialchars($user['meter_id'] ?? 'Not assigned') ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Last Reading</h5>
                            <p class="card-text display-6">
                                <?= $lastUsage ? number_format($lastUsage['water_used'], 2) . ' m³' : 'No data' ?>
                            </p>
                            <small><?= $lastUsage ? date('d M Y H:i', strtotime($lastUsage['log_time'])) : '' ?></small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Action Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">Quick Actions</div>
                        <div class="card-body">
                            <a href="/smart-water-billing/buy-token" class="btn btn-primary me-2">
                                <i class="fas fa-ticket-alt"></i> Buy Token
                            </a>
                            <a href="/smart-water-billing/tokens" class="btn btn-secondary me-2">
                                <i class="fas fa-history"></i> Token History
                            </a>
                            <a href="/smart-water-billing/history" class="btn btn-info">
                                <i class="fas fa-chart-line"></i> View Full History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Chart Section -->
            <!-- <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">Water Consumption (Last 7 Days)</div>
                        <div class="card-body">
                            <canvas id="consumptionChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
             -->
            <!-- Recent Transactions and Usage -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">Recent Payments</div>
                        <div class="card-body">
                            <?php if (count($recentTransactions) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr><th>Amount (TZS)</th><th>Units</th><th>Date</th><th>Status</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentTransactions as $tx): ?>
                                            <tr>
                                                <td><?= number_format($tx['amount'], 2) ?></td>
                                                <td><?= $tx['water_units'] ?></td>
                                                <td><?= date('d M H:i', strtotime($tx['created_at'])) ?></td>
                                                <td><span class="badge bg-<?= $tx['status'] === 'completed' ? 'success' : ($tx['status'] === 'failed' ? 'danger' : 'warning') ?>"><?= $tx['status'] ?></span></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No payments yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">Recent Water Usage</div>
                        <div class="card-body">
                            <?php if (count($recentUsage) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr><th>Water Used (m³)</th><th>Date & Time</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentUsage as $usage): ?>
                                            <tr>
                                                <td><?= number_format($usage['water_used'], 2) ?></td>
                                                <td><?= date('d M H:i', strtotime($usage['log_time'])) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No usage recorded yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // Prepare chart data
    const ctx = document.getElementById('consumptionChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartDates) ?>,
            datasets: [{
                label: 'Water Used (m³)',
                data: <?= json_encode($chartUsage) ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: { callbacks: { label: function(context) { return context.raw + ' m³'; } } }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Cubic Meters (m³)' } },
                x: { title: { display: true, text: 'Date' } }
            }
        }
    });
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>