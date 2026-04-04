/**
 * Real-time Client for SPRIN
 * JavaScript WebSocket client integration
 */

class RealtimeClient {

    constructor(serverUrl = 'ws://localhost:8080') {
        this.serverUrl = serverUrl;
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 3000;
        this.subscribedChannels = [];
        this.callbacks = {};
        this.isConnected = false;
    }

    /**
     * Connect to WebSocket server
     */
    connect() {
        try {
            this.ws = new WebSocket(this.serverUrl);

            this.ws.onopen = (event) => {
                this.isConnected = true;
                this.reconnectAttempts = 0;

                // Re-subscribe to channels
                this.subscribedChannels.forEach(channel => {
                    this.subscribe(channel);
                });

                this.trigger('connected', event);
            };

            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleMessage(data);
                } catch (e) {
                    console.error('Invalid message format:', event.data);
                }
            };

            this.ws.onclose = (event) => {
                this.isConnected = false;
                this.trigger('disconnected', event);

                // Attempt reconnection
                if (this.reconnectAttempts < this.maxReconnectAttempts) {
                    this.reconnectAttempts++;
                    setTimeout(() => {
                        this.connect();
                    }, this.reconnectDelay);
                }
            };

            this.ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.trigger('error', error);
            };

        } catch (error) {
            console.error('Failed to connect:', error);
        }
    }

    /**
     * Handle incoming messages
     */
    handleMessage(data) {
        switch (data.type) {
            case 'welcome':
                break;

            case 'stats':
                this.trigger('stats', data.data);
                break;

            case 'broadcast':
                this.trigger(`broadcast:${data.channel}`, data.data);
                break;

            case 'auth_success':
                this.trigger('authenticated', data);
                break;

            case 'subscribed':
                break;

            case 'error':
                console.error('Server error:', data.message);
                this.trigger('server_error', data);
                break;
        }
    }

    /**
     * Authenticate with JWT token
     */
    authenticate(token) {
        this.send({
            action: 'auth',
            token: token
        });
    }

    /**
     * Subscribe to channel
     */
    subscribe(channel) {
        if (!this.subscribedChannels.includes(channel)) {
            this.subscribedChannels.push(channel);
        }

        this.send({
            action: 'subscribe',
            channel: channel
        });
    }

    /**
     * Unsubscribe from channel
     */
    unsubscribe(channel) {
        this.subscribedChannels = this.subscribedChannels.filter(c => c !== channel);

        this.send({
            action: 'unsubscribe',
            channel: channel
        });
    }

    /**
     * Request statistics
     */
    getStats() {
        this.send({
            action: 'get_stats'
        });
    }

    /**
     * Send message to server
     */
    send(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        } else {
            console.warn('WebSocket not connected');
        }
    }

    /**
     * Register event callback
     */
    on(event, callback) {
        if (!this.callbacks[event]) {
            this.callbacks[event] = [];
        }
        this.callbacks[event].push(callback);
    }

    /**
     * Trigger event callbacks
     */
    trigger(event, data) {
        if (this.callbacks[event]) {
            this.callbacks[event].forEach(callback => {
                try {
                    callback(data);
                } catch (e) {
                    console.error('Callback error:', e);
                }
            });
        }
    }

    /**
     * Disconnect from server
     */
    disconnect() {
        if (this.ws) {
            this.ws.close();
        }
    }

    /**
     * Check connection status
     */
    getStatus() {
        return {
            connected: this.isConnected,
            subscribedChannels: this.subscribedChannels,
            reconnectAttempts: this.reconnectAttempts
        };
    }
}

// Make available globally
window.RealtimeClient = RealtimeClient;
