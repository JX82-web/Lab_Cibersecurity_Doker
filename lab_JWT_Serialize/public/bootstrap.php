<?php
declare(strict_types=1);

/*
 * Autoloader del laboratorio.
 *
 * Il progetto NON segue una semplice mappa PSR-4 "Lab\ => radice".
 * Ogni namespace risiede in una directory precisa, quindi la mappa è esplicita:
 *
 *   Lab\Core     -> src/Core/
 *   Lab\Models   -> Models/
 *   Lab\Plugins  -> plugins/   (minuscolo: il filesystem Linux è case-sensitive)
 *
 * Il nome del file è il nome della classe (es. Lab\Plugins\AuditPlugin -> plugins/AuditPlugin.php).
 */
const LAB_NAMESPACE_MAP = [
    'Lab\\Core\\'    => 'src/Core/',
    'Lab\\Models\\'  => 'Models/',
    'Lab\\Plugins\\' => 'plugins/',
];

spl_autoload_register(function (string $class): void {
    foreach (LAB_NAMESPACE_MAP as $prefix => $dir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        $relative = substr($class, strlen($prefix));
        $file = __DIR__ . '/../' . $dir . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
        return;
    }
});
