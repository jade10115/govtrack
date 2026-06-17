<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequestType;

class RequestTypeSeeder extends Seeder
{
    public function run(): void
    {
        RequestType::insert([
            [
                'request_name'=>'Livelihood Assistance'
            ],
            [
                'request_name'=>'Employment Assistance'
            ],
            [
                'request_name'=>'Training Assistance'
            ],
            [
                'request_name'=>'Legal Assistance'
            ]
        ]);
    }
}