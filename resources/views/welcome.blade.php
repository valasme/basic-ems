<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => __('BasicEMS')])
    </head>
    <body class="min-h-screen bg-white text-[#14130f] dark:bg-[#0b0a07] dark:text-[#f2f0ea] font-sans">
        <div class="mx-auto flex min-h-screen w-full max-w-5xl flex-col px-6 py-10 lg:px-10 lg:py-16">
                <header class="flex items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <img
                        src="{{ asset('favicon.png') }}"
                        alt="BasicEMS"
                        class="h-10 w-10 rounded-full border border-[#14130f] object-cover dark:border-[#f5f3ee]"
                    >
                    <div>
                        <p class="text-base font-semibold tracking-wide">BasicEMS</p>
                        <p class="text-xs text-[#6b655a] dark:text-[#b3ab9f]">Built for tiny teams</p>
                    </div>
                </div>

                    @if (Route::has('login'))
                        <nav class="flex items-center gap-3 text-sm">
                            @auth
                                <a
                                    href="{{ url('/dashboard') }}"
                                    class="inline-flex items-center justify-center rounded-full border border-[#14130f] px-4 py-2 text-sm font-medium hover:bg-[#14130f] hover:text-white dark:border-[#f5f3ee] dark:hover:bg-[#f5f3ee] dark:hover:text-[#0b0a07]"
                                >
                                    Dashboard
                                </a>
                            @else
                                <a
                                    href="{{ route('login') }}"
                                    class="inline-flex items-center justify-center rounded-full border border-transparent px-3 py-2 text-sm font-medium text-[#5b554c] hover:text-[#14130f] dark:text-[#c2b9ad] dark:hover:text-[#f5f3ee]"
                                >
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        class="inline-flex items-center justify-center rounded-full border border-[#14130f] bg-[#14130f] px-4 py-2 text-sm font-medium text-white hover:bg-white hover:text-[#14130f] dark:border-[#f5f3ee] dark:bg-[#f5f3ee] dark:text-[#0b0a07] dark:hover:bg-transparent dark:hover:text-[#f5f3ee]"
                                    >
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </header>

                <main class="mt-12 grid gap-10 lg:mt-16 lg:grid-cols-[1.05fr_0.95fr]">
                    <section class="flex flex-col gap-6">
                        <div class="inline-flex w-fit items-center gap-2 rounded-full border border-[#d9d5ce] px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-[#5a544b] dark:border-[#3b352b] dark:text-[#d9d2c6]">
                            Small business focus
                        </div>
                        <div class="space-y-4">
                            <h1 class="text-4xl font-semibold leading-tight tracking-tight md:text-5xl">
                                Simple employee management for tiny teams that do not need enterprise baggage.
                            </h1>
                            <p class="text-base leading-relaxed text-[#5a544b] dark:text-[#c8c0b5]">
                                BasicEMS keeps your employee directory, tasks, and notes in one lightweight workspace. It is designed for
                                barbershops, kiosks, small retail, and personal teams that need clarity without complexity.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            @if (Route::has('login'))
                                @auth
                                    <a
                                        href="{{ url('/dashboard') }}"
                                        class="inline-flex items-center justify-center rounded-full bg-[#14130f] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#2b261f] dark:bg-[#f5f3ee] dark:text-[#0b0a07] dark:hover:bg-[#e7dfd4]"
                                    >
                                        Open dashboard
                                    </a>
                                @else
                                    @if (Route::has('register'))
                                        <a
                                            href="{{ route('register') }}"
                                            class="inline-flex items-center justify-center rounded-full bg-[#14130f] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#2b261f] dark:bg-[#f5f3ee] dark:text-[#0b0a07] dark:hover:bg-[#e7dfd4]"
                                        >
                                            Register
                                        </a>
                                    @endif
                                    <a
                                        href="{{ route('login') }}"
                                        class="inline-flex items-center justify-center rounded-full border border-[#14130f] px-5 py-2.5 text-sm font-semibold text-[#14130f] hover:bg-[#14130f] hover:text-white dark:border-[#f5f3ee] dark:text-[#f5f3ee] dark:hover:bg-[#f5f3ee] dark:hover:text-[#0b0a07]"
                                    >
                                        Log in
                                    </a>
                                @endauth
                            @endif
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-[#e0dcd6] p-4 text-sm text-[#4f493f] dark:border-[#2f2a21] dark:text-[#cfc6ba]">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#8f8573] dark:text-[#8e8476]">What it does</p>
                                <ul class="mt-3 space-y-2">
                                    <li>Employee directory with contact info and work times.</li>
                                    <li>Task tracking with status, due dates, and assignments.</li>
                                    <li>Notes with titles, optional descriptions, and search.</li>
                                </ul>
                            </div>
                            <div class="rounded-2xl border border-[#e0dcd6] p-4 text-sm text-[#4f493f] dark:border-[#2f2a21] dark:text-[#cfc6ba]">
                                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#8f8573] dark:text-[#8e8476]">Built in</p>
                                <ul class="mt-3 space-y-2">
                                    <li>Fortify auth with optional two-factor setup.</li>
                                    <li>Per-user ownership with policy protection.</li>
                                    <li>Livewire + Flux UI for fast workflows.</li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <section class="flex flex-col gap-6">
                        <div class="rounded-3xl border border-[#e0dcd6] p-6 dark:border-[#2f2a21]">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#8f8573] dark:text-[#938a7a]">Quick view</p>
                                <span class="rounded-full border border-[#d2cdc5] px-3 py-1 text-xs font-semibold text-[#6f6657] dark:border-[#3a342a] dark:text-[#b9b1a5]">v1</span>
                            </div>
                            <div class="mt-6 space-y-4">
                                <div class="rounded-2xl border border-[#e2dbcf] px-4 py-3 dark:border-[#2f2a21]">
                                    <p class="text-xs uppercase tracking-[0.2em] text-[#9a907f] dark:text-[#857c6e]">Ideal for</p>
                                    <p class="mt-2 text-base font-medium">Barbershops, kiosks, pop-ups, and personal teams</p>
                                </div>
                                <div class="rounded-2xl border border-[#e2dbcf] px-4 py-3 dark:border-[#2f2a21]">
                                    <p class="text-xs uppercase tracking-[0.2em] text-[#9a907f] dark:text-[#857c6e]">Core modules</p>
                                    <p class="mt-2 text-base font-medium">Employees, tasks, notes, and scoped search</p>
                                </div>
                                <div class="rounded-2xl border border-[#e2dbcf] px-4 py-3 dark:border-[#2f2a21]">
                                    <p class="text-xs uppercase tracking-[0.2em] text-[#9a907f] dark:text-[#857c6e]">Tech stack</p>
                                    <p class="mt-2 text-base font-medium">Laravel 12, Livewire 4, Flux UI, Tailwind CSS 4</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-[#e0dcd6] p-6 text-sm text-[#473b22] dark:border-[#2f2a21] dark:text-[#d9d2c6]">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em]">Important scope</p>
                            <p class="mt-3 text-base">
                                BasicEMS is intentionally scoped for very small businesses. It is not built for large organizations,
                                multi-department workflows, or high-volume operations.
                            </p>
                        </div>
                    </section>
                </main>

                <section class="mt-12 grid gap-4 lg:mt-16 lg:grid-cols-3">
                    <div class="rounded-2xl border border-[#e0dcd6] p-5 text-sm text-[#4a443b] dark:border-[#2f2a21] dark:text-[#c9c1b4]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#9a907f] dark:text-[#8e8476]">Employee hub</p>
                        <p class="mt-3 text-base font-medium">Contacts, roles, departments, and schedules in one place.</p>
                    </div>
                    <div class="rounded-2xl border border-[#e0dcd6] p-5 text-sm text-[#4a443b] dark:border-[#2f2a21] dark:text-[#c9c1b4]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#9a907f] dark:text-[#8e8476]">Task clarity</p>
                        <p class="mt-3 text-base font-medium">Assign work, track progress, and hit deadlines without noise.</p>
                    </div>
                    <div class="rounded-2xl border border-[#e0dcd6] p-5 text-sm text-[#4a443b] dark:border-[#2f2a21] dark:text-[#c9c1b4]">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#9a907f] dark:text-[#8e8476]">Notes + search</p>
                        <p class="mt-3 text-base font-medium">Capture quick updates and find them instantly.</p>
                    </div>
                </section>

                <footer class="mt-12 flex flex-wrap items-center justify-between gap-4 text-xs text-[#7a7164] dark:text-[#9d9488]">
                    <p>BasicEMS keeps small businesses focused and organized.</p>
                    <p>Laravel 12 | Livewire 4 | Tailwind CSS 4</p>
                </footer>
        </div>
    </body>
</html>
