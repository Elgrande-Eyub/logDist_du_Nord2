<?php

namespace App\Http\Controllers;

use App\Models\Devis;
use App\Models\DevisArticle;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DevisController extends Controller
{
    public function index()
    {
        try {
            $devis = Devis::orderByDesc('created_at')
                ->leftJoin('clients', 'devis.client_id', '=', 'clients.id')
                ->select('devis.*', 'clients.nom_Client')
                ->get();

            return response()->json($devis);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement..'
            ], 404);
        }
    }

    public function getNumeroDevis()
    {
        try {
            $year = Carbon::now()->format('Y');

            $lastRecord = Devis::withTrashed()->latest()->first();

            if (!empty($lastRecord)) {
                $lastIncrementStringYear = substr($lastRecord->Numero_Devis, -4, 4);
                if ($lastIncrementStringYear === $year) {
                    $lastIncrementString = substr($lastRecord->Numero_Devis, 2, 5);
                    $incrementNumber = intval($lastIncrementString) + 1;
                } else {
                    $incrementNumber = 1;
                }
            } else {
                $incrementNumber = 1;
            }

            $incrementString = 'D-' . str_pad($incrementNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            return response()->json(['Numero_Devis' => $incrementString]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'client_id' => 'required',
                'Numero_Devis' => 'required',
                'date_Devis' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            $found = Devis::where('Numero_Devis', $request->Numero_Devis)->exists();
            if ($found) {
                return response()->json([
                    'message' => 'Devis Already Exists'
                ], 409);
            }

            $date = Carbon::parse($request->date_Devis);
            $Etat = $request->Confirme ? 'Recu' : 'Saisi';

            $added = Devis::create([
                'Numero_Devis' => $request->Numero_Devis,
                'client_id' => $request->client_id,
                'Exercice' => $date->format('Y'),
                'Mois' => $date->format('n'),
                'Etat' => $Etat, // saiser, en cours, annule, recus
                'Confirme' => $request->Confirme,
                'Commentaire' => $request->Commentaire,
                'date_Devis' => $request->date_Devis,
                'Total_HT' => $request->Total_HT, // Update later
                'Total_TVA' => $request->Total_TVA, // Update later
                'Total_TTC' => $request->Total_TTC, // Update later
                'TVA' => $request->TVA,
                'remise' => $request->remise,
            ]);

            $articles = $request->articles;

            foreach ($articles as $article) {
                $devisArticle = new DevisArticle();
                $devisArticle->devis_id = $added->id;
                $devisArticle->article_id = $article['article_id'];
                $devisArticle->Prix_unitaire = $article['Prix_unitaire'];
                $devisArticle->Quantity = $article['Quantity'];
                $devisArticle->Total_HT = $article['Total_HT'] ;
                $devisArticle->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Devis Created',
                'id' => $added->id,
            ]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 500);
        }
    }
}
