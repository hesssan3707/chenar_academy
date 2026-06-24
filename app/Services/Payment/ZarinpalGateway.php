<?php

namespace App\Services\Payment;

use ZarinPal\Sdk\ZarinPal;
use ZarinPal\Sdk\Options;
use ZarinPal\Sdk\Endpoint\PaymentGateway\RequestTypes\RequestRequest;
use ZarinPal\Sdk\Endpoint\PaymentGateway\RequestTypes\VerifyRequest;
use ZarinPal\Sdk\HttpClient\Exception\PaymentGatewayException;
use Exception;
use Illuminate\Support\Facades\Log;

class ZarinpalGateway implements PaymentGatewayInterface
{
    protected ZarinPal $zarinpal;
    protected bool $isSandbox;

    public function __construct(bool $isSandbox = false)
    {
        $merchantId = config('services.zarinpal.merchant_id');
        $this->isSandbox = $isSandbox;

        $options = new Options([
            'merchant_id' => $merchantId,
            'sandbox' => $this->isSandbox
        ]);

        $this->zarinpal = new ZarinPal($options);
    }

    /**
     * Request a payment from Zarinpal
     *
     * @param int $amount Amount in Iranian Rial (IRR)
     * @param string $description Payment description
     * @param string $email Customer email
     * @param string $mobile Customer mobile number
     * @param string $callbackUrl Callback URL after payment
     * @return array Array with 'authority' and 'url' keys
     * @throws Exception
     */
    public function requestPayment(
        int $amount,
        string $description,
        string $email,
        string $mobile,
        string $callbackUrl
    ): array {
        try {
            $request = new RequestRequest([
                'amount' => $amount,
                'description' => $description,
                'email' => $email,
                'mobile' => $mobile,
                'callback_url' => $callbackUrl,
            ]);

            $response = $this->zarinpal->paymentGateway()->request($request);

            return [
                'success' => true,
                'authority' => $response->authority,
                'url' => $this->zarinpal->paymentGateway()->getRedirectUrl($response->authority),
            ];
        } catch (PaymentGatewayException $e) {
            return [
                'success' => false,
                'error' => $e->getCode(),
                'message' => $this->getErrorMessage($e->getCode()),
            ];
        } catch (Exception $e) {
            Log::error('Zarinpal Generic Exception on Request', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify payment from Zarinpal
     *
     * @param string $authority Transaction authority from Zarinpal
     * @param int $amount Original payment amount
     * @return array Array with verification result
     */
    public function verifyPayment(string $authority, int $amount): array
    {
        try {
            $request = new VerifyRequest([
                'authority' => $authority,
                'amount' => $amount,
            ]);

            $response = $this->zarinpal->paymentGateway()->verify($request);

            return [
                'success' => true,
                'reference_id' => $response->ref_id,
                'authority' => $authority,
            ];
        } catch (PaymentGatewayException $e) {
            return [
                'success' => false,
                'error' => $e->getCode(),
                'message' => $this->getErrorMessage($e->getCode()),
            ];
        } catch (Exception $e) {
            Log::error('Zarinpal Generic Exception on Verify', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => get_class($e),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get user-friendly error message for Zarinpal errors
     *
     * @param int|string $errorCode Error code from Zarinpal
     * @return string Error message
     */
    protected function getErrorMessage($errorCode): string
    {
        $messages = [
            '-1' => 'اطلاعات ارسالی نامعتبر است.',
            '-2' => 'IP و یا مرچنت درست نیست.',
            '-3' => 'با توجه به محدودیت های موجود برای شما درخواست رد شد.',
            '-4' => 'آدرس IP شما ثبت نشده است.',
            '-11' => 'درخواست مورد نظر یافت نشد.',
            '-12' => 'امکان ویرایش درخواست موجود نیست.',
            '-15' => 'مبلغ درخواست حداقل باید ۱۰۰ ریال باشد.',
            '-16' => 'تراکنش ناموفق.',
            '-17' => 'شناسه درخواست یافت نشد.',
            '-18' => 'شناسه درخواست دوبار استفاده شده است.',
            '-19' => 'آدرس IP نامعتبر است.',
            '-20' => 'آدرس قبولی نامعتبر است.',
            '-30' => 'اجازه دسترسی ندارید.',
            '-31' => 'مرچنت مورد نظر یافت نشد.',
            '-32' => 'سطح امنیت پایین است.',
            '-33' => 'سطح امنیت خیلی پایین است.',
            '-34' => 'محدود شده است.',
            '-40' => 'GET پشتیبانی نمی شود.',
            '-41' => 'مقدار hash_id نامعتبر است.',
            '-42' => 'عدم دسترسی به اطلاعات تراکنش.',
            '-54' => 'درخواست آرشیو شده است.',
            '100' => 'عملیات با موفقیت انجام شد.',
            '101' => 'تراکنش قبلا وریفای شده است.',
        ];

        return $messages[(string)$errorCode] ?? 'خطای نامشخصی رخ داد. لطفاً مجدداً تلاش کنید.';
    }

    /**
     * Get sandbox mode status
     *
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->isSandbox;
    }

    /**
     * Get the name of the gateway
     */
    public function getName(): string
    {
        return 'zarinpal';
    }
}
