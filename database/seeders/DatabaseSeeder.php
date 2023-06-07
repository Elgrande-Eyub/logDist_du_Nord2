<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\BankAccount;
use App\Models\Camion;
use App\Models\Company;
use App\Models\User;
use App\Models\Vendeur;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ArticleCategorySeeder::class,
            FournisseurSeeder::class,
            ArticleSeeder::class,
            DepenseSeeder::class,
            JournalSeeder::class,
            // Company::class,
            // BankAccount::class
            // Vendeur::class,
            // Camion::class,
        ]);
    }
}
