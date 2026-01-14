<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Modulo di iscrizione #{{ $enrollment->id }}</title>

    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 12px; color: #0b2a6f; }
        .sheet { border: 3px solid #0b2a6f; padding: 12mm; }
        .title { text-align: center; font-size: 28px; font-weight: bold; margin: 0 0 12px; letter-spacing: 1px; }
        .row { display: flex; gap: 12px; }
        .col { flex: 1; }
        .muted { color: #0b2a6f; }
        .small { font-size: 11px; }
        .tiny { font-size: 10px; }
        .hr { border-top: 2px solid #0b2a6f; margin: 10px 0; }
        .header { align-items: flex-start; }
        .header .left, .header .right { font-size: 11px; line-height: 1.25; }
        .header .center { text-align: center; }
        .logo { max-height: 70px; max-width: 160px; display: inline-block; }
        .section { margin-top: 8px; }
        .lang-line { margin: 10px 0 6px; font-weight: bold; }
        .clause { margin: 6px 0; line-height: 1.35; }
        .list { margin: 6px 0 6px 18px; }
        .list li { margin: 2px 0; }
        .field-row { display: flex; justify-content: space-between; gap: 18px; margin-top: 14px; }
        .field-box { flex: 1; }
        .label { font-weight: bold; color: #000; }
        .value { color: #000; }
        .line { border-bottom: 1px solid #0b2a6f; height: 14px; display: inline-block; min-width: 140px; vertical-align: bottom; }
        .line.long { min-width: 260px; }
        .sign-row { display: flex; justify-content: space-between; gap: 18px; margin-top: 20px; }
        .sign { flex: 1; text-align: center; color: #000; }
        .sign .sigline { margin-top: 18px; border-bottom: 1px solid #0b2a6f; height: 1px; }
        .footer { margin-top: 10px; display: flex; justify-content: space-between; gap: 12px; align-items: flex-end; }
        .footer .left { font-size: 11px; color: #000; }
        .footer .right { font-size: 10px; color: #000; text-align: right; min-width: 180px; }

        .no-print { margin-bottom: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">Stampa</button>
</div>

@php
    $language = $course?->subject?->name ?: '—';

    $enrolledAt = $enrollment->enrolled_at ? \Carbon\Carbon::parse($enrollment->enrolled_at) : now();

    // Importi (usati solo nei “campi” del modulo, non aggiungiamo tabelle)
    $total = (float) ($enrollment->course_price_eur ?? 0) + (float) ($enrollment->registration_fee_eur ?? 0);
    $deposit = (float) ($enrollment->deposit ?? 0);
    $saldo = max(0, $total - $deposit);

    $plan = $enrollment->payment_plan ?? 'monthly';
    $installmentsCount = (int) ($enrollment->installments_count ?? 0);
    $monthly = ($installmentsCount > 0) ? ($saldo / $installmentsCount) : 0.0;

    $fmt = fn($n) => number_format((float)$n, 2, ',', '.');

    // “entro e non oltre ___ mesi”: se hai ends_at lo stimiamo, altrimenti lasciamo vuoto
    $months = null;
    if (!empty($enrollment->ends_at) && !empty($enrollment->enrolled_at)) {
        $months = \Carbon\Carbon::parse($enrollment->enrolled_at)->diffInMonths(\Carbon\Carbon::parse($enrollment->ends_at));
        $months = max(1, $months);
    }
@endphp

<div class="sheet">

    <div class="title">MODULO DI ISCRIZIONE</div>

    <div class="row header">
        <div class="col left">
            <div class="label">A&amp;A Language Center S.r.l.</div>
            <div>viale Leonardo da Vinci,193 - 00145 Roma</div>
            <div>e-mail: <span class="value">info@aealangagecenter.it</span></div>
            <div>C.F./P.Iva : <span class="value">09121441001</span></div>
        </div>

        <div class="col center">
            {{-- Se hai un logo pubblico, mettilo in public/images/logo-aa.png --}}
            @php
    $logoPath = public_path('images/logo-aa.png');
@endphp

@if (file_exists($logoPath))
    <img
        class="logo"
        src="{{ asset('images/logo-aa.png') }}"
        alt="A&A Language Center"
    >
@else
    <div class="label">A&amp;A Language Center</div>
@endif

            <div class="tiny muted" style="margin-top:4px;">
                {{-- eventuali bandierine/icone puoi inserirle qui se vuoi --}}
            </div>
        </div>

        <div class="col right" style="text-align:right;">
            <div class="label">Trinity College London</div>
            <div>Registered Examination Centre nr. 8241</div>
            <div style="margin-top:12px;" class="label">Centro Didattico e iscrizioni in sede</div>
            <div class="label" style="margin-top:10px;">Roma lì</div>
            <div class="value">{{ $enrolledAt->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="section">
        <div class="lang-line">
            Lingua: <span class="value">{{ mb_strtoupper($language) }}</span>
        </div>

        <div class="clause">
            <span class="label">a)</span>
            In espressa accettazione di quanto oggi proposto da A&amp;A Language Center Srl, nel presente modulo di iscrizione definito nel
            seguito contratto, vengono messi a disposizione del sottoscrittore/a, nella lingua sopra scelta, entro e non oltre
            <span class="value">{{ $months ? $months : '____' }}</span> mesi a far data da oggi, i seguenti servizi che potranno essere svolti previa prenotazione dal
            Lunedì al Venerdì dalle ore 9:00 alle ore 20:00 ed il Sabato dalle ore 9:00 alle ore 14:00 :
        </div>

        <ol class="list">
            <li><span class="label">LEZIONI PERSONALIZZATE</span> nel Vostro disponibile Centro Didattico</li>
            <li><span class="label">LEZIONI DI FULL IMMERSION</span> di piccoli gruppi tenute nel Vostro disponibile Centro Didattico</li>
            <li><span class="label">TEST EXAMINATION</span> per controllare l’avanzamento dell’apprendimento.</li>
        </ol>

        <div class="clause">
            <span class="label">b)</span>
            per i servizi di cui sopra, il corrispettivo, esente IVA ai sensi dell’art.10 n.20 del D.P.R. 26-10-1972 n. 633, viene fissato in :
            ( <span class="value">{{ $fmt($total) }}</span> ) comprensivi di acconto ( <span class="value">{{ $fmt($deposit) }}</span> ), a saldo rimanente
            ( <span class="value">{{ $fmt($saldo) }}</span> ), da versarsi entro 15 giorni dalla sottoscrizione del presente contratto.
        </div>

        <div class="clause">
            In alternativa il corrispettivo viene fissato in:
            ( <span class="value">{{ $fmt($total) }}</span> ) comprensivi di acconto ( <span class="value">{{ $fmt($deposit) }}</span> ), a saldo rimanente
            ( <span class="value">{{ $fmt($saldo) }}</span> ), da pagarsi in n.
            ( <span class="value">{{ $installmentsCount ?: '____' }}</span> ) quote mensili consecutive di
            ( <span class="value">{{ $installmentsCount ? $fmt($monthly) : '____' }}</span> ),
            la cui prima dovrà essere versata entro 15 giorni dalla sottoscrizione del presente contratto alla proponente A&amp;A Language Center Srl.
        </div>

        <div class="clause"><span class="label">c)</span> E’ nulla qualsiasi obbligazione che non risulti scritta sul presente contratto irrevocabile ogni negoziato in sede.</div>

        <div class="clause">
            <span class="label">d)</span>
            In caso di mancato inizio del contratto,la proponente potrà acquisire a titolo di penale la somma depositata o da depositare dal
            sottoscrittore/a a titolo di acconto, ritenendo che il sottoscrittore abbia rinunciato ad eseguire il contratto. Nel caso in cui venga data
            esecuzione al contratto, l’importo contrattuale dovrà essere comunque versato come sopra descritto.
        </div>

        <div class="clause">
            <span class="label">e)</span>
            Le lezioni e/o le Full Immersion prenotate dal sottoscrittore e non disdette con un preavviso minimo di 24 ore precedenti la
            data e l’orario di prenotazione, verranno considerate come dallo stesso fruite.
        </div>

        <div class="clause">
            <span class="label">f)</span>
            D.Lgs 30.06.2003 n.196 “Codice in materia di protezione dei dati personali”: la A&amp;A Language Center Srl si impegna ad utilizzare i
            dati personali acquisiti, esclusivamente per uso interno,trattamento correlato al presente contratto
        </div>

        <div class="clause">
            Il sottoscritto/a, dopo aver preso visione della informativa di cui agli art. 13 e 4 del D.Lgs 30.06.2003 n.196, presta il proprio
            consenso all’intero trattamento dei propri dati personali.
        </div>

        <div class="clause">
            <span class="label">g)</span>
            Foro competente per ogni controversia è esclusivamente quello di Roma
        </div>

        <div class="clause" style="text-align:center; margin-top: 10px;">
            <span class="label">Firma</span> <span class="line long"></span>
        </div>

        <div class="field-row">
            <div class="field-box">
                <div class="label">Nome</div>
                <div class="value">{{ $student->first_name ?? '' }}</div>

                <div style="height:8px;"></div>

                <div class="label">nato/a il:</div>
                <div class="value">
                    {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->format('d/m/Y') : '' }}
                </div>

                <div style="height:8px;"></div>

                <div class="label">Residente in:</div>
                <div class="value">
                    {{ trim(collect([$student->address_line, $student->postal_code, $student->city, $student->province, $student->country])->filter()->implode(', ')) }}
                </div>

                <div style="height:8px;"></div>

                <div class="label">e-mail:</div>
                <div class="value">{{ $student->email ?? '' }}</div>

                <div style="height:10px;"></div>

                <div class="label">A&amp;A Language Center Srl</div>
            </div>

            <div class="field-box">
                <div class="label">Cognome</div>
                <div class="value">{{ $student->last_name ?? '' }}</div>

                <div style="height:8px;"></div>

                <div class="label">a:</div>
                <div class="value">{{ $student->birth_place ?? '' }}</div>
            </div>

            <div class="field-box" style="text-align:left;">
                <div class="label">C.F. /P.IVA</div>
                <div class="value">{{ $student->tax_code ?? $student->vat_number ?? '' }}</div>

                <div style="height:8px;"></div>

                <div class="label">Tel.</div>
                <div class="value">{{ $student->phone ?? '' }}</div>

                <div style="height:8px;"></div>

                <div class="label">Tel. Genitore</div>
                <div class="value">{{ $student->guardian_phone ?? '' }}</div>
            </div>
        </div>

        <div class="sign-row">
            <div class="sign">
                <div class="sigline"></div>
                <div class="tiny">firma del proponente</div>
            </div>
            <div class="sign">
                <div class="sigline"></div>
                <div class="tiny">firma per ricevuta dell’acconto</div>
            </div>
            <div class="sign">
                <div class="sigline"></div>
                <div class="tiny">firma del sottoscrittore</div>
            </div>
        </div>

        <div class="clause tiny" style="margin-top: 12px; color:#000;">
            Per accettazione specifica delle clausole a) Oggetto e Termine; b) Condizioni di pagamento del corrispettivo e alternative; c) irrevocabilità
            della proposta; d) Mancato inizio del contratto,clausola penale esecuzione contratto; e) Disdette delle lezioni e/o Full Immersion; D. Lgs. 196/2003; g) Foro
            esclusivo Roma.
        </div>

        <div class="footer">
            <div class="left">
                <div><span class="label">IBAN:</span> <span class="value">IT 39 Q 03440 03218 0000 0017 6700</span> - Banco Desio</div>
                <div>Tel.: +39 06 5743734 - Tel./Fax +39 06 57301261 - Mobile +39 346 3836175</div>
                <div>website: <span class="value">www.aealanguagecenter.it</span></div>
            </div>
            <div class="right">
                <div>Prodotto da</div>
                <div><span class="label">ID</span> <span class="value">{{ $enrollment->id }}</span></div>
            </div>
        </div>

    </div>

</div>

</body>
</html>
