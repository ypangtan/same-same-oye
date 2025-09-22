<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Storage,
    Validator,
};

use App\Models\{
    AppVersion,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class AppVersionService
{
    public static function lastestAppVersion( $request ) {

        $platform = $request->platform ?? 1;

        $app_version = AppVersion::where( 'platform', $platform )
            ->first();

        $app_version->append( [
            'notes',
            'desc'
        ] );

        return response()->json( [
            'data' => $app_version,
        ] );
    }
}