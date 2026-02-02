<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Option;
use App\Models\Type;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $createType = Option::create( [
            'option_name' => 'trial_period_days',
            'option_value' => '14',
        ] );
    }
}
