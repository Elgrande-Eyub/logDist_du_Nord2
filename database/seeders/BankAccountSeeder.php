<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{

    public function run()
    {


        $banks = [
            [
                "nomBank" => "BMCE",
                "adresse" => "Tetouan  rue 34 agence 56",
                "telephone" => "07.99.90.07.77",
                "numero_compt" => "4559 3994 3849 3247",
                "rib_compt" => "2345 2345938532499843 3495",
                "Solde" => 139998475.00,
                "Commentaire" => "Commentaire",

            ]
        ];

        foreach ($banks as $bank) {
            BankAccount::create($bank);
        }



    }
}
