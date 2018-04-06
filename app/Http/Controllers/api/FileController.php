<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\File;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
        // file test
    public function test(Request $request)
    {
      $file_ids = array();
      if ($request->hasFile('photo')) {
          $files = $request->file('photo');
          foreach ($files as $file) {
            
            $file_name = str_random(8).'.'.$file->guessClientExtension();
            $path = '/storage/user_doc/'.Auth::user()->id.Auth::user()->password.'/'.$file_name;
            $file->move('./storage/user_doc/'.Auth::user()->id.Auth::user()->password.'/',$file_name );
            $file_id = File::create(['path'=>$path])->id;
            array_push($file_ids, $file_id);
          }
         
      }
      return response()->json($file_ids);
    }
}
