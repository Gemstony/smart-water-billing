<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../controllers/TokenController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-water-billing/login');
    exit;
}

$userId = $_SESSION['user_id'];
$tokenController = new TokenController($pdo);
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['units'])) {
    $units = intval($_POST['units']);
    $result = $tokenController->generateToken($userId, $units);
    if (!$result['success']) {
        $error = $result['message'];
    }
}

$pageTitle = 'Buy Water Token';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-ticket-alt"></i> Buy Water Token
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($result && $result['success']): ?>
                        <div class="alert alert-success">
                            <strong>Token generated successfully!</strong><br>
                            Your token: <code><?= htmlspecialchars($result['token']) ?></code><br>
                            Units: <?= htmlspecialchars($result['units']) ?><br>
                            <small>This token will expire in 7 days. Enter it on your smart meter to add water units.</small>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="units" class="form-label">Number of water units (m³)</label>
                            <input type="number" class="form-control" id="units" name="units" min="1" step="1" required>
                            <div class="form-text">Price per unit: <?= number_format(1000, 2) ?> TZS (simulated)</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Simulate Payment & Generate Token</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>