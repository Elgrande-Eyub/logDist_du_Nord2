<?php

use App\Http\Controllers\ArticleCategoryController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AvoirsAchatController;
use App\Http\Controllers\AvoirsVenteController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BonCommandeController;
use App\Http\Controllers\BonCommandeVenteController;
use App\Http\Controllers\BonConsignationController;
use App\Http\Controllers\BonLivraisonController;
use App\Http\Controllers\BonLivraisonVenteController;
use App\Http\Controllers\BonReceptionController;
use App\Http\Controllers\BonretourAchatController;
use App\Http\Controllers\BonretourVenteController;
use App\Http\Controllers\BonSortieController;
use App\Http\Controllers\CaisseController;
use App\Http\Controllers\CamionController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CreditController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeRoleController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\FactureVenteController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\PaiementDepenseController;
use App\Http\Controllers\SecteurController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransfertController;
use App\Http\Controllers\VendeurController;
use App\Http\Controllers\VenteSecteurController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WithdrawController;
use App\Models\BankAccount;
use App\Models\bonCommandeVente;
use App\Models\bonLivraison;
use App\Models\bonretourAchat;
use App\Models\facture;
use App\Models\Fournisseur;
use App\Models\withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use L5Swagger\Http\Controllers\SwaggerController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Articls Routes ----------------------------------------------------------------------------

Route::apiResource('articles',ArticleController::class);
Route::get('articlefr/{id}',[ArticleController::class,'articleFr']);
Route::get('articlewr/{id}',[ArticleController::class,'articleWarehouse']);
Route::post('insertArticles',[ArticleController::class,'insertArticles']);

// Category Routes ----------------------------------------------------------------------------

Route::apiResource('categories',ArticleCategoryController::class);

// returns all articles with the given category ID
Route::get('/categories/{id}/get',[ArticleCategoryController::class,'getCategoryByid']);

// Fournisseurs Routes ----------------------------------------------------------------------------

Route::apiResource('fournisseurs',FournisseurController::class);

// bank Routes ----------------------------------------------------------------------------

Route::apiResource('bank',BankAccountController::class);

// Invetories Routes ----------------------------------------------------------------------------

Route::apiResource('inventories',InventoryController::class);

// Journal Routes ----------------------------------------------------------------------------

Route::apiResource('journal',JournalController::class);

// Warehouse Routes ----------------------------------------------------------------------------

Route::apiResource('warehouse',WarehouseController::class);

// client Routes ----------------------------------------------------------------------------

Route::apiResource('client',ClientController::class);

// Employees roles Routes ----------------------------------------------------------------------------

Route::apiResource('emprole',EmployeeRoleController::class);

// Employees Routes ----------------------------------------------------------------------------

Route::apiResource('employee',EmployeeController::class);

// depense Routes ----------------------------------------------------------------------------

Route::apiResource('depense',DepenseController::class);

// societe Routes ----------------------------------------------------------------------------

Route::apiResource('societe',CompanyController::class);

// camion Routes ----------------------------------------------------------------------------

Route::apiResource('camion',CamionController::class);
Route::post('camions',[CamionController::class,'storeMultiple']);

// vendeur Routes ----------------------------------------------------------------------------

Route::apiResource('vendeur',VendeurController::class);
Route::post('vendeurs',[VendeurController::class,'multipleVendeurs']);

// caisse Routes ----------------------------------------------------------------------------

Route::apiResource('caisse',CaisseController::class);

// caisse Routes ----------------------------------------------------------------------------

Route::apiResource('secteur',SecteurController::class);

///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////


// Bon Commande Routes ----------------------------------------------------------------------------

Route::apiResource('boncommande',BonCommandeController::class);
Route::get('/printbc/{id}/{condition}/{isDownloaded}',[BonCommandeController::class,'printbonCommande']);// print bonCommande
Route::get('/getnbc',[BonCommandeController::class,'getNumeroBC']);
Route::get('boncommande/month/{MonthId}',[BonCommandeController::class,'getByMonth']); // get bonCommandes by Month
Route::put('boncommande/confirme/{id}',[BonCommandeController::class,'markAsConfirmed']); // Confirme BonCommande

// Bon livraison Routes ----------------------------------------------------------------------------

Route::apiResource('bonlivraison',BonLivraisonController::class);
// Route::put('/bonlivraison-addattachement/{id}',[BonLivraisonController::class,'addAttachement']);
Route::get('/getbc',[BonLivraisonController::class,'getBonCommande']); // get all bonCommandes confirmed and not linnked to a bon Livraison
Route::put('bonlivraison/confirme/{id}',[BonLivraisonController::class,'markAsConfirmed']);
Route::get('/printbl/{id}/{isDownloaded}',[BonLivraisonController::class,'printbonLivraison']);
Route::get('/printbr/{id}/{isDownloaded}',[BonLivraisonController::class,'printbonReception']);
Route::get('getchangebr',[BonLivraisonController::class,'getBonRetour']); // get all bon retour are not linked to Bon Change


