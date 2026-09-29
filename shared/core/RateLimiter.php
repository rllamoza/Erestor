<?php
/**
 * Simple Rate Limiter to prevent brute-force attacks
 * Stores attempts in a temporary directory
 */
class RateLimiter {
    private $storageDir;
    
    public function __construct() {
        $this->storageDir = sys_get_temp_dir() . '/ratelimit_hamuy/';
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }
    }

    private function getFilePath($key) {
        return $this->storageDir . md5($key) . '.json';
    }

    /**
     * Check if the rate limit has been exceeded
     * @param string $key (e.g. user IP or email)
     * @param int $maxAttempts
     * @param int $decaySeconds
     * @return array ['exceeded' => bool, 'remaining' => int, 'retry_after' => int]
     */
    public function check($key, $maxAttempts = 5, $decaySeconds = 300) {
        $file = $this->getFilePath($key);
        $now = time();
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            
            // Clean up old attempts
            $data['attempts'] = array_filter($data['attempts'], function($timestamp) use ($now, $decaySeconds) {
                return $timestamp > ($now - $decaySeconds);
            });
        } else {
            $data = ['attempts' => []];
        }

        $attemptsCount = count($data['attempts']);
        $exceeded = $attemptsCount >= $maxAttempts;
        $retryAfter = 0;

        if ($exceeded) {
            $oldestAttempt = min($data['attempts']);
            $retryAfter = ($oldestAttempt + $decaySeconds) - $now;
        }

        return [
            'exceeded' => $exceeded,
            'remaining' => max(0, $maxAttempts - $attemptsCount),
            'retry_after' => $retryAfter
        ];
    }

    /**
     * Record a new attempt
     */
    public function hit($key) {
        $file = $this->getFilePath($key);
        $now = time();
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
        } else {
            $data = ['attempts' => []];
        }

        $data['attempts'][] = $now;
        file_put_contents($file, json_encode($data));
    }

    /**
     * Clear attempts for a key (on successful login)
     */
    public function clear($key) {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
