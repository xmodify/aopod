<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class StampVersionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:version {custom_version? : กำหนดข้อความเวอร์ชันเอง (ถ้าไม่ใส่จะสร้างอัตโนมัติ V.YY-MM-DD HH:mm)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'อัปเดตเลขเวอร์ชันระบบ (ปี พ.ศ. 2 หลัก และนาทีหลัก 10 กลมๆ) ลง config/app.php อัตโนมัติ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customVersion = $this->argument('custom_version');

        if (!empty($customVersion)) {
            $newVersion = trim($customVersion);
        } else {
            // คำนวณรูปแบบ: V.YY-MM-DD HH:mm (นาทีปัดเศษหลัก 10 กลมๆ 00, 10, 20, 30, 40, 50)
            $thaiYear = substr((string)(date('Y') + 543), -2);
            $minute10 = floor(date('i') / 10) * 10;
            $minuteFormatted = sprintf('%02d', $minute10);
            $newVersion = "V.{$thaiYear}-" . date('m-d H:') . $minuteFormatted;
        }

        $configPath = config_path('app.php');

        if (!File::exists($configPath)) {
            $this->error("ไม่พบไฟล์ {$configPath}");
            return 1;
        }

        $content = File::get($configPath);

        // แทนที่ทั้งบรรทัด 'version' => ...
        $pattern = "/^[ \t]*'version'\s*=>\s*.*?,?$/m";
        $replacement = "    'version' => '{$newVersion}',";

        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, $replacement, $content);
        } else {
            // หากยังไม่มี key version ใน return array
            $newContent = preg_replace('/return\s*\[/', "return [\n    'version' => '{$newVersion}',", $content);
        }

        File::put($configPath, $newContent);
        @File::put(base_path('.version'), $newVersion);

        // Clear config cache
        $this->callSilent('config:clear');

        $this->info("✔ อัปเดตเวอร์ชันสำเร็จ: {$newVersion}");
        $this->line("  ไฟล์: {$configPath}");

        return 0;
    }
}
