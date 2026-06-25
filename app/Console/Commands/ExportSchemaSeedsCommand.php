<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportSchemaSeedsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export-schema-seeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export database schema and default seeds to JSON files in docs/ directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database schema and seeds export...');

        // 1. Export Database Schema
        $tables = [];
        $dbName = config('database.connections.mysql.database');
        $tablesRaw = DB::select('SHOW TABLES');
        $key = "Tables_in_{$dbName}";
        foreach ($tablesRaw as $tableObj) {
            if (isset($tableObj->$key)) {
                $tables[] = $tableObj->$key;
            } else {
                // Try getting first property
                $vars = get_object_vars($tableObj);
                $tables[] = reset($vars);
            }
        }

        $schema = [];
        foreach ($tables as $tableName) {
            $this->comment("Extracting schema for table: {$tableName}");
            $columnsRaw = DB::select("SHOW FULL COLUMNS FROM `$tableName`");
            $indexesRaw = DB::select("SHOW INDEX FROM `$tableName`");

            $columns = [];
            foreach ($columnsRaw as $col) {
                $columns[$col->Field] = [
                    'type' => $col->Type,
                    'null' => $col->Null, // "YES" or "NO"
                    'key' => $col->Key, // "PRI", "UNI", "MUL"
                    'default' => $col->Default,
                    'extra' => $col->Extra, // "auto_increment", etc.
                ];
            }

            $indexes = [];
            foreach ($indexesRaw as $idx) {
                $keyName = $idx->Key_name;
                if (!isset($indexes[$keyName])) {
                    $indexes[$keyName] = [
                        'unique' => $idx->Non_unique == 0,
                        'columns' => []
                    ];
                }
                $indexes[$keyName]['columns'][] = $idx->Column_name;
            }

            $schema[$tableName] = [
                'columns' => $columns,
                'indexes' => $indexes
            ];
        }

        $docsPath = base_path('docs');
        if (!is_dir($docsPath)) {
            mkdir($docsPath, 0755, true);
        }

        $schemaFilePath = $docsPath . '/extracted_schemas.json';
        file_put_contents($schemaFilePath, json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("Schema exported successfully to: docs/extracted_schemas.json");

        // 2. Export default seeds from ipd_bed_type and budget_year
        $this->info('Exporting default seeds...');
        $seeds = [
            'ipd_bed_type' => [],
            'budget_year' => []
        ];

        if (Schema::hasTable('ipd_bed_type')) {
            $seeds['ipd_bed_type'] = DB::table('ipd_bed_type')->get()->map(function ($row) {
                return (array) $row;
            })->toArray();
        }

        if (Schema::hasTable('budget_year')) {
            $seeds['budget_year'] = DB::table('budget_year')->get()->map(function ($row) {
                return (array) $row;
            })->toArray();
        }

        $seedsFilePath = $docsPath . '/default_seeds.json';
        file_put_contents($seedsFilePath, json_encode($seeds, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("Seeds exported successfully to: docs/default_seeds.json");

        $this->info('All exports completed successfully!');
        return 0;
    }
}
