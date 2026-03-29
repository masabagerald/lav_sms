<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class LicenseService
{
    protected $publicKey;
   
    protected $disk = 'public';
    protected $keyPath = 'license/public.pem';
    protected $licensePath = 'license/license.json';

    public function __construct()
    {
        $this->loadPublicKey();
    }

    protected function loadPublicKey()
    {
        try {
            if (!Storage::disk($this->disk)->exists($this->keyPath)) {
                Log::warning('Public key file not found', [
                    'path' => $this->keyPath,
                    'full_path' => storage_path('app/' . $this->keyPath)
                ]);
                return;
            }

            $keyContent = Storage::disk($this->disk)->get($this->keyPath);

            if (!$keyContent) {
                Log::warning('Public key file is empty');
                return;
            }

            $this->publicKey = openssl_pkey_get_public($keyContent);

            if (!$this->publicKey) {
                Log::error('Invalid public key format');
            }

        } catch (Exception $e) {
            Log::error('Failed to load public key', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function validate()
    {
        try {
            // 🔴 Check key first
            if (!$this->publicKey) {
                return $this->fail('KEY_MISSING');
            }

            // 🔴 Check license file
            if (!Storage::disk($this->disk)->exists($this->licensePath)) {
                return $this->fail('LICENSE_MISSING');
            }

            $raw = Storage::disk($this->disk)->get($this->licensePath);

            if (!$raw) {
                return $this->fail('LICENSE_UNREADABLE');
            }

            // ✅ Decode JSON
            $license = json_decode($raw, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->fail('INVALID_JSON');
            }

            if (!isset($license['data'], $license['signature'])) {
                return $this->fail('INVALID_STRUCTURE');
            }

            $data = $license['data'];
            $signature = base64_decode($license['signature'], true);

            if ($signature === false) {
                return $this->fail('INVALID_SIGNATURE_ENCODING');
            }

            // 🔐 Verify signature
            $verified = openssl_verify(
                $data,
                $signature,
                $this->publicKey,
                OPENSSL_ALGO_SHA256
            );

            if ($verified !== 1) {
                return $this->fail('SIGNATURE_FAILED');
            }

            // ✅ Decode payload
            $decoded = base64_decode($data, true);
            if ($decoded === false) {
                return $this->fail('INVALID_DATA_ENCODING');
            }

            $payload = json_decode($decoded, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->fail('INVALID_PAYLOAD');
            }

            // ✅ Required fields check
            if (!isset($payload['domain'], $payload['expires_at'])) {
                return $this->fail('INCOMPLETE_PAYLOAD');
            }

            // 🌍 Domain validation
            if (!app()->environment('local')) {
                if ($payload['domain'] !== request()->getHost()) {
                    return $this->fail('INVALID_DOMAIN');
                }
            }

            // ⏳ Expiry check
            if (now()->gt($payload['expires_at'])) {
                return $this->fail('EXPIRED');
            }

            return [
                'valid' => true,
                'data' => $payload
            ];

        } catch (Exception $e) {
            Log::error('License validation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->fail('SYSTEM_ERROR');
        }
    }

    protected function fail($code)
    {
        $messages = [
            'KEY_MISSING' => 'Public key not found. Please upload your public key.',
            'LICENSE_MISSING' => 'License not found. Please upload a valid license file.',
            'LICENSE_UNREADABLE' => 'Unable to read license file.',
            'INVALID_JSON' => 'License file is corrupted.',
            'INVALID_STRUCTURE' => 'License format is invalid.',
            'INVALID_SIGNATURE_ENCODING' => 'License is invalid.',
            'SIGNATURE_FAILED' => 'License verification failed.',
            'INVALID_DATA_ENCODING' => 'License data is invalid.',
            'INVALID_PAYLOAD' => 'License data is corrupted.',
            'INCOMPLETE_PAYLOAD' => 'License information is incomplete.',
            'EXPIRED' => 'Your license has expired. Please renew.',
            'INVALID_DOMAIN' => 'This license is not valid for this system.',
            'SYSTEM_ERROR' => 'System error while validating license.'
        ];

        Log::warning('License check failed', [
            'code' => $code
        ]);

        return [
            'valid' => false,
            'code' => $code,
            'message' => $messages[$code] ?? 'License error occurred'
        ];
    }
}