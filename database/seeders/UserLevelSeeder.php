<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserLevel;

class UserLevelSeeder extends Seeder
{
    public function run(): void
    {
        UserLevel::insert([
            ['name'=>'Admin'],
            ['name'=>'Front Desk'],
            ['name'=>'Designated User'],
            ['name'=>'Supervisor']
        ]);
    }
}