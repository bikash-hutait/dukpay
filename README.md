# DukPay PHP SDK

A comprehensive PHP SDK for integrating with the DukPay payment gateway. This SDK provides easy-to-use methods for creating payment orders, payouts, refunds, subscriptions, and handling callbacks.

**Production URL**: https://www.american-software.net/dpay

## Installation

Install via Composer:

```bash
composer require dukpay/php-sdk
```

Or add to your `composer.json`:

```json
{
    "require": {
        "dukpay/php-sdk": "*"
    }
}
```

## Requirements

- PHP >= 7.4
- cURL extension (usually included with PHP)

## Quick Start

### 1. Initialize the SDK

```php
<?php
require_once 'vendor/autoload.php';

use DukPay\DukPay;

$apiKey = 'YOUR_API_KEY';
$merchantId = 'YOUR_MERCHANT_ID';
$sandbox = true; // Set to false for production

$dukpay = new DukPay($apiKey, $merchantId, $sandbox);
```

### 2. Create a Payment Order

```php
use DukPay\DukPayException;

try {
    $orderParams = [
        'order_id' => 'ORDER_' . time(),
        'amount' => 100.00,
        'currency' => 'TRY',
        'return_url' => 'https://yoursite.com/payment/return',
        'notify_url' => 'https://yoursite.com/payment/notify',
        'product_name' => 'Test Product',
        'customer_name' => 'John Doe',
        'customer_email' => 'john@example.com',
    ];

    $response = $dukpay->orders()->createTurkeyOrder($orderParams);
    
    // Redirect user to payment URL
    header('Location: ' . $response['payment_url']);
    
} catch (DukPayException $e) {
    echo "Error: " . $e->getMessage();
}
```

### 3. Handle Callback

```php
// callback.php
$callbackData = $_POST;

$validCallback = $dukpay->callbacks()->handlePaymentCallback($callbackData);

if ($validCallback === false) {
    $dukpay->callbacks()->sendErrorResponse('Invalid signature');
} else {
    // Process the callback
    $orderId = $validCallback['order_id'];
    $status = $validCallback['status'];
    
    // Update your database
    updateOrderStatus($orderId, $status);
    
    // Send success response
    $dukpay->callbacks()->sendSuccessResponse();
}
```

## Features

### Payment Orders (代收订单)

Create payment orders for various countries:

- **Turkey (土耳其)**: `createTurkeyOrder()`
- **Indonesia (印尼)**: `createIndonesiaOrder()`
- **Brazil (巴西)**: `createBrazilOrder()`
- **Philippines (菲律宾)**: `createPhilippinesOrder()`
- **Mexico (墨西哥)**: `createMexicoOrder()`
- **Russia (俄罗斯)**: `createRussiaOrder()`
- **Belarus (白俄罗斯)**: `createBelarusOrder()`
- **Vietnam (越南)**: `createVietnamOrder()`
- **Pakistan (巴基斯坦)**: `createPakistanOrder()`
- **South Korea (韩国)**: `createKoreaOrder()`
- **Colombia (哥伦比亚)**: `createColombiaOrder()`
- **Thailand (泰国)**: `createThailandOrder()`
- **Saudi Arabia (沙特)**: `createSaudiOrder()`
- **Chile (智利)**: `createChileOrder()`
- **Singapore (新加坡)**: `createSingaporeOrder()`
- **Tanzania (坦桑尼亚)**: `createTanzaniaOrder()`
- **Kenya (肯尼亚)**: `createKenyaOrder()`
- **Uganda (乌干达)**: `createUgandaOrder()`
- **Egypt (埃及)**: `createEgyptOrder()`
- **Kazakhstan (哈萨克斯坦)**: `createKazakhstanOrder()`
- **Nigeria (尼日利亚)**: `createNigeriaOrder()`
- **Ghana (加纳)**: `createGhanaOrder()`
- **South Africa (南非)**: `createSouthAfricaOrder()`
- **Argentina (阿根廷)**: `createArgentinaOrder()`
- **Peru (秘鲁)**: `createPeruOrder()`
- **Aggregated Cashier (聚合收银台)**: `createAggregatedOrder()`

### Payout Orders (代付订单)

Create payout orders for various countries:

- **South Korea**: `createKoreaPayout()`
- **Thailand**: `createThailandPayout()`
- **Pakistan**: `createPakistanPayout()`
- **Vietnam**: `createVietnamPayout()`
- **Mexico**: `createMexicoPayout()`
- **UAE**: `createUAEPayout()`
- **Philippines & Indonesia**: `createPhilippinesIndonesiaPayout()`
- **Brazil**: `createBrazilPayout()`
- **Russia**: `createRussiaPayout()`
- **Kazakhstan**: `createKazakhstanPayout()`

### Query Operations

