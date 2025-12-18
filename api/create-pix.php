<?php
/**
 * API - Criar cobrança PIX
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/EfiPay.php';

try {
    // Receber dados do POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }
    
    $name = trim($input['name'] ?? '');
    $whatsapp = preg_replace('/\D/', '', $input['whatsapp'] ?? '');
    
    // Validações
    if (strlen($name) < 3) {
        throw new Exception('Nome inválido');
    }
    
    if (strlen($whatsapp) < 10 || strlen($whatsapp) > 11) {
        throw new Exception('WhatsApp inválido');
    }
    
    // Criar cobrança PIX (sem CPF)
    $efi = new EfiPay();
    $charge = $efi->createChargeWithQRCode($name, PRODUCT_PRICE, PRODUCT_DESCRIPTION);
    
    if (!isset($charge['txid'])) {
        throw new Exception('Erro ao criar cobrança PIX');
    }
    
    // Salvar no banco de dados
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        INSERT INTO orders (txid, loc_id, customer_name, customer_whatsapp, amount, pix_code, qr_code_base64, status)
        VALUES (:txid, :loc_id, :name, :whatsapp, :amount, :pix_code, :qr_code, 'pending')
    ");
    
    $stmt->execute([
        ':txid' => $charge['txid'],
        ':loc_id' => $charge['loc']['id'] ?? null,
        ':name' => $name,
        ':whatsapp' => $whatsapp,
        ':amount' => PRODUCT_PRICE,
        ':pix_code' => $charge['pixCopiaECola'] ?? '',
        ':qr_code' => $charge['qrcode'] ?? ''
    ]);
    
    // Retornar dados para o frontend
    echo json_encode([
        'success' => true,
        'txid' => $charge['txid'],
        'pixCopiaECola' => $charge['pixCopiaECola'] ?? '',
        'qrcode' => $charge['qrcode'] ?? '',
        'expiration' => PIX_EXPIRATION
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
