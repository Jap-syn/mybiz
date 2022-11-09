<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accounts')->insert([
            ['account_id' => '1', 'gmb_account_id' => '1', 'gmb_account_name' => '焼肉一番かるび', 'gmb_account_type' => 'ORGANIZATION', 'gmb_account_role' => 'OWNER', 'gmb_account_state' => 'VERIFIED', 'gmb_permission_level' => 'MEMBER_LEVEL', 'is_deleted' => '0', 'gmb_orginfo_postaladdr_region_code' => 'ja', 'enterprise_id' => 1],
            ['account_id' => '2', 'gmb_account_id' => '2', 'gmb_account_name' => '丸源ラーメン', 'gmb_account_type' => 'ORGANIZATION', 'gmb_account_role' => 'OWNER', 'gmb_account_state' => 'VERIFIED', 'gmb_permission_level' => 'MEMBER_LEVEL', 'is_deleted' => '0', 'gmb_orginfo_postaladdr_region_code' => 'ja', 'enterprise_id' => 1],
            ['account_id' => '3', 'gmb_account_id' => '3', 'gmb_account_name' => 'カプリチョーザ', 'gmb_account_type' => 'ORGANIZATION', 'gmb_account_role' => 'OWNER', 'gmb_account_state' => 'VERIFIED', 'gmb_permission_level' => 'MEMBER_LEVEL', 'is_deleted' => '0', 'gmb_orginfo_postaladdr_region_code' => 'ja', 'enterprise_id' => 2],
            ['account_id' => '4', 'gmb_account_id' => '4', 'gmb_account_name' => 'Hard Rock CAFE', 'gmb_account_type' => 'ORGANIZATION', 'gmb_account_role' => 'OWNER', 'gmb_account_state' => 'VERIFIED', 'gmb_permission_level' => 'MEMBER_LEVEL', 'is_deleted' => '0', 'gmb_orginfo_postaladdr_region_code' => 'ja', 'enterprise_id' => 2],
        ]);
    }
}
