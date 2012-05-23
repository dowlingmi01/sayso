<?php  

$directory = dirname(__FILE__);

$fileName = ($_GET['env'] === 'PROD' ? 'Say.So Starbar' : 'SaySo-' . $_GET['env']) . '.safariextz';

$filePath = $directory . '/' . $fileName;

header('Content-Type: application/octet-stream');
header('Content-Length: ' . strlen(filesize($filePath)));
header('Content-Disposition: inline; filename="' . $fileName . '"');

exit(file_get_contents($filePath));
