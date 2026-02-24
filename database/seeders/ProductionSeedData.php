<?php

namespace Database\Seeders;

final class ProductionSeedData
{
    public static function customersCsv(): string
    {
        return self::decodeGzipBase64Chunks(self::chunks('customers'));
    }

    public static function productsCsv(): string
    {
        return self::decodeGzipBase64Chunks(self::chunks('products'));
    }

    public static function ordersCsv(): string
    {
        return self::decodeGzipBase64Chunks(self::chunks('orders'));
    }

    private static function chunks(string $key): array
    {
        $payloadPath = __DIR__.DIRECTORY_SEPARATOR.'ProductionSeedDataPayload.php';
        if (! is_file($payloadPath)) {
            throw new \RuntimeException('Production seed payload file is missing.');
        }

        $payload = require $payloadPath;
        if (! is_array($payload)) {
            throw new \RuntimeException('Invalid production seed payload.');
        }

        $chunks = $payload[$key] ?? null;
        if (! is_array($chunks) || $chunks === []) {
            throw new \RuntimeException('Production seed data is missing.');
        }

        return $chunks;
    }

    private static function decodeGzipBase64Chunks(array $chunks): string
    {
        if ($chunks === []) {
            throw new \RuntimeException('Production seed data is missing.');
        }

        $b64 = implode('', $chunks);
        $compressed = base64_decode($b64, true);
        if (! is_string($compressed)) {
            throw new \RuntimeException('Invalid production seed data encoding.');
        }

        $decoded = gzdecode($compressed);
        if (! is_string($decoded)) {
            throw new \RuntimeException('Invalid production seed data compression.');
        }

        return $decoded;
    }
}
