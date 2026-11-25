<?php
/**
 * GET /api/v1/shop/products
 * Lista prodotti in formato JSON
 */

require_once __DIR__ . '/../../../../src/core/Database.php';
require_once __DIR__ . '/../../../../src/core/PluginLoader.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito']);
    exit;
}

try {
    $db = Database::getInstance();
    $loader = PluginLoader::getInstance();
    
    // Parametri query
    $category = $_GET['category'] ?? null;
    $search = $_GET['search'] ?? null;
    $limit = min((int)($_GET['limit'] ?? 50), 100);
    $offset = max((int)($_GET['offset'] ?? 0), 0);
    
    $where = ['stock > 0'];
    $params = [];
    
    if ($category) {
        $where[] = 'category = ?';
        $params[] = $category;
    }
    
    if ($search) {
        $where[] = '(name LIKE ? OR description LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql = "SELECT * FROM products WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $products = $db->fetchAll($sql, $params);
    
    // Applica hook sconti se plugin caricato
    if ($loader->hasPlugin('discount-calc')) {
        // Simula utente corrente per sconti
        $sessionHandler = new SessionHandler();
        $session = $sessionHandler->validate();
        $user = $session ? ['role' => $session['data']['role'] ?? 'user'] : null;
        
        foreach ($products as &$product) {
            $discount = $loader->applyHook('product_price', 0, $product, $user, 1);
            if ($discount > 0) {
                $product['discount'] = $discount;
                $product['discounted_price'] = round($product['price'] * (1 - $discount / 100), 2);
            }
        }
    }
    
    $total = $db->fetchOne("SELECT COUNT(*) as c FROM products WHERE " . implode(' AND ', $where), array_slice($params, 0, -2))['c'];
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset
    ]);
    
} catch (Exception $e) {
    error_log("Products error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore interno del server']);
}
