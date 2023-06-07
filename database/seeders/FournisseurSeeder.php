<?php

namespace Database\Seeders;

use App\Models\Fournisseur;
use Faker\Factory as Faker;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FournisseurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {/*
        $faker = Faker::create();
        $users = [];
        for ($i = 1; $i <= 10; $i++) {
            $company = $faker->company;
            $users[] = [
                'fournisseur' => $company,
                'code_fournisseur' => strtoupper(substr(preg_replace('/\s+/', '', $company), 0, 2)),
                'Adresse' => $faker->address,
                'email' => $faker->email,
                'Telephone' => $faker->phoneNumber,
                'ICE' => $faker->unique()->numberBetween(10000000, 99999999),
                'RC' => $faker->unique()->numerify('RC ######'),
                'IF' => $faker->unique()->numberBetween(10000000, 99999999),

                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ];
        }


        DB::table('fournisseurs')->insert($users); */

        $data = [
            [
                'code_fournisseur' => '001',
                'fournisseur' => 'Valencia',
                'ICE' => '123456789',
                'IF' => '987654321',
                'RC' => 'RC-123',
                'Adresse' => '123 Main Street, City',
                'email' => 'supplierA@example.com',
                'Telephone' => '123-456-7890'
            ],
            [
                'code_fournisseur' => '002',
                'fournisseur' => 'Ain Ifrane',
                'ICE' => '987654321',
                'IF' => '123456789',
                'RC' => 'RC-456',
                'Adresse' => '456 Elm Street, City',
                'email' => 'supplierB@example.com',
                'Telephone' => '987-654-3210'
            ],
            [
                'code_fournisseur' => '003',
                'fournisseur' => 'Milka',
                'ICE' => '567890123',
                'IF' => '543210987',
                'RC' => 'RC-789',
                'Adresse' => '789 Oak Street, City',
                'email' => 'supplierC@example.com',
                'Telephone' => '567-890-1234'
            ]
        ];

        foreach ($data as $supplierData) {
            Fournisseur::create($supplierData);
        }
    }

}
