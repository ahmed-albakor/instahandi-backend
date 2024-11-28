<?php

namespace App\Http\Controllers;

use App\Services\Helper\ImageService;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function testUploadImage()
    {
        return response()->json(
            [
                'res' =>  asset("storage/" .  request()->image->store('test1', 'public')),
            ]
        );
    }
}
