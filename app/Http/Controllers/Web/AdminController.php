<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }
        return view('admin.index');
    }

    public function settings()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }
        try {
            $bedTypes = \App\Models\IpdBedType::all();
        } catch (\Throwable $e) {
            $bedTypes = collect();
        }

        try {
            $hospitals = \App\Models\Hospital::where('is_active', true)->where('hospcode', '!=', '00025')->get();
        } catch (\Throwable $e) {
            $hospitals = collect();
        }
        
        $mophSettings = [
            'provider_id_active' => \App\Models\MainSetting::get('provider_id_active', config('moph.provider_id_active', 'Y')),
            'health_id_client_id' => \App\Models\MainSetting::get('health_id_client_id', config('moph.health_id.client_id', '')),
            'health_id_client_secret' => \App\Models\MainSetting::get('health_id_client_secret', config('moph.health_id.client_secret', '')),
            'provider_id_client_id' => \App\Models\MainSetting::get('provider_id_client_id', config('moph.provider_id.client_id', '')),
            'provider_id_secret_key' => \App\Models\MainSetting::get('provider_id_secret_key', config('moph.provider_id.secret_key', '')),
            'moph_alert_active' => \App\Models\MainSetting::get('moph_alert_active', config('moph.alert.active', 'N')),
            'moph_alert_client_id' => \App\Models\MainSetting::get('moph_alert_client_id', config('moph.alert.client_id', '')),
            'moph_alert_client_secret' => \App\Models\MainSetting::get('moph_alert_client_secret', config('moph.alert.client_secret', '')),
        ];

        return view('admin.settings', compact('bedTypes', 'hospitals', 'mophSettings'));
    }

    public function updateMophSettings(Request $request)
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        $request->validate([
            'provider_id_active' => ['nullable', 'string', 'in:Y,N'],
            'health_id_client_id' => ['nullable', 'string'],
            'health_id_client_secret' => ['nullable', 'string'],
            'provider_id_client_id' => ['nullable', 'string'],
            'provider_id_secret_key' => ['nullable', 'string'],
            'moph_alert_active' => ['nullable', 'string', 'in:Y,N'],
            'moph_alert_client_id' => ['nullable', 'string'],
            'moph_alert_client_secret' => ['nullable', 'string'],
        ]);

        try {
            \App\Models\MainSetting::set('provider_id_active', $request->has('provider_id_active') ? 'Y' : 'N');
            \App\Models\MainSetting::set('health_id_client_id', $request->health_id_client_id ?? '');
            \App\Models\MainSetting::set('health_id_client_secret', $request->health_id_client_secret ?? '');
            \App\Models\MainSetting::set('provider_id_client_id', $request->provider_id_client_id ?? '');
            \App\Models\MainSetting::set('provider_id_secret_key', $request->provider_id_secret_key ?? '');
            \App\Models\MainSetting::set('moph_alert_active', $request->has('moph_alert_active') ? 'Y' : 'N');
            \App\Models\MainSetting::set('moph_alert_client_id', $request->moph_alert_client_id ?? '');
            \App\Models\MainSetting::set('moph_alert_client_secret', $request->moph_alert_client_secret ?? '');

            return response()->json([
                'success' => true,
                'message' => 'บันทึกการตั้งค่า Provider ID และ 2FA สำเร็จแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
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

            // Execute composer install to get new packages
            $output = [];
            exec('composer install --no-interaction --no-dev --optimize-autoloader 2>&1', $output, $exitCode);
            $composerOutput = implode("\n", $output);

            if ($exitCode !== 0) {
                // If composer is not in system path, try local composer.phar or fallback to telling the user
                $output = [];
                exec('php composer.phar install --no-interaction --no-dev --optimize-autoloader 2>&1', $output, $exitCode);
                if ($exitCode !== 0) {
                    $composerOutput = "Composer install failed (both global composer and local composer.phar). Please run 'composer install' manually on server command line. Error details:\n" . implode("\n", $output);
                } else {
                    $composerOutput = implode("\n", $output);
                }
            }

            // Execute php artisan optimize:clear
            $output = [];
            exec('php artisan optimize:clear 2>&1', $output, $exitCode);
            $artisanOutput = implode("\n", $output);

            return response()->json([
                'success' => true,
                'message' => "ดึงข้อมูลจาก Git และเคลียร์แคชระบบสำเร็จแล้ว!\n\n[Git Reset]:\n{$resetOutput}\n\n[Git Pull]:\n{$pullOutput}\n\n[Composer Install]:\n{$composerOutput}\n\n[Artisan Optimize]:\n{$artisanOutput}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการรันคำสั่ง: ' . $e->getMessage()
            ], 500);
        }
    }

    public function upgradeStructureStream()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        return response()->stream(function () {
            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', 1);
            }
            @ini_set('zlib.output_compression', 0);
            @ini_set('implicit_flush', 1);
            
            // Clean output buffers
            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $service = new \App\Services\SchemaUpgradeService();
            $service->upgrade(function ($percent, $step, $status, $details, $changes, $seedsSummary) {
                echo "data: " . json_encode([
                    'percent' => $percent,
                    'step' => $step,
                    'status' => $status,
                    'details' => $details,
                    'changes' => $changes,
                    'seeds_summary' => $seedsSummary
                ], JSON_UNESCAPED_UNICODE) . "\n\n";
                flush();
            });
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
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
            // Keep a legacy fallback or simple check endpoint if needed, but we can return success
            return response()->json([
                'success' => true,
                'message' => 'พร้อมสำหรับการปรับปรุงโครงสร้าง'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    public function users()
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }
        $users = \App\Models\User::with('hospital')->get();
        $hospitals = \App\Models\Hospital::where('is_active', true)->orderBy('hospcode', 'asc')->get();
        return view('admin.users', compact('users', 'hospitals'));
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
            'hospcode' => ['nullable', 'string', 'max:10'],
            'position' => ['nullable', 'string', 'max:255'],
            'cid' => ['nullable', 'string', 'max:13', 'unique:users,cid'],
            'active' => ['nullable'],
            'allow_death' => ['nullable'],
            'allow_death_dashboard' => ['nullable'],
            'allow_birth' => ['nullable'],
            'allow_birth_dashboard' => ['nullable'],
        ]);

        try {
            \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => $request->role,
                'hospcode' => $request->hospcode,
                'position' => $request->position,
                'cid' => $request->cid ? preg_replace('/[^0-9]/', '', $request->cid) : null,
                'active' => $request->has('active') ? 'Y' : 'N',
                'allow_death' => $request->has('allow_death') ? 1 : 0,
                'allow_death_dashboard' => $request->has('allow_death_dashboard') ? 1 : 0,
                'allow_birth' => $request->has('allow_birth') ? 1 : 0,
                'allow_birth_dashboard' => $request->has('allow_birth_dashboard') ? 1 : 0,
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
            'hospcode' => ['nullable', 'string', 'max:10'],
            'position' => ['nullable', 'string', 'max:255'],
            'cid' => ['nullable', 'string', 'max:13', 'unique:users,cid,' . $id],
            'active' => ['nullable'],
            'allow_death' => ['nullable'],
            'allow_death_dashboard' => ['nullable'],
            'allow_birth' => ['nullable'],
            'allow_birth_dashboard' => ['nullable'],
        ]);

        try {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->hospcode = $request->hospcode;
            $user->position = $request->position;
            $user->cid = $request->cid ? preg_replace('/[^0-9]/', '', $request->cid) : null;
            $user->active = $request->has('active') ? 'Y' : 'N';
            $user->allow_death = $request->has('allow_death') ? 1 : 0;
            $user->allow_death_dashboard = $request->has('allow_death_dashboard') ? 1 : 0;
            $user->allow_birth = $request->has('allow_birth') ? 1 : 0;
            $user->allow_birth_dashboard = $request->has('allow_birth_dashboard') ? 1 : 0;
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
