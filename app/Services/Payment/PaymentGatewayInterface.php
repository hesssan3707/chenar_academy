<?php

namespace App\Services\Payment;

interface PaymentGatewayInterface
{
    /**
     * Request a payment from the gateway
     *
     * @param int $amount Amount in the primary currency (e.g. IRR)
     * @param string $description Payment description
     * @param string $email Customer email
     * @param string $mobile Customer mobile number
     * @param string $callbackUrl Callback URL after payment
     * @return array Array with 'success', 'authority', and 'url' (if success), or 'error' and 'message' (if failure)
     */
    public function requestPayment(
        int $amount,
        string $description,
        string $email,
        string $mobile,
        string $callbackUrl
    ): array;

    /**
     * Verify a payment
     *
     * @param string $authority Transaction authority from the gateway
     * @param int $amount Original payment amount
     * @return array Array with 'success', 'reference_id', 'authority' (if success), or 'error', 'message' (if failure)
     */
    public function verifyPayment(string $authority, int $amount): array;

    /**
     * Check if gateway is running in sandbox/testing mode
     */
    public function isSandbox(): bool;

    /**
     * Get the name of the gateway (e.g., 'zarinpal', 'mock')
     */
    public function getName(): string;
}
