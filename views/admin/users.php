<?php
// Ensure admin is logged in (handled by index.php routing, but double-check)
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/User.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$userModel = new User($pdo);
$customers = $userModel->getAllCustomers();

$pageTitle = 'Manage Customers';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">All Customers</h1>
                <a href="/smart-water-billing/admin/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-users"></i> Registered Customers
                </div>
                <div class="card-body">
                    <?php if (count($customers) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Meter ID</th>
                                        <th>Balance (units)</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td><?= $customer['user_id'] ?></td>
                                            <td><?= htmlspecialchars($customer['full_name']) ?></td>
                                            <td><?= htmlspecialchars($customer['email']) ?></td>
                                            <td><?= htmlspecialchars($customer['phone']) ?></td>
                                            <td><?= htmlspecialchars($customer['meter_id'] ?? '—') ?></td>
                                            <td><?= number_format($customer['account_balance'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $customer['is_active'] ? 'success' : 'danger' ?>">
                                                    <?= $customer['is_active'] ? 'Active' : 'Inactive' ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($customer['created_at'])) ?></td>
                                            <td>
                                                <a href="/smart-water-billing/admin/edit_user?id=<?= $customer['user_id'] ?>"
                                                    class="btn btn-sm btn-primary">Edit</a>
                                                <a href="/smart-water-billing/admin/view_customer?id=<?= $customer['user_id'] ?>"
                                                    class="btn btn-sm btn-info">View History</a>
                                                <!-- Delete button can be added later -->
                                            </td>
                                            <!-- Later: edit, toggle status, etc. -->
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No customers registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>