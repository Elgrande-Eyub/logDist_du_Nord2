<?php

namespace Database\Seeders;

use App\Models\Camion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CamionSeeder extends Seeder
{

    public function run()
    {
        $vehicles = [
            [
                "matricule" => "ABC123",
                "marque" => "Ford",
                "modele" => "F-150",
                "annee" => 2020,
                "etat" => "En bon état",
                "km" => 50000
            ],
            [
                "matricule" => "DEF456",
                "marque" => "Toyota",
                "modele" => "Camry",
                "annee" => 2018,
                "etat" => "Usagé",
                "km" => 80000
            ]
        ];

        foreach ($vehicles as $vehicle) {
            Camion::create($vehicle);
        }
    }
}
