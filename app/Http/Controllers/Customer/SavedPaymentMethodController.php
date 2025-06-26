<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedPaymentMethodController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $savedMethods = $customer->savedPaymentMethods;
        $availableMethods = PaymentMethod::where('is_active', true)->get();

        return view('customer.account.saved_payment_methods.index', compact('savedMethods', 'availableMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $customer = Auth::guard('customer')->user();
        // Attach() sẽ thêm liên kết vào bảng trung gian
        $customer->savedPaymentMethods()->syncWithoutDetaching([$request->payment_method_id]);

        return back()->with('success', 'Đã lưu phương thức thanh toán thành công!');
    }

    public function destroy($id)
    {
        $customer = Auth::guard('customer')->user();
        // Detach() sẽ xóa liên kết khỏi bảng trung gian
        $customer->savedPaymentMethods()->detach($id);

        return back()->with('success', 'Đã xóa phương thức thanh toán thành công!');
    }
}
