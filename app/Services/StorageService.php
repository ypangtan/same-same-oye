<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    public static function upload( $path, $file ) {
        $originalName = $file->getClientOriginalName();
        if (!mb_check_encoding($originalName, 'UTF-8')) {
            $originalName = mb_convert_encoding($originalName, 'UTF-8', 'auto');
        }
        
        $extension = $file->getClientOriginalExtension();
        $safeName = time() . '_' . uniqid() . '.' . $extension;
        
        $result = Storage::disk('r2')->put($path, $file, $safeName);

        return [
            'path' => $result,
            'original_name' => $originalName,
        ];
    }

    public static function delete( $path ) {
        return Storage::disk('r2')->delete( $path );
    }

    public static function exists( $path ) {
        return Storage::disk('r2')->exists( $path );
    }

    public static function get( $path ) {
        return Storage::disk('r2')->url( $path );
    }
}
