<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    public static function upload( $path, $file ) {
        return Storage::disk('r2')->put( $path, $file );
    }

    public static function delete( $path ) {
        return Storage::disk('r2')->delete( $path );
    }

    public static function exists( $path ) {
        return Storage::disk('r2')->exists( $path );
    }

    public static function get( $path ) {
        return Storage::disk('r2')->get( $path );
    }
}
