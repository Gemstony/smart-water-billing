<?php
// No session or login check needed – this is a public landing page.
$pageTitle = 'Smart Water Billing System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0D52C1 0%,  #0A429E 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero {
            color: white;
            text-align: center;
            padding: 60px 20px 40px;
        }
        .hero h1 {
            font-size: 2.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .hero p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin: 40px auto;
            max-width: 1200px;
            padding: 0 20px;
        }
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            flex: 1;
            min-width: 250px;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .feature-card i {
            font-size: 3rem;
            color: #0A429E;
            margin-bottom: 20px;
        }
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #333;
        }
        .feature-card p {
            color: #666;
            line-height: 1.5;
        }
        .btn-login {
            background: white;
            color: #0A429E;
            border: none;
            padding: 12px 40px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn-login:hover {
            background: #f0f0f0;
            transform: scale(1.05);
            color: #0A429E;
        }
        footer {
            text-align: center;
            color: rgba(255,255,255,0.7);
            padding: 30px;
            margin-top: 40px;
        }
        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .feature-card { min-width: 200px; }
        }
    </style>
</head>
<body>
    <div class="hero">
        <h1><i class="fas fa-tint"></i> SMART WATER BILLING SYSTEM</h1>
        <p>Using Control Number and Token-Based Units</p>
        <a href="/smart-water-billing/login" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Login to Dashboard
        </a>
    </div>

    <div class="card-container">
        <div class="feature-card">
            <i class="fas fa-ticket-alt"></i>
            <h3>Token-Based Prepaid</h3>
            <p>Pay before you consume. Purchase water tokens using a control number and redeem instantly.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-mobile-alt"></i>
            <h3>Mobile Money Integration</h3>
            <p>Seamless payments via M-Pesa, Tigo Pesa, and Airtel Money. Convenient and secure.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-chart-line"></i>
            <h3>Real-Time Monitoring</h3>
            <p>Track your water consumption in real-time, get alerts, and manage usage efficiently.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-microchip"></i>
            <h3>IoT Smart Meter</h3>
            <p>ESP32-based meter with flow sensor and solenoid valve for automated control.</p>
        </div>
    </div>

    <div class="card-container">
        <div class="feature-card">
            <i class="fas fa-chart-pie"></i>
            <h3>Analytics Dashboard</h3>
            <p>View consumption charts, payment history, and token usage for better decision making.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-shield-alt"></i>
            <h3>Secure & Transparent</h3>
            <p>All transactions are logged and traceable. Eliminates corruption and billing errors.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-users-cog"></i>
            <h3>Admin Management</h3>
            <p>Manage customers, set water rates, generate reports, and oversee operations.</p>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> National Institute of Transport (NIT) - Smart Water Billing System. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>