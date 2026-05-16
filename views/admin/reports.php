<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Transaction.php';
require_once __DIR__ . '/../../models/WaterUsage.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$transactionModel = new Transaction($pdo);
$waterUsageModel = new WaterUsage($pdo);

$transactions = $transactionModel->getAll();
$usageLogs = $waterUsageModel->getAll();

$pageTitle = 'Reports';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Reports</h1>
            
            <!-- Transactions Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-credit-card"></i> All Transactions (Payments)
                </div>
                <div class="card-body">
                    <?php if (count($transactions) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Meter ID</th>
                                        <th>Amount (TZS)</th>
                                        <th>Water Units</th>
                                        <th>Control Number</th>
                                        <th>Payment Method</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $tx): ?>
                                        <tr>
                                            <td><?= $tx['transaction_id'] ?></td>
                                            <td><?= htmlspecialchars($tx['full_name']) ?> (<small><?= htmlspecialchars($tx['email']) ?></small>)</td>
                                            <td><?= htmlspecialchars($tx['meter_id'] ?? '—') ?></td>
                                            <td><?= number_format($tx['amount'], 2) ?></td>
                                            <td><?= $tx['water_units'] ?></td>
                                            <td><?= htmlspecialchars($tx['control_number']) ?></td>
                                            <td><?= $tx['payment_method'] ?></td>
                                            <td>
                                                <span class="badge bg-<?= $tx['status'] === 'completed' ? 'success' : ($tx['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                    <?= ucfirst($tx['status']) ?>
                                                </span>
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
            
            <!-- Water Usage Table -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tint"></i> All Water Usage Logs
                </div>
                <div class="card-body">
                    <?php if (count($usageLogs) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Meter ID</th>
                                        <th>Water Used (m³)</th>
                                        <th>Log Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usageLogs as $log): ?>
                                        <tr>
                                            <td><?= $log['usage_id'] ?></td>
                                            <td><?= htmlspecialchars($log['full_name']) ?> (<small><?= htmlspecialchars($log['email']) ?></small>)</td>
                                            <td><?= htmlspecialchars($log['meter_id']) ?></td>
                                            <td><?= number_format($log['water_used'], 2) ?></td>
                                            <td><?= date('d M Y H:i', strtotime($log['log_time'])) ?></td>
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

<?php require_once __DIR__ . '/../layout/footer.php'; ?>