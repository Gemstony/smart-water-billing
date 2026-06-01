<?php
require_once __DIR__ . '/../models/Rate.php';
require_once __DIR__ . '/../models/Transaction.php';
require_once __DIR__ . '/../controllers/TokenController.php';

class PaymentController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get authentication token from AzamPay
     */
    public function getAzamPayToken()
    {
        $url = (AZAMPAY_ENVIRONMENT === 'sandbox')
            ? 'https://authenticator-sandbox.azampay.co.tz/AppRegistration/GenerateToken'
            : 'https://authenticator.azampay.co.tz/AppRegistration/GenerateToken';

        $payload = [
            'appName' => AZAMPAY_APP_NAME,
            'clientId' => AZAMPAY_CLIENT_ID,
            'clientSecret' => AZAMPAY_CLIENT_SECRET
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['data']['accessToken'] ?? $data['accessToken'] ?? false;
        }
        return false;
    }

    /**
     * Initiate checkout with AzamPay
     */
    public function initiateCheckout($paymentDetails)
    {
        $accessToken = $this->getAzamPayToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to authenticate with AzamPay'];
        }

        error_log("Access Token obtained successfully");

        $url = (AZAMPAY_ENVIRONMENT === 'sandbox')
            ? 'https://sandbox.azampay.co.tz/azampay/mno/checkout'
            : 'https://checkout.azampay.co.tz/azampay/mno/checkout';

        $payload = [
            'accountNumber' => $paymentDetails['accountNumber'],
            'amount' => (float) $paymentDetails['amount'],
            'currency' => 'TZS',
            'externalId' => $paymentDetails['externalId'],
            'provider' => $paymentDetails['provider']
        ];

        error_log("Request Payload: " . json_encode($payload));
        error_log("Request URL: $url");

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'X-API-KEY: ' . AZAMPAY_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        error_log("HTTP Status Code: $httpCode");
        error_log("Raw Response: " . $response);
        if ($curlError) {
            error_log("CURL Error: $curlError");
        }

        $responseData = json_decode($response, true);

        if ($httpCode === 200 && ($responseData['success'] ?? false)) {
            return [
                'success' => true,
                'transactionId' => $responseData['transactionId'] ?? null,
                'message' => $responseData['message'] ?? 'Payment initiated successfully'
            ];
        }

        // Enhanced error details
        $errorMessage = 'Payment initiation failed';
        if ($httpCode === 400 && isset($responseData['errors'])) {
            // Specific validation errors
            $errors = [];
            foreach ($responseData['errors'] as $field => $fieldErrors) {
                $errors[] = "$field: " . implode(', ', $fieldErrors);
            }
            $errorMessage .= ' - Validation errors: ' . implode('; ', $errors);
        } elseif ($httpCode === 401) {
            $errorMessage .= ' - Authentication failed. Check your credentials.';
        } elseif ($httpCode === 403) {
            $errorMessage .= ' - Access forbidden. Check your API key permissions.';
        } elseif ($httpCode === 404) {
            $errorMessage .= ' - API endpoint not found. Check the URL.';
        } elseif ($responseData && isset($responseData['message'])) {
            $errorMessage .= ' - ' . $responseData['message'];
        }

        return [
            'success' => false,
            'message' => $errorMessage,
            'httpCode' => $httpCode,
            'details' => $responseData,
            'curlError' => $curlError
        ];
    }

    /**
     * Process a payment and generate a token
     */
    public function processPayment($userId, $amount, $provider, $phoneNumber)
    {
        // Log incoming request
        error_log("=== AzamPay Payment Request ===");
        error_log("User ID: $userId, Amount: $amount, Provider: $provider, Phone: $phoneNumber");

        $rateModel = new Rate($this->pdo);
        $currentRate = $rateModel->getCurrent();
        $pricePerUnit = $currentRate ? $currentRate['price_per_unit'] : 1000;
        $units = floor($amount / $pricePerUnit);

        // Generate unique externalId for this transaction
        $externalId = 'WTR-' . strtoupper(uniqid());
        $controlNumber = 'AZM-' . strtoupper(uniqid());

        // Record transaction with 'pending' status
        $stmt = $this->pdo->prepare("
        INSERT INTO transactions (user_id, amount, water_units, control_number, payment_method, status, external_id)
        VALUES (?, ?, ?, ?, ?, 'pending', ?)
    ");
        $stmt->execute([$userId, $amount, $units, $controlNumber, $provider, $externalId]);

        $paymentDetails = [
            'accountNumber' => $phoneNumber,
            'amount' => $amount,
            'provider' => $provider,
            'externalId' => $externalId
        ];

        error_log("Payment Details: " . json_encode($paymentDetails));

        $result = $this->initiateCheckout($paymentDetails);

        error_log("Initiate Checkout Result: " . json_encode($result));

        if ($result['success']) {
            $stmt = $this->pdo->prepare("UPDATE transactions SET mpesa_receipt = ? WHERE external_id = ?");
            $stmt->execute([$result['transactionId'], $externalId]);

            return [
                'success' => true,
                'message' => 'Payment initiated. Please check your phone to complete payment.',
                'transactionId' => $result['transactionId']
            ];
        }

        $stmt = $this->pdo->prepare("UPDATE transactions SET status = 'failed' WHERE external_id = ?");
        $stmt->execute([$externalId]);

        return [
            'success' => false,
            'message' => $result['message']
        ];
    }

    /**
     * Handle AzamPay webhook callback
     */
    public function handleWebhook($callbackData)
    {
        error_log("AzamPay Webhook: " . json_encode($callbackData));

        $transactionId = $callbackData['transactionId'] ?? $callbackData['TransactionID'] ?? null;
        $externalId = $callbackData['externalId'] ?? $callbackData['ExternalId'] ?? null;
        $status = $callbackData['status'] ?? $callbackData['Status'] ?? '';

        if (!$transactionId && !$externalId) {
            http_response_code(400);
            echo "Missing transaction identifier";
            return;
        }

        $stmt = $this->pdo->prepare("
            SELECT * FROM transactions 
            WHERE (mpesa_receipt = ? OR external_id = ?) AND status = 'pending'
            LIMIT 1
        ");
        $stmt->execute([$transactionId, $externalId]);
        $transaction = $stmt->fetch();

        if (!$transaction) {
            http_response_code(404);
            echo "Transaction not found";
            return;
        }

        $isCompleted = (stripos($status, 'success') !== false || stripos($status, 'completed') !== false);

        if ($isCompleted) {
            $stmt = $this->pdo->prepare("
                UPDATE transactions 
                SET status = 'completed', completed_at = NOW() 
                WHERE transaction_id = ?
            ");
            $stmt->execute([$transaction['transaction_id']]);

            $tokenController = new TokenController($this->pdo);
            $result = $tokenController->generateToken(
                $transaction['user_id'],
                $transaction['water_units'],
                $transaction['payment_method']
            );

            echo $result['success'] ? "Token generated" : "Token generation failed";
        } else {
            $stmt = $this->pdo->prepare("UPDATE transactions SET status = 'failed' WHERE transaction_id = ?");
            $stmt->execute([$transaction['transaction_id']]);
            echo "Payment failed";
        }
    }
}