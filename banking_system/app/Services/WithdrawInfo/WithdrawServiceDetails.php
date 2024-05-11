<?php
namespace App\Services\WithdrawInfo;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Transaction;
use App\Services\WithdrawInfo\WithdrawService;

class WithdrawServiceDetails implements WithdrawService
{
    public function getAmountByPercent($percent, $total_amount){
        $amount = (floatVal($percent) * floatval($total_amount)) / 100;
        $value = number_format($amount,2);
        return str_replace(",", "", $value);
    }

    public function getInchargeAmount($user_id,$amount){
        $todayDate = date("Y-m-d");
        $todayDate = strtotime($todayDate);
        $dayName = date("l", $todayDate);
        $account_type = User::where('id',$user_id)->value('account_type');


        if($account_type == 'individual'){
            $charge_percent = 0.015;
            $charge_amount = $this->getAmountByPercent($charge_percent,$amount);

            if($dayName == 'Friday'){   
                $charge_amount = 0;
            }else{
                if($amount > 1000){
                    $charge_percent = 0.015;
                    $without_one_thousand_amount = floatVal($amount) - 1000;
                    $charge_amount = $this->getAmountByPercent($charge_percent,$without_one_thousand_amount);
                }else{
                    $charge_amount = 0;  
                }
            }

            /*Each Month Withdrawal check */
            $year_month = date("Y-m");

            $current_month_withdraw_amount = Transaction::whereRaw("DATE_FORMAT(date, '%Y-%m') = '$year_month'")
                                                ->where('transaction_type','withdraw')
                                                ->where('user_id',$user_id)
                                                ->sum('amount');
            if($current_month_withdraw_amount < 5000){
                $charge_percent = 0.015;
                $due_amount = 5000 - floatVal($current_month_withdraw_amount);
                $due_amount = floatVal($amount) - $due_amount;
                $charge_amount = $this->getAmountByPercent($charge_percent,$due_amount);
            }


        }else{
            $charge_percent = 0.025;
            $charge_amount = $this->getAmountByPercent($charge_percent,$amount);
        }
        return $charge_amount;
    }

    public function getAvailableBalance($user_id){

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
        return $current_balance;
    }


    public function withdrawBalance($user_id,$transaction_type,$amount){
        try {

            $fee =  $this->getInchargeAmount($user_id,$amount);
            $data = [
                'user_id' => $user_id,
                'amount' => $amount,
                'fee' => $fee,
                'transaction_type' => $transaction_type,
            ];
            Transaction::create($data);
            $current_balance = $this->getAvailableBalance($user_id);
            User::where('id',$user_id)->update(['balance' => $current_balance]);
            return true;
        } catch (\Throwable $th) {
            return false;
        }        
    }
}

?>