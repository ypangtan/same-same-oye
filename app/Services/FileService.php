<?php

namespace App\Services;

use Illuminate\Support\Facades\{
    DB,
    Validator,
};

use App\Models\{
    FileManager,
};

use Helper;

use Carbon\Carbon;

class FileService
{
    public static function upload( $request ) {

        $createFile = FileManager::create( [
            'name' => $request->file( 'file' )->getClientOriginalName(),
            'file' => $request->file( 'file' )->store( 'file-managers', [ 'disk' => 'public' ] ),
            'type' => $request->file( 'file' )->getClientOriginalExtension() == 'pdf' ? 1 : 2,
        ] );

        return response()->json( [
            'status' => 200,
            'data' => $createFile,
            'url' => asset( 'storage/' . $createFile->file ),
        ] );
    }

    public static function ckeUpload( $request ) {
     
        $createFile = FileManager::create( [
            'name' => $request->file( 'file' )->getClientOriginalName(),
            'file' => $request->file( 'file' )->store( ( $request->source ?? 'ckeditor' ) , [ 'disk' => 'public' ] ),
            'type' => 3,
        ] );

        return response()->json( [
            'status' => 200,
            'data' => $createFile,
            'url' => asset( 'storage/' . $createFile->file ),
            'file' => $createFile->file,
            'file_name' => $createFile->name,
        ] );
    }

    public static function imageUpload( $request ) {
        $path = StorageService::upload( $request->source ?? 'image', $request->file( 'file' ) );
     
        $createFile = FileManager::create( [
            'name' => $request->file( 'file' )->getClientOriginalName(),
            'file' => $path,
            'type' => 3,
        ] );

        return response()->json( [
            'status' => 200,
            'data' => $createFile,
            'url' => StorageService::get( $path ),
            'file' => $createFile->file,
            'file_name' => $createFile->name,
        ] );
    }

    public static function songUpload( $request ) {

        $file = $request->file( 'file' );
        $mimeType = $file->getMimeType();
        $pathname = $file->getPathname();
        $clientName = $file->getClientOriginalName();

        // 验证文件是否可读
        if (empty($pathname) || !file_exists($pathname)) {
            return response()->json([
                'status'  => 422,
                'message' => '上传的文件不可读。',
            ], 422);
        }

        $duration = null;
        try {
            $ffprobe  = \FFMpeg\FFProbe::create();
            $duration = (int) round(
                $ffprobe->format($pathname)->get('duration')
            );
        } catch (\Exception $e) {
            \Log::warning('FFProbe 无法读取时长', [
                'file'  => $clientName,
                'error' => $e->getMessage(),
            ]);
        }

        $path = StorageService::upload( 'song', $file );
            
        if (str_starts_with($mimeType, 'audio/')) {
            $file_type = 1;
        } elseif (str_starts_with($mimeType, 'video/')) {
            $file_type = 2;
        } else {
            $file_type = 4;
        }
        $createFile = FileManager::create( [
            'name' => $file->getClientOriginalName(),
            'file' => $path,
            'type' => 4,
        ] );

        return response()->json( [
            'status' => 200,
            'data' => $createFile,
            'file_type' => $file_type,
            'url' => StorageService::get( $path ),
            'duration' => $duration,
            'file' => $createFile->file,
            'file_name' => $createFile->name,
        ] );
    }
}