<?php

namespace App\Http\Controllers\Admin\Auth; // Namespace chính xác

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendingAuthorizationController extends Controller
{
    public function show()
    {
        $adminUser = Auth::guard('admin')->user();

        if ($adminUser && $adminUser->role !== null) {
            return redirect()->route('admin.dashboard');
        }

        return view('welcome', [ // Trả về view 'welcome.blade.php'
            'showPendingMessage' => true,
            'adminUserName' => $adminUser ? $adminUser->name : 'Quản trị viên'
        ]);
    }
}
