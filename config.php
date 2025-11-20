<?php

/**
 * DukPay Configuration Sample
 * 
 * Copy this file to config.php and fill in your actual credentials
 */

return [
    // API Credentials
    'api_key' => 'YOUR_API_KEY_HERE',
    'merchant_id' => 'YOUR_MERCHANT_ID_HERE',
    
    // Environment
    'sandbox' => true, // Set to false for production
    
    // Base URL (optional, will use default if not set)
    'base_url' => null,
    
    // Callback URLs - Update with your production URLs
    'return_url' => 'https://www.american-software.net/dpay/payment-success.php',
    'notify_url' => 'https://www.american-software.net/dpay/payment-callback.php',
    
    // Default currency
    'default_currency' => 'USD',
    
    // Timeout settings
    'timeout' => 30, // Request timeout in seconds
];

