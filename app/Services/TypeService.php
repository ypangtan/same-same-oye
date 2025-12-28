<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\{
    DB,
    Hash,
    Storage,
    Validator,
};

use Illuminate\Validation\Rules\Password;

use App\Models\{
    FileManager,
    Category,
    User,
    Role as RoleModel,
    Type
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

use PragmaRX\Google2FAQRCode\Google2FA;

class TypeService
{
    // api
    public static function getTypes( $request ) {

        $per_page = $request->input( 'per_page', 10 );

        $types = Type::where( 'status', '10' )
            ->orderBy( 'created_at', 'DESC' )
            ->paginate( $per_page );

        if ( $types ) {
            $types->append( [
                'encrypted_id',
            ] );
        }

        return response()->json( [ 'data' => $types ] );
    }
}