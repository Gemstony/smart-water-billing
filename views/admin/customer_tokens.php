<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Token.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$tokenModel = new Token($pdo);

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$totalTokens = $tokenModel->countAllWithUsers();
$totalPages = max(1, ceil($totalTokens / $limit));
$page = min($page, $totalPages);
$tokens = $tokenModel->getAllWithUsersPaginated($page, $limit);

$pageTitle = 'Customer Tokens';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">All Customer Tokens</h1>
                <a href="/smart-water-billing/admin/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
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
                                            <td><?= htmlspecialchars($token['full_name']) ?><br><small><?= htmlspecialchars($token['email']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($token['meter_id'] ?? '—') ?></td>
                                            <td><?= $token['units_purchased'] ?></td>
                                            <td><?= $status ?></td>
                                            <td><?= date('d M Y H:i', strtotime($token['generated_at'])) ?></td>
                                            <td><?= $token['expires_at'] ? date('d M Y H:i', strtotime($token['expires_at'])) : 'Never' ?>
                                            </td>
                                            <td><?= $token['used_at'] ? date('d M Y H:i', strtotime($token['used_at'])) : '—' ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                <?php endforeach; ?>
                            </table>
                        </div>
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Tokens pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">No tokens have been generated yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>