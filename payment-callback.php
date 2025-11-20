<?php
/**
 * DukPay Payment Callback Handler
 * 
 * This file handles callbacks from DukPay after payment
 */
require_once __DIR__ . '/vendor/autoload.php';

use DukPay\DukPay;

// Load configuration
$config = require __DIR__ . '/config.php';

// Initialize DukPay
$dukpay = new DukPay(
    $config['api_key'],
    $config['merchant_id'],
    $config['sandbox']
);

// Get callback data (usually from POST)
$callbackData = $_POST;

// Log the callback (for debugging)
error_log('DukPay Callback Received: ' . json_encode($callbackData));

// Handle payment callback
$validCallback = $dukpay->callbacks()->handlePaymentCallback($callbackData);

if ($validCallback === false) {
    // Invalid signature - log and reject
    error_log('DukPay Callback: Invalid signature');
    $dukpay->callbacks()->sendErrorResponse('Invalid signature');
} else {
    // Valid callback - process the payment
    
    $orderId = $validCallback['order_id'] ?? null;
    $merchantOrderId = $validCallback['merchant_order_id'] ?? null;
    $status = $validCallback['status'] ?? null;
    $amount = $validCallback['amount'] ?? null;
    $currency = $validCallback['currency'] ?? null;
    
    // Log callback details
    error_log("Payment Callback - Order: {$merchantOrderId}, Status: {$status}, Amount: {$amount} {$currency}");
    
    // TODO: Update your database with the payment status
    // Example:
    // updateOrderStatus($merchantOrderId, $status);
    // if ($status === 'success' || $status === 'paid') {
    //     activateService($merchantOrderId);
    //     sendConfirmationEmail($merchantOrderId);
    // }
    
    // Send success response to DukPay
    $dukpay->callbacks()->sendSuccessResponse();
}