// bon Retour Achat Achat Routes ----------------------------------------------------------------------------

Route::apiResource('bonretourachat',BonretourAchatController::class);
Route::get('printbretour/{id}/{isDownloaded}',[BonretourAchatController::class,'printbonRetour']);
Route::put('bonretourachat/confirme/{id}',[BonretourAchatController::class,'markAsConfirmed']);
Route::get('getnbretour',[BonretourAchatController::class,'getNumerobr']);
Route::get('getblr',[BonretourAchatController::class,'getBonLivraison']);

// Bon Reception Routes ----------------------------------------------------------------------------

Route::apiResource('bonreception',BonReceptionController::class);
// Route::get('/printbr/{id}',[BonReceptionController::class,'printbonReception']);
// Route::get('/getblr',[BonReceptionController::class,'getBonLivraison']);

// facture Routes ----------------------------------------------------------------------------

Route::apiResource('facture',FactureController::class);
Route::get('/printf/{id}/{isDownloaded}',[FactureController::class,'facturePrint']);
Route::put('facture/confirme/{id}',[FactureController::class,'markAsConfirmed']); // Confirme facture
Route::get('/getblf',[FactureController::class,'getBonLivraison']);
Route::put('/markaspaid-facture/{id}',[FactureController::class,'markAsPaid']);

// Avoirs Achat Routes ----------------------------------------------------------------------------

Route::apiResource('avoirsachat',AvoirsAchatController::class);
Route::put('avoirsachat/confirme/{id}',[AvoirsAchatController::class,'markAsConfirmed']);
Route::get('getbonretours',[AvoirsAchatController::class,'getBonRetour']);
Route::get('getarticlesbr/{id}',[AvoirsAchatController::class,'getArticlesBonRetour']);
Route::put('/markaspaid-avoirsachat/{id}',[AvoirsAchatController::class,'markAsPaid']);

// Bon Consignation Routes ----------------------------------------------------------------------------

Route::apiResource('bonconsignation',BonConsignationController::class);

// bon Commande Ventes Routes ----------------------------------------------------------------------------

Route::apiResource('boncommandevente',BonCommandeVenteController::class);
Route::put('boncommandevente/confirme/{id}',[BonCommandeVenteController::class,'markAsConfirmed']);
Route::get('/printbcv/{id}/{condition}/{isDownloaded}',[BonCommandeVenteController::class,'printbcv']);

// bon Livraison Ventes Routes ----------------------------------------------------------------------------

Route::apiResource('bonlivraisonvente',BonLivraisonVenteController::class);
Route::put('bonlivraisonvente/confirme/{id}',[BonLivraisonVenteController::class,'markConfirmed']);
Route::get('getnblv',[BonLivraisonVenteController::class,'getNumeroBLV']);
Route::get('/printblv/{id}/{isDownloaded}',[BonLivraisonVenteController::class,'printbonLivraisonVente']);
Route::get('/printbrv/{id}/{isDownloaded}',[BonLivraisonVenteController::class,'printbonReceptionVente']);
Route::get('getbcv',[BonLivraisonVenteController::class,'getBonCommandeVente']);

// facture Ventes Routes ----------------------------------------------------------------------------

Route::apiResource('facturevente',FactureVenteController::class); // Resource APi for Invioce
Route::put('facturevente/confirme/{id}',[FactureVenteController::class,'markAsConfirmed']); // mark the invioce as Confirmed
Route::get('getnf',[FactureVenteController::class,'getNumeroFacture']); // get the Number Generated for the Invioce
Route::get('/printfv/{id}/{isDownloaded}',[FactureVenteController::class,'facturePrint']); // Print the iniovce
Route::get('getblv',[FactureVenteController::class,'getBonLivraisonVente']); // get Bon Livraison Are not linked to A invioce
Route::put('/markaspaid-facturevent/{id}',[FactureVenteController::class,'markAsPaid']);

// bon Retour Vente Routes ----------------------------------------------------------------------------

Route::apiResource('bonretourvente',BonretourVenteController::class);
Route::put('bonretourvente/confirme/{id}',[BonretourVenteController::class,'markAsConfirmed']);
Route::get('getblrv',[BonretourVenteController::class,'getBonLivraison']);



// Avoirs Vente Routes ----------------------------------------------------------------------------

Route::apiResource('avoirsvente',AvoirsVenteController::class);
Route::put('avoirsvente/confirme/{id}',[AvoirsVenteController::class,'markAsConfirmed']);
Route::get('getnav',[AvoirsVenteController::class,'getNumeroAvoirs']);
Route::get('getfv',[AvoirsVenteController::class,'getFactures']);
Route::get('printav/{id}/{isDownloaded}',[AvoirsVenteController::class,'avoirePrint']);
Route::get('getarticlesbrv/{id}',[AvoirsVenteController::class,'getArticlesBonRetour']);
Route::put('/markaspaid-avoirsvente/{id}',[AvoirsVenteController::class,'markAsPaid']);

