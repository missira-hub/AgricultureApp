<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Category;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        // Clear the table first
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::table('categories')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        $categories = [
            ['name' => 'Vegetables', 'slug' => 'vegetables'],
            ['name' => 'Fruits', 'slug' => 'fruits'],
            ['name' => 'Dairy', 'slug' => 'dairy'],
            ['name' => 'Meat', 'slug' => 'meat'],
            ['name' => 'Grains', 'slug' => 'grains'],
            ['name' => 'Herbs', 'slug' => 'herbs'],
            ['name' => 'Nuts', 'slug' => 'nuts'],
            ['name' => 'Bakery', 'slug' => 'bakery'],
            ['name' => 'Tubers', 'slug' => 'tubers'],
        ];

        foreach ($categories as $category) {
        Category::create([
            'name' => $category['name'],
            'slug' => $category['slug'],
        ]);
    }
    }
}
