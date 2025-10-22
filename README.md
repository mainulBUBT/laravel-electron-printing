# Laravel Electron Printing

[![Latest Version on Packagist](https://img.shields.io/packagist/v/6amtech/laravel-electron-printing.svg?style=flat-square)](https://packagist.org/packages/6amtech/laravel-electron-printing)
[![Total Downloads](https://img.shields.io/packagist/dt/6amtech/laravel-electron-printing.svg?style=flat-square)](https://packagist.org/packages/6amtech/laravel-electron-printing)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/mainulBUBT/laravel-electron-printing/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/mainulBUBT/laravel-electron-printing/actions)
[![License](https://img.shields.io/github/license/mainulBUBT/laravel-electron-printing.svg?style=flat-square)](LICENSE.md)

🖨️ **Silent background printing for Laravel** - Print HTML, Blade views, URLs, and PDFs directly to any printer without user interaction.

Perfect for **POS systems**, **restaurants**, **warehouses**, **e-commerce**, and any application requiring automated printing.

---

## ⚡ Quick Start

```bash
# 1. Install package
composer require 6amtech/laravel-electron-printing

# 2. Run install command
php artisan electron-printing:install

# 3. Configure .env
PRINT_SERVICE_ENABLED=true
PRINT_SERVICE_URL=http://localhost:3000
PRINT_USE_WEBSOCKET=false
PRINT_BROADCAST_CHANNEL=printing

# 4. Start Electron service
cd electron-print-service
npm install
npm start
```

**Print in your Laravel app:**
```php
use LaravelElectronPrinting\Facades\ElectronPrint;

ElectronPrint::printView('receipt', ['order' => $order], null, 'thermal_80mm');
```

---

## 📦 Installation

### Step 1: Install Package

```bash
composer require 6amtech/laravel-electron-printing
```

### Step 2: Run Install Command

```bash
php artisan electron-printing:install
```

This command will:
- Publish the config file to `config/electron-printing.php`
- Publish the Electron app to `electron-print-service/`
- Show you the required `.env` configuration

### Step 3: Configure Environment

Add these to your `.env` file:

```env
PRINT_SERVICE_ENABLED=true
PRINT_SERVICE_URL=http://localhost:3000
PRINT_USE_WEBSOCKET=false
PRINT_BROADCAST_CHANNEL=printing
PRINT_MAX_PAYLOAD_SIZE=50
```

### Step 4: Install Printer Drivers

**⚠️ Important:** You must install the genuine printer drivers on the machine running the Electron service.

- **Thermal Printers:** Install manufacturer drivers (EPSON, Star, etc.)
- **Standard Printers:** Install from manufacturer website
- **Network Printers:** Configure network connection first

### Step 5: Start the Electron Service

#### Option A: Run from Source (Development)

```bash
cd electron-print-service
npm install
npm start
```

The service will:
- Start on `http://localhost:3000`
- Run in the background with a system tray icon
- Auto-start on system boot (optional)

#### Option B: Build Standalone App (Production)

If you want to distribute a ready-to-use application without requiring Node.js:

**Build for your platform:**

```bash
cd electron-print-service

# For macOS
npm run build:mac

# For Windows
npm run build:win

# For Linux
npm run build:linux

# For all platforms (requires macOS)
npm run build:all
```

**Built apps will be in:** `electron-print-service/dist/`

**Installation:**

- **macOS:** 
  - Open `6amTech Printing Service-1.0.0-mac.dmg`
  - Drag app to Applications folder
  - Open from Applications (right-click → Open first time)
  
- **Windows:**
  - Run `6amTech Printing Service Setup 1.0.0.exe`
  - Follow installer wizard
  - App will auto-start on system boot
  
- **Linux:**
  - Install `.deb`: `sudo dpkg -i 6amTech-Printing-Service-1.0.0.deb`
  - Or run `.AppImage` directly (no installation needed)

**Note:** Built apps don't require Node.js or npm to be installed on the target machine.

#### Remote/Network Setup (Different Machine)

**On the machine with printers connected:**

1. **Edit config** (`electron-print-service/config.json`):
```json
{
  "listenIP": "0.0.0.0",
  "port": 3000
}
```

2. **Start the service:**
```bash
cd electron-print-service
npm install
npm start
```

3. **Note the machine's IP address:**
```bash
# Windows
ipconfig

# macOS/Linux
ifconfig
# or
ip addr show
```

**On your Laravel server:**

Update `.env` with the remote machine's IP:
```env
PRINT_SERVICE_URL=http://192.168.1.100:3000
```

**Firewall Configuration:**
- Allow incoming connections on port 3000
- Windows: `Windows Defender Firewall → Allow an app`
- macOS: `System Preferences → Security & Privacy → Firewall`
- Linux: `sudo ufw allow 3000`

---

## 🚀 Usage

### Basic Printing

```php
use LaravelElectronPrinting\Facades\ElectronPrint;

// Print HTML
ElectronPrint::printHtml('<h1>Hello World</h1>');

// Print Blade view
ElectronPrint::printView('receipt', ['order' => $order]);

// Print to specific printer
ElectronPrint::printView('receipt', $data, 'EPSON TM-T88V');
```

### Using Print Profiles

Pre-configured profiles in `config/electron-printing.php`:

```php
// Thermal 80mm receipt
ElectronPrint::printView('receipt', $data, null, 'thermal_80mm');

// Thermal 58mm receipt
ElectronPrint::printView('receipt', $data, null, 'thermal_58mm');

// A4 document
ElectronPrint::printView('invoice', $data, null, 'a4');

// A4 landscape
ElectronPrint::printView('report', $data, null, 'a4_landscape');

// Label printer
ElectronPrint::printView('label', $data, null, 'label');
```

### Custom Print Options

```php
ElectronPrint::printView('receipt', $data, null, [
    'pageSize' => ['width' => 80000, 'height' => 297000],
    'marginsType' => 1,
    'printBackground' => true,
    'scaleFactor' => 100
]);
```

### PDF Printing

```php
// From file
ElectronPrint::printPdfFile(storage_path('app/invoice.pdf'));

// From URL
ElectronPrint::printPdfUrl('https://example.com/invoice.pdf');

// From Base64
ElectronPrint::printPdfBase64($base64String);

// With profile
ElectronPrint::printPdfFile(storage_path('app/invoice.pdf'), null, 'a4');
```

### Print from URL

```php
ElectronPrint::printUrl('https://example.com/receipt');
```

### Get Available Printers

```php
$printers = ElectronPrint::getPrinters();
// Returns: ['EPSON TM-T88V', 'HP LaserJet', ...]
```

### Check Service Health

```php
if (ElectronPrint::isHealthy()) {
    // Service is running
}
```

---

## ⚙️ Configuration

### Environment Variables

```env
# Enable/disable printing
PRINT_SERVICE_ENABLED=true

# Electron service URL (local or remote)
PRINT_SERVICE_URL=http://localhost:3000

# Request timeout (seconds)
PRINT_SERVICE_TIMEOUT=30

# Max payload size for large PDFs (MB)
PRINT_MAX_PAYLOAD_SIZE=50

# Default printer (leave empty for system default)
PRINT_DEFAULT_PRINTER=

# WebSocket mode (optional)
PRINT_USE_WEBSOCKET=false
PRINT_BROADCAST_CHANNEL=printing

# Logging
PRINT_LOGGING_ENABLED=true
PRINT_LOG_CHANNEL=daily
```

### Custom Print Profiles

Edit `config/electron-printing.php`:

```php
'profiles' => [
    'my_custom_printer' => [
        'pageSize' => ['width' => 80000, 'height' => 297000],
        'marginsType' => 1,
        'printBackground' => true,
        'scaleFactor' => 100,
    ],
],
```

Use it:
```php
ElectronPrint::printView('receipt', $data, null, 'my_custom_printer');
```

### Electron Service Configuration

Edit `electron-print-service/config.json`:

```json
{
  "port": 3000,
  "listenIP": "0.0.0.0",
  "maxPayloadSize": 50,
  "websocket": {
    "enabled": false,
    "host": "https://your-domain.com",
    "auth": {}
  }
}
```

---

## 🌐 Network Printing Setup

### Scenario 1: Laravel + Electron on Same Machine

```env
PRINT_SERVICE_URL=http://localhost:3000
```

### Scenario 2: Laravel on Server, Electron on Workstation

**Workstation (with printers):**
- IP: `192.168.1.100`
- Edit `electron-print-service/config.json`:
  ```json
  { "listenIP": "0.0.0.0", "port": 3000 }
  ```
- Start service: `npm start`

**Laravel Server:**
```env
PRINT_SERVICE_URL=http://192.168.1.100:3000
```

### Scenario 3: Multiple Workstations

Each workstation runs its own Electron service:

**Workstation 1 (Kitchen):**
```json
{ "listenIP": "0.0.0.0", "port": 3000 }
```

**Workstation 2 (Counter):**
```json
{ "listenIP": "0.0.0.0", "port": 3001 }
```

**Laravel:**
```php
// Kitchen printer
ElectronPrint::printView('kitchen-order', $data, 'Kitchen Printer', 'thermal_80mm');

// Counter printer (different service)
$counterService = new ElectronPrintService('http://192.168.1.101:3001');
$counterService->printView('receipt', $data, 'Receipt Printer', 'thermal_80mm');
```

---

## 📚 API Reference

```php
use LaravelElectronPrinting\Facades\ElectronPrint;

// Print methods
ElectronPrint::printHtml($html, $printer, $options)
ElectronPrint::printView($view, $data, $printer, $options)
ElectronPrint::printUrl($url, $printer, $options)
ElectronPrint::printPdfFile($path, $printer, $options)
ElectronPrint::printPdfUrl($url, $printer, $options)
ElectronPrint::printPdfBase64($base64, $printer, $options)

// Utility
ElectronPrint::getPrinters()  // Returns array of printer names
ElectronPrint::isHealthy()    // Returns true if service running

// All methods return: ['success' => bool, 'message' => string]
```

---

## 🧪 Testing

```bash
# Test the installation
php artisan electron-printing:test

# Run package tests
composer test
```

---

## 🐛 Troubleshooting

### Service Not Connecting

**Error:** `Print service error: Connection refused`

**Solutions:**
1. Check if Electron service is running:
   ```bash
   curl http://localhost:3000/health
   ```
2. Verify `PRINT_SERVICE_URL` in `.env`
3. Check firewall settings
4. For network printing, ensure `listenIP` is `0.0.0.0`

### Printer Not Found

**Error:** Printer name not recognized

**Solutions:**
1. Get available printers:
   ```php
   dd(ElectronPrint::getPrinters());
   ```
2. Use exact printer name from the list
3. Install printer drivers on the machine running Electron service
4. Use `null` for system default printer

### Payload Too Large

**Error:** `413 Payload Too Large`

**Solution:**
```env
PRINT_MAX_PAYLOAD_SIZE=100  # Increase to 100MB
```
Restart both Laravel and Electron service.

### Print Quality Issues

**Solutions:**
1. Use correct profile for your printer type
2. Adjust `scaleFactor` (default: 100)
3. Enable `printBackground: true` for colored content
4. Check printer driver settings

### Port Already in Use

**Error:** `Port 3000 is already in use`

**Solution - Find and kill the process:**

**macOS/Linux:**
```bash
# Find process using port 3000
lsof -ti:3000

# Kill the process
kill -9 $(lsof -ti:3000)

# Or change port in electron-print-service/config.json
```

**Windows:**
```cmd
# Find process using port 3000
netstat -ano | findstr :3000

# Kill process (replace PID with actual process ID)
taskkill /PID <PID> /F

# Or change port in electron-print-service/config.json
```

**Change Port:**
Edit `electron-print-service/config.json`:
```json
{
  "port": 3001,
  "listenIP": "0.0.0.0"
}
```

Then update Laravel `.env`:
```env
PRINT_SERVICE_URL=http://localhost:3001
```

### Network Printing Not Working

**Solutions:**
1. Verify IP address: `ping 192.168.1.100`
2. Check firewall allows port 3000
3. Ensure `listenIP: "0.0.0.0"` in Electron config
4. Test locally first: `http://localhost:3000/health`

### Electron App Won't Open

#### macOS

**Problem:** "App can't be opened because it is from an unidentified developer"

**Solution:**
```bash
# Remove quarantine attribute
xattr -cr "/Applications/6amTech Printing Service.app"

# Or right-click → Open (first time only)
```

**Problem:** App crashes on startup

**Solution:**
1. Check Console.app for error logs
2. Ensure printer drivers are installed
3. Try running from source: `cd electron-print-service && npm start`

#### Windows

**Problem:** "Windows protected your PC" warning

**Solution:**
1. Click "More info"
2. Click "Run anyway"
3. Or: Right-click installer → Properties → Unblock → Apply

**Problem:** App won't start after installation

**Solution:**
1. Check if port 3000 is already in use:
   ```cmd
   netstat -ano | findstr :3000
   ```
2. Run as Administrator
3. Check Windows Defender logs
4. Reinstall with antivirus temporarily disabled

#### Linux

**Problem:** AppImage won't run

**Solution:**
```bash
# Make executable
chmod +x 6amTech-Printing-Service-1.0.0.AppImage

# Run
./6amTech-Printing-Service-1.0.0.AppImage
```

**Problem:** .deb installation fails

**Solution:**
```bash
# Install dependencies
sudo apt-get install -f

# Try reinstall
sudo dpkg -i 6amTech-Printing-Service-1.0.0.deb
```

**Problem:** App runs but no system tray icon

**Solution:**
```bash
# Install system tray support
sudo apt-get install libappindicator3-1

# For GNOME
sudo apt-get install gnome-shell-extension-appindicator
```

---

## 🎯 Features

✅ **Silent Printing** - No print dialogs  
✅ **Multiple Formats** - HTML, Blade, URL, PDF  
✅ **Print Profiles** - Pre-configured settings  
✅ **Network Support** - Print from any device  
✅ **Thermal Printers** - 80mm, 58mm receipts  
✅ **Standard Printers** - A4, Letter, Labels  
✅ **Cross-Platform** - Windows, macOS, Linux  
✅ **Large Files** - Configurable payload size  

---

## 📖 Resources

- **[Complete Documentation](DOCUMENTATION.md)** - Advanced features and guides
- **[GitHub Repository](https://github.com/mainulBUBT/laravel-electron-printing)** - Source code
- **[Packagist](https://packagist.org/packages/6amtech/laravel-electron-printing)** - Package releases

---

## 📝 License

MIT License - see [LICENSE.md](LICENSE.md)

---

## 👨‍💻 Credits

**Developed by:** Mainul Islam  
**Organization:** 6amTech  
**GitHub:** [@mainulBUBT](https://github.com/mainulBUBT)  
**Email:** mainulislam3057@gmail.com

---

## ⭐ Support

If this package helps your project, give it a ⭐ on [GitHub](https://github.com/mainulBUBT/laravel-electron-printing)!

**Made with ❤️ for the Laravel community**
