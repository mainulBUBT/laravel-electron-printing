const { app, BrowserWindow, ipcMain, Tray, Menu } = require('electron');
const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');
const WebSocketClient = require('./websocket-client');

// 2️⃣ Initialize @electron/remote
require('@electron/remote/main').initialize();

let mainWindow;
let tray = null;
let httpServer = null;
let wsClient = null;

// Load configuration
const userDataPath = app.getPath('userData');
const configPath = path.join(userDataPath, 'config.json');
let config = {
    port: 3000,
    listenIP: '0.0.0.0',
    websocket: {
        enabled: false,
        host: 'https://6am.one',
        auth: {}
    }
};

// Ensure user data directory exists
if (!fs.existsSync(userDataPath)) {
    fs.mkdirSync(userDataPath, { recursive: true });
}

// Load config from file if exists
if (fs.existsSync(configPath)) {
    try {
        const savedConfig = JSON.parse(fs.readFileSync(configPath, 'utf8'));
        config = { ...config, ...savedConfig };
    } catch (error) {
        console.error('Error loading config:', error);
    }
}

const PORT = config.port;

// Create Express server for receiving print requests
const server = express();
server.use(cors());
server.use(express.json());

// Serve static files for the UI
server.use('/assets', express.static(path.join(__dirname, 'assets')));

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 900,
        height: 700,
        minWidth: 800,
        minHeight: 600,
        icon: path.join(__dirname, 'assets', 'icon.png'),
        webPreferences: {
            nodeIntegration: true,
            contextIsolation: false,
            webSecurity: false
        },
        title: '6amTech Printing Service',
        backgroundColor: '#ffffff'
    });

    // Load the UI
    mainWindow.loadFile('index.html');
    
    // 2️⃣ Enable remote module for this window
    require('@electron/remote/main').enable(mainWindow.webContents);

    // Create system tray icon
    createTray();

    // Handle window close - minimize to tray instead
    mainWindow.on('close', (event) => {
        if (!app.isQuitting) {
            event.preventDefault();
            mainWindow.hide();
            console.log('Window hidden to tray');
        } else {
            console.log('App is quitting, closing window');
        }
        return false;
    });

    mainWindow.on('closed', () => {
        console.log('Window closed');
        mainWindow = null;
    });
}

function createTray() {
    const iconPath = path.join(__dirname, 'assets', 'icon.png');
    tray = new Tray(iconPath);
    
    const contextMenu = Menu.buildFromTemplate([
        {
            label: 'Show App',
            click: () => {
                mainWindow.show();
            }
        },
        {
            label: 'Hide App',
            click: () => {
                mainWindow.hide();
            }
        },
        { type: 'separator' },
        {
            label: 'Service Status',
            enabled: false
        },
        {
            label: '✓ Running on port ' + PORT,
            enabled: false
        },
        { type: 'separator' },
        {
            label: 'Quit',
            click: () => {
                app.isQuitting = true;
                app.quit();
            }
        }
    ]);
    
    tray.setToolTip('6amTech Printing Service - Running');
    tray.setContextMenu(contextMenu);
    
    tray.on('click', () => {
        mainWindow.isVisible() ? mainWindow.hide() : mainWindow.show();
    });
}

