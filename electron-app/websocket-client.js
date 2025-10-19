/**
 * WebSocket Client for Electron Print Service
 * Supports: Pusher/Reverb, Socket.IO, and HTTP Polling
 */

const EchoModule = require('laravel-echo');
const Pusher = require('pusher-js');
const io = require('socket.io-client');

// Make Pusher available globally for Laravel Echo
global.Pusher = Pusher;

class WebSocketClient {
    constructor(config) {
        this.config = config;
        this.client = null;
        this.connected = false;
        this.channel = config.channel || 'printing';
        this.driver = config.driver || 'auto';
        
        // Callbacks
        this.onPrintJob = null;
        this.onConnected = null;
        this.onDisconnected = null;
    }

    /**
     * Auto-detect driver based on URL or use configured driver
     */
    detectDriver() {
        if (this.driver !== 'auto') return this.driver;
        
        const host = this.config.host.toLowerCase();
        if (host.includes('socket.io')) return 'socketio';
        if (this.config.usePolling) return 'polling';
        
        return 'pusher'; // Default to Pusher/Reverb
    }

    /**
     * Connect to WebSocket server
     */
    connect() {
        const driver = this.detectDriver();
        console.log(`🌐 Connecting via ${driver}...`);
        console.log(`📡 Channel: ${this.channel}`);
        console.log(`📍 Host: ${this.config.host}`);

        switch (driver) {
            case 'pusher':
                this.connectPusher();
                break;
            case 'socketio':
                this.connectSocketIO();
                break;
            case 'polling':
                this.connectPolling();
                break;
            default:
                console.error(`❌ Unknown driver: ${driver}`);
        }
    }

    /**
     * Connect using Pusher protocol (Laravel WebSockets, Reverb)
     */
    connectPusher() {
        // Parse WebSocket URL
        let wsHost, wsPort, wsScheme;
        try {
            const url = new URL(this.config.host);
            wsHost = url.hostname;
            wsPort = url.port || 6001;
            wsScheme = url.protocol === 'wss:' ? 'https' : 'http';
        } catch (error) {
            wsHost = 'localhost';
            wsPort = 6001;
            wsScheme = 'http';
        }

        // Initialize Laravel Echo
        const LaravelEcho = EchoModule.default;
        this.client = new LaravelEcho({
            broadcaster: 'pusher',
            key: this.config.key || 'local',
            wsHost: wsHost,
            wsPort: wsPort,
            wssPort: wsPort,
            forceTLS: wsScheme === 'https',
            encrypted: wsScheme === 'https',
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            cluster: this.config.cluster || 'mt1',
        });

        const pusher = this.client.connector.pusher;

        // Connection events
        pusher.connection.bind('connected', () => {
            console.log('✅ Connected via Pusher');
            this.connected = true;
            if (this.onConnected) this.onConnected();
        });

        pusher.connection.bind('disconnected', () => {
            console.log('❌ Disconnected');
            this.connected = false;
            if (this.onDisconnected) this.onDisconnected();
        });

        pusher.connection.bind('state_change', (states) => {
            console.log(`🔄 ${states.previous} → ${states.current}`);
        });

        // Subscribe to channel
        const channel = this.client.channel(this.channel);
        
        channel.subscribed(() => {
            console.log(`✅ Subscribed to "${this.channel}"`);
        });

        // Listen for print jobs
        channel.listen('.print.job', (data) => {
            console.log('📄 Print job received:', data.jobId);
            if (this.onPrintJob) this.onPrintJob(data);
        });
    }

    /**
     * Connect using Socket.IO protocol
     */
    connectSocketIO() {
        this.client = io(this.config.host, {
            path: this.config.path || '/socket.io',
            transports: ['websocket', 'polling'],
            reconnection: true,
        });

        this.client.on('connect', () => {
            console.log('✅ Connected via Socket.IO');
            this.connected = true;
            
            this.client.emit('subscribe', {
                channel: this.channel,
                auth: this.config.auth || {}
            });
            
            if (this.onConnected) this.onConnected();
        });

        this.client.on('disconnect', () => {
            console.log('❌ Disconnected');
            this.connected = false;
            if (this.onDisconnected) this.onDisconnected();
        });

        this.client.on('print.job', (data) => {
            console.log('📄 Print job received:', data.jobId);
            if (this.onPrintJob) this.onPrintJob(data);
        });
    }

    /**
     * Connect using HTTP Polling (fallback)
     */
    connectPolling() {
        const axios = require('axios');
        const pollingUrl = this.config.pollingUrl;
        const interval = this.config.pollingInterval || 2000;
        
        console.log(`📡 Polling: ${pollingUrl}`);
        console.log(`⏱️ Interval: ${interval}ms`);
        
        this.connected = true;
        if (this.onConnected) this.onConnected();
        
        this.pollingInterval = setInterval(async () => {
            try {
                const response = await axios.get(pollingUrl, {
                    params: { channel: this.channel },
                    timeout: 5000
                });
                
                if (response.data.jobs?.length > 0) {
                    response.data.jobs.forEach(job => {
                        console.log('📄 Print job received:', job.jobId);
                        if (this.onPrintJob) this.onPrintJob(job);
                    });
                }
            } catch (error) {
                console.error('❌ Polling error:', error.message);
            }
        }, interval);
    }

    /**
     * Disconnect from server
     */
    disconnect() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
        
        if (this.client?.disconnect) {
            this.client.disconnect();
        }
        
        this.client = null;
        this.connected = false;
        console.log('✅ Disconnected');
    }

    /**
     * Check connection status
     */
    isConnected() {
        return this.connected;
    }

    /**
     * Send print result (optional callback)
     */
    sendPrintResult(jobId, success, message = '') {
        console.log(`📤 Result: ${jobId} - ${success ? '✅' : '❌'} ${message}`);
        
        // Optional: Send result back to Laravel
        if (this.config.callbackUrl) {
            const axios = require('axios');
            axios.post(this.config.callbackUrl, {
                jobId, success, message,
                timestamp: new Date().toISOString()
            }).catch(err => console.error('Callback error:', err.message));
        }
    }
}

module.exports = WebSocketClient;
