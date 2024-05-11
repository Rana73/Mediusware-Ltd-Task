<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'name' => 'required|min:2|max:250',
            'account_type' => 'required|max:15',
            'password'=>'required|min:6|max:12',
        ]);
        try {
            $name = $request->input("name");
            $account_type = $request->input("account_type");
            $password = $request->input("password");
            $password = Hash::make($password);
            $data = [
                'name' => $name,
                'account_type' => $account_type,
                'password' => $password,
            ];
            User::insert($data);
            return redirect()->back()->with('success','Successfullty created User');
        } catch (\Throwable $th) {
            return redirect()->back()->with('failed','Something went wrong. please try again later');
        }
    }
}
