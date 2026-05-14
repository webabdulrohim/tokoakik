<?php
/**
 * Tripay Payment Gateway Integration - Core Stone Indonesia
 */
require_once '../config/config.php';

class TripayPayment {
    private $merchantCode;
    private $privateKey;
    private $publicKey;
    private $mode;
    
    public function __construct() {
        $this->merchantCode = TRIPAY_MERCHANT_CODE;
        $this->privateKey = TRIPAY_PRIVATE_KEY;
        $this->publicKey = TRIPAY_PUBLIC_KEY;
        $this->mode = TRIPAY_MODE;
    }
    
    private function getBaseUrl() {
        return $this->mode === 'production' 
            ? 'https://tripay.co.id/api-sandbox/' 
            : 'https://tripay.co.id/api/';
    }
    
    public function createTransaction($orderData) {
        $merchantCode = $this->merchantCode;
        $amount = $orderData['grand_total'];
        $orderNumber = $orderData['order_number'];
        $customerName = $orderData['customer_name'];
        $customerEmail = $orderData['customer_email'] ?? '';
        $customerPhone = $orderData['customer_phone'] ?? '';
        
        $data = [
            'method'         => $orderData['payment_method'] ?? 'BRIVA',
            'merchant_code'  => $merchantCode,
            'amount'         => $amount,
            'order_no'       => $orderNumber,
            'name'           => $customerName,
            'email'          => $customerEmail,
            'phone'          => $customerPhone,
            'return_url'     => APP_URL . '/payment-return.php',
            'signature'      => hash_hmac('sha256', $merchantCode.$orderNumber.$amount, $this->privateKey),
            'expired_time'   => (time() + (24 * 60 * 60)), // 24 jam
            'items'          => $orderData['items'] ?? []
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => $this->getBaseUrl() . 'transaction/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $this->privateKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return json_decode($response, true);
    }
    
    public function getTransactionList($pageNumber = 1, $perPage = 20) {
        $data = [
            'page' => $pageNumber,
            'per_page' => $perPage
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => $this->getBaseUrl() . 'merchant/transaction?' . http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $this->privateKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }
    
    public function getTransactionDetail($orderNumber) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => $this->getBaseUrl() . 'merchant/transaction-detail?merchant_code=' . $this->merchantCode . '&order_no=' . $orderNumber,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $this->privateKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }
    
    public function getPaymentChannels() {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_URL            => $this->getBaseUrl() . 'payment-channel',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $this->privateKey],
            CURLOPT_FAILONERROR    => false,
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }
}

// Handle callback from Tripay
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['merchant_code'])) {
    $json = file_get_contents('php://input');
    $callbackData = json_decode($json, true);
    
    if ($callbackData) {
        $signature = hash_hmac('sha256', $json, TRIPAY_PRIVATE_KEY);
        
        if ($signature === $_SERVER['HTTP_X_CALLBACK_SIGNATURE']) {
            // Valid callback
            $orderNumber = $callbackData['order_no'];
            $status = $callbackData['status'];
            
            // Update order status in database
            $orderStatus = 'pending';
            if ($status == 'PAID') {
                $orderStatus = 'paid';
            } elseif ($status == 'FAILED' || $status == 'EXPIRED') {
                $orderStatus = 'cancelled';
            }
            
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, tripay_reference = ? WHERE order_number = ?");
            $stmt->execute([$orderStatus, $callbackData['tripay_ref'], $orderNumber]);
            
            echo json_encode(['success' => true]);
            exit();
        }
    }
    
    echo json_encode(['success' => false]);
    exit();
}
?>
