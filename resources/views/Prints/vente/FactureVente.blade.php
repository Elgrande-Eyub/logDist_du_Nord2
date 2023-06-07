<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Facture Vente Nº{{ $commande->numero_FactureVente }}</title>

    <style>
        html,
        body {
            margin: 10px;
            padding: 10px;
            font-family: sans-serif;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #303030;
        }

        header {
            background-color: #efefef;
            color: #303030;
            padding: 10px;
            text-align: center;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        span,
        label {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0px !important;

        }

        table thead th {
            height: 1rem;
            text-align: left;
            font-size: 16px;
            font-family: sans-serif;
        }

        table,
        th,
        td {
            border: 1px solid #dddddd60;
            padding: 5px;
            font-size: 14px;
        }

        .heading {
            font-size: 24px;
            margin-top: 12px;
            margin-bottom: 12px;

        }

        .small-heading {
            font-size: 18px;

        }

        .total-heading {
            font-size: 14px;
            font-weight: 700;

        }

        .order-details tbody tr td:nth-child(1) {
            width: 20%;

        }

        .order-details tbody tr td:nth-child(3) {
            width: 20%;

        }

        .text-start {
            text-align: left;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .company-data span {
            margin-bottom: 4px;
            display: inline-block;
            font-family: sans-serif;
            font-size: 14px;
            font-weight: 400;
        }

        .no-border {
            border: 1px solid #fff !important;
        }

        .bg-blue {
            background-color: #efefef;
            color: #303030;
        }

        footer {
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            font-size: .8rem
        }

        footer hr {
            border: 1px solid #eee;
        }
    </style>
</head>

<body>
    <header>
        <h1><u>Facture Nº::{{ $commande->numero_FactureVente }}</u></h1>
    </header>
    <table class="order-details">
        <thead>
            <tr>
                <th width="50%" colspan="1" style="text-align: start">
                    <img src="https://i.ibb.co/P5Dfb0Z/logo-light.png" width="50%" alt="logo">
                </th>
                @php
                    use Carbon\Carbon;
                    $dateFacture = Carbon::parse($commande->date_Facture);
                @endphp
                <th width="50%" colspan="3" class="text-start company-data">
                    <span><strong>Nº Bon de Commande : </strong> <u> Nº{{ $commande->Numero_bonCommandeVente }}</u></span>


                    <span><strong>Nº Bon de Livraison : </strong> <u> Nº{{ $commande->Numero_bonLivraisonVente }}</u></span>
                    <span><strong>Nº Facture : </strong> <u> Nº{{ $commande->numero_FactureVente }}</u></span>
                    <br>
                    <span><strong>Date Facture : </strong>{{ $dateFacture->format('d/m/Y') }}</span> <br>
                    <span><strong>Entrepôt : </strong>{{ $commande->nom_Warehouse }}</span> <br>
                    <span><strong>Cond. Paiement : </strong>60 Jours Fin De Mois</span> <br>
                    <span><strong>Date Echeance : </strong>{{ $dateFacture->addDays($commande->conditionPaiement)->format('d/m/Y') }}</span> <br>
                </th>
            </tr>
            <tr class="bg-blue">
                <th width="50%" colspan="1">Societe</th>
                <th width="50%" colspan="1">Client</th>
            </tr>
        </thead>
        <tbody>

            <tr>
                <td colspan="1">

                    <strong><u> {{ $company->name }} </u><br></strong>
                    <strong>- Address :</strong> {{ $company->adresse }}<br>
                    <strong>- Email :</strong>{{ $company->email }}<br>
                    <strong>- Telephone :</strong> {{ $company->telephone }}<br>
                    <strong>- ICE :</strong> {{ $company->ICE }}<br>
                    <strong>- RC :</strong> {{ $company->RC }}<br>
                    <strong>- IF :</strong> {{ $company->IF }}<br>


                </td>

                <td colspan="1">
                    <strong> <u>{{ $client->nom_Client }}</u></strong><br />
                    <strong>- Address: </strong>{{ $client->adresse_Client }}<br />
                    <strong>- Email: </strong>{{ $client->email_Client }}<br />
                    <strong>- Telephone: </strong>{{ $client->telephone_Client }}<br />
                    <strong>- ICE: </strong>{{ $client->ICE_Client }}<br>
                    <strong>- RC: </strong>{{ $client->RC_Client }}<br>
                    <strong>- PT: </strong>{{ $client->Pattent_Client }}<br>
                </td>

            </tr>

        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                {{--       <th class="no-border text-start heading" colspan="5">
                     List des Articles :
                 </th> --}}
            </tr>
            <tr class="bg-blue">
                <th>Reference</th>
                <th>Desingation</th>
                <th>Cond..</th>
                <th>Quantity</th>
                <th>Prix Unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            {{-- <tr>
                 <td width="10%">16</td>
                 <td>
                     Mi Note 7
                 </td>
                 <td width="10%">P12</td>
                 <td width="10%">$14000</td>
                 <td width="10%">1</td>
                 <td width="15%" class="fw-bold">$14000</td>
             </tr> --}}
            @foreach ($articles as $article)
                <tr class="item">
                    <td>{{ $article->reference }}</td>
                    <td>{{ $article->article_libelle }}</td>
                    <td>{{ $article->unite }}</td>
                    <td>{{ $article->Quantity }}</td>
                    <td> {{ number_format($article['Prix_unitaire'], 2, ',', ' ') }}DHs</td>
                    <td> {{ number_format($article['Total_HT'], 2, ',', ' ') }} DHs</td>


                </tr>
            @endforeach

            <tr>
                <td colspan="3" style="border: rgba(255, 255, 255, 0.01) solid 1px; " class="total-heading"></td>
                <td colspan="2" class="total-heading">Total HT :</td>
                <td colspan="1" class="total-heading">{{ number_format($commande['Total_HT'], 2, ',', ' ') }} Dhs
                </td>
            </tr>
            {{--  <tr>
                <td colspan="3" style="border: rgba(255, 255, 255, 0.01) solid 1px; " class="total-heading"></td>

                <td colspan="2" class="total-heading">TVA :</td>
                <td colspan="1" class="total-heading">{{  $commande->TVA }}%</td>
            </tr> --}}
            <tr>
                <td colspan="3" style="border: rgba(255, 255, 255, 0.01) solid 1px; " class="total-heading"></td>

                <td colspan="2" class="total-heading">Total TVA ({{ $commande->TVA }}%<) :</td>
                <td colspan="1" class="total-heading"> {{ number_format($commande['Total_TVA'], 2, ',', ' ') }} Dhs
                </td>
            </tr>
            <tr>
                <td colspan="3" style="border: rgba(255, 255, 255, 0.01) solid 1px; " class="total-heading"></td>
                <td colspan="2" class="total-heading">Remise</small> :</td>
                <td colspan="1" class="total-heading"> {{ number_format($commande['remise'], 2, ',', ' ') }} Dhs
                </td>
            </tr>
            <tr>
                <td colspan="3" style="border: rgba(255, 255, 255, 0.01) solid 1px; " class="total-heading"></td>
                <td colspan="2" class="total-heading">Total <small>Inc. TTC</small> :</td>
                <td colspan="1" class="total-heading"> {{ number_format($commande['Total_TTC'], 2, ',', ' ') }} Dhs
                </td>
            </tr>
        </tbody>
    </table>

    <br>

    <footer style="position: absolute;margin-bottom:0.5rem; bottom:0 ;width:100% ; text-align:center">

        {!! DNS2D::getBarcodeHTML(url('api/printfv/' . $commande->id . '/false'), 'QRCODE', 4, 4) !!}

        <hr>



        {{ $company->name }}
        - Address: {{ $company->adresse }}
        - Email: {{ $company->email }}
        - Telephone: {{ $company->telephone }}
        - FAX: {{ $company->fax }}
        - ICE: {{ $company->ICE }}
        - RC: {{ $company->RC }}
        - IF: {{ $company->IF }}
        <br> Donnees bancaires :
        - Banque: {{ $bank->nomBank }}
        - Compte: {{ $bank->numero_compt }}
        - RIB: {{ $bank->rib_compt }}


    </footer>
</body>

</html>
