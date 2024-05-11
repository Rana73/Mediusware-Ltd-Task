<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'id',
        'user_id',
        'amount',
        'transaction_type',
        'date',
        'fee'
    ];
    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
