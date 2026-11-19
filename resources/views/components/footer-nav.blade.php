@php
  // Instellingen- en accountpagina activeren het item "Instellingen".
  $isSettingsPage = request()->routeIs('settings', 'account');

  $allowedScreens = ['home', 'bewegen', 'leren', 'stats'];

  $currentScreen = request()->query('screen', 'home');

  if (! in_array($currentScreen, $allowedScreens, true)) {
    $currentScreen = 'home';
  }

  $footerItemClass = function (bool $active): string {
    $color = $active
      ? 'text-orange'
      : 'text-[#9aa0a8]';

    return
      'nav-item flex flex-col items-center gap-1 px-0.5 py-1 ' .
      'text-[10px] font-semibold transition active:scale-95 ' .
      $color;
  };
@endphp

<nav
  class="upmove-footer fixed bottom-0 left-0 right-0 z-50 border-t border-[#eceef2] bg-white/95 backdrop-blur-md"
  aria-label="Hoofdnavigatie">
  <div
    class="mx-auto grid max-w-lg grid-cols-5 px-1.5
               pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-2">

    <!-- HOME -->
    <a
      href="{{ route('home', ['screen' => 'home']) }}"
      class="{{ $footerItemClass(! $isSettingsPage && $currentScreen === 'home') }}"
      data-screen="home">
      <svg
        width="22"
        height="22"
        viewBox="0 0 24 24"
        fill="none"
        aria-hidden="true">
        <path
          d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1v-10.5z"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linejoin="round" />
      </svg>

      <span>Home</span>
    </a>


    <!-- BEWEGEN -->
    <a
      href="{{ route('home', ['screen' => 'bewegen']) }}"
      class="{{ $footerItemClass(! $isSettingsPage && $currentScreen === 'bewegen') }}"
      data-screen="bewegen">
      <svg
        width="22"
        height="22"
        viewBox="0 0 24 24"
        fill="none"
        aria-hidden="true">
        <circle
          cx="12"
          cy="12"
          r="9"
          stroke="currentColor"
          stroke-width="1.8" />

        <path
          d="M10 8.5v7l6-3.5-6-3.5z"
          fill="currentColor" />
      </svg>

      <span>Bewegen</span>
    </a>


    <!-- LEREN -->
    <a
      href="{{ route('home', ['screen' => 'leren']) }}"
      class="{{ $footerItemClass(! $isSettingsPage && $currentScreen === 'leren') }}"
      data-screen="leren">
      <svg
        width="22"
        height="22"
        viewBox="0 0 24 24"
        fill="none"
        aria-hidden="true">
        <path
          d="M4 5.5c2.2-1 4.3-1.2 6.5 0v13c-2.2-1.2-4.3-1-6.5 0v-13zm16 0c-2.2-1-4.3-1.2-6.5 0v13c2.2-1.2 4.3-1 6.5 0v-13z"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linejoin="round" />
      </svg>

      <span>Leren</span>
    </a>


    <!-- STATISTIEKEN -->
    <a
      href="{{ route('home', ['screen' => 'stats']) }}"
      class="{{ $footerItemClass(! $isSettingsPage && $currentScreen === 'stats') }}"
      data-screen="stats">
      <svg
        width="22"
        height="22"
        viewBox="0 0 24 24"
        fill="none"
        aria-hidden="true">
        <path
          d="M5 19V11M12 19V5M19 19v-7"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round" />
      </svg>

      <span>Statistieken</span>
    </a>


    <!-- INSTELLINGEN -->
    <a
      href="{{ route('settings') }}"
      class="{{ $footerItemClass($isSettingsPage) }}"
      @if ($isSettingsPage) aria-current="page" @endif>
      <svg
        width="22"
        height="22"
        viewBox="0 0 24 24"
        fill="none"
        aria-hidden="true">
        <circle
          cx="12"
          cy="12"
          r="3"
          stroke="currentColor"
          stroke-width="1.8" />

        <path
          d="M12 2.5v2.2
                       M12 19.3v2.2
                       M4.2 6.5l1.6 1.6
                       M18.2 15.9l1.6 1.6
                       M2.5 12h2.2
                       M19.3 12h2.2
                       M4.2 17.5l1.6-1.6
                       M18.2 8.1l1.6-1.6"
          stroke="currentColor"
          stroke-width="1.8"
          stroke-linecap="round" />
      </svg>

      <span>Instellingen</span>
    </a>

  </div>
</nav>
