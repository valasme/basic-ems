<?php

namespace Tests\Feature\Notes;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_note_routes(): void
    {
        $response = $this->get(route('notes.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_create_update_or_delete_notes(): void
    {
        $note = Note::factory()->forUser(User::factory()->create())->create();

        $this->get(route('notes.create'))
            ->assertRedirect(route('login'));

        $this->post(route('notes.store'), [
            'note_title' => 'Guest note',
            'note_description' => 'Should not be created.',
        ])->assertRedirect(route('login'));

        $this->put(route('notes.update', $note), [
            'note_title' => 'Guest update',
        ])->assertRedirect(route('login'));

        $this->delete(route('notes.destroy', $note))
            ->assertRedirect(route('login'));
    }

    public function test_index_lists_only_owned_notes_and_supports_search(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->forUser($user)->create([
            'note_title' => 'Important meeting notes',
            'note_description' => 'Discussion about project timeline',
        ]);

        $otherUser = User::factory()->create();
        $otherNote = Note::factory()->forUser($otherUser)->create([
            'note_title' => 'Private note',
        ]);

        $response = $this->actingAs($user)->get(route('notes.index'));
        $response->assertOk();
        $response->assertSee($note->note_title);
        $response->assertDontSee($otherNote->note_title);

        $searchByTitle = $this->actingAs($user)->get(route('notes.index', ['search' => 'Important']));
        $searchByTitle->assertOk();
        $searchByTitle->assertSee($note->note_title);

        $searchByDescription = $this->actingAs($user)->get(route('notes.index', ['search' => 'timeline']));
        $searchByDescription->assertOk();
        $searchByDescription->assertSee($note->note_title);
    }

    public function test_index_is_paginated_to_25_notes_per_page(): void
    {
        $user = User::factory()->create();

        Note::factory()
            ->forUser($user)
            ->count(30)
            ->create();

        $response = $this->actingAs($user)->get(route('notes.index'));

        $response->assertOk();
        $response->assertViewHas('notes', function ($notes): bool {
            return $notes->count() === 25
                && $notes->perPage() === 25
                && $notes->total() === 30;
        });
    }

    public function test_user_can_create_note(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('notes.store'), [
            'note_title' => '  Meeting Summary  ',
            'note_description' => '  Discussed quarterly goals and deadlines. ',
        ]);

        $response->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', [
            'user_id' => $user->id,
            'note_title' => 'Meeting Summary',
            'note_description' => 'Discussed quarterly goals and deadlines.',
        ]);
    }

    public function test_store_validation_rejects_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('notes.store'), [
            'note_title' => 'ab',
            'note_description' => str_repeat('a', 5001),
        ]);

        $response->assertSessionHasErrors(['note_title', 'note_description']);
    }

    public function test_store_allows_empty_description(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('notes.store'), [
            'note_title' => 'Note without description',
            'note_description' => null,
        ]);

        $response->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', [
            'user_id' => $user->id,
            'note_title' => 'Note without description',
            'note_description' => null,
        ]);
    }

    public function test_user_can_update_note_with_normalized_fields(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->forUser($user)->create([
            'note_title' => 'Draft notes',
            'note_description' => 'Initial content',
        ]);

        $response = $this->actingAs($user)->put(route('notes.update', $note), [
            'note_title' => '  Updated notes  ',
            'note_description' => '  Revised content. ',
        ]);

        $response->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'note_title' => 'Updated notes',
            'note_description' => 'Revised content.',
        ]);
    }

    public function test_update_validation_rejects_invalid_data(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->put(route('notes.update', $note), [
            'note_title' => 'ab',
            'note_description' => str_repeat('a', 5001),
        ]);

        $response->assertSessionHasErrors(['note_title', 'note_description']);
    }

    public function test_user_can_view_and_edit_their_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->forUser($user)->create();

        $showResponse = $this->actingAs($user)->get(route('notes.show', $note));
        $showResponse->assertOk();
        $showResponse->assertSee($note->note_title);

        $editResponse = $this->actingAs($user)->get(route('notes.edit', $note));
        $editResponse->assertOk();
    }

    public function test_user_cannot_view_or_edit_other_users_note(): void
    {
        $user = User::factory()->create();
        $otherNote = Note::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)
            ->get(route('notes.show', $otherNote))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('notes.edit', $otherNote))
            ->assertForbidden();
    }

    public function test_user_can_update_their_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->forUser($user)->create([
            'note_title' => 'Draft notes',
            'note_description' => 'Initial content',
        ]);

        $response = $this->actingAs($user)->put(route('notes.update', $note), [
            'note_title' => 'Updated notes',
            'note_description' => 'Revised and finalized content.',
        ]);

        $response->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'note_title' => 'Updated notes',
            'note_description' => 'Revised and finalized content.',
        ]);
    }

    public function test_user_cannot_update_other_users_note(): void
    {
        $user = User::factory()->create();
        $otherNote = Note::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)
            ->put(route('notes.update', $otherNote), [
                'note_title' => 'Nope',
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_their_note(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->delete(route('notes.destroy', $note));
        $response->assertRedirect(route('notes.index'));

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_user_cannot_delete_other_users_note(): void
    {
        $user = User::factory()->create();
        $otherNote = Note::factory()->forUser(User::factory()->create())->create();

        $this->actingAs($user)
            ->delete(route('notes.destroy', $otherNote))
            ->assertForbidden();
    }
}
