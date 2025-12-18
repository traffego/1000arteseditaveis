/**
 * Checkout 1000 Artes - JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('checkoutForm');
    const whatsappInput = document.getElementById('whatsapp');
    const submitBtn = document.getElementById('submitBtn');
    const modal = document.getElementById('pixModal');
    const qrCodeImg = document.getElementById('qrCodeImg');
    const pixCodeText = document.getElementById('pixCode');
    const copyBtn = document.getElementById('copyBtn');
    const statusContainer = document.getElementById('statusContainer');

    let currentTxid = null;
    let checkPaymentInterval = null;

    // Máscara de WhatsApp
    whatsappInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');

        if (value.length <= 11) {
            if (value.length > 2) {
                value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
            }
            if (value.length > 10) {
                value = value.substring(0, 10) + '-' + value.substring(10);
            }
        }

        e.target.value = value;
    });

    // Validação de WhatsApp
    function validateWhatsApp(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 10 && cleaned.length <= 11;
    }

    // Mostrar erro no campo
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        input.classList.add('error');
        let errorEl = formGroup.querySelector('.form-error');
        if (!errorEl) {
            errorEl = document.createElement('span');
            errorEl.className = 'form-error';
            formGroup.appendChild(errorEl);
        }
        errorEl.textContent = message;
    }

    // Limpar erro do campo
    function clearError(input) {
        const formGroup = input.closest('.form-group');
        input.classList.remove('error');
        const errorEl = formGroup.querySelector('.form-error');
        if (errorEl) errorEl.remove();
    }

    // Submit do formulário
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const name = document.getElementById('name').value.trim();
        const whatsapp = whatsappInput.value.trim();

        let hasError = false;

        // Validações
        clearError(document.getElementById('name'));
        clearError(whatsappInput);

        if (name.length < 3) {
            showError(document.getElementById('name'), 'Digite seu nome completo');
            hasError = true;
        }

        if (!validateWhatsApp(whatsapp)) {
            showError(whatsappInput, 'Digite um WhatsApp válido');
            hasError = true;
        }

        if (hasError) return;

        // Desabilitar botão e mostrar loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner"></span> Gerando PIX...';

        try {
            const response = await fetch('api/create-pix.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name, whatsapp })
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.error || 'Erro ao gerar PIX');
            }

            // Exibir modal com QR Code
            currentTxid = data.txid;
            qrCodeImg.src = data.qrcode;
            pixCodeText.textContent = data.pixCopiaECola;

            modal.classList.add('active');

            // Iniciar verificação de pagamento
            startPaymentCheck();

        } catch (error) {
            showToast(error.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '🔒 Pagar com PIX - R$ 9,90';
        }
    });

    // Copiar código PIX
    copyBtn.addEventListener('click', async function () {
        try {
            await navigator.clipboard.writeText(pixCodeText.textContent);
            showToast('Código PIX copiado!', 'success');
            copyBtn.textContent = '✓ Copiado!';
            setTimeout(() => {
                copyBtn.textContent = '📋 Copiar código PIX';
            }, 2000);
        } catch (err) {
            // Fallback para navegadores antigos
            const textArea = document.createElement('textarea');
            textArea.value = pixCodeText.textContent;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast('Código PIX copiado!', 'success');
        }
    });

    // Fechar modal ao clicar fora
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            // Não fechar enquanto aguarda pagamento
        }
    });

    // Verificar pagamento periodicamente
    function startPaymentCheck() {
        if (checkPaymentInterval) {
            clearInterval(checkPaymentInterval);
        }

        statusContainer.innerHTML = `
            <div class="status-icon pending">⏳</div>
            <p class="status-text">Aguardando pagamento...</p>
            <p style="font-size: 0.75rem; color: hsl(var(--muted-foreground)); margin-top: 0.5rem;">
                O link para download aparecerá aqui automaticamente
            </p>
        `;

        checkPaymentInterval = setInterval(async () => {
            try {
                const response = await fetch(`api/check-payment.php?txid=${currentTxid}`);
                const data = await response.json();

                if (data.status === 'paid') {
                    clearInterval(checkPaymentInterval);

                    statusContainer.innerHTML = `
                        <div class="status-icon success">✓</div>
                        <p class="status-text">Pagamento confirmado! Redirecionando...</p>
                    `;

                    setTimeout(() => {
                        window.location.href = `success.php?txid=${currentTxid}`;
                    }, 2000);
                }
            } catch (error) {
                console.error('Erro ao verificar pagamento:', error);
            }
        }, 3000); // Verifica a cada 3 segundos
    }

    // Toast notification
    function showToast(message, type = 'info') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'toast ' + type;
        toast.classList.add('active');

        setTimeout(() => {
            toast.classList.remove('active');
        }, 3000);
    }
});
