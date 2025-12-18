<?php
/**
 * API - Verificar status do pagamento
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/EfiPay.php';

try {
    $txid = $_GET['txid'] ?? '';
    
    if (empty($txid)) {
        throw new Exception('TXID não informado');
    }
    
    // Primeiro, verificar no banco de dados local
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("SELECT status, paid_at FROM orders WHERE txid = :txid");
    $stmt->execute([':txid' => $txid]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Pedido não encontrado');
    }
    
    // Se já está pago no banco local, retornar
    if ($order['status'] === 'paid') {
        echo json_encode([
            'success' => true,
            'status' => 'paid',
            'paid_at' => $order['paid_at']
        ]);
        exit;
    }
    
    // Consultar status na API da EFI Bank
    $efi = new EfiPay();
    $charge = $efi->getCharge($txid);
    
    $status = 'pending';
    
    if (isset($charge['status'])) {
        switch ($charge['status']) {
            case 'CONCLUIDA':
                $status = 'paid';
                
                // Atualizar no banco de dados
                $stmt = $db->prepare("
                    UPDATE orders 
                    SET status = 'paid', paid_at = NOW() 
                    WHERE txid = :txid AND status = 'pending'
                ");
                $stmt->execute([':txid' => $txid]);
                break;
                
            case 'REMOVIDA_PELO_USUARIO_RECEBEDOR':
            case 'REMOVIDA_PELO_PSP':
                $status = 'cancelled';
                
                $stmt = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE txid = :txid");
                $stmt->execute([':txid' => $txid]);
                break;
        }
    }
    
    echo json_encode([
        'success' => true,
        'status' => $status,
        'efi_status' => $charge['status'] ?? null
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
