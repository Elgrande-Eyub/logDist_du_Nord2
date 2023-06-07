<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\articleCategory;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /* $articls = [
            ['article_libelle' => 'Jus Valencia Juper 1L',
            'reference' => '200',
            'prix_unitaire' => '8',
            'prix_public' => '10',
            'client_Fedele' => '9',
            'demi_grossiste' => '9.5',
            'unite' => 'Pack of 12',
            'alert_stock' => '30',
            'category_id' => 1,
            'fournisseur_id' => 1,
            'created_at' => Carbon::now()->format('Y-m-d H:i:s')],
        ]; */

        $articles = [
            [
                "article_libelle" => "Jus Valencia Juper 1L",
                "reference" => "200",
                "prix_unitaire" => "8",
                "prix_public" => "10",
                "client_Fedele" => "9",
                "demi_grossiste" => "9.5",
                "unite" => "P12",
                "alert_stock" => "30",
                "category_id" => 1,
                "fournisseur_id" => 1,
                "prix_achat" => "7.5"
            ],
            [
                "article_libelle" => "Jus Valencia Juper 0.5L",
                "reference" => "201",
                "prix_unitaire" => "4",
                "prix_public" => "6",
                "client_Fedele" => "7",
                "demi_grossiste" => "7.5",
                "unite" => "P6",
                "alert_stock" => "20",
                "category_id" => 1,
                "fournisseur_id" => 1,
                "prix_achat" => "3.5"
            ],
            [
                "article_libelle" => "Ain Ifran Water 1L",
                "reference" => "300",
                "prix_unitaire" => "2",
                "prix_public" => "3",
                "client_Fedele" => "2",
                "demi_grossiste" => "2.5",
                "unite" => "P12",
                "alert_stock" => "50",
                "category_id" => 2,
                "fournisseur_id" => 2,
                "prix_achat" => "1.5"
            ],
            [
                "article_libelle" => "Ain Ifran Water 0.5L",
                "reference" => "301",
                "prix_unitaire" => "1",
                "prix_public" => "2",
                "client_Fedele" => "1",
                "demi_grossiste" => "1.5",
                "unite" => "P6",
                "alert_stock" => "40",
                "category_id" => 2,
                "fournisseur_id" => 2,
                "prix_achat" => "0.75"
            ]
        ];

        foreach ($articles as $article) {
            Article::create($article);
        }
    }

}
