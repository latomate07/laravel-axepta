<?php

namespace TLM\LaravelAxepta\Data;

class PaymentData
{
    public function __construct(
        public readonly string|int $transactionId,
        public readonly float $amount,
        public readonly string $notifyUrl,
        public readonly string $successUrl,
        public readonly string $failureUrl,
        public readonly ?string $currency = null,
        public readonly ?string $cancelUrl = null,
        public readonly ?string $customerEmail = null,
        public readonly ?string $orderDescription = null,
        public readonly bool $isTest = false,
    ) {}

    /**
     * Create PaymentData from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: $data['transaction_id'],
            amount: $data['amount'],
            notifyUrl: $data['notify_url'],
            successUrl: $data['success_url'],
            failureUrl: $data['failure_url'],
            currency: $data['currency'] ?? null,
            cancelUrl: $data['cancel_url'] ?? null,
            customerEmail: $data['customer_email'] ?? null,
            orderDescription: $data['order_description'] ?? null,
            isTest: $data['is_test'] ?? false,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'amount' => $this->amount,
            'notify_url' => $this->notifyUrl,
            'success_url' => $this->successUrl,
            'failure_url' => $this->failureUrl,
            'currency' => $this->currency,
            'cancel_url' => $this->cancelUrl,
            'customer_email' => $this->customerEmail,
            'order_description' => $this->orderDescription,
            'is_test' => $this->isTest,
        ];
    }
}