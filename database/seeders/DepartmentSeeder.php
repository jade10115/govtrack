<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::insert([
            [
                'department_name'=>'Livelihood'
            ],
            [
                'department_name'=>'Employment'
            ],
            [
                'department_name'=>'Training'
            ],
            [
                'department_name'=>'Legal'
            ]
        ]);
    }
}