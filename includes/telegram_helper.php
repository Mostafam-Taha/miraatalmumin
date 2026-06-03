<?php
// includes/telegram_helper.php

class TelegramHelper {
    private $token;
    
    public function __construct($token) {
        $this->token = $token;
    }
    
    private function call($method, $data = []) {
        // استخدام cURL بدلاً من file_get_contents
        $ch = curl_init();
        $url = "https://api.telegram.org/bot{$this->token}/{$method}";
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'MiraatAlmuminBot/1.0'
        ];
        
        if (!empty($data)) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
            $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);
        
        if ($curlError) {
            return ['success' => false, 'error' => $curlError];
        }
        
        if ($httpCode !== 200) {
            return ['success' => false, 'error' => "HTTP $httpCode"];
        }
        
        $result = json_decode($response, true);
        
        if (!$result || !isset($result['ok']) || !$result['ok']) {
            return ['success' => false, 'error' => $result['description'] ?? 'Unknown error'];
        }
        
        return ['success' => true, 'data' => $result];
    }
    
    public function getMe() {
        return $this->call('getMe');
    }
    
    public function getUpdates($offset = null) {
        $data = [];
        if ($offset !== null) {
            $data['offset'] = $offset;
        }
        return $this->call('getUpdates', $data);
    }
    
    public function sendMessage($chatId, $text, $parseMode = 'HTML') {
        return $this->call('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode
        ]);
    }
}
?>
