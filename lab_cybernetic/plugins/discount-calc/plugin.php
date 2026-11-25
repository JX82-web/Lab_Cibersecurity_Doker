<?php
/**
 * Plugin Calcolatore Sconti
 * Fornisce funzioni per calcolo sconti dinamici
 */

if (!defined('CYBERNETIC_APP')) {
    die('Accesso diretto non consentito');
}

class DiscountCalculator {
    private $db;
    private $config;
    
    public function __construct($db, $config) {
        $this->db = $db;
        $this->config = $config;
    }
    
    /**
     * Calcola sconto per singolo prodotto
     * Hook: product_price
     */
    public function calculate_discount($product, $user = null, $quantity = 1) {
        $discount = 0;
        
        // Sconto quantità
        if ($quantity >= 10) {
            $discount = 15;
        } elseif ($quantity >= 5) {
            $discount = 10;
        } elseif ($quantity >= 3) {
            $discount = 5;
        }
        
        // Sconto utente premium
        if ($user && isset($user['role']) && $user['role'] === 'premium') {
            $discount += 5;
        }
        
        // Sconto categoria specifica
        $categoryDiscounts = [
            'elettronica' => 5,
            'accessori' => 10,
            'software' => 15
        ];
        
        if (isset($categoryDiscounts[strtolower($product['category'] ?? '')])) {
            $discount += $categoryDiscounts[strtolower($product['category'])];
        }
        
        // Cap massimo sconto
        return min($discount, 30);
    }
    
    /**
     * Applica sconto al carrello totale
     * Hook: cart_total
     */
    public function apply_cart_discount($cartTotal, $user = null, $itemCount = 0) {
        $discount = 0;
        
        // Sconto fedeltà per ordini grandi
        if ($cartTotal > 500) {
            $discount = 50;
        } elseif ($cartTotal > 200) {
            $discount = 20;
        } elseif ($cartTotal > 100) {
            $discount = 10;
        }
        
        // Bonus utente registrato
        if ($user) {
            $discount += 5;
        }
        
        return min($discount, $cartTotal * 0.3); // Max 30% del totale
    }
    
    /**
     * Restituisce info plugin per admin panel
     */
    public function getInfo() {
        return [
            'name' => 'Calcolatore Sconti',
            'version' => '1.0.0',
            'description' => 'Calcola sconti dinamici basati su quantità, utente e categoria',
            'hooks' => ['product_price', 'cart_total'],
            'settings' => []
        ];
    }
}

// Auto-registrazione hook se PluginLoader è attivo
if (class_exists('PluginLoader')) {
    $loader = PluginLoader::getInstance();
    $calculator = new DiscountCalculator($loader->getDatabase(), $loader->getConfig());
    
    $loader->registerHook('product_price', [$calculator, 'calculate_discount']);
    $loader->registerHook('cart_total', [$calculator, 'apply_cart_discount']);
}
