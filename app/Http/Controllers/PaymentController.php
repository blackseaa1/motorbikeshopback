<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Helpers\MomoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // Đảm bảo import Str

class PaymentController extends Controller
{
    protected $momoService;

    public function __construct(MomoService $momoService)
    {
        $this->momoService = $momoService;
    }

    /**
     * Khởi tạo thanh toán Momo.
     *
     * @param string $order_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiateMomoPayment(string $order_id)
    {
        $order = Order::findOrFail($order_id);

        if (!$order->isRetriable()) {
            $redirectToRoute = Auth::guard('customer')->check() ? 'account.orders.show' : 'guest.order.show';
            return redirect()->route($redirectToRoute, $order->id)
                ->with('error', 'Đơn hàng này không thể thanh toán lại (trạng thái hiện tại: ' . $order->status_text . ').');
        }

        if ($order->status === Order::STATUS_FAILED || $order->status === Order::STATUS_CANCELLED) {
            $order->status = Order::STATUS_PENDING;
            $order->save();
            Log::info('Order #' . $order->id . ' status reset to PENDING for retry payment.');
        }

        $dynamicRedirectUrl = '';
        if (Auth::guard('customer')->check()) {
            $dynamicRedirectUrl = route('account.orders.show', $order->id);
        } else {
            $dynamicRedirectUrl = route('guest.order.show', $order->id);
        }

        $payUrl = $this->momoService->createPaymentUrl($order, $dynamicRedirectUrl);

        if ($payUrl) {
            return redirect()->away($payUrl);
        } else {
            $redirectToRoute = Auth::guard('customer')->check() ? 'account.orders.show' : 'guest.order.show';
            return redirect()->route($redirectToRoute, $order->id)
                ->with('error', 'Không thể khởi tạo thanh toán Momo. Vui lòng thử lại.');
        }
    }

    /**
     * Handles the callback from Momo after the user completes the payment.
     * (This is the Redirect URL that Momo redirects the user's browser to)
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleMomoCallback(Request $request)
    {
        $momoResponse = $request->all();
        Log::info('Momo Callback Data: ', $momoResponse);

        $result = $this->momoService->handlePaymentCallback($momoResponse);

        $originalOrderId = explode('_', $result['order_id'])[0];
        $order = Order::find($originalOrderId);

        $redirectToRoute = Auth::guard('customer')->check() ? 'account.orders.show' : 'guest.order.show';

        if ($result['success']) {
            if ($order) {
                // IPN is responsible for updating order status. This callback only displays messages.
            }
            return redirect()->route($redirectToRoute, $originalOrderId)
                ->with('success', $result['message']);
        } else {
            return redirect()->route($redirectToRoute, $originalOrderId)
                ->with('error', 'Không thành công.');
        }
    }

    /**
     * Handles IPN (Instant Payment Notification) from Momo.
     * (This is the server-to-server IPN URL that Momo calls)
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleMomoIpn(Request $request)
    {
        $momoResponse = $request->all();
        Log::info('Momo IPN Data: ', $momoResponse);

        $result = $this->momoService->handleIpnCallback($momoResponse);

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message']
        ], $result['status']);
    }

    /**
     * Initiates Vnpay payment.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $order_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiateVnpayPayment(Request $request, string $order_id)
    {
        $order = Order::findOrFail($order_id);

        if (!$order->isRetriable()) {
            $redirectToRoute = Auth::guard('customer')->check() ? 'account.orders.show' : 'guest.order.show';
            return redirect()->route($redirectToRoute, $order->id)
                ->with('error', 'Đơn hàng này không thể thanh toán lại (trạng thái hiện tại: ' . $order->status_text . ').');
        }

        if ($order->status === Order::STATUS_FAILED || $order->status === Order::STATUS_CANCELLED) {
            $order->status = Order::STATUS_PENDING;
            $order->save();
            Log::info('Order #' . $order->id . ' status reset to PENDING for retry payment.');
        }

        // VNPAY Configuration from config/services.php
        $vnp_TmnCode = config('services.vnpay.tmn_code');
        // RẤT QUAN TRỌNG: trim() Secret Key để loại bỏ khoảng trắng thừa
        $vnp_HashSecret = trim(config('services.vnpay.hash_secret'));
        $vnp_Url = config('services.vnpay.url');
        $vnp_ReturnUrl = config('services.vnpay.return_url');

        // VNPAY Payment Parameters
        $vnp_TxnRef = $order->id . '_' . time(); // Mã tham chiếu giao dịch, phải là duy nhất
        $vnp_Amount = (int) $order->total_price * 100; // Số tiền thanh toán (VND, nhân 100)

        // Làm sạch vnp_OrderInfo cho mục đích hashing
        // Chỉ dùng chữ cái (không dấu), số, không khoảng trắng
        $vnp_OrderInfo_cleaned = preg_replace('/[^A-Za-z0-9]/', '', Str::ascii("Thanh toan don hang " . $order->id));

        $vnp_OrderType = "billpayment"; // Mã danh mục hàng hóa
        $vnp_Locale = "vn"; // Ngôn ngữ giao diện (vn/en)
        $vnp_IpAddr = $request->ip(); // Customer's IP address
        $vnp_CreateDate = date('YmdHis'); // Transaction creation date and time
        $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes', time())); // Thời gian hết hạn giao dịch (e.g., 15 phút)

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_Command" => "pay",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo_cleaned,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate,
            // "vnp_BankCode" => "NCB", // Optional: Uncomment and set a bank code to pre-select a bank
        );

        // Sort parameters alphabetically before creating hash
        ksort($inputData);
        $query = "";
        $hashdata = ""; // String to create hash

        // SỬA ĐỔI QUAN TRỌNG: Cách tạo hashdata và query string
        // Dùng http_build_query để đảm bảo định dạng và encoding
        // Sau đó thay thế %20 bằng + (nếu có) và %2F bằng / để khớp VNPAY
        // CHỈ CÁC GIÁ TRỊ CỦA inputData mới được encode cho query string
        foreach ($inputData as $key => $value) {
            if ($key != 'vnp_SecureHash' && $key != 'vnp_SecureHashType') {
                $hashdata .= $key . '=' . $value . '&'; // DO NOT URL-ENCODE VALUES HERE for hashdata
                $query .= urlencode($key) . '=' . urlencode($value) . '&'; // URL-ENCODE values for query string
            }
        }
        $hashdata = rtrim($hashdata, '&'); // Remove trailing '&' from hashdata
        $query = rtrim($query, '&'); // Remove trailing '&' from query

        // Tạo VNPAY SecureHash
        $vnpSecureHash = hash_hmac('sha256', $hashdata, $vnp_HashSecret);

        $vnp_Url .= "?" . $query . '&vnp_SecureHash=' . $vnpSecureHash;

        Log::info('VNPAY Generated Hashdata (raw): ' . $hashdata); // Log generated hashdata for comparison
        Log::info('VNPAY Hash Secret Length: ' . strlen($vnp_HashSecret)); // Log secret key length
        Log::info('VNPAY Generated URL: ' . $vnp_Url);

        // DÒNG DEBUG CUỐI CÙNG: Dừng ứng dụng và hiển thị các chuỗi quan trọng
        // var_dump('Raw Hashdata:', $hashdata);
        // var_dump('Raw Hash Secret:', $vnp_HashSecret);
        // dd('Kiểm tra các chuỗi trên terminal/trình duyệt một cách cẩn thận.');


        return redirect()->away($vnp_Url); // Redirect user to Vnpay's payment gateway
    }

    /**
     * Handles the callback from Vnpay after the user completes the payment.
     * (This is the vnp_ReturnUrl that Vnpay redirects the user's browser to)
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleVnpayCallback(Request $request)
    {
        $vnp_HashSecret = trim(config('services.vnpay.hash_secret')); // trim() Secret Key
        $inputData = $request->query(); // Get all parameters from query string (GET request)

        $vnp_SecureHash = $inputData['vnp_SecureHash']; // Get secure hash from VNPAY response
        unset($inputData['vnp_SecureHash']); // Exclude from hash verification
        // VNPAY often includes vnp_SecureHashType in the query string as well
        if (isset($inputData['vnp_SecureHashType'])) {
            unset($inputData['vnp_SecureHashType']); // Exclude from hash verification
        }

        ksort($inputData); // Sort alphabetically
        $hashData = "";
        foreach ($inputData as $key => $value) {
            $hashData .= $key . '=' . $value . '&'; // Use raw values from input for hash verification
        }
        $hashData = rtrim($hashData, '&'); // Remove trailing '&'

        Log::info('VNPAY Callback Hashdata (raw): ' . $hashData); // Log callback hashdata
        Log::info('VNPAY Callback Hash Secret Length: ' . strlen($vnp_HashSecret)); // Log secret key length
        $secureHash = hash_hmac('sha256', $hashData, $vnp_HashSecret); // Generate hash for verification

        $orderId = explode('_', $inputData['vnp_TxnRef'])[0]; // Extract original order ID
        $order = Order::find($orderId);

        // Determine the final redirect route based on customer login status
        $redirectToRoute = Auth::guard('customer')->check() ? 'account.orders.show' : 'guest.order.show';

        if ($secureHash === $vnp_SecureHash) { // Verify signature
            if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                // Payment successful
                // IPN is the main source of truth for status updates, but we can update here for immediate feedback
                if ($order && $order->status === Order::STATUS_PENDING) {
                    $order->status = Order::STATUS_APPROVED; // Update status
                    $order->save();
                    Log::info('VNPAY Callback: Payment successful for Order ID ' . $order->id);
                }
                return redirect()->route($redirectToRoute, $orderId)->with('success', 'Thanh toán Vnpay thành công!');
            } else {
                // Payment failed or cancelled
                if ($order && $order->status === Order::STATUS_PENDING) {
                    $order->status = Order::STATUS_FAILED; // Update status to failed
                    $order->save();
                    Log::warning('VNPAY Callback: Payment failed for Order ID ' . $order->id . '. ResponseCode: ' . $inputData['vnp_ResponseCode'] . ', TxnStatus: ' . $inputData['vnp_TransactionStatus']);
                }
                return redirect()->route($redirectToRoute, $orderId)->with('error', 'Thanh toán Vnpay thất bại. Vui lòng thử lại hoặc chọn phương thức khác. Mã lỗi: ' . $inputData['vnp_ResponseCode']);
            }
        } else {
            // Invalid signature
            Log::error('VNPAY Callback: Invalid signature for Order ID ' . $orderId);
            return redirect()->route($redirectToRoute, $orderId)->with('error', 'Lỗi xác minh thanh toán. Vui lòng liên hệ hỗ trợ.');
        }
    }

    /**
     * Handles IPN (Instant Payment Notification) from Vnpay.
     * (This is the server-to-server IPN URL that Vnpay calls)
     * Must respond with JSON containing RspCode and Message as per Vnpay's requirements.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleVnpayIpn(Request $request)
    {
        $vnp_HashSecret = trim(config('services.vnpay.hash_secret')); // trim() Secret Key
        $inputData = $request->all(); // Get all parameters from request body (POST request)

        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']); // Exclude from hash verification
        unset($inputData['vnp_SecureHashType']); // Exclude from hash verification (if present in IPN)

        ksort($inputData); // Sort alphabetically
        $hashData = "";
        foreach ($inputData as $key => $value) {
            $hashData .= $key . '=' . $value . '&'; // Use raw values from input for hash verification
        }
        $hashData = rtrim($hashData, '&'); // Remove trailing '&'

        Log::info('VNPAY IPN Hashdata (raw): ' . $hashData); // Log IPN hashdata
        Log::info('VNPAY IPN Hash Secret Length: ' . strlen($vnp_HashSecret)); // Log secret key length
        $secureHash = hash_hmac('sha256', $hashData, $vnp_HashSecret); // Generate hash for verification

        $orderId = explode('_', $inputData['vnp_TxnRef'])[0]; // Extract original order ID
        $order = Order::find($orderId);

        // Check conditions for IPN response codes (RspCode)
        if ($secureHash === $vnp_SecureHash) {
            if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                if ($order) {
                    // Only update if the order is not already completed/approved
                    if ($order->status !== Order::STATUS_APPROVED && $order->status !== Order::STATUS_COMPLETED) {
                        $order->status = Order::STATUS_APPROVED; // Update status
                        $order->save();
                        Log::info('VNPAY IPN: Payment successful for Order ID ' . $order->id);
                        return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']); // Success response to VNPAY
                    } else {
                        // Order already confirmed by previous IPN or callback
                        Log::info('VNPAY IPN: Order ID ' . $order->id . ' already confirmed. No action needed.');
                        return response()->json(['RspCode' => '00', 'Message' => 'Order Already Confirmed']); // Already confirmed
                    }
                } else {
                    // Order not found in your database
                    Log::warning('VNPAY IPN: Order not found for ID ' . $orderId);
                    return response()->json(['RspCode' => '01', 'Message' => 'Order not found']); // Order not found
                }
            } else {
                // Payment failed or cancelled (from IPN)
                if ($order && $order->status === Order::STATUS_PENDING) {
                    $order->status = Order::STATUS_FAILED; // Update status
                    $order->save();
                    Log::warning('VNPAY IPN: Payment failed for Order ID ' . $order->id . '. ResponseCode: ' . $inputData['vnp_ResponseCode'] . ', TxnStatus: ' . $inputData['vnp_TransactionStatus']);
                }
                Log::error('VNPAY IPN: Payment failed for Order ID ' . $orderId . '. ResponseCode: ' . $inputData['vnp_ResponseCode'] . ', Message: ' . $inputData['vnp_Message']);
                return response()->json(['RspCode' => '01', 'Message' => 'Payment Failed']); // Payment failed
            }
        } else {
            // Invalid signature
            Log::error('VNPAY IPN: Invalid signature for Order ID ' . $orderId);
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']); // Invalid signature
        }
    }

    /**
     * Displays bank transfer details page.
     *
     * @param string $order_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showBankTransferDetails(string $order_id)
    {
        $order = Order::findOrFail($order_id);

        // Access control for the order details page
        if ($order->customer_id && Auth::guard('customer')->id() !== $order->customer_id) {
            abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
        } else if (!$order->customer_id && Auth::guard('customer')->check()) {
            // If it's a guest order but a customer is logged in, redirect to their order history
            return redirect()->route('account.orders.show', $order->id)->with('info', 'Bạn đã đăng nhập, xem đơn hàng này trong lịch sử mua hàng.');
        }
        // If it's a guest order and no one is logged in, or it's a logged-in customer's order
        return view('customer.checkout.bank_transfer_details', compact('order'));
    }
}
