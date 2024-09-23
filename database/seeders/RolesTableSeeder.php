<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'CM']);
        Role::create(['name' => 'Coach']);
        Role::create(['name' => 'Apprenant']);
    }
}
