<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BankAccount;
use App\Models\client;
use App\Models\Company;
use App\Models\Devis;
use App\Models\DevisArticle;
use App\Models\Fournisseur;
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
                ->leftjoin('clients', 'devis.client_id', '=', 'clients.id')
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
                    'message' => 'Devis est déjà existe'
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
                'Total_HT' => $request->Total_HT,
                'Total_TVA' => $request->Total_TVA,
                'Total_TTC' => $request->Total_TTC,
                'TVA' => $request->TVA,
                'remise' => $request->remise,
            ]);

            foreach ($request->Articles as $article) {
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
                'message' => 'Création réussie de Devis',
                'id' => $added->id,
            ]);

         } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $devis = Devis::find($id);
            if(!$devis) {
                return response()->json([
                    'message' => 'Devis introuvable'
                ], 404);
            }

            $detailsCommande = DevisArticle::where('devis_id', $devis->id)->withTrashed()->get();

            $articles = [];

            foreach($detailsCommande as $detail) {

                $articl = Article::withTrashed()->find($detail->article_id);


                $article = [
                    'article_id' => $detail->article_id,
                    'reference' => $articl->reference,
                    'article_libelle' => $articl->article_libelle,
                    'Quantity' => $detail->Quantity,
                    'Prix_unitaire' => $detail->Prix_unitaire,
                    'Total_HT' => $detail->Total_HT,

                ];
                $articles[] = $article;
            }

            $devis = devis::
            leftjoin('clients', 'devis.client_id', '=', 'clients.id')->withTrashed()
            ->select('devis.*', 'clients.*')
            ->where('devis.id', $id)
            ->first();

            $devisArray = $devis->toArray();
            $devisArray['Articles'] = $articles;

            return response()->json($devisArray);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Devis introuvable'
            ], 404);
        }
    }

    public function markAsConfirmed($id)
    {
        DB::beginTransaction();

        try {
            $devis = Devis::findOrFail($id);

            if ($devis->Confirme) {
                return response()->json([
                    'message' => 'Devis est déjà Confirmé'
                ], 400);
            }

            $devis->Confirme = true;
            $devis->Etat = 'Recu';
            $devis->save();

            DB::commit();

            return response()->json(['message' => 'Devis se confirmè avec succès']);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $devis = Devis::findOrFail($id);

            if ($devis->Confirme) {
                return response()->json([
                    'message' => 'Devis est Confirmé, ne peut pas être supprimé'
                ], 400);
            }

            DevisArticle::where('devis_id',$id)->forceDelete();
            $devis->forceDelete();

            DB::commit();

            return response()->json(['message' => 'Devis nest plus disponible']);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 500);
        }
    }

    public function printbonCommande($id, $isDownloaded)
    {
        // try {

            $commande = Devis::withTrashed()->find($id);
            $articles = DevisArticle::withTrashed()->select('devis_articles.*', 'articles.*')
                ->join('articles', 'devis_articles.article_id', '=', 'articles.id')
                ->where('devis_id', $id)
                ->get();

            $client = client::withTrashed()->find($commande->client_id);

            $company = Company::get()->first();
            $bank = BankAccount::get()->first();

            $pdf = app('dompdf.wrapper');

            //############ Permitir ver imagenes si falla ################################
            $contxt = stream_context_create([
              'ssl' => [
                  'verify_peer' => false,
                  'verify_peer_name' => false,
                  'allow_self_signed' => true,
              ]
            ]);
            $pdf->setPaper('A4', 'portrait');
            $pdf->getDomPDF()->setHttpContext($contxt);

            $pdf->loadView('Prints.Devis', compact('commande', 'articles', 'client', 'bank', 'company', 'pdf'));

            if($isDownloaded === 'true') {
                return $pdf->download('Devis_Nº'.$commande->Numero_bonCommande.'.pdf');
            }

            return $pdf->stream('Devis_'.$commande->Numero_bonCommande.'.pdf');

       /*  } catch (Exception $e) {
            abort(404);
        } */

    }
}
