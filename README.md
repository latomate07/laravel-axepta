# Laravel Axepta

A Laravel package for BNP Paribas Axepta payment gateway integration.

## Installation

Install the package via composer:

```bash
composer require tlm/laravel-axepta
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=axepta-config
```

## Configuration

Add the following environment variables to your `.env` file:

```env
AXEPTA_API_URL=https://paymentpage.axepta.bnpparibas/paymentpage.aspx
AXEPTA_MERCHANT_ID=your-merchant-id
AXEPTA_BLOWFISH_KEY=your-blowfish-key
AXEPTA_HMAC_KEY=your-hmac-key
AXEPTA_TEST_MODE=true
AXEPTA_DEFAULT_CURRENCY=EUR
```

## Usage

### Creating a Payment URL

```php
use TLM\LaravelAxepta\Facades\Axepta;
use TLM\LaravelAxepta\Data\PaymentData;

$paymentData = new PaymentData(
    transactionId: 'TXN123',
    amount: 99.99,
    notifyUrl: route('payment.notify'),
    successUrl: route('payment.success'),
    failureUrl: route('payment.failure'),
    currency: 'EUR',
    customerEmail: 'customer@example.com',
    orderDescription: 'Order #123'
);

$paymentUrl = Axepta::createHostedPaymentPageUrl($paymentData);

// Redirect user to payment page
return redirect($paymentUrl);
```

### Handling Payment Notifications

Create a route to handle payment notifications:

```php
Route::post('/payment/notify', function (Request $request) {
    $isValid = Axepta::validatePaymentNotification($request->all());
    
    if ($isValid) {
        $status = $request->input('Status');
        $transactionId = $request->input('TransID');
        
        if ($status === 'SUCCESS') {
            // Payment successful - update your database
            // Update order status, send confirmation emails, etc.
        } else {
            // Payment failed - handle accordingly
        }
        
        return response()->json(['status' => 'success'], 200);
    }
    
    return response()->json(['status' => 'error'], 400);
})->name('payment.notify');
```

### Success and Failure Handlers

```php
Route::get('/payment/success', function (Request $request) {
    // Handle successful payment
    return view('payment.success');
})->name('payment.success');

Route::get('/payment/failure', function (Request $request) {
    // Handle failed payment
    return view('payment.failure');
})->name('payment.failure');
```

### Using PaymentData from Array

```php
$paymentData = PaymentData::fromArray([
    'transaction_id' => 'TXN123',
    'amount' => 99.99,
    'notify_url' => route('payment.notify'),
    'success_url' => route('payment.success'),
    'failure_url' => route('payment.failure'),
    'currency' => 'EUR',
    'customer_email' => 'customer@example.com',
    'order_description' => 'Order #123',
    'is_test' => false,
]);
```

## Configuration Options

The package supports the following configuration options:

- `api_url` - Axepta API endpoint URL
- `merchant_id` - Your merchant ID provided by BNP Paribas
- `blowfish_key` - Blowfish encryption key
- `hmac_key` - HMAC signature key
- `test_mode` - Enable/disable test mode
- `default_currency` - Default currency (EUR)
- `message_version` - API message version (2.0)

## Security

- All payment data is encrypted using Blowfish encryption
- HMAC signatures are used to verify payment notifications
- Sensitive configuration data should be stored in environment variables

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover a security vulnerability, please send an e-mail to lmtahirou@gmail.com .

## See BNP Paribas Axepta Official Documentation
[Official Documentation](https://docs.axepta.bnpparibas/display/DOCBNP/Premiers+pas+avec+AXEPTA+BNP+Paribas)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.