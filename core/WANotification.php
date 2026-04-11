<?php
/**
 * WhatsApp Notification Helper
 * Integrates with Fonnte or Wablas API for sending WhatsApp messages
 */

require_once __DIR__ . '/config.php';

class WANotification {
    
    // Configuration - set these in config.php or environment variables
    private static $apiKey = '';
    private static $provider = 'fonnte'; // 'fonnte' or 'wablas'
    private static $baseUrl = '';
    
    /**
     * Initialize with API configuration
     */
    private static function init() {
        self::$apiKey = defined('WA_API_KEY') ? WA_API_KEY : '';
        self::$provider = defined('WA_PROVIDER') ? WA_PROVIDER : 'fonnte';
        
        if (self::$provider === 'fonnte') {
            self::$baseUrl = defined('WA_BASE_URL') ? WA_BASE_URL : 'https://api.fonnte.com/send';
        } else {
            self::$baseUrl = defined('WA_BASE_URL') ? WA_BASE_URL : 'https://solo.wablas.com/api/send-message';
        }
    }
    
    /**
     * Send WhatsApp message
     * @param string $phone Phone number (format: 628xxx, no + or 0 prefix)
     * @param string $message Message content
     * @return array Response from API
     */
    public static function send($phone, $message) {
        self::init();
        
        if (empty(self::$apiKey)) {
            return ['success' => false, 'message' => 'WA API key not configured'];
        }
        
        // Normalize phone number (remove +, convert 08 to 628)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        if (substr($phone, 0, 1) === '+') {
            $phone = substr($phone, 1);
        }
        
        try {
            $ch = curl_init();
            
            if (self::$provider === 'fonnte') {
                // Fonnte API
                $payload = [
                    'target' => $phone,
                    'message' => $message,
                ];
                
                curl_setopt($ch, CURLOPT_URL, self::$baseUrl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: ' . self::$apiKey
                ]);
                
            } else {
                // Wablas API
                $payload = [
                    'phone' => $phone,
                    'message' => $message,
                ];
                
                curl_setopt($ch, CURLOPT_URL, self::$baseUrl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: ' . self::$apiKey
                ]);
            }
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if ($httpCode === 200 && isset($result['status']) && $result['status'] === true) {
                return ['success' => true, 'message' => 'Message sent successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to send message', 'response' => $result];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send piket notification H-1
     * @param string $phone Phone number
     * @param string $name Personil name
     * @param string $date Piket date
     * @param string $shift Shift type
     * @param string $location Location
     */
    public static function sendPiketReminder($phone, $name, $date, $shift, $location = 'Markas Polres Samosir') {
        $message = "*PENGINGAT PIKET H-1*\n\n";
        $message .= "Yth. Bpk/Ibu *$name*\n\n";
        $message .= "Besok Anda memiliki jadwal piket:\n";
        $message .= "📅 Tanggal: $date\n";
        $message .= "⏰ Shift: $shift\n";
        $message .= "📍 Lokasi: $location\n\n";
        $message .= "Mohon hadir tepat waktu. Terima kasih.\n\n";
        $message .= "_BAGOPS Polres Samosir_";
        
        return self::send($phone, $message);
    }
    
    /**
     * Send Sprint notification
     * @param string $phone Phone number
     * @param string $name Personil name
     * @param string $nomorSprint Sprint number
     * @param string $operasi Operation name
     * @param string $tanggal Operation date
     */
    public static function sendSprintNotification($phone, $name, $nomorSprint, $operasi, $tanggal) {
        $message = "*SURAT PERINTAH (SPRIN)*\n\n";
        $message .= "Yth. Bpk/Ibu *$name*\n\n";
        $message .= "Nomor: $nomorSprint\n";
        $message .= "Operasi: $operasi\n";
        $message .= "Tanggal: $tanggal\n\n";
        $message .= "Dimohon melaksanakan tugas dengan penuh tanggung jawab.\n\n";
        $message .= "_BAGOPS Polres Samosir_";
        
        return self::send($phone, $message);
    }
    
    /**
     * Send rotation notification
     * @param string $phone Phone number
     * @param string $name Personil name
     * @param string $faseBaru New phase
     */
    public static function sendRotationNotification($phone, $name, $faseBaru) {
        $message = "*INFORMASI ROTASI PIKET*\n\n";
        $message .= "Yth. Bpk/Ibu *$name*\n\n";
        $message .= "Posisi fase piket Anda telah dirotasi ke:\n";
        $message .= "🔄 Fase Baru: $faseBaru\n\n";
        $message .= "Silakan cek jadwal piket terbaru di sistem.\n\n";
        $message .= "_BAGOPS Polres Samosir_";
        
        return self::send($phone, $message);
    }
    
    /**
     * Send bulk messages (for multiple recipients)
     * @param array $recipients Array of ['phone' => 'xxx', 'name' => 'xxx', ...params]
     * @param callable $messageBuilder Function to build message for each recipient
     * @return array Results
     */
    public static function sendBulk($recipients, $messageBuilder) {
        $results = [];
        
        foreach ($recipients as $recipient) {
            if (empty($recipient['phone'])) {
                $results[] = ['success' => false, 'message' => 'No phone number', 'recipient' => $recipient];
                continue;
            }
            
            $message = $messageBuilder($recipient);
            $result = self::send($recipient['phone'], $message);
            $result['recipient'] = $recipient;
            $results[] = $result;
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }
        
        return $results;
    }
}
