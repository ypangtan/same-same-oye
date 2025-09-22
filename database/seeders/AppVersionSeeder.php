<?php

namespace Database\Seeders;

use App\Models\AppVersion;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Rank;

class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $appVersions = [
            [
                'version' => '1',
                'force_logout' => '10',
                'status' => '10',
                'platform' => '1',
            ],
            [
                'version' => '1',
                'force_logout' => '10',
                'status' => '10',
                'platform' => '2',
            ],
            [
                'version' => '1',
                'force_logout' => '10',
                'status' => '10',
                'platform' => '3',
            ],
        ];

        $en_notes = 'New Version is now available. Please update to the latest version for the best experience.';
        $zh_notes = '新版本現已推出。請更新至最新版本以獲得最佳體驗。';

        foreach ($appVersions as $key => $appVersion) {
            $appVersion['en_notes'] = $en_notes;
            $appVersion['zh_notes'] = $zh_notes;
            $appVersion['en_desc'] = '';
            $appVersion['zh_desc'] = '';
            $createAppVersion = AppVersion::create( $appVersion );
        }
    }
}
