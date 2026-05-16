<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Transaction.php';
require_once __DIR__ . '/../../models/WaterUsage.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-water-billing/login');
    exit;
}

$userId = $_SESSION['user_id'];
$transactionModel = new Transaction($pdo);
$waterUsageModel = new WaterUsage($pdo);

$transactions = $transactionModel->getByUser($userId);
$usageLogs = $waterUsageModel->getByUser($userId);

$pageTitle = 'My History';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">My History</h1>
            
            <!-- My Transactions -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-credit-card"></i> My Payment History
                </div>
                <div class="card-body">
                    <?php if (count($transactions) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Amount (TZS)</th>
                                        <th>Water Units</th>
                                        <th>Control Number</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $tx): ?>
                                        <tr>
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
            
            <!-- My Water Usage -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tint"></i> My Water Consumption
                </div>
                <div class="card-body">
                    <?php if (count($usageLogs) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Water Used (m³)</th>
                                        <th>Date & Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usageLogs as $log): ?>
                                        <tr>
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