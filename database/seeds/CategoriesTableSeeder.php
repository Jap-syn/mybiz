<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            ['category_id' => '1', 'gmb_category_id' => '0001', 'gmb_display_name' => 'カテゴリー１', ],
            ['category_id' => '2', 'gmb_category_id' => '0002', 'gmb_display_name' => 'カテゴリー２', ],
            ['category_id' => '3', 'gmb_category_id' => '0004', 'gmb_display_name' => 'カテゴリー３', ],
            ['category_id' => '4', 'gmb_category_id' => '0004', 'gmb_display_name' => 'カテゴリー４', ],
            ['category_id' => '5', 'gmb_category_id' => '0005', 'gmb_display_name' => 'カテゴリー５', ],
        ]);
    }
}
