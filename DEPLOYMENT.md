# Deployment Guide

This guide will help you deploy the DukPay payment form to `https://www.american-software.net/dpay`.

## Prerequisites

1. PHP >= 7.4 installed on your server
2. cURL extension enabled
3. Composer installed
4. DukPay API credentials (API Key and Merchant ID)

## Deployment Steps

### 1. Upload Files

Upload all files to your server at: `https://www.american-software.net/dpay/`

Required files:
- All files in `src/` directory
- `payment-form.php`
- `payment-callback.php`
- `payment-success.php`
- `payment-error.php`
- `config.sample.php`
- `composer.json`
- `.gitignore`

### 2. Install Dependencies

SSH into your server and navigate to the deployment directory:

```bash
cd /path/to/www.american-software.net/dpay
composer install --no-dev --optimize-autoloader
```

Or if you've already run `composer install` locally, upload the `vendor/` folder as well.

### 3. Configure the Application

Copy the example config file and update it with your credentials:

```bash
cp config.sample.php config.php
```

Edit `config.php` and update:

```php
<?php
return [
    // API Credentials - Get these from DukPay
    'api_key' => 'YOUR_ACTUAL_API_KEY',
    'merchant_id' => 'YOUR_ACTUAL_MERCHANT_ID',
    
    // Environment - Set to false for production
    'sandbox' => false, // Set to false for production
    
    // Callback URLs (already configured for your domain)
    'return_url' => 'https://www.american-software.net/dpay/payment-success.php',
    'notify_url' => 'https://www.american-software.net/dpay/payment-callback.php',
    
    // Default currency
    'default_currency' => 'USD',
    
    // Timeout settings
    'timeout' => 30,
];
```

### 4. Set Permissions

Ensure PHP can write to necessary directories (if any):

```bash
chmod 644 config.php
chmod 755 payment-form.php payment-callback.php payment-success.php payment-error.php
```

### 5. Secure the Config File

Add to your `.htaccess` (if using Apache) to protect `config.php`:

```apache
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>
```

### 6. Test the Installation

1. Visit: `https://www.american-software.net/dpay/payment-form.php`
2. Fill out the form with test data
3. Verify that payment redirects work correctly
4. Test the callback URL is accessible

### 7. Verify Callback URL is Public

Ensure `payment-callback.php` is accessible from the internet. DukPay needs to send webhooks to:
- `https://www.american-software.net/dpay/payment-callback.php`

## File Structure on Server

```
/dpay/
├── src/
│   ├── Service/
│   │   ├── AccountService.php
│   │   ├── BalanceService.php
│   │   ├── OrderService.php
│   │   ├── PayoutService.php
│   │   ├── RefundService.php
│   │   └── SubscriptionService.php
│   ├── CallbackHandler.php
│   ├── DukPay.php
│   ├── DukPayClient.php
│   └── DukPayException.php
├── vendor/ (composer dependencies)
├── config.php (create from config.example.php)
├── payment-form.php
├── payment-callback.php
├── payment-success.php
├── payment-error.php
├── composer.json
└── .gitignore
```

## URLs

After deployment, your payment form will be available at:
- **Payment Form**: `https://www.american-software.net/dpay/payment-form.php`
- **Success Page**: `https://www.american-software.net/dpay/payment-success.php`
- **Error Page**: `https://www.american-software.net/dpay/payment-error.php`
- **Callback Handler**: `https://www.american-software.net/dpay/payment-callback.php` (webhook)

## Important Notes

1. **Never commit `config.php`** - It contains sensitive credentials
2. **Set `sandbox => false`** in production
3. **Ensure HTTPS** - Required for secure payment processing
4. **Test in sandbox mode first** before going live
5. **Monitor logs** - Check server logs for any errors

## Troubleshooting

### Callback Not Working
- Verify the callback URL is publicly accessible
- Check that `payment-callback.php` exists and has correct permissions
- Verify signature verification in callback handler

### 500 Errors
- Check PHP error logs
- Verify all dependencies are installed (`composer install`)
- Ensure PHP version >= 7.4

### SSL Certificate Issues
- Ensure your domain has a valid SSL certificate
- DukPay requires HTTPS for callbacks

## Support

For issues with the DukPay API, refer to:
http://api.dukpay.com/project/1mrm8ZGOtDU/1mrm8bCBc6i

