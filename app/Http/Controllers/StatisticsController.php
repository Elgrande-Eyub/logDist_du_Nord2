<?php

namespace App\Http\Controllers;

use App\Models\bonCommande;
use App\Models\bonCommandeVente;
use App\Models\client;
use App\Models\facture;
use App\Models\factureVente;
use App\Models\Fournisseur;
use App\Models\Transaction;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{

    public function getFournisseurCount()
    {
    $fournisseurCount = Fournisseur::count();
    return response()->json($fournisseurCount);
    }

    public function getClientCount()
    {
    $clientCount = client::count();
    return response()->json($clientCount);
    }

    public function getCommandeCount()
    {
    $bonCommandeVenteCount = bonCommandeVente::count() + bonCommande::count();
    return response()->json($bonCommandeVenteCount);
    }

    public function getAchatApayer()
    {
    $AchatApayer = facture::where('Confirme',true)->sum('Total_Rester');
    return response()->json($AchatApayer);
    }

    public function getVenteTotal()
    {
    $VenteTotal = factureVente::where('Confirme',true)->sum('Total_TTC');
    return response()->json($VenteTotal);
    }

    public function getRevenue()
    {
    $factureVenteRevenue = factureVente::sum('Total_TTC') - facture::sum('Total_TTC');
    return response()->json($factureVenteRevenue);
    }

    public function getChequeportfeuille()
    {
    $ChequeRetard = Transaction::where('etat_cheque','portfeuille')->count();
    return response()->json($ChequeRetard);
    }

}
