<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeCompletion;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Bevat alle regels rond de "challenge van het moment":
 *
 * - de tijd is verdeeld in vaste blokken van 90 minuten;
 * - per blok krijgt de gebruiker een willekeurige challenge (niet dezelfde als de vorige);
 * - de gebruiker mag per dag maximaal twee keer direct wisselen;
 * - een challenge levert per blok maar één keer XP op.
 *
 * De gekozen challenge en het aantal wissels staan in de sessie,
 * met dezelfde sleutels als in de oorspronkelijke vanilla PHP-versie.
 */
class ChallengeService
{
    public const INTERVAL_SECONDS = 90 * 60;

    public const DAILY_CHANGE_LIMIT = 2;

    private const SESSION_KEY = 'home_challenge';

    public function currentInterval(): int
    {
        return intdiv(now()->getTimestamp(), self::INTERVAL_SECONDS);
    }

    /**
     * Unix-tijdstempel waarop het volgende blok begint.
     */
    public function nextChangeAt(): int
    {
        return ($this->currentInterval() + 1) * self::INTERVAL_SECONDS;
    }

    public function changesRemaining(User $user): int
    {
        $usage = $this->usage($user);

        return self::DAILY_CHANGE_LIMIT - min(self::DAILY_CHANGE_LIMIT, max(0, $usage['count']));
    }

    /**
     * Bepaalt (en onthoudt) de challenge voor het huidige blok.
     *
     * @return array{challenge: ?Challenge, completed: bool, noDifferentAvailable: bool}
     */
    public function current(User $user): array
    {
        $interval = $this->currentInterval();
        $challenges = Challenge::query()->orderBy('id')->get();
        $saved = session(self::SESSION_KEY);
        $selected = null;
        $noDifferentAvailable = false;

        if ($challenges->isNotEmpty()) {
            if (is_array($saved) && ($saved['interval'] ?? null) === $interval) {
                $selected = $challenges->firstWhere('id', (int) ($saved['id'] ?? 0));
            }

            if ($selected === null) {
                $previousId = is_array($saved) ? (int) ($saved['id'] ?? 0) : 0;
                $options = $challenges->reject(fn (Challenge $challenge): bool => $challenge->id === $previousId);

                if ($options->isEmpty()) {
                    $noDifferentAvailable = true;
                } else {
                    $selected = $options->random();
                    session([self::SESSION_KEY => ['interval' => $interval, 'id' => $selected->id]]);
                }
            }
        }

        $completed = $selected !== null && ChallengeCompletion::query()
            ->where('user_id', $user->id)
            ->where('challenge_id', $selected->id)
            ->where('challenge_interval', $interval)
            ->exists();

        return [
            'challenge' => $selected,
            'completed' => $completed,
            'noDifferentAvailable' => $noDifferentAvailable,
        ];
    }

    /**
     * Laadt direct een andere challenge en geeft de melding voor de gebruiker terug.
     */
    public function next(User $user): string
    {
        $usage = $this->usage($user);

        if ($usage['count'] >= self::DAILY_CHANGE_LIMIT) {
            return 'Je hebt je '.self::DAILY_CHANGE_LIMIT.' directe opdrachtwissels voor vandaag al gebruikt.';
        }

        $challengeIds = Challenge::query()->orderBy('id')->pluck('id')->map(fn ($id): int => (int) $id)->all();

        if ($challengeIds === []) {
            return 'Er zijn momenteel geen opdrachten beschikbaar.';
        }

        $previousId = (int) (session(self::SESSION_KEY)['id'] ?? 0);

        if (count($challengeIds) === 1 && $challengeIds[0] === $previousId) {
            return 'Er is maar één opdracht beschikbaar, dus er is geen andere opdracht om te laden.';
        }

        $options = $challengeIds;
        if (count($options) > 1 && $previousId !== 0) {
            $options = array_values(array_filter($options, fn (int $id): bool => $id !== $previousId));
        }

        session([self::SESSION_KEY => [
            'interval' => $this->currentInterval(),
            'id' => $options[random_int(0, count($options) - 1)],
        ]]);

        $usage['count']++;
        session([$this->usageKey($user) => $usage]);

        $remaining = self::DAILY_CHANGE_LIMIT - $usage['count'];

        return $remaining === 0
            ? 'Nieuwe opdracht geladen. Je hebt vandaag geen directe opdrachtwissels meer.'
            : "Nieuwe opdracht geladen. Je kunt dit vandaag nog {$remaining} keer doen.";
    }

    /**
     * Markeert de huidige challenge als voltooid, kent XP toe en geeft de melding terug.
     */
    public function complete(User $user): string
    {
        $saved = session(self::SESSION_KEY);
        $interval = $this->currentInterval();

        if (
            ! is_array($saved)
            || (int) ($saved['interval'] ?? -1) !== $interval
            || (int) ($saved['id'] ?? 0) < 1
        ) {
            return 'Deze opdracht is niet meer actief. Ververs de pagina en probeer het opnieuw.';
        }

        $challenge = Challenge::query()->find((int) $saved['id']);

        if ($challenge === null || $challenge->xp_reward < 1) {
            return 'Deze opdracht is niet beschikbaar voor voltooiing.';
        }

        try {
            DB::transaction(function () use ($user, $challenge, $interval): void {
                ChallengeCompletion::query()->create([
                    'user_id' => $user->id,
                    'challenge_id' => $challenge->id,
                    'challenge_interval' => $interval,
                ]);

                $user->increment('xp', $challenge->xp_reward);
            });
        } catch (UniqueConstraintViolationException) {
            return 'Je hebt deze opdracht al voltooid.';
        } catch (QueryException $exception) {
            Log::error('Opdracht voltooien/databasefout: '.$exception->getMessage());

            return 'De opdracht kon niet worden voltooid. Probeer het later opnieuw.';
        }

        return "Opdracht voltooid! Je hebt {$challenge->xp_reward} XP verdiend.";
    }

    /**
     * @return array{date: string, count: int}
     */
    private function usage(User $user): array
    {
        $today = now()->toDateString();
        $usage = session($this->usageKey($user));

        if (! is_array($usage) || ($usage['date'] ?? null) !== $today) {
            return ['date' => $today, 'count' => 0];
        }

        return ['date' => $today, 'count' => (int) ($usage['count'] ?? 0)];
    }

    private function usageKey(User $user): string
    {
        return 'manual_challenge_changes_'.$user->id;
    }
}
