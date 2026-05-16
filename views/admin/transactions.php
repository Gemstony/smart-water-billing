<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Transaction.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$transactionModel = new Transaction($pdo);
$transactions = $transactionModel->getAll();

$pageTitle = 'All Transactions';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">All Transactions</h1>
                <a href="/smart-water-billing/admin/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-credit-card"></i> Customer Payments
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
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $tx): ?>
                                        <tr>
                                            <td><?= $tx['transaction_id'] ?></td>
                                            <td><?= htmlspecialchars($tx['full_name']) ?><br><small><?= htmlspecialchars($tx['email']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($tx['meter_id'] ?? '—') ?></td>
                                            <td><?= number_format($tx['amount'], 2) ?></td>
                                            <td><?= $tx['water_units'] ?></td>
                                            <td><?= htmlspecialchars($tx['control_number']) ?></td>
                                            <td><?= $tx['payment_method'] ?></td>
                                            <td>
                                                <span
                                                    class="badge bg-<?= $tx['status'] === 'completed' ? 'success' : ($tx['status'] === 'pending' ? 'warning' : 'danger') ?>">
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
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>