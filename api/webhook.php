<?php
/**
 * Webhook - Receber notificações de pagamento da EFI Bank
 * 
 * Configure esta URL na dashboard da EFI Bank:
 * https://1000artes.traffego.xyz/api/webhook.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';

// Log do webhook para debug
function logWebhook($txid, $payload) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO webhook_logs (txid, payload) VALUES (:txid, :payload)");
        $stmt->execute([
            ':txid' => $txid,
            ':payload' => json_encode($payload)
        ]);
    } catch (Exception $e) {
        error_log("Erro ao logar webhook: " . $e->getMessage());
    }
}

try {
    // Receber payload do webhook
    $payload = json_decode(file_get_contents('php://input'), true);
    
    if (!$payload) {
        http_response_code(400);
        exit;
    }
    
    // A EFI Bank envia notificações no formato:
    // { "pix": [{ "endToEndId": "...", "txid": "...", "valor": "...", "horario": "..." }] }
    
    if (isset($payload['pix']) && is_array($payload['pix'])) {
        $db = Database::getInstance()->getConnection();
        
        foreach ($payload['pix'] as $pix) {
            $txid = $pix['txid'] ?? null;
            
            if ($txid) {
                // Logar webhook
                logWebhook($txid, $pix);
                
                // Atualizar status do pedido para pago
                $stmt = $db->prepare("
                    UPDATE orders 
                    SET status = 'paid', paid_at = NOW() 
                    WHERE txid = :txid AND status = 'pending'
                ");
                $stmt->execute([':txid' => $txid]);
                
                error_log("Pagamento confirmado via webhook: $txid");
            }
        }
    }
    
    // Responder com sucesso para a EFI Bank
    http_response_code(200);
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Erro no webhook: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
