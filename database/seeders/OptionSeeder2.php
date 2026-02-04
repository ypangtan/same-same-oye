<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Option;
use App\Models\Type;

class OptionSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $createOption = Option::create( [
            'option_name' => 'contact_us_email',
            'option_value' => 'Samasamaoye@gmail.com',
        ] );
    }
}
