<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\{
    CategoryService,
    CollectionService,
    FileManagerService,
    FileService,
};

class CategoryController extends Controller
{
    public function allCategories( Request $request ) {
        return CategoryService::allCategories( $request );
    }
}
