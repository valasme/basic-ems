<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Note::class);

        /** @var string|null $search */
        $search = $request->query('search');
        $user = $request->user();

        /** @var LengthAwarePaginator<Note> $notes */
        $notes = Note::query()
            ->select(['id', 'note_title', 'note_description', 'created_at'])
            ->ownedBy($user)
            ->search($search)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('notes.index', [
            'notes' => $notes,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Note::class);

        return view('notes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request): RedirectResponse
    {
        $request->user()
            ->notes()
            ->create($request->validated());

        return redirect()
            ->route('notes.index')
            ->with('success', 'Note created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note): View
    {
        $this->authorize('view', $note);

        return view('notes.show', [
            'note' => $note,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note): View
    {
        $this->authorize('update', $note);

        return view('notes.edit', [
            'note' => $note,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoteRequest $request, Note $note): RedirectResponse
    {
        $note->update($request->validated());

        return redirect()
            ->route('notes.index')
            ->with('success', 'Note updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note): RedirectResponse
    {
        $this->authorize('delete', $note);

        $note->delete();

        return redirect()
            ->route('notes.index')
            ->with('success', 'Note deleted successfully.');
    }
}
