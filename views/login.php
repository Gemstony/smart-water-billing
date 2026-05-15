<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1>Login</h1>
<form method="POST" action="/smart-water-billing/controllers/AuthController.php">
    <label>Email:</label>
    <input type="email" name="email" required>
    <label>Password:</label>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
</form>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>