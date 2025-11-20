<?php
/**
 * DukPay Payment Success Page
 * 
 * This page is shown after successful payment return
 */
session_start();

// Load configuration
$config = require __DIR__ . '/config.php';

require_once __DIR__ . '/vendor/autoload.php';
use DukPay\DukPay;
use DukPay\DukPayException;

$orderId = $_SESSION['order_id'] ?? null;
$dukpayOrderId = $_SESSION['dukpay_order_id'] ?? null;
$amount = $_SESSION['amount'] ?? null;
$currency = $_SESSION['currency'] ?? 'USD';

// If order info is in session, try to query the order status
$orderStatus = null;
if ($orderId) {
    try {
        $dukpay = new DukPay(
            $config['api_key'],
            $config['merchant_id'],
            $config['sandbox']
        );
        
        $orderStatus = $dukpay->orders()->queryByMerchantOrderId($orderId);
    } catch (DukPayException $e) {
        // Order query failed, but we can still show success page
        error_log('Order query failed: ' . $e->getMessage());
    }
}

// Clear session data
unset($_SESSION['order_id']);
unset($_SESSION['dukpay_order_id']);
unset($_SESSION['amount']);
unset($_SESSION['currency']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - DukPay</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .success-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #4caf50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .success-message {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .order-details {
            background: #f5f5f5;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .detail-label {
            color: #666;
        }
        
        .detail-value {
            color: #333;
            font-weight: 600;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1>Payment Successful!</h1>
        <p class="success-message">Thank you for your payment. Your transaction has been processed.</p>
        
        <?php if ($orderId || $amount): ?>
        <div class="order-details">
            <?php if ($orderId): ?>
            <div class="detail-row">
                <span class="detail-label">Order ID:</span>
                <span class="detail-value"><?php echo htmlspecialchars($orderId); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($dukpayOrderId): ?>
            <div class="detail-row">
                <span class="detail-label">Transaction ID:</span>
                <span class="detail-value"><?php echo htmlspecialchars($dukpayOrderId); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($amount): ?>
            <div class="detail-row">
                <span class="detail-label">Amount:</span>
                <span class="detail-value"><?php echo htmlspecialchars($amount); ?> <?php echo htmlspecialchars($currency); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($orderStatus && isset($orderStatus['status'])): ?>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value">
                    <span class="status-badge status-<?php echo htmlspecialchars($orderStatus['status']); ?>">
                        <?php echo strtoupper(htmlspecialchars($orderStatus['status'])); ?>
                    </span>
                </span>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <a href="payment-form.php" class="btn">Make Another Payment</a>
    </div>
</body>
</html>

