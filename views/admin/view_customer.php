<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Transaction.php';
require_once __DIR__ . '/../../models/Token.php';
require_once __DIR__ . '/../../models/WaterUsage.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$userModel = new User($pdo);
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$customer = $userModel->findById($userId);
if (!$customer || $customer['role'] !== 'customer') {
    header('Location: /smart-water-billing/admin/users');
    exit;
}

$transactionModel = new Transaction($pdo);
$tokenModel = new Token($pdo);
$waterUsageModel = new WaterUsage($pdo);

$transactions = $transactionModel->getByUser($userId);
$tokens = $tokenModel->getByUser($userId);
$usages = $waterUsageModel->getByUser($userId);

$pageTitle = 'Customer Details: ' . htmlspecialchars($customer['full_name']);
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Customer: <?= htmlspecialchars($customer['full_name']) ?></h1>
                <a href="/smart-water-billing/admin/users" class="btn btn-secondary">Back to Users</a>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Customer Information</div>
                        <div class="card-body">
                            <p><strong>Email:</strong> <?= htmlspecialchars($customer['email']) ?></p>
                            <p><strong>Phone:</strong> <?= htmlspecialchars($customer['phone']) ?></p>
                            <p><strong>Meter ID:</strong> <?= htmlspecialchars($customer['meter_id'] ?? 'Not assigned') ?></p>
                            <p><strong>Current Balance:</strong> <?= number_format($customer['account_balance'], 2) ?> units</p>
                            <p><strong>Status:</strong> <span class="badge bg-<?= $customer['is_active'] ? 'success' : 'danger' ?>"><?= $customer['is_active'] ? 'Active' : 'Inactive' ?></span></p>
                            <p><strong>Registered:</strong> <?= date('d M Y H:i', strtotime($customer['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">Transactions (Payments)</div>
                <div class="card-body">
                    <?php if (count($transactions) > 0): ?>
                        <table class="table table-bordered">
                            <thead><tr><th>Amount (TZS)</th><th>Units</th><th>Control Number</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php foreach ($transactions as $tx): ?>
                                <tr>
                                    <td><?= number_format($tx['amount'], 2) ?></td>
                                    <td><?= $tx['water_units'] ?></td>
                                    <td><?= htmlspecialchars($tx['control_number']) ?></td>
                                    <td><?= $tx['payment_method'] ?></td>
                                    <td><span class="badge bg-<?= $tx['status'] === 'completed' ? 'success' : 'warning' ?>"><?= $tx['status'] ?></span></td>
                                    <td><?= date('d M Y H:i', strtotime($tx['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No transactions</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">Tokens Generated</div>
                <div class="card-body">
                    <?php if (count($tokens) > 0): ?>
                        <table class="table table-bordered">
                            <thead><tr><th>Token Code</th><th>Units</th><th>Status</th><th>Generated</th><th>Expires</th><th>Used At</th></tr></thead>
                            <tbody>
                                <?php foreach ($tokens as $token): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($token['token_code']) ?></code></td>
                                    <td><?= $token['units_purchased'] ?></td>
                                    <td><?= $token['is_used'] ? 'Used' : (strtotime($token['expires_at']) < time() ? 'Expired' : 'Valid') ?></td>
                                    <td><?= date('d M Y H:i', strtotime($token['generated_at'])) ?></td>
                                    <td><?= $token['expires_at'] ? date('d M Y H:i', strtotime($token['expires_at'])) : 'Never' ?></td>
                                    <td><?= $token['used_at'] ? date('d M Y H:i', strtotime($token['used_at'])) : '—' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No tokens generated</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">Water Usage Logs</div>
                <div class="card-body">
                    <?php if (count($usages) > 0): ?>
                        <table class="table table-bordered">
                            <thead><tr><th>Water Used (m³)</th><th>Log Time</th></tr></thead>
                            <tbody>
                                <?php foreach ($usages as $usage): ?>
                                <tr>
                                    <td><?= number_format($usage['water_used'], 2) ?></td>
                                    <td><?= date('d M Y H:i', strtotime($usage['log_time'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No usage logs</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>