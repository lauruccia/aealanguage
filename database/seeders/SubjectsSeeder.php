<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectsSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            'ARABO',
            'FRANCESE',
            'INGLESE',
            'ITALIANO per STRANIERI',
            'PORTOGHESE',
            'RUSSO',
            'SPAGNOLO',
            'TEDESCO',
        ];

        foreach ($subjects as $name) {
            DB::table('subjects')->updateOrInsert(
                ['name' => $name],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
