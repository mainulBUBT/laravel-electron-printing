# Electron Print Service

🖨️ **Silent background printing service** built with Electron for Laravel applications.

This is the desktop application component of the `6amtech/laravel-electron-printing` package.

## 📥 Where to Find This

After installing the Laravel package, this folder is located at:
```
vendor/6amtech/laravel-electron-printing/electron-app/
```

Or download directly from:
- **GitHub**: https://github.com/mainulBUBT/laravel-electron-printing/tree/main/electron-app
- **Packagist**: https://packagist.org/packages/6amtech/laravel-electron-printing

---

## 🚀 Quick Start

```bash
# Navigate to this folder
cd vendor/6amtech/laravel-electron-printing/electron-app

# Install dependencies
npm install

# Start service
npm start

# Test
curl -X POST http://localhost:3000/print \
  -H "Content-Type: application/json" \
  -d '{"html":"<h1>Test</h1>"}'
```

---

## 📄 Supported Formats

| Format | Method | Example |
|--------|--------|---------|
| **HTML** | `POST /print` | `print_html('<h1>Invoice</h1>')` |
| **Blade View** | `POST /print` | `print_view('invoice', $data)` |
| **URL** | `POST /print-url` | `print_url(route('invoice', $id))` |
| **PDF URL** | `POST /print-pdf` | `print_pdf_url('https://example.com/file.pdf')` |
| **PDF File** | `POST /print-pdf` | `print_pdf_file(storage_path('invoice.pdf'))` |
| **PDF Base64** | `POST /print-pdf` | `print_pdf_base64($base64Data)` |

---

## 🔧 Configuration

**Edit `config.json`:**
```json
{
  "port": 3000,
  "listenIP": "0.0.0.0",
  "websocket": {
    "enabled": false,
    "host": "ws://127.0.0.1:6001",
    "channel": "printing",
    "auth": {}
  }
}
```

### Configuration Options

| Option | Description | Default | Example |
|--------|-------------|---------|---------|
| `port` | HTTP server port | `3000` | `3000` |
| `listenIP` | Network interface | `0.0.0.0` | `0.0.0.0` (all), `127.0.0.1` (local only) |
| `websocket.enabled` | Enable WebSocket mode | `false` | `true` or `false` |
| `websocket.host` | Laravel WebSocket URL | `ws://127.0.0.1:6001` | `ws://192.168.1.100:6001` |
| `websocket.channel` | Broadcast channel | `printing` | `printing` |

### Network Printing Setup

**To print from other devices on your network:**

1. **Edit `config.json`:**
```json
{
  "port": 3000,
  "listenIP": "0.0.0.0"  // ← Listen on all network interfaces
}
```

2. **Find your computer's IP address:**
```bash
# macOS/Linux
ifconfig | grep "inet "

# Windows
ipconfig
```

3. **Update Laravel `.env`:**
```env
PRINT_SERVICE_URL=http://192.168.1.100:3000  # ← Use your computer's IP
```

4. **Allow firewall access** (if needed):
   - macOS: System Preferences → Security → Firewall → Allow port 3000
   - Windows: Windows Defender Firewall → Allow an app → Add port 3000

Now any device on your network can send print jobs to this computer!

---

## 📡 Laravel Setup

### .env Configuration
```env
BROADCAST_DRIVER=pusher
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PRINT_SERVICE_ENABLED=true
PRINT_USE_WEBSOCKET=true
```

### Start Laravel WebSockets
```bash
php artisan websockets:serve
```

### Helper Functions (add to `app/helpers.php`)

```php
// Print HTML
function print_html($html, $printer = null, $options = []) {
    $client = new \GuzzleHttp\Client();
    $response = $client->post(env('PRINT_SERVICE_URL', 'http://localhost:3000') . '/print', [
        'json' => ['html' => $html, 'printerName' => $printer, 'options' => $options],
        'timeout' => 10
    ]);
    return json_decode($response->getBody(), true);
}

// Print Blade View
function print_view($view, $data = [], $printer = null) {
    return print_html(view($view, $data)->render(), $printer);
}

// Print URL
function print_url($url, $printer = null) {
    $client = new \GuzzleHttp\Client();
    $response = $client->post(env('PRINT_SERVICE_URL') . '/print-url', [
        'json' => ['url' => $url, 'printerName' => $printer],
        'timeout' => 15
    ]);
    return json_decode($response->getBody(), true);
}

// Print PDF from URL
function print_pdf_url($url, $printer = null) {
    $client = new \GuzzleHttp\Client();
    $response = $client->post(env('PRINT_SERVICE_URL') . '/print-pdf', [
        'json' => ['pdfUrl' => $url, 'printerName' => $printer],
        'timeout' => 20
    ]);
    return json_decode($response->getBody(), true);
}

// Print PDF from file
function print_pdf_file($path, $printer = null) {
    $base64 = base64_encode(file_get_contents($path));
    $client = new \GuzzleHttp\Client();
    $response = $client->post(env('PRINT_SERVICE_URL') . '/print-pdf', [
        'json' => ['pdfBase64' => $base64, 'printerName' => $printer],
        'timeout' => 20
    ]);
    return json_decode($response->getBody(), true);
}

// Get available printers
function get_printers() {
    $client = new \GuzzleHttp\Client();
    $response = $client->get(env('PRINT_SERVICE_URL') . '/printers', ['timeout' => 5]);
    $data = json_decode($response->getBody(), true);
    return $data['printers'] ?? [];
}
```

---

## 🧪 Usage Examples

