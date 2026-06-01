<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../controllers/PaymentController.php';
require_once __DIR__ . '/../../models/Rate.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-water-billing/login');
    exit;
}

$userId = $_SESSION['user_id'];
$paymentController = new PaymentController($pdo);
$rateModel = new Rate($pdo);
$currentRate = $rateModel->getCurrent();
$pricePerUnit = $currentRate ? $currentRate['price_per_unit'] : 1000;

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['amount'])) {
        $amount = floatval($_POST['amount']);
        $provider = $_POST['provider'];
        $phoneNumber = $_POST['phone_number'];
        
        if ($amount <= 0) {
            $error = 'Please enter a valid amount (minimum 100 TZS)';
        } elseif (empty($phoneNumber)) {
            $error = 'Please enter your mobile number';
        } else {
            $result = $paymentController->processPayment($userId, $amount, $provider, $phoneNumber);
            if (!$result['success']) {
                $error = $result['message'];
            }
        }
    } else {
        // Fallback to unit-based purchase
        $units = intval($_POST['units']);
        $provider = $_POST['provider'] ?? 'Simulated';
        $phoneNumber = $_POST['phone_number'] ?? '';
        
        if ($units <= 0) {
            $error = 'Please enter a valid number of units';
        } elseif ($provider !== 'Simulated' && empty($phoneNumber)) {
            $error = 'Please enter your mobile number for payment';
        } else {
            $amount = $units * $pricePerUnit;
            $result = $paymentController->processPayment($userId, $amount, $provider, $phoneNumber);
            if (!$result['success']) {
                $error = $result['message'];
            }
        }
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
                            <strong>✓ <?= htmlspecialchars($result['message']) ?></strong><br>
                            <?php if (isset($result['transactionId'])): ?>
                            Transaction ID: <code><?= htmlspecialchars($result['transactionId']) ?></code><br>
                            <?php endif; ?>
                            <small>Please check your phone and enter your PIN to complete the payment. The token will be generated automatically once payment is confirmed.</small>
                        </div>
                    <?php endif; ?>

                    <!-- Payment Methods -->
                    <ul class="nav nav-tabs mb-4" id="paymentTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="amount-tab" data-bs-toggle="tab" data-bs-target="#amountMethod" type="button" role="tab">
                                Pay by Amount
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="units-tab" data-bs-toggle="tab" data-bs-target="#unitsMethod" type="button" role="tab">
                                Pay by Units
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Tab 1: Pay by Amount -->
                        <div class="tab-pane fade show active" id="amountMethod" role="tabpanel">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Amount (TZS)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">TZS</span>
                                        <input type="number" class="form-control" name="amount" min="100" step="100" required>
                                    </div>
                                    <div class="form-text">Minimum amount: 100 TZS. You'll receive <?= number_format(floor(100 / $pricePerUnit), 2) ?> units per 100 TZS.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <select class="form-select" name="provider" required>
                                        <option value="">Select Provider</option>
                                        <option value="Mpesa">M-Pesa</option>
                                        <option value="Tigo">Tigo Pesa</option>
                                        <option value="Airtel">Airtel Money</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mobile Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+255</span>
                                        <input type="tel" class="form-control" name="phone_number" placeholder="7xxxxxxxxx" pattern="[0-9]{9}" required>
                                    </div>
                                    <div class="form-text">Enter your mobile number (without 0 or +255 prefix)</div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-mobile-alt"></i> Pay with Mobile Money
                                </button>
                            </form>
                        </div>

                        <!-- Tab 2: Pay by Units -->
                        <div class="tab-pane fade" id="unitsMethod" role="tabpanel">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Number of Units (m³)</label>
                                    <input type="number" class="form-control" name="units" min="1" step="1" required>
                                    <div class="form-text">Price: <?= number_format($pricePerUnit, 2) ?> TZS per m³</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <select class="form-select" name="provider" required>
                                        <option value="Simulated">Simulated (Demo)</option>
                                        <option value="Mpesa">M-Pesa</option>
                                        <option value="Tigo">Tigo Pesa</option>
                                        <option value="Airtel">Airtel Money</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mobile Number (required for real payment)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+255</span>
                                        <input type="tel" class="form-control" name="phone_number" placeholder="7xxxxxxxxx" pattern="[0-9]{9}">
                                    </div>
                                    <div class="form-text">Required for M-Pesa, Tigo Pesa, or Airtel Money</div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-ticket-alt"></i> Generate Token
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Calculate units when amount changes
    const amountInput = document.querySelector('input[name="amount"]');
    const pricePerUnit = <?= $pricePerUnit ?>;
    
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            let amount = parseFloat(this.value);
            if (isNaN(amount)) amount = 0;
            let units = Math.floor(amount / pricePerUnit);
            let helper = this.closest('form').querySelector('.form-text');
            if (helper && units > 0) {
                helper.innerHTML = `You will receive approximately ${units} units of water.`;
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>