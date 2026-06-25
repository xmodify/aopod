<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchemaUpgradeService
{
    /**
     * Build the SQL column definition snippet from JSON definition.
     */
    private function buildColumnSql($name, $colDef)
    {
        $sql = "`$name` " . $colDef['type'];
        
        if ($colDef['null'] === 'NO') {
            $sql .= " NOT NULL";
        } else {
            $sql .= " NULL";
        }
        
        if ($colDef['default'] !== null) {
            if (strtoupper($colDef['default']) === 'CURRENT_TIMESTAMP') {
                $sql .= " DEFAULT CURRENT_TIMESTAMP";
            } else {
                $sql .= " DEFAULT " . DB::getPdo()->quote($colDef['default']);
            }
        }
        
        if (!empty($colDef['extra'])) {
            $sql .= " " . $colDef['extra'];
        }
        
        return $sql;
    }

    /**
     * Run the upgrade process yielding progress status and messages.
     * 
     * @param callable $onProgress Callback function(int $percent, int $step, string $status, string $details, array $changes, array $seedsSummary)
     */
    public function upgrade(callable $onProgress)
    {
        $changes = [];
        $seedsSummary = [];

        $onProgress(5, 1, 'ขั้นตอนที่ 1/2: ตรวจสอบและอัปเกรดโครงสร้างตารางระบบทั้งหมด...', 'กำลังอ่านไฟล์โครงสร้างฐานข้อมูล...', [], []);

        $schemaPath = base_path('docs/extracted_schemas.json');
        if (!file_exists($schemaPath)) {
            $onProgress(10, 1, 'ขั้นตอนที่ 1/2: ตรวจสอบและอัปเกรดโครงสร้างตารางระบบทั้งหมด...', 'ไม่พบไฟล์ docs/extracted_schemas.json', [], []);
            return;
        }

        $jsonSchema = json_decode(file_get_contents($schemaPath), true);
        if (!$jsonSchema) {
            $onProgress(10, 1, 'ขั้นตอนที่ 1/2: ตรวจสอบและอัปเกรดโครงสร้างตารางระบบทั้งหมด...', 'ไฟล์ docs/extracted_schemas.json มีรูปแบบไม่ถูกต้อง', [], []);
            return;
        }

        // Exclude system tables from being dropped
        $excludeTables = ['migrations', 'sessions', 'personal_access_tokens', 'password_reset_tokens'];

        // Get current tables
        $dbName = config('database.connections.mysql.database');
        $tablesRaw = DB::select('SHOW TABLES');
        $dbTables = [];
        $key = "Tables_in_{$dbName}";
        foreach ($tablesRaw as $tableObj) {
            if (isset($tableObj->$key)) {
                $dbTables[] = $tableObj->$key;
            } else {
                $vars = get_object_vars($tableObj);
                $dbTables[] = reset($vars);
            }
        }

        // 1. Tables to drop (exist in DB but not in JSON)
        $tablesToDrop = array_diff($dbTables, array_keys($jsonSchema));
        $tablesToDrop = array_diff($tablesToDrop, $excludeTables);

        foreach ($tablesToDrop as $table) {
            $changes[] = "-ตาราง {$table}";
            DB::statement("DROP TABLE `$table`");
        }

        // 2. Tables to create (exist in JSON but not in DB)
        $tablesToCreate = array_diff(array_keys($jsonSchema), $dbTables);
        $totalToCreate = count($tablesToCreate);
        $createdCount = 0;

        foreach ($tablesToCreate as $table) {
            $createdCount++;
            $percent = 15 + (int)(($createdCount / max(1, $totalToCreate)) * 20); // 15% to 35%
            $onProgress($percent, 1, 'ขั้นตอนที่ 1/2: ตรวจสอบและอัปเกรดโครงสร้างตารางระบบทั้งหมด...', "กำลังสร้างตาราง {$table}...", $changes, []);

            $changes[] = "+ตาราง {$table}";
            $tableDef = $jsonSchema[$table];
            $colSqls = [];
            foreach ($tableDef['columns'] as $colName => $colDef) {
                $colSqls[] = $this->buildColumnSql($colName, $colDef);
            }

            // Include primary key in creation if defined
            $primaryKeyColumns = [];
            if (isset($tableDef['indexes']['PRIMARY'])) {
                $primaryKeyColumns = $tableDef['indexes']['PRIMARY']['columns'];
            }

            $createSql = "CREATE TABLE `$table` (\n  " . implode(",\n  ", $colSqls);
            if (!empty($primaryKeyColumns)) {
                $createSql .= ",\n  PRIMARY KEY (" . implode(", ", array_map(fn($c) => "`$c`", $primaryKeyColumns)) . ")";
            }
            $createSql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

            DB::statement($createSql);

            // Add other indexes
            foreach ($tableDef['indexes'] as $indexName => $idxDef) {
                if ($indexName === 'PRIMARY') continue;
                $colsList = implode(", ", array_map(fn($c) => "`$c`", $idxDef['columns']));
                if ($idxDef['unique']) {
                    DB::statement("ALTER TABLE `$table` ADD UNIQUE `$indexName` ($colsList)");
                } else {
                    DB::statement("ALTER TABLE `$table` ADD INDEX `$indexName` ($colsList)");
                }
            }
        }

        // 3. Tables to modify (exist in both JSON and DB)
        $tablesToCompare = array_intersect(array_keys($jsonSchema), $dbTables);
        $totalToCompare = count($tablesToCompare);
        $comparedCount = 0;

        foreach ($tablesToCompare as $table) {
            $comparedCount++;
            $percent = 35 + (int)(($comparedCount / max(1, $totalToCompare)) * 30); // 35% to 65%
            
            // Get current columns
            $colsRaw = DB::select("SHOW FULL COLUMNS FROM `$table`");
            $dbColumns = [];
            foreach ($colsRaw as $col) {
                $dbColumns[$col->Field] = [
                    'type' => $col->Type,
                    'null' => $col->Null,
                    'key' => $col->Key,
                    'default' => $col->Default,
                    'extra' => $col->Extra
                ];
            }

            $jsonColumns = $jsonSchema[$table]['columns'];

            // Columns to Add
            $colsToAdd = array_diff(array_keys($jsonColumns), array_keys($dbColumns));
            foreach ($colsToAdd as $col) {
                $changes[] = "{$table}: +{$col}";
                $colDef = $jsonColumns[$col];
                $colSql = $this->buildColumnSql($col, $colDef);
                DB::statement("ALTER TABLE `$table` ADD $colSql");
            }

            // Columns to Drop
            $colsToDrop = array_diff(array_keys($dbColumns), array_keys($jsonColumns));
            foreach ($colsToDrop as $col) {
                $changes[] = "{$table}: -{$col}";
                DB::statement("ALTER TABLE `$table` DROP COLUMN `$col`");
            }

            // Columns to Modify
            $colsToCompare = array_intersect(array_keys($jsonColumns), array_keys($dbColumns));
            foreach ($colsToCompare as $col) {
                $jCol = $jsonColumns[$col];
                $dCol = $dbColumns[$col];

                $typeMismatched = strtolower(str_replace(' ', '', $jCol['type'])) !== strtolower(str_replace(' ', '', $dCol['type']));
                $nullMismatched = $jCol['null'] !== $dCol['null'];
                $defaultMismatched = $jCol['default'] !== $dCol['default'];
                $extraMismatched = $jCol['extra'] !== $dCol['extra'];

                if ($typeMismatched || $nullMismatched || $defaultMismatched || $extraMismatched) {
                    $changes[] = "{$table}: *{$col}";
                    $colSql = $this->buildColumnSql($col, $jCol);
                    DB::statement("ALTER TABLE `$table` MODIFY $colSql");
                }
            }

            // Compare indexes
            $idxRaw = DB::select("SHOW INDEX FROM `$table`");
            $dbIndexes = [];
            foreach ($idxRaw as $idx) {
                $keyName = $idx->Key_name;
                if (!isset($dbIndexes[$keyName])) {
                    $dbIndexes[$keyName] = [
                        'unique' => $idx->Non_unique == 0,
                        'columns' => []
                    ];
                }
                $dbIndexes[$keyName]['columns'][] = $idx->Column_name;
            }

            $jsonIndexes = $jsonSchema[$table]['indexes'];

            // Drop removed indexes
            $idxsToDrop = array_diff(array_keys($dbIndexes), array_keys($jsonIndexes));
            foreach ($idxsToDrop as $idxName) {
                if ($idxName === 'PRIMARY') {
                    DB::statement("ALTER TABLE `$table` DROP PRIMARY KEY");
                } else {
                    DB::statement("ALTER TABLE `$table` DROP INDEX `$idxName`");
                }
            }

            // Add new or modified indexes
            foreach ($jsonIndexes as $idxName => $jIdx) {
                $shouldCreate = false;
                if (!isset($dbIndexes[$idxName])) {
                    $shouldCreate = true;
                } else {
                    $dIdx = $dbIndexes[$idxName];
                    if ($jIdx['unique'] !== $dIdx['unique'] || $jIdx['columns'] !== $dIdx['columns']) {
                        if ($idxName === 'PRIMARY') {
                            DB::statement("ALTER TABLE `$table` DROP PRIMARY KEY");
                        } else {
                            DB::statement("ALTER TABLE `$table` DROP INDEX `$idxName`");
                        }
                        $shouldCreate = true;
                    }
                }

                if ($shouldCreate) {
                    $colsList = implode(", ", array_map(fn($c) => "`$c`", $jIdx['columns']));
                    if ($idxName === 'PRIMARY') {
                        DB::statement("ALTER TABLE `$table` ADD PRIMARY KEY ($colsList)");
                    } elseif ($jIdx['unique']) {
                        DB::statement("ALTER TABLE `$table` ADD UNIQUE `$idxName` ($colsList)");
                    } else {
                        DB::statement("ALTER TABLE `$table` ADD INDEX `$idxName` ($colsList)");
                    }
                }
            }
        }

        // Complete Step 1
        $detailStep1 = 'ตรวจสอบโครงสร้างทุกตารางสำเร็จ';
        if (count($changes) > 0) {
            $detailStep1 .= ' (ปรับปรุง: ' . implode(', ', $changes) . ')';
        }
        $onProgress(70, 1, 'ขั้นตอนที่ 1/2: ตรวจสอบและอัปเกรดโครงสร้างตารางระบบทั้งหมด...', $detailStep1, $changes, []);

        // Start Step 2
        $onProgress(75, 2, 'ขั้นตอนที่ 2/2: นำเข้า/ซิงค์ข้อมูลพื้นฐาน (ipd_bed_type, budget_year)...', 'กำลังเริ่มนำเข้าข้อมูลพื้นฐาน...', $changes, []);

        // 4. Seeding Default Data
        $seedsPath = base_path('docs/default_seeds.json');
        if (!file_exists($seedsPath)) {
            $onProgress(100, 2, 'ขั้นตอนที่ 2/2: นำเข้า/ซิงค์ข้อมูลพื้นฐาน (ipd_bed_type, budget_year)...', 'ไม่พบไฟล์ docs/default_seeds.json', $changes, []);
            return;
        }

        $seeds = json_decode(file_get_contents($seedsPath), true);
        if (empty($seeds)) {
            $onProgress(100, 2, 'ขั้นตอนที่ 2/2: นำเข้า/ซิงค์ข้อมูลพื้นฐาน (ipd_bed_type, budget_year)...', 'ไม่มีข้อมูลตั้งต้นใน default_seeds.json', $changes, []);
            return;
        }

        // Import ipd_bed_type
        if (isset($seeds['ipd_bed_type']) && count($seeds['ipd_bed_type']) > 0) {
            $bedTypes = $seeds['ipd_bed_type'];
            $totalBeds = count($bedTypes);
            foreach ($bedTypes as $index => $row) {
                $subPercent = 75 + (int)(($index / $totalBeds) * 12); // 75% to 87%
                $onProgress($subPercent, 2, 'ขั้นตอนที่ 2/2: นำเข้า/ซิงค์ข้อมูลพื้นฐาน (ipd_bed_type, budget_year)...', "กำลังซิงค์ข้อมูลประเภทเตียง: {$row['bed_name']}...", $changes, []);
                
                DB::table('ipd_bed_type')->updateOrInsert(
                    ['bed_code' => $row['bed_code']],
                    $row
                );
            }
            $seedsSummary[] = "ipd_bed_type ({$totalBeds} รายการ)";
        }

        // Import budget_year
        if (isset($seeds['budget_year']) && count($seeds['budget_year']) > 0) {
            $budgetYears = $seeds['budget_year'];
            $totalYears = count($budgetYears);
            foreach ($budgetYears as $index => $row) {
                $subPercent = 87 + (int)(($index / $totalYears) * 12); // 87% to 99%
                $onProgress($subPercent, 2, 'ขั้นตอนที่ 2/2: นำเข้า/ซิงค์ข้อมูลพื้นฐาน (ipd_bed_type, budget_year)...', "กำลังซิงค์ข้อมูลปีงบประมาณ: {$row['LEAVE_YEAR_NAME']}...", $changes, []);
                
                DB::table('budget_year')->updateOrInsert(
                    ['LEAVE_YEAR_ID' => $row['LEAVE_YEAR_ID']],
                    $row
                );
            }
            $seedsSummary[] = "budget_year ({$totalYears} รายการ)";
        }

        // Complete Step 2 & Finish
        $onProgress(100, 2, 'ขั้นตอนที่ 2/2: นำเข้า/ซิงค์ข้อมูลพื้นฐาน (ipd_bed_type, budget_year)...', 'นำเข้าข้อมูลพื้นฐานสำเร็จ', $changes, $seedsSummary);
    }
}
