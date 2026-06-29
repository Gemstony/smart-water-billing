<?php
require_once __DIR__ . '/../models/Rate.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../controllers/TokenController.php';

class PaymentController
{
    private $pdo;

    // Keep the provider map for realistic selection
    private const PROVIDER_MAP = [
        'M-Pesa' => 'Mpesa',
        'Mpesa' => 'Mpesa',
        'Tigo Pesa' => 'Tigo',
        'Tigo' => 'Tigo',
        'Airtel Money' => 'Airtel',
        'Airtel' => 'Airtel',
        'Halopesa' => 'Halopesa',
        'Azampesa' => 'Azampesa',
    ];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Log a payment attempt (keeping for debugging)
     */
    private function logPaymentAttempt($transactionId, $externalId, $requestPayload, $responsePayload, $httpCode, $errorMessage)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO payment_logs 
            (transaction_id, external_id, request_payload, response_payload, http_code, error_message)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $transactionId,
            $externalId,
            json_encode($requestPayload),
            json_encode($responsePayload),
            $httpCode,
            $errorMessage
        ]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Normalize phone number to international format (255XXXXXXXXX)
     */
    private function normalizePhoneNumber($phone)
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (strlen($phone) === 10 && $phone[0] === '0') {
            $phone = '255' . substr($phone, 1);
        }
        if (strpos($phone, '255') === 0 && strlen($phone) === 12) {
            return $phone;
        }
        if (strlen($phone) === 12 && substr($phone, 0, 3) === '255') {
            return $phone;
        }
        if (strlen($phone) === 9) {
            return '255' . $phone;
        }
        return $phone;
    }

    /**
     * Map provider to AzamPay accepted value (keep for realism)
     */
    private function normalizeProvider($provider)
    {
        $clean = trim($provider);
        if (isset(self::PROVIDER_MAP[$clean])) {
            return self::PROVIDER_MAP[$clean];
        }
        return $clean;
    }

    /**
     * SIMULATED: Get authentication token (no real API call)
     */
    public function getAzamPayToken()
    {
        // Return a fake token
        return 'fake_simulated_token_' . md5(uniqid());
    }

    /**
     * SIMULATED: Initiate checkout – immediately returns success
     */
    public function initiateCheckout($paymentDetails, $retry = true)
    {
        // Log the attempt
        error_log("SIMULATED AzamPay Checkout: " . json_encode($paymentDetails));

        // Generate a fake transaction ID
        $fakeTransactionId = 'SIM-' . strtoupper(uniqid());

        return [
            'success' => true,
            'transactionId' => $fakeTransactionId,
            'message' => 'Payment initiated successfully ',
            'httpCode' => 200,
            'responseData' => ['simulated' => true]
        ];
    }

    /**
     * Process a payment – immediately generates token (no webhook needed)
     */
    public function processPayment($userId, $amount, $provider, $phoneNumber)
    {
        error_log("=== Payment Request ===");
        error_log("User ID: $userId, Amount: $amount, Provider: $provider, Phone: $phoneNumber");

        // Normalize inputs
        $phoneNumber = $this->normalizePhoneNumber($phoneNumber);
        $provider = $this->normalizeProvider($provider);

        // Validate phone number (keep the validation for realism)
        if (!preg_match('/^255[0-9]{9}$/', $phoneNumber)) {
            return [
                'success' => false,
                'message' => 'Invalid phone number format. Must be 255XXXXXXXXX (12 digits)'
            ];
        }

        $rateModel = new Rate($this->pdo);
        $currentRate = $rateModel->getCurrent();
        $pricePerUnit = $currentRate ? $currentRate['price_per_unit'] : 1000;
        $units = floor($amount / $pricePerUnit);

        if ($units <= 0) {
            return ['success' => false, 'message' => 'Amount too low to purchase any units'];
        }

        // Generate unique identifiers
        $externalId = 'WTR-' . strtoupper(uniqid());
        $controlNumber = 'AZM-' . strtoupper(uniqid());

        // Insert transaction as 'pending' (will be completed immediately)
        $stmt = $this->pdo->prepare("
            INSERT INTO transactions (user_id, amount, water_units, control_number, payment_method, status, external_id)
            VALUES (?, ?, ?, ?, ?, 'pending', ?)
        ");
        $stmt->execute([$userId, $amount, $units, $controlNumber, $provider, $externalId]);
        $transactionId = $this->pdo->lastInsertId();

        // Prepare payment details for logging
        $paymentDetails = [
            'accountNumber' => $phoneNumber,
            'amount' => $amount,
            'provider' => $provider,
            'externalId' => $externalId
        ];

        error_log("Simulated Payment Details: " . json_encode($paymentDetails));

        // Log the attempt (with no response yet)
        $this->logPaymentAttempt(
            $transactionId,
            $externalId,
            $paymentDetails,
            null,
            null,
            'Initiated payment'
        );

        // Simulate checkout – always succeeds
        $result = $this->initiateCheckout($paymentDetails);

        error_log("Simulated Checkout Result: " . json_encode($result));

        // Update log with the simulated response
        $this->logPaymentAttempt(
            $transactionId,
            $externalId,
            $paymentDetails,
            $result,
            $result['httpCode'] ?? 200,
            $result['success'] ? null : 'Payment failure'
        );

        // Immediately mark transaction as completed
        $stmt = $this->pdo->prepare("
            UPDATE transactions 
            SET status = 'completed', completed_at = NOW(), mpesa_receipt = ? 
            WHERE external_id = ?
        ");
        $stmt->execute([$result['transactionId'], $externalId]);

        // Generate the token
        $tokenController = new TokenController($this->pdo);
        $tokenResult = $tokenController->generateToken(
            $userId,
            $units,
            $provider
        );

        if ($tokenResult['success']) {
            return [
                'success' => true,
                'message' => 'Payment successfully completed. Token generated.',
                'transactionId' => $result['transactionId'],
                'token' => $tokenResult['token'],
                'units' => $units
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Payment succeeded but token generation failed: ' . $tokenResult['message']
            ];
        }
    }

    /**
     * Handle webhook (stub – not used, but kept for compatibility)
     */
    public function handleWebhook($callbackData)
    {
        error_log("Simulated Webhook called (ignored): " . json_encode($callbackData));
        echo "Webhook received but ignored in simulation mode.";
    }
}