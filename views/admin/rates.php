<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Rate.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$rateModel = new Rate($pdo);
$message = '';
$error = '';

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $price = floatval($_POST['price']);
        $effectiveDate = $_POST['effective_date'];
        if ($rateModel->add($price, $effectiveDate, $_SESSION['user_id'])) {
            $message = 'Rate added successfully';
        } else {
            $error = 'Failed to add rate';
        }
    } elseif ($_POST['action'] === 'update' && isset($_POST['rate_id'])) {
        $rateId = intval($_POST['rate_id']);
        $price = floatval($_POST['price']);
        $effectiveDate = $_POST['effective_date'];
        if ($rateModel->update($rateId, $price, $effectiveDate)) {
            $message = 'Rate updated successfully';
        } else {
            $error = 'Failed to update rate';
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['rate_id'])) {
        $rateId = intval($_POST['rate_id']);
        if ($rateModel->delete($rateId)) {
            $message = 'Rate deleted successfully';
        } else {
            $error = 'Failed to delete rate';
        }
    }
}

$rates = $rateModel->getAll();
$currentRate = $rateModel->getCurrent();

$pageTitle = 'Manage Water Rates';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4">Water Rates (Price per Unit)</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-5">
                    <div class="card mb-4">
                        <div class="card-header">Add New Rate</div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="add">
                                <div class="mb-3">
                                    <label>Price per Unit (TZS)</label>
                                    <input type="number" step="0.01" name="price" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Effective Date</label>
                                    <input type="date" name="effective_date" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Add Rate</button>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">Current Active Rate</div>
                        <div class="card-body">
                            <?php if ($currentRate): ?>
                                <h3><?= number_format($currentRate['price_per_unit'], 2) ?> TZS / m³</h3>
                                <small>Effective from <?= date('d M Y', strtotime($currentRate['effective_date'])) ?></small>
                            <?php else: ?>
                                <p class="text-muted">No rate set</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">All Rates</div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr><th>ID</th><th>Price (TZS)</th><th>Effective Date</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rates as $rate): ?>
                                    <tr>
                                        <td><?= $rate['rate_id'] ?></td>
                                        <td><?= number_format($rate['price_per_unit'], 2) ?></td>
                                        <td><?= date('d M Y', strtotime($rate['effective_date'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $rate['rate_id'] ?>">Edit</button>
                                            <form method="POST" style="display:inline-block" onsubmit="return confirm('Delete this rate?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="rate_id" value="<?= $rate['rate_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?= $rate['rate_id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="rate_id" value="<?= $rate['rate_id'] ?>">
                                                    <div class="modal-header"><h5>Edit Rate</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                    <div class="modal-body">
                                                        <div class="mb-3"><label>Price</label><input type="number" step="0.01" name="price" class="form-control" value="<?= $rate['price_per_unit'] ?>" required></div>
                                                        <div class="mb-3"><label>Effective Date</label><input type="date" name="effective_date" class="form-control" value="<?= $rate['effective_date'] ?>" required></div>
                                                    </div>
                                                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>