<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\WithdrawInfo\WithdrawService;

class TransactionController extends Controller
{   
    protected $withdraw_info;
    public function __construct(WithdrawService $withdraw_info)
    {
        $this->withdraw_info = $withdraw_info;
    }

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
                'user_id' => $user_id,
                'amount' => $amount,
                'transaction_type' => 'deposit',
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


    public function withdrawalTransactionShow(){
        try {
            $data  = Transaction::with(['user' => function($q){
                        $q->select('id','name');
                    }])
                    ->select('id','user_id','amount','transaction_type','fee','date')
                    ->where('transaction_type', 'withdraw')
                    ->orderBy('user_id','asc')
                    ->orderBy('date','asc')
                    ->get()->toArray();    
            return view('admin.transaction.withdraw_transaction_info',compact('data'));
        } catch (\Throwable $th) {
            return view('admin.error');
        }
    }


    public function withdrawalTransaction(Request $request){
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
            
            $transaction_type = 'withdraw';
            $response = false;
            $check_balance = $this->withdraw_info->getAvailableBalance($user_id);
   
            if($check_balance < $amount){
                return redirect()->back()->with('failed','Insufficient Fund.'); 
            }else{
                $response = $this->withdraw_info->withdrawBalance($user_id,$transaction_type,$amount);
            }

            if($response == true){
                DB::commit();
                return redirect()->back()->with('success','Successfullty Deposited and Balance Updated');
            }else{
                DB::rollBack();
                return redirect()->back()->with('failed','Something went wrong. please try again later');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('failed','Something went wrong. please try again later');
        }
    }
}
