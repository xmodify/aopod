<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        if (!auth()->check() || (!auth()->user()->isAdmin() && !auth()->user()->canAccessDeath() && !auth()->user()->canAccessBirth())) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }
        return view('admin.index');
    }

    public function settings()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }
        $bedTypes = \App\Models\IpdBedType::all();
        return view('admin.settings', compact('bedTypes'));
    }

    public function createBedType(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $request->validate([
            'bed_code' => ['required', 'string', 'max:6', 'unique:ipd_bed_type,bed_code'],
            'bed_name' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:100'],
        ]);

        try {
            \App\Models\IpdBedType::create([
                'bed_code' => $request->bed_code,
                'bed_name' => $request->bed_name,
                'unit' => $request->unit,
            ]);

            return response()->json(['success' => true, 'message' => 'เพิ่มประเภทเตียงเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    public function updateBedType(Request $request, $bed_code)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $bedType = \App\Models\IpdBedType::where('bed_code', $bed_code)->firstOrFail();

        $request->validate([
            'bed_name' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:100'],
        ]);

        try {
            $bedType->update([
                'bed_name' => $request->bed_name,
                'unit' => $request->unit,
            ]);

            return response()->json(['success' => true, 'message' => 'แก้ไขประเภทเตียงเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    public function deleteBedType($bed_code)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        try {
            $bedType = \App\Models\IpdBedType::where('bed_code', $bed_code)->firstOrFail();
            $bedType->delete();
            return response()->json(['success' => true, 'message' => 'ลบประเภทเตียงเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    public function gitPull()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์ดำเนินการนี้'
            ], 403);
        }

        try {
            $output = [];
            $exitCode = 0;

            // Execute git reset --hard
            exec('git reset --hard 2>&1', $output, $exitCode);
            $resetOutput = implode("\n", $output);

            if ($exitCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Git Reset Failed: ' . $resetOutput
                ], 500);
            }

            // Execute git pull origin main
            $output = [];
            exec('git pull origin main 2>&1', $output, $exitCode);
            $pullOutput = implode("\n", $output);

            if ($exitCode !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Git Pull Failed: ' . $pullOutput
                ], 500);
            }

            // Execute php artisan optimize:clear
            $output = [];
            exec('php artisan optimize:clear 2>&1', $output, $exitCode);
            $artisanOutput = implode("\n", $output);

            return response()->json([
                'success' => true,
                'message' => "ดึงข้อมูลจาก Git และเคลียร์แคชระบบสำเร็จแล้ว!\n\n[Git Reset]:\n{$resetOutput}\n\n[Git Pull]:\n{$pullOutput}\n\n[Artisan Optimize]:\n{$artisanOutput}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการรันคำสั่ง: ' . $e->getMessage()
            ], 500);
        }
    }

    public function upgradeStructure()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์ดำเนินการนี้'
            ], 403);
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();

            return response()->json([
                'success' => true,
                'message' => "ปรับปรุงโครงสร้างฐานข้อมูลสำเร็จแล้ว!\n\n{$output}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการปรับปรุงฐานข้อมูล: ' . $e->getMessage()
            ], 500);
        }
    }

    public function users()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }
        $users = \App\Models\User::all();
        return view('admin.users', compact('users'));
    }

    public function createUser(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:user,admin'],
            'allow_death' => ['nullable'],
            'allow_birth' => ['nullable'],
        ]);

        try {
            \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => $request->role,
                'allow_death' => $request->has('allow_death') ? 1 : 0,
                'allow_birth' => $request->has('allow_birth') ? 1 : 0,
            ]);

            return response()->json(['success' => true, 'message' => 'เพิ่มสมาชิกเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    public function updateUser(Request $request, $id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $user = \App\Models\User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:user,admin'],
            'allow_death' => ['nullable'],
            'allow_birth' => ['nullable'],
        ]);

        try {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->allow_death = $request->has('allow_death') ? 1 : 0;
            $user->allow_birth = $request->has('allow_birth') ? 1 : 0;
            if ($request->password) {
                $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
            }
            $user->save();

            return response()->json(['success' => true, 'message' => 'แก้ไขข้อมูลสมาชิกเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    public function deleteUser($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        try {
            $user = \App\Models\User::findOrFail($id);
            
            if ($user->id === auth()->user()->id) {
                return response()->json(['success' => false, 'message' => 'คุณไม่สามารถลบบัญชีของคุณเองได้'], 400);
            }

            $user->delete();
            return response()->json(['success' => true, 'message' => 'ลบสมาชิกเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    public function resetPassword($id)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        try {
            $user = \App\Models\User::findOrFail($id);
            $user->password = \Illuminate\Support\Facades\Hash::make('12345678');
            $user->save();

            return response()->json(['success' => true, 'message' => 'รีเซ็ตรหัสผ่านเป็น 12345678 สำเร็จแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }
}
