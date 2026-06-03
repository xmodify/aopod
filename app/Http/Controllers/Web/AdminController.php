<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function gitPull()
    {
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
}
