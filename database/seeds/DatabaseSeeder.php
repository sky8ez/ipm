<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //  $this->call(UsersTableSeeder::class);

        DB::table('mt_user_access')->insert([
        'id' => '1',
        'name' => 'System',
        'role' => 'admin',
        ]);

      DB::table('users')->insert([
        'name' => 'tryanto',
        'email' => '4cun12@gmail.com',
        'password' => bcrypt('12345678'),
        'position' => 'User',
        'user_access_id' => '1',
        'address' => '',
        'phone' => '',
        'image_link' => '',
        'background_color' => '',
      ]);
    }
}
