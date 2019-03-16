<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        $faker = \Faker\Factory::create();
        DB::table('users')->insert([
            [  'name'=> "John Doe",
               'slug'=> "John-Doe",
              'email' => "johndoe@test.com",
              'password'=> bcrypt('maheeb'),
                'bio' => $faker->text(rand(250, 300))

            ],
            [   'name'=> "Jane Doe",
                'slug'=> "Jane-Doe",
                'email' => "janedoe@test.com",
                'password'=> bcrypt('maheeb'),
                'bio' => $faker->text(rand(250, 300))

            ],


            [
                'name'=> "Edo Doe",
                'slug'=> "Edo-Doe",
                'email' => "Edodoe@test.com",
                'password'=> bcrypt('maheeb'),
                'bio' => $faker->text(rand(250, 300))

            ],

            ]);





    }
}