// Bon Sortie Routes ----------------------------------------------------------------------------

Route::apiResource('bonsortie',BonSortieController::class);
Route::put('bonsortie/confirme/{id}',[BonSortieController::class,'markAsConfirmed']);
Route::get('printbs/{id}/{isDownloaded}',[BonSortieController::class,'printbs']);
Route::get('getnbs',[BonSortieController::class,'getNumeroBS']);

// Vente Secteur Routes ----------------------------------------------------------------------------

Route::apiResource('ventesecteur',VenteSecteurController::class);
Route::put('ventesecteur/confirme/{id}',[VenteSecteurController::class,'markAsConfirmed']);
Route::get('printvs/{id}/{isDownloaded}',[VenteSecteurController::class,'printvs']);
Route::get('getbs',[VenteSecteurController::class,'getbonSortie']);


// Paiement des depense Routes ----------------------------------------------------------------------------

Route::apiResource('paiementdepense',PaiementDepenseController::class);
Route::put('paiementdepense/confirme/{id}',[PaiementDepenseController::class,'markAsConfirmed']);
Route::get('paiementdepense/paymentrest/{id}',[TransactionController::class,'paymentDepenseRest']);

// transfert Routes ----------------------------------------------------------------------------

Route::apiResource('transfert',TransfertController::class);
Route::put('transfert/confirme/{id}',[TransfertController::class,'markAsConfirmed']);
Route::get('printt/{id}/{isDownloaded}',[TransfertController::class,'printt']);
Route::get('getnt',[TransfertController::class,'getNumeroT']);
Route::get('getartbyware/{id}',[TransfertController::class,'getInventoryBywarehouse']);


// Credit Routes ----------------------------------------------------------------------------

Route::apiResource('credit',CreditController::class); // declaration old credit
Route::put('credit/confirme/{id}',[CreditController::class,'markAsConfirmed']); // Confirme Credit

Route::get('credit-vendeur',[CreditController::class,'getCreditvendeurs']); // get all Credit Vendeurs
Route::get('credit-fournisseur',[CreditController::class,'getCreditFournisseurs']); // get all Credit fournisseur
Route::get('credit-client',[CreditController::class,'getCreditClients']); // get All Credit Client
Route::get('credit-vendeur/{id}',[CreditController::class,'getCreditVendeur']); // get detail of credit Vendeur
Route::get('credit-fournisseur/{id}',[CreditController::class,'getCreditFournisseur']); // get detail of credit fournissuer
Route::get('credit-client/{id}',[CreditController::class,'getCreditClient']);// get detail of credit client

// Transactions Routes ----------------------------------------------------------------------------

Route::apiResource('transaction',TransactionController::class);
Route::get('facture/transactions/{id}/{type}',[TransactionController::class,'transactionByFacture']); // get All transaction linked to Invioce

Route::get('factureachat/paymentrest/{id}',[TransactionController::class,'paymentAchatRest']); // the rest of an Invioce Achat
Route::get('facturevente/paymentrest/{id}',[TransactionController::class,'paymentVenteRest']);// the rest of an invioce Vente

Route::get('transactions/get',[TransactionController::class,'getNumeroTR']); // Get Number Generated for the Transaction
Route::put('transactions/confirme/{id}',[TransactionController::class,'confirmeCheque']); // Confirmed Transation Type Cheque


// withdraw Routes ----------------------------------------------------------------------------


Route::apiResource('withdraw',WithdrawController::class);
Route::get('opbank',[WithdrawController::class,'OperationBancaire']);
Route::get('opcaisse',[WithdrawController::class,'OperationCaisse']);
Route::get('trbank',[WithdrawController::class,'TransactionBancaire']);
Route::get('trcaisse',[WithdrawController::class,'TransactionCaisse']);

Route::get('getclientcredit/{id}',[BonCommandeVenteController::class,'CheckClientCredit']);
Route::get('getvendeurcredit/{id}',[BonSortieController::class,'CheckVendeurCredit']);


// image Routes ----------------------------------------------------------------------------

Route::get('getimage/{type}/{attachement}',[Controller::class,'getImage']);

// statics

Route::get('/fournisseur-count', [StatisticsController::class, 'getFournisseurCount']);
Route::get('/client-count', [StatisticsController::class, 'getClientCount']);
Route::get('/commande-count', [StatisticsController::class, 'getCommandeCount']);
Route::get('/achat-payer', [StatisticsController::class, 'getAchatApayer']);
Route::get('/vente-total', [StatisticsController::class, 'getVenteTotal']);
Route::get('/cheque-retard', [StatisticsController::class, 'getChequeportfeuille']);
Route::get('/revenue', [StatisticsController::class, 'getRevenue']);
