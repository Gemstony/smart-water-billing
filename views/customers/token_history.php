<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Token.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-water-billing/login');
    exit;
}

$userId = $_SESSION['user_id'];
$tokenModel = new Token($pdo);
$tokens = $tokenModel->getByUser($userId);

$pageTitle = 'Token History';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">My Token History</h1>
            
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-ticket-alt"></i> All Tokens
                </div>
                <div class="card-body">
                    <?php if (count($tokens) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Token Code</th>
                                        <th>Units (m³)</th>
                                        <th>Status</th>
                                        <th>Generated At</th>
                                        <th>Expires At</th>
                                        <th>Used At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tokens as $token): ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars($token['token_code']) ?></code></td>
                                            <td><?= $token['units_purchased'] ?></td>
                                            <td>
                                                <?php if ($token['is_used']): ?>
                                                    <span class="badge bg-secondary">Used</span>
                                                <?php elseif (strtotime($token['expires_at']) < time()): ?>
                                                    <span class="badge bg-danger">Expired</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Valid</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d M Y H:i', strtotime($token['generated_at'])) ?></td>
                                            <td><?= $token['expires_at'] ? date('d M Y H:i', strtotime($token['expires_at'])) : 'Never' ?></td>
                                            <td><?= $token['used_at'] ? date('d M Y H:i', strtotime($token['used_at'])) : '—' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No tokens generated yet. <a href="/smart-water-billing/buy-token">Buy your first token</a>.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>