// Print endpoint - receives HTML content and prints it
server.post('/print', async (req, res) => {
    try {
        const { html, printerName, options } = req.body;

        if (!html) {
            return res.status(400).json({ 
                success: false, 
                message: 'HTML content is required' 
            });
        }

        // Create a hidden window for printing
        const printWindow = new BrowserWindow({
            show: false,
            webPreferences: {
                nodeIntegration: true,
                contextIsolation: false
            }
        });

        // Load the HTML content
        await printWindow.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(html)}`);

        // Wait for content to load
        await new Promise(resolve => setTimeout(resolve, 500));

        // Print options
        const printOptions = {
            silent: true, // Silent printing without dialog
            printBackground: true,
            deviceName: printerName || '', // Use default printer if not specified
            ...options
        };

        // Print the content
        printWindow.webContents.print(printOptions, (success, errorType) => {
            printWindow.close();
            
            if (success) {
                res.json({ 
                    success: true, 
                    message: 'Print job sent successfully' 
                });
            } else {
                res.status(500).json({ 
                    success: false, 
                    message: `Print failed: ${errorType}` 
                });
            }
        });

    } catch (error) {
        console.error('Print error:', error);
        res.status(500).json({ 
            success: false, 
            message: error.message 
        });
    }
});

// Print from URL endpoint (for Blade views, external URLs)
server.post('/print-url', async (req, res) => {
    try {
        const { url, printerName, options } = req.body;

        if (!url) {
            return res.status(400).json({ 
                success: false, 
                message: 'URL is required' 
            });
        }

        // Create a hidden window for printing
        const printWindow = new BrowserWindow({
            show: false,
            webPreferences: {
                nodeIntegration: true,
                contextIsolation: false,
                webSecurity: false
            }
        });

        // Load the URL
        await printWindow.loadURL(url);

        // Wait for content to load
        await new Promise(resolve => setTimeout(resolve, 1000));

        // Print options
        const printOptions = {
            silent: true,
            printBackground: true,
            deviceName: printerName || '',
            ...options
        };

        // Print the content
        printWindow.webContents.print(printOptions, (success, errorType) => {
            printWindow.close();
            
            if (success) {
                res.json({ 
                    success: true, 
                    message: 'Print job sent successfully' 
                });
            } else {
                res.status(500).json({ 
                    success: false, 
                    message: `Print failed: ${errorType}` 
                });
            }
        });

    } catch (error) {
        console.error('Print error:', error);
        res.status(500).json({ 
            success: false, 
            message: error.message 
        });
    }
});

// Print PDF endpoint
server.post('/print-pdf', async (req, res) => {
    try {
        const { pdfUrl, pdfBase64, printerName, options } = req.body;

        if (!pdfUrl && !pdfBase64) {
            return res.status(400).json({ 
                success: false, 
                message: 'PDF URL or Base64 data is required' 
            });
        }

        // Create a hidden window for PDF
        const printWindow = new BrowserWindow({
            show: false,
            webPreferences: {
                plugins: true,
                nodeIntegration: true,
                contextIsolation: false,
                webSecurity: false
            }
        });

        // Load PDF from URL or Base64
        if (pdfUrl) {
            await printWindow.loadURL(pdfUrl);
        } else {
            // Load PDF from Base64
            const pdfData = `data:application/pdf;base64,${pdfBase64}`;
            await printWindow.loadURL(pdfData);
        }

        // Wait for PDF to load
        await new Promise(resolve => setTimeout(resolve, 1500));

        // Print options for PDF
        const printOptions = {
            silent: true,
            printBackground: false, // PDFs don't need background
            deviceName: printerName || '',
            ...options
        };

        // Print the PDF
        printWindow.webContents.print(printOptions, (success, errorType) => {
            printWindow.close();
            
            if (success) {
                res.json({ 
                    success: true, 
                    message: 'PDF printed successfully' 
                });
            } else {
                res.status(500).json({ 
                    success: false, 
                    message: `PDF print failed: ${errorType}` 
                });
            }
        });

    } catch (error) {
        console.error('PDF print error:', error);
        res.status(500).json({ 
            success: false, 
            message: error.message 
        });
    }
});

// Get available printers
server.get('/printers', async (req, res) => {
    try {
        const printers = await mainWindow.webContents.getPrintersAsync();
        res.json({ 
            success: true, 
            printers: printers 
        });
    } catch (error) {
        res.status(500).json({ 
            success: false, 
            message: error.message 
        });
    }
});

// Health check endpoint
server.get('/health', (req, res) => {
    res.json({ 
        success: true, 
        message: 'Print service is running',
        port: PORT
    });
});

// Start Express server
httpServer = server.listen(PORT, config.listenIP, () => {
    console.log(`Print service listening on ${config.listenIP}:${PORT}`);
    console.log('Available endpoints:');
    console.log(`  POST http://localhost:${PORT}/print - Print HTML content`);
    console.log(`  POST http://localhost:${PORT}/print-url - Print from URL (Blade views)`);
    console.log(`  POST http://localhost:${PORT}/print-pdf - Print PDF files`);
    console.log(`  GET  http://localhost:${PORT}/printers - Get available printers`);
    console.log(`  GET  http://localhost:${PORT}/health - Health check`);
});

// Handle server errors
httpServer.on('error', (error) => {
    if (error.code === 'EADDRINUSE') {
        console.error(`Port ${PORT} is already in use!`);
        app.quit();
    } else {
        console.error('Server error:', error);
    }
});

