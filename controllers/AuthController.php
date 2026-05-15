<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    /**
     * Login user with email and password
     * @param string $email
     * @param string $password
     * @return bool true on success, false on failure
     */
    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['meter_id'] = $user['meter_id'];
            return true;
        }
        return false;
    }

    /**
     * Logout user - destroy session
     */
    public function logout() {
        session_destroy();
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /**
     * Require login – redirect to login page if not logged in
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /smart-water-billing/login');
            exit;
        }
    }

    /**
     * Require admin role – redirect to customer dashboard if not admin
     */
    public function requireAdmin() {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'admin') {
            header('Location: /smart-water-billing/dashboard');
            exit;
        }
    }
}