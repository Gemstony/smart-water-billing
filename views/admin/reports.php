<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Transaction.php';
require_once __DIR__ . '/../../models/WaterUsage.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$transactionModel = new Transaction($pdo);
$waterUsageModel = new WaterUsage($pdo);

$transactions = $transactionModel->getAll();
$usageLogs = $waterUsageModel->getAll();

// Prepare data for charts
$transactionDates = [];
$transactionAmounts = [];
$usageDates = [];
$usageAmounts = [];

// Process transactions for chart (daily totals)
$dailyTransactionTotals = [];
foreach ($transactions as $tx) {
    $date = date('Y-m-d', strtotime($tx['created_at']));
    if (!isset($dailyTransactionTotals[$date])) {
        $dailyTransactionTotals[$date] = 0;
    }
    $dailyTransactionTotals[$date] += $tx['amount'];
}

// Sort by date
ksort($dailyTransactionTotals);
foreach ($dailyTransactionTotals as $date => $amount) {
    $transactionDates[] = date('M d', strtotime($date));
    $transactionAmounts[] = $amount;
}

// Process usage logs for chart (daily totals)
$dailyUsageTotals = [];
foreach ($usageLogs as $log) {
    $date = date('Y-m-d', strtotime($log['log_time']));
    if (!isset($dailyUsageTotals[$date])) {
        $dailyUsageTotals[$date] = 0;
    }
    $dailyUsageTotals[$date] += $log['water_used'];
}

// Sort by date
ksort($dailyUsageTotals);
foreach ($dailyUsageTotals as $date => $amount) {
    $usageDates[] = date('M d', strtotime($date));
    $usageAmounts[] = $amount;
}

