<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class ImportStudentsFromExcel extends Command
{
    protected $signature = 'import:students
        {--file=studenti.xlsx : Nome file dentro storage/app}
        {--temp-password= : Password temporanea per i nuovi utenti (opzionale)}';

    protected $description = 'Importa studenti da Excel (colonne tecniche) e crea/aggiorna anche gli utenti (senza invio email)';

    public function handle(): int
    {
        $fileName = $this->option('file') ?: 'studenti.xlsx';
        $path = storage_path('app/' . $fileName);

        if (! file_exists($path)) {
            $this->error("File non trovato: {$path}");
            return self::FAILURE;
        }

        $tempPassword = $this->option('temp-password') ?: 'Aea2026!';

        $rows = Excel::toArray([], $path)[0] ?? [];
        if (count($rows) <= 1) {
            $this->warn('Nessuna riga da importare (file vuoto o solo header).');
            return self::SUCCESS;
        }

        // ===== HEADER MAP (NORMALIZZA header tipo first_name, postal_code, ecc.) =====
        $headerRow = $rows[0] ?? [];
        $map = [];

        $norm = function ($v): string {
            $v = is_string($v) ? $v : '';
            $v = trim(mb_strtolower($v));
            $v = str_replace([' ', '-', '.', ':'], '', $v); // NON rimuovo "_" così resta coerente
            return $v;
        };

        foreach ($headerRow as $i => $h) {
            $key = $norm($h);
            if ($key !== '') {
                $map[$key] = $i;
            }
        }

        $get = function (array $row, string $key) use ($map, $norm) {
            $k = $norm($key);
            if (! isset($map[$k])) return null;
            return $row[$map[$k]] ?? null;
        };

        $createdStudents = 0;
        $updatedStudents = 0;
        $createdUsers = 0;
        $skipped = 0;

        $skippedRows = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // header

            try {
                // ===== LEGGI ESATTAMENTE LE TUE COLONNE =====
                $email       = $get($row, 'email');
                $firstName   = $get($row, 'first_name');
                $lastName    = $get($row, 'last_name');
                $phone       = $get($row, 'phone');
                $taxCode     = $get($row, 'tax_code');
                $postalCode  = $get($row, 'postal_code');
                $province    = $get($row, 'province');
                $city        = $get($row, 'city');
                $addressLine = $get($row, 'address_line');

                // Normalizza
                $email     = is_string($email) ? trim($email) : $email;
                $firstName = is_string($firstName) ? trim($firstName) : $firstName;
                $lastName  = is_string($lastName) ? trim($lastName) : $lastName;

                // ===== VALIDAZIONE =====
                if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skipped++;
                    $skippedRows[] = [$index, 'Email mancante o non valida', (string)$email];
                    continue;
                }

                if (! $lastName) {
                    $skipped++;
                    $skippedRows[] = [$index, 'last_name mancante (NOT NULL)', $email];
                    continue;
                }

                $firstName = $firstName ?: '';

                // ===== CREA/RECUPERA USER =====
                $user = User::where('email', $email)->first();

                if (! $user) {
                    $user = User::create([
                        'name' => trim(($firstName ? $firstName . ' ' : '') . $lastName) ?: $email,
                        'email' => $email,
                        'password' => Hash::make($tempPassword),
                    ]);
                    $createdUsers++;

                    if (method_exists($user, 'assignRole')) {
                        $user->assignRole('studente');
                    }
                }

                // ===== CREA/AGGIORNA STUDENT =====
                $studentData = [
                    'first_name'   => $firstName,
                    'last_name'    => $lastName,
                    'email'        => $email,
                    'phone'        => is_string($phone) ? trim($phone) : $phone,
                    'tax_code'     => is_string($taxCode) ? trim($taxCode) : $taxCode,
                    'address_line' => is_string($addressLine) ? trim($addressLine) : $addressLine,
                    'postal_code'  => is_string($postalCode) ? trim($postalCode) : $postalCode,
                    'city'         => is_string($city) ? trim($city) : $city,
                    'province'     => is_string($province) ? trim($province) : $province,
                    'country'      => 'Italia',
                    'user_id'      => $user->id,
                ];

                $student = Student::where('email', $email)->first();

                if (! $student) {
                    Student::create($studentData);
                    $createdStudents++;
                } else {
                    $student->update($studentData);
                    $updatedStudents++;
                }

            } catch (\Throwable $e) {
                $skipped++;
                $skippedRows[] = [$index, 'Errore: ' . $e->getMessage(), (string)($row[0] ?? '')];
                continue;
            }
        }

        if (! empty($skippedRows)) {
            $outPath = storage_path('app/import_students_skipped.csv');
            $fh = fopen($outPath, 'w');
            fputcsv($fh, ['row_index', 'reason', 'email_or_value']);
            foreach ($skippedRows as $r) {
                fputcsv($fh, $r);
            }
            fclose($fh);

            $this->warn("Righe saltate salvate in: {$outPath}");
        }

        $this->info("Import completato ✅");
        $this->line("Users creati: {$createdUsers}");
        $this->line("Students creati: {$createdStudents}");
        $this->line("Students aggiornati: {$updatedStudents}");
        $this->line("Righe saltate: {$skipped}");
        $this->warn("Password temporanea usata (solo nuovi users): {$tempPassword} (non inviata via email)");

        return self::SUCCESS;
    }
}
