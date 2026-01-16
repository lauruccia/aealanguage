<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $p['subject'] ?? 'Invito lezione (Google Meet)' }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color:#111; line-height: 1.45;">
    <div style="max-width: 640px; margin: 0 auto; padding: 16px;">
        <h2 style="margin: 0 0 10px;">
            {{ $p['title'] ?? 'Lezione online' }}
        </h2>

        <p style="margin: 0 0 12px;">
            Ciao,<br>
            questa è la conferma della tua lezione online con A&amp;A Language Center.
        </p>

        <div style="padding: 12px; border: 1px solid #ddd; border-radius: 10px; margin: 0 0 14px;">
            <p style="margin: 0 0 6px;">
                <strong>Corso:</strong> {{ $p['course_name'] ?? '—' }}
            </p>
            <p style="margin: 0 0 6px;">
                <strong>Quando:</strong> {{ $p['when_text'] ?? '—' }}
            </p>

            @if(!empty($p['meet_url']))
                <p style="margin: 10px 0 0;">
                    <strong>Link Google Meet:</strong><br>
                    <a href="{{ $p['meet_url'] }}" target="_blank" rel="noopener noreferrer">
                        {{ $p['meet_url'] }}
                    </a>
                </p>
            @endif

            @if(!empty($p['calendar_html_link']))
                <p style="margin: 10px 0 0;">
                    <strong>Evento su Google Calendar:</strong><br>
                    <a href="{{ $p['calendar_html_link'] }}" target="_blank" rel="noopener noreferrer">
                        Apri evento
                    </a>
                </p>
            @endif
        </div>

        <p style="margin: 0 0 8px;">
            Se non trovi l’invito automatico di Google, questa email contiene comunque tutti i link necessari.
        </p>

        <p style="margin: 16px 0 0; font-size: 12px; color:#555;">
            A&amp;A Language Center<br>
            Tel. +39 06 5743734<br>
            Viale Leonardo da Vinci, 193 - Roma
        </p>
    </div>
</body>
</html>
