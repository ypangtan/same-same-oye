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
     
        $createFile = FileManager::create( [
            'name' => $request->file( 'file' )->getClientOriginalName(),
            'file' => $request->file( 'file' )->store( ( $request->source ?? 'image' ) , [ 'disk' => 'public' ] ),
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

    public static function songUpload( $request ) {

        $path = StorageService::upload( 'song', $request->file( 'file' ) );

        $createFile = FileManager::create( [
            'name' => $path['original_name'],
            'file' => $path['path'],
            'type' => 4,
        ] );

        return response()->json( [
            'status' => 200,
            'data' => $createFile,
            'url' => StorageService::get( $path['path'] ),
            'file' => $createFile->file,
            'file_name' => $createFile->name,
        ] );
    }
}