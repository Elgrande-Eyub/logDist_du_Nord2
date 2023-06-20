<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\bonCommande;
use App\Models\Fournisseur;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FournisseurController extends Controller
{
    // This function returns all categories
    public function index()
    {
        try {
            $fournisseurs = Fournisseur::all();
            return response()->json($fournisseurs);
        } catch(Exception $e) {

            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 404);
        }

    }

    // // This function returns all Fournisseurs with the given Fournisseur ID
    // public function getFournisseurByid($id)
    // {
    //     $FournisseurSelected = Fournisseur::where('Fournisseur_id', $id)->get();
    //     return response()->json($FournisseurSelected);
    // }


    // This function is not used
    public function create()
    {
    }

    // This function creates a new Fournisseur
    public function store(Request $request)
    {
        try {
            // Check if the Fournisseur is empty
            if (!$request->filled(['code_fournisseur','fournisseur'])) {
                return response()->json([
                    'message' => 'Please fill all fields required'
                ], 400);
            }

            // Check if the Fournisseur already exists
            $found = Fournisseur::where('fournisseur', $request->fournisseur)->exists();
            if ($found) {
                return response()->json([
                    'message' => 'Fournisseur cannot be duplicated'
                ], 400);
            }

            // Create the new Fournisseur
            $Added = Fournisseur::create([
                'code_fournisseur' => $request->code_fournisseur,
                'fournisseur' => $request->fournisseur,
                'ICE' => $request->ICE,
                'IF' => $request->IF,
                'RC' => $request->RC,
                'Adresse' => $request->Adresse,
                'email' => $request->email,
                'Telephone' => $request->Telephone,

            ]);

            // Check if the Fournisseur was successfully created
            if (!$Added) {
                Log::error('Failed to create Fournisseur');
                return response()->json([
                    'message' => 'Something went wrong. Please try again later.'
                ], 400);
            }

            // Return a success message and the new Fournisseur ID
            return response()->json([
                    'message' => 'Fournisseur created successfully',
                    'id' => $Added->id
                ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 404);
        }
    }

   // This function returns a single Fournisseur by ID
    public function show($id)
    {
        try {
            // Find the Fournisseur with the given ID
            $FoundedFournisseur = Fournisseur::withTrashed()->find($id);

            // Check if the Fournisseur was found
            if (!$FoundedFournisseur) {
                return response()->json([
                    'message' => 'Fournisseur not found'
                ], 404);
            }

            $FoundedFournisseurToArray =  $FoundedFournisseur->toArray();


            $Commandes = bonCommande::leftjoin('bon_livraisons', 'bon_commandes.id', '=', 'bon_livraisons.bonCommande_id')
            ->leftjoin('factures', 'bon_livraisons.id', '=', 'factures.bonLivraison_id')
            ->select(
                'bon_commandes.id as bonCommande_id',
                'bon_commandes.Numero_bonCommande',
                'bon_livraisons.id as bonLivraison_id',
                'bon_livraisons.Numero_bonLivraison',
                'factures.id as facture_id',
                'factures.numero_Facture'
            )
            ->orderByDesc('bon_commandes.created_at')
            ->limit(10)
            ->get();

            $FoundedFournisseurToArray['Commandes'] = $Commandes;
            $Transactions= [];

            foreach($Commandes as $facture) {
                $Transactions = Transaction::where('factureAchat_id', $facture->facture_id)
                ->select(
                    'transactions.num_transaction',
                    'transactions.montant',
                    'transactions.modePaiement'
                )
                ->limit(10)
                ->get();
            }

            $FoundedFournisseurToArray['Transactions'] = $Transactions->reverse();
            // Return the Fournisseur data
            return response()->json([
                'Fournisseur Requested' => $FoundedFournisseurToArray

            ], 200);

        } catch(Exception $e) {

            return response()->json([
                'message' => 'Something went wrong. Please try again later.'
            ], 404);
        }
    }

      // Update an Fournisseur Fournisseur
      public function update(Request $request, $id)
      {
          try {
              // Check if the Fournisseur input is not empty
              if (!$request->filled(['code_fournisseur','fournisseur'])) {
                  return response()->json([
                      'message' => 'Please fill all fields required'
                  ], 400);
              }

              // Find the Fournisseur Fournisseur with the given ID
              $FournisseurFounded = Fournisseur::find($id);

              // If the Fournisseur doesn't exist, return an error
              if (!$FournisseurFounded) {
                  return response()->json([
                      'message' => 'Fournisseur not found'
                  ], 404);
              }

              // Update the Fournisseur name and save the changes

              $FournisseurFounded->code_fournisseur = $request->code_fournisseur;
              $FournisseurFounded->fournisseur = $request->fournisseur;
              $FournisseurFounded->ICE = $request->ICE;
              $FournisseurFounded->IF = $request->IF;
              $FournisseurFounded->RC = $request->RC;
              $FournisseurFounded->Adresse = $request->Adresse;
              $FournisseurFounded->email = $request->email;
              $FournisseurFounded->Telephone = $request->Telephone;

              $FournisseurFounded->save();

              // Return a success message with the updated Fournisseur
              return response()->json([
                  'message' => 'Fournisseur updated successfully',
                  'id'=>$FournisseurFounded->id
              ]);
          } catch (Exception $e) {
              DB::rollBack();
              // Return an error message for any other exceptions
              return response()->json([
                  'message' => 'Something went wrong. Please try again later.'
              ], 404);
          }
      }

      // Delete an Fournisseur Fournisseur
      public function destroy($id)
      {
          try {
              // Find the Fournisseur Fournisseur with the given ID
              $FournisseurFounded = Fournisseur::find($id);

              // If the Fournisseur doesn't exist, return an error
              if (!$FournisseurFounded) {
                  return response()->json([
                      'message' => 'Fournisseur not found'
                  ], 404);
              }
              $Articles = Article::where('fournisseur_id', $FournisseurFounded->id)->get();

              // Delete each individual article
              foreach ($Articles as $article) {
                  $article->delete();
              }

              // Delete the Fournisseur
              $FournisseurFounded->delete();
              // Return a success message with the deleted Fournisseur
              return response()->json([
                  'message' => 'Fournisseur deleted successfully',
                  'id' => $FournisseurFounded->id
              ]);
          } catch (Exception $e) {
              DB::rollBack();
              // Return an error message for any other exceptions
              return response()->json([
                  'message' => 'Something went wrong. Please try again later.'
              ], 404);
          }
      }
}
