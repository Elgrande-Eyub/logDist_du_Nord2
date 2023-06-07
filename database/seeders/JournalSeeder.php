<?php

namespace Database\Seeders;

use App\Models\Journal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $articls = [
        ['Code_journal'=>'Vente','type'=>'Vente'],
        ['Code_journal'=>'Achat','type'=>'Achat'],
        ['Code_journal'=>'Banque AJW','type'=>'Banque'],
        ['Code_journal'=>'Espece Tetouan','type'=>'Espece'],
        ['Code_journal'=>'Divers','type'=>'Divers'],
        ['Code_journal'=>'compte associe Reda','type'=>'Espece'],
        ['Code_journal'=>'compte associe Bilal','type'=>'Espece'],
        ['Code_journal'=>'Solde','type'=>'Espece'],
        ['Code_journal'=>'Caisse Hoceima','type'=>'Espece'],
        ['Code_journal'=>'Sakina','type'=>'Espece'],
        ['Code_journal'=>'Banque B.P','type'=>'Banque'],
        ['Code_journal'=>'Caisse Laarach','type'=>'Espece'],
     ];

        foreach ($articls as $supplierData) {
            Journal::create($supplierData);
        }
    }
}
