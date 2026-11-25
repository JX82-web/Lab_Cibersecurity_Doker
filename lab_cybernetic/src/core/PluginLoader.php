<?php
/**
 * Plugin Loader - Caricamento dinamico plugin esterni
 * Legge installed.json e include plugin.php abilitati
 */

class PluginLoader {
    private static $instance = null;
    private $plugins = [];
    private $hooks = [];
    private $db;
    private $config;
    private $pluginPath;
    
    private function __construct() {
        $this->db = Database::getInstance();
        $this->loadConfig();
        $this->pluginPath = $this->config['plugin_path'] ?? '/var/www/html/plugins';
        $this->loadPlugins();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function loadConfig() {
        $configPath = __DIR__ . '/../../config/app_config.json';
        if (file_exists($configPath)) {
            $this->config = json_decode(file_get_contents($configPath), true);
        } else {
            $this->config = [
                'plugin_path' => '/var/www/html/plugins',
                'debug_mode' => true
            ];
        }
    }
    
    /**
     * Carica tutti i plugin abilitati da installed.json
     */
    private function loadPlugins() {
        $installedPath = $this->pluginPath . '/installed.json';
        
        if (!file_exists($installedPath)) {
            if ($this->config['debug_mode']) {
                error_log("Plugin registry not found: " . $installedPath);
            }
            return;
        }
        
        $registry = json_decode(file_get_contents($installedPath), true);
        
        if (!isset($registry['plugins']) || !is_array($registry['plugins'])) {
            return;
        }
        
        foreach ($registry['plugins'] as $pluginId => $pluginInfo) {
            if (($pluginInfo['enabled'] ?? false) === false) {
                continue;
            }
            
            $pluginDir = $this->pluginPath . '/' . $pluginId;
            $entryPoint = $pluginDir . '/' . ($pluginInfo['entry_point'] ?? 'plugin.php');
            
            if (!file_exists($entryPoint)) {
                if ($this->config['debug_mode']) {
                    error_log("Plugin entry point not found: " . $entryPoint);
                }
                continue;
            }
            
            try {
                // Include il plugin - PERICOLOSO se plugin malizioso!
                require_once $entryPoint;
                
                $this->plugins[$pluginId] = [
                    'info' => $pluginInfo,
                    'path' => $pluginDir,
                    'loaded' => true
                ];
                
                if ($this->config['debug_mode']) {
                    error_log("Plugin loaded: " . $pluginId);
                }
            } catch (Exception $e) {
                if ($this->config['debug_mode']) {
                    error_log("Plugin load error (" . $pluginId . "): " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Registra hook per estensioni
     */
    public function registerHook($hookName, $callback) {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
        }
        $this->hooks[$hookName][] = $callback;
    }
    
    /**
     * Esegue tutti i callback registrati per un hook
     */
    public function runHook($hookName, ...$args) {
        $results = [];
        
        if (!isset($this->hooks[$hookName])) {
            return $results;
        }
        
        foreach ($this->hooks[$hookName] as $callback) {
            try {
                $result = call_user_func_array($callback, $args);
                $results[] = $result;
            } catch (Exception $e) {
                if ($this->config['debug_mode']) {
                    error_log("Hook error (" . $hookName . "): " . $e->getMessage());
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Applica hook con valore di ritorno singolo (primo non-null)
     */
    public function applyHook($hookName, $initialValue, ...$args) {
        $value = $initialValue;
        
        if (!isset($this->hooks[$hookName])) {
            return $value;
        }
        
        foreach ($this->hooks[$hookName] as $callback) {
            try {
                $result = call_user_func_array($callback, array_merge([$value], $args));
                if ($result !== null) {
                    $value = $result;
                }
            } catch (Exception $e) {
                if ($this->config['debug_mode']) {
                    error_log("Hook apply error (" . $hookName . "): " . $e->getMessage());
                }
            }
        }
        
        return $value;
    }
    
    /**
     * Ottiene lista plugin caricati
     */
    public function getPlugins() {
        return $this->plugins;
    }
    
    /**
     * Verifica se plugin è caricato
     */
    public function hasPlugin($pluginId) {
        return isset($this->plugins[$pluginId]);
    }
    
    /**
     * Ottiene info plugin
     */
    public function getPluginInfo($pluginId) {
        return $this->plugins[$pluginId]['info'] ?? null;
    }
    
    /**
     * Installa nuovo plugin da file ZIP o directory
     */
    public function installPlugin($sourcePath, $pluginId = null) {
        $installedPath = $this->pluginPath . '/installed.json';
        $registry = [];
        
        if (file_exists($installedPath)) {
            $registry = json_decode(file_get_contents($installedPath), true);
            if (!isset($registry['plugins'])) {
                $registry['plugins'] = [];
            }
        } else {
            $registry['plugins'] = [];
        }
        
        // Se è ZIP, estrae
        if (strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION)) === 'zip') {
            $extractPath = $this->pluginPath . '/temp_extract_' . uniqid();
            $zip = new ZipArchive();
            
            if ($zip->open($sourcePath) !== true) {
                throw new Exception('Impossibile aprire file ZIP');
            }
            
            $zip->extractTo($extractPath);
            $zip->close();
            
            // Trova manifest.json
            $manifestPath = $extractPath . '/manifest.json';
            if (!file_exists($manifestPath)) {
                // Cerca in sottocartelle
                $dirs = glob($extractPath . '/*', GLOB_ONLYDIR);
                foreach ($dirs as $dir) {
                    if (file_exists($dir . '/manifest.json')) {
                        $manifestPath = $dir . '/manifest.json';
                        $extractPath = $dir;
                        break;
                    }
                }
            }
            
            if (!file_exists($manifestPath)) {
                throw new Exception('manifest.json non trovato nel plugin');
            }
            
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $pluginId = $pluginId ?? $manifest['name'] ?? basename($extractPath);
            
            // Sposta in posizione finale
            $finalPath = $this->pluginPath . '/' . $pluginId;
            if (is_dir($finalPath)) {
                throw new Exception('Plugin gia esistente: ' . $pluginId);
            }
            
            rename($extractPath, $finalPath);
            
            // Pulizia temp se diverso
            if ($extractPath !== $finalPath) {
                $this->rrmdir(dirname($extractPath));
            }
            
        } else {
            // Directory o file PHP diretto
            $manifestPath = $sourcePath . '/manifest.json';
            if (!file_exists($manifestPath)) {
                throw new Exception('manifest.json richiesto per installazione');
            }
            
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $pluginId = $pluginId ?? $manifest['name'] ?? basename($sourcePath);
            
            $finalPath = $this->pluginPath . '/' . $pluginId;
            if (is_dir($finalPath)) {
                throw new Exception('Plugin gia esistente: ' . $pluginId);
            }
            
            $this->copyDirectory($sourcePath, $finalPath);
        }
        
        // Registra nel registry
        $registry['plugins'][$pluginId] = [
            'name' => $manifest['display_name'] ?? $manifest['name'] ?? $pluginId,
            'version' => $manifest['version'] ?? '1.0.0',
            'description' => $manifest['description'] ?? '',
            'author' => $manifest['author'] ?? 'Unknown',
            'entry_point' => $manifest['entry_point'] ?? 'plugin.php',
            'enabled' => true,
            'installed_at' => date('c'),
            'permissions' => $manifest['permissions'] ?? []
        ];
        
        file_put_contents($installedPath, json_encode($registry, JSON_PRETTY_PRINT));
        
        // Ricarica plugin
        $this->loadPlugins();
        
        return $pluginId;
    }
    
    /**
     * Disabilita plugin
     */
    public function disablePlugin($pluginId) {
        $installedPath = $this->pluginPath . '/installed.json';
        $registry = json_decode(file_get_contents($installedPath), true);
        
        if (isset($registry['plugins'][$pluginId])) {
            $registry['plugins'][$pluginId]['enabled'] = false;
            file_put_contents($installedPath, json_encode($registry, JSON_PRETTY_PRINT));
            unset($this->plugins[$pluginId]);
            return true;
        }
        return false;
    }
    
    /**
     * Rimuove plugin completamente
     */
    public function uninstallPlugin($pluginId) {
        $installedPath = $this->pluginPath . '/installed.json';
        $registry = json_decode(file_get_contents($installedPath), true);
        
        if (isset($registry['plugins'][$pluginId])) {
            unset($registry['plugins'][$pluginId]);
            file_put_contents($installedPath, json_encode($registry, JSON_PRETTY_PRINT));
            
            $pluginDir = $this->pluginPath . '/' . $pluginId;
            if (is_dir($pluginDir)) {
                $this->rrmdir($pluginDir);
            }
            
            unset($this->plugins[$pluginId]);
            return true;
        }
        return false;
    }
    
    private function copyDirectory($source, $dest) {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        
        $items = scandir($source);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            
            $sourcePath = $source . '/' . $item;
            $destPath = $dest . '/' . $item;
            
            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }
    }
    
    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $items = scandir($dir);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                $path = $dir . '/' . $item;
                is_dir($path) ? $this->rrmdir($path) : unlink($path);
            }
            rmdir($dir);
        }
    }
    
    public function getDatabase() {
        return $this->db;
    }
    
    public function getConfig() {
        return $this->config;
    }
}
