<?php

namespace TLM\LaravelAxepta\Tests;

use Orchestra\Testbench\TestCase;
use TLM\LaravelAxepta\AxeptaService;
use TLM\LaravelAxepta\AxeptaServiceProvider;
use TLM\LaravelAxepta\Data\PaymentData;
use TLM\LaravelAxepta\Exceptions\AxeptaException;

class AxeptaServiceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [AxeptaServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('axepta.merchant_id', 'TEST_MERCHANT');
        $app['config']->set('axepta.blowfish_key', 'test_blowfish_key_123456789');
        $app['config']->set('axepta.hmac_key', 'test_hmac_key_123456789');
        $app['config']->set('axepta.api_url', 'https://test.axepta.com');
        $app['config']->set('axepta.test_mode', true);
    }

    public function test_can_create_payment_url()
    {
        $service = new AxeptaService();
        
        $paymentData = new PaymentData(
            transactionId: 'TEST123',
            amount: 99.99,
            notifyUrl: 'https://example.com/notify',
            successUrl: 'https://example.com/success',
            failureUrl: 'https://example.com/failure'
        );

        $url = $service->createHostedPaymentPageUrl($paymentData);

        $this->assertStringContainsString('https://test.axepta.com', $url);
        $this->assertStringContainsString('MerchantID=TEST_MERCHANT', $url);
        $this->assertStringContainsString('Len=', $url);
        $this->assertStringContainsString('Data=', $url);
    }

    public function test_validates_payment_notification()
    {
        $service = new AxeptaService();
        
        // Mock valid notification data
        $notificationData = [
            'PayID' => 'PAY123',
            'TransID' => 'TRANS123',
            'Amount' => '9999',
            'Currency' => 'EUR',
            'MAC' => hash_hmac('sha256', 'PAY123*TRANS123*TEST_MERCHANT*9999*EUR', 'test_hmac_key_123456789')
        ];

        $isValid = $service->validatePaymentNotification($notificationData);

        $this->assertTrue($isValid);
    }

    public function test_rejects_invalid_payment_notification()
    {
        $service = new AxeptaService();
        
        // Mock invalid notification data
        $notificationData = [
            'PayID' => 'PAY123',
            'TransID' => 'TRANS123',
            'Amount' => '9999',
            'Currency' => 'EUR',
            'MAC' => 'invalid_mac'
        ];

        $isValid = $service->validatePaymentNotification($notificationData);

        $this->assertFalse($isValid);
    }

    public function test_throws_exception_for_missing_config()
    {
        $this->expectException(AxeptaException::class);
        $this->expectExceptionMessage("Axepta configuration 'merchant_id' is missing");

        config(['axepta.merchant_id' => null]);
        
        new AxeptaService();
    }

    public function test_payment_data_from_array()
    {
        $data = [
            'transaction_id' => 'TEST123',
            'amount' => 99.99,
            'notify_url' => 'https://example.com/notify',
            'success_url' => 'https://example.com/success',
            'failure_url' => 'https://example.com/failure',
            'currency' => 'EUR',
            'customer_email' => 'test@example.com',
        ];

        $paymentData = PaymentData::fromArray($data);

        $this->assertEquals('TEST123', $paymentData->transactionId);
        $this->assertEquals(99.99, $paymentData->amount);
        $this->assertEquals('EUR', $paymentData->currency);
        $this->assertEquals('test@example.com', $paymentData->customerEmail);
    }
}