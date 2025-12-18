<?php
/**
 * Configurações do Sistema - Checkout 1000 Artes
 */

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'platafo5_1000artes');
define('DB_USER', 'platafo5_1000artes');
define('DB_PASS', 'Traffego444#');
define('DB_CHARSET', 'utf8mb4');

// Configurações da API EFI Bank (Produção)
define('EFI_CLIENT_ID', 'Client_Id_b187616fae15e359c208232bf5782c6ebde142f7');
define('EFI_CLIENT_SECRET', 'Client_Secret_6cd88e6a8cb4dc2254c97926b4cbfd42c96711b6');
define('EFI_PIX_KEY', 'e5a0a896-3d52-4146-87ea-b0da489d664e');
define('EFI_CERTIFICATE', __DIR__ . '/../pagamentos.pem');

// URLs da API EFI Bank
define('EFI_API_URL', 'https://pix.api.efipay.com.br'); // Produção
// define('EFI_API_URL', 'https://pix-h.api.efipay.com.br'); // Homologação

// Configurações do Produto
define('PRODUCT_NAME', '1000 Artes Editáveis no Canva');
define('PRODUCT_PRICE', 0.01);
define('PRODUCT_DESCRIPTION', 'Pack com 1000 artes editáveis para Canva');

// Configurações do Sistema
define('SITE_URL', 'https://1000artes.traffego.xyz');
define('SITE_NAME', '1000 Artes Editáveis');

// Caminho do arquivo para download
define('DOWNLOAD_FILE', __DIR__ . '/../1000arteseditaveis.pdf');
define('DOWNLOAD_FILENAME', '1000arteseditaveis.pdf');

// Tempo de expiração do PIX em segundos (1 hora)
define('PIX_EXPIRATION', 3600);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Error reporting (desabilitar em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);
