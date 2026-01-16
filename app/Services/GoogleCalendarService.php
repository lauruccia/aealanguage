<?php

namespace App\Services;

use App\Models\GoogleToken;
use Carbon\Carbon;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;

class GoogleCalendarService
{
    public function client(): GoogleClient
    {
        $client = new GoogleClient();

        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        // offline + consent (refresh_token arriva solo la prima volta)
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $client->setScopes([Calendar::CALENDAR]);

        /** @var GoogleToken|null $token */
        $token = GoogleToken::query()->where('provider', 'google')->first();

        if (! $token) {
            throw new \RuntimeException('Token Google non configurato. Esegui prima il collegamento OAuth (Google).');
        }

        // 1) set token se presente
        if ($token->access_token) {
            $expiresIn = 3600;

            if ($token->expires_at) {
                $diff = now()->diffInSeconds(Carbon::parse($token->expires_at), false);
                $expiresIn = max(1, $diff);
            }

            // created coerente con expires_in
            $created = time() - max(0, (3600 - $expiresIn));

            $client->setAccessToken([
                'access_token'  => $token->access_token,
                'refresh_token' => $token->refresh_token,
                'expires_in'    => $expiresIn,
                'created'       => $created,
            ]);
        } elseif ($token->refresh_token) {
            // caso: access_token mancante ma refresh_token presente
            $client->setAccessToken([
                'refresh_token' => $token->refresh_token,
                'created'       => time(),
                'expires_in'    => 1,
            ]);
        }

        // 2) refresh se scaduto (o se non ho access_token)
        if (($client->isAccessTokenExpired() || empty($token->access_token)) && $token->refresh_token) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($token->refresh_token);

            if (! empty($newToken['error'])) {
                throw new \RuntimeException(
                    'Errore refresh token Google: ' . ($newToken['error_description'] ?? $newToken['error'])
                );
            }

            if (! empty($newToken['access_token'])) {
                $token->access_token = $newToken['access_token'];
                $token->expires_at = now()->addSeconds($newToken['expires_in'] ?? 3600);

                if (! empty($newToken['refresh_token'])) {
                    $token->refresh_token = $newToken['refresh_token'];
                }

                $token->save();

                $client->setAccessToken([
                    'access_token'  => $token->access_token,
                    'refresh_token' => $token->refresh_token,
                    'expires_in'    => $newToken['expires_in'] ?? 3600,
                    'created'       => time(),
                ]);
            }
        }

        return $client;
    }

    /**
     * Crea o aggiorna un SOLO evento ricorrente settimanale con Google Meet.
     *
     * $data:
     * - summary
     * - description
     * - start (Carbon|string) -> prima occorrenza
     * - end (Carbon|string)   -> fine prima occorrenza
     * - weekly_day_iso (1..7) -> 1=Lun ... 7=Dom
     * - until (Carbon|string) -> data/ora limite (ultima lezione)
     * - attendees_emails []   -> docente/studente
     * - existing_event_id ?   -> se presente, aggiorna invece di creare
     */
    public function createOrUpdateRecurringMeetEvent(array $data): array
    {
        $client = $this->client();
        $service = new Calendar($client);

        $calendarId = config('services.google.calendar_id', 'primary');
        $tz = config('app.timezone', 'Europe/Rome');

        $start = Carbon::parse($data['start'])->timezone($tz);
        $end   = Carbon::parse($data['end'])->timezone($tz);

        $weeklyDayIso = (int) ($data['weekly_day_iso'] ?? 1);
        $byDay = $this->isoDayToRruleDay($weeklyDayIso);

        // UNTIL in UTC: YYYYMMDDTHHMMSSZ
        $untilUtc = Carbon::parse($data['until'])->utc();
        $untilStr = $untilUtc->format('Ymd\THis\Z');

        $attendees = collect($data['attendees_emails'] ?? [])
            ->filter()
            ->unique()
            ->map(fn ($email) => ['email' => $email])
            ->values()
            ->all();

        // payload base evento
        $event = new Event([
            'summary' => $data['summary'] ?? 'Lezione',
            'description' => $data['description'] ?? null,
            'start' => new EventDateTime([
                'dateTime' => $start->toRfc3339String(),
                'timeZone' => $tz,
            ]),
            'end' => new EventDateTime([
                'dateTime' => $end->toRfc3339String(),
                'timeZone' => $tz,
            ]),
            'attendees' => $attendees,
            'recurrence' => [
                "RRULE:FREQ=WEEKLY;BYDAY={$byDay};UNTIL={$untilStr}",
            ],
        ]);

        // =========================
        // UPDATE (se event_id esiste)
        // =========================
        if (! empty($data['existing_event_id'])) {
            $existingId = (string) $data['existing_event_id'];

            // 1) Verifico che l'evento esista e NON sia cancellato
            try {
                $existing = $service->events->get($calendarId, $existingId); // <-- niente conferenceDataVersion (ti dava errore)

                if ($existing && $existing->getStatus() === 'cancelled') {
                    // Evento cancellato => lo ricreiamo da zero
                    $data['existing_event_id'] = null;
                }
            } catch (\Throwable $e) {
                // Not found / access issues => ricreo
                $data['existing_event_id'] = null;
            }

            // 2) Se ancora esiste, patch (senza rigenerare meet)
            if (! empty($data['existing_event_id'])) {
                $updated = $service->events->patch($calendarId, $existingId, $event, [
                    'sendUpdates' => 'all',
                ]);

                return $this->extractEventResult($updated);
            }
        }

        // =========================
        // CREATE (nuovo evento + Meet)
        // =========================
        $requestId = 'meet-' . uniqid('', true);

        $event->setConferenceData(new ConferenceData([
            'createRequest' => new CreateConferenceRequest([
                'requestId' => $requestId,
            ]),
        ]));

        $created = $service->events->insert($calendarId, $event, [
            'conferenceDataVersion' => 1,
            'sendUpdates' => 'all',
        ]);

        return $this->extractEventResult($created);
    }

    private function extractEventResult(Event $event): array
    {
        $meetLink = null;

        // 1) spesso è qui
        if (method_exists($event, 'getHangoutLink') && $event->getHangoutLink()) {
            $meetLink = $event->getHangoutLink();
        }

        // 2) oppure qui
        $conf = $event->getConferenceData();
        if (! $meetLink && $conf && $conf->getEntryPoints()) {
            foreach ($conf->getEntryPoints() as $ep) {
                if ($ep->getEntryPointType() === 'video' && $ep->getUri()) {
                    $meetLink = $ep->getUri();
                    break;
                }
            }
        }

        return [
            'event_id'  => $event->getId(),
            'meet_url'  => $meetLink,
            'html_link' => $event->getHtmlLink(),
        ];
    }

    private function isoDayToRruleDay(int $isoDay): string
    {
        return match (max(1, min(7, $isoDay))) {
            1 => 'MO',
            2 => 'TU',
            3 => 'WE',
            4 => 'TH',
            5 => 'FR',
            6 => 'SA',
            7 => 'SU',
        };
    }
}
