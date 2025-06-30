<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Carbon\Carbon;

class MomoService
{
    protected $partnerCode;
    protected $accessKey;
    protected $secretKey;
    protected $endpoint;
    protected $redirectUrlBase;
    protected $ipnUrl;
    protected $autoCapture;
    protected $lang;

    public function __construct()
    {
        $this->partnerCode = config('services.momo.partnerCode');
        $this->accessKey = config('services.momo.accessKey');
        $this->secretKey = config('services.momo.secretKey');
        $this->endpoint = config('services.momo.endpoint');
        $this->redirectUrlBase = config('services.momo.redirectUrl');
        $this->ipnUrl = config('services.momo.ipnUrl');
        $this->autoCapture = config('services.momo.autoCapture');
        $this->lang = config('services.momo.lang');
    }

    /**
     * Tạo URL thanh toán Momo cho một đơn hàng.
     *
     * @param \App\Models\Order $order
     * @param string $dynamicRedirectUrl URL chuyển hướng động đã được tạo từ controller
     * @return string|false URL chuyển hướng đến Momo hoặc false nếu có lỗi
     */
    public function createPaymentUrl(Order $order, string $dynamicRedirectUrl)
    {
        $amount = (string) (int) $order->total_price;
        $momoOrderId = (string) $order->id . '_' . Carbon::now()->timestamp;
        $orderInfo = "Thanh toán đơn hàng " . $order->id;
        $requestId = (string) Carbon::now()->timestamp . rand(100, 999);
        $extraData = "";

        // THAY ĐỔI QUAN TRỌNG: Đặt requestType thành 'payWithATM'
        $requestType = 'payWithATM'; // Chỉ định chỉ hiển thị thanh toán bằng thẻ ATM

        $redirectUrl = $dynamicRedirectUrl;

        // Chuỗi rawHash: thứ tự không thay đổi
        $rawHash = "accessKey=" . $this->accessKey .
                   "&amount=" . $amount .
                   "&extraData=" . $extraData .
                   "&ipnUrl=" . $this->ipnUrl .
                   "&orderId=" . $momoOrderId .
                   "&orderInfo=" . $orderInfo .
                   "&partnerCode=" . $this->partnerCode .
                   "&redirectUrl=" . $redirectUrl .
                   "&requestId=" . $requestId .
                   "&requestType=" . $requestType; // requestType cũng được đưa vào hash

        Log::info('Momo Generated RawHash: ' . $rawHash);

        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

        $data = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => "Motorbike Shop",
            'storeId' => 'MotorbikeShopStore',
            'requestId' => $requestId,
            'amount' => (int) $amount,
            'orderId' => $momoOrderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $this->ipnUrl,
            'lang' => $this->lang,
            'autoCapture' => true, // autoCapture cũng là true
            'extraData' => $extraData,
            'requestType' => $requestType, // Sử dụng requestType mới
            'signature' => $signature,
        ];

        Log::info('Momo Request Payload: ', $data);

        try {
            $response = Http::timeout(10)->post($this->endpoint, $data);
            $jsonResult = $response->json();

            Log::info('Momo Response: ', $jsonResult);

            if ($response->successful() && isset($jsonResult['payUrl'])) {
                return $jsonResult['payUrl'];
            } else {
                Log::error('Momo Payment Error: ' . ($jsonResult['message'] ?? 'Unknown error') . ' - ResultCode: ' . ($jsonResult['resultCode'] ?? 'N/A') . ' - SubErrors: ' . json_encode($jsonResult['subErrors'] ?? []));
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception during Momo payment initiation: ' . $e->getMessage() . ' - File: ' . $e->getFile() . ' - Line: ' . $e->getLine());
            return false;
        }
    }

