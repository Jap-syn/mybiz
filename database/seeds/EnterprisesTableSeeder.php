<?php

use Illuminate\Database\Seeder;

class EnterprisesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('enterprises')->insert([
            ['enterprise_id' => 1, 'name' => '株式会社ParaWorks', 'contract_type' => 0 ],
            ['enterprise_id' => 2, 'name' => '物語コーポレーション', 'contract_type' => 1 ],
            ['enterprise_id' => 3, 'name' => '株式会社 WDI', 'contract_type' => 2 ],
            ['enterprise_id' => 4, 'name' => '株式会社コンプリート・サークル', 'contract_type' => 3 ],
            ['enterprise_id' => 5, 'name' => '株式会社 ユウ・フード・サービス', 'contract_type' => 4 ],
        ]);
    }
}
