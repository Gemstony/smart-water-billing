<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1>Customer Dashboard</h1>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'Customer'); ?>!</p>
<!-- Customer dashboard content -->

<?php require_once __DIR__ . '/../layout/footer.php'; ?>