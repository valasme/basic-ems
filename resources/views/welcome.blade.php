<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => __('BasicEMS')])
    </head>
    <body class="min-h-screen bg-white text-zinc-900 dark:bg-zinc-950 dark:text-zinc-100">
        <div class="mx-auto flex min-h-screen w-full max-w-5xl flex-col px-6 py-10 lg:px-10 lg:py-16">
            <header class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('favicon.png') }}"
                        alt="BasicEMS"
                        class="h-10 w-10 object-cover"
                    >
                    <div>
                        <p class="text-base font-semibold tracking-wide">BasicEMS</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Lean team operations</p>
                    </div>
                </div>

                @if (Route::has('login'))
                    <div class="flex items-center gap-2 text-sm">
                        @auth
                            <a
                                href="{{ url('/dashboard') }}"
                                class="inline-flex items-center justify-center rounded-md border border-zinc-300 px-4 py-2 font-medium text-zinc-700 hover:bg-zinc-900 hover:text-white dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-100 dark:hover:text-zinc-900"
                            >
                                Open app
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="inline-flex items-center justify-center rounded-md border border-transparent px-3 py-2 font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                            >
                                Sign in
                            </a>

                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex items-center justify-center rounded-md border border-zinc-900 bg-zinc-900 px-4 py-2 font-medium text-white hover:bg-white hover:text-zinc-900 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-transparent dark:hover:text-zinc-100"
                                >
                                    Register
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </header>

            <main id="overview" class="mt-16 flex flex-1 flex-col gap-14">
                <section class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                    <div class="space-y-6">
                        <div class="inline-flex w-fit items-center gap-2 rounded-md border border-zinc-200 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                            Minimal by design
                        </div>
                        <div class="space-y-4">
                            <h1 class="text-4xl font-semibold leading-tight tracking-tight md:text-5xl">
                                Simple operations for tiny teams.
                            </h1>
                            <p class="text-base leading-relaxed text-zinc-600 dark:text-zinc-300">
                                Track people, tasks, notes, and pay dates without clutter. Designed to stay quiet and usable.
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            @if (Route::has('login'))
                                @auth
                                    <a
                                        href="{{ url('/dashboard') }}"
                                        class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                                    >
                                        Open dashboard
                                    </a>
                                @else
                                    @if (Route::has('register'))
                                        <a
                                            href="{{ route('register') }}"
                                            class="inline-flex items-center justify-center rounded-md bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-zinc-800 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                                        >
                                            Register
                                        </a>
                                    @endif
                                    <a
                                        href="{{ route('login') }}"
                                        class="inline-flex items-center justify-center rounded-md border border-zinc-300 px-5 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800"
                                    >
                                        Sign in
                                    </a>
                                @endauth
                            @endif
                        </div>
                    </div>
                    <div class="space-y-4 rounded-md border border-zinc-200 p-6 text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-400">At a glance</p>
                        <div class="space-y-3">
                            <div class="rounded-md border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                                <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">Best fit</p>
                                <p class="mt-2 text-base font-medium text-zinc-900 dark:text-zinc-100">Small, owner-led teams</p>
                            </div>
                            <div class="rounded-md border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                                <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">Modules</p>
                                <p class="mt-2 text-base font-medium text-zinc-900 dark:text-zinc-100">Employees, tasks, notes</p>
                            </div>
                            <div class="rounded-md border border-zinc-200 px-4 py-3 dark:border-zinc-800">
                                <p class="text-xs uppercase tracking-[0.2em] text-zinc-400">Stack</p>
                                <p class="mt-2 text-base font-medium text-zinc-900 dark:text-zinc-100">Laravel 12, Livewire 4</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="features" class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-md border border-zinc-200 p-5 text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-400">Employees</p>
                        <p class="mt-3 text-base font-medium text-zinc-900 dark:text-zinc-100">Employee directory with roles and schedules.</p>
                    </div>
                    <div class="rounded-md border border-zinc-200 p-5 text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-400">Tasks</p>
                        <p class="mt-3 text-base font-medium text-zinc-900 dark:text-zinc-100">Assignments, status, due dates.</p>
                    </div>
                    <div class="rounded-md border border-zinc-200 p-5 text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-400">Notes</p>
                        <p class="mt-3 text-base font-medium text-zinc-900 dark:text-zinc-100">Quiet notes with quick search.</p>
                    </div>
                </section>

                <section id="scope" class="rounded-md border border-zinc-200 p-6 text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-300">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-400">Scope</p>
                    <p class="mt-3 text-base text-zinc-900 dark:text-zinc-100">
                        Built for small businesses and personal teams. Not intended for large organizations or complex workflows.
                    </p>
                </section>
            </main>

            <footer class="mt-12 flex flex-wrap items-center justify-between gap-4 text-xs text-zinc-500 dark:text-zinc-400">
                <p>BasicEMS for teams who prefer less.</p>
                <p>Laravel 12 | Livewire 4 | Tailwind CSS 4</p>
            </footer>
        </div>
    </body>
</html>
