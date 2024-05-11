<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function show(){
        $data  = Transaction::with(['user' => function($q){
                        $q->select('id','name');
                    }])
                    ->select('id','user_id','amount','transaction_type','fee','date')
                    ->oderBy('user_id','asc')
                    ->oderBy('date','asc')
                    ->get();
        dd($data);       
        return view('admin.transaction.transaction_info',compact('data'));
    }
}
