<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Vente Secteur Nº{{ $commande->reference }}</title>

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

        .tableContent thead th {
            text-align: center
        }

        table,
        th,
        td {
            border: 0.5px solid #dddddd60;
            padding: 4px;
            font-size: 15px;

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
        <h1><u> Vente Secteur::{{ $commande->reference }}</u></h1>
    </header>
    <table class="order-details">
        <thead>

            <tr class="bg-blue">
                <th width="50%" colspan="2">Details de Vente Secteur : </th>
                <th width="50%" colspan="2"></th>
            </tr>
        </thead>
        <tbody>

            <tr>
                <td colspan="2">
                    @php
                        use Carbon\Carbon;
                        $dateCommande = Carbon::parse($commande->dateSortie);
                    @endphp
                    {{-- <strong><u> {{ $company->name }} </u><br></strong> --}}
                    <strong>- Reference :</strong> {{ $commande->reference }}<br>
                    <strong>- Secteur :</strong>{{ $secteur->secteur }}<br>
                    <strong>- Location de Distrubtion :</strong> {{ $commande->nom_Warehouse }}<br>
                    <strong>- Date de Sortie : </strong>{{ $dateCommande->format('d/m/Y H:i') }} <br>
                    <strong>- Date de Tirage :</strong> {{ $dateTirage->format('d/m/Y H:i') }}<br>


                </td>
                <td colspan="2">
                    <strong>- Vendeur :</strong> {{ $vendeur1->nomComplet ?? 'N/A' }}<br>
                    <strong>- Aide-Vendeur :</strong> {{ $vendeur2->nomComplet ?? 'N/A' }}<br>
                    @if ($vendeur3 && $vendeur3->nomComplet)
                        <strong>- Aide-Vendeur 2 :</strong> {{ $vendeur3->nomComplet }}<br>
                    @endif
                    <strong>- Camion :</strong>
                    {{ $camion->matricule . ' ' . $camion->marque . ' ' . $camion->modele ?? 'N/A' }}<br>
                    <strong>- Kelometrage de Sortie :</strong> {{ $commande->kilometrageFait . ' Km' }}<br>


                </td>

            </tr>

        </tbody>
    </table>

    <table class="tableContent">
        <thead>
            <tr>
                {{--       <th class="no-border text-start heading" colspan="5">
                     List des Articles :
                 </th> --}}
            </tr>
            <tr class="bg-blue">
                <th>Reference</th>
                <th>Desingation</th>
                {{-- <th>Condition</th> --}}
                {{-- <th>Prix Unitaire</th> --}}
                <th>QTE Sortie</th>
                <th>QTE RetourV</th>
                <th>QTE Perime</th>
                <th>QTE Echange</th>
                <th>QTE Gratuit</th>
                <th>QTE Credit</th>
                <th>QTE Vendu</th>
                <th>PU HT</th>
                <th>Total Vendu HT</th>
                {{-- <th>Total</th> --}}
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
                    {{-- <td>{{  $article->unite }}</td> --}}
                    <td>{{ $article->qte_sortie }}</td>
                    <td>{{ $article->qte_retourV }}</td>
                    <td>{{ $article->qte_perime }}</td>
                    <td>{{ $article->qte_echange }}</td>
                    <td>{{ $article->qte_gratuit }}</td>
                    <td>{{ $article->qte_credit }}</td>
                    <td>{{ $article->qte_vendu }}</td>
                    <td>{{ number_format($article['Prix_unitaire'], 2, ',', ' ') }} DHs</td>
                    <td>{{ number_format($article['Total_Vendu'], 2, ',', ' ') }} DHs</td>
                    {{-- <td>   {{number_format($article['Prix_unitaire'], 2, ',', ' ')}}DH</td>
                <td>     {{number_format($article['Total_HT'], 2, ',', ' ')}} DH</td>
 --}}

                </tr>
            @endforeach

            <tr>
                <td colspan="8" style="border: rgba(255, 255, 255, 0.01) solid 1px; " class="total-heading"></td>
                <td colspan="2" class="total-heading">Total HT :</td>
                <td colspan="1" class="total-heading">{{ number_format($commande['Total_HT'], 2, ',', ' ') }} Dhs
                </td>
            </tr>
            <tr>
                <td colspan="8" style="border: rgba(255, 255, 255, 0.01) solid 1px; " class="total-heading"></td>

                <td colspan="2" class="total-heading">Total TVA ({{ $commande->TVA }}%<) :</td>
                <td colspan="1" class="total-heading"> {{ number_format($commande['Total_TVA'], 2, ',', ' ') }} Dhs
                </td>
            </tr>

            <tr>
                <td colspan="8" style="border: rgba(255, 255, 255, 0.01) solid 1px; " class="total-heading"></td>
                <td colspan="2" class="total-heading">Total <small>Inc. TTC</small> :</td>
                <td colspan="1" class="total-heading"> {{ number_format($commande['Total_TTC'], 2, ',', ' ') }} Dhs
                </td>
            </tr>
        </tbody>
    </table>

    <br>

    <footer style="position: absolute;margin-bottom:0.5rem; bottom:0 ;width:100% ; text-align:center">

        {!! DNS2D::getBarcodeHTML(url('api/printvs/' . $commande->id . '/false'), 'QRCODE', 4, 4) !!}

        <hr>

        {{ $company->name }}
        - Address: {{ $company->adresse }}
        - Email: {{ $company->email }}
        - Telephone: {{ $company->telephone }}
        - FAX: {{ $company->fax }}
        - ICE: {{ $company->ICE }}
        - RC: {{ $company->RC }}
        - IF: {{ $company->IF }}
        {{--    <br> Donnees bancaires :
            - Banque: {{ $bank->nomBank }}
            - Compte: {{ $bank->numero_compt }}
            - RIB: {{ $bank->rib_compt }} --}}

    </footer>
</body>

</html>
