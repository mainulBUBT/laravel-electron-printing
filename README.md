# Laravel Electron Printing

[![Latest Version on Packagist](https://img.shields.io/packagist/v/6amtech/laravel-electron-printing.svg?style=flat-square)](https://packagist.org/packages/6amtech/laravel-electron-printing)
[![Total Downloads](https://img.shields.io/packagist/dt/6amtech/laravel-electron-printing.svg?style=flat-square)](https://packagist.org/packages/6amtech/laravel-electron-printing)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mainulBUBT/laravel-electron-printing/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mainulBUBT/laravel-electron-printing/actions)
[![License](https://img.shields.io/github/license/mainulBUBT/laravel-electron-printing.svg?style=flat-square)](LICENSE.md)

🖨️ **Plug & Play** silent background printing for Laravel. Print HTML, Blade views, URLs, and PDFs without user interaction.

Perfect for **POS**, **restaurants**, **e-commerce**, and any app requiring automated printing.

> 📖 **[View Complete Documentation](DOCUMENTATION.md)** - Installation, Usage, Publishing Guide, Contributing & More

---

## ⚡ Quick Start

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

## 📦 Installation

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

## 🚀 Usage

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

Configure in `config/electron-printing.php`:

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

---

## ⚙️ Configuration

### Profiles (config/electron-printing.php)

```php
'profiles' => [
    'thermal_80mm' => [
        'pageSize' => ['width' => 80000, 'height' => 297000],
        'marginsType' => 1,
        'printBackground' => true,
    ],
    // Add your own profiles...
],
```

### Custom Profile

```php
'profiles' => [
    'my_custom' => [
        'pageSize' => ['width' => 100000, 'height' => 150000],
        'marginsType' => 0,
        'landscape' => true,
    ],
],
```

Then use:
```php
ElectronPrint::printView('view', $data, null, 'my_custom');
```

---

## 📋 Complete Example

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

That's it! No complex options, just use profiles.

---

## 🌐 Network Setup

Print from any device on your network:

**1. Edit `electron-print-service/config.json`:**
```json
{
  "listenIP": "0.0.0.0"
}
```

**2. Update Laravel `.env`:**
```env
PRINT_SERVICE_URL=http://192.168.1.100:3000
```

---

## ⚡ WebSocket (Real-time)

**1. Enable in `.env`:**
```env
PRINT_USE_WEBSOCKET=true
BROADCAST_DRIVER=pusher
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
```

**2. Start Laravel WebSockets:**
```bash
php artisan websockets:serve
```

**3. Configure Electron:**
```json
{
  "websocket": {
    "enabled": true,
    "host": "ws://127.0.0.1:6001"
  }
}
```

**4. Broadcast:**
```php
use LaravelElectronPrinting\Events\PrintJobCreated;

event(new PrintJobCreated($html, $printer, $options));
```

---

## 🧪 Testing

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

## 🔍 Troubleshooting

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

## 📚 API Reference

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

## 🎯 Features

✅ **Plug & Play** - Install and use immediately  
✅ **Profiles** - Pre-configured print settings  
✅ **Multi-format** - HTML, Blade, URL, PDF  
✅ **Network** - Print from any device  
✅ **WebSocket** - Real-time printing  
✅ **Thermal** - 80mm, 58mm receipts  
✅ **A4** - Standard documents  
✅ **Cross-platform** - Windows, macOS, Linux  

---

## 📄 License

MIT License - Free to use

---

## 🆘 Support

- **Issues**: [GitHub Issues](https://github.com/mainulBUBT/laravel-electron-printing/issues)
- **Documentation**: [Complete Guide](DOCUMENTATION.md)
- **Email**: mainulislam3057@gmail.com
- **GitHub**: [@mainulBUBT](https://github.com/mainulBUBT)

---

**Made with ❤️ by Mainul Islam for Laravel developers**
