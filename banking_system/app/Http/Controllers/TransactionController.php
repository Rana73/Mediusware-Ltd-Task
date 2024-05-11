<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function show(){
        try {
            $data  = Transaction::with(['user' => function($q){
                        $q->select('id','name','balance');
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


    public function depositTransactionShow(){
        try {
            $data  = Transaction::with(['user' => function($q){
                        $q->select('id','name','balance');
                    }])
                    ->select('id','user_id','amount','transaction_type','fee','date')
                    ->where('transaction_type', 'deposit')
                    ->orderBy('user_id','asc')
                    ->orderBy('date','asc')
                    ->get()->toArray();    
            return view('admin.transaction.deposit_transaction_info',compact('data'));
        } catch (\Throwable $th) {
            return view('admin.error');
        }
    }

    public function depositTransaction(Request $request){
        $request->validate([
            'amount' => 'required|numeric|max:8|min|1',
        ]);

        try {

            DB::beginTransaction();
            $amount = $request->input("amount");
            $data = [
                'amount' => $amount,
                'transaction_type' => 'deposit',
                'user_id' => Auth::user()->id,
                'fee'     => 0,
                'date' => date("Y-m-d")
            ];
            Transaction::insert($data);
            DB::commit();
            return redirect()->back()->with('success','Successfullty Deposited and Balance Updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('failed','Something went wrong. please try again later');
        }
    }
}
