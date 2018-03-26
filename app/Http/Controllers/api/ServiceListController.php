<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ServiceListController extends Controller
{
    public function test(Request $request)
    {
      
      if ($request->hasFile('photo')) {
          $files = $request->file('photo');
          foreach ($files as $file) {
            
            $file_name = str_random(8).'.'.$file->guessClientExtension();
            $file->move('./storage/user_doc/'.Auth::user()->id.'/',$file_name );
          }
         
      }
      return response()->json('done');
    }
}
