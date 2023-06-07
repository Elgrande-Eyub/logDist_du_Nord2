<?php

namespace Database\Seeders;

use App\Models\articleCategory;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $categories = [
            ['category' => 'Tous'],
            ['category' => 'Jus valencia'],
            ['category' => 'Ain Ifrane'],
            ['category' => 'Valencia'],
            ['category' => 'Mondial'],
            ['category' => 'Services et Soldes'],
            ['category' => 'Cadeaux'],
            ['category' => 'Rita'],
            ['category' => 'Laiko'],
            ['category' => 'Amiris'],
            ['category' => 'Hell Energy Drink'],
            ['category' => 'Gacidis'],
            ['category' => 'Zakia'],
        ];


        foreach ($categories as $category) {
            articleCategory::create($category);
        }
    }
}
