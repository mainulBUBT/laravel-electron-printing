# Laravel Electron Printing - Complete Documentation

**Version:** 1.0.0  
**Author:** Mainul Islam  
**GitHub:** https://github.com/mainulBUBT/laravel-electron-printing  
**Packagist:** https://packagist.org/packages/6amtech/laravel-electron-printing

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Configuration](#configuration)
5. [Publishing to Packagist](#publishing-to-packagist)
6. [Contributing](#contributing)
7. [Changelog](#changelog)
8. [Support](#support)

---

## Quick Start

```bash
# 1. Install
composer require 6amtech/laravel-electron-printing

# 2. Setup
php artisan electron-printing:install

# 3. Configure .env
PRINT_SERVICE_ENABLED=true
PRINT_SERVICE_URL=http://localhost:3000

# 4. Start service
cd electron-print-service && npm install && npm start

# 5. Print!
ElectronPrint::printView('invoice', ['order' => $order], null, 'thermal_80mm');
```

---

## Installation

### Step 1: Install Package

```bash
composer require 6amtech/laravel-electron-printing
php artisan electron-printing:install
```

### Step 2: Add to `.env`

```env
PRINT_SERVICE_ENABLED=true
PRINT_SERVICE_URL=http://localhost:3000
PRINT_DEFAULT_PRINTER=
```

### Step 3: Start Electron Service

```bash
cd electron-print-service
npm install
npm start
```

---

## Usage

### Simple - Use Profiles

```php
use LaravelElectronPrinting\Facades\ElectronPrint;

// Thermal 80mm receipt
ElectronPrint::printView('invoices.receipt', ['order' => $order], null, 'thermal_80mm');

// A4 document
ElectronPrint::printView('invoices.a4', ['order' => $order], null, 'a4');

// PDF
ElectronPrint::printPdfFile(storage_path('app/invoice.pdf'), null, 'a4');
```

### Available Profiles

- `thermal_80mm` - 80mm thermal printer
- `thermal_58mm` - 58mm thermal printer
- `a4` - A4 portrait
- `a4_landscape` - A4 landscape
- `letter` - US Letter
- `label` - Label printer

### All Methods

```php
// Print HTML
ElectronPrint::printHtml('<h1>Invoice</h1>');

// Print view with profile
ElectronPrint::printView('invoice', $data, null, 'thermal_80mm');

// Print URL
ElectronPrint::printUrl(route('invoice', $id));

// Print PDF
ElectronPrint::printPdfFile($path);

// Get printers
$printers = ElectronPrint::getPrinters();
```

### Complete Example

```php
namespace App\Http\Controllers;

use LaravelElectronPrinting\Facades\ElectronPrint;

class OrderController extends Controller
{
    public function printInvoice($id)
    {
        $order = Order::with('customer', 'items')->findOrFail($id);
        
        $result = ElectronPrint::printView(
            'invoices.thermal',
            ['order' => $order],
            null,              // Use default printer
            'thermal_80mm'     // Use profile
        );
        
        return response()->json($result);
    }
}
```

---

## Configuration

### Print Profiles

Edit `config/electron-printing.php`:

```php
'profiles' => [
    'thermal_80mm' => [
        'pageSize' => ['width' => 80000, 'height' => 297000],
        'marginsType' => 1,
        'printBackground' => true,
    ],
    // Add custom profiles...
],
```

### Network Setup

Print from any device on your network:

**1. Edit `electron-print-service/config.json`:**
```json
{
  "listenIP": "0.0.0.0",
  "port": 3000
}
```

**2. Update Laravel `.env`:**
```env
PRINT_SERVICE_URL=http://192.168.1.100:3000
```

### Large PDF/Payload Support

For printing large PDFs or high-resolution images, increase the payload size limit:

**Add to `.env`:**
```env
PRINT_MAX_PAYLOAD_SIZE=100  # Size in MB (default: 50MB)
```

The Laravel package will automatically sync this configuration with the Electron service. Restart the Electron app after changing this value.

**Note:** Increase this value if you encounter `413 Payload Too Large` errors when printing large files.

### WebSocket Mode (Real-time)

**1. Enable in `.env`:**
```env
PRINT_USE_WEBSOCKET=true
BROADCAST_DRIVER=pusher
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
```

**2. Configure Electron:**
```json
{
  "websocket": {
    "enabled": true,
    "host": "ws://127.0.0.1:6001"
  }
}
```

---

## Publishing to Packagist

### Prerequisites

- GitHub account
- Packagist account (https://packagist.org)
- Git installed
- Package tested and working

### Step 1: Push to GitHub

```bash
cd /Applications/MAMP/htdocs/Backend-Mart/packages/6amtech/laravel-electron-printing

# Initialize git
git init
git add .
git commit -m "Initial release v1.0.0"
git branch -M main

# Add remote
git remote add origin https://github.com/mainulBUBT/laravel-electron-printing.git

# Push
git push -u origin main

# Create release tag
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

### Step 2: Create GitHub Release

1. Go to: https://github.com/mainulBUBT/laravel-electron-printing
2. Click "Releases" → "Create a new release"
3. Choose tag: `v1.0.0`
4. Release title: `v1.0.0 - Initial Release`
5. Copy description from CHANGELOG section below
6. Click "Publish release"

### Step 3: Submit to Packagist

1. Go to https://packagist.org
2. Login with GitHub
3. Click "Submit"
4. Enter: `https://github.com/mainulBUBT/laravel-electron-printing`
5. Click "Check" then "Submit"

### Step 4: Setup Auto-Update Webhook

1. On Packagist, go to your package settings
2. Copy the webhook URL
3. Go to GitHub repo → Settings → Webhooks → Add webhook
4. Paste webhook URL
5. Content type: `application/json`
6. Select "Just the push event"
7. Click "Add webhook"

### Releasing Updates

**Bug Fixes (Patch: 1.0.x):**
```bash
git add .
git commit -m "Fix: Description"
git tag -a v1.0.1 -m "Bug fix release"
git push origin main
git push origin v1.0.1
```

**New Features (Minor: 1.x.0):**
```bash
git add .
git commit -m "Feature: Description"
git tag -a v1.1.0 -m "Feature release"
git push origin main
git push origin v1.1.0
```

---

## Contributing

### How to Contribute

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Make your changes**
4. **Test thoroughly**
5. **Commit** (`git commit -m 'Add amazing feature'`)
6. **Push** (`git push origin feature/amazing-feature`)
7. **Open a Pull Request**

### Coding Standards

- Follow PSR-12 coding standards
- Write clear commit messages
- Add comments for complex logic
- Update documentation
- Ensure backward compatibility

### Development Setup

```bash
# Clone your fork
git clone https://github.com/YOUR_USERNAME/laravel-electron-printing.git
cd laravel-electron-printing

# Install dependencies
composer install
cd electron-app && npm install

# Run tests
composer test

# Start Electron service
cd electron-app && npm start
```

### Reporting Bugs

Create an issue with:
- Clear title and description
- Steps to reproduce
- Expected vs actual behavior
- Environment details (OS, PHP, Laravel version)
- Logs or screenshots

### Code of Conduct

- Be respectful and inclusive
- Welcome newcomers
- Focus on constructive feedback
- Help others learn and grow

---

## Changelog

### [1.0.0] - 2025-01-19

#### Added
- 🎉 Initial release
- Silent background printing for Laravel
- Electron-based print service
- Support for HTML, Blade views, URLs, and PDFs
- Pre-configured print profiles (thermal 80mm, 58mm, A4, Letter, etc.)
- Network printing support
- WebSocket support (Laravel Reverb/Pusher)
- HTTP mode for direct connection
- Modern Electron UI
- Auto-detection of available printers
- Bluetooth printer support (Rongta RPP-300, etc.)
- Cross-platform (Windows, macOS, Linux)
- Artisan commands (`electron-printing:install`, `electron-printing:test`)
- Health check endpoint
- Comprehensive documentation

#### Features
- ✅ Plug & Play installation
- ✅ Profile-based printing
- ✅ Multi-format support
- ✅ Network printing
- ✅ Real-time WebSocket printing
- ✅ Thermal printer support
- ✅ Standard document printing
- ✅ PDF printing
- ✅ Blade view rendering
- ✅ Custom print options

---

## Troubleshooting

### Service Not Running
```bash
curl http://localhost:3000/health
```

### Port in Use
```bash
lsof -i :3000
kill -9 <PID>
```

### Package Not Found
```bash
composer dump-autoload
php artisan config:clear
```

---

## Testing

```bash
# Test command
php artisan electron-printing:test

# Test in tinker
php artisan tinker
>>> ElectronPrint::printHtml('<h1>Test</h1>');

# Check health
>>> ElectronPrint::isHealthy();

# Get printers
>>> ElectronPrint::getPrinters();
```

---

## API Reference

```php
// Print with profile
ElectronPrint::printView(string $view, array $data, ?string $printer, string $profile)

// Print with custom options
ElectronPrint::printView(string $view, array $data, ?string $printer, array $options)

// Print HTML
ElectronPrint::printHtml(string $html, ?string $printer, string|array $options)

// Print URL
ElectronPrint::printUrl(string $url, ?string $printer, string|array $options)

// Print PDF
ElectronPrint::printPdfUrl(string $url, ?string $printer, string|array $options)
ElectronPrint::printPdfFile(string $path, ?string $printer, string|array $options)

// Utilities
ElectronPrint::getPrinters(): array
ElectronPrint::isHealthy(): bool
```

---

## Features

✅ **Plug & Play** - Install and use immediately  
✅ **Profiles** - Pre-configured print settings  
✅ **Multi-format** - HTML, Blade, URL, PDF  
✅ **Network** - Print from any device  
✅ **WebSocket** - Real-time printing  
✅ **Thermal** - 80mm, 58mm receipts  
✅ **A4** - Standard documents  
✅ **Cross-platform** - Windows, macOS, Linux  
✅ **Bluetooth** - Supports Bluetooth printers  

---

## Support

- **GitHub Issues**: https://github.com/mainulBUBT/laravel-electron-printing/issues
- **Documentation**: https://github.com/mainulBUBT/laravel-electron-printing#readme
- **Email**: mainulislam3057@gmail.com
- **Packagist**: https://packagist.org/packages/6amtech/laravel-electron-printing

---

## License

MIT License - Free to use

Copyright (c) 2025 Mainul Islam

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

---

**Made with ❤️ by Mainul Islam for Laravel developers**
