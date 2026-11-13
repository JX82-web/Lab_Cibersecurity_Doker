<?php
require_once __DIR__ . '/../config.php';
require_login();

$result='';
$error='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $xml = $_POST['xml'] ?? '';
    if ($xml === '') {
        $error='XML document is required.';
    } else {
        libxml_use_internal_errors(true);

        /*
         * INTENTIONAL XXE VULNERABILITY
         * External entity loading is enabled so that the lab supports
         * in-band and blind/OOB XXE exercises.
         */
        $dom = new DOMDocument();
        $dom->resolveExternals = true;
        $dom->substituteEntities = true;

        if (@$dom->loadXML($xml, LIBXML_DTDLOAD | LIBXML_NOENT)) {
            $name = $dom->getElementsByTagName('name')->item(0)?->nodeValue ?? 'unknown';
            $price = $dom->getElementsByTagName('price')->item(0)?->nodeValue ?? '0';
            $result = "Supplier feed parsed. Product name: " . htmlspecialchars($name) . " | price: " . htmlspecialchars($price);
        } else {
            $error='The XML could not be parsed.';
        }
    }
}
include __DIR__ . '/../header.php';
?>
<div class="card">
<h1>Supplier XML Import</h1>
<p class="muted">Internal supplier feeds are processed by the warehouse integration.</p>
<?php if($error): ?><div class="alert"><?=e($error)?></div><?php endif; ?>
<?php if($result): ?><div class="card"><?= $result ?></div><?php endif; ?>
<form method="post">
<label>XML feed</label>
<textarea name="xml" rows="16" spellcheck="false"><?php if(isset($_POST['xml'])) echo e($_POST['xml']); ?></textarea>
<button>Import XML</button>
</form>
</div>
<?php include __DIR__ . '/../footer.php'; ?>
