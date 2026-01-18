<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    public static function upload( $path, $file ) {
        $extension = $file->getClientOriginalExtension();
        $safeName = time() . '_' . uniqid() . '.' . $extension;
        $fullPath = $path . '/' . $safeName;
        return Storage::disk('r2')->put( $fullPath, file_get_contents( $file ) );
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
