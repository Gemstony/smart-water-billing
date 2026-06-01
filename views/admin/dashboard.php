<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../controllers/AdminDashboardController.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$dashboardController = new AdminDashboardController($pdo);
$data = $dashboardController->getDashboardData();

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Admin Dashboard</h1>

            <!-- Key Metrics Row -->
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">Total Customers</h5>
                            <p class="card-text display-6"><?= $data['totalCustomers'] ?></p>
                            <small>Active meters: <?= $data['activeMeters'] ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Total Revenue</h5>
                            <p class="card-text display-6">TZS <?= number_format($data['totalRevenue'], 0) ?></p>
                            <small>Today: TZS <?= number_format($data['todayRevenue'], 0) ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title">Water Units Sold</h5>
                            <p class="card-text display-6"><?= number_format($data['totalUnitsSold'], 0) ?> m³</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Pending Transactions</h5>
                            <p class="card-text display-6"><?= $data['pendingTransactions'] ?></p>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Quick Actions Row -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">Quick Actions</div>
                        <div class="card-body">
                            <a href="/smart-water-billing/admin/users" class="btn btn-primary">Manage Customers</a>
                            <a href="/smart-water-billing/admin/reports" class="btn btn-secondary">View Reports</a>
                            <a href="/smart-water-billing/admin/rates" class="btn btn-info">Manage Rates</a>
                            <a href="/smart-water-billing/admin/add_user" class="btn btn-success">Add New Customer</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Row -->
            <!-- <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">Revenue (Last 30 Days)</div>
                        <div class="card-body">
                            <canvas id="revenueChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">Water Usage Trend (Last 7 Days)</div>
                        <div class="card-body">
                            <canvas id="usageChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Second Row: Top Customers & Active/Inactive -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">Top 5 Customers by Units Purchased</div>
                        <div class="card-body">
                            <?php if (count($data['topCustomers']) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Total Units (m³)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['topCustomers'] as $customer): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($customer['full_name']) ?><br><small><?= htmlspecialchars($customer['email']) ?></small>
                                                    </td>
                                                    <td><?= number_format($customer['total_units'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No data available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">Customer Status</div>
                        <div class="card-body">
                            <canvas id="statusChart" height="200"></canvas>
                            <div class="mt-3">
                                <p>Active: <?= $data['activeCustomers'] ?> | Inactive: <?= $data['inactiveCustomers'] ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">Recent Transactions</div>
                        <div class="card-body">
                            <?php if (count($data['recentTransactions']) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Amount (TZS)</th>
                                                <th>Units</th>
                                                <th>Method</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['recentTransactions'] as $tx): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($tx['full_name']) ?></td>
                                                    <td><?= number_format($tx['amount'], 2) ?></td>
                                                    <td><?= $tx['water_units'] ?></td>
                                                    <td><?= $tx['payment_method'] ?></td>
                                                    <td><span
                                                            class="badge bg-<?= $tx['status'] === 'completed' ? 'success' : ($tx['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= $tx['status'] ?></span>
                                                    </td>
                                                    <td><?= date('d M Y H:i', strtotime($tx['created_at'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No transactions found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Water Usage -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">Recent Water Usage (Last 5 logs)</div>
                        <div class="card-body">
                            <?php if (count($data['recentUsage']) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Customer</th>
                                                <th>Meter ID</th>
                                                <th>Water Used (m³)</th>
                                                <th>Log Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['recentUsage'] as $usage): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($usage['full_name']) ?></td>
                                                    <td><?= htmlspecialchars($usage['meter_id']) ?></td>
                                                    <td><?= number_format($usage['water_used'], 2) ?></td>
                                                    <td><?= date('d M Y H:i', strtotime($usage['log_time'])) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No water usage logs found.</p>
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
    // Revenue Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($data['revenueDates']) ?>,
            datasets: [{
                label: 'Revenue (TZS)',
                data: <?= json_encode($data['revenueAmounts']) ?>,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, title: { display: true, text: 'TZS' } } }
        }
    });

    // Usage Chart
    const usageCtx = document.getElementById('usageChart').getContext('2d');
    new Chart(usageCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($data['usageDates']) ?>,
            datasets: [{
                label: 'Water Used (m³)',
                data: <?= json_encode($data['usageAmounts']) ?>,
                backgroundColor: '#17a2b8',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, title: { display: true, text: 'Cubic Meters (m³)' } } }
        }
    });

    // Customer Status Doughnut Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active Customers', 'Inactive Customers'],
            datasets: [{
                data: [<?= $data['activeCustomers'] ?>, <?= $data['inactiveCustomers'] ?>],
                backgroundColor: ['#28a745', '#dc3545'],
                hoverOffset: 4
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>