<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Rank;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $ranks = [
            [
                'title' => 'Member',
                'description' => '',
                'target_spending' => '0',
                'reward_value' => '1',
                'status' => '10',
            ],
            [
                'title' => 'Silver',
                'description' => '',
                'target_spending' => '3000',
                'reward_value' => '2',
                'status' => '10',
            ],
            [
                'title' => 'Gold',
                'description' => '',
                'target_spending' => '30000',
                'reward_value' => '3',
                'status' => '10',
            ],
            [
                'title' => 'Premium',
                'description' => '',
                'target_spending' => '100000',
                'reward_value' => '4',
                'status' => '10',
            ],
        ];

        foreach ($ranks as $key => $rank) {
            $rank['priority'] = $key;
            $createRank = Rank::create( $rank );
        }
    }
}
