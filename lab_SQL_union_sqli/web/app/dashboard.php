<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>SecureCorp - Terminal Dashboard</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #0d0e15; color: #00ff66; padding: 40px; }
        .container { max-width: 900px; margin: auto; background: #151821; padding: 30px; border-radius: 8px; border: 1px solid #1f2430; }
        h1 { color: #fff; border-bottom: 1px solid #1f2430; padding-bottom: 10px; margin-top:0; }
        .panel { background: #1f2430; border: 1px solid #2f3647; padding: 20px; border-radius: 6px; font-family: monospace; }
        .btn-logout { color: #ff5555; border: 1px solid #ff5555; text-decoration: none; padding: 5px 15px; border-radius: 4px; float: right; font-weight: bold; }
        .btn-logout:hover { background: #ff5555; color: #151821; }
        .console-log { background: #000; color: #00ff00; padding: 15px; border-radius: 4px; border: 1px solid #333; overflow-x: auto; }
    </style>
</head>
<body>
<div class="container">
    <a href="logout.php" class="btn-logout">Esci dal portale</a>
    <h1>📊 Pannello di Controllo Riservato</h1>
    <p>Operatore attivo: <b style="color:#ffffff;"><?php echo htmlspecialchars($_SESSION['username']); ?></b></p>
    
    <div class="panel">
        <h3>⚙️ Stato dei Servizi di Automazione Interna</h3>
        <p>I sistemi di manutenzione ordinaria sono attivi in background sul server.</p>
        
        <h4>📄 Ultimo Output Attività Schedulate:</h4>
        <div class="console-log">
            [+] LOG: Inizializzazione routine di sincronizzazione...<br>
            [+] INFO: Archiviazione periodica in esecuzione tramite script aziendali.<br>
            [+] TARGET: Backup automatico generato con successo nella directory dedicata.<br>
            [*] STATUS: In attesa del prossimo ciclo cron (Esecuzione ogni 120 secondi).
        </div>
    </div>
    <br>
    <a href="index.php" style="color: #00bfff; text-decoration: none;">← Torna al Catalogo Prodotti</a>
</div>
</body>
</html>