```php
// Query order by merchant order ID
$response = $dukpay->orders()->queryByMerchantOrderId('ORDER_123');

// Query order by DukPay order ID
$response = $dukpay->orders()->queryByOrderId('DP_123');

// Query payout order
$response = $dukpay->payouts()->queryPayout('PAYOUT_123');

// Query account balance
$response = $dukpay->balance()->queryBalance();

// Query balance for specific currency
$response = $dukpay->balance()->queryBalance('USD');
```

### Refunds

```php
$refundParams = [
    'order_id' => 'DP_1234567890',
    'refund_amount' => 50.00,
    'refund_reason' => 'Customer requested refund',
];

$response = $dukpay->refunds()->createRefund($refundParams);

// Query refund status
$response = $dukpay->refunds()->queryRefund('REFUND_123');
```

### Account Authorization (授权账户)

```php
// Create account for Russia
$accountParams = [
    'account_number' => '1234567890',
    'account_name' => 'John Doe',
    'bank_code' => '044525225',
];

$response = $dukpay->accounts()->createRussiaAccount($accountParams);

// Query account status
$response = $dukpay->accounts()->queryAccount('ACCOUNT_123');
```

### Subscriptions (订阅收款)

```php
// Create subscription for Russia
$subscriptionParams = [
    'order_id' => 'SUB_' . time(),
    'amount' => 100.00,
    'currency' => 'RUB',
    'account_id' => 'ACCOUNT_123',
    'subscription_plan' => 'monthly',
];

$response = $dukpay->subscriptions()->createRussiaSubscription($subscriptionParams);

// Cancel subscription
$response = $dukpay->subscriptions()->cancelSubscription('SUB_123');

// Query subscription
$response = $dukpay->subscriptions()->querySubscription('SUB_123');

// Switch subscription plan
$response = $dukpay->subscriptions()->switchSubscription([
    'subscription_id' => 'SUB_123',
    'new_plan' => 'yearly',
]);
```

### Callback Handling

The SDK provides methods to handle various types of callbacks:

```php
// Payment callback (代收回调)
$callback = $dukpay->callbacks()->handlePaymentCallback($_POST);

// Refund callback (退款回调)
$callback = $dukpay->callbacks()->handleRefundCallback($_POST);

// Payout callback (代付回调)
$callback = $dukpay->callbacks()->handlePayoutCallback($_POST);

// Chargeback callback (拒付回调)
$callback = $dukpay->callbacks()->handleChargebackCallback($_POST);

// Subscription callback (订阅回调)
$callback = $dukpay->callbacks()->handleSubscriptionCallback($_POST);

// Account status callback (账户状态回调)
$callback = $dukpay->callbacks()->handleAccountCallback($_POST);
```

## Error Handling

All methods throw `DukPayException` on errors:

```php
use DukPay\DukPayException;

try {
    $response = $dukpay->orders()->createTurkeyOrder($params);
} catch (DukPayException $e) {
    echo "Error: " . $e->getMessage();
    echo "Error Code: " . $e->getErrorCode();
}
```

## Security

The SDK automatically:
- Generates signatures for all API requests
- Verifies signatures for all callbacks
- Uses secure HTTP connections (HTTPS)

Always verify callback signatures before processing:

```php
$callback = $dukpay->callbacks()->handlePaymentCallback($_POST);
if ($callback === false) {
    // Invalid signature - reject the callback
    $dukpay->callbacks()->sendErrorResponse();
    exit;
}
```

## API Documentation

For detailed API documentation, please refer to:
http://api.dukpay.com/project/1mrm8ZGOtDU/1mrm8bCBc6i

## Payment Form

A ready-to-use payment form is included at `payment-form.php`. This form allows users to:

- Enter customer information (name, email, phone)
- Select payment amount and currency
- Choose country
- Submit payment directly

The form automatically:
- Creates the payment order
- Redirects to DukPay payment page
- Handles callbacks
- Shows success/error pages

### Using the Payment Form

1. Configure your credentials in `config.sample.php` (copy to `config.php`)
2. Update the return URLs in the config file:
   ```php
   'return_url' => 'https://yoursite.com/payment-success.php',
   'notify_url' => 'https://yoursite.com/payment-callback.php',
   ```
3. Access `payment-form.php` in your browser
4. Users can enter payment information and proceed

### Files Included

- **`payment-form.php`** - Main payment form for users to enter payment information
- **`payment-callback.php`** - Handles callbacks from DukPay (webhook handler)
- **`payment-success.php`** - Success page shown after successful payment
- **`payment-error.php`** - Error page shown when payment fails

## Technical Details

The SDK uses cURL for HTTP requests (no external dependencies required). All methods include error handling and signature verification for security.

## Support

For issues and questions, please contact DukPay support or refer to the official documentation.

## License

MIT License

