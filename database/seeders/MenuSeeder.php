<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run()
    {
        DB::table('menus')->insert([
            [
                'menu_id' => 1,
                'menu_name' => 'Dashboard',
                'menu_url' => 'dashboard',
                'menu_parent' => 0,
                'menu_type' => 'side-menu',
                'menu_order' => 1,
                'menu_icon' => 'fas fa-fw fa-tachometer-alt',
                'menu_description' => '',
                'status' => 1
            ],
            [
                'menu_id' => 2,
                'menu_name' => 'List Karyawan',
                'menu_url' => 'list-karyawan',
                'menu_parent' => 0,
                'menu_type' => 'side-menu',
                'menu_order' => 2,
                'menu_icon' => 'fas fa-user',
                'menu_description' => '',
                'status' => 1
            ]
        ]);
    }
}