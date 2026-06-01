<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../models/Token.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /smart-water-billing/login');
    exit;
}

$userId = $_SESSION['user_id'];
$tokenModel = new Token($pdo);
$tokens = $tokenModel->getByUser($userId);

$pageTitle = 'Token History';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3">My Token History</h1>
                <a href="/smart-water-billing/dashboard" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-ticket-alt"></i> All Tokens
                </div>
                <div class="card-body">
                    <?php if (count($tokens) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tokensTable">
                                <thead>
                                    <tr>
                                        <th>Token Code</th>
                                        <th>Units (m³)</th>
                                        <th>Status</th>
                                        <th>Generated At</th>
                                        <th>Expires At</th>
                                        <th>Used At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tokens as $token): ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars($token['token_code']) ?></code></td>
                                            <td><?= $token['units_purchased'] ?></td>
                                            <td>
                                                <?php if ($token['is_used']): ?>
                                                    <span class="badge bg-secondary">Used</span>
                                                <?php elseif (strtotime($token['expires_at']) < time()): ?>
                                                    <span class="badge bg-danger">Expired</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Valid</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d M Y H:i', strtotime($token['generated_at'])) ?></td>
                                            <td><?= $token['expires_at'] ? date('d M Y H:i', strtotime($token['expires_at'])) : 'Never' ?></td>
                                            <td><?= $token['used_at'] ? date('d M Y H:i', strtotime($token['used_at'])) : '—' ?></td>
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
                            Showing <span id="showingFrom">1</span> to <span id="showingTo">10</span> of <span id="totalRecords"><?= count($tokens) ?></span> entries
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No tokens generated yet. <a href="/smart-water-billing/buy-token">Buy your first token</a>.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('tokensTable');
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