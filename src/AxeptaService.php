<?php

namespace TLM\LaravelAxepta;

use phpseclib3\Crypt\Blowfish;
use TLM\LaravelAxepta\Exceptions\AxeptaException;
use TLM\LaravelAxepta\Data\PaymentData;

class AxeptaService
{
    protected string $merchantId;
    protected string $blowfishKey;
    protected string $hmacKey;
    protected string $apiUrl;
    protected bool $testMode;
    protected string $defaultCurrency;
    protected string $messageVersion;

    public function __construct()
    {
        $this->merchantId = config('axepta.merchant_id');
        $this->blowfishKey = config('axepta.blowfish_key');
        $this->hmacKey = config('axepta.hmac_key');
        $this->apiUrl = config('axepta.api_url');
        $this->testMode = config('axepta.test_mode', false);
        $this->defaultCurrency = config('axepta.default_currency', 'EUR');
        $this->messageVersion = config('axepta.message_version', '2.0');

        $this->validateConfiguration();
    }

    /**
     * Create hosted payment page URL
     */
    public function createHostedPaymentPageUrl(PaymentData $paymentData): string
    {
        $parameters = $this->buildPaymentParameters($paymentData);
        
        // Calculate HMAC
        $hmacString = implode('*', [
            '',
            $parameters['TransID'],
            $parameters['MerchantID'],
            $parameters['Amount'],
            $parameters['Currency']
        ]);
        $parameters['MAC'] = $this->generateHmac($hmacString);

        // Encrypt data using Blowfish
        $dataString = http_build_query($parameters, '', '&');
        $encryptedData = $this->encryptBlowfish($dataString);
        $len = strlen($dataString);

        // Build the URL
        return "{$this->apiUrl}?MerchantID={$this->merchantId}&Len={$len}&Data={$encryptedData}";
    }

    /**
     * Validate payment notification
     */
    public function validatePaymentNotification(array $data): bool
    {
        $requiredKeys = ['PayID', 'TransID', 'Amount', 'Currency', 'MAC'];
        
        foreach ($requiredKeys as $key) {
            if (empty($data[$key])) {
                return false;
            }
        }

        $hmacString = implode('*', [
            $data['PayID'],
            $data['TransID'],
            $this->merchantId,
            $data['Amount'],
            $data['Currency']
        ]);

        $calculatedMAC = $this->generateHmac($hmacString);
        return hash_equals($calculatedMAC, $data['MAC']);
    }

    /**
     * Build payment parameters array
     */
    protected function buildPaymentParameters(PaymentData $paymentData): array
    {
        $parameters = [
            'MerchantID' => $this->merchantId,
            'MsgVer' => $this->messageVersion,
            'TransID' => $paymentData->transactionId,
            'RefNr' => str_pad((string)$paymentData->transactionId, 12, '0', STR_PAD_LEFT),
            'Amount' => (int) ($paymentData->amount * 100), // Amount in cents
            'Currency' => $paymentData->currency ?? $this->defaultCurrency,
            'URLNotify' => $paymentData->notifyUrl,
            'URLSuccess' => $paymentData->successUrl,
            'URLFailure' => $paymentData->failureUrl,
            'OrderDesc' => $this->getOrderDescription($paymentData),
        ];

        // Add optional parameters
        if ($paymentData->cancelUrl) {
            $parameters['URLCancel'] = $paymentData->cancelUrl;
        }

        if ($paymentData->customerEmail) {
            $parameters['CustomerEmail'] = $paymentData->customerEmail;
        }

        return $parameters;
    }

    /**
     * Get order description
     */
    protected function getOrderDescription(PaymentData $paymentData): string
    {
        if ($this->testMode || $paymentData->isTest) {
            return 'Test:0000';
        }

        return $paymentData->orderDescription ?? "Payment #{$paymentData->transactionId}";
    }

    /**
     * Generate HMAC signature
     */
    protected function generateHmac(string $data): string
    {
        return hash_hmac('sha256', $data, $this->hmacKey);
    }

    /**
     * Encrypt data using Blowfish
     */
    protected function encryptBlowfish(string $data): string
    {
        $paddedData = $this->pkcs5Pad($data, 8);
        $key = str_pad($this->blowfishKey, 72, $this->blowfishKey);

        $blowfish = new Blowfish('ecb');
        $blowfish->setKey($key);

        $encryptedData = $blowfish->encrypt($paddedData);

        if ($encryptedData === false) {
            throw new AxeptaException('Blowfish encryption failed');
        }

        return bin2hex($encryptedData);
    }

    /**
     * Decrypt data using Blowfish
     */
    public function decryptBlowfish(string $encryptedData): string
    {
        $key = str_pad($this->blowfishKey, 72, $this->blowfishKey);

        $blowfish = new Blowfish('ecb');
        $blowfish->setKey($key);

        $decryptedData = $blowfish->decrypt(hex2bin($encryptedData));
        
        if ($decryptedData === false) {
            throw new AxeptaException('Blowfish decryption failed');
        }

        return $this->pkcs5Unpad($decryptedData);
    }

    /**
     * Apply PKCS5 padding
     */
    protected function pkcs5Pad(string $data, int $blockSize): string
    {
        $pad = $blockSize - (strlen($data) % $blockSize);
        return $data . str_repeat(chr($pad), $pad);
    }

    /**
     * Remove PKCS5 padding
     */
    protected function pkcs5Unpad(string $data): string
    {
        $pad = ord($data[strlen($data) - 1]);
        return substr($data, 0, -$pad);
    }

    /**
     * Validate configuration
     */
    protected function validateConfiguration(): void
    {
        $requiredConfigs = [
            'merchant_id' => $this->merchantId,
            'blowfish_key' => $this->blowfishKey,
            'hmac_key' => $this->hmacKey,
            'api_url' => $this->apiUrl,
        ];

        foreach ($requiredConfigs as $key => $value) {
            if (empty($value)) {
                throw new AxeptaException("Axepta configuration '{$key}' is missing");
            }
        }
    }
}