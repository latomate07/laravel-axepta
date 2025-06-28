<?php

namespace TLM\LaravelAxepta\Facades;

use Illuminate\Support\Facades\Facade;
use TLM\LaravelAxepta\AxeptaService;

/**
 * @method static string createHostedPaymentPageUrl(\TLM\LaravelAxepta\Data\PaymentData $paymentData)
 * @method static bool validatePaymentNotification(array $data)
 * @method static string decryptBlowfish(string $encryptedData)
 * 
 * @see \TLM\LaravelAxepta\AxeptaService
 */
class Axepta extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AxeptaService::class;
    }
}