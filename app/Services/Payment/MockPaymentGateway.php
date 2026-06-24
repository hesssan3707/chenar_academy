<?php

namespace App\Services\Payment;

use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Request a payment from Mock Gateway
     */
    public function requestPayment(
        int $amount,
        string $description,
        string $email,
        string $mobile,
        string $callbackUrl
    ): array {
        $authority = 'MOCK-' . Str::random(16);

        // Save the callback URL in session or pass it via URL
        // We'll pass it to our fake payment controller via query parameters or let the controller handle it.
        // Actually, we can encode it in the URL.
        $url = route('fake-gateway.show', [
            'amount' => $amount,
            'authority' => $authority,
            'callback' => urlencode($callbackUrl)
        ]);

        return [
            'success' => true,
            'authority' => $authority,
            'url' => $url,
        ];
    }

    /**
     * Verify a mock payment
     */
    public function verifyPayment(string $authority, int $amount): array
    {
        if (str_starts_with($authority, 'MOCK-')) {
            return [
                'success' => true,
                'reference_id' => 'REF-' . Str::random(8),
                'authority' => $authority,
            ];
        }

        return [
            'success' => false,
            'error' => 'invalid_authority',
            'message' => 'The provided authority is not a valid mock authority.',
        ];
    }

    /**
     * Check if gateway is running in sandbox/testing mode
     */
    public function isSandbox(): bool
    {
        return true;
    }

    /**
     * Get the name of the gateway
     */
    public function getName(): string
    {
        return 'mock';
    }
}
