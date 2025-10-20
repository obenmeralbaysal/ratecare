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
        DB::table('users')->insert([
            'namesurname' => 'Abc Abc',
            'email' => 'abc@abc.com',
            'password' => bcrypt('12341234'),
            'is_admin' => 1,
        ]);

        DB::table('users')->insert([
            'namesurname' => 'Umut Germeyan',
            'email' => 'umut.germeyan@onlinehotelbusiness.com',
            'password' => bcrypt('I9210059'),
            'is_admin' => 1,
        ]);
    }
}
