<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Token.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$tokenModel = new Token($pdo);
$tokens = $tokenModel->getAllWithUsers();

$pageTitle = 'Customer Tokens';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">All Customer Tokens</h1>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-ticket-alt"></i> Tokens Generated
                </div>
                <div class="card-body">
                    <?php if (count($tokens) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Token Code</th>
                                        <th>Customer</th>
                                        <th>Meter ID</th>
                                        <th>Units</th>
                                        <th>Status</th>
                                        <th>Generated</th>
                                        <th>Expires</th>
                                        <th>Used At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tokens as $token): ?>
                                        <?php
                                        $isExpired = strtotime($token['expires_at']) < time();
                                        $status = '';
                                        if ($token['is_used']) {
                                            $status = '<span class="badge bg-secondary">Used</span>';
                                        } elseif ($isExpired) {
                                            $status = '<span class="badge bg-danger">Expired</span>';
                                        } else {
                                            $status = '<span class="badge bg-success">Valid</span>';
                                        }
                                        ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars($token['token_code']) ?></code></td>
                                            <td><?= htmlspecialchars($token['full_name']) ?><br><small><?= htmlspecialchars($token['email']) ?></small></td>
                                            <td><?= htmlspecialchars($token['meter_id'] ?? '—') ?></td>
                                            <td><?= $token['units_purchased'] ?></td>
                                            <td><?= $status ?></td>
                                            <td><?= date('d M Y H:i', strtotime($token['generated_at'])) ?></td>
                                            <td><?= $token['expires_at'] ? date('d M Y H:i', strtotime($token['expires_at'])) : 'Never' ?></td>
                                            <td><?= $token['used_at'] ? date('d M Y H:i', strtotime($token['used_at'])) : '—' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No tokens have been generated yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>