<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run()
    {
        Company::create([
            'name' => 'Iker.MA',
            'ICE' => 001750634000012,
            'IF' => 'IF123456',
            'RC' => '21001 (Tribunal de Tetouan)',
            'adresse' => '21 Avenue Mokaouama 4eme Etage N°21b - Tetouan (M)',
            'email' => 'info@ikercompany.com',
            'telephone' => '06.60.17.07.33',
            'fax' => '05.39.71.35.36'
        ]);
    }
}
