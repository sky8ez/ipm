<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('mt_user_access')->insert([
        'id' => '1',
        'access_name' => 'System',
        'role' => 'admin',
        ]);

      DB::table('users')->insert([
        'name' => 'tryanto',
        'email' => '4cun12@gmail.com',
        'password' => bcrypt('12345678'),
        'position' => 'Supervisor',
        'user_access_id' => '1',
        'branch_id' => '1',
        'division_id' => '1',
        'address' => '',
        'phone' => '',
        'image_link' => '',
        'background_color' => '',
      ]);

    }
}
