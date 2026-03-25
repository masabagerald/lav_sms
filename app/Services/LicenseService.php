<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class LicenseService
{
    protected $publicKey;

    public function __construct()
    {

        $this->publicKey = null;

        $path = storage_path('app/license/public.pem');

        if (file_exists($path)) {
            $keyContent = file_get_contents($path);

            if ($keyContent) {
                $this->publicKey = openssl_pkey_get_public($keyContent);
            }
        }
   

       
    }

    public function validate()
    {
        try {
            $path = storage_path('app/license/license.json');

            if (!$this->publicKey) {
                return $this->fail('KEY_MISSING');
            }

            // 1. Check file existence
            if (!file_exists($path)) {
                return $this->fail('LICENSE_MISSING');
            }

            $raw = file_get_contents($path);

            if (!$raw) {
                return $this->fail('LICENSE_UNREADABLE');
            }

            // 2. Decode JSON
            $license = json_decode($raw, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->fail('INVALID_JSON');
            }

            // 3. Validate structure
            if (!isset($license['data'], $license['signature'])) {
                return $this->fail('INVALID_STRUCTURE');
            }

            $data = $license['data'];
            $signature = base64_decode($license['signature'], true);

            if ($signature === false) {
                return $this->fail('INVALID_SIGNATURE_ENCODING');
            }

            // 4. Verify signature (CRITICAL SECURITY STEP)
            $verified = openssl_verify(
                $data,
                $signature,
                $this->publicKey,
                OPENSSL_ALGO_SHA256
            );

            if ($verified !== 1) {
                return $this->fail('SIGNATURE_FAILED');
            }

            // 5. Decode base64 payload (NO AES anymore)
            $decoded = base64_decode($data, true);

            if ($decoded === false) {
                return $this->fail('INVALID_DATA_ENCODING');
            }

            $payload = json_decode($decoded, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->fail('INVALID_PAYLOAD');
            }

            // 6. Validate required fields
            $allowedDomains = [$payload['domain'], '127.0.0.1', 'localhost'];
 
            if (!in_array(request()->getHost(), $allowedDomains)) {
                return $this->fail('INVALID_DOMAIN');
            } 

            // 7. Expiry check
            if (now()->gt($payload['expires_at'])) {
                return $this->fail('EXPIRED');
            }

            // 8. Domain check

            if (app()->environment('local')) {
                // skip domain check
            } else {
                if ($payload['domain'] !== request()->getHost()) {
                    return $this->fail('INVALID_DOMAIN');
                }
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
            'code' => $code,
            'internal_message' => $this->getInternalMessage($code)
        ]);

        return [
            'valid' => false,
            'code' => $code,
            'message' => isset($messages[$code]) ? $messages[$code] : 'License error occurred'
        ];
    }

    protected function getInternalMessage($code)
    {
        $map = [
            'LICENSE_MISSING' => 'License file not found in storage',
            'LICENSE_UNREADABLE' => 'file_get_contents failed',
            'INVALID_JSON' => 'json_decode error',
            'SIGNATURE_FAILED' => 'openssl_verify returned != 1',
            'INVALID_DATA_ENCODING' => 'base64_decode failed',
            'INVALID_PAYLOAD' => 'decoded JSON invalid',
            'KEY_MISSING' => 'Public key not found. Please upload your public key.',
        ];

        return isset($map[$code]) ? $map[$code] : 'Unknown license validation error';
    }
}