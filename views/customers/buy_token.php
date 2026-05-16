<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../controllers/TokenController.php';
require_once __DIR__ . '/../../models/Rate.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-water-billing/login');
    exit;
}

$userId = $_SESSION['user_id'];
$tokenController = new TokenController($pdo);
$rateModel = new Rate($pdo);
$currentRate = $rateModel->getCurrent();
$pricePerUnit = $currentRate ? $currentRate['price_per_unit'] : 1000;

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['units'])) {
    $units = intval($_POST['units']);
    $paymentMethod = $_POST['payment_method'] ?? 'Simulated';
    $result = $tokenController->generateToken($userId, $units, $paymentMethod);
    if (!$result['success']) {
        $error = $result['message'];
    }
}

$pageTitle = 'Buy Water Token';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Buy Water Token</h1>
        <a href="/smart-water-billing/dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
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
                            <strong>✓ Token generated successfully!</strong><br>
                            <strong>Token:</strong> <code><?= htmlspecialchars($result['token']) ?></code><br>
                            <strong>Units:</strong> <?= $result['units'] ?> m³<br>
                            <strong>Amount paid:</strong> <?= number_format($result['amount'], 2) ?> TZS<br>
                            <small>This token will expire in 7 days. Enter it on your smart meter to add water units.</small>
                        </div>
                    <?php endif; ?>
                    
                    <form id="buyTokenForm" method="POST">
                        <div class="mb-3">
                            <label for="units" class="form-label">Number of water units (m³)</label>
                            <input type="number" class="form-control" id="units" name="units" min="1" step="1" required>
                            <div class="form-text">Current price: <strong><?= number_format($pricePerUnit, 2) ?> TZS per m³</strong></div>
                        </div>
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                <option value="Simulated">Simulated Payment (Demo)</option>
                                <option value="M-Pesa">M-Pesa</option>
                                <option value="Tigo Pesa">Tigo Pesa</option>
                                <option value="Airtel Money">Airtel Money</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="alert alert-info">
                                <strong>Total amount:</strong> <span id="totalAmount">0.00</span> TZS
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Proceed to Payment & Generate Token</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const pricePerUnit = <?= $pricePerUnit ?>;
    const unitsInput = document.getElementById('units');
    const totalSpan = document.getElementById('totalAmount');
    const form = document.getElementById('buyTokenForm');
    const submitBtn = document.getElementById('submitBtn');
    
    // Update total amount when units change
    function updateTotal() {
        let units = parseFloat(unitsInput.value);
        if (isNaN(units) || units < 0) units = 0;
        const total = units * pricePerUnit;
        totalSpan.textContent = total.toFixed(2);
    }
    
    unitsInput.addEventListener('input', updateTotal);
    updateTotal(); // initial
    
    // SweetAlert confirmation on form submit
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // stop normal submission
        
        const units = unitsInput.value;
        if (!units || units <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Input',
                text: 'Please enter a valid number of units (at least 1).'
            });
            return;
        }
        
        const total = (parseFloat(units) * pricePerUnit).toFixed(2);
        
        Swal.fire({
            title: 'Confirm Purchase',
            html: `You are about to purchase <strong>${units} m³</strong> of water.<br>Total amount: <strong>${total} TZS</strong><br><br>Do you want to proceed?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Processing...',
                    text: 'Generating your token. Please wait.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                // Submit the form programmatically
                form.submit();
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>