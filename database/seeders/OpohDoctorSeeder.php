<?php

namespace Database\Seeders;

use App\Models\OpohDoctor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class OpohDoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/opoh_doctor_seeder.json');
        if (!File::exists($jsonPath)) {
            $jsonPath = base_path('docs/opoh_doctor_seeder.json');
        }

        if (!File::exists($jsonPath)) {
            $this->command->error("Could not find opoh_doctor_seeder.json in database/seeders or docs/");
            return;
        }

        $doctors = json_decode(File::get($jsonPath), true);
        if (empty($doctors) || !is_array($doctors)) {
            $this->command->error("Invalid or empty data in opoh_doctor_seeder.json");
            return;
        }

        $count = 0;
        foreach ($doctors as $item) {
            OpohDoctor::updateOrCreate(
                [
                    'fname' => trim($item['fname']),
                    'lname' => trim($item['lname']),
                ],
                [
                    'pname'          => !empty($item['pname']) ? trim($item['pname']) : null,
                    'doctor_name'    => !empty($item['doctor_name']) ? trim($item['doctor_name']) : trim(($item['pname'] ?? '') . ' ' . $item['fname'] . ' ' . $item['lname']),
                    'cid'            => !empty($item['cid']) ? trim($item['cid']) : null,
                    'licenseno'      => !empty($item['licenseno']) ? trim($item['licenseno']) : null,
                    'spclty_code'    => trim($item['spclty_code']),
                    'specialty_name' => trim($item['specialty_name']),
                    'position'       => !empty($item['position']) ? trim($item['position']) : null,
                    'is_active'      => !empty($item['is_active']) ? trim($item['is_active']) : 'Y',
                ]
            );
            $count++;
        }

        $this->command->info("Successfully seeded {$count} OPOH doctors.");
    }
}

