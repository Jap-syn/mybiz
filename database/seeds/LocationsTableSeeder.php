<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('locations')->insert([
            ['location_id' => '1', 'account_id' => '1', 'gmb_account_id' => '001', 'gmb_location_id' => '001', 'gmb_language_code' => '', 'gmb_store_code' => '', 'gmb_location_name' => '焼肉一番かるび 座光寺店　　', 'gmb_postaladdr_admin_area' => '愛知県', 'gmb_primary_phone' => '', 'gmb_additional_phones_1' => '', 'gmb_additional_phones_2' => '', 'gmb_postaladdr_region_code' => 'ja', 'is_deleted' => '0', ],
            ['location_id' => '2', 'account_id' => '1', 'gmb_account_id' => '001', 'gmb_location_id' => '002', 'gmb_language_code' => '', 'gmb_store_code' => '', 'gmb_location_name' => '焼肉一番かるび 白根店　　　', 'gmb_postaladdr_admin_area' => '長野県', 'gmb_primary_phone' => '', 'gmb_additional_phones_1' => '', 'gmb_additional_phones_2' => '', 'gmb_postaladdr_region_code' => 'ja', 'is_deleted' => '0', ],
            ['location_id' => '3', 'account_id' => '1', 'gmb_account_id' => '001', 'gmb_location_id' => '003', 'gmb_language_code' => '', 'gmb_store_code' => '', 'gmb_location_name' => '焼肉一番かるび 北習志野店　', 'gmb_postaladdr_admin_area' => '愛知県', 'gmb_primary_phone' => '', 'gmb_additional_phones_1' => '', 'gmb_additional_phones_2' => '', 'gmb_postaladdr_region_code' => 'ja', 'is_deleted' => '0', ],
            ['location_id' => '4', 'account_id' => '2', 'gmb_account_id' => '002', 'gmb_location_id' => '004', 'gmb_language_code' => '', 'gmb_store_code' => '', 'gmb_location_name' => '丸源ラーメン つくば店　　　', 'gmb_postaladdr_admin_area' => '愛知県', 'gmb_primary_phone' => '', 'gmb_additional_phones_1' => '', 'gmb_additional_phones_2' => '', 'gmb_postaladdr_region_code' => 'ja', 'is_deleted' => '0', ],
            ['location_id' => '5', 'account_id' => '2', 'gmb_account_id' => '002', 'gmb_location_id' => '005', 'gmb_language_code' => '', 'gmb_store_code' => '', 'gmb_location_name' => '丸源ラーメン 伊丹店　　　　', 'gmb_postaladdr_admin_area' => '三重県', 'gmb_primary_phone' => '', 'gmb_additional_phones_1' => '', 'gmb_additional_phones_2' => '', 'gmb_postaladdr_region_code' => 'ja', 'is_deleted' => '0', ],
            ['location_id' => '6', 'account_id' => '2', 'gmb_account_id' => '002', 'gmb_location_id' => '006', 'gmb_language_code' => '', 'gmb_store_code' => '', 'gmb_location_name' => '丸源ラーメン 一宮バイパス店', 'gmb_postaladdr_admin_area' => '愛知県', 'gmb_primary_phone' => '', 'gmb_additional_phones_1' => '', 'gmb_additional_phones_2' => '', 'gmb_postaladdr_region_code' => 'ja', 'is_deleted' => '0', ],
            ['location_id' => '7', 'account_id' => '3', 'gmb_account_id' => '003', 'gmb_location_id' => '007', 'gmb_language_code' => '', 'gmb_store_code' => '', 'gmb_location_name' => 'カプリチョーザ JR岡山駅店', 'gmb_postaladdr_admin_area' => '愛知県', 'gmb_primary_phone' => '', 'gmb_additional_phones_1' => '', 'gmb_additional_phones_2' => '', 'gmb_postaladdr_region_code' => 'ja', 'is_deleted' => '0', ],
            ['location_id' => '8', 'account_id' => '3', 'gmb_account_id' => '003', 'gmb_location_id' => '008', 'gmb_language_code' => '', 'gmb_store_code' => '', 'gmb_location_name' => 'カプリチョーザ イオンモールりんくう泉南店', 'gmb_postaladdr_admin_area' => '愛知県', 'gmb_primary_phone' => '', 'gmb_additional_phones_1' => '', 'gmb_additional_phones_2' => '', 'gmb_postaladdr_region_code' => 'ja', 'is_deleted' => '0', ],
            ['location_id' => '9', 'account_id' => '4', 'gmb_account_id' => '004', 'gmb_location_id' => '009', 'gmb_language_code' => '', 'gmb_store_code' => '', 'gmb_location_name' => 'ハードロックカフェ京都', 'gmb_postaladdr_admin_area' => '京都府', 'gmb_primary_phone' => '', 'gmb_additional_phones_1' => '', 'gmb_additional_phones_2' => '', 'gmb_postaladdr_region_code' => 'ja', 'is_deleted' => '0', ],
            ['location_id' => '10', 'account_id' => '4', 'gmb_account_id' => '004', 'gmb_location_id' => '010', 'gmb_language_code' => '', 'gmb_store_code' => '', 'gmb_location_name' => 'ハードロックカフェ ユニバーサル・シティウォーク大阪™', 'gmb_postaladdr_admin_area' => '大阪府', 'gmb_primary_phone' => '', 'gmb_additional_phones_1' => '', 'gmb_additional_phones_2' => '', 'gmb_postaladdr_region_code' => 'ja', 'is_deleted' => '0', ],
        ]);
    }
}
