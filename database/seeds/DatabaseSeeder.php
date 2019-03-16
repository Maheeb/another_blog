<?php

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
//        Eloquent::unguard();
//        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $this->call(UsersTableSeeder::class);
        $this->call(PostsTableSeeder::class);
        $this->call(CategoriesTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(TagsTableSeeder::class);
        $this->call(CommentsTableSeeder::class);

//        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
