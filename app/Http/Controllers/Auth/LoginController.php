<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
      // แสดงหน้า Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // ตรวจสอบและเข้าสู่ระบบ
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('admin.index'));
        }

        return back()->withErrors([
            'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
        ])->onlyInput('email');
    }

    // ออกจากระบบ
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(url('/'));
    }

    // เปลี่ยนรหัสผ่าน
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง',
            'new_password.min' => 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            'new_password.confirmed' => 'การยืนยันรหัสผ่านใหม่ไม่ตรงกัน',
        ]);

        $user = Auth::guard('web')->user();
        if ($user) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
            // Save user
            \App\Models\User::where('id', $user->getAuthIdentifier())->update(['password' => $user->password]);
            return back()->with('success', 'เปลี่ยนรหัสผ่านสำเร็จแล้ว');
        }

        return back()->with('error', 'ไม่สามารถเปลี่ยนรหัสผ่านได้');
    }
}
