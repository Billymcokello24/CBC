<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurriculumResetSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('competency_indicators')->delete();
        DB::table('sub_strands')->delete();
        DB::table('strands')->delete();
        DB::table('subjects')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->call(CurriculumSeeder::class);
    }
}
