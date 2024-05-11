<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function show(){
        try {
            $data  = Transaction::with(['user' => function($q){
                        $q->select('id','name');
                    }])
                    ->select('id','user_id','amount','transaction_type','fee','date')
                    ->orderBy('user_id','asc')
                    ->orderBy('date','asc')
                    ->get()->toArray();    
            return view('admin.transaction.transaction_info',compact('data'));
        } catch (\Throwable $th) {
            return view('admin.error');
        }
        
        
    }
}
