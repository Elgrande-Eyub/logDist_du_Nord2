<?php

namespace App\Http\Controllers;

use App\Models\bonReceptionVente;
use App\Models\bonReceptionVenteArticle;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BonReceptionVenteController extends Controller
{
    public function index()
    {

    }



    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required',
            'Numero_bonReceptionVente' => 'required',
            'warehouse_id' => 'required',
            // 'date_Blivraison' => 'required',
            'Confirme' => 'required',
            'Total_HT' => 'required',
            'Total_TTC' => 'required',
            'attachement' => 'nullable|mimes:jpeg,pngnjpg,pdf',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 400);
        }

        $found = bonReceptionVente::where('Numero_bonReceptionVente', $request->Numero_bonReceptionVente)->exists();
        if ($found) {
            DB::rollBack();
            return response()->json([
                'message' => 'ce bon de Reception est déjà en place.'
            ], 409);
        }

        $date = Carbon::parse($request->date_BCommandeVente);
        $Etat = $request->Confirme ? 'Recu' : 'Saisi';




        $added = bonReceptionVente::create([
            'client_id' => $request->client_id,
            'Numero_bonReceptionVente' => $request->Numero_bonReceptionVente,
            'Exercice' => $date->format('Y'),
            'Mois' => $date->format('n'),
            'Etat' => $Etat,
            'Confirme' => $request->Confirme,
            'Commentaire' => $request->Commentaire,
            'date_BReceptionVente' => $request->date_BReceptionVente,
            'Total_HT' => $request->Total_HT,
            'remise' => $request->remise,
            'TVA' => $request->TVA,
            'Total_TVA' => $request->Total_TVA,
            'Total_TTC' => $request->Total_TTC,

        ]);

        if (!$added) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 400);
        }

        if ($request->hasFile('attachement')) {
            $image = $request->file('attachement');
            $imageName =  Carbon::now()->timestamp.'.'.$image->getClientOriginalExtension();

            $extension = $image->getClientOriginalExtension();
            $validExtensions = ['pdf', 'jpg', 'jpeg', 'png','PDF', 'JPG', 'JPEG', 'PNG'];

            if (!in_array($extension, $validExtensions)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Veuillez télécharger une pièce jointe valide IMG/PDF'
                ], 404);
            }

            Storage::disk('bonRetourVente')->put($imageName, file_get_contents($image));
            $Added->update([
                'attachement' => $imageName
            ]);
        }


        foreach ($request->Articles as $article) {
            bonReceptionVenteArticle::create([
                'article_id' => $article['article_id'],
                'Prix_unitaire' => $article['Prix_unitaire'],
                'Quantity' => $article['Quantity'],
                'Total_HT' => $article['Total_HT'],
                'brVente_id' => $added->id,
            ]);
        }

        DB::commit();

        return response()->json(['message' => 'Création réussie de bons de Reception.',
                    'id' =>    $added->id

                ]);
    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
        ], 400);
    }
    }


    public function show(bonReceptionVente $bonReceptionVente)
    {

    }


    public function destroy(bonReceptionVente $bonReceptionVente)
    {

    }
}
