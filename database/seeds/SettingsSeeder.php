<?php

use Illuminate\Database\Seeder;
use Tortuga\SettingsName;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $settings = [];

        $settings[SettingsName::IS_OPEN_FOR_BOOKING()] = 1;
        $settings[SettingsName::MAX_ORDERS_PER_SLOT()] = 3;

        foreach ($settings as $name => $value) {
            if (!\App\Settings::where('name', '=', $name)->count()) {
                $item        = new \App\Settings();
                $item->name  = $name;
                $item->value = $value;
                $item->save();
            }
        }
    }
}
