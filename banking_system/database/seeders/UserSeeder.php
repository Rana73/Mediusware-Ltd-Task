<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //User Demo Data
       $jsonString = File::get(storage_path('data/users.json'));

       $data = json_decode($jsonString, true);
       foreach ($data as $item) {       
           $item['password'] = Hash::make($item['password']);       
           User::insert($item);
       } 
    }
}
