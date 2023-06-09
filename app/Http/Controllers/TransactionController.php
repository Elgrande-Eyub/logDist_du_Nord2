<?php

namespace App\Http\Controllers;

use App\Models\avoirsAchat;
use App\Models\BankAccount;
use App\Models\Caisse;
use App\Models\depenseFacture;
use App\Models\facture;
use App\Models\factureVente;
use App\Models\paiementDepense;
use App\Models\Transaction;
use App\Models\venteSecteur;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index()
    {
        try {

            $Transaction = Transaction::all();
            return  response()->json($Transaction);
        } catch(Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
             ], 404);
        }


    }


    public function create()
    {

    }

    public function paymentAchatRest($id)
    {
        try {


            $facture= facture::find($id);

            $chequesAvaiable = Transaction::where('modePaiement', 'cheque')
                ->where('factureAchat_id', $id)
                ->where('etat_cheque', 'portfeuille')
                ->get();

            $totalCheque = $chequesAvaiable->sum('montant');

            return response()->json([
                'rest' => $facture->Total_Rester - $totalCheque
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function paymentVenteRest($id)
    {
        try {
            $facture= factureVente::find($id);

            $chequesAvaiable = Transaction::where('modePaiement', 'cheque')
                ->where('factureVente_id', $id)
                ->where('etat_cheque', 'portfeuille')
                ->get();

            $totalCheque = $chequesAvaiable->sum('montant');

            return response()->json([
                'rest' => $facture->Total_Rester - $totalCheque
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function paymentAvoirsAchatRest($id)
    {
        try {
            $facture= avoirsAchat::find($id);

            $chequesAvaiable = Transaction::where('modePaiement', 'cheque')
                ->where('avoirsAchat_id', $id)
                ->where('etat_cheque', 'portfeuille')
                ->get();

            $totalCheque = $chequesAvaiable->sum('montant');

            return response()->json([
                'rest' => $facture->Total_Rester - $totalCheque
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function paymentDepenseRest($id)
    {
        try {
            $facture= paiementDepense::find($id);

            $chequesAvaiable = Transaction::where('modePaiement', 'cheque')
                ->where('paiementDepense_id', $id)
                ->where('etat_cheque', 'portfeuille')
                ->get();

            $totalCheque = $chequesAvaiable->sum('montant');

            return response()->json([
                'rest' => $facture->Total_Rester - $totalCheque
            ]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }



    public function store(Request $request)
    {

        DB::beginTransaction();

         try {

            $found = Transaction::where('num_transaction', $request->num_transaction)->exists();

            if ($found) {
                return response()->json([
                    'message' => 'La transaction peut pas être dupliqué '
                ], 400);
            }

            if($request->montant <=0) {
                return response()->json([
                    'message' => 'Le Montant ne peut être inférieur ou égal à 0.'
                ], 400);
            }

            $bank = BankAccount::first();
            if (!$bank) {
                return response()->json([
                    'message' => 'La Banque introuvable dans ce system'
                ], 400);
            }
            $caisse = Caisse::first();
            if (!$caisse) {
                return response()->json([
                    'message' => 'La Caisse introuvable dans ce system'
                ], 400);
            }

            $Added =  Transaction::create([
                'num_transaction' => $request->num_transaction,
                'date_transaction' => $request->date_transaction,
                'montant' => $request->montant,
                'commentaire' => $request->commentaire,
                'factureVente_id' => $request->factureVente_id,
                'factureAchat_id' => $request->factureAchat_id,
                'paiementDepense_id' => $request->paiementDepense_id,
                'venteSecteur_id' => $request->venteSecteur_id,
                'avoirsAchat_id'=> $request->avoirsAchat_id,
                'modePaiement' => $request->modePaiement,// escpece , virement , cheque , trait
                'journal_id' => $request->journal_id,
            ]);

            if(!$Added) {
                DB::rollBack();
                Log::error('Échec de création La Transaction , réessayer ultérieurement');
                return response()->json([
                    'message' => 'Quelque chose a mal tourné. Veuillez réessayer plus tard.'
                ], 400);
            }

            if($request->factureAchat_id) {
                $facture = facture::find($request->factureAchat_id);
                $bank = BankAccount::first();
                if (!$facture) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Facture introuvable'
                    ], 400);
                }

                if($facture->Confirme == false) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Facture nest pas Confirmé'
                    ], 400);
                }

                if($facture->EtatPaiement == "Paye") {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Facture est deja Paye'
                    ], 400);
                }



                if($request->modePaiement == "cheque") {
                    $validator = Validator::make($request->all(), [
                        'numero_cheque' => 'required',
                        'delais_cheque' => 'required',
                        'etat_cheque'=> 'required',
                    ]);

                    if ($validator->fails()) {
                        DB::rollBack();
                        return response()->json([
                            'message' => $validator->errors()->first()
                        ], 400);
                    }

                    $payementResterResponse = $this->paymentAchatRest($facture->id);
                    $payementRester = json_decode($payementResterResponse->getContent(), true)['rest'];

                    if($request->montant >  $payementRester) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le montant payé est supérieur au montant requis',
                            'montant rest'=>$payementRester
                        ], 400);
                    }

                    if($request->etat_cheque == "portfeuille") {

                        $Added->update([
                            'numero_cheque' => $request->numero_cheque,
                            'delais_cheque' => $request->delais_cheque,
                            'etat_cheque' => $request->etat_cheque,
                        ]);

                        $etat = "En Cours";

                        $facture->update([
                            'EtatPaiement' =>$etat
                        ]);

                        DB::commit();
                        return response()->json([
                            'message' => 'Cheque a ete créée avec succès , le delais final est :'.$request->delais_cheque,
                            'data' => $Added,
                            'EtatPaiement' => $etat
                        ]);
                    }

                    /* if($request->etat_cheque == "regler") {
                        $bank = BankAccount::first();
                        if($request->montant > $bank->solde) {
                            DB::rollBack();
                            return response()->json([
                                'message' => 'Le compte banquire est insuffisant pour effectuer cette opération',
                                'solde ' =>$bank->solde
                            ], 400);
                        }
                        $Added->update([
                            'numero_cheque' => $request->numero_cheque,
                            'delais_cheque' => $request->delais_cheque,
                            'etat_cheque' => $request->etat_cheque,
                        ]);

                        $etat = "En Cours";
                        if($request->montant == $facture->Total_Rester) {
                            $etat= "Paye";
                        }

                        $bank->update([
                            'solde' => $bank->solde - $request->montant,
                        ]);

                        $facture->update([
                                 'Total_Rester'=>$facture->Total_Rester - $request->montant,
                                'Total_Regler'=>$facture->Total_Regler + $request->montant,
                                'EtatPaiement' =>$etat,
                        ]);

                        DB::commit();
                        return response()->json([
                            'message' => 'Cheque a ete créée avec succès , le delais final est : '.$request->delais_cheque,
                            'data' => $Added,
                            'EtatPaiement' => $etat
                        ]);
                    } */

                }


                if($request->modePaiement == "espece") {
                    $modePaiement = Caisse::first();

                    if($request->montant > $modePaiement->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'La caisse est insuffisant pour effectuer cette opération',
                            'solde ' =>$modePaiement->solde
                        ], 400);
                    }
                }

                if($request->modePaiement == "virement") {
                    $modePaiement = BankAccount::first();
                    $Added->update([
                        'num_virement' => $request->num_virement,
                    ]);
                    if($request->montant > $modePaiement->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le compte banquire est insuffisant pour effectuer cette opération',
                            'solde ' =>$modePaiement->solde
                        ], 400);
                    }

                }

                $payementResterResponse = $this->paymentAchatRest($facture->id);
                $payementRester = json_decode($payementResterResponse->getContent(), true)['rest'];

                if($request->montant > $payementRester) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le montant payé est supérieur au montant requis '
                    ], 400);
                }

                $etat = "En Cours";
                if($request->montant == $facture->Total_Rester) {
                    $etat= "Paye";
                }

                $modePaiement->update([
                    'solde' => $modePaiement->solde - $request->montant,
                ]);

                $facture->update([
                         'Total_Rester'=>$facture->Total_Rester - $request->montant,
                        'Total_Regler'=>$facture->Total_Regler + $request->montant,
                        'EtatPaiement' =>$etat,
                ]);



                DB::commit();
                return response()->json([
                    'message' => 'Création réussie de Transaction Achat',
                    'data' => $Added,
                    'EtatPaiement' => $etat
                ]);
            }

            if($request->factureVente_id) {
                $facture = factureVente::find($request->factureVente_id);
                $bank = BankAccount::first();
                if (!$facture) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Facture de Vente introuvable'
                    ], 400);
                }

                if($facture->Confirme == false) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Facture nest pas Confirmé'
                    ], 400);
                }

                if($facture->EtatPaiement == "Paye") {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Facture de Vente est deja Paye'
                    ], 400);
                }

                if($request->modePaiement == "cheque") {
                    $validator = Validator::make($request->all(), [
                        'numero_cheque' => 'required',
                        'delais_cheque' => 'required',
                        'etat_cheque'=> 'required',
                    ]);

                    if ($validator->fails()) {
                        DB::rollBack();
                        return response()->json([
                            'message' => $validator->errors()->first()
                        ], 400);
                    }

                    $payementResterResponse = $this->paymentVenteRest($facture->id);
                    $payementRester = json_decode($payementResterResponse->getContent(), true)['rest'];

                    if($request->montant >  $payementRester) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le montant payé est supérieur au montant requis',
                            'montant rest'=>$payementRester
                        ], 400);
                    }

                    if($request->etat_cheque == "portfeuille") {

                        $Added->update([
                            'numero_cheque' => $request->numero_cheque,
                            'delais_cheque' => $request->delais_cheque,
                            'etat_cheque' => $request->etat_cheque,
                        ]);

                        $etat = "En Cours";

                        $facture->update([
                            'EtatPaiement' =>$etat
                        ]);

                        DB::commit();
                        return response()->json([
                            'message' => 'Cheque a ete créée avec succès , le delais final est :'.$request->delais_cheque,
                            'data' => $Added,
                            'EtatPaiement' => $etat
                        ]);
                    }

                   /*  if($request->etat_cheque == "regler") {
                        $bank = BankAccount::first();
                        if($request->montant > $bank->solde) {
                            DB::rollBack();
                            return response()->json([
                                'message' => 'Le compte banquire est insuffisant pour effectuer cette opération',
                                'solde ' =>$bank->solde
                            ], 400);
                        }
                        $Added->update([
                            'numero_cheque' => $request->numero_cheque,
                            'delais_cheque' => $request->delais_cheque,
                            'etat_cheque' => $request->etat_cheque,
                        ]);

                        $etat = "En Cours";
                        if($request->montant == $facture->Total_Rester) {
                            $etat= "Paye";
                        }

                        $bank->update([
                            'solde' => $bank->solde + $request->montant,
                        ]);

                        $facture->update([
                                 'Total_Rester'=>$facture->Total_Rester - $request->montant,
                                'Total_Regler'=>$facture->Total_Regler + $request->montant,
                                'EtatPaiement' =>$etat,
                        ]);

                        DB::commit();
                        return response()->json([
                            'message' => 'Cheque a ete créée avec succès , le delais final est : '.$request->delais_cheque,
                            'data' => $Added,
                            'EtatPaiement' => $etat
                        ]);
                    } */

                }


                if($request->modePaiement == "espece") {
                    $modePaiement = Caisse::first();
                }

                if($request->modePaiement == "virement") {
                    $modePaiement = BankAccount::first();
                    $Added->update([
                        'num_virement' => $request->num_virement,
                    ]);
                }

                $payementResterResponse = $this->paymentVenteRest($facture->id);
                $payementRester = json_decode($payementResterResponse->getContent(), true)['rest'];


                if($request->montant > $payementRester) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le montant payé est supérieur au montant requis '
                    ], 400);
                }

                $etat = "En Cours";
                if($request->montant == $facture->Total_Rester) {
                    $etat= "Paye";
                }

                $modePaiement->update([
                    'solde' => $modePaiement->solde + $request->montant,
                ]);

                $facture->update([
                         'Total_Rester'=>$facture->Total_Rester - $request->montant,
                        'Total_Regler'=>$facture->Total_Regler + $request->montant,
                        'EtatPaiement' =>$etat,
                ]);



                DB::commit();

                return response()->json([
                    'message' => 'Création réussie de Transaction Vente',
                    'data' => $Added,
                    'EtatPaiement' => $etat
                ]);
            }

            if($request->venteSecteur_id) {
                $facture = venteSecteur::find($request->venteSecteur_id);

                if (!$facture) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Vente Secteur introuvable'
                    ], 400);
                }

                if($facture->Confirme == false) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Facture nest pas Confirmé'
                    ], 400);
                }

                if($facture->EtatPaiement == "Paye") {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Vente Secteur est deja Paye'
                    ], 400);
                }

                if($request->modePaiement == "espece") {
                    $modePaiement = Caisse::first();
                } else {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Les ventes Secteurs paye juste en espèces.'
                    ], 400);
                }

                if($request->montant > $facture->Total_Rester) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le montant payé est supérieur au montant requis '
                    ], 400);
                }

                $etat = "En Cours";
                if($request->montant == $facture->Total_Rester) {
                    $etat= "Paye";
                }

                $modePaiement->update([
                    'solde' => $modePaiement->solde + $request->montant,
                ]);

                $facture->update([
                         'Total_Rester'=>$facture->Total_Rester - $request->montant,
                        'Total_Regler'=>$facture->Total_Regler + $request->montant,
                        'EtatPaiement' =>$etat,
                ]);



                DB::commit();

                return response()->json([
                    'message' => 'Création réussie de Transaction Vente',
                    'data' => $Added,
                    'EtatPaiement' => $etat
                ]);
            }

            if($request->paiementDepense_id) {
                $facture = paiementDepense::find($request->paiementDepense_id);
                $bank = BankAccount::first();
                if (!$facture) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le paiement de cet depense introuvable'
                    ], 400);
                }

                if($facture->Confirme == false) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le paiement de cet depense nest pas Confirmé'
                    ], 400);
                }

                if($facture->EtatPaiement == "Paye") {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le paiement de cet depense est deja Paye'
                    ], 400);
                }



                if($request->modePaiement == "cheque") {
                    $validator = Validator::make($request->all(), [
                        'numero_cheque' => 'required',
                        'delais_cheque' => 'required',
                        'etat_cheque'=> 'required',
                    ]);

                    if ($validator->fails()) {
                        DB::rollBack();
                        return response()->json([
                            'message' => $validator->errors()->first()
                        ], 400);
                    }

                    $payementResterResponse = $this->paymentDepenseRest($facture->id);
                    $payementRester = json_decode($payementResterResponse->getContent(), true)['rest'];

                    if($request->montant >  $payementRester) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le montant payé est supérieur au montant requis',
                            'montant rest'=>$payementRester
                        ], 400);
                    }

                    if($request->etat_cheque == "portfeuille") {

                        $Added->update([
                            'numero_cheque' => $request->numero_cheque,
                            'delais_cheque' => $request->delais_cheque,
                            'etat_cheque' => $request->etat_cheque,
                        ]);

                        $etat = "En Cours";

                        $facture->update([
                            'EtatPaiement' =>$etat
                        ]);

                        DB::commit();
                        return response()->json([
                            'message' => 'Cheque a ete créée avec succès , le delais final est :'.$request->delais_cheque,
                            'data' => $Added,
                            'EtatPaiement' => $etat
                        ]);
                    }

                }


                if($request->modePaiement == "espece") {
                    $modePaiement = Caisse::first();

                    if($request->montant > $modePaiement->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'La caisse est insuffisant pour effectuer cette opération',
                            'solde ' =>$modePaiement->solde
                        ], 400);
                    }
                }

                if($request->modePaiement == "virement") {
                    $modePaiement = BankAccount::first();
                    $Added->update([
                        'num_virement' => $request->num_virement,
                    ]);
                    if($request->montant > $modePaiement->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le compte banquire est insuffisant pour effectuer cette opération',
                            'solde ' =>$modePaiement->solde
                        ], 400);
                    }

                }

                $payementResterResponse = $this->paymentDepenseRest($facture->id);
                $payementRester = json_decode($payementResterResponse->getContent(), true)['rest'];

                if($request->montant > $payementRester) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le montant payé est supérieur au montant requis '
                    ], 400);
                }

                $etat = "En Cours";
                if($request->montant == $facture->Total_Rester) {
                    $etat= "Paye";
                }

                $modePaiement->update([
                    'solde' => $modePaiement->solde - $request->montant,
                ]);

                $facture->update([
                         'Total_Rester'=>$facture->Total_Rester - $request->montant,
                        'Total_Regler'=>$facture->Total_Regler + $request->montant,
                        'EtatPaiement' =>$etat,
                ]);



                DB::commit();
                return response()->json([
                    'message' => 'Création réussie de Transaction Achat',
                    'data' => $Added,
                    'EtatPaiement' => $etat
                ]);
            }

            if($request->avoirsAchat_id) {

                $facture = avoirsAchat::find($request->avoirsAchat_id);
                $bank = BankAccount::first();
                if (!$facture) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'L`Avoirs introuvable'
                    ], 400);
                }

                if($facture->Confirme == false) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Facture nest pas Confirmé'
                    ], 400);
                }

                if($facture->EtatPaiement == "Paye") {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'L`Avoirs est deja Paye'
                    ], 400);
                }

                if($request->modePaiement == "cheque") {
                    $validator = Validator::make($request->all(), [
                        'numero_cheque' => 'required',
                        'delais_cheque' => 'required',
                        'etat_cheque'=> 'required',
                    ]);

                    if ($validator->fails()) {
                        DB::rollBack();
                        return response()->json([
                            'message' => $validator->errors()->first()
                        ], 400);
                    }

                    $payementResterResponse = $this->paymentAvoirsAchatRest($facture->id);
                    $payementRester = json_decode($payementResterResponse->getContent(), true)['rest'];

                    if($request->montant >  $payementRester) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le montant payé est supérieur au montant requis',
                            'montant rest'=>$payementRester
                        ], 400);
                    }

                    if($request->etat_cheque == "portfeuille") {

                        $Added->update([
                            'numero_cheque' => $request->numero_cheque,
                            'delais_cheque' => $request->delais_cheque,
                            'etat_cheque' => $request->etat_cheque,
                        ]);

                        $etat = "En Cours";

                        $facture->update([
                            'EtatPaiement' =>$etat
                        ]);

                        DB::commit();
                        return response()->json([
                            'message' => 'Cheque a ete créée avec succès , le delais final est :'.$request->delais_cheque,
                            'data' => $Added,
                            'EtatPaiement' => $etat
                        ]);
                    }

                }


                if($request->modePaiement == "espece") {
                    $modePaiement = Caisse::first();

                    if($request->montant > $modePaiement->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'La caisse est insuffisant pour effectuer cette opération',
                            'solde ' =>$modePaiement->solde
                        ], 400);
                    }
                }

                if($request->modePaiement == "virement") {
                    $modePaiement = BankAccount::first();
                    $Added->update([
                        'num_virement' => $request->num_virement,
                    ]);
                    if($request->montant > $modePaiement->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le compte banquire est insuffisant pour effectuer cette opération',
                            'solde ' =>$modePaiement->solde
                        ], 400);
                    }

                }

                $payementResterResponse = $this->paymentAvoirsAchatRest($facture->id);
                $payementRester = json_decode($payementResterResponse->getContent(), true)['rest'];

                if($request->montant > $payementRester) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le montant payé est supérieur au montant requis '
                    ], 400);
                }

                $etat = "En Cours";
                if($request->montant == $facture->Total_Rester) {
                    $etat= "Paye";
                }

                $modePaiement->update([
                    'solde' => $modePaiement->solde + $request->montant,
                ]);

                $facture->update([
                        'Total_Rester'=>$facture->Total_Rester - $request->montant,
                        'Total_Regler'=>$facture->Total_Regler + $request->montant,
                        'EtatPaiement' =>$etat,
                ]);



                DB::commit();
                return response()->json([
                    'message' => 'Création réussie de Transaction Achat',
                    'data' => $Added,
                    'EtatPaiement' => $etat
                ]);


            }

         } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
               'message' => 'Quelque chose a mal tourné. Veuillez réessayer plus tard'
            ], 404);
        }
    }


    public function show($id)
    {
        try {

            $TransactionExiste = Transaction::find($id);
            if(!$TransactionExiste) {
                return response()->json([
                    'message' => 'Transaction introuvable'
                ], 404);
            }

            return response()->json($TransactionExiste);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

     public function transactionByFacture($id, $type)
     {
         try {

             if($type === 'achat') {
                 $factureModel = 'factureAchat_id';
                 $facture = facture::findOrFail($id)->first();
             } elseif($type === 'vente') {
                 $factureModel = 'factureVente_id';
                 $facture = factureVente::findOrFail($id)->first();
             } elseif($type === 'avoirsachat') {
                 $factureModel = 'avoirsAchat_id';
                 $facture = avoirsAchat::findOrFail($id)->first();
             } elseif ($type === 'ventesecteur') {
                 $factureModel = 'venteSecteur_id';
                 $facture = venteSecteur::findOrFail($id)->first();
             }elseif ($type === 'depense') {
                $factureModel = 'paiementDepense_id';
                $facture = paiementDepense::findOrFail($id)->first();
            }
             else {
                 return abort(404);
             }

             $transactions = Transaction::where($factureModel, $id)
                 ->orderBy('id', 'desc')
                 ->get();

             if (!$transactions) {
                 return response()->json([
                     'message' => 'Transaction introuvable'
                 ], 404);
             }

             $transactionFactures = [];

             foreach ($transactions as $transaction) {
                 $date_transaction = Carbon::parse($transaction->date_transaction)->format('d F Y');

                 $transactionFacture = [
                     'id' => $transaction->id,
                     'description' => 'Paiement en ' . $transaction->modePaiement . ' Nª' . $transaction->num_transaction,
                     'montant' => $transaction->montant,
                     'date_transaction' => $date_transaction,
                     'modePaiement' => $transaction->modePaiement,
                     'etat_cheque' => $transaction->etat_cheque,
                 ];

                 $transactionFactures[] = $transactionFacture;
             }

             return response()->json($transactionFactures);
         } catch (Exception $e) {
             DB::rollBack();
             return response()->json([
                 'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
             ], 404);
         }
     }

    public function getNumeroTR()
    {
        try {
            $year = Carbon::now()->format('Y');

            $lastRecord = Transaction::latest()->first();

            if (!empty($lastRecord)) {
                $lastIncrementStringYear = substr($lastRecord->num_transaction, -4, 4);
                if ($lastIncrementStringYear === $year) {
                    $lastIncrementString = substr($lastRecord->num_transaction, 3, 5);
                    $incrementNumber = intval($lastIncrementString) + 1;
                } else {
                    $incrementNumber = 1;
                }
            } else {
                $incrementNumber = 1;
            }

            $incrementString = 'TR-' . str_pad($incrementNumber, 5, '0', STR_PAD_LEFT) . '/' . $year;

            return response()->json(['num_transaction' => $incrementString]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
            ], 404);
        }
    }


    public function confirmeCheque($id)
    {
        try {
            DB::beginTransaction();
            $Transaction = Transaction::find($id);

            if(!$Transaction) {
                DB::rollBack();
                return response()->json([
                    'message' => 'La Transaction introuvable'
                ], 400);
            }


            $bankAccount = BankAccount::first();

            if(!$bankAccount) {
                DB::rollBack();
                return response()->json([
                    'message' => 'La banque introuvable'
                ], 400);
            }

            if($Transaction->modePaiement !="cheque") {
                DB::rollBack();
                return response()->json([
                    'message' => 'La Transaction cest pas un cheque'
                ], 400);
            }

            if($Transaction->etat_cheque =="regler") {
                DB::rollBack();
                return response()->json([
                    'message' => 'La Transaction est déja Confirmé'
                ], 400);
            }

            $facture=null;

            if($Transaction->factureAchat_id) {
                $facture = facture::find($Transaction->factureAchat_id);
                if($Transaction->montant > $bankAccount->solde) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le compte banquire est insuffisant pour effectuer cette opération'
                    ], 400);
                }
            }

            if($Transaction->avoirsAchat_id) {
                $facture = avoirsAchat::find($Transaction->avoirsAchat_id);
            }

            if($Transaction->factureVente_id) {
                $facture = factureVente::find($Transaction->factureVente_id);
            }

            if($Transaction->paiementDepense_id) {
                $facture = paiementDepense::find($Transaction->paiementDepense_id);
                if($Transaction->montant > $bankAccount->solde) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Le compte banquire est insuffisant pour effectuer cette opération'
                    ], 400);
                }
            }


            if(!$facture) {
                DB::rollBack();
                return response()->json([
                    'message' => 'La facture introuvable'
                ], 400);
            }

            $etat = "En Cours";
            if($Transaction->montant == $facture->Total_Rester) {
                $etat= "Paye";
            }


            if($Transaction->factureVente_id || $Transaction->avoirsAchat_id) {
                $bankAccount->update([
                    $bankAccount->solde += $Transaction->montant
                ]);
            }



            if($Transaction->factureAchat_id || $Transaction->paiementDepense_id) {
                $bankAccount->update([
                    $bankAccount->solde -= $Transaction->montant
                ]);
            }


            if (!$bankAccount->wasChanged()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'une erreur pendant lexécution de cette opération'
                ], 400);
            }


            $Transaction->update([
                'etat_cheque' => 'regler'
            ]);

            if (!$Transaction->wasChanged()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'une erreur pendant lexécution de cette opération'
                ], 400);
            }

            $facture->update([
               $facture->Total_Rester -= $Transaction->montant,
               $facture->Total_Regler += $Transaction->montant
            ]);

            if ($facture->Total_Rester == 0) {
                $facture->update([
                    'EtatPaiement' => 'Paye',
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Cheque a été Reglè et se confirme avec succès.',
                'EtatPaiement' =>$etat]);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }


}
