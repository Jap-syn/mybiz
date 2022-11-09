<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('attributes')->insert([
            ['attribute_id' => '1', 'location_id' => '1', 'gmb_attributes_attribute_id' => 'has_high_chairs', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '2', 'location_id' => '1', 'gmb_attributes_attribute_id' => 'accepts_reservations', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '3', 'location_id' => '1', 'gmb_attributes_attribute_id' => 'has_delivery', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '4', 'location_id' => '2', 'gmb_attributes_attribute_id' => 'welcomes_children', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '5', 'location_id' => '2', 'gmb_attributes_attribute_id' => 'has_all_you_can_eat_always', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '6', 'location_id' => '3', 'gmb_attributes_attribute_id' => 'serves_halal_food', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '7', 'location_id' => '3', 'gmb_attributes_attribute_id' => 'requires_cash_only', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '8', 'location_id' => '3', 'gmb_attributes_attribute_id' => 'has_live_music', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '9', 'location_id' => '4', 'gmb_attributes_attribute_id' => 'has_seating_outdoors', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '10', 'location_id' => '4', 'gmb_attributes_attribute_id' => 'serves_vegetarian', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '11', 'location_id' => '5', 'gmb_attributes_attribute_id' => 'serves_alcohol', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '12', 'location_id' => '5', 'gmb_attributes_attribute_id' => 'has_takeout', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '13', 'location_id' => '6', 'gmb_attributes_attribute_id' => 'has_high_chairs', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
            ['attribute_id' => '14', 'location_id' => '6', 'gmb_attributes_attribute_id' => 'accepts_reservations', 'gmb_attributes_value_type' => 'BOOL', 'gmb_attributes_values' => 'true', 'gmb_attributes_repeated_set_values' => '', 'gmb_attributes_repeated_unset_values' => '', 'gmb_attributes_url_values' => '', ],
        ]);
    }
}
