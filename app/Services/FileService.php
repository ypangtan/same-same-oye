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

        $path = StorageService::upload( 'song', $request->file( 'file' ) );
        $mimeType = $request->file( 'file' )->getMimeType();
            
        if (str_starts_with($mimeType, 'audio/')) {
            $file_type = 1;
        } elseif (str_starts_with($mimeType, 'video/')) {
            $file_type = 2;
        } else {
            $file_type = 4;
        }
        $createFile = FileManager::create( [
            'name' => $request->file( 'file' )->getClientOriginalName(),
            'file' => $path,
            'type' => 4,
        ] );

        return response()->json( [
            'status' => 200,
            'data' => $createFile,
            'file_type' => $file_type,
            'url' => StorageService::get( $path ),
            'file' => $createFile->file,
            'file_name' => $createFile->name,
        ] );
    }
}