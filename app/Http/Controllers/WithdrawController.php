<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Caisse;
use App\Models\Transaction;
use App\Models\withdraw;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WithdrawController extends Controller
{
    public function index()
    {
        try {
            return response()->json(withdraw::get());
        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function OperationBancaire()
    {
        try {
            $AllBanksOperations = withdraw::whereIn('mode', ['bank'])
            ->whereIn('type', ['depots', 'transfert', 'withdraw'])
            ->orWhere(function ($query) {
                $query->where('mode', 'caisse')->where('type', 'transfert');
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

            return response()->json($AllBanksOperations);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }


    public function OperationCaisse()
    {
        try {
            $AllCaisseOperations = withdraw::whereIn('mode', ['caisse'])
            ->whereIn('type', ['depots', 'transfert', 'withdraw'])
            ->orWhere(function ($query) {
                $query->where('mode', 'bank')->where('type', 'transfert');
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

            return response()->json($AllCaisseOperations);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function TransactionBancaire()
    {
        try {

            $AllBanksTransaction = Transaction::whereIn('modePaiement', ['virement', 'cheque'])
               ->where(function ($query) {
                   $query->orWhere(function ($query) {
                       $query->where('modePaiement', 'cheque')
                       ->where('etat_cheque', 'regler');
                   })
                ->orWhere('modePaiement', 'virement');
               })
               ->orderBy('created_at', 'desc')
               ->limit(10)
               ->get();

            return response()->json($AllBanksTransaction);

        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }
    }

    public function TransactionCaisse()
    {
        try {
            /*  $AllCaisseTransaction = Transaction::whereIn('modePaiement', ['escpece'])
             ->get(); */
            $AllCaisseTransaction = Transaction::whereIn('modePaiement', ['espece'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

            return response()->json($AllCaisseTransaction);

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


            $validator = Validator::make($request->all(), [
                'type' => 'required',
                'solde' => 'required',
                'mode' => 'required',
                'motif' => 'required',
                'journal_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first()
                ], 400);
            }

            if($request->solde <=0) {
                return response()->json([
                    'message' => 'Le Montant ne peut être inférieur ou égal à 0.'
                ], 400);
            }

            if ($request->type != "depots" && $request->type != "withdraw" && $request->type != "transfert") {
                DB::rollBack();
                return response()->json([
                    'message' => 'Vous devez choisir le type de transaction.'
                ], 400);
            }

            $Added = withdraw::create([
                'type' => $request->type,
                'solde' => $request->solde,
                'mode' => $request->mode,
                'motif' => $request->motif,
                'journal_id' => $request->journal_id,
                // 'depense_id' => $request->depense_id,
            ]);

            if (!$Added) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement.'
                ], 400);
            }

            if($request->type =="withdraw") {

                if($request->mode=="bank") {

                    $bank = BankAccount::first();
                    if(!$bank) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'La Banque introuvable'
                        ], 400);
                    }

                    if($request->solde > $bank->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le compte banquire est insuffisant pour effectuer cette opération'
                        ], 400);
                    }

                    $bank->update([
                        $bank->solde -= $request->solde
                    ]);

                    if (!$bank->wasChanged()) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'une erreur pendant lexécution de cette opération'
                        ], 400);
                    }

                }

                if($request->mode=="caisse") {

                    $Caisse = Caisse::first();
                    if(!$Caisse) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'La Caisse introuvable'
                        ], 400);
                    }

                    if($request->solde > $Caisse->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le solde est insuffisant pour effectuer cette opération'
                        ], 400);
                    }

                    $Caisse->update([
                        $Caisse->solde -= $request->solde
                    ]);

                    if (!$Caisse->wasChanged()) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'une erreur pendant lexécution de cette opération'
                        ], 400);
                    }

                }

            }

            if($request->type =="depots") {

                if($request->mode=="bank") {

                    $bank = BankAccount::first();
                    if(!$bank) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'La Banque introuvable'
                        ], 400);
                    }

                    $bank->update([
                        $bank->solde += $request->solde
                    ]);
                    if (!$bank->wasChanged()) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'une erreur pendant lexécution de cette opération'
                        ], 400);
                    }

                }

                if($request->mode=="caisse") {

                    $Caisse = Caisse::first();
                    if(!$Caisse) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'La Caisse introuvable'
                        ], 400);
                    }

                    $Caisse->update([
                        $Caisse->solde += $request->solde
                    ]);

                    if (!$Caisse->wasChanged()) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'une erreur pendant lexécution de cette opération'
                        ], 400);
                    }
                }

            }

            if($request->type =="transfert") {
                $bank = BankAccount::first();
                if(!$bank) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Banque introuvable'
                    ], 400);
                }

                $caisse =Caisse::first();
                if(!$caisse) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Caisse introuvable'
                    ], 400);
                }

                if($request->mode=="bank") {



                    if($bank->solde < $request->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le solde est insuffisant pour effectuer cette opération'
                        ], 400);
                    }

                    $caisse->update([
                        $caisse->solde += $request->solde
                    ]);

                    $bank->update([
                        $bank->solde -= $request->solde
                    ]);

                }

                if($request->mode=="caisse") {

                    if($caisse->solde < $request->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le solde est insuffisant pour effectuer cette opération'
                        ], 400);
                    }

                    $caisse->update([
                        $caisse->solde -= $request->solde
                    ]);
                    $bank->update([
                        $bank->solde += $request->solde
                    ]);

                }

            }

            /* if($request->type =="paiementDepense") {
                $bank = BankAccount::first();
                if(!$bank) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Banque introuvable'
                    ], 400);
                }

                $caisse =Caisse::first();
                if(!$caisse) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'La Caisse introuvable'
                    ], 400);
                }

                if($request->mode=="bank") {



                    if($bank->solde < $request->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le solde est insuffisant pour effectuer cette opération'
                        ], 400);
                    }

                    $caisse->update([
                        $caisse->solde += $request->solde
                    ]);

                    $bank->update([
                        $bank->solde -= $request->solde
                    ]);

                }

                if($request->mode=="caisse") {

                    if($caisse->solde < $request->solde) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Le solde est insuffisant pour effectuer cette opération'
                        ], 400);
                    }

                    $caisse->update([
                        $caisse->solde -= $request->solde
                    ]);
                    $bank->update([
                        $bank->solde += $request->solde
                    ]);

                }

            } */

            DB::commit();
            return response()->json([
                'message' => 'L`opération effectue avec succès',
                ]);
        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quelque chose est arrivé. Veuillez réessayer ultérieurement'
            ], 404);
        }

    }

}
