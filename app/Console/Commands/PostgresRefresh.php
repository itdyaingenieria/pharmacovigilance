<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PostgresRefresh extends Command
{
    protected $signature = 'pg:refresh
                          {--seed : Run seeders}
                          {--seeder= : Specific seeder class}';

    protected $description = 'Full PostgreSQL refresh (including schemas)';

    public function handle()
    {
        // 1. Drop all schemas
        $schemas = ['general_settings', 'public']; // Add more schemas if needed

        foreach ($schemas as $schema) {
            DB::statement("DROP SCHEMA IF EXISTS $schema CASCADE");
        }

        // 2. Recreate public schema
        DB::statement('CREATE SCHEMA public');

        // 3. Run migrations
        $this->call('migrate');

        // 4. Run seeder if requested
        if ($this->option('seed')) {
            $seeder = $this->option('seeder') ?: 'Database\\Seeders\\DatabaseSeeder';
            $this->call('db:seed', ['--class' => $seeder]);
        }

        $this->info('PostgreSQL database refreshed successfully.');
    }
}
