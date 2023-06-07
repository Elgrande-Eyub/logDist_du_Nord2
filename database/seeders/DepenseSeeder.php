<?php

namespace Database\Seeders;

use App\Models\depense;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $depenses = [
            ['depense'=>'Gasoil','depense_Tax'=>10],
            ['depense'=>'Eaux/Electricite','depense_Tax'=>00],
            ['depense'=>'Administration','depense_Tax'=>20],
            ['depense'=>'Frais bancaires','depense_Tax'=>00],
            ['depense'=>'Salaires','depense_Tax'=>00],
            ['depense'=>'Transport','depense_Tax'=>00],
            ['depense'=>'Loyer','depense_Tax'=>00],
            ['depense'=>'Reparation Vehicule','depense_Tax'=>00],
            ['depense'=>'CNSS','depense_Tax'=>00],
            ['depense'=>'La Perception','depense_Tax'=>00],
            ['depense'=>'Gasoil Cuelma','depense_Tax'=>10],
            ['depense'=>'Parking','depense_Tax'=>00],
            ['depense'=>'Gasoil Exterieur','depense_Tax'=>00],
            ['depense'=>'Reception Infraction','depense_Tax'=>00],
            ['depense'=>'Frais Autoroute','depense_Tax'=>00],
            ['depense'=>'Lavage','depense_Tax'=>00],
            ['depense'=>'Hotel Laarach et Loyer','depense_Tax'=>20],
            ['depense'=>'Gasoil Laarach','depense_Tax'=>07],
            ['depense'=>'Reparation Laarache','depense_Tax'=>20],
            ['depense'=>'Fourniture','depense_Tax'=>00],

        ];


        foreach ($depenses as $depense) {
            depense::create($depense);
        }
    }
}
