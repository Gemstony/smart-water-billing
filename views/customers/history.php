<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Transaction.php';
require_once __DIR__ . '/../../models/WaterUsage.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-water-billing/login');
    exit;
}

$userId = $_SESSION['user_id'];
$transactionModel = new Transaction($pdo);
$waterUsageModel = new WaterUsage($pdo);

$transactions = $transactionModel->getByUser($userId);
$usageLogs = $waterUsageModel->getByUser($userId);

$pageTitle = 'My History';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">My History</h1>
                <a href="/smart-water-billing/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <!-- My Transactions -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-credit-card"></i> My Payment History
                </div>
                <div class="card-body">
                     <?php if (count($transactions) > 0): ?>
                         <div class="table-responsive">
                             <table class="table table-bordered table-striped" id="transactionsTable">
                                 <thead>
                                     <tr>
                                         <th>Amount (TZS)</th>
                                         <th>Water Units</th>
                                         <th>Control Number</th>
                                         <th>Method</th>
                                         <th>Status</th>
                                         <th>Date</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <?php foreach ($transactions as $tx): ?>
                                         <tr>
                                             <td><?= number_format($tx['amount'], 2) ?></td>
                                             <td><?= $tx['water_units'] ?></td>
                                             <td><?= htmlspecialchars($tx['control_number']) ?></td>
                                             <td><?= $tx['payment_method'] ?></td>
                                             <td>
                                                 <span
                                                     class="badge bg-<?= $tx['status'] === 'completed' ? 'success' : ($tx['status'] === 'pending' ? 'warning' : 'danger') ?>">
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
                         <nav aria-label="Page navigation">
                             <ul class="pagination justify-content-center mt-3">
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
                         <?php endif; ?>
                     <?php else: ?>
                         <p class="text-muted">No transactions found.</p>
                     <?php endif; ?>
                </div>
            </div>

            <!-- My Water Usage -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-tint"></i> My Water Consumption
                </div>
                <div class="card-body">
                     <?php if (count($usageLogs) > 0): ?>
                         <div class="table-responsive">
                             <table class="table table-bordered table-striped" id="usageTable">
                                 <thead>
                                     <tr>
                                         <th>Water Used (m³)</th>
                                         <th>Date & Time</th>
                                     </tr>
                                 </thead>
                                 <tbody>
                                     <?php foreach ($usageLogs as $log): ?>
                                         <tr>
                                             <td><?= number_format($log['water_used'], 2) ?></td>
                                             <td><?= date('d M Y H:i', strtotime($log['log_time'])) ?></td>
                                         </tr>
                                     <?php endforeach; ?>
                                 </tbody>
                             </table>
                         </div>
                         <?php if (count($usageLogs) > 5): ?>
                         <nav aria-label="Page navigation">
                             <ul class="pagination justify-content-center mt-3">
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
                         <?php endif; ?>
                     <?php else: ?>
                         <p class="text-muted">No water usage logs found.</p>
                     <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pagination for Transactions Table
    const transactionsTable = document.getElementById('transactionsTable');
    if (transactionsTable) {
        const transactionsRows = transactionsTable.querySelectorAll('tbody tr');
        const transactionsPrev = document.getElementById('transactionsPrev');
        const transactionsNext = document.getElementById('transactionsNext');
        
        let transactionsCurrentPage = 1;
        const transactionsRowsPerPage = 5;
        const transactionsTotalPages = Math.ceil(transactionsRows.length / transactionsRowsPerPage);
        
        function showTransactionsPage(page) {
            transactionsRows.forEach((row, index) => {
                const start = (page - 1) * transactionsRowsPerPage;
                const end = start + transactionsRowsPerPage;
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
            
            // Update active page indicator
            document.querySelectorAll('#transactionsPage1, #transactionsPage2, #transactionsPage3, #transactionsPage4, #transactionsPage5, #transactionsPageLast')
                .forEach(el => el.classList.remove('active'));
            
            if (page <= 5) {
                document.getElementById(`transactionsPage${page}`)?.classList.add('active');
            } else if (page === transactionsTotalPages) {
                document.getElementById('transactionsPageLast')?.classList.add('active');
            }
            
            // Update prev/next buttons
            transactionsPrev.classList.toggle('disabled', page === 1);
            transactionsNext.classList.toggle('disabled', page === transactionsTotalPages);
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
            if (!this.classList.contains('disabled') && transactionsCurrentPage < transactionsTotalPages) {
                transactionsCurrentPage++;
                showTransactionsPage(transactionsCurrentPage);
            }
        });
        
        // Page number clicks
        document.querySelectorAll('[id^="transactionsPage"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (this.classList.contains('disabled') || this.classList.contains('active')) return;
                
                const pageNum = this.textContent === '...' ? 
                    (this.id.includes('Last') ? transactionsTotalPages : 1) : 
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
        
        let usageCurrentPage = 1;
        const usageRowsPerPage = 5;
        const usageTotalPages = Math.ceil(usageRows.length / usageRowsPerPage);
        
        function showUsagePage(page) {
            usageRows.forEach((row, index) => {
                const start = (page - 1) * usageRowsPerPage;
                const end = start + usageRowsPerPage;
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
            
            // Update active page indicator
            document.querySelectorAll('#usagePage1, #usagePage2, #usagePage3, #usagePage4, #usagePage5, #usagePageLast')
                .forEach(el => el.classList.remove('active'));
            
            if (page <= 5) {
                document.getElementById(`usagePage${page}`)?.classList.add('active');
            } else if (page === usageTotalPages) {
                document.getElementById('usagePageLast')?.classList.add('active');
            }
            
            // Update prev/next buttons
            usagePrev.classList.toggle('disabled', page === 1);
            usageNext.classList.toggle('disabled', page === usageTotalPages);
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
            if (!this.classList.contains('disabled') && usageCurrentPage < usageTotalPages) {
                usageCurrentPage++;
                showUsagePage(usageCurrentPage);
            }
        });
        
        // Page number clicks
        document.querySelectorAll('[id^="usagePage"]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (this.classList.contains('disabled') || this.classList.contains('active')) return;
                
                const pageNum = this.textContent === '...' ? 
                    (this.id.includes('Last') ? usageTotalPages : 1) : 
                    parseInt(this.textContent);
                
                if (!isNaN(pageNum)) {
                    usageCurrentPage = pageNum;
                    showUsagePage(usageCurrentPage);
                }
            });
        });
    }
});
</script>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>