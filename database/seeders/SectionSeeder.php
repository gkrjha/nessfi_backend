<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'section' => 'Basic',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section' => 'Self Feedback',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'section' => 'Office Feedback',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($sections as $section) {
            Section::firstOrCreate(
                ['section' => $section['section']],
                $section
            );
        }

        $this->command->info('Sections seeded successfully!');
    }
}
