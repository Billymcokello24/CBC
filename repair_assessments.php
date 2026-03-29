<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Stancl\Tenancy\Database\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

Tenant::all()->each(function($tenant) {
    echo "Processing Tenant: {$tenant->id}\n";
    tenancy()->initialize($tenant);
    
    $migrations = [
        '2026_03_29_120553_create_assessment_items_table',
        '2026_03_29_120728_create_student_assessment_ratings_table'
    ];
    
    foreach ($migrations as $m) {
        if (!DB::table('migrations')->where('migration', $m)->exists()) {
             echo "  Dropping tables for migration {$m} to allow clean run...\n";
             DB::statement('SET FOREIGN_KEY_CHECKS=0;');
             Schema::dropIfExists('student_assessment_ratings');
             Schema::dropIfExists('assessment_items');
             DB::statement('SET FOREIGN_KEY_CHECKS=1;');
             break; // Break and let migrate command handle it
        }
    }
});

echo "Running tenants:migrate...\n";
passthru('php artisan tenants:migrate --force');
