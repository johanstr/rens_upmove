/*
 * Aftelklok tot de volgende opdracht.
 * Het tijdstip komt uit data-changes-at (milliseconden) op #next-break.
 * Bij 0 wordt de pagina herladen, zodat de nieuwe opdracht verschijnt.
 */
(() => {
  const countdown = document.getElementById('next-break');
  const challengeChangesAt = Number(countdown?.dataset.changesAt);

  if (!countdown || !Number.isFinite(challengeChangesAt) || challengeChangesAt === 0) {
    return;
  }

  let countdownInterval;

  const updateCountdown = () => {
    const secondsRemaining = Math.max(0, Math.ceil((challengeChangesAt - Date.now()) / 1000));
    const minutes = Math.floor(secondsRemaining / 60);
    const seconds = String(secondsRemaining % 60).padStart(2, '0');
    countdown.textContent = `${minutes}m ${seconds}s`;

    if (secondsRemaining === 0) {
      window.clearInterval(countdownInterval);
      window.location.reload();
    }
  };

  updateCountdown();
  countdownInterval = window.setInterval(updateCountdown, 1000);
})();

/*
 * Melding "Te lang gezeten!" 60 minuten verbergen na wegklikken.
 */
(() => {
  const alertCard = document.getElementById('alert-card');
  const dismissButton = document.getElementById('btn-dismiss-alert');

  if (!alertCard || !dismissButton) {
    return;
  }

  const storageKey = `project-in-beweging:alert-dismissed-until:${alertCard.dataset.userId}`;
  const duration = 60 * 60 * 1000; // 1 uur in milliseconden
  let timer;

  const showAlert = () => {
    alertCard.classList.remove('hidden');
    localStorage.removeItem(storageKey);
  };

  const scheduleAlert = (showAt) => {
    alertCard.classList.add('hidden');
    timer = window.setTimeout(showAlert, Math.max(0, showAt - Date.now()));
  };

  const dismissedUntil = Number(localStorage.getItem(storageKey));
  if (Number.isFinite(dismissedUntil) && dismissedUntil > Date.now()) {
    scheduleAlert(dismissedUntil);
  } else {
    localStorage.removeItem(storageKey);
  }

  dismissButton.addEventListener('click', () => {
    window.clearTimeout(timer);
    const showAt = Date.now() + duration;
    localStorage.setItem(storageKey, String(showAt));
    scheduleAlert(showAt);
  });
})();
