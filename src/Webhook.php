<?php

namespace Codeplugtech\CreemPayments;

class Webhook
{
    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public static function fromRaw(string $secret): self
    {
        return new self($secret);
    }

    /**
     * @param string $payload
     * @param array $headers
     * @return array
     * @throws \Exception
     */
    public function verify(string $payload, array $headers): array
    {
        // 1. Extract the signature
        // Laravel/Symfony headers are lowercased automatically in headers->all()
        $signatureHeader = $headers['creem-signature']
            ?? $headers['Creem-Signature']
            ?? null;

        // --- FIX STARTS HERE ---
        // If the header is an array (common in Laravel), take the first value.
        if (is_array($signatureHeader)) {
            $signatureHeader = $signatureHeader[0] ?? null;
        }
        // --- FIX ENDS HERE ---

        if (empty($signatureHeader) || !is_string($signatureHeader)) {
            throw new \Exception("Missing or invalid 'creem-signature' header");
        }

        // 2. Generate the expected signature
        // Creem uses HMAC-SHA256 (Hex)
        $expectedSignature = hash_hmac('sha256', $payload, $this->secret);

        // 3. Compare signatures
        if (!hash_equals($expectedSignature, $signatureHeader)) {
            throw new \Exception("Invalid signature");
        }

        // 4. Return decoded payload
        $data = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON payload");
        }

        return $data;
    }
}
