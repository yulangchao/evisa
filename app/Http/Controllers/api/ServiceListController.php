<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\File;

class ServiceListController extends Controller
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
            $file->move('/storage/user_doc/'.Auth::user()->id.Auth::user()->password.'/',$file_name );
            $file_id = File::create(['path'=>$path])->id;
            array_push($file_ids, $file_id);
          }
         
      }
      return response()->json($file_ids);
    }
  
  
  
  //申请逻辑
  
  
  
  
  
  
    public function checkCaVisa(Request $request)
    {
      $order = \DB::table('ca_visa_orders')->where('user_id', Auth::user()->id)->first();
      if ($order){
        return response()->json(['status'=>true,'order'=>$order,'ifexist'=> true]);
      }
      
      return response()->json(['status'=>true,'order'=>null,'ifexist'=> false]);
    }
  
    public function applyCaVisa(Request $request)
    {

      $order_id = \DB::table('ca_visa_orders')->insertGetId(
          ['user_id' => Auth::user()->id,           
           "created_at" =>  \Carbon\Carbon::now(), # \Datetime()
            "updated_at" => \Carbon\Carbon::now(),  # \Datetime()
          ]
      );
      return response()->json(['status'=>true,'order_id'=>$order_id]);
    }
  
    public function getCaVisaBaseInfo(Request $request)
    {
      $order = \DB::table('ca_visa_orders')->where(['id'=> request('id'),'user_id' => Auth::user()->id])->first();
      if (!$order){
        return response()->json(['status'=>false,'error'=>'此订单不存在']);
      }
      
      if ($order->base_info_status == 0){
        
        $user_base_info = \DB::table('user_base_infos')->where('user_id', Auth::user()->id)->first();
        return response()->json(['status'=>true,'base_user_info'=>$user_base_info, 'source' =>'info']);
       
      }
      return response()->json(['status'=>true,'base_user_info'=>$order, 'source' =>'order']);
    }
  
    public function saveCaVisaBaseInfo(Request $request)
    {
      $order = \DB::table('ca_visa_orders')->where(['id'=> request('id'),'user_id' => Auth::user()->id])->first();
      if (!$order){
        return response()->json(['status'=>false,'error'=>'此订单不存在']);
      }
      $order = \DB::table('ca_visa_orders')->where('id', request('id'))->update(
        ['base_info_status' => 1,
         'birthday' => request('birthday'), 
         "updated_at" => \Carbon\Carbon::now(),
         'family_name' => request('family_name'), 
         'given_name' => request('given_name'), 
         'if_has_other_name' => request('if_has_other_name'), 
         'other_family_name' => request('other_family_name'), 
         'other_given_name' => request('other_given_name'), 
         'sex' => request('sex'), 
         'birth_place' => request('birth_place'), 
         'birth_country' => request('birth_country'), 
         'citizenship' => request('citizenship')
        ]);
      
      
      $update_user_base_info = \App\UserBaseInfo::firstOrNew(array('user_id' => Auth::user()->id));

      $update_user_base_info->user_id = Auth::user()->id;
      $update_user_base_info->birthday = request('birthday');
      $update_user_base_info->family_name = request('family_name');
      $update_user_base_info->given_name= request('given_name');
      $update_user_base_info->if_has_other_name=request('if_has_other_name');
      $update_user_base_info->other_family_name= request('other_family_name');
      $update_user_base_info->other_given_name= request('other_given_name');
      $update_user_base_info->sex= request('sex');
      $update_user_base_info->birth_place=request('birth_place');
      $update_user_base_info->birth_country= request('birth_country');
      $update_user_base_info->citizenship= request('citizenship');
      $update_user_base_info->save();
      return response()->json(['status'=>true,'message'=>'更新完成']);
    }
}
