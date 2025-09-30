<?php
/**
 * QiGateway Service (Placeholder)
 * Integrates with Qi Card (Mastercard) payment API in Iraq.
 *
 * NOTE: This is a minimal scaffold. Please supply the real API base URL,
 * merchant credentials, and signature verification details.
 */

class QiGateway {
    private $apiUrl;
    private $merchantId;
    private $terminalId;
    private $secret;
    private $mode; // test|live

    public function __construct() {
        $this->apiUrl = rtrim((string)(defined('QI_API_URL') ? QI_API_URL : ''), '/');
        $this->merchantId = defined('QI_MERCHANT_ID') ? QI_MERCHANT_ID : '';
        $this->terminalId = defined('QI_TERMINAL_ID') ? QI_TERMINAL_ID : '';
        $this->secret = defined('QI_SECRET') ? QI_SECRET : '';
        $this->mode = defined('QI_MODE') ? QI_MODE : 'test';
    }

    public function isConfigured(): bool {
        return $this->apiUrl && $this->merchantId && $this->terminalId && $this->secret;
    }

    /**
     * Create a hosted payment session/order.
     * Returns array with keys: ok(bool), redirect_url(string), gateway_ref(string), raw(array)
     */
    public function createPaymentSession(array $payload): array {
        // TODO: Replace with actual Qi API fields and signature process.
        // The below is a placeholder to allow wiring the flows.
        $fakeRef = 'QI-' . time() . '-' . rand(1000,9999);
        return [
            'ok' => true,
            'redirect_url' => ($payload['return_url'] ?? '/') . '?mock_qi_ref=' . urlencode($fakeRef),
            'gateway_ref' => $fakeRef,
            'raw' => ['note' => 'Placeholder Qi integration']
        ];
    }

    /**
     * Verify webhook/callback signature (placeholder)
     */
    public function verifySignature(array $headers, string $body): bool {
        // TODO: Implement HMAC or signature verification based on Qi docs.
        return true;
    }
}

