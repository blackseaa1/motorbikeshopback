<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Helpers\MomoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

        // Đảm bảo chỉ xử lý các đơn hàng đang chờ xử lý, hoặc các đơn hàng đã thất bại/hủy có thể thanh toán lại
        if (!$order->isRetriable()) { // Sử dụng phương thức isRetriable() từ Order model
            // Chuyển hướng về trang chi tiết đơn hàng (cho cả khách vãng lai tra cứu)
            $redirectToRoute = Auth::guard('customer')->check() ? 'account.orders.show' : 'guest.order.show';
            return redirect()->route($redirectToRoute, $order->id)
                ->with('error', 'Đơn hàng này không thể thanh toán lại (trạng thái hiện tại: ' . $order->status_text . ').');
        }

        // Nếu đơn hàng là FAILED/CANCELLED, đặt lại trạng thái về PENDING trước khi gửi Momo
        if ($order->status === Order::STATUS_FAILED || $order->status === Order::STATUS_CANCELLED) {
            $order->status = Order::STATUS_PENDING;
            $order->save();
            Log::info('Order #' . $order->id . ' status reset to PENDING for retry payment.');
        }

        // Xác định redirectUrl động dựa trên trạng thái đăng nhập
        $dynamicRedirectUrl = '';
        if (Auth::guard('customer')->check()) {
            // Khách có tài khoản: Chuyển về trang chi tiết đơn hàng của tài khoản
            $dynamicRedirectUrl = route('account.orders.show', $order->id);
        } else {
            // Khách vãng lai: Chuyển về trang chi tiết đơn hàng khách vãng lai
            $dynamicRedirectUrl = route('guest.order.show', $order->id);
        }

        // Truyền dynamicRedirectUrl vào createPaymentUrl
        $payUrl = $this->momoService->createPaymentUrl($order, $dynamicRedirectUrl);

        if ($payUrl) {
            return redirect()->away($payUrl); // Chuyển hướng người dùng đến URL của Momo
        } else {
            // Fallback nếu không tạo được URL thanh toán Momo
            $redirectToRoute = Auth::guard('customer')->check() ? 'account.orders.show' : 'guest.order.show';
            return redirect()->route($redirectToRoute, $order->id)
                ->with('error', 'Không thể khởi tạo thanh toán Momo. Vui lòng thử lại.');
        }
    }

    /**
     * Xử lý kết quả Callback từ Momo sau khi người dùng thanh toán xong.
     * (Đây là Redirect URL mà Momo chuyển hướng người dùng về)
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleMomoCallback(Request $request)
    {
        $momoResponse = $request->all();
        Log::info('Momo Callback Data: ', $momoResponse);

        $result = $this->momoService->handlePaymentCallback($momoResponse);

        // Khi nhận callback, orderId từ Momo sẽ là "26_1751137234"
        // Bạn cần trích xuất ID gốc của đơn hàng (ví dụ: 26) để tìm trong DB
        $originalOrderId = explode('_', $result['order_id'])[0];
        $order = Order::find($originalOrderId); // Tìm đơn hàng gốc

        // Xác định URL chuyển hướng cuối cùng dựa trên trạng thái đăng nhập
        $redirectToRoute = Auth::guard('customer')->check() ? 'account.orders.show' : 'guest.order.show';

        if ($result['success']) {
            if ($order) {
                // Logic cập nhật trạng thái đơn hàng đã được chuyển sang IPN để đáng tin cậy hơn.
                // Callback này chỉ để hiển thị thông báo cho người dùng.
            }
            return redirect()->route($redirectToRoute, $originalOrderId)
                ->with('success', $result['message']);
        } else {
            return redirect()->route($redirectToRoute, $originalOrderId)
                ->with('error', $result['message']);
        }
    }

    /**
     * Xử lý IPN (Instant Payment Notification) từ Momo.
     * (Đây là IPN URL mà Momo gọi server-to-server)
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

    // Phương thức cho Vnpay có thể tương tự initiateMomoPayment
    public function initiateVnpayPayment(string $order_id)
    {
        $order = Order::findOrFail($order_id);
        // Logic để gọi API Vnpay và chuyển hướng
        // Ví dụ: return redirect($this->vnpayService->createPaymentUrl($order, $order->total_price));
        return "Tích hợp VNPAY tại đây cho đơn hàng #" . $order_id;
    }

    // Phương thức cho trang chi tiết chuyển khoản ngân hàng
    public function showBankTransferDetails(string $order_id)
    {
        $order = Order::findOrFail($order_id);
        // Đảm bảo quyền truy cập: Nếu có customer_id, phải khớp với Auth::id()
        // Nếu là khách vãng lai, phải đảm bảo không có customer nào đăng nhập và có thể có thêm token bảo mật

        // Logic kiểm tra quyền truy cập đơn hàng (từ CheckoutController)
        if ($order->customer_id && Auth::guard('customer')->id() !== $order->customer_id) {
            abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
        } else if (!$order->customer_id && Auth::guard('customer')->check()) {
            // Nếu là đơn hàng của khách vãng lai nhưng có khách hàng đăng nhập, chuyển hướng về lịch sử mua hàng
            return redirect()->route('account.orders.show', $order->id)->with('info', 'Bạn đã đăng nhập, xem đơn hàng này trong lịch sử mua hàng.');
        }
        // Nếu là khách vãng lai và không có ai đăng nhập, hoặc là khách hàng đăng nhập và là đơn hàng của họ
        return view('customer.checkout.bank_transfer_details', compact('order'));
    }
}
