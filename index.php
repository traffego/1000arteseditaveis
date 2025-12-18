<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="1000 Artes Editáveis no Canva por apenas R$ 9,90. Pack completo para Feed, Stories, Anúncios e Ofertas.">
    <title>1000 Artes Editáveis no Canva - Compre Agora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <div class="hero">
            <h1 class="hero-title">📦 1000 Artes Editáveis</h1>
            <p class="hero-subtitle">Pack completo para Canva • Feed, Stories, Anúncios e Ofertas</p>
            
            <div class="trust-badges">
                <div class="trust-badge">
                    <span class="trust-icon">🔒</span>
                    <span>Site Seguro</span>
                </div>
                <div class="trust-badge">
                    <span class="trust-icon">⚡</span>
                    <span>Entrega Imediata</span>
                </div>
                <div class="trust-badge">
                    <span class="trust-icon">🏢</span>
                    <span>CNPJ Ativo</span>
                </div>
                <div class="trust-badge">
                    <span class="trust-icon">✅</span>
                    <span>Garantia 7 dias</span>
                </div>
            </div>
        </div>
        
        <!-- Checkout Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Finalizar Compra</h2>
                <p class="card-description">Preencha seus dados para receber o acesso</p>
            </div>
            
            <!-- Product Info -->
            <div class="product-info">
                <span class="product-name">Pack 1000 Artes Editáveis</span>
                <span class="product-price">R$ 9,90 <small>PIX</small></span>
            </div>
            
            <!-- Form -->
            <form id="checkoutForm" autocomplete="on">
                <div class="form-group">
                    <label class="form-label" for="name">Nome Completo</label>
                    <input type="text" id="name" name="name" class="form-input" 
                           placeholder="Digite seu nome completo" required autocomplete="name">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="whatsapp">WhatsApp</label>
                    <input type="tel" id="whatsapp" name="whatsapp" class="form-input" 
                           placeholder="(00) 00000-0000" maxlength="15" required autocomplete="tel">
                </div>
                
                <button type="submit" id="submitBtn" class="btn">
                    🔒 Pagar com PIX - R$ 9,90
                </button>
                
                <!-- Info box -->
                <div class="info-box">
                    <p>📲 <strong>Importante:</strong> O link para download aparecerá na tela assim que o pagamento for confirmado. Entrega imediata!</p>
                </div>
                
                <div class="security-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span>Pagamento 100% seguro via PIX</span>
                </div>
            </form>
        </div>
        
        <!-- Footer -->
        <footer class="footer">
            <div class="company-info">
                <p><strong>Traffego - Assessoria de Marketing Ltda</strong></p>
                <p>CNPJ: 46.143.888/0001-12</p>
            </div>
            <p>© 2024 Traffego. Todos os direitos reservados.</p>
        </footer>
    </div>
    
    <!-- PIX Modal -->
    <div id="pixModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Pague com PIX</h2>
            </div>
            
            <div class="qr-container">
                <div class="qr-code">
                    <img id="qrCodeImg" src="" alt="QR Code PIX">
                </div>
                <p style="font-size: 0.875rem; color: hsl(var(--muted-foreground));">
                    Escaneie o QR Code com seu app do banco
                </p>
            </div>
            
            <div class="pix-copy">
                <p class="pix-copy-label">Ou copie o código PIX:</p>
                <p id="pixCode" class="pix-copy-code"></p>
                <button type="button" id="copyBtn" class="btn btn-secondary btn-copy">
                    📋 Copiar código PIX
                </button>
            </div>
            
            <div id="statusContainer" class="status-container">
                <div class="status-icon pending">⏳</div>
                <p class="status-text">Aguardando pagamento...</p>
                <p style="font-size: 0.75rem; color: hsl(var(--muted-foreground)); margin-top: 0.5rem;">
                    O link para download aparecerá aqui automaticamente
                </p>
            </div>
        </div>
    </div>
    
    <!-- Toast -->
    <div id="toast" class="toast"></div>
    
    <script src="js/app.js"></script>
</body>
</html>
