-- Database: checkout_1000artes
-- Execute este script no MySQL para criar a estrutura necessária

CREATE DATABASE IF NOT EXISTS checkout_1000artes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE checkout_1000artes;

-- Tabela de pedidos
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    txid VARCHAR(100) UNIQUE NOT NULL,
    loc_id INT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_whatsapp VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 9.90,
    pix_code TEXT COMMENT 'Código copia e cola do PIX',
    qr_code_base64 LONGTEXT COMMENT 'QR Code em base64',
    status ENUM('pending', 'paid', 'expired', 'cancelled') DEFAULT 'pending',
    paid_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_txid (txid),
    INDEX idx_status (status),
    INDEX idx_whatsapp (customer_whatsapp),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de logs de webhook (opcional, para debug)
CREATE TABLE IF NOT EXISTS webhook_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    txid VARCHAR(100),
    payload JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_txid (txid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
