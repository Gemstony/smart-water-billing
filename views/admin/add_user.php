<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/User.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$userModel = new User($pdo);
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => $_POST['full_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'meter_id' => $_POST['meter_id'] ?: null,
        'account_balance' => floatval($_POST['account_balance']),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT)
    ];
    if ($userModel->createUser($data)) {
        $message = 'Customer added successfully. Default password: ' . $_POST['password'];
    } else {
        $error = 'Failed to add customer. Email or meter_id might already exist.';
    }
}

$pageTitle = 'Add Customer';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Add New Customer</h1>
                <a href="/smart-water-billing/admin/users" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Customers
                </a>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header">Add New Customer</div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3"><label>Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                        <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" required></div>
                        <div class="mb-3"><label>Meter ID (optional)</label><input type="text" name="meter_id" class="form-control"></div>
                        <div class="mb-3"><label>Initial Balance (units)</label><input type="number" step="0.01" name="account_balance" class="form-control" value="0"></div>
                        <div class="mb-3"><label>Password</label><input type="text" name="password" class="form-control" value="password123" required></div>
                        <div class="mb-3 form-check"><input type="checkbox" name="is_active" class="form-check-input" checked> <label class="form-check-label">Active</label></div>
                        <button type="submit" class="btn btn-primary">Add Customer</button>
                        <a href="/smart-water-billing/admin/users" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>