    /**
     * Xử lý kết quả callback (RedirectUrl) từ Momo.
     *
     * @param array $momoResponse Mảng dữ liệu nhận được từ Momo qua GET/POST
     * @return array Kết quả xử lý (success, message, order_id, RspCode)
     */
    public function handlePaymentCallback(array $momoResponse)
    {
        $accessKey = $this->accessKey;
        $secretKey = $this->secretKey;

        // rawHash cho Callback (Standard Payment)
        $rawHash = "accessKey=" . $accessKey .
                   "&amount=" . ($momoResponse['amount'] ?? '') .
                   "&extraData=" . ($momoResponse['extraData'] ?? '') .
                   "&message=" . ($momoResponse['message'] ?? '') .
                   "&orderId=" . ($momoResponse['orderId'] ?? '') .
                   "&orderInfo=" . ($momoResponse['orderInfo'] ?? '') .
                   "&partnerCode=" . ($momoResponse['partnerCode'] ?? '') .
                   "&payType=" . ($momoResponse['payType'] ?? '') .
                   "&requestId=" . ($momoResponse['requestId'] ?? '') .
                   "&responseTime=" . ($momoResponse['responseTime'] ?? '') .
                   "&resultCode=" . ($momoResponse['resultCode'] ?? '') .
                   "&transId=" . ($momoResponse['transId'] ?? '');

        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        if ($signature !== ($momoResponse['signature'] ?? '')) {
            Log::error('Momo Callback: Invalid signature for Order ID ' . ($momoResponse['orderId'] ?? 'N/A'));
            return [
                'success' => false,
                'message' => 'Chữ ký không hợp lệ.',
                'order_id' => $momoResponse['orderId'] ?? null,
                'RspCode' => 99,
            ];
        }

        // Xử lý kết quả thanh toán dựa trên resultCode
        if (($momoResponse['resultCode'] ?? '') == 0) {
            Log::info('Momo Callback: Payment successful for Order ID ' . ($momoResponse['orderId'] ?? 'N/A'));
            return [
                'success' => true,
                'message' => 'Thanh toán thành công.',
                'order_id' => $momoResponse['orderId'] ?? null,
                'RspCode' => 0,
            ];
        } else {
            Log::warning('Momo Callback: Payment failed for Order ID ' . ($momoResponse['orderId'] ?? 'N/A') . ' with code ' . ($momoResponse['resultCode'] ?? 'N/A'));
            return [
                'success' => false,
                'message' => 'Thanh toán thất bại: ' . ($momoResponse['message'] ?? 'Lỗi không xác định'),
                'order_id' => $momoResponse['orderId'] ?? null,
                'RspCode' => $momoResponse['resultCode'] ?? 99,
            ];
        }
    }

    /**
     * Xử lý thông báo IPN từ Momo (Instant Payment Notification).
     *
     * @param array $momoResponse Mảng dữ liệu nhận được từ Momo (thường là POST request body)
     * @return array Kết quả xử lý cho Momo (status, message)
     */
    public function handleIpnCallback(array $momoResponse)
    {
        $accessKey = $this->accessKey;
        $secretKey = $this->secretKey;

        // rawHash cho IPN (Standard Payment)
        $rawHash = "partnerCode=" . ($momoResponse['partnerCode'] ?? '') .
                   "&accessKey=" . $accessKey .
                   "&requestId=" . ($momoResponse['requestId'] ?? '') .
                   "&amount=" . ($momoResponse['amount'] ?? '') .
                   "&orderId=" . ($momoResponse['orderId'] ?? '') .
                   "&orderInfo=" . ($momoResponse['orderInfo'] ?? '') .
                   "&orderType=" . ($momoResponse['orderType'] ?? '') .
                   "&transId=" . ($momoResponse['transId'] ?? '') .
                   "&message=" . ($momoResponse['message'] ?? '') .
                   "&localMessage=" . ($momoResponse['localMessage'] ?? '') .
                   "&responseTime=" . ($momoResponse['responseTime'] ?? '') .
                   "&errorCode=" . ($momoResponse['errorCode'] ?? '') .
                   "&payType=" . ($momoResponse['payType'] ?? '') .
                   "&extraData=" . ($momoResponse['extraData'] ?? '') .
                   "&signature=" . ($momoResponse['signature'] ?? '');

        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        if ($signature !== ($momoResponse['signature'] ?? '')) {
            Log::error('Momo IPN: Invalid signature for Order ID ' . ($momoResponse['orderId'] ?? 'N/A'));
            return ['status' => 400, 'message' => 'INVALID_SIGNATURE'];
        }

        // Xử lý IPN dựa trên errorCode
        if (($momoResponse['errorCode'] ?? '') == 0) {
            $originalOrderId = explode('_', $momoResponse['orderId'])[0];
            $order = Order::find($originalOrderId);

            if ($order) {
                if ($order->status !== Order::STATUS_COMPLETED && $order->status !== Order::STATUS_APPROVED) {
                    $order->status = Order::STATUS_APPROVED;
                    $order->save();
                    Log::info('Momo IPN: Order #' . $order->id . ' status updated to APPROVED.');
                }
            } else {
                Log::warning('Momo IPN: Order not found for ID ' . ($momoResponse['orderId'] ?? 'N/A') . ' (Original: ' . $originalOrderId . ')');
            }

            return ['status' => 200, 'message' => 'SUCCESS'];
        } else {
            $originalOrderId = explode('_', $momoResponse['orderId'])[0];
            $order = Order::find($originalOrderId);
            if ($order) {
                if ($order->status !== Order::STATUS_FAILED && $order->status !== Order::STATUS_CANCELLED) {
                    $order->status = Order::STATUS_FAILED;
                    $order->save();
                    Log::warning('Momo IPN: Order #' . $order->id . ' status updated to FAILED. Error: ' . ($momoResponse['message'] ?? 'Unknown IPN error'));
                }
                Log::error('Momo IPN: Order #' . ($momoResponse['orderId'] ?? 'N/A') . ' failed with errorCode ' . ($momoResponse['errorCode'] ?? 'N/A') . ' message: ' . ($momoResponse['message'] ?? ''));
            }
            return ['status' => 400, 'message' => 'PAYMENT_FAILED'];
        }
    }
}