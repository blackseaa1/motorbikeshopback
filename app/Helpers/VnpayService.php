<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // Import Str cho các hàm làm sạch chuỗi
use App\Models\Order; // Import Order model

class VnpayService
{
    protected $vnp_TmnCode;
    protected $vnp_HashSecret;
    protected $vnp_Url;
    protected $vnp_ReturnUrl;
    protected $vnp_IpnUrl;

    public function __construct()
    {
        $this->vnp_TmnCode = config('services.vnpay.tmn_code');
        $this->vnp_HashSecret = trim(config('services.vnpay.hash_secret')); // Đảm bảo trim() secret
        $this->vnp_Url = config('services.vnpay.url');
        $this->vnp_ReturnUrl = config('services.vnpay.return_url');
        $this->vnp_IpnUrl = config('services.vnpay.ipn_url');
    }

    /**
     * Tạo URL thanh toán VNPAY.
     *
     * @param \App\Models\Order $order
     * @param string $customerIpAddr Địa chỉ IP của khách hàng
     * @return string VNPAY payment URL
     */
    public function createPaymentUrl(Order $order, string $customerIpAddr): string
    {
        $vnp_TxnRef = $order->id . '_' . time(); // Mã tham chiếu giao dịch, phải là duy nhất
        $vnp_Amount = (int) $order->total_price * 100; // Số tiền thanh toán (VND, nhân 100)

        // SỬA ĐỔI: vnp_OrderInfo theo mẫu (tiếng Việt không dấu, có khoảng trắng)
        $vnp_OrderInfo = "Thanh toan don hang " . $order->id;

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_Command" => "pay",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $customerIpAddr,
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => $vnp_OrderInfo, // Sử dụng OrderInfo theo mẫu
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $this->vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => date('YmdHis', strtotime('+15 minutes', time())),
            // "vnp_BankCode" => "NCB", // Optional: Uncomment and set a bank code to pre-select a bank
        );

        // Sort parameters alphabetically
        ksort($inputData);
        $query = "";
        $hashdata = "";
        $i = 0; // Biến đếm để xử lý dấu '&' như trong mẫu VNPAY
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                // SỬA ĐỔI: Thêm '&' trước khi nối
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        // $hashdata = rtrim($hashdata, '&'); // Không cần trim() vì đã xử lý dấu '&' ở trên
        $query = rtrim($query, '&'); // Remove trailing '&' from query

        // SỬA ĐỔI: Tạo VNPAY SecureHash bằng SHA512
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);

        $paymentUrl = $this->vnp_Url . "?" . $query . '&vnp_SecureHash=' . $vnpSecureHash;

        Log::info('VNPAY Generated Hashdata (URL-encoded): ' . $hashdata);
        Log::info('VNPAY Hash Secret Length: ' . strlen($this->vnp_HashSecret));
        Log::info('VNPAY Generated URL: ' . $paymentUrl);

        return $paymentUrl;
    }

    /**
     * Xử lý kết quả Callback từ VNPAY.
     *
     * @param array $inputData Dữ liệu nhận được từ VNPAY qua query string (GET)
     * @return array Kết quả xử lý (success, message, order_id, response_code, transaction_status)
     */
    public function handlePaymentCallback(array $inputData): array
    {
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        if (isset($inputData['vnp_SecureHashType'])) {
            unset($inputData['vnp_SecureHashType']);
        }

        ksort($inputData);
        $hashData = "";
        $i = 0; // Biến đếm để xử lý dấu '&' như trong mẫu VNPAY
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        // $hashData = rtrim($hashData, '&'); // Không cần trim()

        Log::info('VNPAY Callback Hashdata (URL-encoded): ' . $hashData);
        Log::info('VNPAY Callback Hash Secret Length: ' . strlen($this->vnp_HashSecret));
        // SỬA ĐỔI: Xác minh chữ ký bằng SHA512
        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);

        $orderId = explode('_', $inputData['vnp_TxnRef'])[0];
        $responseCode = $inputData['vnp_ResponseCode'] ?? '99';
        $transactionStatus = $inputData['vnp_TransactionStatus'] ?? '99';
        $message = $inputData['vnp_Message'] ?? 'Unknown error';

        if ($secureHash === $vnp_SecureHash) {
            if ($responseCode == '00' && $transactionStatus == '00') {
                return [
                    'success' => true,
                    'message' => 'Thanh toán thành công.',
                    'order_id' => $orderId,
                    'response_code' => $responseCode,
                    'transaction_status' => $transactionStatus,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Thanh toán thất bại. Mã lỗi: ' . $responseCode . ', Trạng thái: ' . $transactionStatus . '. Chi tiết: ' . $message,
                    'order_id' => $orderId,
                    'response_code' => $responseCode,
                    'transaction_status' => $transactionStatus,
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Lỗi xác minh chữ ký VNPAY.',
                'order_id' => $orderId,
                'response_code' => '97',
                'transaction_status' => '97',
            ];
        }
    }

    /**
     * Xử lý IPN (Instant Payment Notification) từ VNPAY.
     *
     * @param array $inputData Dữ liệu nhận được từ VNPAY qua POST request body
     * @return array Kết quả xử lý cho VNPAY (RspCode, Message)
     */
    public function handleIpnCallback(array $inputData): array
    {
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        if (isset($inputData['vnp_SecureHashType'])) {
            unset($inputData['vnp_SecureHashType']);
        }

        ksort($inputData);
        $hashData = "";
        $i = 0; // Biến đếm để xử lý dấu '&' như trong mẫu VNPAY
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        // $hashData = rtrim($hashData, '&'); // Không cần trim()

        Log::info('VNPAY IPN Hashdata (URL-encoded): ' . $hashData);
        Log::info('VNPAY IPN Hash Secret Length: ' . strlen($this->vnp_HashSecret));
        // SỬA ĐỔI: Xác minh chữ ký bằng SHA512
        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);

        $orderId = explode('_', $inputData['vnp_TxnRef'])[0];
        $responseCode = $inputData['vnp_ResponseCode'] ?? '99';
        $transactionStatus = $inputData['vnp_TransactionStatus'] ?? '99';
        $message = $inputData['vnp_Message'] ?? 'Unknown IPN error';

        if ($secureHash === $vnp_SecureHash) {
            if ($responseCode == '00' && $transactionStatus == '00') {
                return [
                    'success' => true,
                    'message' => 'Xác nhận thành công.',
                    'order_id' => $orderId,
                    'RspCode' => '00',
                    'vnp_ResponseCode' => $responseCode,
                    'vnp_TransactionStatus' => $transactionStatus,
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Thanh toán thất bại từ IPN. Mã lỗi: ' . $responseCode . ', Trạng thái: ' . $transactionStatus . '. Chi tiết: ' . $message,
                    'order_id' => $orderId,
                    'RspCode' => '01',
                    'vnp_ResponseCode' => $responseCode,
                    'vnp_TransactionStatus' => $transactionStatus,
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Lỗi xác minh chữ ký VNPAY từ IPN.',
                'order_id' => $orderId,
                'RspCode' => '97',
                'vnp_ResponseCode' => '97',
                'vnp_TransactionStatus' => '97',
            ];
        }
    }
}
