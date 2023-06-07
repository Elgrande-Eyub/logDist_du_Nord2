<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;
    public $timestamps = true;

    protected $table = 'transactions';
    protected $fillable = [
        'num_transaction',
        'num_virement',
        'date_transaction',
        'montant',
        'factureAchat_id',
        'factureVente_id',
        'paiementDepense_id',
        'venteSecteur_id',
        'avoirsVente_id',
        'avoirsAchat_id',
        'modePaiement',
        'numero_cheque',
        'delais_cheque',
        'etat_cheque',
        'journal_id',
        'commentaire',
    ];

    public function facture()
    {
        return $this->belongsTo(facture::class);
    }
    public function factureVente()
    {
        return $this->belongsTo(factureVente::class);
    }
    public function bank_id()
    {
        return $this->belongsTo(BankAccount::class);
    }
    public function journal_id()
    {
        return $this->belongsTo(Journal::class);
    }


}
