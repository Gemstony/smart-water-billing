# 💧 Smart Water Billing System

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat&logo=bootstrap)
![License](https://img.shields.io/badge/License-MIT-green.svg)

A comprehensive PHP-based smart water billing application with token-based billing, real-time usage tracking, and comprehensive admin management. Designed for water utility companies to manage customer accounts, process payments, and monitor consumption patterns.

## ✨ Features

### Customer Portal
- 📊 **Dashboard**: Real-time balance, recent transactions, and consumption charts
- 🎫 **Buy Tokens**: Purchase water tokens via multiple payment methods (M-Pesa, Tigo Pesa, Airtel Money)
- 📜 **Payment History**: Track all past transactions and payments
- 🔑 **Token History**: View generated tokens, expiration dates, and usage status
- 📈 **Usage Analytics**: Visual charts showing water consumption over time

### Admin Panel
- 👥 **Customer Management**: Add, edit, view, and manage customer accounts
- 💰 **Transaction Monitoring**: View all customer payments and transactions
- 🎫 **Token Management**: Monitor all generated tokens across the system
- 📊 **Reports**: Generate comprehensive reports on revenue and usage
- 💵 **Rate Management**: Set and update water rates per unit
- 📈 **Dashboard Analytics**: Real-time metrics, revenue charts, and customer insights

### REST API (ESP32 Integration)
- 🔍 **Get Balance**: Retrieve current account balance
- ✅ **Validate Token**: Verify and activate water tokens
- 📝 **Submit Usage**: Log water consumption readings
- 🔧 **Valve Control**: Control smart meter valve status

### Additional Features
- 🔐 **Role-Based Access**: Separate interfaces for admins and customers
- 📱 **Responsive Design**: Mobile-friendly Bootstrap 5 interface
- 🎨 **Modern UI**: Clean, professional dashboard with Chart.js visualizations
- ⚡ **Real-Time Updates**: Dynamic data fetching and display
- 🔔 **SweetAlert Integration**: Beautiful confirmation dialogs and notifications

## 📁 Project Structure

```
smart-water-billing/
├── 📂 assets/                    # Static assets
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
├── 📂 includes/                  # Core configuration
│   ├── config.php                 # Database & app configuration
│   └── auth.php                   # Authentication helpers
├── 📂 controllers/                # Business logic layer
│   ├── AuthController.php         # Login/logout logic
│   ├── TokenController.php       # Token generation & validation
│   ├── PaymentController.php     # Payment processing
│   ├── UsageController.php       # Water usage logging
│   ├── AdminController.php       # Admin operations
│   ├── AdminDashboardController.php  # Admin dashboard data
│   ├── DashboardController.php  # Customer dashboard data
│   └── EspDeviceController.php   # ESP32 device management
├── 📂 models/                     # Data models
│   ├── User.php                   # User operations
│   ├── Token.php                  # Token operations
│   ├── Transaction.php           # Transaction operations
│   ├── WaterUsage.php            # Usage logging
│   ├── Rate.php                   # Rate management
│   └── EspDevice.php              # ESP device management
├── 📂 views/                      # Presentation layer
│   ├── customers/                # Customer-facing pages
│   │   ├── dashboard.php          # Customer dashboard
│   │   ├── buy_token.php          # Token purchase
│   │   ├── history.php            # Payment history
│   │   └── token_history.php      # Token history
│   ├── admin/                    # Admin-facing pages
│   │   ├── dashboard.php          # Admin dashboard
│   │   ├── users.php              # Customer list
│   │   ├── add_user.php           # Add new customer
│   │   ├── edit_user.php          # Edit customer
│   │   ├── view_customer.php      # Customer details
│   │   ├── transactions.php      # All transactions
│   │   ├── customer_tokens.php    # All tokens
│   │   ├── rates.php              # Rate management
│   │   └── reports.php            # Reports
│   ├── layout/                   # Layout templates
│   │   ├── header.php             # Common header & sidebar
│   │   └── footer.php             # Common footer
│   ├── login.php                 # Login page
│   └── logout.php                # Logout handler
├── 📂 api/                        # REST API endpoints
│   ├── get_balance.php            # Get account balance
│   ├── validate_token.php         # Validate water token
│   ├── submit_usage.php           # Submit usage reading
│   └── get_valve_status.php       # Get valve status
├── index.php                      # Front controller (routing)
├── .htaccess                      # URL rewriting rules
├── smart_water_billing.sql        # Database schema
└── README.md                      # This file
```

## 🚀 Setup Instructions

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache web server with mod_rewrite enabled
- Composer (optional, for dependency management)

### Installation Steps

1. **Clone or download the repository**
   ```bash
   git clone <repository-url>
   cd smart-water-billing
   ```

2. **Import the database schema**
   ```bash
   mysql -u root -p < smart_water_billing.sql
   ```

3. **Configure database credentials**
   Edit `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'smart_water_billing');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Configure web server**
   - Ensure Apache mod_rewrite is enabled
   - Point document root to the project directory
   - Or use PHP built-in server:
     ```bash
     php -S localhost:8000
     ```

5. **Access the application**
   - Admin: `http://localhost/smart-water-billing/admin/dashboard`
   - Customer: `http://localhost/smart-water-billing/dashboard`
   - Login: `http://localhost/smart-water-billing/login`

### Default Credentials
- **Admin**: Create via database or registration
- **Customer**: Create via admin panel or registration

## 🛠️ Tech Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: Bootstrap 5.3, Font Awesome 6
- **Charts**: Chart.js 4.4
- **Notifications**: SweetAlert2
- **Architecture**: MVC Pattern

## 📊 Database Schema

The application uses the following main tables:
- `users` - Customer and admin accounts
- `tokens` - Generated water tokens
- `transactions` - Payment transactions
- `water_usage` - Water consumption logs
- `rates` - Water rate configurations
- `esp_devices` - ESP32 device registrations

## 🔌 API Endpoints

### Get Balance
```
GET /api/get_balance.php?user_id={id}
```

### Validate Token
```
POST /api/validate_token.php
Body: { token_code: "XXXXX", meter_id: "YYYY" }
```

### Submit Usage
```
POST /api/submit_usage.php
Body: { meter_id: "YYYY", water_used: 10.5 }
```

### Get Valve Status
```
GET /api/get_valve_status.php?meter_id={id}
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👨‍💻 Author

Smart Water Billing System - Water Utility Management Solution

---

**Note**: This system is designed for integration with ESP32 smart meters for real-time water monitoring and control.