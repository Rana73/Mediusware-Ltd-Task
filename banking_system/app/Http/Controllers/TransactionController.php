<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            $user_id = Auth::user()->id;

            /*lock this account info*/
            $lockedUser = user::where('id',$user_id)->lockForUpdate()->first();
            $lockedTransaction = Transaction::where('user_id',$user_id)->lockForUpdate()->get();
            /*close lock this account info*/
            
            $data = [
                'amount' => $amount,
                'transaction_type' => 'deposit',
                'user_id' => $user_id,
                'fee'     => 0,
                'date' => date("Y-m-d")
            ];
            Transaction::insert($data);

            $deposit_balance = Transaction::where('user_id',$user_id)
                                ->where('transaction_type','deposit')
                                ->selectRaw("(sum(IFNULL(amount,0)) - sum(IFNULL(fee,0))) as amount")->first();
            $withdraw_balance = Transaction::where('user_id',$user_id)
                                ->where('transaction_type','withdraw')
                                ->selectRaw("(sum(IFNULL(amount,0)) + sum(IFNULL(fee,0))) as amount")->first();
            $balance = User::where('id',$user_id)->value('balance');

            $deposit_balance = isset($deposit_balance->amount) ? $deposit_balance->amount : 0 ; 
            $withdraw_balance = isset($withdraw_balance->amount) ? $withdraw_balance->amount : 0 ; 
            $current_balance = (floatVal($deposit_balance) + floatVal($balance)) - (floatVal($withdraw_balance));

            User::where('id',$user_id)->update(['balance' => $current_balance]);
            DB::commit();
            return redirect()->back()->with('success','Successfullty Deposited and Balance Updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('failed','Something went wrong. please try again later');
        }
    }
}
