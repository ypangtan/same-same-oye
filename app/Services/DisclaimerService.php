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
    Disclaimer,
};

use App\Rules\CheckASCIICharacter;

use Helper;

use Carbon\Carbon;

class DisclaimerService
{
    public static function getDisclaimer() {

        $disclaimer = Disclaimer::first();

        return response()->json( [
            'data' => $disclaimer,
        ] );
    }

    public static function updateDisclaimer( $request ) {

        $validator = Validator::make( $request->all(), [
            'content' => [ 'required' ],
        ] );

        $attributeName = [
            'content' => __( 'disclaimer.content' ),
        ];

        foreach ( $attributeName as $key => $aName ) {
            $attributeName[$key] = strtolower( $aName );
        }
        
        $validator->setAttributeNames( $attributeName )->validate();


        DB::beginTransaction();

        try {
            $disclaimer = Disclaimer::first();
            if( !$disclaimer ) {
                $disclaimer = Disclaimer::create( [
                    'content' => $request->content,
                ] );
            } else {
                $disclaimer->content = $request->content;
                $disclaimer->save();
            }

            DB::commit();

        } catch ( \Throwable $th ) {

            DB::rollback();

            return response()->json( [
                'message' => $th->getMessage() . ' in line: ' . $th->getLine(),
            ], 500 );
        }

        return response()->json( [
            'message' => __( 'template.x_updated', [ 'title' => Str::singular( __( 'template.disclaimers' ) ) ] ),
        ] );
    }
}