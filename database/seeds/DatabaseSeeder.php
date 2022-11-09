<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // 外部キー制限があるのでこの順序でシーディングする
            AccountsTableSeeder::class,
            LocationsTableSeeder::class,
            CategoriesTableSeeder::class,
            AdditionalCategoriesTableSeeder::class,
            AttributesTableSeeder::class,
            EnterprisesTableSeeder::class,
            // EnterpriseAccountsTableSeeder::class,
            RolesTableSeeder::class,

            BusinessHoursTableSeeder::class,
            LocalPostsTableSeeder::class,
            LocalPostGroupsTableSeeder::class,
            MediaItemsTableSeeder::class,
            ReviewsTableSeeder::class,
            ReviewRepliesTableSeeder::class,
            ReviewReplyTemplatesTableSeeder::class,
            SpecialHoursTableSeeder::class,
            UsersTableSeeder::class,
            UserRolesTableSeeder::class,
            LocationReportsTableSeeder::class,
            ReviewAggregatesTableSeeder::class,
            MediaItem2GroupsTableSeeder::class,
            MediaItems2TableSeeder::class,
        ]);
    }
}
