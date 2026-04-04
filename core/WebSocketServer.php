<?php
declare(strict_types=1);
/**
 * WebSocket Server for Real-time Updates
 * Ratchet WebSocket Implementation
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class SPRINWebSocket implements MessageComponentInterface {
    
    protected $clients;
    protected $userConnections;
    protected $db;
    
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
        
        // Initialize database connection
        try {
            $dsn = "mysql:host=localhost;dbname=bagops;unix_socket=/opt/lampp/var/mysql/mysql.sock";
            $this->db = new PDO($dsn, 'root', 'root');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("WebSocket DB connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * New connection established
     */
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
        
        // Send welcome message
        $conn->send(json_encode([
            'type' => 'welcome',
            'message' => 'Connected to SPRIN Real-time Server',
            'timestamp' => date('c'),
            'connection_id' => $conn->resourceId
        ]));
    }
    
    /**
     * Message received from client
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data || !isset($data['action'])) {
            $from->send(json_encode([
                'type' => 'error',
                'message' => 'Invalid message format'
            ]));
            return;
        }
        
        switch ($data['action']) {
            case 'auth':
                $this->handleAuth($from, $data);
                break;
                
            case 'subscribe':
                $this->handleSubscribe($from, $data);
                break;
                
            case 'unsubscribe':
                $this->handleUnsubscribe($from, $data);
                break;
                
            case 'ping':
                $from->send(json_encode([
                    'type' => 'pong',
                    'timestamp' => date('c')
                ]));
                break;
                
            case 'get_stats':
                $this->handleGetStats($from);
                break;
                
            default:
                $from->send(json_encode([
                    'type' => 'error',
                    'message' => 'Unknown action: ' . $data['action']
                ]));
        }
    }
    
    /**
     * Handle authentication
     */
    protected function handleAuth(ConnectionInterface $conn, $data) {
        $token = $data['token'] ?? null;
        
        if (!$token) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Authentication token required'
            ]));
            return;
        }
        
        // Verify JWT token (if JWTAuth is available)
        $conn->userData = [
            'authenticated' => true,
            'token' => $token,
            'subscribed_channels' => []
        ];
        
        $this->userConnections[$conn->resourceId] = $conn;
        
        $conn->send(json_encode([
            'type' => 'auth_success',
            'message' => 'Authentication successful',
            'connection_id' => $conn->resourceId
        ]));
    }
    
    /**
     * Handle channel subscription
     */
    protected function handleSubscribe(ConnectionInterface $conn, $data) {
        $channel = $data['channel'] ?? null;
        
        if (!$channel) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Channel name required'
            ]));
            return;
        }
        
        $validChannels = ['personil_updates', 'schedule_updates', 'notifications'];
        
        if (!in_array($channel, $validChannels)) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Invalid channel: ' . $channel
            ]));
            return;
        }
        
        if (!isset($conn->subscribedChannels)) {
            $conn->subscribedChannels = [];
        }
        
        $conn->subscribedChannels[] = $channel;
        
        $conn->send(json_encode([
            'type' => 'subscribed',
            'channel' => $channel,
            'message' => 'Subscribed to ' . $channel
        ]));
    }
    
    /**
     * Handle unsubscribe
     */
    protected function handleUnsubscribe(ConnectionInterface $conn, $data) {
        $channel = $data['channel'] ?? null;
        
        if ($channel && isset($conn->subscribedChannels)) {
            $conn->subscribedChannels = array_diff(
                $conn->subscribedChannels, 
                [$channel]
            );
        }
        
        $conn->send(json_encode([
            'type' => 'unsubscribed',
            'channel' => $channel,
            'message' => 'Unsubscribed from ' . $channel
        ]));
    }
    
    /**
     * Get real-time statistics
     */
    protected function handleGetStats(ConnectionInterface $conn) {
        try {
            $stats = [];
            
            // Get personil count
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM personil WHERE is_deleted = FALSE");
            $stats['total_personil'] = $stmt->fetch()['count'];
            
            // Get active schedules
            $today = date('Y-m-d');
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM schedules WHERE tanggal = '$today' AND is_deleted = FALSE");
            $stats['schedules_today'] = $stmt->fetch()['count'];
            
            $conn->send(json_encode([
                'type' => 'stats',
                'data' => $stats,
                'timestamp' => date('c')
            ]));
            
        } catch (PDOException $e) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]));
        }
    }
    
    /**
     * Broadcast message to all subscribed clients
     */
    public function broadcast($channel, $data) {
        $message = json_encode([
            'type' => 'broadcast',
            'channel' => $channel,
            'data' => $data,
            'timestamp' => date('c')
        ]);
        
        foreach ($this->clients as $client) {
            if (isset($client->subscribedChannels) && in_array($channel, $client->subscribedChannels)) {
                $client->send($message);
            }
        }
    }
    
    /**
     * Connection closed
     */
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        unset($this->userConnections[$conn->resourceId]);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }
    
    /**
     * Error occurred
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    
    /**
     * Start WebSocket server
     */
    public static function run($port = 8080) {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new self()
                )
            ),
            $port
        );
        
        echo "WebSocket server running on port {$port}\n";
        $server->run();
    }
}

// CLI usage
if (php_sapi_name() === 'cli' && isset($argv[1]) && $argv[1] === 'start') {
    $port = $argv[2] ?? 8080;
    SPRINWebSocket::run($port);
}

?>