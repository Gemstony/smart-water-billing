<?php
// If user is already logged in, redirect to appropriate dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'];
    if ($role === 'admin') {
        header('Location: /smart-water-billing/admin');
    } else {
        header('Location: /smart-water-billing/dashboard');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // We will handle login via index.php routing, but keep form action to current page
    // Actually our index.php will catch POST to /smart-water-billing/login
    // So this file will not process POST directly; we'll let index.php handle.
    // But for simplicity, we can leave action empty and rely on index.php.
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Water Billing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header bg-primary text-white text-center">
                    <h4><i class="fas fa-tint"></i> Smart Water Billing</h4>
                    <small>Login to your account</small>
                </div>
                <div class="card-body">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">Invalid email or password</div>
                    <?php endif; ?>
                    <form action="/smart-water-billing/login" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="/smart-water-billing/">← Back to Home</a>
                    </div>
                </div>
                <div class="card-footer text-center text-muted">
                    &copy; <?= date('Y') ?> Smart Water Billing System
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>