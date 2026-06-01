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
                <a href="/smart-water-billing/admin/add_user" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Customer
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <i class="fas fa-users"></i> Registered Customers
                </div>
                <div class="card-body">
                     <?php if (count($customers) > 0): ?>
                         <div class="table-responsive">
                             <table class="table table-bordered table-striped" id="customersTable">
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
                                         </tr>
                                     <?php endforeach; ?>
                                 </tbody>
                             </table>
                         </div>
                         <?php if (count($customers) > 5): ?>
                         <div class="d-flex justify-content-between mt-3">
                             <nav aria-label="Page navigation">
                                 <ul class="pagination mb-0">
                                     <li class="page-item disabled" id="customersPrev">
                                         <a class="page-link" href="#" aria-label="Previous">
                                             <span aria-hidden="true">&laquo;</span>
                                         </a>
                                     </li>
                                     <li class="page-item active" id="customersPage1">
                                         <a class="page-link" href="#">1</a>
                                     </li>
                                     <?php
                                     $totalPages = ceil(count($customers) / 5);
                                     for ($i = 2; $i <= min($totalPages, 5); $i++):
                                     ?>
                                     <li class="page-item" id="customersPage<?= $i ?>">
                                         <a class="page-link" href="#"><?= $i ?></a>
                                     </li>
                                     <?php endfor; ?>
                                     <?php if ($totalPages > 5): ?>
                                     <li class="page-item disabled">
                                         <span class="page-link">...</span>
                                     </li>
                                     <li class="page-item" id="customersPageLast">
                                         <a class="page-link" href="#"><?= $totalPages ?></a>
                                     </li>
                                     <?php endif; ?>
                                     <li class="page-item" id="customersNext">
                                         <a class="page-link" href="#" aria-label="Next">
                                             <span aria-hidden="true">&raquo;</span>
                                         </a>
                                     </li>
                                 </ul>
                             </nav>
                             <div>
                                 <select id="customersPageSize" class="form-select form-select-sm" style="width: auto;">
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
                         <p class="text-muted">No customers registered yet.</p>
                     <?php endif; ?>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <script>
 document.addEventListener('DOMContentLoaded', function() {
     // Pagination for Customers Table
     const customersTable = document.getElementById('customersTable');
     if (customersTable) {
         const customersRows = customersTable.querySelectorAll('tbody tr');
         const customersPrev = document.getElementById('customersPrev');
         const customersNext = document.getElementById('customersNext');
         const customersPageSizeSelect = document.getElementById('customersPageSize');
         
         let customersCurrentPage = 1;
         let customersRowsPerPage = 5;
         const updateCustomersTotalPages = () => Math.ceil(customersRows.length / customersRowsPerPage);
         
         function showCustomersPage(page) {
             customersRows.forEach((row, index) => {
                 const start = (page - 1) * customersRowsPerPage;
                 const end = start + customersRowsPerPage;
                 row.style.display = (index >= start && index < end) ? '' : 'none';
             });
             
             // Update active page indicator
             document.querySelectorAll('[id^="customersPage"]').forEach(el => el.classList.remove('active'));
             
             if (page <= 5) {
                 document.getElementById(`customersPage${page}`)?.classList.add('active');
             } else if (page === updateCustomersTotalPages()) {
                 document.getElementById('customersPageLast')?.classList.add('active');
             }
             
             // Update prev/next buttons
             customersPrev.classList.toggle('disabled', page === 1);
             customersNext.classList.toggle('disabled', page === updateCustomersTotalPages());
         }
         
         // Initial display
         showCustomersPage(customersCurrentPage);
         
         // Event listeners for pagination
         customersPrev.addEventListener('click', function(e) {
             e.preventDefault();
             if (!this.classList.contains('disabled') && customersCurrentPage > 1) {
                 customersCurrentPage--;
                 showCustomersPage(customersCurrentPage);
             }
         });
         
         customersNext.addEventListener('click', function(e) {
             e.preventDefault();
             if (!this.classList.contains('disabled') && customersCurrentPage < updateCustomersTotalPages()) {
                 customersCurrentPage++;
                 showCustomersPage(customersCurrentPage);
             }
         });
         
         // Page size change
         customersPageSizeSelect.addEventListener('change', function() {
             customersRowsPerPage = parseInt(this.value);
             customersCurrentPage = 1;
             showCustomersPage(customersCurrentPage);
         });
         
         // Page number clicks
         document.querySelectorAll('[id^="customersPage"]').forEach(button => {
             button.addEventListener('click', function(e) {
                 e.preventDefault();
                 if (this.classList.contains('disabled') || this.classList.contains('active')) return;
                 
                 const pageNum = this.textContent === '...' ? 
                     (this.id.includes('Last') ? updateCustomersTotalPages() : 1) : 
                     parseInt(this.textContent);
                 
                 if (!isNaN(pageNum)) {
                     customersCurrentPage = pageNum;
                     showCustomersPage(customersCurrentPage);
                 }
             });
         });
     }
 });
 </script>
 
 <?php require_once __DIR__ . '/../layout/footer.php'; ?>