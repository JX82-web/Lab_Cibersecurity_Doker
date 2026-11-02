<?php
// dashboard.php
if (!isset($_SESSION['user'])) {
    echo "<div class='alert alert-warning text-center'>Accesso negato. Autenticazione richiesta.</div>";
    return;
}
?>
<div class="card p-4">
    <h2>Pannello di Controllo Riservato</h2>
    <p>Livello di accesso attuale: <span class="badge bg-info"><?= strtoupper($_SESSION['role']) ?></span></p>
    <hr class="border-secondary">
    
    <div class="row mt-3">
        <div class="col-md-6">
            <h5>Informazioni di Sessione</h5>
            <table class="table table-dark table-striped table-bordered border-secondary">
                <tr><td>ID Utente Sessione</td><td><?= session_id() ?></td></tr>
                <tr><td>Nodo Connessione</td><td>Internal DMZ Gateway</td></tr>
            </table>
        </div>
        <div class="col-md-6">
            <h5>Note per gli Sviluppatori</h5>
            <div class="p-3 bg-black rounded border border-warning text-warning">
                <strong>[!] AVVISO CRITICO:</strong> Il server di staging Python interno per i test automatici è attivo sulla rete locale (porta 8888). Non esporre questo servizio su indirizzi IP esterni!
            </div>
        </div>
    </div>
</div>
