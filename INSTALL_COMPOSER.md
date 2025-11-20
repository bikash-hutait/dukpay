# Installing Composer

Composer is not installed on your system. Here's how to install it:

## For Windows

### Option 1: Download Composer-Setup.exe (Recommended)

1. Download Composer-Setup.exe from: https://getcomposer.org/download/
2. Run the installer
3. It will automatically detect your PHP installation
4. Follow the installation wizard

After installation, restart your terminal and run:
```bash
composer install
```

### Option 2: Manual Installation

1. Download `composer.phar` from: https://getcomposer.org/download/
2. Place it in your project directory
3. Run:
```bash
php composer.phar install
```

### Option 3: Install PHP First

If PHP is not installed:

1. Download PHP from: https://windows.php.net/download/
2. Add PHP to your system PATH
3. Then install Composer using Option 1 or 2

## Verify Installation

After installation, verify it works:
```bash
composer --version
```

## Run Installation

Once Composer is installed, run:
```bash
composer install
```

This will install all dependencies defined in `composer.json` into the `vendor/` directory.

## Alternative: Manual Autoloading

Since this project only requires PHP (no external dependencies), you can also create a simple autoloader if needed. However, Composer is recommended for proper PSR-4 autoloading.

