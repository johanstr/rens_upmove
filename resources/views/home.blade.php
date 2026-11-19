@extends('layouts.app', ['scripts' => ['resources/js/home.js', 'resources/js/footer-nav.js']])

@section('title', 'Home | UpMove')

@section('body')
<div class="mx-auto flex min-h-dvh w-full max-w-lg flex-col">

  <main class="screen flex-1 overflow-y-auto px-4 pb-28 pt-[max(1rem,env(safe-area-inset-top))] sm:px-6"
    id="screen-home">

    <header class="mb-4 flex flex-wrap items-start justify-between gap-3 sm:mb-5">
      <div class="min-w-0 flex-1">
        <p class="mb-0.5 text-sm font-medium text-muted">Welkom terug</p>
        <h1 class="text-2xl font-extrabold tracking-tight sm:text-[1.75rem]">
          Hé, {{ $user->full_name }} 👋
        </h1>
        <p class="text-sm font-bold text-orange">Totaal: {{ (int) $user->xp }} XP</p>
      </div>
      <div class="mb-4 flex shrink-0 justify-end">
        <a href="{{ route('activities.index') }}"
          class="rounded-full px-3 py-2 text-xs font-bold text-white hover:bg-[#e02e06] bg-[#ff6b4a] cursor-pointer">Activiteiten</a>
      </div>
    </header>

    <div id="alert-card"
      data-user-id="{{ $user->id }}"
      class="alert-card mb-4 flex w-full items-center gap-3 rounded-[18px] border-[1.5px] border-alert-border bg-alert-bg p-3.5 text-left sm:p-4"
      role="alert">
      <span class="grid h-[42px] w-[42px] shrink-0 place-items-center rounded-xl bg-alert-icon" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
          <path d="M12 3.5L21.5 20H2.5L12 3.5Z" fill="#1A1A1A" />
          <rect x="11" y="9" width="2" height="6" rx="1" fill="#F5D76E" />
          <circle cx="12" cy="17.5" r="1.2" fill="#F5D76E" />
        </svg>
      </span>
      <span class="flex min-w-0 flex-col gap-0.5">
        <strong class="text-[15px] font-bold tracking-tight">Te lang gezeten! (60 min)</strong>
        <span class="text-[13px] leading-snug text-[#5c616a]">Tijd om op te staan en een snelle 2 min stretch te
          doen.</span>
      </span>
      <button type="button" id="btn-dismiss-alert"
        class="cursor-pointer grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white text-teal transition hover:bg-teal-soft active:scale-95"
        aria-label="Melding sluiten voor 60 minuten" title="Sluiten voor 60 minuten">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="m5 12 4.5 4.5L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
            stroke-linejoin="round" />
        </svg>
      </button>
    </div>




    <section class="mb-3.5 rounded-[22px] bg-surface p-5 shadow-card sm:p-7 sm:pb-5" aria-label="Dagelijkse voortgang">


      <div class="relative mx-auto mb-5 h-[190px] w-[190px] sm:mb-6 sm:h-[210px] sm:w-[210px]">


        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
          @if ($selectedChallenge === null)
            <p class="text-sm text-muted">
              {{ $noDifferentChallengeAvailable
                ? 'Er is geen andere opdracht beschikbaar.'
                : 'Er is nog geen opdracht beschikbaar.' }}
            </p>
          @else
            <img src="{{ $selectedChallenge->imageUrl() }}"
              alt="{{ $selectedChallenge->title }}"
              class="h-[210px] w-[210px] rounded-full object-cover" loading="lazy">
          @endif
        </div>
      </div>

      @if ($selectedChallenge !== null)
        <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2">

          <div class="flex flex-col items-center gap-1 text-center">
            <span class="text-l font-extrabold tracking-tight sm:text-[22px]">
              {{ $selectedChallenge->title }}
            </span>
            <span class="text-[13px] font-bold text-muted">Moeilijkheid:
              {{ $selectedChallenge->difficulty }}</span>
          </div>

          <div class="h-9 w-px bg-[#e6e8ec]" aria-hidden="true"></div>

          <div class="flex flex-col items-center gap-1 text-center">
            <span
              class="text-l font-extrabold tracking-tight sm:text-[22px]">{{ (int) $selectedChallenge->kcal_burned }}
              kcal</span>
            <span class="text-[13px] font-bold text-muted">{{ (int) $selectedChallenge->xp_reward }} XP te
              verdienen</span>
          </div>
        </div>
      @endif

      <div class="my-4 h-px bg-[#e6e8ec]" aria-hidden="true"></div>

      @if ($selectedChallenge !== null)
        <form method="post" action="{{ route('challenges.complete') }}" class="space-y-3">
          @csrf
          <button type="submit"
            class="w-full rounded-xl bg-teal px-4 py-3 text-sm font-extrabold text-white transition hover:brightness-95 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60"
            @disabled($challengeCompleted)>
            Voltooid
          </button>
          @if ($challengeCompletionNotice !== '')
            <p class="rounded-xl bg-white/70 px-3 py-2 text-sm font-semibold text-ink" role="status">
              {{ $challengeCompletionNotice }}
            </p>
          @endif
        </form>
      @endif

    </section>


    <section class="mb-5 flex items-center gap-3 rounded-[18px] border-2 border-teal bg-surface p-3 sm:p-3.5">

      <div class="grid h-[42px] w-[42px] shrink-0 place-items-center rounded-xl bg-teal-soft" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
          <circle cx="12" cy="13" r="8" stroke="#2EC4B6" stroke-width="2" />
          <path d="M12 9v4l2.5 1.5" stroke="#2EC4B6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M9 3h6" stroke="#2EC4B6" stroke-width="2" stroke-linecap="round" />
        </svg>
      </div>

      <div class="min-w-0 flex-1">
        <p class="text-xs font-medium text-muted">Volgende opdracht</p>
        <p class="text-base font-extrabold tracking-tight" id="next-break"
          @if ($selectedChallenge !== null) data-changes-at="{{ $challengeChangeAt * 1000 }}" @endif>
          {{ intdiv($challengeChangeAt - now()->getTimestamp(), 60) }}m {{ sprintf('%02d', ($challengeChangeAt - now()->getTimestamp()) % 60) }}s
        </p>
      </div>

    </section>


    <section>
      <h2 class="mb-3 text-lg font-extrabold tracking-tight">Klaar voor de volgende opdracht?</h2>
      <form method="post" action="{{ route('challenges.next') }}" class="rounded-[20px] bg-orange-soft p-4 sm:p-[18px]">
        @csrf
        <div class="flex items-center gap-3">
          <div class="min-w-0 flex-1">
            <p class="text-base font-extrabold text-orange">Direct de volgende opdracht starten?</p>
            <p class="text-[13px] leading-snug text-[#6a707a]">
              Je kunt vandaag nog {{ $challengeChangesRemaining }} keer direct wisselen.
            </p>
          </div>

          <button type="submit" id="btn-play"
            class="cursor-pointer btn-play grid h-14 w-14 shrink-0 place-items-center rounded-full bg-orange shadow-[0_8px_18px_rgba(255,107,74,0.35)] transition hover:shadow-[0_10px_22px_rgba(255,107,74,0.45)] active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
            aria-label="Laad een andere opdracht" @disabled($challengeChangesRemaining === 0)>
            <svg class="ml-0.5" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M8 5.5v13l11-6.5L8 5.5z" fill="#fff" />
            </svg>
          </button>
        </div>
        @if ($challengeNotice !== '')
          <p class="mt-3 rounded-xl bg-white/70 px-3 py-2 text-sm font-semibold text-ink" role="status">
            {{ $challengeNotice }}
          </p>
        @endif
      </form>
    </section>

  </main>

</div>

<x-footer-nav />
@endsection
