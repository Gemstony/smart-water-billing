<?php
// Ensure session is started and user is logged in
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/User.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-water-billing/login');
    exit;
}

$userModel = new User($pdo);
$user = $userModel->findById($_SESSION['user_id']);

// Optionally fetch last water usage (from water_usage table)
$lastUsage = null;
$stmt = $pdo->prepare("SELECT water_used, log_time FROM water_usage WHERE user_id = ? ORDER BY log_time DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$lastUsage = $stmt->fetch();

$pageTitle = 'Customer Dashboard';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Customer') ?></h1>
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
            <!-- Add more widgets as needed -->
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>