### Print HTML
```php
print_html('<h1>Order #123</h1><p>Total: $100</p>');
```

### Print Blade View
```php
print_view('admin-views.order.invoice', ['order' => $order]);
```

### Print URL
```php
print_url(route('admin.order.invoice', $orderId));
```

### Print PDF
```php
// From URL
print_pdf_url('https://example.com/invoice.pdf');

// From storage
print_pdf_file(storage_path('app/invoices/invoice-123.pdf'));

// From generated PDF
$pdf = PDF::loadView('invoice', $data);
$base64 = base64_encode($pdf->output());
print_pdf_file($base64); // Will auto-detect base64
```

### Print Order Invoice (Complete)
```php
function print_order_invoice($orderId, $format = 'thermal') {
    $order = Order::with(['customer', 'details'])->findOrFail($orderId);
    
    switch ($format) {
        case 'thermal':
            return print_view('invoices.thermal', ['order' => $order], null, [
                'pageSize' => ['width' => 80000, 'height' => 297000],
                'marginsType' => 1
            ]);
        case 'a4':
            return print_view('invoices.a4', ['order' => $order], null, [
                'pageSize' => 'A4'
            ]);
        case 'pdf':
            $pdf = PDF::loadView('invoices.pdf', ['order' => $order]);
            return print_pdf_file(base64_encode($pdf->output()));
    }
}

// Usage
print_order_invoice(100104, 'thermal');
```

---

## 🖨️ Printer Options

```php
$options = [
    'silent' => true,              // No dialog
    'printBackground' => true,     // Print backgrounds
    'deviceName' => 'Printer Name', // Specific printer
    'pageSize' => 'A4',            // A4, Letter, Legal, or custom
    'marginsType' => 0,            // 0=default, 1=none, 2=min
    'landscape' => false,          // Orientation
    'scaleFactor' => 100,          // Scale %
    'copies' => 1                  // Number of copies
];

// Custom size (microns)
$options['pageSize'] = ['width' => 80000, 'height' => 297000]; // 80mm thermal
```

---

## 📋 Quick Commands

```bash
# Start service
npm start

# Stop (Ctrl+C, NOT Ctrl+Z)

# Check status
curl http://localhost:3000/health

# Get printers
curl http://localhost:3000/printers

# Test HTML print
curl -X POST http://localhost:3000/print \
  -H "Content-Type: application/json" \
  -d '{"html":"<h1>Test</h1>"}'

# Test URL print
curl -X POST http://localhost:3000/print-url \
  -H "Content-Type: application/json" \
  -d '{"url":"http://localhost/invoice"}'

# Test PDF print
curl -X POST http://localhost:3000/print-pdf \
  -H "Content-Type: application/json" \
  -d '{"pdfUrl":"https://example.com/file.pdf"}'

# Kill all instances
pkill -9 -f "electron-print-service"

# Check port
lsof -i :3000
```

---

## 🔍 Troubleshooting

### No WebSocket Connection
```bash
# 1. Check Laravel WebSockets running
php artisan websockets:serve

# 2. Verify config matches
# config.json host = Laravel .env PUSHER_HOST:PUSHER_PORT

# 3. Try HTTP mode first
# Set websocket.enabled = false in config.json
```

### Duplicate Prints
```bash
# Kill all instances
pkill -9 -f "electron-print-service"

# Start fresh
npm start
```

### PDF Not Printing
- Check PDF is valid and accessible
- Increase timeout to 20+ seconds
- Verify PDF size < 50MB
- Test with simple PDF first

### Blade View Issues
- Test view renders in browser first
- Ensure all CSS is inline
- Pass all required data
- Try simple HTML first

---

## 📊 Compatibility

| Feature | HTTP | WebSocket | Thermal | A4 | PDF |
|---------|------|-----------|---------|----|----|
| HTML | ✅ | ✅ | ✅ | ✅ | ✅ |
| Blade | ✅ | ✅ | ✅ | ✅ | ✅ |
| URL | ✅ | ❌ | ✅ | ✅ | ✅ |
| PDF | ✅ | ✅ | ✅ | ✅ | ✅ |

**Protocols:** Pusher, Reverb, Socket.IO, HTTP Polling  
**Platforms:** Windows, macOS, Linux  
**Printers:** Thermal, A4, Label, Custom

---

## 📁 File Structure

```
electron-print-service/
├── main.js              # Electron app
├── websocket-client.js  # Multi-protocol client
├── index.html           # UI interface
├── config.json          # Configuration
├── package.json         # Dependencies
└── README.md            # This file
```

---

## 🎯 How It Works

```
Laravel → Broadcast Event → WebSocket/HTTP → Electron → Printer
```

1. Laravel broadcasts `PrintJobCreated` event
2. Electron receives via WebSocket or HTTP
3. Creates hidden BrowserWindow
4. Loads HTML/PDF content
5. Silent print to configured printer
6. Logs result

---

## ✨ Features

✅ Multi-format (HTML, Blade, URL, PDF)  
✅ Multi-protocol (Pusher, Socket.IO, HTTP)  
✅ Auto-detection  
✅ Silent printing  
✅ Web UI  
✅ System tray  
✅ All printer types  
✅ Real-time WebSocket  

---

## 🆘 Emergency Reset

```bash
# Kill everything
pkill -9 -f "electron-print-service"
pkill -9 -f "websockets:serve"

# Fresh start
cd /Applications/MAMP/htdocs/Backend-Mart
php artisan websockets:serve &

cd electron-print-service
npm start
```

---

## 📝 License

MIT License - 6amTech

---

**Everything you need in one file!** 🚀
