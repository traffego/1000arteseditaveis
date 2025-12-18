<?php
/**
 * Classe de Integração com a API EFI Bank (PIX)
 * 
 * Documentação: https://dev.efipay.com.br/docs/api-pix
 */

class EfiPay {
    private $clientId;
    private $clientSecret;
    private $certificate;
    private $apiUrl;
    private $pixKey;
    private $accessToken;
    private $tokenExpiry;
    
    public function __construct() {
        $this->clientId = EFI_CLIENT_ID;
        $this->clientSecret = EFI_CLIENT_SECRET;
        $this->certificate = EFI_CERTIFICATE;
        $this->apiUrl = EFI_API_URL;
        $this->pixKey = EFI_PIX_KEY;
        $this->accessToken = null;
        $this->tokenExpiry = 0;
    }
    
    /**
     * Autentica na API e obtém o token de acesso
     */
    private function authenticate(): bool {
        // Verifica se o token ainda é válido
        if ($this->accessToken && time() < $this->tokenExpiry) {
            return true;
        }
        
        $url = $this->apiUrl . '/oauth/token';
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['grant_type' => 'client_credentials']),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret)
            ],
            CURLOPT_SSLCERT => $this->certificate,
            CURLOPT_SSLCERTTYPE => 'PEM',
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            throw new Exception("Erro cURL na autenticação: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Erro na autenticação EFI Bank. HTTP: $httpCode - Response: $response");
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['access_token'])) {
            throw new Exception("Token de acesso não recebido da EFI Bank");
        }
        
        $this->accessToken = $data['access_token'];
        $this->tokenExpiry = time() + ($data['expires_in'] ?? 3600) - 60; // 60s de margem
        
        return true;
    }
    
    /**
     * Faz uma requisição autenticada para a API
     */
    private function request(string $method, string $endpoint, array $data = null): array {
        $this->authenticate();
        
        $url = $this->apiUrl . $endpoint;
        
        $curl = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->accessToken
            ],
            CURLOPT_SSLCERT => $this->certificate,
            CURLOPT_SSLCERTTYPE => 'PEM',
        ];
        
        switch (strtoupper($method)) {
            case 'POST':
                $options[CURLOPT_POST] = true;
                if ($data) {
                    $options[CURLOPT_POSTFIELDS] = json_encode($data);
                }
                break;
            case 'PUT':
                $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                if ($data) {
                    $options[CURLOPT_POSTFIELDS] = json_encode($data);
                }
                break;
            case 'PATCH':
                $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
                if ($data) {
                    $options[CURLOPT_POSTFIELDS] = json_encode($data);
                }
                break;
            case 'DELETE':
                $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;
        }
        
        curl_setopt_array($curl, $options);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        if ($error) {
            throw new Exception("Erro cURL: " . $error);
        }
        
        $result = json_decode($response, true) ?? [];
        $result['_http_code'] = $httpCode;
        
        return $result;
    }
    
    /**
     * Cria uma cobrança PIX imediata (sem CPF - campo devedor é opcional)
     * 
     * @param string $nome Nome do pagador (para referência interna)
     * @param float $valor Valor da cobrança
     * @param string $descricao Descrição da cobrança
     * @return array Dados da cobrança criada
     */
    public function createCharge(string $nome, float $valor, string $descricao = ''): array {
        $body = [
            'calendario' => [
                'expiracao' => PIX_EXPIRATION
            ],
            'valor' => [
                'original' => number_format($valor, 2, '.', '')
            ],
            'chave' => $this->pixKey,
            'solicitacaoPagador' => $descricao ?: PRODUCT_DESCRIPTION,
            'infoAdicionais' => [
                [
                    'nome' => 'Cliente',
                    'valor' => $nome
                ]
            ]
        ];
        
        $response = $this->request('POST', '/v2/cob', $body);
        
        if (!isset($response['txid'])) {
            throw new Exception("Erro ao criar cobrança PIX: " . json_encode($response));
        }
        
        return $response;
    }
    
    /**
     * Consulta uma cobrança pelo txid
     */
    public function getCharge(string $txid): array {
        $response = $this->request('GET', '/v2/cob/' . $txid);
        return $response;
    }
    
    /**
     * Obtém o QR Code de uma cobrança
     */
    public function getQRCode(int $locId): array {
        $response = $this->request('GET', '/v2/loc/' . $locId . '/qrcode');
        return $response;
    }
    
    /**
     * Cria cobrança e retorna com QR Code
     */
    public function createChargeWithQRCode(string $nome, float $valor, string $descricao = ''): array {
        // Cria a cobrança
        $charge = $this->createCharge($nome, $valor, $descricao);
        
        // O QR Code já vem no campo pixCopiaECola, mas precisamos gerar a imagem
        if (isset($charge['loc']['id'])) {
            $qrcode = $this->getQRCode($charge['loc']['id']);
            $charge['qrcode'] = $qrcode['imagemQrcode'] ?? null;
        }
        
        return $charge;
    }
}
