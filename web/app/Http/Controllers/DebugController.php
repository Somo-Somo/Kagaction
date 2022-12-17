<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugController extends Controller
{
    public function debug(Request $request)
    {
        $data = 'return';
        Log::debug($data);
        return;
    }
}
