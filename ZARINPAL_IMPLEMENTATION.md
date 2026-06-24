# Zarinpal Payment Gateway Implementation

## Overview
This document describes the implementation of Zarinpal payment gateway in the Chenar application with automatic switching between Sandbox (development) and Production modes.

## Installation & Configuration

### 1. Environment Variables
Add the following to your `.env` file:

```env
# Zarinpal Payment Gateway
ZARINPAL_MERCHANT_ID=your_merchant_id_here
```

For testing in development/sandbox mode, you can use a test merchant ID provided by Zarinpal.

### 2. Package Installation
The Zarinpal SDK has been installed via Composer:
```bash
composer require zarinpal/zarinpal-php-sdk
```

## Architecture

### Gateway Service
- **Location**: `app/Services/Payment/ZarinpalGateway.php`
- **Purpose**: Handles all Zarinpal API interactions

### Key Features

#### Automatic Environment Detection
- **Production (APP_ENV=production)**: Uses Zarinpal real mode
- **Development/Local (APP_ENV!=production)**: Uses Zarinpal sandbox mode

#### Methods

1. **requestPayment()** - Initiates a payment request
   ```php
   $gateway = new ZarinpalGateway();
   $result = $gateway->requestPayment(
       amount: 100000,           // Amount in IRR
       description: 'Order #123',
       email: 'customer@example.com',
       mobile: '09120000000',
       callbackUrl: 'https://example.com/callback'
   );
   ```

2. **verifyPayment()** - Verifies payment after callback
   ```php
   $result = $gateway->verifyPayment(
       authority: 'A00000000000000000000000000000',
       amount: 100000
   );
   ```

3. **isSandbox()** - Check current environment mode
   ```php
   if ($gateway->isSandbox()) {
       // Running in sandbox mode
   }
   ```

### Controller Methods

#### CheckoutController

1. **pay()** - Initiates checkout process
   - Creates order and payment records
   - Routes to mock gateway in development
   - Routes to Zarinpal in production

2. **requestZarinpalPayment()** - Sends payment request to Zarinpal
   - Calls Zarinpal API
   - Stores authority in payment record
   - Redirects user to Zarinpal payment page

3. **zarinpalCallback()** - Handles Zarinpal callback
   - Verifies payment with Zarinpal
   - Updates payment and order status
   - Grants product access
   - Handles coupon redemption
   - Clears shopping cart

## Payment Flow

### Development (Mock Gateway)
1. User initiates checkout
2. Mock gateway page is displayed
3. User selects success/fail option
4. Payment is immediately marked as paid/failed

### Production (Zarinpal)
1. User initiates checkout
2. Payment request sent to Zarinpal
3. User redirected to Zarinpal payment page
4. User completes payment on Zarinpal
5. Zarinpal redirects to callback URL with Authority and Status
6. Server verifies payment with Zarinpal
7. Payment marked as paid, order confirmed, access granted

## Error Handling

All Zarinpal errors are translated to user-friendly Persian messages:

- `-1` - اطلاعات ارسالی نامعتبر است
- `-2` - IP و یا مرچنت درست نیست
- `-3` - محدودیت های موجود
- `-4` - آدرس IP ثبت نشده است
- And 16+ more specific errors...

## Database Changes

### Payment Model
The `payments` table now stores:
- `gateway`: 'zarinpal' for Zarinpal payments, 'mock' for development
- `authority`: Transaction authority from Zarinpal
- `reference_id`: Reference ID from Zarinpal verification
- `meta`: Additional data including sandbox mode flag

## Routes

### New Zarinpal Route
- `GET /checkout/zarinpal/{payment}/callback` - Handles Zarinpal callback

### Existing Routes (Updated)
- `POST /checkout/pay` - Now supports Zarinpal in production

## Testing Zarinpal

### Sandbox Testing
1. Use test Merchant ID from Zarinpal
2. Set `APP_ENV=local` or `APP_ENV=testing`
3. Zarinpal SDK automatically uses sandbox API

### Production Setup
1. Register with Zarinpal and get Merchant ID
2. Set `APP_ENV=production`
3. Update `ZARINPAL_MERCHANT_ID` in `.env`
4. Ensure callback URL is publicly accessible

## Zarinpal Test Cards (Sandbox)
- Card Number: `6219861061827451`
- Expiry: Any future date
- CVV: Any 3 digits

[Check Zarinpal documentation for current test cards](https://www.zarinpal.com/docs/)

## Admin Panel Updates

The admin payment views now display:
- 'درگاه زرین‌پال' (Zarinpal Gateway) for Zarinpal payments
- 'درگاه آزمایشی' (Mock Gateway) for development
- 'کارت‌به‌کارت' (Card to Card) for manual transfers

## Security Notes

1. **Authority Storage**: Zarinpal authority is stored in payment record for verification
2. **User Verification**: Callback verifies payment belongs to authenticated user
3. **Amount Verification**: Payment verification confirms amount matches original request
4. **HTTPS**: Ensure callback URL uses HTTPS in production

## Troubleshooting

### Payment Request Fails
- Verify `ZARINPAL_MERCHANT_ID` is correct
- Check if callback URL is publicly accessible
- Verify internet connection and Zarinpal API availability

### Verification Fails
- Ensure authority and amount match original request
- Check that payment is in 'initiated' status
- Verify user is authenticated

### Sandbox Not Working
- Verify `APP_ENV` is set correctly
- Check test card number format
- Review Zarinpal error messages in payment meta

## Future Enhancements

1. Webhook support for async verification
2. Refund functionality
3. Payment retry logic
4. Admin refund management
5. Invoice generation for payments
