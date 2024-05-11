<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Transaction Demo Data
       $jsonString = File::get(storage_path('data/transactions.json'));

       $data = json_decode($jsonString, true);
       foreach ($data as $item) {
           $identify = []; 
           $identify['user_id'] = $item['user_id'];         
           $identify['date'] = $item['date'];         
           $identify['amount'] = $item['amount'];         
           Transaction::insert($item);
       } 
    }
}
