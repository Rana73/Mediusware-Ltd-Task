<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request){
        $request->validate([
            'email' => 'required|email|max:70',
            'password' => 'required|min:6|max:12'
          ]);
      
          try {
                $credential = $request->only('email', 'password');
                if(Auth::attempt($credential)){
                    return redirect()->route('dashboard');
                }
                else{
                    return redirect()->back()->with('failed', 'Email or Password Incorrect!');
                }    
              } catch (\Throwable $th) {
                return redirect()->back()->with('failed','Something went wrong. please try again later');
              }
    }

    public function dashbboard(){
        if (Auth::check()){
            return view('admin.index');
        }
        else{        
            return view('auth.login');
        }
    }
}
