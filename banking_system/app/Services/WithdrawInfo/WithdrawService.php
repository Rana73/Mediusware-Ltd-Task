<?php

namespace App\Services\WithdrawInfo;

interface WithdrawService
{
    public function getAvailableBalance($user_id);
    public function withdrawBalance($user_id,$transaction_type,$amount);
}
?>