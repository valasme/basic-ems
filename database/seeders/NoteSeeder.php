<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    /**
     * The number of notes to create per user.
     */
    private const NOTES_PER_USER = 25;

    /**
     * The number of users to seed notes for.
     */
    private const USER_LIMIT = 5;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()
            ->select(['id'])
            ->orderBy('id')
            ->limit(self::USER_LIMIT)
            ->withCount('notes')
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        $users->each(function (User $user): void {
            $notesToCreate = max(0, self::NOTES_PER_USER - (int) $user->notes_count);

            if ($notesToCreate === 0) {
                return;
            }

            Note::factory()
                ->count($notesToCreate)
                ->forUser($user)
                ->create();
        });
    }
}