$pageTitle = 'Reports';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">Reports</h1>
                <div>
                    <a href="/smart-water-billing/admin/dashboard" class="btn btn-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button id="printReport" class="btn btn-outline-primary">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-line"></i> Daily Transaction Trends
                        </div>
                        <div class="card-body">
                            <canvas id="transactionChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-tint"></i> Daily Water Usage Trends
                        </div>
                        <div class="card-body">
                            <canvas id="usageChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Transactions Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-credit-card"></i> All Transactions (Payments)
                </div>
                <div class="card-body">
                     <?php if (count($transactions) > 0): ?>
                         <div class="table-responsive">
                             <table class="table table-bordered table-striped" id="transactionsTable">
                                 <thead>
                                     <tr>
                                         <th>ID</th>
                                         <th>Customer</th>
                                         <th>Meter ID</th>
                                         <th>Amount (TZS)</th>
                                         <th>Water Units</th>
                                         <th>Control Number</th>
                                         <th>Payment Method</th>
                                         <th>Status</th>
                                         <th>Date</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <?php foreach ($transactions as $tx): ?>
                                         <tr>
                                             <td><?= $tx['transaction_id'] ?></td>
                                             <td><?= htmlspecialchars($tx['full_name']) ?> (<small><?= htmlspecialchars($tx['email']) ?></small>)</td>
                                             <td><?= htmlspecialchars($tx['meter_id'] ?? '—') ?></td>
                                             <td><?= number_format($tx['amount'], 2) ?></td>
                                             <td><?= $tx['water_units'] ?></td>
                                             <td><?= htmlspecialchars($tx['control_number']) ?></td>
                                             <td><?= $tx['payment_method'] ?></td>
                                             <td>
                                                 <span class="badge bg-<?= $tx['status'] === 'completed' ? 'success' : ($tx['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                     <?= ucfirst($tx['status']) ?>
                                                 </span>
                                             </td>
                                             <td><?= date('d M Y H:i', strtotime($tx['created_at'])) ?></td>
                                         </tr>
                                     <?php endforeach; ?>
                                 </tbody>
                             </table>
                         </div>
                         <?php if (count($transactions) > 5): ?>
                         <div class="d-flex justify-content-between mt-3">
                             <nav aria-label="Page navigation">
                                 <ul class="pagination mb-0">
                                     <li class="page-item disabled" id="transactionsPrev">
                                         <a class="page-link" href="#" aria-label="Previous">
                                             <span aria-hidden="true">&laquo;</span>
                                         </a>
                                     </li>
                                     <li class="page-item active" id="transactionsPage1">
                                         <a class="page-link" href="#">1</a>
                                     </li>
                                     <?php
                                     $totalPages = ceil(count($transactions) / 5);
                                     for ($i = 2; $i <= min($totalPages, 5); $i++):
                                     ?>
                                     <li class="page-item" id="transactionsPage<?= $i ?>">
                                         <a class="page-link" href="#"><?= $i ?></a>
                                     </li>
                                     <?php endfor; ?>
                                     <?php if ($totalPages > 5): ?>
                                     <li class="page-item disabled">
                                         <span class="page-link">...</span>
                                     </li>
                                     <li class="page-item" id="transactionsPageLast">
                                         <a class="page-link" href="#"><?= $totalPages ?></a>
                                     </li>
                                     <?php endif; ?>
                                     <li class="page-item" id="transactionsNext">
                                         <a class="page-link" href="#" aria-label="Next">
                                             <span aria-hidden="true">&raquo;</span>
                                         </a>
                                     </li>
                                 </ul>
                             </nav>
                             <div>
                                 <select id="transactionsPageSize" class="form-select form-select-sm" style="width: auto;">
                                     <option value="5">5 per page</option>
                                     <option value="10">10 per page</option>
                                     <option value="25">25 per page</option>
                                     <option value="50">50 per page</option>
                                     <option value="100">100 per page</option>
                                 </select>
                             </div>
                         </div>
                         <?php endif; ?>
                     <?php else: ?>
                         <p class="text-muted">No transactions found.</p>
                     <?php endif; ?>
                </div>
            </div>
            
            <!-- Water Usage Table -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tint"></i> All Water Usage Logs
                </div>
                <div class="card-body">
                    <?php if (count($usageLogs) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="usageTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer</th>
                                        <th>Meter ID</th>
                                        <th>Water Used (m³)</th>
                                        <th>Log Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usageLogs as $log): ?>
                                        <tr>
                                            <td><?= $log['usage_id'] ?></td>
                                            <td><?= htmlspecialchars($log['full_name']) ?> (<small><?= htmlspecialchars($log['email']) ?></small>)</td>
                                            <td><?= htmlspecialchars($log['meter_id']) ?></td>
                                            <td><?= number_format($log['water_used'], 2) ?></td>
                                            <td><?= date('d M Y H:i', strtotime($log['log_time'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($usageLogs) > 5): ?>
                        <div class="d-flex justify-content-between mt-3">
                            <nav aria-label="Page navigation">
                                <ul class="pagination mb-0">
                                    <li class="page-item disabled" id="usagePrev">
                                        <a class="page-link" href="#" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    <li class="page-item active" id="usagePage1">
                                        <a class="page-link" href="#">1</a>
                                    </li>
                                    <?php
                                    $totalPages = ceil(count($usageLogs) / 5);
                                    for ($i = 2; $i <= min($totalPages, 5); $i++):
                                    ?>
                                    <li class="page-item" id="usagePage<?= $i ?>">
                                        <a class="page-link" href="#"><?= $i ?></a>
                                    </li>
                                    <?php endfor; ?>
                                    <?php if ($totalPages > 5): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                    <li class="page-item" id="usagePageLast">
                                        <a class="page-link" href="#"><?= $totalPages ?></a>
                                    </li>
                                    <?php endif; ?>
                                    <li class="page-item" id="usageNext">
                                        <a class="page-link" href="#" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            <div>
                                <select id="usagePageSize" class="form-select form-select-sm" style="width: auto;">
                                    <option value="5">5 per page</option>
                                    <option value="10">10 per page</option>
                                    <option value="25">25 per page</option>
                                    <option value="50">50 per page</option>
                                    <option value="100">100 per page</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted">No water usage logs found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pagination for Transactions Table
    const transactionsTable = document.getElementById('transactionsTable');
    if (transactionsTable) {
        const transactionsRows = transactionsTable.querySelectorAll('tbody tr');
        const transactionsPrev = document.getElementById('transactionsPrev');
        const transactionsNext = document.getElementById('transactionsNext');
        const transactionsPageSizeSelect = document.getElementById('transactionsPageSize');
        
        let transactionsCurrentPage = 1;
        let transactionsRowsPerPage = 5;
        const updateTransactionsTotalPages = () => Math.ceil(transactionsRows.length / transactionsRowsPerPage);
        
        function showTransactionsPage(page) {
            transactionsRows.forEach((row, index) => {
                const start = (page - 1) * transactionsRowsPerPage;
                const end = start + transactionsRowsPerPage;
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
            
            // Update active page indicator
            document.querySelectorAll('[id^="transactionsPage"]').forEach(el => el.classList.remove('active'));
            
            if (page <= 5) {
                document.getElementById(`transactionsPage${page}`)?.classList.add('active');
            } else if (page === updateTransactionsTotalPages()) {
                document.getElementById('transactionsPageLast')?.classList.add('active');
            }
            
            // Update prev/next buttons
            transactionsPrev.classList.toggle('disabled', page === 1);
            transactionsNext.classList.toggle('disabled', page === updateTransactionsTotalPages());
        }
        
        // Initial display
        showTransactionsPage(transactionsCurrentPage);
        
        // Event listeners for pagination
        transactionsPrev.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.classList.contains('disabled') && transactionsCurrentPage > 1) {
                transactionsCurrentPage--;
                showTransactionsPage(transactionsCurrentPage);
            }
        });
        
        transactionsNext.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.classList.contains('disabled') && transactionsCurrentPage < updateTransactionsTotalPages()) {
                transactionsCurrentPage++;
                showTransactionsPage(transactionsCurrentPage);
            }
        });
        
        // Page size change
        transactionsPageSizeSelect.addEventListener('change', function() {
            transactionsRowsPerPage = parseInt(this.value);
            transactionsCurrentPage = 1;
            showTransactionsPage(transactionsCurrentPage);
        });
        
        // Page number clicks
        document.querySelectorAll('[id^="transactionsPage"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (this.classList.contains('disabled') || this.classList.contains('active')) return;
                
                const pageNum = this.textContent === '...' ? 
                    (this.id.includes('Last') ? updateTransactionsTotalPages() : 1) : 
                    parseInt(this.textContent);
                
                if (!isNaN(pageNum)) {
                    transactionsCurrentPage = pageNum;
                    showTransactionsPage(transactionsCurrentPage);
                }
            });
        });
    }
    
    // Pagination for Usage Table
    const usageTable = document.getElementById('usageTable');
    if (usageTable) {
        const usageRows = usageTable.querySelectorAll('tbody tr');
        const usagePrev = document.getElementById('usagePrev');
        const usageNext = document.getElementById('usageNext');
        const usagePageSizeSelect = document.getElementById('usagePageSize');
        
        let usageCurrentPage = 1;
        let usageRowsPerPage = 5;
        const updateUsageTotalPages = () => Math.ceil(usageRows.length / usageRowsPerPage);
        
        function showUsagePage(page) {
            usageRows.forEach((row, index) => {
                const start = (page - 1) * usageRowsPerPage;
                const end = start + usageRowsPerPage;
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
            
            // Update active page indicator
            document.querySelectorAll('[id^="usagePage"]').forEach(el => el.classList.remove('active'));
            
            if (page <= 5) {
                document.getElementById(`usagePage${page}`)?.classList.add('active');
            } else if (page === updateUsageTotalPages()) {
                document.getElementById('usagePageLast')?.classList.add('active');
            }
            
            // Update prev/next buttons
            usagePrev.classList.toggle('disabled', page === 1);
            usageNext.classList.toggle('disabled', page === updateUsageTotalPages());
        }
        
        // Initial display
        showUsagePage(usageCurrentPage);
        
        // Event listeners for pagination
        usagePrev.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.classList.contains('disabled') && usageCurrentPage > 1) {
                usageCurrentPage--;
                showUsagePage(usageCurrentPage);
            }
        });
        
        usageNext.addEventListener('click', function(e) {
            e.preventDefault();
            if (!this.classList.contains('disabled') && usageCurrentPage < updateUsageTotalPages()) {
                usageCurrentPage++;
                showUsagePage(usageCurrentPage);
            }
        });
        
        // Page size change
        usagePageSizeSelect.addEventListener('change', function() {
            usageRowsPerPage = parseInt(this.value);
            usageCurrentPage = 1;
            showUsagePage(usageCurrentPage);
        });
        
        // Page number clicks
        document.querySelectorAll('[id^="usagePage"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (this.classList.contains('disabled') || this.classList.contains('active')) return;
                
                const pageNum = this.textContent === '...' ? 
                    (this.id.includes('Last') ? updateUsageTotalPages() : 1) : 
                    parseInt(this.textContent);
                
                if (!isNaN(pageNum)) {
                    usageCurrentPage = pageNum;
                    showUsagePage(usageCurrentPage);
                }
            });
        });
    }
    
    // Charts
    const transactionCtx = document.getElementById('transactionChart');
    if (transactionCtx) {
        new Chart(transactionCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($transactionDates); ?>,
                datasets: [{
                    label: 'Daily Transaction Amount (TZS)',
                    data: <?php echo json_encode($transactionAmounts); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    const usageCtx = document.getElementById('usageChart');
    if (usageCtx) {
        new Chart(usageCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($usageDates); ?>,
                datasets: [{
                    label: 'Daily Water Usage (m³)',
                    data: <?php echo json_encode($usageAmounts); ?>,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Print button
    document.getElementById('printReport').addEventListener('click', function() {
        window.print();
    });
});
</script>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>