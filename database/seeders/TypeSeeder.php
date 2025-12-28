<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Type;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $types = [
            [
                'en_name' => 'Song',
                // 'zh_name' => '歌曲',
                'status' => 10,
            ],
            [
                'en_name' => 'Podcast',
                // 'zh_name' => '电子书',
                'status' => 10,
            ],
            [
                'en_name' => 'Talk',
                // 'zh_name' => '播客',
                'status' => 10,
            ],
        ];

        foreach ($types as $type) {

            $createType = Type::create( $type );
        }
    }
}