// Initialize WebSocket client if enabled
function initializeWebSocket() {
    if (config.websocket && config.websocket.enabled) {
        console.log('🌐 Initializing WebSocket client...');
        
        wsClient = new WebSocketClient(config.websocket);
        
        // Handle incoming print jobs
        wsClient.onPrintJob = async (data) => {
            console.log(`📄 Received print job: ${data.jobId}`);
            
            try {
                if (!data.html) {
                    console.error(`❌ Print job ${data.jobId} missing HTML content`);
                    wsClient.sendPrintResult(data.jobId, false, 'Missing HTML content');
                    return;
                }

                // Create a hidden window for printing
                const printWindow = new BrowserWindow({
                    show: false,
                    webPreferences: {
                        nodeIntegration: true,
                        contextIsolation: false
                    }
                });

                // Load the HTML content
                await printWindow.loadURL(`data:text/html;charset=utf-8,${encodeURIComponent(data.html)}`);

                // Wait for content to load
                await new Promise(resolve => setTimeout(resolve, 500));

                // Print options
                const printOptions = {
                    silent: true, // Silent printing without dialog
                    printBackground: true,
                    deviceName: data.printerName || '', // Use default printer if not specified
                    ...data.options
                };

                // Print the content
                printWindow.webContents.print(printOptions, (success, errorType) => {
                    printWindow.close();
                    
                    if (success) {
                        console.log(`✅ Print job ${data.jobId} completed successfully`);
                        wsClient.sendPrintResult(data.jobId, true, 'Printed successfully');
                        
                        // Notify UI
                        if (mainWindow) {
                            mainWindow.webContents.send('print-job-completed', {
                                jobId: data.jobId,
                                success: true
                            });
                        }
                    } else {
                        console.error(`❌ Print job ${data.jobId} failed:`, errorType);
                        wsClient.sendPrintResult(data.jobId, false, `Print failed: ${errorType}`);
                        
                        // Notify UI
                        if (mainWindow) {
                            mainWindow.webContents.send('print-job-failed', {
                                jobId: data.jobId,
                                error: errorType
                            });
                        }
                    }
                });
            } catch (error) {
                console.error(`❌ Error processing print job ${data.jobId}:`, error);
                wsClient.sendPrintResult(data.jobId, false, error.message);
            }
        };
        
        wsClient.onConnected = () => {
            console.log('✅ WebSocket connected to server');
            if (mainWindow) {
                mainWindow.webContents.send('websocket-status', { connected: true });
            }
        };
        
        wsClient.onDisconnected = () => {
            console.log('❌ WebSocket disconnected from server');
            if (mainWindow) {
                mainWindow.webContents.send('websocket-status', { connected: false });
            }
        };
        
        wsClient.connect();
    } else {
        console.log('ℹ️  WebSocket mode disabled, using HTTP mode');
    }
}

// Electron app lifecycle
app.whenReady().then(() => {
    createWindow();
    initializeWebSocket();
});


app.on('activate', () => {
    if (mainWindow === null) {
        createWindow();
    }
});

// Cleanup when app is quitting
app.on('before-quit', (event) => {
    console.log('=================================');
    console.log('Print service shutting down...');
    console.log('=================================');
    
    // Disconnect WebSocket
    if (wsClient) {
        console.log('Disconnecting WebSocket client...');
        wsClient.disconnect();
        wsClient = null;
        console.log('✅ WebSocket disconnected');
    }
    
    // Close HTTP server and release port
    if (httpServer) {
        console.log(`Closing HTTP server on port ${PORT}...`);
        httpServer.close();
        httpServer = null;
        console.log(`✅ Port ${PORT} released successfully`);
    }
    
    // Destroy tray icon
    if (tray) {
        console.log('Destroying tray icon...');
        tray.destroy();
        tray = null;
        console.log('✅ Tray icon destroyed');
    }
    
    console.log('✅ All services stopped');
    console.log('✅ Application closed successfully');
    console.log('=================================');
});

// Handle all windows closed
app.on('window-all-closed', () => {
    // On macOS, keep app running in tray
    if (process.platform === 'darwin') {
        console.log('All windows closed, app running in tray');
    } else {
        // On other platforms, quit when all windows are closed
        console.log('All windows closed, quitting app');
        app.quit();
    }
});

// IPC handlers for configuration
ipcMain.handle('get-config', () => {
    return config;
});

ipcMain.handle('save-config', (event, newConfig) => {
    try {
        config = { ...config, ...newConfig };
        fs.writeFileSync(configPath, JSON.stringify(config, null, 2));
        return { success: true, message: 'Configuration saved. Please restart the app.' };
    } catch (error) {
        return { success: false, message: error.message };
    }
});
