<?php
/**
 * DukPay Payment Form
 * 
 * This form allows users to enter payment information
 */
session_start();

// Load configuration
$config = require __DIR__ . '/config.php';

// Initialize DukPay (if needed for validation)
require_once __DIR__ . '/vendor/autoload.php';
use DukPay\DukPay;

// Handle form submission
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dukpay = new DukPay(
            $config['api_key'],
            $config['merchant_id'],
            $config['sandbox']
        );
        
        // Get form data
        $orderId = 'ORDER_' . time() . '_' . rand(1000, 9999);
        $amount = floatval($_POST['amount'] ?? 0);
        $currency = $_POST['currency'] ?? 'USD';
        $country = $_POST['country'] ?? 'US';
        
        // Customer information
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        
        // Product information
        $productName = trim($_POST['product_name'] ?? 'Payment');
        $productDescription = trim($_POST['product_description'] ?? '');
        
        // Validate required fields
        if (empty($customerName) || empty($customerEmail) || $amount <= 0) {
            throw new Exception('Please fill in all required fields and enter a valid amount.');
        }
        
        // Prepare order parameters
        $orderParams = [
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $currency,
            'country' => $country,
            'return_url' => $config['return_url'] ?? 'https://www.american-software.net/dpay/payment-success.php',
            'notify_url' => $config['notify_url'] ?? 'https://www.american-software.net/dpay/payment-callback.php',
            'product_name' => $productName,
            'product_description' => $productDescription,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
        ];
        
        // Create order based on country
        $method = 'create' . ucfirst(strtolower($country)) . 'Order';
        if (!method_exists($dukpay->orders(), $method)) {
            // Use aggregated order for unsupported countries
            $response = $dukpay->orders()->createAggregatedOrder($orderParams);
        } else {
            $response = call_user_func([$dukpay->orders(), $method], $orderParams);
        }
        
        // Store order info in session for success page
        $_SESSION['order_id'] = $orderId;
        $_SESSION['dukpay_order_id'] = $response['order_id'] ?? null;
        $_SESSION['amount'] = $amount;
        $_SESSION['currency'] = $currency;
        
        // Redirect to payment URL
        if (isset($response['payment_url'])) {
            header('Location: ' . $response['payment_url']);
            exit;
        } else {
            throw new Exception('Payment URL not received from DukPay.');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DukPay Payment Form</title>
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
        
        .payment-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .required {
            color: #e74c3c;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .currency-amount {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 10px;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .payment-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h1>💳 Payment Form</h1>
        <p class="subtitle">Enter your payment information to proceed</p>
        
        <?php if ($error): ?>
            <div class="error-message">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="paymentForm">
            <div class="form-group">
                <label for="customer_name">Full Name <span class="required">*</span></label>
                <input 
                    type="text" 
                    id="customer_name" 
                    name="customer_name" 
                    placeholder="John Doe"
                    required
                    value="<?php echo isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="customer_email">Email Address <span class="required">*</span></label>
                <input 
                    type="email" 
                    id="customer_email" 
                    name="customer_email" 
                    placeholder="john@example.com"
                    required
                    value="<?php echo isset($_POST['customer_email']) ? htmlspecialchars($_POST['customer_email']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="customer_phone">Phone Number</label>
                <input 
                    type="tel" 
                    id="customer_phone" 
                    name="customer_phone" 
                    placeholder="+1234567890"
                    value="<?php echo isset($_POST['customer_phone']) ? htmlspecialchars($_POST['customer_phone']) : ''; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="product_name">Product/Service Name</label>
                <input 
                    type="text" 
                    id="product_name" 
                    name="product_name" 
                    placeholder="Product or Service"
                    value="<?php echo isset($_POST['product_name']) ? htmlspecialchars($_POST['product_name']) : 'Payment'; ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="product_description">Description</label>
                <textarea 
                    id="product_description" 
                    name="product_description" 
                    rows="3"
                    placeholder="Optional description"
                ><?php echo isset($_POST['product_description']) ? htmlspecialchars($_POST['product_description']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Amount & Currency <span class="required">*</span></label>
                <div class="currency-amount">
                    <select name="currency" id="currency" required>
                        <option value="USD" <?php echo (isset($_POST['currency']) && $_POST['currency'] === 'USD') ? 'selected' : 'selected'; ?>>USD</option>
                        <option value="EUR" <?php echo (isset($_POST['currency']) && $_POST['currency'] === 'EUR') ? 'selected' : ''; ?>>EUR</option>
                        <option value="TRY" <?php echo (isset($_POST['currency']) && $_POST['currency'] === 'TRY') ? 'selected' : ''; ?>>TRY</option>
                        <option value="RUB" <?php echo (isset($_POST['currency']) && $_POST['currency'] === 'RUB') ? 'selected' : ''; ?>>RUB</option>
                        <option value="KRW" <?php echo (isset($_POST['currency']) && $_POST['currency'] === 'KRW') ? 'selected' : ''; ?>>KRW</option>
                        <option value="PHP" <?php echo (isset($_POST['currency']) && $_POST['currency'] === 'PHP') ? 'selected' : ''; ?>>PHP</option>
                        <option value="IDR" <?php echo (isset($_POST['currency']) && $_POST['currency'] === 'IDR') ? 'selected' : ''; ?>>IDR</option>
                        <option value="BRL" <?php echo (isset($_POST['currency']) && $_POST['currency'] === 'BRL') ? 'selected' : ''; ?>>BRL</option>
                        <option value="MXN" <?php echo (isset($_POST['currency']) && $_POST['currency'] === 'MXN') ? 'selected' : ''; ?>>MXN</option>
                        <option value="THB" <?php echo (isset($_POST['currency']) && $_POST['currency'] === 'THB') ? 'selected' : ''; ?>>THB</option>
                        <option value="VND" <?php echo (isset($_POST['currency']) && $_POST['currency'] === 'VND') ? 'selected' : ''; ?>>VND</option>
                    </select>
                    <input 
                        type="number" 
                        id="amount" 
                        name="amount" 
                        placeholder="100.00"
                        step="0.01"
                        min="0.01"
                        required
                        value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>"
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="country">Country <span class="required">*</span></label>
                <select name="country" id="country" required>
                    <option value="US" <?php echo (isset($_POST['country']) && $_POST['country'] === 'US') ? 'selected' : 'selected'; ?>>United States</option>
                    <option value="TR" <?php echo (isset($_POST['country']) && $_POST['country'] === 'TR') ? 'selected' : ''; ?>>Turkey</option>
                    <option value="ID" <?php echo (isset($_POST['country']) && $_POST['country'] === 'ID') ? 'selected' : ''; ?>>Indonesia</option>
                    <option value="BR" <?php echo (isset($_POST['country']) && $_POST['country'] === 'BR') ? 'selected' : ''; ?>>Brazil</option>
                    <option value="PH" <?php echo (isset($_POST['country']) && $_POST['country'] === 'PH') ? 'selected' : ''; ?>>Philippines</option>
                    <option value="MX" <?php echo (isset($_POST['country']) && $_POST['country'] === 'MX') ? 'selected' : ''; ?>>Mexico</option>
                    <option value="RU" <?php echo (isset($_POST['country']) && $_POST['country'] === 'RU') ? 'selected' : ''; ?>>Russia</option>
                    <option value="VN" <?php echo (isset($_POST['country']) && $_POST['country'] === 'VN') ? 'selected' : ''; ?>>Vietnam</option>
                    <option value="KR" <?php echo (isset($_POST['country']) && $_POST['country'] === 'KR') ? 'selected' : ''; ?>>South Korea</option>
                    <option value="PK" <?php echo (isset($_POST['country']) && $_POST['country'] === 'PK') ? 'selected' : ''; ?>>Pakistan</option>
                    <option value="TH" <?php echo (isset($_POST['country']) && $_POST['country'] === 'TH') ? 'selected' : ''; ?>>Thailand</option>
                    <option value="CO" <?php echo (isset($_POST['country']) && $_POST['country'] === 'CO') ? 'selected' : ''; ?>>Colombia</option>
                    <option value="SA" <?php echo (isset($_POST['country']) && $_POST['country'] === 'SA') ? 'selected' : ''; ?>>Saudi Arabia</option>
                    <option value="SG" <?php echo (isset($_POST['country']) && $_POST['country'] === 'SG') ? 'selected' : ''; ?>>Singapore</option>
                    <option value="AR" <?php echo (isset($_POST['country']) && $_POST['country'] === 'AR') ? 'selected' : ''; ?>>Argentina</option>
                </select>
            </div>
            
            <button type="submit" class="submit-btn" id="submitBtn">
                Proceed to Payment
            </button>
        </form>
    </div>
    
    <script>
        // Form validation
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
        });
        
        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>

