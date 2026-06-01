<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Transaction.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /smart-water-billing/login');
    exit;
}

$transactionModel = new Transaction($pdo);
$transactions = $transactionModel->getAll();

$pageTitle = 'All Transactions';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">All Transactions</h1>
                <a href="/smart-water-billing/admin/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-credit-card"></i> Customer Payments
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
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transactions as $tx): ?>
                                        <tr>
                                            <td><?= $tx['transaction_id'] ?></td>
                                            <td><?= htmlspecialchars($tx['full_name']) ?><br><small><?= htmlspecialchars($tx['email']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($tx['meter_id'] ?? '—') ?></td>
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
                        <!-- Pagination Controls -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center" id="pagination">
                            </ul>
                        </nav>
                        <div class="text-center text-muted small">
                            Showing <span id="showingFrom">1</span> to <span id="showingTo">10</span> of <span id="totalRecords"><?= count($transactions) ?></span> entries
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No transactions found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('transactionsTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const pagination = document.getElementById('pagination');
    const showingFrom = document.getElementById('showingFrom');
    const showingTo = document.getElementById('showingTo');
    const totalRecords = document.getElementById('totalRecords');

    const rowsPerPage = 10;
    let currentPage = 1;
    const totalPages = Math.ceil(rows.length / rowsPerPage);

    function showPage(page) {
        // Hide all rows
        rows.forEach(row => row.style.display = 'none');

        // Calculate start and end indices
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        // Show rows for current page
        for (let i = start; i < end && i < rows.length; i++) {
            rows[i].style.display = '';
        }

        // Update showing info
        showingFrom.textContent = start + 1;
        showingTo.textContent = Math.min(end, rows.length);

        // Update pagination controls
        updatePaginationControls();
    }

    function updatePaginationControls() {
        pagination.innerHTML = '';

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>`;
        pagination.appendChild(prevLi);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            pagination.appendChild(li);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>`;
        pagination.appendChild(nextLi);

        // Add click event listeners
        pagination.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page >= 1 && page <= totalPages && page !== currentPage) {
                    currentPage = page;
                    showPage(currentPage);
                }
            });
        });
    }

    // Initialize
    if (rows.length > 0) {
        showPage(currentPage);
    } else {
        pagination.style.display = 'none';
        showingFrom.parentElement.style.display = 'none';
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>