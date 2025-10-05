<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $categories = [
            [
                'en_name' => 'Song',
                'zh_name' => '歌曲',
                'status' => 10,
            ],
            [
                'en_name' => 'E-Book',
                'zh_name' => '电子书',
                'status' => 10,
            ],
            [
                'en_name' => 'Podcast',
                'zh_name' => '播客',
                'status' => 10,
            ],
        ];

        foreach ($categories as $category) {

            $createCategory = Category::create( $category );
        }
    }
}
