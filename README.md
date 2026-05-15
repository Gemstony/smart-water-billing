# Smart Water Billing System

A PHP-based smart water billing application with token-based billing, usage tracking, and admin management.

## Features

- **Customer Portal**: View balance, buy tokens, track usage history
- **Admin Panel**: Manage users, generate reports, set rates
- **REST API**: Get balance, validate tokens, submit usage readings
- **Token-Based Billing**: Purchase and activate water tokens

## Project Structure

```
smart-water-billing/
├── assets/              # Static files
│   ├── css/style.css
│   ├── js/main.js
│   └── images/
├── includes/            # Core PHP includes
│   ├── config.php       # Database configuration
│   └── auth.php         # Authentication helpers
├── controllers/         # Business logic controllers
│   ├── AuthController.php
│   ├── TokenController.php
│   ├── PaymentController.php
│   ├── UsageController.php
│   └── AdminController.php
├── models/              # Data models
│   ├── User.php
│   ├── Token.php
│   ├── Transaction.php
│   ├── WaterUsage.php
│   └── Rate.php
├── views/               # Presentation layer
│   ├── customers/
│   │   ├── dashboard.php
│   │   ├── buy_token.php
│   │   └── history.php
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── users.php
│   │   └── reports.php
│   ├── layout/
│   │   ├── header.php
│   │   └── footer.php
│   └── login.php
├── api/                 # API endpoints
│   ├── get_balance.php
│   ├── validate_token.php
│   └── submit_usage.php
├── index.php            # Front controller
├── .htaccess            # URL rewriting
└── README.md
```

## Setup

1. Import the database schema
2. Configure database credentials in `includes/config.php`
3. Run on Apache with mod_rewrite enabled (or use the built-in PHP server)

## License

MIT