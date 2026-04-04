<?php
declare(strict_types=1);
/**
 * Cache Manager for SPRIN
 * File-based and Memory-based caching system
 */

class CacheManager {
    
    private $cachePath;
    private $defaultTTL;
    private $memoryCache;
    
    public function __construct($cachePath = __DIR__ . '/../cache/', $defaultTTL = 300) {
        $this->cachePath = $cachePath;
        $this->defaultTTL = $defaultTTL;
        $this->memoryCache = [];
        
        // Create cache directory if not exists
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    /**
     * Generate cache key
     */
    private function generateKey($key) {
        return md5($key);
    }
    
    /**
     * Get cache file path
     */
    private function getCacheFile($key) {
        return $this->cachePath . $this->generateKey($key) . '.cache';
    }
    
    /**
     * Store data in cache
     */
    public function set($key, $data, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTTL;
        $cacheFile = $this->getCacheFile($key);
        
        $cacheData = [
            'expires' => time() + $ttl,
            'data' => $data
        ];
        
        // Store in memory for faster access
        $this->memoryCache[$key] = $cacheData;
        
        // Store in file
        file_put_contents($cacheFile, serialize($cacheData), LOCK_EX);
        
        return true;
    }
    
    /**
     * Get data from cache
     */
    public function get($key) {
        // Check memory cache first
        if (isset($this->memoryCache[$key])) {
            if ($this->memoryCache[$key]['expires'] > time()) {
                return $this->memoryCache[$key]['data'];
            } else {
                unset($this->memoryCache[$key]);
                return null;
            }
        }
        
        // Check file cache
        $cacheFile = $this->getCacheFile($key);
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $cacheData = unserialize(file_get_contents($cacheFile));
        
        if ($cacheData['expires'] < time()) {
            unlink($cacheFile);
            return null;
        }
        
        // Store in memory for faster future access
        $this->memoryCache[$key] = $cacheData;
        
        return $cacheData['data'];
    }
    
    /**
     * Check if key exists in cache
     */
    public function has($key) {
        return $this->get($key) !== null;
    }
    
    /**
     * Delete cache entry
     */
    public function delete($key) {
        unset($this->memoryCache[$key]);
        
        $cacheFile = $this->getCacheFile($key);
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        
        return true;
    }
    
    /**
     * Clear all cache
     */
    public function clear() {
        $this->memoryCache = [];
        
        $files = glob($this->cachePath . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Get cache statistics
     */
    public function getStats() {
        $files = glob($this->cachePath . '*.cache');
        $totalSize = 0;
        $validCount = 0;
        $expiredCount = 0;
        
        foreach ($files as $file) {
            $size = filesize($file);
            $totalSize += $size;
            
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] > time()) {
                $validCount++;
            } else {
                $expiredCount++;
            }
        }
        
        return [
            'total_files' => count($files),
            'valid_entries' => $validCount,
            'expired_entries' => $expiredCount,
            'memory_entries' => count($this->memoryCache),
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2)
        ];
    }
    
    /**
     * Cache database query result
     */
    public function remember($key, $callback, $ttl = null) {
        $cached = $this->get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $data = $callback();
        $this->set($key, $data, $ttl);
        
        return $data;
    }
    
    /**
     * Delete cache by pattern
     */
    public function deletePattern($pattern) {
        $files = glob($this->cachePath . '*.cache');
        $deleted = 0;
        
        foreach ($files as $file) {
            $content = unserialize(file_get_contents($file));
            // Note: This is a simple implementation, could be enhanced
            if (strpos(serialize($content['data']), $pattern) !== false) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Clean expired cache entries
     */
    public function cleanExpired() {
        $files = glob($this->cachePath . '*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
}

/**
 * Query Cache for Database Queries
 */
class QueryCache {
    
    private $cache;
    private $queryCacheTime;
    
    public function __construct($cacheTime = 60) {
        $this->cache = new CacheManager();
        $this->queryCacheTime = $cacheTime;
    }
    
    /**
     * Cache database query
     */
    public function cacheQuery($sql, $params = [], $ttl = null) {
        $key = $this->generateQueryKey($sql, $params);
        $ttl = $ttl ?? $this->queryCacheTime;
        
        return $this->cache->remember($key, function() use ($sql, $params) {
            // This would be called by Database class
            return ['sql' => $sql, 'params' => $params];
        }, $ttl);
    }
    
    /**
     * Generate cache key for query
     */
    private function generateQueryKey($sql, $params) {
        return 'query_' . md5($sql . serialize($params));
    }
    
    /**
     * Invalidate cache by table
     */
    public function invalidateTable($table) {
        return $this->cache->deletePattern($table);
    }
    
    /**
     * Get cache stats
     */
    public function getStats() {
        return $this->cache->getStats();
    }
}

/**
 * Application Cache Helper
 */
class AppCache {
    
    private static $instance = null;
    private $cache;
    
    private function __construct() {
        $this->cache = new CacheManager();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Cache personil statistics
     */
    public function cachePersonilStats($data, $ttl = 300) {
        return $this->cache->set('personil_stats', $data, $ttl);
    }
    
    /**
     * Get cached personil statistics
     */
    public function getPersonilStats() {
        return $this->cache->get('personil_stats');
    }
    
    /**
     * Cache dropdown data
     */
    public function cacheDropdownData($type, $data, $ttl = 3600) {
        return $this->cache->set("dropdown_{$type}", $data, $ttl);
    }
    
    /**
     * Get cached dropdown data
     */
    public function getDropdownData($type) {
        return $this->cache->get("dropdown_{$type}");
    }
    
    /**
     * Clear all application cache
     */
    public function clearAll() {
        return $this->cache->clear();
    }
    
    /**
     * Get cache statistics
     */
    public function getStats() {
        return $this->cache->getStats();
    }
}

?>