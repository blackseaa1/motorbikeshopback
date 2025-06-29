<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Helpers\MomoService;
use App\Helpers\VnpayService; // Import VnpayService
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $momoService;
    protected $vnpayService; // Thêm VnpayService

    public function __construct(MomoService $momoService, VnpayService $vnpayService) // Inject VnpayService
    {
        $this->momoService = $momoService;
        $this->vnpayService = $vnpayService; // Gán VnpayService
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

        // Gọi VnpayService để tạo URL thanh toán Vnpay
        $payUrl = $this->vnpayService->createPaymentUrl($order, $request->ip());

        return redirect()->away($payUrl); // Redirect user to Vnpay's payment gateway
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
        $result = $this->vnpayService->handlePaymentCallback($request->query());

        $orderId = $result['order_id'];
        $order = Order::find($orderId);

        $redirectToRoute = Auth::guard('customer')->check() ? 'account.orders.show' : 'guest.order.show';

        if ($result['success']) {
            if ($order && $order->status === Order::STATUS_PENDING) {
                $order->status = Order::STATUS_APPROVED; // Cập nhật trạng thái
                $order->save();
                Log::info('VNPAY Callback: Payment successful for Order ID ' . $order->id);
            }
            return redirect()->route($redirectToRoute, $orderId)->with('success', $result['message']);
        } else {
            if ($order && $order->status === Order::STATUS_PENDING) {
                $order->status = Order::STATUS_FAILED; // Cập nhật trạng thái thất bại
                $order->save();
                Log::warning('VNPAY Callback: Payment failed for Order ID ' . $order->id . '. Message: ' . $result['message']);
            }
            return redirect()->route($redirectToRoute, $orderId)->with('error', $result['message']);
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
        $result = $this->vnpayService->handleIpnCallback($request->all());

        // Lấy orderId gốc từ kết quả xử lý của service
        $orderId = $result['order_id'];
        $order = Order::find($orderId);

        if ($result['success']) {
            if ($order) {
                if ($order->status !== Order::STATUS_APPROVED && $order->status !== Order::STATUS_COMPLETED) {
                    $order->status = Order::STATUS_APPROVED;
                    $order->save();
                    Log::info('VNPAY IPN: Payment successful for Order ID ' . $order->id);
                } else if ($order->status === Order::STATUS_APPROVED) {
                    Log::info('VNPAY IPN: Order ID ' . $order->id . ' already confirmed. No action needed.');
                }
            } else {
                Log::warning('VNPAY IPN: Order not found for ID ' . $orderId);
            }
            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
        } else {
            if ($order && $order->status === Order::STATUS_PENDING) {
                $order->status = Order::STATUS_FAILED;
                $order->save();
                Log::warning('VNPAY IPN: Payment failed for Order ID ' . $order->id . '. Message: ' . $result['message']);
            }
            Log::error('VNPAY IPN: Payment failed for Order ID ' . $orderId . '. Message: ' . $result['message']);
            return response()->json(['RspCode' => '01', 'Message' => 'Payment Failed']);
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
