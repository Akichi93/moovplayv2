<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $usersRecords = [
            ['id'=>1,'name'=>'Admin','role'=>'PROPRIETAIRE','prenom'=>'','email'=>'jean@gmail.com','email_verified_at'=> NULL,'password'=>'$2y$10$Y2niMCQWuigFfFn76gbWx.34lxgasyrNb27sB6yeTYvXVejuRYXN6','remember_token'=>NULL,'created_at'=>NULL,'updated_at'=>NULL],
        ];

        Admin::insert($usersRecords);
    }
}
