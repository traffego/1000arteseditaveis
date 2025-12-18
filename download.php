<?php
/**
 * Download do PDF - Verificação de pagamento
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';

$txid = $_GET['txid'] ?? '';

if (empty($txid)) {
    http_response_code(403);
    die('Acesso negado');
}

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT * FROM orders WHERE txid = :txid AND status = 'paid'");
    $stmt->execute([':txid' => $txid]);
    $order = $stmt->fetch();
    
    if (!$order) {
        http_response_code(403);
        die('Acesso negado - Pagamento não confirmado');
    }
    
    // Verificar se o arquivo existe
    if (!file_exists(DOWNLOAD_FILE)) {
        http_response_code(404);
        die('Arquivo não encontrado');
    }
    
    // Enviar arquivo para download
    $fileSize = filesize(DOWNLOAD_FILE);
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . DOWNLOAD_FILENAME . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    
    readfile(DOWNLOAD_FILE);
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    die('Erro ao processar download');
}
