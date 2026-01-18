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
        $disk = Storage::disk('r2');
    
        if (method_exists($disk, 'temporaryUrl')) {
            return $disk->temporaryUrl($path, now()->addHours(24));
        }
        
        $publicUrl = config('filesystems.disks.r2.url');
        if ($publicUrl) {
            return $publicUrl . '/' . $path;
        }
        
        return $disk->url($path);
    }
}
