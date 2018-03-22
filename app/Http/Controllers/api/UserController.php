<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Post;
use Response;
use App\SmsRecord;
use Twilio;
use Validator;
use Hash;

class UserController extends Controller
{
    public function __construct()
    {
        
    }
    public function login()
    {
        if(Auth::attempt(['phone' => request('phone'), 'password' => request('password')]))
        {
            $user = Auth::user();
            $token = $user->createToken('Pizza App')->accessToken;
            return response()->json(['success'=> true, 'token'=> $token]);   
        } else {
            $error = "账号信息错误";
            return response()->json(['success'=> false, 'error'=> $error]);   
        }
         
    }
  
  
    public function register(Request $request)
    {
        $credentials = $request->only('name', 'phone' ,'email', 'password');
      

        $rules = [
            'name' => 'bail|required|max:255',
            'phone' => 'bail|required|digits:10|unique:users',
            'email' => 'bail|required|email|max:255|unique:users',
            'password' => 'bail|required|min:8|max:100|regex:/^[a-zA-Z0-9!$#%]+$/'
        ];
        $validator = Validator::make($credentials, $rules);
      
        if($validator->fails()) {
            return response()->json(['success'=> false, 'error'=> $validator->messages()->first()]);
        }
        $record = SmsRecord::where([ 'to'=>request('phone')])->orderBy('created_at', 'desc')->first();
        
        if($record){
           if (time() - strtotime($record['created_at']) >= 60 * 30){
             return response()->json(['success'=> false, 'error'=> '验证码已过期']);
           } else if ($record['code'] != request('code')){
             return response()->json(['success'=> false, 'error'=> "验证码不正确"]);
           }
        } else {
          return response()->json(['success'=> false, 'error'=> "验证码不正确"]);
        }
        $name = $request->name;
        $phone = $request->phone;
        $email = $request->email;
        $password = $request->password;
        
        $user = User::create(['name' => $name, 'email' => $email, 'phone' => $phone, 'password' => Hash::make($password)]);
        
      
        if ($user){
          if(Auth::attempt(['phone' => $phone, 'password' => $password]))
          {
              $user = Auth::user();
              $token = $user->createToken('Pizza App')->accessToken;
          }
        }
        return response()->json(['success'=> true, 'message'=> 'Thanks for signing up!', 'token'=>$token]);
    }
  
  
    public function details()
    {
      return response()->json(['user' => Auth::user()]);
    }
  
    public function sendCode(Request $request)
    {
        $credentials = $request->only('phone');
        $rules = [
            'phone' => 'bail|required|digits:10|unique:users',
        ];
        $validator = Validator::make($credentials, $rules);
      
        if($validator->fails()) {
            return response()->json(['success'=> false, 'error'=> $validator->messages()->first()]);
        }
      
       $verification_code = rand ( 1000 , 9999 );
      
      try {
            $test = Twilio::message(request('phone'), '傻逼鸽子王，验证码是'. $verification_code.'有效期30分钟');
      }
      catch (\Exception $e) {
            return response()->json(['success' => false,'error' => "发送失败"]);
      }

       $result = json_decode($test, true);
       SmsRecord::create(['status'=>$result['status'],'from'=>$result['from'],'to'=>request('phone'),'body'=>$result['body'], 'code'=>$verification_code]);
       return response()->json(['success'=> true, 'status' => $result['status']]);
    }
  
    public function test()
    {
       $verification_code = rand ( 1000 , 9999 );
       return test();
    }
  
  
    public function forgetPassword()
    {
        $user = User::where('email', '=', request('email'))->where(['phone'=>request('phone')])->first();
        if (!$user) {
           return response()->json(['success'=>false, 'message' => '此用户不存在']);
        }
      
        $temp_password = str_random(8);
        $user->password = Hash::make($temp_password);
        $user->save();
        try {
            $test = Twilio::message(request('phone'), '新的密码为“'.$temp_password."”，请及时登录修改。");
        }
        catch (\Exception $e) {
              return response()->json(['success' => false,'error' => "发送失败"]);
        }
        return response()->json(['success'=>true,'message' => '临时密码已经发送至您的短信，请及时登录修改密码。']);
    }
  
  
    public function changePassword(Request $request)
    {
        $request_old_passowrd= request('old_password');

        if (!Hash::check($request_old_passowrd, Auth::user()->password)){
          return response()->json(['success'=> false,'message'=>'旧密码错误！请重新输入']);
        } 
        
      
        $credentials = $request->only('password');
      
        $rules = [
            'password' => 'bail|required|min:8|max:100|regex:/^[a-zA-Z0-9!$#%]+$/'
        ];
        $validator = Validator::make($credentials, $rules);
      
        if($validator->fails()) {
            return response()->json(['success'=> false, 'error'=> $validator->messages()->first()]);
        }
        $user = User::where('id', '=', Auth::user()->id)->first();
        $user->password = Hash::make(request('password'));
        if ($user->save()){
          return response()->json(['success'=> true, 'message'=> '修改密码成功']);
        } else{
          return response()->json(['success'=> false, 'message'=> '修改密码失败，请稍后再试']);
        }
      
      
    }
}