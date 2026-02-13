<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;
use Throwable;

class NoteController extends Controller
{
    /**
     * Available filter modes for note listing.
     *
     * @var list<string>
     */
    private const FILTERS = [
        'title_alpha',
        'title_reverse',
        'description_alpha',
        'description_reverse',
        'created_newest',
        'created_oldest',
        'updated_newest',
        'updated_oldest',
    ];

    /**
     * Backward-compatible aliases for simple and legacy filter values.
     *
     * @var array<string, string>
     */
    private const FILTER_ALIASES = [
        'title' => 'title_alpha',
        'description' => 'description_alpha',
        'created_date' => 'created_newest',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Note::class);

        /** @var string|null $search */
        $search = $request->query('search');
        $user = $request->user();

        /** @var string|null $rawFilter */
        $rawFilter = is_string($request->query('filter'))
            ? $request->query('filter')
            : null;

        [$filter, $validationError] = $this->resolveFilter($rawFilter);

        if ($validationError !== null) {
            $request->session()->flash('error', $validationError);
        }

        /** @var LengthAwarePaginator<Note> $notes */
        $notes = $this->emptyNotesPaginator($request);

        try {
            $notes = $this->buildFilteredNotesPaginator($user->id, $search, $filter, $request);
        } catch (Throwable $exception) {
            report($exception);

            $request->session()->flash('error', 'Unable to apply the selected filter. Showing default ordering instead.');
            $filter = 'created_newest';

            try {
                $notes = $this->buildFilteredNotesPaginator($user->id, $search, $filter, $request);
            } catch (Throwable $fallbackException) {
                report($fallbackException);

                $request->session()->flash('error', 'Unable to load notes right now. Please try again.');
            }
        }

        return view('notes.index', [
            'notes' => $notes,
            'search' => $search,
            'filter' => $filter,
        ]);
    }

    /**
     * Resolve filter value from request input.
     *
     * @return array{0: string, 1: string|null}
     */
    private function resolveFilter(?string $rawFilter): array
    {
        if ($rawFilter === null) {
            return ['created_newest', null];
        }

        $candidate = self::FILTER_ALIASES[$rawFilter] ?? $rawFilter;

        if (! in_array($candidate, self::FILTERS, true)) {
            return ['created_newest', 'Invalid filter selected. Showing default ordering.'];
        }

        return [$candidate, null];
    }

    /**
     * Build paginated notes for the selected filter.
     *
     * @return LengthAwarePaginator<Note>
     */
    private function buildFilteredNotesPaginator(int $userId, ?string $search, string $filter, Request $request): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<Note> $notes */
        $notes = $this->applyFilter(
            Note::query()
                ->select(['id', 'note_title', 'note_description', 'created_at', 'updated_at'])
                ->where('user_id', $userId)
                ->search($search),
            $filter
        )
            ->paginate(25)
            ->withQueryString();

        return $notes;
    }

    /**
     * Get an empty paginator for failure fallback states.
     *
     * @return LengthAwarePaginator<Note>
     */
    private function emptyNotesPaginator(Request $request): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<Note> $paginator */
        $paginator = new BaseLengthAwarePaginator(
            collect(),
            0,
            25,
            $request->integer('page', 1),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return $paginator;
    }

    /**
     * Apply ordering strategy based on selected filter mode.
     *
     * @param  Builder<Note>  $query
     * @return Builder<Note>
     */
    private function applyFilter(Builder $query, string $filter): Builder
    {
        return match ($filter) {
            'title_alpha' => $query->orderBy('note_title')->latest('created_at'),
            'title_reverse' => $query->orderByDesc('note_title')->latest('created_at'),
            'description_alpha' => $query
                ->orderByRaw('CASE WHEN note_description IS NULL OR note_description = "" THEN 1 ELSE 0 END')
                ->orderBy('note_description')
                ->orderBy('note_title'),
            'description_reverse' => $query
                ->orderByRaw('CASE WHEN note_description IS NULL OR note_description = "" THEN 1 ELSE 0 END')
                ->orderByDesc('note_description')
                ->orderBy('note_title'),
            'created_oldest' => $query->orderBy('created_at')->orderBy('id'),
            'updated_newest' => $query->latest('updated_at')->latest('id'),
            'updated_oldest' => $query->orderBy('updated_at')->orderBy('id'),
            default => $query->latest('created_at')->latest('id'),
        };
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
        try {
            $request->user()
                ->notes()
                ->create($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to create note right now. Please try again.');
        }

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
        try {
            $note->update($request->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Unable to update note right now. Please try again.');
        }

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

        try {
            $note->delete();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('notes.index')
                ->with('error', 'Unable to delete note right now. Please try again.');
        }

        return redirect()
            ->route('notes.index')
            ->with('success', 'Note deleted successfully.');
    }
}
