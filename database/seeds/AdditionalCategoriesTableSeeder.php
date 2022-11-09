<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdditionalCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('additional_categories')->insert([
            ['location_id' => '1', 'category_id' => '1', ],
            ['location_id' => '2', 'category_id' => '2', ],
            ['location_id' => '3', 'category_id' => '3', ],
            ['location_id' => '4', 'category_id' => '4', ],
            ['location_id' => '5', 'category_id' => '4', ],
            ['location_id' => '6', 'category_id' => '5', ],
        ]);
    }
}
